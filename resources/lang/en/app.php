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

    // Additional UI copy
    'app_title' => 'Attendance Management System',
    'admin_dashboard' => 'Admin Dashboard',
    'dark_mode' => 'Dark Mode',
    'login' => 'Log In',
    'welcome' => 'Welcome',
    'admin_login' => 'Sign in as Admin',
    'remember_me' => 'Remember Me',
    'password' => 'Password',
    'id' => 'ID',
    'all' => 'All',
    'all_data' => 'All',
    'add' => 'Add',
    'update' => 'Update',
    'schedules' => 'Schedules',
    'add_employee' => 'Add Employee',
    'edit_employee' => 'Edit Employee',
    'delete_employee' => 'Delete Employee',
    'add_schedule' => 'Add Schedule',
    'update_schedule' => 'Update Schedule',
    'delete_schedule' => 'Delete Schedule',
    'enter_employee_id' => 'Enter employee ID',
    'enter_employee_name' => 'Enter employee name',
    'enter_position' => 'Enter position',
    'select' => 'Select',
    'from' => 'from',
    'to_time' => 'to',
    'confirm_delete_item' => 'Are you sure you want to delete:',
    'data_not_saved_retry' => 'Data was not saved. Please try again.',
    'saving' => 'Saving...',
    'employee' => 'Employee',
    'employees_count' => 'employees',
    'days_count' => 'days',
    'work_days_count' => 'work days',
    'cell_edit_hint' => 'Click a cell to edit',
    'no_employees' => 'No employees',
    'no_employees_for_filter' => 'No employee data found for this filter.',
    'clear_day_attendance_hint' => 'Clear Scan In and Scan Out to delete this day record.',
    'clear_scan_in' => 'Clear scan in',
    'clear_scan_out' => 'Clear scan out',
    'edit_attendance_aria' => 'Edit :name attendance on :day',
    'prev' => 'Previous',
    'next_label' => 'Next',
    'red_date' => 'Red Date',
    'red_date_api' => 'National Holiday (API)',
    'sunday_label' => 'Sunday',
    'saturday_label' => 'Saturday',
    'weekday_label' => 'Weekday',
    'regular_workday' => 'Regular Workday',
    'holiday_leave' => 'Holiday',
    'note_placeholder_holiday' => 'example: Workday despite national holiday',
    'cache_cleared' => 'Cache cleared successfully',
    'export_excel' => 'Export Excel',
    'overtime_data' => 'Overtime Data',
    'sheet_report_data' => 'Sheet Report Data',
    'copy' => 'Copy',
    'no_available_data' => 'No data available',
    'showing_data_range' => 'Showing _START_ - _END_ of _TOTAL_ data',
    'showing_zero_data' => 'Showing 0 data',
    'datatable_search' => 'Search:',
    'datatable_length' => 'Show _MENU_ entries',
    'import_to_database' => 'Import to Database',
    'read_preview_csv' => 'Read & Preview CSV',
    'choose_csv_first' => 'Choose a CSV file first',
    'reading_validating_csv' => 'Reading and validating CSV...',
    'reading_processing_csv' => 'Reading and processing CSV...',
    'csv_only_allowed' => 'Only CSV/TXT files are allowed.',
    'csv_process_failed' => 'Failed to process CSV.',
    'no_csv_data_read' => 'No data could be read.',
    'preview_csv_data' => 'CSV Data Preview',
    'create_new_employees_notice' => 'Some employees are not registered in the database',
    'create_new_employees_help' => 'Enable the toggle below to automatically create new employees and import their attendance.',
    'create_new_employees_hint' => 'Fill the position column in the CSV so job positions are populated. Default: Employee.',
    'create_new_employees_toggle' => 'Create new employees automatically',
    'found_in_db' => 'Found in DB',
    'not_registered' => 'Not registered',
    'records' => 'records',
    'scan_in_label' => 'Scan In',
    'scan_out_label' => 'Scan Out',
    'import_result' => 'Database Import Result',
    'new_import' => 'Upload Another CSV',
    'employees_created_short' => 'EMP CREATED',
    'inserted_short' => 'INSERTED',
    'updated_short' => 'UPDATED',
    'skipped_short' => 'SKIPPED',
    'not_found_short' => 'NOT FOUND',
    'not_found_in_database' => 'Not found in database',
    'new_employee' => 'New Employee',
    'added' => 'added',
    'updated' => 'updated',
    'skipped' => 'skipped',
    'import_confirm_title' => 'Import attendance data to the database?',
    'registered_employees' => 'employees are already registered.',
    'new_employees_will_be_created' => 'new employees will be created automatically.',
    'unregistered_employees_skipped' => 'unregistered employees will be skipped.',
    'duplicate_records_skipped' => 'Duplicate records are skipped automatically.',
    'continue_question' => 'Continue?',
    'importing' => 'Importing...',
    'import_failed' => 'Import failed.',
    'template_help' => 'Download the template, fill attendance data, then upload it again.',
    'accepted_csv_format' => 'Accepted CSV format:',
    'csv_columns_help' => 'Columns',
    'csv_date_format_help' => 'Date format',
    'csv_time_format_help' => 'Time format',
    'csv_optional_columns_help' => 'The position and scan_keluar columns may be left blank.',
    'csv_position_usage_help' => 'The position column is used when creating new employees that do not exist in the database.',
    'add_leave_permission' => 'Add Leave & Permission',
    'employee_name_placeholder' => 'Type employee name...',
    'outside_duty' => 'Outside Duty',
    'note_placeholder_leave' => 'example: high fever, needs rest',
    'employee_date_required' => 'Employee and date are required!',
    'save_failed' => 'Failed to save',
    'delete_failed' => 'Failed to delete',
    'delete_this_data' => 'Delete this data?',
    'delete_leave_text' => 'Leave/permission data will be permanently deleted.',
    'try_again_error' => 'An error occurred, please try again',
    'leave_saved' => 'Leave/permission saved successfully',
    'leave_deleted' => 'Leave/permission deleted successfully',
    'invalid_time_format' => 'Invalid time format. Use HH:MM.',
    'attendance_saved' => 'Attendance data saved successfully!',
    'missing_date_or_employee' => 'Date or employee is missing',
    'data_removed' => 'Data removed',
    'saved' => 'Saved',
    'language_switched' => 'Language switched to :locale',
    'employee_created' => 'Employee record created successfully!',
    'employee_updated' => 'Employee record updated successfully!',
    'employee_deleted' => 'Employee record deleted successfully!',
    'schedule_created' => 'Schedule created successfully!',
    'schedule_updated' => 'Schedule updated successfully!',
    'schedule_deleted' => 'Schedule deleted successfully!',
    'biometric_created' => 'Biometric device created successfully!',
    'biometric_connect_failed' => 'Failed connecting to biometric device!',
    'biometric_updated' => 'Biometric device updated successfully!',
    'biometric_deleted' => 'Biometric device deleted successfully!',
    'biometric_employees_added' => 'All employees added to biometric device successfully!',
    'attendance_queue_started' => 'Attendance queue will run in a minute!',
    'clear_device_attendance' => 'Clear device attendance',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'get_attendance' => 'Get Attendance',
    'csv_empty_or_invalid' => 'The CSV file is empty or invalid. Make sure you use the correct template.',
    'csv_no_valid_rows' => 'No valid data rows were found. Check the date format (YYYY-MM-DD) and column names.',
    'csv_read_summary' => ':employees employees, :records records were read from the CSV.',
    'json_invalid' => 'Invalid JSON data.',
    'employee_not_found_db' => 'Employee not found in database.',
    'import_done_summary' => 'Import complete: :inserted inserted, :updated updated, :skipped skipped',
    'employees_created_suffix' => ', :count new employees created',
    'not_found_suffix' => ', :count not found in database',
];
