<?php
require_once '../app/views/layout_creation.php';
?>

<body>

    <main>
        <div class="container mt-5">
            <h2 class="mb-4"><?= t('Editar Empresa'); ?></h2>
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
                    <label for="logo" class="form-label"><?= t('Alterar Logo'); ?></label>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                </div>

                <div class="col-12">
                    <span class="col-12 fw-bold mt-4">Dados da Empresa</span>
                    <hr>
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
                                    #logoCropStage {
                                        height: 65vh;
                                        min-height: 320px;
                                    }
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
                        <label for="name" class="form-label"><?= t('Nome da Empresa'); ?></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="registration_number" class="form-label"><?= t('CNPJ'); ?></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required inputmode="numeric" autocomplete="off" maxlength="14" placeholder="<?= t('CNPJ'); ?>">
                        <div class="form-text" id="registration_help" style="display:none;"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label"><?= t('Telefone'); ?></label>
                        <div class="d-flex flex-wrap gap-2" id="tel1">
                            <select class="form-select countryPhone tel" name="phone_ddi" id="phone_ddi">
                                <option selected value="">Carregando DDI...</option>
                            </select>
                            <input type="text" class="form-control telnumber" name="phone" id="phone" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label"><?= t('Email'); ?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label"><?= t('Website'); ?></label>
                        <input type="text" class="form-control" id="website" name="website">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label"><?= t('Endereço'); ?></label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label"><?= t('País'); ?></label>
                        <select class="form-select" id="country" name="country">
                            <option value=""><?= t('Selecione um país'); ?></option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="city" class="form-label"><?= t('Cidade'); ?></label>
                        <select class="form-select" id="city" name="city">
                            <option value=""><?= t('Selecione uma cidade'); ?></option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label"><?= t('Moeda Padrão'); ?></label>
                        <select class="form-select" id="currency" name="currency" required>
                            <?= currencySelects(); ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="goods_services" class="form-label">Bens e serviços</label>
                        <input type="text" class="form-control" id="goods_services" name="goods_services" placeholder="Os bens e serviços foram colocados à disposição do adquirente na data do documento.">
                    </div>

                    <div class="col-12">
                        <span class="col-12 fw-bold mt-4">Dados Fiscais</span>
                        <hr>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="vat_regime" class="form-label">Regime de IVA</label>
                        <select name="vat_regime" id="vat_regime" class="iva-select form-select">
                            <option value="">- Selecione -</option>
                            <option value="524">IVA - 14%</option>
                            <option value="563">IVA - 7%</option>
                            <option value="564">IVA - 5%</option>
                            <option value="525">M00 - Regime Simplificado</option>
                            <option value="526">M02 - Transmissão de bens e serviço não sujeita</option>
                            <option value="527">M04 - Regime de Exclusão</option>
                            <option value="528">M11 - Isento nos termos da alínea b) do nº1 do artigo 12.º do CIVA </option>
                            <option value="529">M12 - Isento nos termos da alínea c) do nº1 do artigo 12.º do CIVA </option>
                            <option value="530">M13 - Isento nos termos da alínea d) do nº1 do artigo 12.º do CIVA </option>
                            <option value="531">M14 - Isento nos termos da alínea e) do nº1 do artigo 12.º do CIVA </option>
                            <option value="532">M15 - Isento nos termos da alínea f) do nº1 do artigo 12.º do CIVA </option>
                            <option value="533">M16 - Isento nos termos da alínea g) do nº1 do artigo 12.º do CIVA </option>
                            <option value="534">M17 - Isento nos termos da alínea h) do nº1 do artigo 12.º do CIVA </option>
                            <option value="535">M18 - Isento nos termos da alínea i) do nº1 do artigo 12.º do CIVA </option>
                            <option value="536">M19 - Isento nos termos da alínea j) do nº1 do artigo 12.º do CIVA </option>
                            <option value="537">M20 - Isento nos termos da alínea k) do nº1 do artigo 12.º do CIVA </option>
                            <option value="538">M21 - Isento nos termos da alínea l) do nº1 do artigo 12.º do CIVA </option>
                            <option value="539">M22 - Isento nos termos da alínea m) do nº1 do artigo 12.º do CIVA </option>
                            <option value="540">M23 - Isento nos termos da alínea n) do nº1 do artigo 12.º do CIVA </option>
                            <option value="541">M24 - Isento nos termos da alínea o) do nº1 do artigo 12.º do CIVA </option>
                            <option value="542">M80 - Isento nos termos da alínea a) do nº1 do artigo 14.º do CIVA </option>
                            <option value="543">M81 - Isento nos termos da alínea b) do nº1 do artigo 14.º do CIVA </option>
                            <option value="544">M82 - Isento nos termos da alínea c) do nº1 do artigo 14.º do CIVA </option>
                            <option value="545">M83 - Isento nos termos da alínea d) do nº1 do artigo 14.º do CIVA </option>
                            <option value="546">M84 - Isento nos termos da alínea e) do nº1 do artigo 14.º do CIVA </option>
                            <option value="547">M85 - Isento nos termos da alínea a) do nº2 do artigo 14.º do CIVA </option>
                            <option value="548">M86 - Isento nos termos da alínea b) do nº2 do artigo 14.º do CIVA </option>
                            <option value="549">M30 - Isento nos termos da alínea a) do artigo 15.º do CIVA </option>
                            <option value="550">M31 - Isento nos termos da alínea b) do artigo 15.º do CIVA </option>
                            <option value="551">M32 - Isento nos termos da alínea c) do artigo 15.º do CIVA </option>
                            <option value="552">M33 - Isento nos termos da alínea d) do artigo 15.º do CIVA </option>
                            <option value="553">M34 - Isento nos termos da alínea e) do artigo 15.º do CIVA </option>
                            <option value="554">M35 - Isento nos termos da alínea f) do artigo 15.º do CIVA </option>
                            <option value="555">M36 - Isento nos termos da alínea g) do artigo 15.º do CIVA </option>
                            <option value="556">M37 - Isento nos termos da alínea h) do artigo 15.º do CIVA </option>
                            <option value="557">M38 - Isento nos termos da alínea i) do artigo 15.º do CIVA </option>
                            <option value="558">M90 - Isento nos termos da alinea a) do nº1 do artigo 16.º</option>
                            <option value="559">M91 - Isento nos termos da alinea b) do nº1 do artigo 16.º</option>
                            <option value="560">M92 - Isento nos termos da alinea c) do nº1 do artigo 16.º</option>
                            <option value="561">M93 - Isento nos termos da alinea d) do nº1 do artigo 16.º</option>
                            <option value="562">M94 - Isento nos termos da alinea e) do nº1 do artigo 16.º</option>

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="vat_irt" class="form-label">Retenção na fonte de IRT</label>
                        <select name="vat_irt" id="vat_irt" class="iva-select form-select">
                            <option value="">- Selecione -</option>
                            <option value="6,5">6,5% - Art. 67.º do CII</option>
                            <option value="6,5">6,5% - Art. 16.º, n.º 2, do CIRT</option>
                        </select>
                    </div>

                    <span class="col-12 mb-3 mt-4 fw-bold">Dados Bancários</span>
                    <hr>

                    <div class="col-md-6 mb-3">
                        <label for="bank_name" class="form-label">Banco (Conta principal)</label>
                        <select class="form-select" id="bank_name" name="bank_name">
                            <option value="" selected>- Selecione -</option>
                            <option value="Banco Angolano de Investimentos (BAI)">Banco Angolano de Investimentos (BAI)</option>
                            <option value="Banco de Fomento Angola (BFA)">Banco de Fomento Angola (BFA)</option>
                            <option value="Banco BIC">Banco BIC</option>
                            <option value="Banco Millennium Atlântico">Banco Millennium Atlântico</option>
                            <option value="Banco de Poupança e Crédito (BPC)">Banco de Poupança e Crédito (BPC)</option>
                            <option value="Banco de Comércio e Indústria (BCI)">Banco de Comércio e Indústria (BCI)</option>
                            <option value="Banco Caixa Geral Angola">Banco Caixa Geral Angola</option>
                            <option value="Banco Comercial Angolano (BCA)">Banco Comercial Angolano (BCA)</option>
                            <option value="Banco Sol">Banco Sol</option>
                            <option value="Banco Económico">Banco Económico</option>
                            <option value="Banco de Negócios Internacional (BNI)">Banco de Negócios Internacional (BNI)</option>
                            <option value="Banco Keve">Banco Keve</option>
                            <option value="Banco Valor">Banco Valor</option>
                            <option value="Access Bank Angola">Access Bank Angola</option>
                            <option value="Banco de Investimento Rural (BIR)">Banco de Investimento Rural (BIR)</option>
                            <option value="Banco Comercial do Huambo">Banco Comercial do Huambo</option>
                            <option value="Banco de Crédito do Sul (BCS)">Banco de Crédito do Sul (BCS)</option>
                            <option value="Banco Yetu">Banco Yetu</option>
                            <option value="Standard Bank de Angola">Standard Bank de Angola</option>
                            <option value="Standard Chartered Bank de Angola">Standard Chartered Bank de Angola</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="iban" class="form-label">IBAN</label>
                        <input type="text" class="form-control" id="iban" name="iban" placeholder="AO06..." autocomplete="off">
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="bank_name1" class="form-label">Banco (Conta secundária)</label>
                        <select class="form-select" id="bank_name1" name="bank_name1">
                            <option value="" selected>- Selecione -</option>
                            <option value="Banco Angolano de Investimentos (BAI)">Banco Angolano de Investimentos (BAI)</option>
                            <option value="Banco de Fomento Angola (BFA)">Banco de Fomento Angola (BFA)</option>
                            <option value="Banco BIC">Banco BIC</option>
                            <option value="Banco Millennium Atlântico">Banco Millennium Atlântico</option>
                            <option value="Banco de Poupança e Crédito (BPC)">Banco de Poupança e Crédito (BPC)</option>
                            <option value="Banco de Comércio e Indústria (BCI)">Banco de Comércio e Indústria (BCI)</option>
                            <option value="Banco Caixa Geral Angola">Banco Caixa Geral Angola</option>
                            <option value="Banco Comercial Angolano (BCA)">Banco Comercial Angolano (BCA)</option>
                            <option value="Banco Sol">Banco Sol</option>
                            <option value="Banco Económico">Banco Económico</option>
                            <option value="Banco de Negócios Internacional (BNI)">Banco de Negócios Internacional (BNI)</option>
                            <option value="Banco Keve">Banco Keve</option>
                            <option value="Banco Valor">Banco Valor</option>
                            <option value="Access Bank Angola">Access Bank Angola</option>
                            <option value="Banco de Investimento Rural (BIR)">Banco de Investimento Rural (BIR)</option>
                            <option value="Banco Comercial do Huambo">Banco Comercial do Huambo</option>
                            <option value="Banco de Crédito do Sul (BCS)">Banco de Crédito do Sul (BCS)</option>
                            <option value="Banco Yetu">Banco Yetu</option>
                            <option value="Standard Bank de Angola">Standard Bank de Angola</option>
                            <option value="Standard Chartered Bank de Angola">Standard Chartered Bank de Angola</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="iban1" class="form-label">IBAN</label>
                        <input type="text" class="form-control" id="iban1" name="iban1" placeholder="AO06..." autocomplete="off">
                    </div>

                    <br>
                    <hr>

                    <div class="col-md-6 mb-3">
                        <label for="invoice_impression" class="form-label">Impressão da Factura</label>
                        <select name="invoice_impression" class="form-select" id="invoice_impression" name="invoice_impression" placeholder="Ex: Impressão em papel, digital, etc.">
                            <option value="">- Selecione -</option>
                            <option value="A4">A4</option>
                            <option value="A5">A5</option>
                            <option value="Recibo">Recibo</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?= t('Salvar Alterações'); ?></button>
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