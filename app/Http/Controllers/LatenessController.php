<?php

namespace App\Http\Controllers;

use App\Exports\LatenessExport;
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
        $records = $this->baseQuery($filters)
            ->orderBy('date', 'desc')
            ->orderBy('actual_scan_in', 'desc')
            ->paginate(50)
            ->appends($request->query());

        return view('lateness.index', [
            'records' => $records,
            'employees' => Employee::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function data(Request $request)
    {
        $filters = $this->filters($request);
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
                'schedule_in' => $record->schedule_time_in ?? '-',
                'status' => $this->formatStatus($record->status),
                'late_duration' => $record->late_duration ?? '-',
                'late_minutes' => $record->late_minutes ?? 0,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function recap(Request $request)
    {
        $filters = $this->filters($request);
        $records = $this->baseQuery($filters)->get();
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

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $monthName = Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->format('Y_m');
        $filename = "Lateness_Report_{$monthName}.xlsx";

        return Excel::download(new LatenessExport($filters), $filename);
    }

    private function filters(Request $request): array
    {
        return [
            'bulan' => (int) ($request->input('bulan') ?: now()->format('m')),
            'tahun' => (int) ($request->input('tahun') ?: now()->format('Y')),
            'employee' => $request->input('employee'),
            'status' => $request->input('status'),
        ];
    }

    private function baseQuery(array $filters)
    {
        $query = LatenessRecord::with(['employee', 'check', 'schedule'])
            ->forMonth($filters['tahun'], $filters['bulan']);

        if (!empty($filters['employee'])) {
            $employee = $filters['employee'];
            $query->whereHas('employee', function ($query) use ($employee) {
                $query->where('id', $employee)->orWhere('emp_id', $employee);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
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
