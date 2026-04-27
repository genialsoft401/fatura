    <div class="modal fade" id="createContactModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">

            <div class="modal-content">
                <div class="modal-header modal-item">
                    <h5 class="modal-title" id="createContactModalLabel">Novo Contacto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <div class="col-lg-3" id="aside">
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