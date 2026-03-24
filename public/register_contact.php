<?php
require_once '../app/views/layout_creation.php';

?>

<style>
.select2-container .select2-selection--single .select2-selection__rendered {
    padding-right: 10px;
}
</style>

<body>
    <main class="bg-light min-vh-100 py-4">
        <div class="container">
            <form id="contactForm" class="">
                <!-- Cabeçalho da Página -->
                <div class="mt-4 pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
                    <div>
                        <h3 id="infoEdit" class="fw-bold text-dark mt-4  mb-1"><?= t("Adicionar Nova Empresa"); ?></h3>
                        <p class="text-muted mb-0">
                            <?= t("Preencha os dados abaixo para cadastrar um novo cliente ou fornecedor."); ?></p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <?php if ($isLocal): // Renderiza o botão apenas se estiver em localhost ?>
                        <button type="button" id="btnFillContact" class="btn btn-outline-warning btn-sm">
                            <i class="material-icons-round align-middle fs-6">science</i> Preencher (Teste)
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <input type="text" readonly hidden name="company_id" value="<?= $_SESSION['user']['company_id'] ?>">
                <input type="text" readonly hidden name="<?= isset($_GET['id']) ? 'updated_at' : 'created_at'; ?>"
                    value="<?= $dateAtual ?>">

                <div class="row g-4">
                    <!-- Coluna Esquerda: Dados Principais -->
                    <div class="col-lg-8">
                        <!-- Card: Dados da Empresa -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                    <span class="material-icons-round me-2">business</span> <?= t('Dados da Empresa') ?>
                                </h5>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="type"
                                            class="form-label text-muted small fw-bold required"><?= t('Tipo de Cliente') ?></label>
                                        <select class="form-select" id="type" name="type" required>
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
                                    <div class="col-12">
                                        <label for="name"
                                            class="form-label text-muted small fw-bold required"><?= t('Nome da Empresa') ?></label>
                                        <input type="text" class="form-control form-control-lg" id="name" name="name"
                                            placeholder="Nome comercial completo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email"
                                            class="form-label text-muted small fw-bold required"><?= t('Email Corporativo') ?></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="contato@empresa.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website"
                                            class="form-label text-muted small fw-bold"><?= t('Website') ?></label>
                                        <input type="text" class="form-control" id="website" name="website"
                                            placeholder="www.empresa.com">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Localização e Contato -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                    <span class="material-icons-round me-2">place</span>
                                    <?= t('Localização e Contatos') ?>
                                </h5>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
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
                                    <div class="col-md-4">
                                        <label for="po_box"
                                            class="form-label text-muted small fw-bold"><?= t('Caixa Postal') ?></label>
                                        <input type="text" class="form-control" id="po_box" name="po_box">
                                    </div>
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
                                        <label for="fax"
                                            class="form-label text-muted small fw-bold"><?= t('Fax') ?></label>
                                        <input type="text" class="form-control" id="fax" name="fax">
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                        class="form-label text-muted small fw-bold required"><?= t('Nome do Responsável') ?></label>
                                    <input type="text" class="form-control" id="pref_name" name="pref_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pref_email"
                                        class="form-label text-muted small fw-bold required"><?= t('Email Pessoal') ?></label>
                                    <input type="email" class="form-control" id="pref_email" name="pref_email" required>
                                </div>
                                <div class="mb-3">
                                    <label
                                        class="form-label text-muted small fw-bold required"><?= t('Telefone Direto') ?></label>
                                    <div class="input-group flex-nowrap">
                                        <select class="form-select countryPhone tel" name="pref_telephone_ddi"
                                            id="pref_telephone_ddi" style="max-width: 80px;">
                                            <option selected value="">DDI</option>
                                        </select>
                                        <input type="text" class="form-control telnumber" name="pref_telephone"
                                            id="pref_telephone" required>
                                    </div>
                                </div>
                                <div class="mb-3">
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

                    <!-- Coluna Direita: Preferências e Contato Pessoal -->
                    <div class="col-lg-4">
                        <!-- Card: Configurações -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                <h5 class="card-title fw-bold text-primary d-flex align-items-center">
                                    <span class="material-icons-round me-2">settings</span> <?= t('Configurações') ?>
                                </h5>
                            </div>
                            <div class="card-body pt-3">
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

                <!-- Botões de Ação -->
                <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                    <div id="divsaveContact" class="w-100 w-md-auto">
                        <!-- O botão de salvar será injetado aqui pelo JavaScript -->
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Evita quebra de JS quando selects com onchange="handleOtherOption()" disparam.
        // (Algumas funções PHP renderizam esse handler inline.)
        window.handleOtherOption = window.handleOtherOption || function () { return true; };
    </script>

    <script src="contacts/register_contact.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>