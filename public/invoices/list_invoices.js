$(document).ready(function() {
    // Configuração do DataTable
    $('#invoicesTable').DataTable({
        ajax: {
            url: 'invoices/ajax/fetch_invoices.php', 
            dataSrc: ''
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json",
            lengthMenu: "Mostrar  _MENU_",
        },
        columns: [
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                    <div class="d-flex flex-wrap justify-content-evenly">
                        <input 
                            name="${row.id}" 
                            data-id="${row.id}" 
                            type="checkbox" 
                            value="${row.id}">
                        <div 
                            class="px-2 icon-statusFatura" 
                            style="background-color:${row.color};color:${row.text_color};font-weight:900; cursor: default;" 
                            data-bs-title="${row.status_invoice}" 
                            data-bs-toggle="tooltip" data-bs-placement="top">
                            ${row.status_invoice.trim().charAt(0)}
                        </div>
                    </div>
                    `;
                }
            },
            { data: 'codigo' },
            { data: 'cliente' },
            { 
                data: 'issue_date',
                render: function(data) {
                    const date = new Date(data);
                    return date.toLocaleDateString('pt-BR', { timeZone: 'UTC' });
                }
            },
            { 
                data: null,
                render:  function(data, type, row) {
                    const hoje = new Date();
                    const date = new Date(row.due_date);
                    return `<span style="color: ${date < hoje ? 'red' : 'inherit'};" ${date < hoje && row.status_invoice == "Rascunho" ? ' data-bs-title="Aguardando Finalizar Fatura"  data-bs-toggle="tooltip"  ' : ''}>${date.toLocaleDateString('pt-BR', { timeZone: 'UTC' })}</span>`; 
                }
            },
            { data: 'currency' },
            { 
                data: null,
                render: function(data, type, row) {
                    return formatCurrency(row.final_total, row.symbol, row.position);
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    let btns = `<button class="btn btn-secondary btn-sm" onclick="window.location.href='invoice.php?id=${data.issue_date.replace('-', '').replace('-', '')}/${data.company_id}/${data.id}'; event.stopPropagation();">Detalhes</button>`;
                    if (row.status_invoice === 'Rascunho') {
                        btns += ` <button class="btn btn-primary btn-sm ms-1" onclick="window.location.href='create_invoices.php?edit_id=${row.id}'; event.stopPropagation();">Editar</button>`;
                    }
                    return btns;
                }
            }
        ],
        paging: true,
        searching: true,
        ordering: true,
        responsive: true,
        destroy: true, 
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, 100, -1],  
            ['10 linhas', '25 linhas', '50 linhas', '100 linhas', 'Tudo'] 
        ],
        order: [[1, 'desc']],
        rowCallback: function(row, data) {
            $(row).css('cursor', 'pointer');
            $(row).on('click', function(event) {
                const cellIndex = $(event.target).closest('td').index();
        
                if (cellIndex === 0) {
                    const checkbox = $(row).find('input[type="checkbox"]'); 
                    checkbox.prop('checked', !checkbox.prop('checked')); 
                } else if (cellIndex !== 7) {
                    window.location.href = `invoice.php?id=${data.issue_date.replace('-', '').replace('-', '')}/${data.company_id}/${data.id}`;
                }
            });
        }
        
        
    });
 
    $('#exportCsv').on('click', function() {
        window.location.href = 'export_csv.php';
    });
 
     
});
 
function downloadPDF(invoiceId) {
    window.location.href = `download_pdf.php?id=${invoiceId}`;
}
 
  $('#selectAll').on('click', function() {
    const isChecked = $(this).is(':checked'); 
    $('#invoicesTable tbody input[type="checkbox"]').prop('checked', isChecked);
});
 
$('#invoicesTable tbody').on('change', 'input[type="checkbox"]', function() {
    const totalCheckboxes = $('#invoicesTable tbody input[type="checkbox"]').length;
    const checkedCheckboxes = $('#invoicesTable tbody input[type="checkbox"]:checked').length;
 
    $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
});

function exportFile(format) {
    const preloader = document.getElementById("preloader");
    const progressBar = document.getElementById("progressBar");

    // Exibe o preloader
    preloader.style.display = "block";
    progressBar.style.width = "0%";

    // Inicia o monitoramento do progresso
    let checkProgress = setInterval(() => {
        fetch(`invoices/ajax/faturas_export.php?status=1`)
            .then(res => res.json())
            .then(data => {
                progressBar.style.width = data.progress + "%";

                if (data.progress >= 100) {
                    clearInterval(checkProgress);
                    setTimeout(() => {
                        preloader.style.display = "none";
                    }, 500);
                }
            });
    }, 1000);

    // Aguarda um pequeno tempo para garantir que o progresso começou
    setTimeout(() => {
        window.location.href = `invoices/ajax/faturas_export.php?formato=${format}`;
    }, 2000);
}
