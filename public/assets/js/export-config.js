/**
 * AMS Export Config
 * Reusable DataTables export buttons dengan styling konsisten
 * Usage: amsExportButtons('Nama Halaman')
 */
function amsExportButtons(title) {
    return [
        {
            extend: 'copy',
            text: '<i class="mdi mdi-content-copy mr-1"></i> Copy',
            className: 'btn btn-sm btn-secondary',
            title: title,
        },
        {
            extend: 'excel',
            text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',
            className: 'btn btn-sm btn-success',
            title: title,
            filename: title.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0,10),
            customize: function(xlsx) {
                var sheet = xlsx.xl.worksheets['sheet1.xml'];

                // Style header row (row 1)
                $('row:first c', sheet).attr('s', '2'); // bold + center

                // Warna header abu-abu profesional (#5a6268)
                var styles = xlsx.xl['styles.xml'];
                // Tambah fill abu-abu
                var fills = $('fills', styles);
                fills.append(
                    '<fill><patternFill patternType="solid"><fgColor rgb="FF5a6268"/><bgColor indexed="64"/></patternFill></fill>'
                );
                // Update font header jadi putih + bold
                var fonts = $('fonts', styles);
                fonts.append(
                    '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
                );
                var fontIdx = $('font', fonts).length - 1;
                var fillIdx = $('fill', fills).length - 1;

                // Apply ke semua cell header
                $('row:first c', sheet).each(function() {
                    $(this).attr('s', '');
                    var xf = $('cellXfs xf', styles).length;
                    $('cellXfs', styles).append(
                        '<xf numFmtId="0" fontId="' + fontIdx + '" fillId="' + fillIdx + '" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
                    );
                    $(this).attr('s', xf);
                });
            }
        },
        {
            extend: 'pdf',
            text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',
            className: 'btn btn-sm btn-danger',
            title: title,
            filename: title.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0,10),
            orientation: 'landscape',
            pageSize: 'A4',
            customize: function(doc) {
                // Header title styling
                doc.styles.title = {
                    color: '#343a40',
                    fontSize: 14,
                    bold: true,
                    alignment: 'center',
                    margin: [0, 0, 0, 8]
                };

                // Tanggal cetak di bawah judul
                doc.content.splice(1, 0, {
                    text: 'Dicetak: ' + new Date().toLocaleDateString('id-ID', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                    }),
                    style: { fontSize: 9, color: '#6c757d', alignment: 'center' },
                    margin: [0, 0, 0, 10]
                });

                // Table header: abu-abu profesional
                doc.content[doc.content.length - 1].layout = {
                    fillColor: function(rowIndex) {
                        if (rowIndex === 0) return '#5a6268'; // header abu-abu
                        return rowIndex % 2 === 0 ? '#f8f9fa' : null; // zebra stripe
                    },
                    hLineWidth: function() { return 0.5; },
                    vLineWidth: function() { return 0.5; },
                    hLineColor: function() { return '#dee2e6'; },
                    vLineColor: function() { return '#dee2e6'; },
                };

                // Header font putih
                var tableBody = doc.content[doc.content.length - 1].table.body;
                tableBody[0].forEach(function(cell) {
                    cell.color = '#ffffff';
                    cell.bold  = true;
                    cell.fontSize = 9;
                    cell.alignment = 'center';
                });

                // Body font size
                for (var i = 1; i < tableBody.length; i++) {
                    tableBody[i].forEach(function(cell) {
                        cell.fontSize = 8;
                    });
                }

                // Footer halaman
                doc.footer = function(currentPage, pageCount) {
                    return {
                        text: title + '  |  Halaman ' + currentPage + ' dari ' + pageCount,
                        alignment: 'center',
                        fontSize: 8,
                        color: '#6c757d',
                        margin: [0, 5, 0, 0]
                    };
                };
            }
        },
    ];
}