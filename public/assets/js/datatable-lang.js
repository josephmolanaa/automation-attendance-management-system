/**
 * DataTables Language Configuration
 * Automatically detects current locale and applies appropriate language
 */

(function() {
    // Get current locale from Laravel (set by SetLocale middleware)
    const locale = document.documentElement.lang || 'id';
    
    // Language configurations
    const languages = {
        en: {
            emptyTable: "No data available",
            info: "Showing _START_ to _END_ of _TOTAL_ results",
            infoEmpty: "Showing 0 results",
            infoFiltered: "(filtered from _MAX_ total results)",
            lengthMenu: "Show _MENU_ entries",
            loadingRecords: "Loading...",
            processing: "Processing...",
            search: "Search:",
            zeroRecords: "No matching records found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            aria: {
                sortAscending: ": activate to sort column ascending",
                sortDescending: ": activate to sort column descending"
            }
        },
        id: {
            emptyTable: "Tidak ada data tersedia",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ hasil",
            infoEmpty: "Menampilkan 0 hasil",
            infoFiltered: "(disaring dari _MAX_ total hasil)",
            lengthMenu: "Tampilkan _MENU_ data",
            loadingRecords: "Memuat...",
            processing: "Memproses...",
            search: "Cari:",
            zeroRecords: "Tidak ada data yang cocok",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            aria: {
                sortAscending: ": aktifkan untuk mengurutkan kolom naik",
                sortDescending: ": aktifkan untuk mengurutkan kolom turun"
            }
        }
    };
    
    // Export to global scope
    window.DataTableLang = languages[locale] || languages.id;
    
    // Set default DataTables language
    if (typeof $.fn.dataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            language: window.DataTableLang
        });
    }
})();
