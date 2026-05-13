<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */
    'nav' => [
        'home' => 'Beranda',
        'dashboard' => 'Dashboard',
        'employees' => 'Karyawan',
        'attendance' => 'Absensi',
        'schedule' => 'Jadwal',
        'reports' => 'Laporan',
        'settings' => 'Pengaturan',
        'logout' => 'Keluar',
        'profile' => 'Profil',
        'lock_screen' => 'Kunci Layar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu
    |--------------------------------------------------------------------------
    */
    'sidebar' => [
        'main' => 'Utama',
        'dashboard' => 'Dashboard',
        'employees_menu' => 'Karyawan',
        'employees_list' => 'Daftar Karyawan',
        'management' => 'Manajemen',
        'schedule_menu' => 'Jadwal',
        'attendance_sheet' => 'Sheet Absensi',
        'sheet_report_menu' => 'Laporan Sheet',
        'attendance_logs' => 'Log Absensi',
        'late_time_menu' => 'Keterlambatan',
        'leave_permission_menu' => 'Izin & Cuti',
        'overtime_menu' => 'Lembur',
        'tools' => 'Tools',
        'biometric_device' => 'Perangkat Biometrik',
        'upload_scanlog' => 'Upload Scanlog',
    ],

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb
    |--------------------------------------------------------------------------
    */
    'breadcrumb' => [
        'home' => 'Beranda',
        'attendance' => 'Absensi',
        'employees' => 'Karyawan',
        'schedule' => 'Jadwal',
        'reports' => 'Laporan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */
    'form' => [
        'label' => [
            'name' => 'Nama',
            'email' => 'Email',
            'phone' => 'Telepon',
            'position' => 'Posisi',
            'employee_id' => 'ID Karyawan',
            'shift' => 'Shift',
            'status' => 'Status',
            'date' => 'Tanggal',
            'time_in' => 'Jam Masuk',
            'time_out' => 'Jam Keluar',
            'duration' => 'Durasi',
            'reason' => 'Alasan',
            'note' => 'Catatan',
            'required' => 'wajib diisi',
        ],
        'placeholder' => [
            'search' => 'Cari...',
            'select' => 'Pilih...',
            'enter_name' => 'Masukkan nama',
            'enter_email' => 'Masukkan email',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */
    'button' => [
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'delete' => 'Hapus',
        'edit' => 'Edit',
        'add' => 'Tambah',
        'add_new' => 'Tambah Baru',
        'search' => 'Cari',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'close' => 'Tutup',
        'submit' => 'Kirim',
        'export' => 'Export',
        'import' => 'Import',
        'download' => 'Unduh',
        'upload' => 'Unggah',
        'back' => 'Kembali',
        'next' => 'Selanjutnya',
        'previous' => 'Sebelumnya',
        'view_all' => 'Lihat Semua',
        'view_details' => 'Lihat Detail',
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */
    'message' => [
        'success' => [
            'title' => 'Berhasil!',
            'save' => 'Data berhasil disimpan',
            'delete' => 'Data berhasil dihapus',
            'update' => 'Data berhasil diperbarui',
            'import' => 'Data berhasil diimpor',
        ],
        'error' => [
            'title' => 'Gagal!',
            'general' => 'Terjadi kesalahan',
            'not_found' => 'Data tidak ditemukan',
            'validation' => 'Terjadi kesalahan validasi',
        ],
        'warning' => [
            'title' => 'Peringatan!',
        ],
        'info' => [
            'title' => 'Informasi',
            'no_data' => 'Tidak ada data tersedia',
            'loading' => 'Memuat...',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */
    'alert' => [
        'confirm_delete' => 'Apakah Anda yakin ingin menghapus data ini?',
        'confirm_save' => 'Apakah Anda yakin ingin menyimpan perubahan?',
        'button' => [
            'yes_delete' => 'Ya, Hapus',
            'yes' => 'Ya',
            'no' => 'Tidak',
            'cancel' => 'Batal',
            'ok' => 'OK',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DataTables
    |--------------------------------------------------------------------------
    */
    'datatable' => [
        'show' => 'Tampilkan',
        'entries' => 'data',
        'search' => 'Cari',
        'showing' => 'Menampilkan',
        'to' => 'sampai',
        'of' => 'dari',
        'results' => 'hasil',
        'no_data_available' => 'Tidak ada data tersedia',
        'loading' => 'Memuat...',
        'copy' => 'Salin',
        'excel' => 'Excel',
        'pdf' => 'PDF',
        'print' => 'Cetak',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dates - Month Names
    |--------------------------------------------------------------------------
    */
    'month' => [
        'january' => 'Januari',
        'february' => 'Februari',
        'march' => 'Maret',
        'april' => 'April',
        'may' => 'Mei',
        'june' => 'Juni',
        'july' => 'Juli',
        'august' => 'Agustus',
        'september' => 'September',
        'october' => 'Oktober',
        'november' => 'November',
        'december' => 'Desember',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dates - Day Names
    |--------------------------------------------------------------------------
    */
    'day' => [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dates - Relative Time
    |--------------------------------------------------------------------------
    */
    'date' => [
        'today' => 'Hari Ini',
        'yesterday' => 'Kemarin',
        'last_week' => 'Minggu Lalu',
        'from_date' => 'Dari Tanggal',
        'to_date' => 'Sampai Tanggal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Keys (Backward Compatibility)
    |--------------------------------------------------------------------------
    */
    // Navigation & Menu
    'home' => 'Beranda',
    'attendance' => 'Absensi',
    'employees' => 'Karyawan',
    'schedule' => 'Jadwal',
    'reports' => 'Laporan',
    'settings' => 'Pengaturan',
    'logout' => 'Keluar',
    'profile' => 'Profil',
    'lock_screen' => 'Kunci Layar',
    
    // Attendance
    'attendance_list' => 'Daftar Absensi',
    'late_time' => 'Keterlambatan',
    'overtime' => 'Lembur',
    'leave_permission' => 'Izin & Cuti',
    'holiday_manager' => 'Kelola Hari Libur',
    'manual_check' => 'Input Manual',
    'sheet_report' => 'Laporan Sheet',
    'import_csv' => 'Import CSV',
    
    // Page Titles
    'manual_attendance' => 'Absensi Manual',
    'import_attendance_data' => 'Import Data Absensi',
    
    // Table Headers
    'employee_id' => 'ID Karyawan',
    'name' => 'Nama',
    'position' => 'Posisi',
    'shift' => 'Shift',
    'status' => 'Status',
    'date' => 'Tanggal',
    'time_in' => 'Jam Masuk',
    'time_out' => 'Jam Keluar',
    'duration' => 'Durasi',
    'late_duration' => 'Durasi Terlambat',
    'overtime_duration' => 'Durasi Lembur',
    'reason' => 'Alasan',
    'note' => 'Catatan',
    'action' => 'Aksi',
    'email' => 'Email',
    'member_since' => 'Bergabung Sejak',
    'actions' => 'Aksi',
    'job_position' => 'Posisi Jabatan',
    'day' => 'Hari',
    'week' => 'Minggu',
    'scan_1' => 'Scan 1',
    'scan_2' => 'Scan 2',
    'scan_3' => 'Scan 3',
    'normal' => 'Normal',
    'double' => 'Double',
    'izin_cuti' => 'Izin/Cuti',
    'schedule_time_out' => 'Jadwal Keluar',
    'actual_time_out' => 'Waktu Keluar Aktual',
    
    // Upload Scanlog
    'upload_csv' => 'Upload CSV',
    'review_preview' => 'Review Preview',
    'confirm_import' => 'Konfirmasi Import',
    'upload_csv_file' => 'Upload File CSV Absensi',
    'need_template' => 'Butuh template CSV?',
    'download_template' => 'Download Template CSV',
    'drag_drop_csv' => 'Drag & Drop file CSV di sini',
    'or_click_to_select' => 'atau klik untuk memilih file. Format: CSV / TXT, max 5MB',
    'csv_format_detected' => 'Format CSV yang diterima',
    'back_preview_csv' => 'Back & Preview CSV',
    'edit_csv_again' => 'Edit file CSV lagi',
    
    // Status
    'on_time' => 'Tepat Waktu',
    'late' => 'Terlambat',
    'no_scan_in' => 'Belum Scan Masuk',
    'no_scan_out' => 'Belum Scan Keluar',
    'present' => 'Hadir',
    'absent' => 'Tidak Hadir',
    'leave' => 'Cuti',
    'permission' => 'Izin',
    'sick' => 'Sakit',
    
    // Filters
    'filter' => 'Filter',
    'month' => 'Bulan',
    'year' => 'Tahun',
    'all_months' => 'Semua Bulan',
    'all_years' => 'Semua Tahun',
    'reset' => 'Reset',
    'search' => 'Cari',
    'show' => 'Tampilkan',
    'entries' => 'data',
    
    // Months (flat keys for backward compatibility)
    'january' => 'Januari',
    'february' => 'Februari',
    'march' => 'Maret',
    'april' => 'April',
    'may' => 'Mei',
    'june' => 'Juni',
    'july' => 'Juli',
    'august' => 'Agustus',
    'september' => 'September',
    'october' => 'Oktober',
    'november' => 'November',
    'december' => 'Desember',
    
    // Days (flat keys for backward compatibility)
    'sunday' => 'Minggu',
    'monday' => 'Senin',
    'tuesday' => 'Selasa',
    'wednesday' => 'Rabu',
    'thursday' => 'Kamis',
    'friday' => 'Jumat',
    'saturday' => 'Sabtu',
    
    // DataTables (flat keys for backward compatibility)
    'showing' => 'Menampilkan',
    'to' => 'sampai',
    'of' => 'dari',
    'results' => 'hasil',
    'no_data_available' => 'Tidak ada data tersedia',
    'loading' => 'Memuat...',
    'copy' => 'Salin',
    'excel' => 'Excel',
    'pdf' => 'PDF',
    'print' => 'Cetak',
    
    // Common Filters
    'all_employees' => 'Semua Karyawan',
    'select_employee' => 'Pilih Karyawan',
    'select_month' => 'Pilih Bulan',
    'select_year' => 'Pilih Tahun',
    
    // Buttons (flat keys for backward compatibility)
    'add_new' => 'Tambah Baru',
    'edit' => 'Edit',
    'delete' => 'Hapus',
    'save' => 'Simpan',
    'cancel' => 'Batal',
    'close' => 'Tutup',
    'submit' => 'Kirim',
    'export' => 'Export',
    'import' => 'Import',
    'download' => 'Unduh',
    'upload' => 'Unggah',
    'back' => 'Kembali',
    'next' => 'Selanjutnya',
    'previous' => 'Sebelumnya',
    
    // Messages (flat keys for backward compatibility)
    'success' => 'Berhasil!',
    'error' => 'Gagal!',
    'warning' => 'Peringatan!',
    'info' => 'Informasi',
    'confirm_delete' => 'Apakah Anda yakin ingin menghapus data ini?',
    'data_saved' => 'Data berhasil disimpan',
    'data_deleted' => 'Data berhasil dihapus',
    'data_updated' => 'Data berhasil diperbarui',
    'no_data' => 'Tidak ada data tersedia',
    
    // Holiday Manager
    'holiday' => 'Hari Libur',
    'weekday' => 'Hari Kerja',
    'national_holiday' => 'Tanggal Merah (API)',
    'admin_override' => 'Override Admin',
    'day_type' => 'Tipe Hari',
    'specific_shift' => 'Shift Spesifik',
    'optional' => 'opsional',
    'auto_detect' => 'Auto detect dari jam scan',
    'save_override' => 'Simpan Override',
    'delete_override' => 'Hapus Override',
    
    // Dashboard
    'good_morning' => 'Selamat Pagi',
    'good_afternoon' => 'Selamat Siang',
    'good_evening' => 'Selamat Malam',
    'total_employees' => 'Total Karyawan',
    'on_time_percentage' => 'Tepat Waktu',
    'present_today' => 'Hadir Hari Ini',
    'late_today' => 'Terlambat',
    'view_all' => 'Lihat Semua',
    'today' => 'Hari Ini',
    'view_attendance' => 'Lihat Absensi',
    'view_details' => 'Lihat Detail',
    'monthly_report' => 'Laporan Bulanan',
    'recent_attendance' => 'Absensi Terbaru',
    'no_attendance_data' => 'Belum ada data absensi',
    'scan_in' => 'Scan Masuk',
    'scan_out' => 'Scan Keluar',
    
    // Sidebar Menu (flat keys for backward compatibility)
    'main' => 'Utama',
    'dashboard' => 'Dashboard',
    'employees_menu' => 'Karyawan',
    'employees_list' => 'Daftar Karyawan',
    'management' => 'Manajemen',
    'schedule_menu' => 'Jadwal',
    'attendance_sheet' => 'Sheet Absensi',
    'sheet_report_menu' => 'Laporan Sheet',
    'attendance_logs' => 'Log Absensi',
    'late_time_menu' => 'Keterlambatan',
    'leave_permission_menu' => 'Izin & Cuti',
    'overtime_menu' => 'Lembur',
    'tools' => 'Tools',
    'biometric_device' => 'Perangkat Biometrik',
    'upload_scanlog' => 'Upload Scanlog',
    
    // Breadcrumb (flat keys for backward compatibility)
    'breadcrumb_home' => 'Beranda',
    'breadcrumb_attendance' => 'Absensi',
    'breadcrumb_employees' => 'Karyawan',
    'breadcrumb_schedule' => 'Jadwal',
    'breadcrumb_reports' => 'Laporan',
];
