<?php
require_once '../app/views/layout_creation.php';
?>

<body>
<div class="layout-container d-flex">
    <main >
        <!-- Gráficos -->
        <div class="container mt-5">
    <div class="row g-4">
        <!-- Assinatura / Limites -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-1">Plano / Limites</h5>
                        <div id="subInfo" class="text-muted" style="font-size:0.95rem;">Carregando...</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" id="subActions">
                        <a class="btn btn-warning" id="btnRenewFromIndex" href="subscription.php">Renovar</a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row" id="subMetrics"></div>
                </div>
            </div>
        </div>

        <!-- Coluna dos Gráficos -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-center mb-3"><?= t('Faturas por Ano') ?></h5>
                    <canvas id="chartYearly" height="200"></canvas>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h5 class="card-title text-center mb-3"><?= t('Faturas por Contato') ?></h5>
                    <canvas id="chartByContact" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Coluna da Tabela -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3 text-center"><?= t('Últimas Faturas Emitidas') ?></h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="invoicesTable">
                            <thead class="table-light">
                                <tr>
                                    <th><?= t('Fatura') ?></th>
                                    <th><?= t('Emissão') ?></th>
                                    <th><?= t('Total') ?></th>
                                    <th><?= t('Status') ?></th>
                                    <th><?= t('Contato') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dados via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<style>
    .card-title {
    font-weight: 600;
    font-size: 1.2rem;
}

.card-body canvas {
    margin-top: 10px;
}

</style>
        <?php
        require_once '../app/views/footer.php';
        ?> 
    <script>
        // Função AJAX para buscar dados para gráficos e faturas
        function fetchInvoiceData() {
            $.ajax({
                url: 'index/ajax/get_invoice_data.php', // Caminho para o script PHP que retorna os dados
                type: 'GET',
                data: {
                    company_id: <?php echo $_SESSION['user']['company_id']; ?>
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    updateInvoiceTable(data.invoices); // Passa os dados das faturas
                    updateCharts(data.graphs); // Passa os dados para os gráficos
                },
                error: function() {
                    alert('Erro ao carregar dados.');
                }
            });
        }

        // Atualiza a tabela de faturas
        function updateInvoiceTable(invoices) {
            let tableBody = $('#invoicesTable tbody');
            tableBody.empty();

            invoices.forEach(function(invoice) {
                const issueDate = new Date(invoice.issue_date);
                const formattedDate = issueDate.toLocaleDateString('pt-BR');
                const invoiceUrl = `invoice.php?id=${invoice.issue_date.replace(/-/g, '')}/${invoice.company_id}/${invoice.id}`;

                let row = $(`
                    <tr style="cursor: pointer;" onclick="window.location.href='${invoiceUrl}'">
                        <td>${invoice.codigo}</td>
                        <td>${formattedDate}</td>
                        <td>${formatCurrency(invoice.final_total, invoice.symbol, invoice.position)}</td>
                        <td>${invoice.status_name}</td>
                        <td>${invoice.name}</td>
                    </tr>
                `);

                tableBody.append(row);
            });
        }


        // Atualiza os gráficos
        function updateCharts(graphs) {
            const chartYearly = new Chart(document.getElementById('chartYearly'), {
                type: 'bar',
                data: {
                    labels: graphs.yearly.labels,
                    datasets: [{
                        label: 'Faturas por Ano',
                        data: graphs.yearly.values,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
               options: {
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }

            });

            const chartByContact = new Chart(document.getElementById('chartByContact'), {
                type: 'pie',
                data: {
                    labels: graphs.contact.labels, // Agora está usando os nomes dos contatos
                    datasets: [{
                        label: 'Faturas por Contato',
                        data: graphs.contact.values,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
        }

        // Chama a função ao carregar a página
        $(document).ready(function() {
            fetchInvoiceData();

            // Assinatura / limites
            $.getJSON('assets/ajax/get_company_limits.php', { company_id: <?php echo (int)$_SESSION['user']['company_id']; ?> }, function(resp){
                if(!resp.success){
                    $('#subInfo').text('Não foi possível carregar os limites.');
                    return;
                }
                const expIso = resp.plan_expires_at || '';
                const exp = expIso ? new Date(expIso + 'T00:00:00').toLocaleDateString('pt-PT') : '-';
                const days = (resp.days_left === null) ? '-' : resp.days_left;
                $('#subInfo').text(`${resp.plan_name} • vence em ${exp} • ${days} dias restantes`);

                const limInv = resp.limits.invoice_limit_month === null ? '∞' : resp.limits.invoice_limit_month;
                const limUsers = resp.limits.user_limit === null ? '∞' : resp.limits.user_limit;

                const m = [
                    {label:'Faturas (mês)', val:`${resp.usage.invoice_count} / ${limInv}`},
                    {label:'Utilizadores', val:`${resp.usage.user_count} / ${limUsers}`},
                    {label:'RH (ativos)', val:`${resp.usage.employee_count} / ${resp.limits.rh_employee_limit ?? '-'}`},
                    {label:'Stock (itens)', val:`${resp.usage.stock_item_count} / ${resp.limits.stock_item_limit ?? '-'}`},
                ];
                $('#subMetrics').html(m.map(x => `
                    <div class="col-md-3 col-6 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-muted" style="font-size:0.85rem;">${x.label}</div>
                            <div style="font-weight:600;">${x.val}</div>
                        </div>
                    </div>
                `).join(''));

                $('#btnRenewFromIndex').attr('href', `subscription.php?company_id=${resp.company_id}`);
            });
        });


        let table = $("#invoicesTable").DataTable({
            destroy: true, 
            pageLength: 5, 
            lengthMenu: [
                [5, 10, 25, 50, -1],  
                ['5 linhas', '10 linhas', '25 linhas', '50 linhas', 'Tudo'] 
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json",
                search: "Pesquisar:",
                lengthMenu: "Mostrar _MENU_",
                zeroRecords: "Nenhuma fatura encontrada",
                paginate: {
                    first: "",
                    last: "Último",
                    next: "Próximo",
                    previous: ""
                }
            },
            order: [[1, 'desc']],
            responsive: true
        });

    </script>
</body>

</html>