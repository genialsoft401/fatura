<?php
require_once '../app/views/layout_creation.php';

?>

<style>
    /* ===== HEADER ===== */

    .container {
        margin-top: 80px !important;
    }

    .container h2 {
        font-weight: 600;
    }

    .container .btn-primary {
        background: #007abd;
        border: none;
        border-radius: 999px;
        padding: 8px 18px;
        transition: all 0.2s ease;
    }

    .container .btn-primary:hover {
        background: #025d8e;
        transform: translateY(-1px);
    }

    /* ===== CARD ===== */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }

    /* HEADER DO CARD */
    .card-header {
        background: transparent !important;
        border-bottom: none;
        padding: 20px;
    }

    /* BOTÕES EXPORT */
    .card-header .btn {
        border-radius: 999px;
        font-weight: 500;
    }

    /* ===== TABELA ESTILO ===== */
    #contactTable {
        border-collapse: separate;
        border-spacing: 0 12px;
        width: 100%;
    }

    /* HEADER */
    #contactTable thead th {
        border: none;
        font-size: 12px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 16px;
    }

    /* ROW */
    #contactTable tbody tr {
        background: #fff !important;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    /* HOVER PRO */
    #contactTable tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    /* CELLS */
    #contactTable tbody td {
        border: none;
        padding: 18px 16px;
        vertical-align: middle;
        font-size: 0.95rem;
        background: #fff !important;
    }

    /* BORDAS ARREDONDADAS */
    #contactTable tbody td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
        background: #fff !important;
    }

    #contactTable tbody th {
        text-align: right !important;
    }

    #contactTable tbody td:last-child {
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
        text-align: right;
        padding-right: 24px;
    }

    /* ===== NOME (PRINCIPAL) ===== */
    #contactTable tbody td:first-child {
        font-weight: 600;
        color: #111;
    }

    /* SUBINFO */
    #contactTable tbody td small {
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
    .edit-contact .material-icons-round,
    .delete-contact .material-icons-round {
        transition: all 0.2s ease;
    }

    .edit-contact:hover .material-icons-round {
        color: #2563eb;
        transform: scale(1.2);
    }

    .delete-contact:hover .material-icons-round {
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

    /* ===== MOBILE ===== */
    @media (max-width: 768px) {

        #contactTable thead {
            display: none;
        }

        #contactTable,
        #contactTable tbody,
        #contactTable tr,
        #contactTable td {
            display: block;
            width: 100%;
        }

        #contactTable tbody tr {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 14px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        #contactTable tbody td {
            padding: 6px 0;
            text-align: left;
        }

        #contactTable tbody td:first-child {
            font-size: 1.1rem;
            font-weight: 600;
        }

        #contactTable tbody td[data-label="Ações"] {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 10px;
        }
    }

    #dt-length-0 {
        background: #fff !important;
        border-radius: 8px;
        padding: 5px;
        border: 0.5px solid #e5e7eb;
    }

    #dt-search {
        position: relative;
        margin-bottom: 15px;
    }

    /* ÍCONE */
    #dt-search-0 .search-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    /* INPUT */
    #dt-search-0 {
        padding-left: 35px !important;
        border-radius: 12px !important;
        border: 1px solid #e5e7eb !important;
    }

    /* FOCUS */
    #dt-search-0:focus {
        border-color: #16a34a !important;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15) !important;
    }

    /* ===== MODAL HEADER ===== */
    .modal-header {
        background: linear-gradient(135deg, #007abd, #00c6ff);
        color: #fff;
        border: none;
    }

    .modal-title {
        font-weight: 600;
    }

    .modal-content {
        background: #ffff !important;
    }

    /* ===== CARD CLEAN ===== */
    .card-clean {
        background: none !important;
        border-radius: 14px;
        padding: 18px;
        /* box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05); */
        transition: 0.2s;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #007abd !important;
    }

    .card-clean:hover {
        transform: translateY(-2px);
    }

    /* ===== CORES POR CARD ===== */
    .card-clean.primary {
        border-left-color: #6a5cff;
    }

    .card-clean.info {
        border-left-color: #00c6ff;
    }

    .card-clean.success {
        border-left-color: #10b981;
    }

    .card-clean.danger {
        border-left-color: #f43f5e;
    }

    /* ===== TITULO ===== */
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #111827;
        margin-left: -10px;
    }

    .section-title i {
        background: #eef2ff;
        color: #6a5cff;
        padding: 8px;
        border-radius: 10px;
        font-size: 18px;
    }

    /* ===== LABEL / VALUE ===== */
    .label {
        font-size: 12px;
        color: #6b7280;
    }

    .value {
        font-size: 14px;
        font-weight: 500;
        color: #111827;
        margin-bottom: 8px;
    }

    /* ===== PHONE BADGE ===== */
    .phone-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #eef6ff;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 13px;
        color: #1d4ed8;
        font-weight: 500;
    }

    .phone-badge i {
        font-size: 16px;
    }

    /* ===== RESPONSIVO ===== */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 10px;
        }
    }

    #downloadCSV {
        border-radius: 999px;
        font-weight: 500;
    }

    #downloadCSV:hover,
    #downloadExcel:hover,
    #downloadPDF:hover {
        transform: translateY(-1px);
    }

    #downloadCSV:hover {
        background: #16a34a;
        color: #fff;
    }

    #downloadExcel:hover {
        background: #2563eb;
        color: #fff;
    }

    #downloadPDF:hover {
        background: #dc2626;
        color: #fff;
    }

    .edit-contact:hover i {
        color: #2563eb;
        transform: scale(1.2);
    }

    #downloadExcel {
        border-radius: 999px;
        font-weight: 500;
    }

    #downloadCSV:hover,
    #downloadExcel:hover,
    #downloadPDF:hover {
        transform: translateY(-1px);
    }

    #downloadCSV:hover {
        background: #16a34a;
        color: #fff;
    }

    #downloadExcel:hover {
        background: #2563eb;
        color: #fff;
    }

    #downloadPDF:hover {
        background: #dc2626;
        color: #fff;
    }

    .edit-contact:hover i {
        color: #2563eb;
        transform: scale(1.2);
    }

    #downloadPDF {
        border-radius: 999px;
        font-weight: 500;
    }

    #downloadCSV,
    #downloadExcel,
    #downloadPDF,
    #newContact {
        transform: translateY(-1px);
        font-size: 0.9rem;
        padding: 5px 18px !important;
        height: 35px !important;
    }

    #downloadCSV:hover {
        background: #16a34a;
        color: #fff;
    }

    #downloadExcel:hover {
        background: #2563eb;
        color: #fff;
    }

    #downloadPDF:hover {
        background: #dc2626;
        color: #fff;
    }

    .edit-contact:hover i {
        color: #2563eb;
        transform: scale(1.2);
    }
