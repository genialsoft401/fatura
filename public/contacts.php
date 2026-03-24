<?php
require_once '../app/views/layout_creation.php';

?>

<style>
    a {
        color: #000;
    }
    .contact-detail-label {
        font-weight: 600;
        color: #343a40; /* Darker text for labels */
        margin-right: 0.5rem;
    }
    .contact-detail-value {
        color: #6c757d; /* Slightly lighter text for values */
        word-break: break-word; /* Fix para textos longos que quebram o layout */
    }
    .phone-card {
        display: inline-flex;
        align-items: center;
        background-color: #e9ecef; /* Light gray background */
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.3rem 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        color: #495057;
    }
    .phone-card i {
        font-size: 1rem;
        margin-right: 0.5rem;
        color: var(--bs-primary); /* Using a CSS variable for consistency */
    }
    .card-header .card-title {
        font-size: 1.1rem;
        font-weight: bold;
    }

    /* ===== DESKTOP: tabela limpa, sem cara de Excel ===== */
    #contactTable {
        border-collapse: separate;
        border-spacing: 0 10px;
        width: 100%;
    }

    #contactTable thead th {
        border: none;
        font-weight: 600;
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
    }

    #contactTable tbody tr {
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    #contactTable tbody tr:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    #contactTable tbody td {
        border: none;
        padding: 18px 16px;
        vertical-align: middle;
        font-size: 0.95rem;
    }

    #contactTable tbody td:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    #contactTable tbody td:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        text-align: right;
        padding-right: 24px;
    }

    .table-icon {
        font-size: 1.2rem;
        vertical-align: middle;
        color: #adb5bd;
        transition: color 0.2s;
    }

    .table-icon:hover {
        color: var(--bs-primary);
    }

    .edit-contact .material-icons-round,
    .delete-contact .material-icons-round {
        transition: transform 0.2s;
    }

    .edit-contact:hover .material-icons-round,
    .delete-contact:hover .material-icons-round {
        transform: scale(1.15);
    }

    /* ===== MOBILE: Transforma tabela em cards ===== */
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
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background-color: #fff;
            transition: box-shadow 0.2s ease;
            cursor: pointer;
        }

        #contactTable tbody tr:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: none; /* Remove o transform do desktop */
        }

        #contactTable tbody td {
            padding: 0.25rem 0;
            border: none;
            text-align: left;
        }

        #contactTable tbody td::before {
            content: none !important;
        }

        /* Hierarquia Visual */
        #contactTable tbody td[data-label="Nome"] {
            font-weight: 600;
            font-size: 1.1rem;
            color: #212529;
            padding-bottom: 0.5rem;
        }

        #contactTable tbody td[data-label="Email"],
        #contactTable tbody td[data-label="Telefone"] {
            font-size: 0.95rem;
            color: #495057;
            padding-top: 0.25rem;
        }
        
        #contactTable tbody td[data-label="Email"] .material-icons-round,
        #contactTable tbody td[data-label="Telefone"] .material-icons-round {
            color: #6c757d;
        }

        #contactTable tbody td[data-label="País/Cidade"] {
            font-size: 0.85rem;
            color: #6c757d;
            padding-top: 0.5rem;
        }

        /* Ícones e Links */
        #contactTable .table-icon,
        #contactTable .material-icons-round {
            font-size: 1.5rem;
        }

        #contactTable .view-contact {
            display: none;
        }

        /* Ações no rodapé do card */
        #contactTable tbody td[data-label="Ações"] {
            border-top: 1px solid #f0f0f0;
            margin-top: 1rem;
            padding-top: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        #contactTable .btn-link {
            padding: 0.25rem;
            line-height: 1;
        }
        
        #contactTable .edit-contact .material-icons-round {
            color: var(--bs-primary);
        }
        
        #contactTable .delete-contact .material-icons-round {
            color: var(--bs-danger);
        }
    }

</style>

