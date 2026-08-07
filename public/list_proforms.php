<?php
require_once '../app/views/layout_creation.php';
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>


<style>
    /* ===== TABELA ESTILO ===== */
    #invoicesTable {
        border-collapse: separate;
        border-spacing: 0 12px;
        width: 100%;
    }

    /* HEADER */
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

    /* ROW */
    #invoicesTable tbody tr {
        background: #fff !important;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        text-align: left !important;
        cursor: pointer;
    }

    /* HOVER PRO */
    #invoicesTable tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    /* CELLS */
    #invoicesTable tbody td {
        border: none;
        padding: 18px 16px;
        vertical-align: middle;
        font-size: 0.95rem;
        background: #fff !important;
        text-align: left;
    }

    /* BORDAS ARREDONDADAS */
    #invoicesTable tbody td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
        background: #fff !important;
        font-weight: 600;
        color: #111;
    }

    #invoicesTable tbody th {
        text-align: left !important;
    }

    #invoicesTable tbody td:last-child {
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
        text-align: right;
        padding-right: 24px;
    }

    /* SUBINFO */
    #invoicesTable tbody td small {
        display: block;
        color: #6b7280;
    }

    /* ===== ÍCONES ===== */
    .table-icon {
        font-size: 1.2rem;
        color: #9ca3af;
        transition: all 0.2s;
    }

    .table-icon:hover {
        color: #111;
        transform: scale(1.1);
    }

    /* ===== AÇÕES ===== */
    .edit-contact i,
    .delete-contact i {
        transition: all 0.2s ease;
    }

    .edit-contact:hover i {
        color: #2563eb;
        transform: scale(1.2);
    }

    .delete-contact:hover i {
        color: #dc2626;
        transform: scale(1.2);
    }

    /* ===== MODAL MAIS PREMIUM ===== */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    /* ===== CARDS DO MODAL ===== */
    .card-header.bg-primary,
    .card-header.bg-info,
    .card-header.bg-secondary,
    .card-header.bg-success,
    .card-header.bg-warning {
        border-radius: 12px 12px 0 0;
        font-size: 0.95rem;
    }

    /* ===== PHONE CARDS ===== */
    .phone-card {
        display: inline-flex;
        align-items: center;
        background: #f3f4f6;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .phone-card:hover {
        background: #e5e7eb;
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
            <h2 class="mb-4"><?= t('Minhas Proformas') ?></h2>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dropdown">
                    <button
                        class="btn btn-success rounded-pill dropdown-toggle"
                        type="button"
                        id="exportDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Exportar Proformas
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('excel')">
                                Exportar para Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportFile('csv')">
                                Exportar para CSV
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- FILTROS -->
            <div class="card border-0 mb-4" style="background: none !important;">
                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cliente</label>
                            <input type="text" id="filterClient" class="form-control" placeholder="Pesquisar cliente...">
                        </div>

                        <div class="col-md-2">
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
                    <thead style="background: none !important;">
                        <tr>
                            <th><input type="checkbox" id="selectAll"> <?= t('Status') ?></th>
                            <th data-key="codigo"><?= t('Proforma') ?> <i class="sort-icon bi bi-arrow-down text-muted ms-1"></i></th>
                            <th data-key="cliente"><?= t('Cliente') ?> <i class="sort-icon bi bi-arrow-down-up text-muted ms-1"></i></th>
                            <th data-key="issue_date"><?= t('Emissão') ?> <i class="sort-icon bi bi-arrow-down-up text-muted ms-1"></i></th>
                            <th data-key="due_date"><?= t('Vencimento') ?> <i class="sort-icon bi bi-arrow-down-up text-muted ms-1"></i></th>
                            <th><?= t('Moeda') ?></th>
                            <th data-key="final_total"><?= t('Valor Final') ?> <i class="sort-icon bi bi-arrow-down-up text-muted ms-1"></i></th>
                            <th class="text-end"><?= t('Ações') ?></th>
                        </tr>
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
        <div id="proforma-container" class="d-none"></div>
    </main>

    <script src="proform/list_proforms.js?v=0.1"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>