</style>

<body>

    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Contato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row g-4">

                            <!-- ESQUERDA -->
                            <div class="col-lg-6">

                                <!-- Empresa -->
                                <div class="card-clean mb-3">
                                    <div class="section-title">
                                        <i class="bi bi-building"></i>
                                        Empresa
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Nome</div>
                                        <div class="value" id="contactName"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Tipo</div>
                                        <div class="value" id="contactType"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">NIF</div>
                                        <div class="value" id="contactContributor"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Email</div>
                                        <div class="value" id="contactEmail"></div>
                                    </div>
                                </div>

                                <!-- Contatos -->
                                <div class="card-clean mb-3">
                                    <div class="section-title">
                                        <i class="bi bi-telephone"></i>
                                        Contatos
                                    </div>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <div class="phone-badge">
                                            <i class="bi bi-telephone"></i>
                                            <span id="contactTelephone"></span>
                                        </div>

                                        <div class="phone-badge">
                                            <i class="bi bi-phone"></i>
                                            <span id="contactCellphone"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Localização -->
                                <div class="card-clean">
                                    <div class="section-title">
                                        <i class="bi bi-geo-alt"></i>
                                        Localização
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Endereço</div>
                                        <div class="value" id="contactAddress"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Cidade</div>
                                        <div class="value" id="contactLocation"></div>
                                    </div>
                                </div>

                            </div>

                            <!-- DIREITA -->
                            <div class="col-lg-6">

                                <!-- Contato principal -->
                                <div class="card-clean mb-3">
                                    <div class="section-title">
                                        <i class="bi bi-person"></i>
                                        Contato Principal
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Nome</div>
                                        <div class="value" id="contactPrefName"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Email</div>
                                        <div class="value" id="contactPrefEmail"></div>
                                    </div>
                                </div>

                                <!-- Configurações -->
                                <div class="card-clean">
                                    <div class="section-title">
                                        <i class="bi bi-gear"></i>
                                        Configurações
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Pagamento</div>
                                        <div class="value" id="contactPaymentMethod"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Moeda</div>
                                        <div class="value" id="contactCurrency"></div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="label">Atualizado</div>
                                        <div class="value" id="contactUpdatedAt"></div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <main>
        <div class="container mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><?= t('Meus Contatos') ?></h2>
                <div class="d-flex flex-wrap gap-2">
                    <button id="downloadCSV" class="btn btn-outline-success rounded-pill"><i class="material-icons-round align-middle fs-6">download</i> <?= t('Baixar em CSV') ?></button>
                    <button id="downloadExcel" class="btn btn-outline-primary rounded-pill"><i class="material-icons-round align-middle fs-6">download</i> <?= t('Baixar em Excel') ?></button>
                    <button id="downloadPDF" class="btn btn-outline-danger rounded-pill"><i class="material-icons-round align-middle fs-6">picture_as_pdf</i> <?= t('Baixar em PDF') ?></button>
                    <a href="register_contact.php" id="newContact" class="btn btn-primary"><i class="material-icons-round align-middle fs-6">add</i> <?= t('Novo Contato') ?></a>
                </div>
            </div>

            <div class="col-12">
                <div class="card-body">
                    <table id="contactTable" class="table nowrap w-100">
                        <thead>
                            <tr>
                                <th><?= t('Nome') ?></th>
                                <th><?= t('Telefone') ?></th>
                                <th><?= t('País') ?>/<?= t('Cidade') ?></th>
                                <th class="text-align-right"><?= t('Ações') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>


    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script src="contacts/contacts.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>