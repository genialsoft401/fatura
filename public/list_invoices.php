<?php
require_once '../app/views/layout_creation.php';
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

<style>
    #invoicesTable {
        border-collapse: separate;
        border-spacing: 0 12px;
        width: 100%;
    }

    #invoicesTable thead th {
        border: none;
        font-size: 12px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 16px;
    }

    #invoicesTable thead th[data-key] {
        cursor: pointer;
        user-select: none;
    }

    #invoicesTable tbody tr {
        background: #fff !important;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        text-align: left !important;
        cursor: pointer;
    }

    #invoicesTable tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    #invoicesTable tbody td {
        border: none;
        padding: 18px 16px;
        vertical-align: middle;
        font-size: 0.95rem;
        background: #fff !important;
        text-align: left;
    }

    #invoicesTable tbody td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
        background: #fff !important;
        font-weight: 600;
        color: #111;
    }

    #invoicesTable tbody td:last-child {
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
        text-align: right;
        padding-right: 24px;
    }

    .table-icon {
        font-size: 1.2rem;
        color: #9ca3af;
        transition: all 0.2s;
    }

    .table-icon:hover {
        color: #111;
        transform: scale(1.1);
    }

    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        min-height: 100vh;
    }

    #filterClient,
    #filterStatus,
    #filterStartDate,
    #filterEndDate {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        min-height: 44px;
    }

    #filterClient:focus,
    #filterStatus:focus,
    #filterStartDate:focus,
    #filterEndDate:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
    }

    /* Submenu do botão Exportar */
    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu>.dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -6px;
        margin-left: 2px;
        display: none;
    }

    .dropdown-submenu:hover>.dropdown-menu,
    .dropdown-submenu.show>.dropdown-menu {
        display: block;
    }

    .dropdown-submenu>.dropdown-item.dropdown-toggle::after {
        content: "";
        border-top: 0.3em solid transparent;
        border-bottom: 0.3em solid transparent;
        border-left: 0.3em solid;
        float: right;
        margin-top: 7px;
    }

    @media (max-width: 767px) {
        .dropdown-submenu>.dropdown-menu {
            position: static;
            left: 0;
            box-shadow: none;
            border: none;
            padding-left: 12px;
        }
    }
</style>

<body>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Confirmar exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Tem certeza que deseja eliminar?
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>

            </div>
        </div>
    </div>

    <div id="preloader" style="display: none; position: fixed; bottom: 20px; right: 20px; width: 200px; padding: 10px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.2);">
        <p style="margin: 0; font-size: 14px;">Gerando arquivo...</p>
        <div style="height: 5px; width: 100%; background: #ddd; border-radius: 3px; overflow: hidden; margin-top: 5px;">
            <div id="progressBar" style="height: 100%; width: 0%; background: #007bff;"></div>
        </div>
    </div>

    <main>
        <div class="container mt-5">
            <h2 class="mb-4"><?= t('Minhas Faturas') ?></h2>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dropdown">
                    <button
                        class="btn btn-success rounded-pill dropdown-toggle"
                        type="button"
                        id="exportDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Exportar
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">

                        <!-- Fatura (com sub-submenu de formatos) -->
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="javascript:void(0)">Fatura</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('invoices', 'excel')">Excel</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('invoices', 'pdf')">PDF</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('invoices', 'csv')">CSV</a></li>
                            </ul>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('credit_notes')">
                                Nota de Crédito
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('receipts')">
                                Recibos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('debit_notes')">
                                Nota de Débito
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('sales_report')">
                                Relatório de Vendas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('invoices_paid')">
                                Faturas Pagas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('invoices_pending')">
                                Faturas Pendentes
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- FILTROS -->
            <div class="card border-0 mb-4" style="background: none !important;">
                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipo de Documento</label>
                            <select id="filterDocType" class="form-select">
                                <option value="invoices">Faturas</option>
                                <option value="receipts">Recibos</option>
                                <option value="credit_notes">Notas de Crédito</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" id="filterClientLabel">Cliente</label>
                            <input type="text" id="filterClient" class="form-control" placeholder="Pesquisar cliente...">
                        </div>

                        <div class="col-md-2" id="filterStatusWrapper">
                            <label class="form-label fw-semibold">Status</label>
                            <select id="filterStatus" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendente">Pendente</option>
                                <option value="parcial">Parcial</option>
                                <option value="pago">Pago</option>
                                <option value="rascunho">Rascunho</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Data Inicial</label>
                            <input type="date" id="filterStartDate" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Data Final</label>
                            <input type="date" id="filterEndDate" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <button id="btnClearFilters" class="btn btn-secondary w-100">Limpar</button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="invoicesTable" class="table-bx-standard table nowrap w-100">
                    <thead id="invoicesTableHead" style="background: none !important;">
                        <!-- Cabeçalho é gerado dinamicamente via JS conforme o Tipo de Documento selecionado -->
                    </thead>
                    <tbody>
                        <!-- Dados gerados via JS -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Mostrar</span>
                    <select id="pageSizeSelect" class="form-select form-select-sm" style="width:auto;">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-muted small" id="tableInfo"></span>
                </div>

                <nav>
                    <ul class="pagination pagination-sm mb-0" id="tablePagination"></ul>
                </nav>
            </div>
        </div>

        <div id="fatura-container" class="d-none"></div>
    </main>

    <script>
        // Mantém o submenu aberto ao clicar (útil em ecrãs sem hover / mobile)
        document.querySelectorAll('.dropdown-submenu > .dropdown-toggle').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const parentLi = this.closest('.dropdown-submenu');
                document.querySelectorAll('.dropdown-submenu.show').forEach(function(openLi) {
                    if (openLi !== parentLi) openLi.classList.remove('show');
                });
                parentLi.classList.toggle('show');
            });
        });
    </script>

    <script src="invoices/list_invoices.js?v=1.e"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>