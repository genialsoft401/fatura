<?php
require_once '../app/views/layout_creation.php';
?>
<link rel="stylesheet" href="create_invoices/create_invoices.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* ===== STEPPER ===== */
    .stepper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .step {
        flex: 1;
        text-align: center;
    }

    .step .circle {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .step.active .circle {
        background: #0d6efd;
        color: #fff;
    }

    .step.completed .circle {
        background: #198754;
        color: #fff;
    }

    /* ===== STEPS ===== */
    .form-step {
        display: none;
        opacity: 0;
        transform: translateX(30px);
        transition: all .3s;
    }

    .form-step.active {
        display: block;
        opacity: 1;
        transform: translateX(0);
    }

    /* ===== STEP UI ===== */

    .step-contentC {
        display: none;
        animation: fadeSlide .3s ease;
    }

    .step-contentC.active {
        display: block;
    }

    @keyframes fadeSlide {
        from {
            opacity: 0;
            transform: translateX(15px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Progress */
    .stepC-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        position: relative;
    }

    .stepC-progress::before {
        content: '';
        position: absolute;
        top: 50%;
        width: 100%;
        height: 3px;
        background: #e5e7eb;
        transform: translateY(-50%);
    }

    .stepC-bar {
        position: absolute;
        top: 50%;
        height: 3px;
        background: #007abd;
        width: 0%;
        transform: translateY(-50%);
        transition: .4s;
    }

    .stepC {
        z-index: 2;
        background: white;
        border: 2px solid #e5e7eb;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stepC.active {
        background: #007abd;
        color: white;
        border-color: #007abd;
    }

    /* Aside fixo */
    .sticky-aside {
        position: sticky;
        top: 20px;
    }

    /* Buttons */
    .stepC-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .btn-step {
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
    }

    .btn-next {
        background: #007abd;
        color: white;
    }

    .btn-prev {
        background: #e5e7eb;
    }
</style>

<main>
    <div class="container mt-5">
        <br><br>
        <h2 class="text-left mb-4"><?= t('Emissão de Proforma') ?></h2>
        <form id="formFatura" class="mt-5">

            <!-- STEPPER -->
            <div class="stepper">
                <div class="step active" data-step="1">
                    <div class="circle">1</div><span>Cliente</span>
                </div>
                <div class="step" data-step="2">
                    <div class="circle">2</div><span>Documento</span>
                </div>
                <div class="step" data-step="3">
                    <div class="circle">3</div><span>Produtos & Serviços</span>
                </div>
            </div>

            <div class="progress mb-4" style="height:6px;">
                <div id="progressBar" class="progress-bar" style="width:33%"></div>
            </div>

            <div class="row">

                <!-- ================= LEFT ================= -->
                <div class="col-lg-9">

                    <!-- ================= STEP 1 ================= -->
                    <div class="form-step active bg-white shadow-sm p-3 rounded" data-step="1">

                        <div class="d-flex gap-8 mb-3 justify-content-between">
                            <h4><?= t('Dados do Cliente') ?></h4>
                            <button type="btn btn-success btn-sm" style="cursor: pointer; width: 140px !important; padding: 5px 10px !important; border-radius: 20px; background: #198754 !important; font-size: 10pt !important; border: 0px solid #000;" class="bg-plan" data-bs-toggle="modal" data-bs-target="#createContactModal">
                                <span class="ml-4 text-white"><i class="bi-plus-circle"></i> Novo cliente</span>
                            </button>
                        </div>
                        <hr>

                        <div class="col-lg-12">
                            <div id="select-contact-container">
                                <label for="contact-select" class="form-label"><?= t('Escolha um Contato') ?>:</label>
                                <select id="contact-select" class="form-select">
                                    <option value=""><?= t('Selecione um contato...') ?></option>
                                    <!-- Os contatos existentes serão carregados via JS -->
                                </select>
                            </div>

                            <!-- Formulário de Cliente -->
                            <div id="contact-form" style="display: none;">
                                <input type="text" hidden readonly id="contact_id" name="contact_id">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div>
                                            <label for="name" class="form-label"><?= t('Nome') ?>:</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        <div class="mt-2">
                                            <label for="contributor" class="form-label"><?= t('NIF') ?>:</label>
                                            <input type="text" class="form-control" id="contributor" name="contributor" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label"><?= t('Endereço') ?>:</label>
                                        <textarea style="height: 7rem;" class="form-control" id="address" name="address" required></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label"><?= t('Email') ?>:</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="po_box" class="form-label"><?= t('Telefone') ?>:</label>
                                        <input type="tel" class="form-control" id="po_box" name="po_box" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label"><?= t('País') ?>:</label>
                                        <select class="form-select" id="country" name="country" required>
                                            <option value=""><?= t('Carregando países') ?>...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label"><?= t('Cidade') ?>: </label>
                                        <select class="form-select" id="city" name="city" required>
                                            <option value=""><?= t('Escolha um país primeiro') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ================= STEP 2 ================= -->
                    <div class="form-step bg-white shadow-sm p-3 rounded" data-step="2">

                        <h4><?= t('Detalhes do Documento') ?></h4>

                        <hr>
                        <div class="row">
                            <input hidden readonly value="<?= $_SESSION['user']['company_id'] ?>" id="company_id" name="company_id" required>
                            <input hidden readonly value="<?= $_SESSION['user']['id'] ?>" id="user_id" name="user_id" required>


                            <div class="col-md-6 mb-3">
                                <div>
                                    <label for="issue_date" class="form-label"><?= t('Data') ?>:</label>
                                    <input type="date" class="form-control" id="issue_date" name="issue_date" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="mt-2">
                                    <label for="due_date" class="form-label"><?= t('Vencimento') ?>:</label>
                                    <select class="form-select" name="due_date" id="due_date" required="" onchange="handleOtherOption()">
                                        <option value="0" selected>Pronto Pagamento</option>
                                        <option value="15">15 Dias</option>
                                        <option value="30">30 Dias</option>
                                        <option value="45">45 Dias</option>
                                        <option value="60">60 Dias</option>
                                        <option value="90">90 Dias</option>
                                        <option value="other">Outro</option>
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="observation" class="form-label"><?= t('Observações') ?>:</label>
                                <textarea style="height: 11rem;" type="text" class="form-control" id="observation" name="observation" required></textarea>
                            </div>
                        </div>
                        <div class="row" style="margin-top: -45px !important;">
                            <div class="col-md-6 mb-3">
                                <label for="series" class="form-label"><?= t('Série') ?>:</label>
                                <select class="form-control" id="series" name="series" required>
                                    <option selected><?= date('Y') ?></option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-6" style="margin-top: 40px;">
                                <label for="retention" class="form-label"><?= t('Retenção') ?>: (%)</label>
                                <input type="number" value="0.00" step="0.01" class="form-control" id="retention" name="retention">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label"><?= t('Moeda') ?>:</label>
                                <select class="form-select" id="currency" name="currency" required>
                                    <?= currencySelects(); ?>
                                </select>

                            </div>

                            <div class="col-md-6 mb-3" id="exchange_rate_container" style="display: none;">
                                <label for="manual_exchange_rate" class="form-label"><?= t('Câmbio') ?>:</label>
                                <input type="number" step="0.0001" class="form-control" id="manual_exchange_rate" name="manual_exchange_rate">
                            </div>

                        </div>

                    </div>

                    <!-- ================= STEP 3 ================= -->
                    <div class="form-step bg-white shadow-sm p-3 rounded" data-step="3">
                        <div class="d-flex gap-2 mb-2 justify-content-between">
                            <h4><?= t('Itens') ?></h4>
                            <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#itemModal">
                                <i class="bi bi-plus-circle"></i> Novo produto
                            </a>
                        </div>

                        <hr>
                        <div class="row mt-3">
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="items-container d-none" id="items_list">
                                        <div class="row fw-bold bg-light p-2 rounded">
                                            <div class="col-1 text-center"><?= t('Código') ?></div>
                                            <div class="col-4"><?= t('Descrição') ?></div>
                                            <div class="col-2 text-center"><?= t('Preço Unitário') ?></div>
                                            <div class="col-1 text-center"><?= t('Qtd.') ?></div>
                                            <div class="col-2 text-center"><?= t('Taxa/IVA') ?></div>
                                            <div class="col-1 text-center"><?= t('Desc.%') ?></div>
                                            <div class="col-1 text-center"></div>
                                        </div>
                                    </div>
                                    <label for="item_select" class="form-label mt-4 mb-2"><?= t('Selecionar Item') ?></label><br>
                                    <select class="form-select col-12 select2" style="width: 100% !important;" id="item_select">
                                        <option value=""><?= t('Carregando itens...') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ================= RIGHT (ASIDE RESUMO) ================= -->
                <div class="col-lg-3">

                    <div class="bg-white shadow-sm p-3 rounded sticky-aside">

                        <h5 class="mb-3"><?= t('Resumo da Fatura') ?></h5>

                        <!-- IVA -->
                        <table class="table table-bordered mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th><?= t('Taxa/IVA') ?></th>
                                    <th><?= t('Incidência') ?></th>
                                    <th><?= t('Valor') ?></th>
                                </tr>
                            </thead>
                            <tbody id="tax_summary">
                                <tr>
                                    <td>0%</td>
                                    <td id="tax_exempt_incidence">0,00</td>
                                    <td id="tax_exempt_value">0,00</td>
                                </tr>
                                <tr>
                                    <td>14%</td>
                                    <td id="tax_14_incidence">0,00</td>
                                    <td id="tax_14_value">0,00</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- SUMÁRIO -->
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><?= t('Soma') ?></td>
                                    <td id="total_sum">0,00</td>
                                </tr>
                                <tr>
                                    <td><?= t('Desconto') ?></td>
                                    <td id="total_discount">0,00</td>
                                </tr>
                                <tr>
                                    <td><?= t('Subtotal') ?></td>
                                    <td id="subtotal_without_tax">0,00</td>
                                </tr>
                                <tr>
                                    <td><?= t('IVA') ?></td>
                                    <td id="total_tax">0,00</td>
                                </tr>
                                <tr>
                                    <td><?= t('Retenção') ?></td>
                                    <td id="retention_value">0,00</td>
                                </tr>
                                <tr>
                                    <td><strong><?= t('Total') ?></strong></td>
                                    <td><strong id="final_total">0,00</strong></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>

            <!-- BOTÕES -->
            <div class="mt-4 d-flex justify-content-between">
                <button type="button" id="prevBtn" class="btn btn-light d-none">← Anterior</button>
                <button type="button" id="nextBtn" class="btn btn-primary">Próximo →</button>
                <button id="saveInvoiceBtn" type="button" class="btn btn-primary d-none">
                    <?= t('Salvar Rascunho') ?>
                </button>
            </div>

        </form>
    </div>

    <div class="modal fade" id="createContactModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">

            <div class="modal-content">
                <div class="modal-header modal-item">
                    <h5 class="modal-title" id="createContactModalLabel">Novo Contacto</h5>
                    <button type="button" class="btn-close" id="createModalCloseButton" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="contactForm">

                        <div class="row g-4">

                            <!-- LEFT (STEPS) -->
                            <div class="col-lg-9">

                                <!-- PROGRESS -->
                                <div class="stepC-progress">
                                    <div class="step-bar" id="stepCBar"></div>
                                    <div class="step active">1</div>
                                    <div class="step">2</div>
                                    <div class="step">3</div>
                                </div>

                                <!-- STEP 1 -->
                                <div class="step-contentC active">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                            <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                                <span class="material-icons-round me-2">business</span> <?= t('Dados da Empresa') ?>
                                            </h5>
                                        </div>
                                        <div class="card-body pt-3">
                                            <div class="row g-3">

                                                <div class="col-12">
                                                    <label for="name"
                                                        class="form-label text-muted small fw-bold required"><?= t('Nome da Empresa') ?></label>
                                                    <input type="text" class="form-control form-control-lg" id="name" name="name"
                                                        placeholder="Nome comercial completo" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="type"
                                                        class="form-label text-muted small fw-bold"><?= t('Tipo de Cliente') ?></label>
                                                    <select class="form-select" id="type" name="type">
                                                        <option value="<?= t('Normal') ?>"><?= t('Normal') ?></option>
                                                        <option value="<?= t('Autofaturação') ?>"><?= t('Autofaturação') ?></option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="contributor"
                                                        class="form-label text-muted small fw-bold required"><?= t('NIF / Registro') ?></label>
                                                    <input type="text" class="form-control" id="contributor" name="contributor"
                                                        placeholder="Ex: 000000000" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="country"
                                                        class="form-label text-muted small fw-bold"><?= t('País') ?></label>
                                                    <select class="form-select" id="country" name="country">
                                                        <option value=""><?= t('Carregando países') ?>...</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="city"
                                                        class="form-label text-muted small fw-bold"><?= t('Cidade') ?></label>
                                                    <select class="form-select" id="city" name="city">
                                                        <option value=""><?= t('Escolha um país primeiro') ?></option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label for="address"
                                                        class="form-label text-muted small fw-bold required"><?= t('Endereço Completo') ?></label>
                                                    <textarea class="form-control" id="address" name="address" rows="2"
                                                        placeholder="Rua, Número, Bairro..." required></textarea>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2 -->
                                <div class="step-contentC">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                            <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                                <span class="material-icons-round me-2">place</span>
                                                <?= t('Contatos') ?>
                                            </h5>
                                        </div>
                                        <div class="card-body pt-3">
                                            <div class="row g-3">

                                                <div class="col-md-4 d-none">
                                                    <label for="po_box"
                                                        class="form-label text-muted small fw-bold"><?= t('Caixa Postal') ?></label>
                                                    <input type="text" class="form-control" id="po_box" name="po_box">
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="email"
                                                        class="form-label text-muted small fw-bold"><?= t('Email Corporativo') ?></label>
                                                    <input type="email" class="form-control" id="email" name="email"
                                                        placeholder="contato@empresa.com">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="website"
                                                        class="form-label text-muted small fw-bold"><?= t('Website') ?></label>
                                                    <input type="text" class="form-control" id="website" name="website"
                                                        placeholder="www.empresa.com">
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="telephone"
                                                        class="form-label text-muted small fw-bold required"><?= t('Telefone Fixo') ?></label>
                                                    <div class="input-group flex-nowrap">
                                                        <select class="form-select countryPhone tel" name="telephone_ddi"
                                                            id="telephone_ddi" style="max-width: 90px;">
                                                            <option selected value="">DDI</option>
                                                        </select>
                                                        <input type="text" name="telephone" class="form-control telnumber"
                                                            id="telephone" placeholder="000 000 000" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="cellphone"
                                                        class="form-label text-muted small fw-bold"><?= t('Telemóvel') ?></label>
                                                    <div class="input-group flex-nowrap">
                                                        <select class="form-select countryPhone tel" name="cellphone_ddi"
                                                            id="cellphone_ddi" style="max-width: 90px;">
                                                            <option selected value="">DDI</option>
                                                        </select>
                                                        <input type="text" name="cellphone" id="cellphone"
                                                            class="form-control telnumber" placeholder="900 000 000">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 d-none">
                                                    <label for="fax"
                                                        class="form-label text-muted small fw-bold"><?= t('Fax') ?></label>
                                                    <input type="text" class="form-control" id="fax" name="fax">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- STEP 3 -->
                                <div class="step-contentC">
                                    <!-- Card: Contato Preferencial -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                            <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                                <span class="material-icons-round me-2">person</span> <?= t('Pessoa de Contato') ?>
                                            </h5>
                                        </div>
                                        <div class="card-body pt-3">
                                            <div class="mb-3">
                                                <label for="pref_name"
                                                    class="form-label text-muted small fw-bold"><?= t('Nome do Responsável') ?></label>
                                                <input type="text" class="form-control" id="pref_name" name="pref_name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="pref_email"
                                                    class="form-label text-muted small fw-bold"><?= t('Email Pessoal') ?></label>
                                                <input type="email" class="form-control" id="pref_email" name="pref_email">
                                            </div>
                                            <div class="mb-3">
                                                <label
                                                    class="form-label text-muted small fw-bold"><?= t('Telefone Direto') ?></label>
                                                <div class="input-group flex-nowrap">
                                                    <select class="form-select countryPhone tel" name="pref_telephone_ddi"
                                                        id="pref_telephone_ddi" style="max-width: 80px;">
                                                        <option selected value="">DDI</option>
                                                    </select>
                                                    <input type="text" class="form-control telnumber" name="pref_telephone"
                                                        id="pref_telephone">
                                                </div>
                                            </div>
                                            <div class="mb-3 d-none">
                                                <label
                                                    class="form-label text-muted small fw-bold"><?= t('Telemóvel Direto') ?></label>
                                                <div class="input-group flex-nowrap">
                                                    <select class="form-select countryPhone tel" name="pref_cellphone_ddi"
                                                        id="pref_cellphone_ddi" style="max-width: 80px;">
                                                        <option selected value="">DDI</option>
                                                    </select>
                                                    <input type="text" class="form-control telnumber" name="pref_cellphone"
                                                        id="pref_cellphone">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botões de Ação -->
                                <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                                    <div id="divsaveContact" class="w-100 w-md-auto">
                                        <!-- O botão de salvar será injetado aqui pelo JavaScript -->
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT (ASIDE ORIGINAL) -->
                            <!-- Coluna Direita: Preferências e Contato Pessoal -->
                            <div class="col-lg-3">
                                <!-- Card: Configurações -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                        <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                            <span class="bi bi-gear"></span>&nbsp;<?= t('Configurações') ?>
                                        </h5>
                                    </div>
                                    <div class="card-body pt-3" id="side-card">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" checked type="checkbox" id="usar_definicoes">
                                            <label class="form-check-label small"
                                                for="usar_definicoes"><?= t('Usar definições padrão da conta') ?></label>
                                        </div>
                                        <div class="mb-3">
                                            <label for="num_copias"
                                                class="form-label text-muted small fw-bold"><?= t('Nº de cópias') ?></label>
                                            <?= numberCopysSelect(); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label for="due_date"
                                                class="form-label text-muted small fw-bold"><?= t('Vencimento Padrão') ?></label>
                                            <?= due_dateSelect(); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label for="language"
                                                class="form-label text-muted small fw-bold required"><?= t('Idioma') ?></label>
                                            <select class="form-select" name="language" required id="language">
                                                <option value="BR">Português Brasileiro</option>
                                                <option value="AO" selected>Português Angolano</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="payment_method"
                                                class="form-label text-muted small fw-bold required"><?= t('Método de Pagamento') ?></label>
                                            <select class="form-select" name="payment_method" required id="payment_method">
                                                <?= getPaymentMethods(); ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="currency"
                                                class="form-label text-muted small fw-bold required"><?= t('Moeda Preferencial') ?></label>
                                            <select class="form-select" name="currency" required id="currency">
                                                <?= currencySelects(); ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="observations"
                                                class="form-label text-muted small fw-bold"><?= t('Observações Internas') ?></label>
                                            <textarea class="form-control" id="observations" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <!-- ACTIONS -->
                    <div class="stepC-actions d-flex">
                        <button type="button" class="btn-step btn-prev" id="prevCBtn">Voltar</button>
                        <button type="button" class="btn-step btn-next" id="nextCBtn">Próximo</button>
                        <button type="button" class="btn-step btn-next d-none" id="saveChangesContact">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    let currentStep = 1;

    const steps = document.querySelectorAll(".form-step");
    const nextBtn = document.getElementById("nextBtn");
    const saveBtn = document.getElementById("saveInvoiceBtn");
    const prevBtn = document.getElementById("prevBtn");
    const form = document.getElementById("formFatura");

    /* ===== SHOW STEP ===== */
    function showStep(step) {
        steps.forEach(s => s.classList.remove("active"));

        const current = document.querySelector(`.form-step[data-step="${step}"]`);
        if (current) current.classList.add("active");

        prevBtn.classList.toggle("d-none", step === 1);
        step === steps.length ? saveBtn.classList.remove("d-none") : nextBtn.classList.remove("d-none");
        step < steps.length ? saveBtn.classList.add("d-none") : nextBtn.classList.add("d-none");

        updateStepper();
        updateProgress();
    }

    /* ===== STEPPER ===== */
    function updateStepper() {
        document.querySelectorAll(".step").forEach(el => {
            const s = parseInt(el.dataset.step);
            el.classList.remove("active", "completed");

            if (s === currentStep) el.classList.add("active");
            else if (s < currentStep) el.classList.add("completed");
        });
    }

    /* ===== PROGRESS ===== */
    function updateProgress() {
        document.getElementById("progressBar").style.width =
            (currentStep / steps.length) * 100 + "%";
    }

    /* ===== VALIDATION ===== */
    function validateStep() {
        const current = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        const inputs = current.querySelectorAll("[required]");

        let valid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid");
                valid = false;
            } else {
                input.classList.remove("is-invalid");
            }
        });

        return valid;
    }

    /* ===== LOCAL STORAGE ===== */
    function saveDraft() {
        const data = new FormData(form);
        const obj = {};

        data.forEach((v, k) => obj[k] = v);

        localStorage.setItem("invoiceDraft", JSON.stringify(obj));
    }

    function loadDraft() {
        const draft = JSON.parse(localStorage.getItem("invoiceDraft"));
        if (!draft) return;

        Object.keys(draft).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) field.value = draft[key];
        });
    }

    /* ===== CALC ===== */
    function calc() {
        let total = 0;

        document.querySelectorAll(".item-row").forEach(row => {
            const p = parseFloat(row.querySelector(".price").value) || 0;
            const q = parseFloat(row.querySelector(".qty").value) || 0;

            total += p * q;
        });

        document.getElementById("final_total").innerText = total.toFixed(2);
    }

    /* ===== EVENTS ===== */
    nextBtn.addEventListener("click", () => {
        if (!validateStep()) return;

        if (currentStep < steps.length) {
            currentStep++;
            showStep(currentStep);
        } else {
            form.submit();
            localStorage.removeItem("invoiceDraft");
        }
    });

    prevBtn.addEventListener("click", () => {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    //     document.getElementById("addItem").addEventListener("click", () => {
    //         const div = document.createElement("div");
    //         div.className = "item-row d-flex gap-2 mb-2";
    //         div.innerHTML = `
    //     <input class="form-control price" placeholder="Preço">
    //     <input class="form-control qty" placeholder="Qtd">
    // `;
    //         document.getElementById("items").appendChild(div);
    //     });

    /* AUTO SAVE + CALC */
    form.addEventListener("input", () => {
        saveDraft();
        calc();
    });

    /* INIT */
    document.addEventListener("DOMContentLoaded", () => {
        loadDraft();
        showStep(1);
    });

    let userCurrency = "<?= $_SESSION['user']['iso_code'] ?>";
    let currencySymbol = "<?= $_SESSION['user']['symbol'] ?>";
    let currencyPosition = "<?= $_SESSION['user']['position'] ?>";

    const formClient = () => {
        let current = 0;

        const steps = document.querySelectorAll(".step-contentC");
        const indicators = document.querySelectorAll(".stepC");
        const bar = document.getElementById("stepCBar");
        const prevBtn = document.getElementById("prevCBtn");
        const nextBtn = document.getElementById("nextCBtn");
        const saveBtn = document.getElementById("saveChangesContact");




        function update() {
            const lastStep = steps.length - 1;

            // STEP ACTIVE
            steps.forEach((s, i) =>
                s.classList.toggle("active", i === current)
            );

            // INDICATORS
            indicators.forEach((s, i) =>
                s.classList.toggle("active", i <= current)
            );

            // PROGRESS BAR
            bar.style.width = (current / lastStep) * 100 + "%";

            // BUTTONS
            prevBtn.style.display = current === 0 ? "none" : "block";

            // FINAL STEP
            const isLast = current === lastStep;

            if (isLast) {
                nextBtn.classList.add("d-none");
                saveBtn.classList.remove("d-none");
            } else {
                nextBtn.classList.remove("d-none");
                saveBtn.classList.add("d-none");
                nextBtn.innerText = "Próximo";
            }
        }


        nextBtn.onclick = () => {
            if (current < steps.length - 1) {
                current++;
                update();
            } else {
                contactForm.submit();
            }
        };

        prevBtn.onclick = () => {
            current--;
            update();
        };

        update();

    }

    formClient()
</script>


<script src="create_invoices/create_invoices.js"></script>

<?php require_once '../app/views/footer.php'; ?>