<!-- Modal Criar Contato -->
<div class="modal fade" id="createContactModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">

        <div class="modal-content">
            <div class="modal-header modal-item">
                <h5 class="modal-title" id="createContactModalLabel">Novo Contacto</h5>
                <button type="button" class="btn-close" id="createModalCloseButton" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">

                    <!-- DADOS DO CLIENTE -->
                    <div class="mb-3">
                        <h5 class="mb-2"><?= t('Dados do Cliente') ?></h5>
                        <input type="text" readonly hidden name="company_id" value="<?=$_SESSION['user']['company_id']?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label"><?= t('Tipo') ?>: </label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="<?= t('Normal') ?>"><?= t('Normal') ?></option>
                                    <option value="<?= t('Autofaturação') ?>"><?= t('Autofaturação') ?></option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label"><?= t('País') ?>:</label>
                                <select class="form-select" id="country" name="country">
                                    <option value=""><?= t('Carregando países') ?>...</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label"><?= t('Nome') ?>: </label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label"><?= t('Cidade') ?>: </label>
                                <select class="form-select" id="city" name="city">
                                    <option value=""><?= t('Escolha um país primeiro') ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contributor" class="form-label"><?= t('Contribuinte') ?>:</label>
                                <input type="text" class="form-control" id="contributor" name="contributor" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label"><?= t('Email') ?>:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label"><?= t('Endereço') ?>: </label>
                                <textarea class="form-control" id="address" name="address" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label"><?= t('Telefone') ?>:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <select class="form-select countryPhone tel" name="telephone_ddi" id="telephone_ddi">
                                        <option selected value="">Carregando DDI...</option>
                                    </select>
                                    <input type="text" name="telephone" class="form-control telnumber" id="telephone" placeholder="Digite seu telefone" required> 
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="caixa_postal" class="form-label"><?= t('Caixa Postal') ?>:</label>
                                <input type="text" class="form-control" id="caixa_postal" name="po_box" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telemovel" class="form-label"><?= t('Telemóvel') ?>:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <select class="form-select countryPhone tel" name="cellphone_ddi" id="cellphone_ddi">
                                        <option selected value="">Carregando DDI...</option>
                                    </select>
                                    <input type="text" name="cellphone" class="form-control telnumber" placeholder="Digite seu telefone"> 
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label"><?= t('Website') ?>:</label>
                                <input type="text" class="form-control" id="website" name="website">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fax" class="form-label"><?= t('Fax') ?>:</label>
                                <input type="text" class="form-control" id="fax" name="fax">
                            </div>
                        </div>
                    </div>

                    <!-- CONTACTO PREFERENCIAL -->
                    <div class="mb-3">
                        <h5 class="mb-2"><?= t('Contato Preferencial') ?></h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pref_name" class="form-label"><?= t('Nome') ?>:</label>
                                <input type="text" class="form-control" id="pref_name" name="pref_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pref_email" class="form-label"><?= t('Email') ?>:</label>
                                <input type="email" class="form-control" id="pref_email" name="pref_email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pref_telefone" class="form-label"><?= t('Telefone') ?>:</label>
                                <div class="d-flex flex-wrap gap-2" id="tel1">
                                    <select class="form-s6elect countryPhone tel" name="pref_telephone_ddi" id="pref_telephone_ddi">
                                        <option selected value="">Carregando DDI...</option>
                                    </select>
                                    <input type="text" class="form-control telnumber" name="pref_telephone" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pref_telemovel" class="form-label"><?= t('Telemóvel') ?>:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <select class="form-select  countryPhone tel" name="pref_cellphone_ddi" id="pref_cellphone_ddi">
                                        <option selected value="">Carregando DDI...</option>
                                    </select>
                                    <input type="text" class="form-control telnumber" name="pref_cellphone" >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PREFERÊNCIAS DE FACTURAÇÃO -->
                    <div class="mb-3">
                        <h5 class="mb-2"><?= t('Preferências de Faturação') ?></h5>
                        <div class="form-check mb-3">
                            <input class="form-check-input" checked type="checkbox" id="usar_definicoes">
                            <label class="form-check-label" for="usar_definicoes"><?= t('Usar definições de conta') ?></label>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="num_copias" class="form-label"><?= t('Nº de cópias') ?>:</label>
                                <?= numberCopysSelect(); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="observations" class="form-label"><?= t('Observações') ?>:</label>
                                <textarea class="form-control" id="observations"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label"><?= t('Vencimento') ?>:</label>
                                <?= due_dateSelect(); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label"><?= t('Idioma') ?>:</label>
                                <select class="form-select" name="language" required id="language">
                                    <option value="BR">Português Brasileiro</option>
                                    <option value="AO" selected>Português Angolano</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                            <label for="moeda" class="form-label"><?= t('Método de Pagamento') ?>:</label>
                                <select class="form-select" name="payment_method" required id="payment_method">
                                    <?= getPaymentMethods(); ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="moeda" class="form-label"><?= t('Moeda') ?>:</label>
                                <select class="form-select" name="currency" required id="currency">
                                   <?= currencySelects(); ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-success mt-3" id="saveContactButton"><?=t('Salvar Contato')?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes e edição -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Contato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" id="contact_id_edit">
                    <div class="mb-3">
                        <label>Nome:</label>
                        <input type="text" id="contact_name_edit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" id="contact_email_edit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Telefone:</label>
                        <input type="text" id="contact_telephone_edit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>País:</label>
                        <input type="text" id="contact_country_edit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Observações:</label>
                        <textarea id="contact_observations_edit" class="form-control"></textarea>
                    </div>
                    <button type="button" id="saveChanges" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="contacts/contacts.js"></script>
<script src="contacts/edit_create_contact.js"></script>
 