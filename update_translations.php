<?php
/**
 * Batch Translation Update Script
 * This script updates common Indonesian text to use translation helpers
 */

$replacements = [
    // Common DataTables
    "'Tampilkan'" => "__('app.show')",
    '"Tampilkan"' => '__("app.show")',
    "'data'" => "__('app.entries')",
    '"data"' => '__("app.entries")',
    "'Cari:'" => "__('app.search') . ':'",
    '"Cari:"' => '__("app.search") . ":"',
    "'Cari'" => "__('app.search')",
    '"Cari"' => '__("app.search")',
    "'Menampilkan'" => "__('app.showing')",
    '"Menampilkan"' => '__("app.showing")',
    "'sampai'" => "__('app.to')",
    '"sampai"' => '__("app.to")',
    "'dari'" => "__('app.of')",
    '"dari"' => '__("app.of")',
    "'Tidak ada data tersedia'" => "__('app.no_data_available')",
    '"Tidak ada data tersedia"' => '__("app.no_data_available")',
    "'Tidak ada data yang tersedia'" => "__('app.no_data_available')",
    '"Tidak ada data yang tersedia"' => '__("app.no_data_available")',
    
    // Months
    "'Semua Bulan'" => "__('app.all_months')",
    '"Semua Bulan"' => '__("app.all_months")',
    "'Mei'" => "__('app.may')",
    '"Mei"' => '__("app.may")',
    
    // Common
    "'Semua Karyawan'" => "__('app.all_employees')",
    '"Semua Karyawan"' => '__("app.all_employees")',
    "'Bulan'" => "__('app.month')",
    '"Bulan"' => '__("app.month")',
    "'Tahun'" => "__('app.year')",
    '"Tahun"' => '__("app.year")',
    "'Dari Tanggal'" => "__('app.from_date')",
    '"Dari Tanggal"' => '__("app.from_date")',
    "'Sampai Tanggal'" => "__('app.to_date')",
    '"Sampai Tanggal"' => '__("app.to_date")',
    "'Reset'" => "__('app.reset')",
    '"Reset"' => '__("app.reset")',
    "'Export Excel'" => "__('app.export') . ' Excel'",
    '"Export Excel"' => '__("app.export") . " Excel"',
    
    // Page titles
    "'Izin & Cuti'" => "__('app.leave_permission')",
    '"Izin & Cuti"' => '__("app.leave_permission")',
    "'Manual Attendance'" => "__('app.manual_attendance')",
    '"Manual Attendance"' => '__("app.manual_attendance")',
    "'Sheet Report'" => "__('app.sheet_report')",
    '"Sheet Report"' => '__("app.sheet_report")',
    "'Over Time'" => "__('app.overtime')",
    '"Over Time"' => '__("app.overtime")',
    "'Late Time'" => "__('app.late_time')",
    '"Late Time"' => '__("app.late_time")',
];

$files = [
    'resources/views/admin/izindancuti.blade.php',
    'resources/views/admin/sheet-report.blade.php',
    'resources/views/admin/check.blade.php',
    'resources/views/admin/scanlog-upload.blade.php',
    'resources/views/admin/overtime.blade.php',
    'resources/views/admin/employee.blade.php',
    'resources/views/admin/latetime.blade.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, "{{ $replace }}", $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    } else {
        echo "No changes: $file\n";
    }
}

echo "\nDone!\n";
