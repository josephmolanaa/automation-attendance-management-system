<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */
    'nav' => [
        'home' => 'Home',
        'dashboard' => 'Dashboard',
        'employees' => 'Employees',
        'attendance' => 'Attendance',
        'schedule' => 'Schedule',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'profile' => 'Profile',
        'lock_screen' => 'Lock Screen',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu
    |--------------------------------------------------------------------------
    */
    'sidebar' => [
        'main' => 'Main',
        'dashboard' => 'Dashboard',
        'employees_menu' => 'Employees',
        'employees_list' => 'Employees List',
        'management' => 'Management',
        'schedule_menu' => 'Schedule',
        'attendance_sheet' => 'Attendance Sheet',
        'sheet_report_menu' => 'Sheet Report',
        'attendance_logs' => 'Attendance Logs',
        'late_time_menu' => 'Late Time',
        'leave_permission_menu' => 'Leave & Permission',
        'overtime_menu' => 'Overtime',
        'tools' => 'Tools',
        'biometric_device' => 'Biometric Device',
        'upload_scanlog' => 'Upload Scanlog',
    ],

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb
    |--------------------------------------------------------------------------
    */
    'breadcrumb' => [
        'home' => 'Home',
        'attendance' => 'Attendance',
        'employees' => 'Employees',
        'schedule' => 'Schedule',
        'reports' => 'Reports',
    ],

    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */
    'form' => [
        'label' => [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'position' => 'Position',
            'employee_id' => 'Employee ID',
            'shift' => 'Shift',
            'status' => 'Status',
            'date' => 'Date',
            'time_in' => 'Time In',
            'time_out' => 'Time Out',
            'duration' => 'Duration',
            'reason' => 'Reason',
            'note' => 'Note',
            'required' => 'required',
        ],
        'placeholder' => [
            'search' => 'Search...',
            'select' => 'Select...',
            'enter_name' => 'Enter name',
            'enter_email' => 'Enter email',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */
    'button' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'add' => 'Add',
        'add_new' => 'Add New',
        'search' => 'Search',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'close' => 'Close',
        'submit' => 'Submit',
        'export' => 'Export',
        'import' => 'Import',
        'download' => 'Download',
        'upload' => 'Upload',
        'back' => 'Back',
        'next' => 'Next',
        'previous' => 'Previous',
        'view_all' => 'View All',
        'view_details' => 'View Details',
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */
    'message' => [
        'success' => [
            'title' => 'Success!',
            'save' => 'Data saved successfully',
            'delete' => 'Data deleted successfully',
            'update' => 'Data updated successfully',
            'import' => 'Data imported successfully',
        ],
        'error' => [
            'title' => 'Error!',
            'general' => 'An error occurred',
            'not_found' => 'Data not found',
            'validation' => 'Validation error occurred',
        ],
        'warning' => [
            'title' => 'Warning!',
        ],
        'info' => [
            'title' => 'Information',
            'no_data' => 'No data available',
            'loading' => 'Loading...',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */
    'alert' => [
        'confirm_delete' => 'Are you sure you want to delete this data?',
        'confirm_save' => 'Are you sure you want to save changes?',
        'button' => [
            'yes_delete' => 'Yes, Delete',
            'yes' => 'Yes',
            'no' => 'No',
            'cancel' => 'Cancel',
            'ok' => 'OK',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DataTables
    |--------------------------------------------------------------------------
    */
    'datatable' => [
        'show' => 'Show',
        'entries' => 'entries',
        'search' => 'Search',
        'showing' => 'Showing',
        'to' => 'to',
        'of' => 'of',
        'results' => 'results',
        'no_data_available' => 'No data available',
        'loading' => 'Loading...',
        'copy' => 'Copy',
        'excel' => 'Excel',
        'pdf' => 'PDF',
        'print' => 'Print',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dates - Month Names
    |--------------------------------------------------------------------------
    */
    'month' => [
        'january' => 'January',
        'february' => 'February',
        'march' => 'March',
        'april' => 'April',
        'may' => 'May',
        'june' => 'June',
        'july' => 'July',
        'august' => 'August',
        'september' => 'September',
        'october' => 'October',
        'november' => 'November',
        'december' => 'December',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dates - Day Names
    |--------------------------------------------------------------------------
    */
    'day' => [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dates - Relative Time
    |--------------------------------------------------------------------------
    */
    'date' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'last_week' => 'Last Week',
        'from_date' => 'From Date',
        'to_date' => 'To Date',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Keys (Backward Compatibility)
    |--------------------------------------------------------------------------
    */
    // Navigation & Menu
    'home' => 'Home',
    'attendance' => 'Attendance',
    'employees' => 'Employees',
    'schedule' => 'Schedule',
    'reports' => 'Reports',
    'settings' => 'Settings',
    'logout' => 'Logout',
    'profile' => 'Profile',
    'lock_screen' => 'Lock Screen',
    
    // Attendance
    'attendance_list' => 'Attendance List',
    'late_time' => 'Late Time',
    'overtime' => 'Overtime',
    'leave_permission' => 'Leave & Permission',
    'holiday_manager' => 'Holiday Manager',
    'manual_check' => 'Manual Check',
    'sheet_report' => 'Sheet Report',
    'import_csv' => 'Import CSV',
    
    // Page Titles
    'manual_attendance' => 'Manual Attendance',
    'import_attendance_data' => 'Import Attendance Data',
    
    // Table Headers
    'employee_id' => 'Employee ID',
    'name' => 'Name',
    'position' => 'Position',
    'shift' => 'Shift',
    'status' => 'Status',
    'date' => 'Date',
    'time_in' => 'Time In',
    'time_out' => 'Time Out',
    'duration' => 'Duration',
    'late_duration' => 'Late Duration',
    'overtime_duration' => 'Overtime Duration',
    'reason' => 'Reason',
    'note' => 'Note',
    'action' => 'Action',
    'email' => 'Email',
    'member_since' => 'Member Since',
    'actions' => 'Actions',
    'job_position' => 'Job Position',
    'day' => 'Day',
    'week' => 'Week',
    'scan_1' => 'Scan 1',
    'scan_2' => 'Scan 2',
    'scan_3' => 'Scan 3',
    'normal' => 'Normal',
    'double' => 'Double',
    'izin_cuti' => 'Leave/Permission',
    'schedule_time_out' => 'Schedule Time Out',
    'actual_time_out' => 'Actual Time Out',
    
    // Upload Scanlog
    'upload_csv' => 'Upload CSV',
    'review_preview' => 'Review Preview',
    'confirm_import' => 'Confirm Import',
    'upload_csv_file' => 'Upload CSV Attendance File',
    'need_template' => 'Need template CSV?',
    'download_template' => 'Download Template CSV',
    'drag_drop_csv' => 'Drag & Drop CSV file here',
    'or_click_to_select' => 'or click to select file. Format: CSV / TXT, max 5MB',
    'csv_format_detected' => 'CSV format detected',
    'back_preview_csv' => 'Back & Preview CSV',
    'edit_csv_again' => 'Edit CSV file again',
    
    // Status
    'on_time' => 'On Time',
    'late' => 'Late',
    'no_scan_in' => 'No Scan In',
    'no_scan_out' => 'No Scan Out',
    'present' => 'Present',
    'absent' => 'Absent',
    'leave' => 'Leave',
    'permission' => 'Permission',
    'sick' => 'Sick',
    
    // Filters
    'filter' => 'Filter',
    'month' => 'Month',
    'year' => 'Year',
    'all_months' => 'All Months',
    'all_years' => 'All Years',
    'reset' => 'Reset',
    'search' => 'Search',
    'show' => 'Show',
    'entries' => 'entries',
    
    // Months (flat keys for backward compatibility)
    'january' => 'January',
    'february' => 'February',
    'march' => 'March',
    'april' => 'April',
    'may' => 'May',
    'june' => 'June',
    'july' => 'July',
    'august' => 'August',
    'september' => 'September',
    'october' => 'October',
    'november' => 'November',
    'december' => 'December',
    
    // Days (flat keys for backward compatibility)
    'sunday' => 'Sunday',
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
    
    // DataTables (flat keys for backward compatibility)
    'showing' => 'Showing',
    'to' => 'to',
    'of' => 'of',
    'results' => 'results',
    'no_data_available' => 'No data available',
    'loading' => 'Loading...',
    'copy' => 'Copy',
    'excel' => 'Excel',
    'pdf' => 'PDF',
    'print' => 'Print',
    
    // Common Filters
    'all_employees' => 'All Employees',
    'select_employee' => 'Select Employee',
    'select_month' => 'Select Month',
    'select_year' => 'Select Year',
    
    // Buttons (flat keys for backward compatibility)
    'add_new' => 'Add New',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'close' => 'Close',
    'submit' => 'Submit',
    'export' => 'Export',
    'import' => 'Import',
    'download' => 'Download',
    'upload' => 'Upload',
    'back' => 'Back',
    'next' => 'Next',
    'previous' => 'Previous',
    
    // Messages (flat keys for backward compatibility)
    'success' => 'Success!',
    'error' => 'Error!',
    'warning' => 'Warning!',
    'info' => 'Information',
    'confirm_delete' => 'Are you sure you want to delete this data?',
    'data_saved' => 'Data saved successfully',
    'data_deleted' => 'Data deleted successfully',
    'data_updated' => 'Data updated successfully',
    'no_data' => 'No data available',
    'from_date' => 'From Date',
    'to_date' => 'To Date',
    
    // Holiday Manager
    'holiday' => 'Holiday',
    'weekday' => 'Weekday',
    'national_holiday' => 'National Holiday (API)',
    'admin_override' => 'Admin Override',
    'day_type' => 'Day Type',
    'specific_shift' => 'Specific Shift',
    'optional' => 'optional',
    'auto_detect' => 'Auto detect from scan time',
    'save_override' => 'Save Override',
    'delete_override' => 'Delete Override',
    
    // Dashboard
    'good_morning' => 'Good Morning',
    'good_afternoon' => 'Good Afternoon',
    'good_evening' => 'Good Evening',
    'total_employees' => 'Total Employees',
    'on_time_percentage' => 'On Time',
    'present_today' => 'Present Today',
    'late_today' => 'Late Today',
    'view_all' => 'View All',
    'today' => 'Today',
    'view_attendance' => 'View Attendance',
    'view_details' => 'View Details',
    'monthly_report' => 'Monthly Report',
    'recent_attendance' => 'Recent Attendance',
    'no_attendance_data' => 'No attendance data yet',
    'scan_in' => 'Scan In',
    'scan_out' => 'Scan Out',
    
    // Sidebar Menu (flat keys for backward compatibility)
    'main' => 'Main',
    'dashboard' => 'Dashboard',
    'employees_menu' => 'Employees',
    'employees_list' => 'Employees List',
    'management' => 'Management',
    'schedule_menu' => 'Schedule',
    'attendance_sheet' => 'Attendance Sheet',
    'sheet_report_menu' => 'Sheet Report',
    'attendance_logs' => 'Attendance Logs',
    'late_time_menu' => 'Late Time',
    'leave_permission_menu' => 'Leave & Permission',
    'overtime_menu' => 'Overtime',
    'tools' => 'Tools',
    'biometric_device' => 'Biometric Device',
    'upload_scanlog' => 'Upload Scanlog',
    
    // Breadcrumb (flat keys for backward compatibility)
    'breadcrumb_home' => 'Home',
    'breadcrumb_attendance' => 'Attendance',
    'breadcrumb_employees' => 'Employees',
    'breadcrumb_schedule' => 'Schedule',
    'breadcrumb_reports' => 'Reports',
];
