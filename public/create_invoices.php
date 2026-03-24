<?php
require_once '../app/views/layout_creation.php';
?>
<link rel="stylesheet" href="create_invoices/create_invoices.css">

<body>
    <main>
        <div class="container mt-5">
            <h2 class="mb-2 pt-3 font-logo d-flex justify-content-center"><?= t('Emissão de Fatura') ?></h2>
            <p class="text-muted text-center mb-4"><?= t('Preencha os detalhes para criar uma nova fatura.') ?></p>

            <div class="w-100">
                <form id="formFatura">
                    <input type="hidden" id="edit_invoice_id" name="edit_invoice_id" value="<?= isset($_GET['edit_id']) ? htmlspecialchars($_GET['edit_id']) : '' ?>">
                    <?php
                    $companies = getUserCompanies($_SESSION['user']['id']);
                    ?>
                    <div class="d-flex align-items-center">
                        <h4><?= t('Dados do Cliente') ?></h4>
                        <span style="cursor: pointer" id="toggle-contact-form" data-bs-title="Inserir novo Contato" data-bs-toggle="tooltip" data-bs-placement="top">
                            <span class="material-icons-round border rounded" id="spanIconCreateInvoices">person_add</span>
                        </span>

                    </div>
                    <hr>

                    <!-- Select2 para escolher contato existente -->
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
                                    <label for="contributor" class="form-label"><?= t('Registro') ?>:</label>
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
                                <label for="po_box" class="form-label"><?= t('Caixa Postal') ?>:</label>
                                <input type="text" class="form-control" id="po_box" name="po_box" required>
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

                    <hr class="mt-3">
                    <div class="d-flex align-items-center mt-4">
                        <h4>Detalhes do documento</h4>

                    </div>
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
                                <?= due_dateSelect(); ?>
                            </div>
                            <div class="mt-2">
                                <label for="reference" class="form-label"><?= t('V/Ref.') ?>:</label>
                                <input type="text" class="form-control" id="reference" name="reference">
                            </div>


                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="observation" class="form-label"><?= t('Observações') ?>:</label>
                            <textarea style="height: 11rem;" type="text" class="form-control" id="observation" name="observation" required></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="series" class="form-label"><?= t('Série') ?>:</label>
                            <select class="form-control" id="series" name="series" required>
                                <option selected><?=date('Y')?></option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
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


                    <div class="row mt-3">
                        <h4 class="mb-3"><?= t('Itens') ?> <span class="text-danger">*</span></h4>
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
                                <label for="item_select" class="form-label"><?= t('Selecionar Item') ?></label>
                                <select class="form-select select2 w-100" id="item_select">
                                    <option value=""><?= t('Carregando itens...') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row mt-3">
                        <h5 class="mb-3"><?= t('Resumo da Fatura') ?></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th><?= t('Taxa/IVA') ?></th>
                                            <th><?= t('Incidência') ?></th>
                                            <th><?= t('Valor') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tax_summary">
                                        <tr>
                                            <td><?= t('Isento (0%)') ?></td>
                                            <td id="tax_exempt_incidence">0,00</td>
                                            <td id="tax_exempt_value">0,00</td>
                                        </tr>
                                        <tr>
                                            <td><?= t('14% - Taxa14') ?></td>
                                            <td id="tax_14_incidence">0,00</td>
                                            <td id="tax_14_value">0,00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th colspan="2"><?= t('Sumário') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= t('Soma') ?>:</td>
                                            <td id="total_sum">0,00</td>
                                            <input type="text" hidden id="total_sumInput" name="total_sum">
                                        </tr>
                                        <tr>
                                            <td><?= t('Desconto') ?>:</td>
                                            <td id="total_discount">0,00</td>
                                            <input type="text" hidden id="total_discountInput" name="total_discount">

                                        </tr>
                                        <tr>
                                            <td><?= t('S/Imposto/IVA/Com Desc.') ?>:</td>
                                            <td id="subtotal_without_tax">0,00</td>
                                            <input type="text" hidden id="subtotal_without_taxInput" name="subtotal_without_tax">
                                        </tr>
                                        <tr>
                                            <td><?= t('Imposto/IVA') ?>:</td>
                                            <td id="total_tax">0,00</td>
                                            <input type="text" hidden id="total_taxInput" name="total_tax">
                                        </tr>
                                        <tr id="retention_sumary">
                                            <td><?= t('Retenção') ?><span class="percentageRetention"></span>:</td>
                                            <td id="retention_value">0,00</td>
                                            <input type="text" hidden id="retention_valueInput" name="retention_value">
                                        </tr>
                                        <tr>
                                            <td><strong><?= t('Total') ?>:</strong></td>
                                            <td><strong id="final_total">0,00</strong></td>
                                            <input type="text" hidden id="final_totalInput" name="final_total">
                                        </tr>

                                        <tr id="conversion_row" style="display: none;">
                                            <td><strong><?= t('Total em') ?> <span id="selected_currency"><?= $_SESSION['user']['iso_code'] ?></span>:</strong></td>
                                            <td><strong id="converted_total">0,00 <?= $_SESSION['user']['iso_code'] ?></strong></td>
                                            <input type="text" hidden id="converted_totalInput" name="converted_total">
                                        </tr>
                                        <tr id="exchange_rate_row" style="display: none;">
                                            <td colspan="2">
                                                <?= t('Câmbio') ?> <span id="currency_pair"></span>:
                                                <span id="exchange_rate">-</span>
                                            </td>
                                        </tr>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <button id="saveInvoiceBtn" type="button" class="btn btn-primary"><?= t('Salvar Fatura') ?></button>

            </div>
        </div>
        </form>
    </main>

    <script>
        let userCurrency = "<?= $_SESSION['user']['iso_code'] ?>";
        let currencySymbol = "<?= $_SESSION['user']['symbol'] ?>";
        let currencyPosition = "<?= $_SESSION['user']['position'] ?>";
    </script>


    <script src="create_invoices/create_invoices.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>