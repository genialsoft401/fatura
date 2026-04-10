<?php
require_once '../app/views/layout_creation.php';

?>

<style>
    /* ===== BASE ===== */
    body {
        background: #f9fafb;
    }

    /* LINKS */
    a {
        color: #111;
        text-decoration: none;
    }

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

    /* ===== TABELA ESTILO SAAS ===== */
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
        background: #fff;
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
    }

    /* BORDAS ARREDONDADAS */
    #contactTable tbody td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
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

    /* modal */
     .modal-content {
        border-radius: 20px;
        border: none;
    }

    .modal-header {
        border-bottom: none;
        padding: 20px 25px;
    }

    .modal-title {
        font-weight: 600;
        font-size: 20px;
    }

    .card-clean {
        background: #f8f9fb;
        border-radius: 16px;
        padding: 20px;
        border: none;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
    }

    .label {
        font-size: 13px;
        color: #888;
    }

    .value {
        font-size: 15px;
        font-weight: 500;
        color: #222;
    }

    .phone-badge {
        background: #eef1f6;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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
                                    <i class="material-icons-round">business</i>
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
                                    <i class="material-icons-round">call</i>
                                    Contatos
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="phone-badge">
                                        <i class="material-icons-round">call</i>
                                        <span id="contactTelephone"></span>
                                    </div>

                                    <div class="phone-badge">
                                        <i class="material-icons-round">smartphone</i>
                                        <span id="contactCellphone"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Localização -->
                            <div class="card-clean">
                                <div class="section-title">
                                    <i class="material-icons-round">location_on</i>
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
                                    <i class="material-icons-round">person</i>
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
                                    <i class="material-icons-round">settings</i>
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
                    <a href="register_contact.php" class="btn btn-primary"><i class="material-icons-round align-middle fs-6">add</i> <?= t('Novo Contato') ?></a>
                </div>
            </div>

            <div class="col-12">
                <div class="card-body">
                    <table id="contactTable" class="dataTables-BXpert table display nowrap w-100">
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