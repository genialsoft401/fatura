<?php
require_once '../app/views/layout_creation.php';
?>

<body>

    <main>
        <div class="container mt-5">
            <h2 class="mb-4"><?= t('Editar Empresa');?></h2>
            <form id="editCompanyForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id">

                <div class="mb-3 text-center">
                    <div class="d-inline-block position-relative" style="min-width: 160px; min-height: 160px;">
                        <div id="companyLogoSpinner" class="position-absolute top-50 start-50 translate-middle" style="display:none;">
                            <div class="spinner-border text-primary" role="status" aria-label="Carregando logo"></div>
                        </div>
                        <img id="companyLogo" src="" alt="Logo da Empresa" class="img-thumbnail" style="max-height: 150px; opacity:0; transition: opacity .15s ease;">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label"><?= t('Alterar Logo');?></label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                </div>

                <!-- Modal de Corte/Enquadramento (Logo) -->
                <div class="modal fade" id="logoCropModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Ajustar logo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <style>
                                    #logoCropStage { height: 65vh; min-height: 320px; }
                                </style>
                                <div id="logoCropStage">
                                    <img id="logoCropImage" alt="Cortar logo" style="max-width:100%; display:block;" />
                                </div>
                                <div class="small text-muted mt-2">Arraste para reenquadrar e use scroll/pinch para zoom. O corte é livre (sem proporção fixa).</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" id="saveLogoCropBtn" class="btn btn-primary">Usar este corte</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label"><?= t('Nome da Empresa');?></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="registration_number" class="form-label"><?= t('CNPJ');?></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required inputmode="numeric" autocomplete="off" maxlength="14" placeholder="<?= t('CNPJ');?>">
                        <div class="form-text" id="registration_help" style="display:none;"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label"><?= t('Telefone');?></label>
                        <div class="d-flex flex-wrap gap-2" id="tel1">
                            <select class="form-select countryPhone tel" name="phone_ddi" id="phone_ddi">
                                <option selected value="">Carregando DDI...</option>
                            </select>
                            <input type="text" class="form-control telnumber" name="phone" id="phone" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label"><?= t('Email');?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label"><?= t('Website');?></label>
                        <input type="text" class="form-control" id="website" name="website">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label"><?= t('Endereço');?></label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label"><?= t('País');?></label>
                        <select class="form-select" id="country" name="country">
                            <option value=""><?= t('Selecione um país');?></option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label"><?= t('Cidade');?></label>
                        <select class="form-select" id="city" name="city">
                            <option value=""><?= t('Selecione uma cidade');?></option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="zip_code" class="form-label"><?= t('CEP');?></label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label"><?= t('Moeda Padrão');?></label>
                        <select class="form-select" id="currency" name="currency" required>
                                <?= currencySelects(); ?>
                        </select>
                    </div>

                    <div class="col-12"><hr></div>

                    <div class="col-md-6 mb-3">
                        <label for="vat_regime" class="form-label">Regime de IVA</label>
                        <input type="text" class="form-control" id="vat_regime" name="vat_regime" placeholder="Ex: Regime geral / Isento / ...">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="goods_services" class="form-label">Bens e serviços</label>
                        <input type="text" class="form-control" id="goods_services" name="goods_services" placeholder="Os bens e serviços foram colocados à disposição do adquirente na data do documento.">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bank_name" class="form-label">Banco</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Ex: BAI / BFA / ...">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="iban" class="form-label">IBAN</label>
                        <input type="text" class="form-control" id="iban" name="iban" placeholder="AO06..." autocomplete="off">
                    </div>

                    <div class="col-12 mb-3">
                        <label for="bank_details" class="form-label">Dados bancários (complemento)</label>
                        <input type="text" class="form-control" id="bank_details" name="bank_details" placeholder="Ex: Nº Conta, SWIFT, agência, observações">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?= t('Salvar Alterações');?></button>
            </form>
        </div>


        <script>
            window.APP_LANG = <?= json_encode($_SESSION['user']['lang'] ?? 'angola') ?>;
        </script>
    </main>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    <script src="edit_company/edit_company.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>