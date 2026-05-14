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

    // Additional UI copy
    'app_title' => 'Sistem Manajemen Absensi',
    'admin_dashboard' => 'Dashboard Admin',
    'dark_mode' => 'Mode Gelap',
    'login' => 'Masuk',
    'welcome' => 'Selamat Datang',
    'admin_login' => 'Masuk sebagai Admin',
    'remember_me' => 'Ingat Saya',
    'password' => 'Password',
    'id' => 'ID',
    'all' => 'Semua',
    'all_data' => 'Semua',
    'add' => 'Tambah',
    'update' => 'Perbarui',
    'schedules' => 'Jadwal',
    'add_employee' => 'Tambah Karyawan',
    'edit_employee' => 'Edit Karyawan',
    'delete_employee' => 'Hapus Karyawan',
    'add_schedule' => 'Tambah Jadwal',
    'update_schedule' => 'Perbarui Jadwal',
    'delete_schedule' => 'Hapus Jadwal',
    'enter_employee_id' => 'Masukkan ID karyawan',
    'enter_employee_name' => 'Masukkan nama karyawan',
    'enter_position' => 'Masukkan posisi',
    'select' => 'Pilih',
    'from' => 'dari',
    'to_time' => 'sampai',
    'confirm_delete_item' => 'Apakah Anda yakin ingin menghapus:',
    'data_not_saved_retry' => 'Data belum tersimpan. Coba lagi.',
    'saving' => 'Menyimpan...',
    'employee' => 'Karyawan',
    'employees_count' => 'karyawan',
    'days_count' => 'hari',
    'work_days_count' => 'hari kerja',
    'cell_edit_hint' => 'Klik sel untuk edit',
    'no_employees' => 'Tidak ada karyawan',
    'no_employees_for_filter' => 'Data karyawan tidak ditemukan untuk filter ini.',
    'clear_day_attendance_hint' => 'Kosongkan Scan In dan Scan Out untuk menghapus data hari ini.',
    'clear_scan_in' => 'Kosongkan scan in',
    'clear_scan_out' => 'Kosongkan scan out',
    'edit_attendance_aria' => 'Edit absensi :name pada :day',
    'prev' => 'Sebelumnya',
    'next_label' => 'Selanjutnya',
    'red_date' => 'Tanggal Merah',
    'red_date_api' => 'Tanggal Merah (API)',
    'sunday_label' => 'Minggu',
    'saturday_label' => 'Sabtu',
    'weekday_label' => 'Hari Kerja',
    'regular_workday' => 'Masuk Kerja Biasa',
    'holiday_leave' => 'Libur',
    'note_placeholder_holiday' => 'contoh: Masuk kerja meski libur nasional',
    'cache_cleared' => 'Cache berhasil dihapus',
    'export_excel' => 'Export Excel',
    'overtime_data' => 'Data Lembur',
    'sheet_report_data' => 'Data Laporan Sheet',
    'copy' => 'Salin',
    'no_available_data' => 'Tidak ada data yang tersedia',
    'showing_data_range' => 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
    'showing_zero_data' => 'Menampilkan 0 data',
    'datatable_search' => 'Cari:',
    'datatable_length' => 'Tampilkan _MENU_ data',
    'import_to_database' => 'Import ke Database',
    'read_preview_csv' => 'Baca & Preview CSV',
    'choose_csv_first' => 'Pilih file CSV terlebih dahulu',
    'reading_validating_csv' => 'Membaca dan memvalidasi CSV...',
    'reading_processing_csv' => 'Membaca dan memproses CSV...',
    'csv_only_allowed' => 'Hanya file CSV/TXT yang diizinkan.',
    'csv_process_failed' => 'Gagal memproses CSV.',
    'no_csv_data_read' => 'Tidak ada data yang berhasil dibaca.',
    'preview_csv_data' => 'Preview Data CSV',
    'create_new_employees_notice' => 'Ada karyawan yang belum terdaftar di database',
    'create_new_employees_help' => 'Aktifkan toggle di bawah agar karyawan baru otomatis dibuat sekaligus absensinya diimport.',
    'create_new_employees_hint' => 'Isi kolom posisi di CSV agar jabatan terisi. Default: Karyawan.',
    'create_new_employees_toggle' => 'Buat karyawan baru otomatis',
    'found_in_db' => 'Ada di DB',
    'not_registered' => 'Belum terdaftar',
    'records' => 'record',
    'scan_in_label' => 'Scan Masuk',
    'scan_out_label' => 'Scan Keluar',
    'import_result' => 'Hasil Import ke Database',
    'new_import' => 'Upload CSV Lain',
    'employees_created_short' => 'KRY DIBUAT',
    'inserted_short' => 'DITAMBAHKAN',
    'updated_short' => 'DIPERBARUI',
    'skipped_short' => 'DILEWATI',
    'not_found_short' => 'TIDAK DITEMUKAN',
    'not_found_in_database' => 'Tidak ditemukan di database',
    'new_employee' => 'Karyawan Baru',
    'added' => 'ditambah',
    'updated' => 'diperbarui',
    'skipped' => 'dilewati',
    'import_confirm_title' => 'Import data absensi ke database?',
    'registered_employees' => 'karyawan sudah terdaftar.',
    'new_employees_will_be_created' => 'karyawan baru akan DIBUAT otomatis.',
    'unregistered_employees_skipped' => 'karyawan belum terdaftar akan dilewati.',
    'duplicate_records_skipped' => 'Record duplikat dilewati otomatis.',
    'continue_question' => 'Lanjutkan?',
    'importing' => 'Mengimport...',
    'import_failed' => 'Import gagal.',
    'template_help' => 'Download template, isi data absensi, lalu upload kembali.',
    'accepted_csv_format' => 'Format CSV yang diterima:',
    'csv_columns_help' => 'Kolom',
    'csv_date_format_help' => 'Format tanggal',
    'csv_time_format_help' => 'Format waktu',
    'csv_optional_columns_help' => 'Kolom posisi dan scan_keluar boleh dikosongkan.',
    'csv_position_usage_help' => 'Kolom posisi digunakan untuk membuat karyawan baru jika belum ada di database.',
    'add_leave_permission' => 'Tambah Izin & Cuti',
    'employee_name_placeholder' => 'Ketik nama karyawan...',
    'outside_duty' => 'Dinas Luar',
    'note_placeholder_leave' => 'contoh: demam tinggi, perlu istirahat',
    'employee_date_required' => 'Karyawan dan tanggal wajib diisi!',
    'save_failed' => 'Gagal menyimpan',
    'delete_failed' => 'Gagal menghapus',
    'delete_this_data' => 'Hapus data ini?',
    'delete_leave_text' => 'Data izin/cuti akan dihapus permanen.',
    'try_again_error' => 'Terjadi kesalahan, coba lagi',
    'leave_saved' => 'Izin/cuti berhasil disimpan',
    'leave_deleted' => 'Izin/cuti berhasil dihapus',
    'invalid_time_format' => 'Format jam tidak valid. Gunakan HH:MM.',
    'attendance_saved' => 'Data kehadiran berhasil disimpan!',
    'missing_date_or_employee' => 'Tanggal atau karyawan belum dipilih',
    'data_removed' => 'Data dihapus',
    'saved' => 'Tersimpan',
    'language_switched' => 'Bahasa diubah ke :locale',
    'employee_created' => 'Data karyawan berhasil dibuat!',
    'employee_updated' => 'Data karyawan berhasil diperbarui!',
    'employee_deleted' => 'Data karyawan berhasil dihapus!',
    'schedule_created' => 'Jadwal berhasil dibuat!',
    'schedule_updated' => 'Jadwal berhasil diperbarui!',
    'schedule_deleted' => 'Jadwal berhasil dihapus!',
    'biometric_created' => 'Perangkat biometrik berhasil dibuat!',
    'biometric_connect_failed' => 'Gagal terhubung ke perangkat biometrik!',
    'biometric_updated' => 'Perangkat biometrik berhasil diperbarui!',
    'biometric_deleted' => 'Perangkat biometrik berhasil dihapus!',
    'biometric_employees_added' => 'Semua karyawan berhasil ditambahkan ke perangkat biometrik!',
    'attendance_queue_started' => 'Antrean absensi akan berjalan dalam satu menit!',
    'clear_device_attendance' => 'Bersihkan absensi perangkat',
    'active' => 'Aktif',
    'inactive' => 'Tidak Aktif',
    'get_attendance' => 'Ambil Absensi',
    'csv_empty_or_invalid' => 'File CSV kosong atau format tidak valid. Pastikan menggunakan template yang benar.',
    'csv_no_valid_rows' => 'Tidak ada baris data yang valid. Cek format tanggal (YYYY-MM-DD) dan nama kolom.',
    'csv_read_summary' => ':employees karyawan, :records record berhasil dibaca dari CSV.',
    'json_invalid' => 'Data JSON tidak valid.',
    'employee_not_found_db' => 'Karyawan tidak ditemukan di database.',
    'import_done_summary' => 'Import selesai: :inserted ditambahkan, :updated diperbarui, :skipped dilewati',
    'employees_created_suffix' => ', :count karyawan baru dibuat',
    'not_found_suffix' => ', :count tidak ditemukan di database',
    'position_values' => [
        'karyawan' => 'Karyawan',
    ],
];
