<?php

namespace App\Console\Commands;

use App\Models\Check;
use App\Services\LatenessCalculatorService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateLatenessCommand extends Command
{
    protected $signature = 'attendance:calculate-lateness
        {--date= : Hitung satu tanggal, format Y-m-d}
        {--month= : Hitung satu bulan, format Y-m}
        {--employee= : Filter karyawan berdasarkan employees.id atau employees.emp_id}
        {--force : Hitung ulang record yang sudah ada}';

    protected $description = 'Calculate employee lateness records from checks.';

    public function handle(LatenessCalculatorService $service): int
    {
        [$start, $end] = $this->resolveDateRange();

        $query = Check::with(['employee', 'schedule'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('attendance_time', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->whereNull('attendance_time')
                            ->whereBetween('leave_time', [$start, $end]);
                    });
            })
            ->orderBy('attendance_time');

        if ($employee = $this->option('employee')) {
            $query->whereHas('employee', function ($query) use ($employee) {
                $query->where('id', $employee)->orWhere('emp_id', $employee);
            });
        }

        $checks = $query->get();
        if ($checks->isEmpty()) {
            $this->info('Tidak ada data checks untuk periode tersebut.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($checks->count());
        $bar->start();

        $calculated = 0;
        foreach ($checks as $check) {
            if ($service->calculate($check, (bool) $this->option('force'))) {
                $calculated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai. {$calculated} record keterlambatan dihitung/disimpan.");

        return self::SUCCESS;
    }

    private function resolveDateRange(): array
    {
        if ($date = $this->option('date')) {
            $carbon = Carbon::parse($date);
            return [$carbon->copy()->startOfDay(), $carbon->copy()->endOfDay()];
        }

        if ($month = $this->option('month')) {
            $carbon = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            return [$carbon->copy()->startOfDay(), $carbon->copy()->endOfMonth()->endOfDay()];
        }

        $today = Carbon::today();
        return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
    }
}
