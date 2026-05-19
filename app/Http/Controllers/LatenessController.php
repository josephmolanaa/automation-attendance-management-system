<?php

namespace App\Http\Controllers;

use App\Exports\LatenessExport;
use App\Models\Check;
use App\Models\Employee;
use App\Models\LatenessRecord;
use App\Services\LatenessCalculatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LatenessController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);

        return view('lateness.index', [
            'employees' => Employee::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function data(Request $request, LatenessCalculatorService $service)
    {
        $filters = $this->filters($request);
        $this->syncCalculatedRecords($filters, $service);

        $records = $this->baseQuery($filters)
            ->orderBy('date', 'desc')
            ->orderBy('actual_scan_in', 'desc')
            ->get();

        $data = $records->map(function ($record) {
            return [
                'nip' => $record->employee->emp_id ?? $record->employee->id ?? '-',
                'name' => $record->employee->name ?? '-',
                'position' => $record->employee->position ?? '-',
                'date' => $record->date ? \Carbon\Carbon::parse($record->date)->format('Y-m-d') : '-',
                'day' => $record->date ? __('app.' . strtolower(\Carbon\Carbon::parse($record->date)->format('l'))) : '-',
                'scan_in' => $record->actual_scan_in ?? '-',
                'scan_out' => $record->actual_scan_out ?? '-',
                'shift' => $record->schedule->slug ?? '-',
                'schedule_in' => $record->scheduled_in ? \Carbon\Carbon::parse($record->scheduled_in)->format('H:i:s') : '-',
                'status' => $this->formatStatus($record->status),
                'late_duration' => $record->late_duration ?? '-',
                'late_minutes' => $record->late_minutes ?? 0,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function recap(Request $request, LatenessCalculatorService $service)
    {
        $filters = $this->filters($request);
        $this->syncCalculatedRecords($filters, $service);

        $records = $this->baseQuery($filters, false)->get();
        $recap = $records
            ->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employee;
                $lateItems = $items->where('status', 'terlambat');
                $totalMinutes = $lateItems->sum('late_minutes');

                return [
                    'employee' => $employee,
                    'total_late_days' => $lateItems->count(),
                    'total_minutes' => $totalMinutes,
                    'total_duration' => $this->formatMinutes($totalMinutes),
                    'average_minutes' => $lateItems->count() > 0
                        ? round($totalMinutes / $lateItems->count(), 1)
                        : 0,
                ];
            })
            ->sortByDesc('total_late_days')
            ->values();

        return view('lateness.recap', [
            'recap' => $recap,
            'employees' => Employee::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function calculate(Request $request, LatenessCalculatorService $service)
    {
        $month = $request->input('month') ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $records = $service->calculateRange(
            $start,
            $end,
            $request->input('employee') ?: null,
            (bool) $request->input('force', false)
        );

        return redirect()
            ->route('lateness.index', [
                'bulan' => $start->format('m'),
                'tahun' => $start->format('Y'),
                'employee' => $request->input('employee'),
            ])
            ->with('success', __('app.lateness_calculated', ['count' => $records->count()]));
    }

    public function export(Request $request, LatenessCalculatorService $service)
    {
        $filters = $this->filters($request);
        $this->syncCalculatedRecords($filters, $service);

        $period = ($filters['tahun'] ?: 'all_years') . '_' . ($filters['bulan'] ? str_pad($filters['bulan'], 2, '0', STR_PAD_LEFT) : 'all_months');
        $filename = "Lateness_Report_{$period}.xlsx";

        return Excel::download(new LatenessExport($filters), $filename);
    }

    private function filters(Request $request): array
    {
        $month = $request->has('bulan') ? $request->input('bulan') : null;
        $year = $request->has('tahun') ? $request->input('tahun') : now()->format('Y');

        return [
            'bulan' => $month !== null && $month !== '' ? (int) $month : null,
            'tahun' => $year !== null && $year !== '' ? (int) $year : null,
            'employee' => $request->input('employee'),
            'status' => $request->input('status'),
        ];
    }

    private function baseQuery(array $filters, bool $lateOnly = true)
    {
        $query = LatenessRecord::with(['employee', 'check', 'schedule']);

        if (!empty($filters['tahun'])) {
            $query->whereYear('date', $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $query->whereMonth('date', $filters['bulan']);
        }

        if (!empty($filters['employee'])) {
            $employee = $filters['employee'];
            $query->whereHas('employee', function ($query) use ($employee) {
                $query->where('id', $employee)->orWhere('emp_id', $employee);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif ($lateOnly) {
            // Default: only show late records in the main table
            $query->where('status', 'terlambat');
        }

        return $query;
    }

    private function syncCalculatedRecords(array $filters, LatenessCalculatorService $service): void
    {
        $query = Check::query()
            ->where(function ($query) {
                $query->whereNotNull('attendance_time')
                    ->orWhereNotNull('leave_time');
            })
            ->orderBy('attendance_time');

        $this->applyCheckPeriodFilters($query, $filters);

        if (!empty($filters['employee'])) {
            $employee = $filters['employee'];
            $query->whereHas('employee', function ($query) use ($employee) {
                $query->where('id', $employee)->orWhere('emp_id', $employee);
            });
        }

        $query->get()->each(function (Check $check) use ($service) {
            $service->calculate($check, true);
        });
    }

    private function applyCheckPeriodFilters($query, array $filters): void
    {
        if (!empty($filters['tahun'])) {
            $start = !empty($filters['bulan'])
                ? Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->startOfMonth()
                : Carbon::createFromDate($filters['tahun'], 1, 1)->startOfYear();

            $end = !empty($filters['bulan'])
                ? $start->copy()->endOfMonth()
                : Carbon::createFromDate($filters['tahun'], 12, 31)->endOfYear();

            $query->where(function ($query) use ($start, $end) {
                $query->whereBetween('attendance_time', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->whereNull('attendance_time')
                            ->whereBetween('leave_time', [$start, $end]);
                    })
                    ->orWhereBetween('leave_time', [$start, $end]);
            });

            return;
        }

        if (!empty($filters['bulan'])) {
            $query->where(function ($query) use ($filters) {
                $query->whereMonth('attendance_time', $filters['bulan'])
                    ->orWhereMonth('leave_time', $filters['bulan']);
            });
        }
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return sprintf('%02d:%02d:00', $hours, $remaining);
    }

    private function formatStatus(string $status): string
    {
        $statusMap = [
            'terlambat' => '<span class="badge badge-danger">' . __('app.status_late') . '</span>',
            'tepat_waktu' => '<span class="badge badge-success">' . __('app.status_on_time') . '</span>',
            'tidak_ada_scan' => '<span class="badge badge-secondary">' . __('app.status_no_scan') . '</span>',
        ];

        return $statusMap[$status] ?? '<span class="badge badge-secondary">' . $status . '</span>';
    }
}
