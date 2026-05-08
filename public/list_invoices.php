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

    /* ROW */
    #invoicesTable tbody tr {
        background: #fff !important;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        text-align: left !important;
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

    /* ===== NOME (PRINCIPAL) ===== */
    #invoicesTable tbody td:first-child {
        font-weight: 600;
        color: #111;
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

    #dt-length-0 {
        background: #fff !important;
        border-radius: 8px;
        padding: 5px;
        border: 0.5px solid #e5e7eb;
    }

    .dt-search {
        position: relative;
        margin-bottom: 15px;
    }

    .dt-search label {
        display: none !important;
    }

    /* ÍCONE */
    .dt-search-0 .search-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    /* INPUT */
    #dt-search-0 {
        padding-left: 15px !important;
        border-radius: 12px !important;
        border: 2px solid #ddd !important;
    }

    /* FOCUS */
    #dt-search-0:focus {
        border-color: #16a34a !important;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15) !important;
    }

    .modal-dialog {
        display: flex;
        align-items: center;
        min-height: 100vh;
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
            <h2 class="mb-4"><?= t('Emissão de Fatura') ?></h2>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dropdown">
                    <button
                        class="btn btn-success rounded-pill dropdown-toggle"
                        type="button"
                        id="exportDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Exportar Faturas
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

            <div class="table-responsive">
                <table id="invoicesTable" class="table-bx-standard table nowrap w-100">
                    <thead style="background: none !important;">
                        <tr>
                            <th><input type="checkbox" id="selectAll"> <?= t('Status') ?></th>
                            <th><?= t('Fatura') ?></th>
                            <th><?= t('Cliente') ?></th>
                            <th><?= t('Emissão') ?></th>
                            <th><?= t('Vencimento') ?></th>
                            <th><?= t('Moeda') ?></th>
                            <th><?= t('Valor Final') ?></th>
                            <th><?= t('Ações') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dados gerados via PHP -->
                    </tbody>
                </table>
            </div>
        </div>
        <div id="fatura-container" class="d-none"></div>
    </main>

    <script src="invoices/list_invoices.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>