<body>
    <!-- Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel"><?=t('Detalhes do Contato')?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row g-4">
                            <!-- Coluna Esquerda -->
                            <div class="col-lg-6">
                                <!-- Card: Dados da Empresa/Contato Principal -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-primary text-white d-flex align-items-center">
                                        <i class="material-icons-round me-2">business</i>
                                        <h5 class="card-title mb-0"><?=t('Dados da Empresa/Contato')?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Nome')?>:</span> <span class="contact-detail-value" id="contactName"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Tipo')?>:</span> <span class="contact-detail-value" id="contactType"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('NIF / Registro')?>:</span> <span class="contact-detail-value" id="contactContributor"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Email')?>:</span> <span class="contact-detail-value" id="contactEmail"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Website')?>:</span> <span class="contact-detail-value" id="contactWebsite"></span></p>
                                    </div>
                                </div>

                                <!-- Card: Contatos Telefônicos -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-info text-white d-flex align-items-center">
                                        <i class="material-icons-round me-2">phone</i>
                                        <h5 class="card-title mb-0"><?=t('Contatos Telefônicos')?></h5>
                                    </div>
                                    <div class="card-body d-flex flex-wrap gap-2">
                                        <a id="contactTelephoneLink" href="#" class="phone-card text-decoration-none" style="display: none;"><i class="material-icons-round">call</i><span id="contactTelephone"></span></a>
                                        <a id="contactCellphoneLink" href="#" class="phone-card text-decoration-none" style="display: none;"><i class="material-icons-round">smartphone</i><span id="contactCellphone"></span></a>
                                    </div>
                                </div>

                                <!-- Card: Localização -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-secondary text-white d-flex align-items-center">
                                        <i class="material-icons-round me-2">location_on</i>
                                        <h5 class="card-title mb-0"><?=t('Localização')?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Endereço')?>:</span> <span class="contact-detail-value" id="contactAddress"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('País')?>/<?=t('Cidade')?>:</span> <span class="contact-detail-value" id="contactLocation"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Caixa Postal')?>:</span> <span class="contact-detail-value" id="contactPoBox"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Fax')?>:</span> <span class="contact-detail-value" id="contactFax"></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Coluna Direita -->
                            <div class="col-lg-6">
                                <!-- Card: Pessoa de Contato Preferencial -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-success text-white d-flex align-items-center">
                                        <i class="material-icons-round me-2">person</i>
                                        <h5 class="card-title mb-0"><?=t('Pessoa de Contato Preferencial')?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Nome')?>:</span> <span class="contact-detail-value" id="contactPrefName"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Email')?>:</span> <span class="contact-detail-value" id="contactPrefEmail"></span></p>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <a id="contactPrefTelephoneLink" href="#" class="phone-card text-decoration-none" style="display: none;"><i class="material-icons-round">call</i><span id="contactPrefTelephone"></span></a>
                                            <a id="contactPrefCellphoneLink" href="#" class="phone-card text-decoration-none" style="display: none;"><i class="material-icons-round">smartphone</i><span id="contactPrefCellphone"></span></a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card: Configurações e Observações -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-warning text-white d-flex align-items-center">
                                        <i class="material-icons-round me-2">settings</i>
                                        <h5 class="card-title mb-0"><?=t('Configurações e Observações')?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Nº de Cópias')?>:</span> <span class="contact-detail-value" id="contactNumberCopys"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Vencimento')?>:</span> <span class="contact-detail-value" id="contactdue_date"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Idioma')?>:</span> <span class="contact-detail-value" id="contactLanguage"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Método de Pagamento')?>:</span> <span class="contact-detail-value" id="contactPaymentMethod"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Moeda')?>:</span> <span class="contact-detail-value" id="contactCurrency"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Última Atualização')?>:</span> <span class="contact-detail-value" id="contactUpdatedAt"></span></p>
                                        <p class="mb-1"><span class="contact-detail-label"><?=t('Observações')?>:</span> <span class="contact-detail-value" id="contactObservations"></span></p>
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
                <a href="register_contact.php" class="btn btn-primary"><i class="material-icons-round align-middle fs-6">add</i> <?=t('Novo Contato')?></a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center">
                      
                    <div class="d-flex flex-wrap gap-2">
                        <button id="downloadCSV" class="btn btn-sm btn-outline-success"><i class="material-icons-round align-middle fs-6">download</i> <?=t('Baixar em CSV')?></button>
                        <button id="downloadExcel" class="btn btn-sm btn-outline-primary"><i class="material-icons-round align-middle fs-6">download</i> <?=t('Baixar em Excel')?></button>
                        <button id="downloadPDF" class="btn btn-sm btn-outline-danger"><i class="material-icons-round align-middle fs-6">picture_as_pdf</i> <?=t('Baixar em PDF')?></button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="contactTable"  class="dataTables-BXpert table table-striped table-hover display nowrap w-100">
                        <thead>
                            <tr>
                                <th><?=t('Nome')?></th>
                                <th><?=t('Email')?></th>
                                <th><?=t('Telefone')?></th>
                                <th><?=t('País')?>/<?=t('Cidade')?></th>
                                <th><?=t('Ações')?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="contacts/contacts.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>