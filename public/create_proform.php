<?php
require_once '../app/views/layout_creation.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link rel="stylesheet" href="create_invoices/create_invoices.css">
<link href="assets/css/select2.min.css" rel="stylesheet" />
<script src="assets/js/select2.min.js"></script>

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

    #aside input,
    #aside select {
        padding: 5px 5px !important;
    }

    #aside textarea {
        height: 60px !important;
    }

    .row-total {
        font-size: 10pt !important;
    }
</style>

<main>
    <div class="container mt-5">
        <br><br>
        <h2 class="text-left mb-4"><?= t('Emissão de Proforma') ?></h2>

        <form id="formFatura" class="mt-5">

            <input type="hidden" value="<?= $_SESSION['user']['company_id'] ?>" id="id_company" name="id_company">
            <input type="hidden" id="edit_invoice_id" name="edit_invoice_id">

            <!-- STEPPER -->
            <div class="stepper">
                <div class="step active d-flex align-items-center gap-4 fw-bold" data-step="1">
                    <div class="circle">1</div><span>Cliente & Documentos</span>
                </div>
                <div class="step d-flex align-items-center gap-4 fw-bold" data-step="2">
                    <div class="circle">2</div><span>Produtos & Serviços</span>
                </div>
            </div>

            <!-- PROGRESS -->
            <div class="progress mb-4" style="height:6px;">
                <div id="progressBar" class="progress-bar" style="width:25%"></div>
            </div>

            <div class="row">
                <div class="col-lg-12">

                    <!-- STEP 1 -->
                    <div class="form-step active" data-step="1">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0"><?= t('Dados do Cliente') ?></h4>

                            <a href="register_contact.php"
                                class="btn btn-success btn-sm rounded-pill px-3">
                                <i class="bi bi-plus-circle"></i> Novo cliente
                            </a>
                        </div>

                        <hr>

                        <div class="col-lg-12 bg-white shadow-sm p-3 rounded">
                            <div id="select-contact-container">
                                <label for="contact-select" class="form-label"><?= t('Escolha um Contato') ?>:</label>
                                <select id="contact-select" class="form-select">
                                    <option value=""><?= t('Selecione um contato...') ?></option>
                                    <!-- Os contatos existentes serão carregados via JS -->
                                </select>
                            </div>

                            <!-- Formulário de Cliente -->
                            <div id="contact-form d-none" style="display: none;">
                                <input type="text" hidden readonly id="contact_id" name="contact_id">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div>
                                            <label for="name" class="form-label"><?= t('Nome') ?>:</label>
                                            <input type="text" class="form-control" id="contact_name" name="name" required>
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
                                        <input type="tel" class="form-control" id="po_box" name="po_box">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label"><?= t('País') ?>:</label>
                                        <select class="form-select" id="country" name="country">
                                            <option value=""><?= t('Carregando países') ?>...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label"><?= t('Cidade') ?>: </label>
                                        <select class="form-select" id="city" name="city">
                                            <option value=""><?= t('Escolha um país primeiro') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <h4 class="mt-5"><?= t('Detalhes do Documento') ?></h4>
                        <hr>

                        <div class="row mb-4 bg-white shadow-sm p-2 rounded">
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
                                <textarea style="height: 11rem;" type="text" class="form-control" id="observation" name="observation"></textarea>
                            </div>

                            <div class="row" style="margin-top: -45px !important;">
                                <div class="col-md-6 mb-3">
                                    <label for="series" class="form-label"><?= t('Série') ?>:</label>
                                    <input class="form-control" id="series" name="series" readonly>
                                </div>

                                <div class="col-md-6 mb-6 d-none" style="margin-top: 40px;">
                                    <label for="retention" class="form-label"><?= t('Retenção') ?>: (%)</label>
                                    <input type="number" value="0.00" step="0.01" class="form-control" id="retention" name="retention">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 d-none">
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

                    <!-- STEP 3 -->
                    <div class="form-step" data-step="2">

                        <div class="d-flex justify-content-between mb-2">
                            <h4><?= t('Itens') ?></h4>

                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#itemModal">
                                <i class="bi bi-plus-circle"></i> Novo produto/serviço
                            </button>
                        </div>

                        <hr>

                        <div class="row mt-3 bg-white shadow-sm p-3 rounded">
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="items-container d-none" id="items_list">
                                        <div class="row fw-bold bg-light p-2 rounded">
                                            <div style="width: 120px !important;" class="col-1 text-center"><?= t('Código') ?></div>
                                            <div class="col-3"><?= t('Descrição') ?></div>
                                            <div class="col-2 text-center"><?= t('Preço Unitário') ?></div>
                                            <div class="col-1 text-center"><?= t('Qtd.') ?></div>
                                            <div style="width: 80px !important;" class="col-1 text-center"><?= t('Taxa/IVA') ?></div>
                                            <div class="col-1 text-center"><?= t('Desc.%') ?></div>
                                            <div class="col-2 text-center"><?= t('Total') ?></div>
                                            <div style="width: 50px !important;" class="text-center">Ações</div>
                                        </div>
                                    </div>
                                    <label for="item_select" class="form-label mt-4 mb-2"><?= t('Selecionar Item') ?></label><br>
                                    <select class="form-select col-12 select2" style="width: 100% !important;" id="item_select">
                                        <option value=""><?= t('Carregando itens...') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <h4 class="mt-3"><?= t('Resumo da Fatura') ?></h4>
                        <hr>

                        <div class="bg-white shadow-sm p-3 rounded">
                            <table class="table table-bordered mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= t('Taxa/IVA') ?></th>
                                        <th><?= t('Incidência') ?></th>
                                        <th><?= t('Valor (IVA)') ?></th>
                                        <th><?= t('Retenção') ?></th>
                                        <th><?= t('Total') ?></th>
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

                            <input type="hidden" name="total_sum">
                            <input type="hidden" name="total_discount">
                            <input type="hidden" name="subtotal_without_tax">
                            <input type="hidden" name="total_tax">
                            <input type="hidden" name="retention_value">
                            <input type="hidden" name="final_total">
                        </div>
                    </div>

                </div>
            </div>

            <!-- BOTÕES -->
            <div class="mt-4 d-flex justify-content-between">
                <button type="button" id="prevBtn" class="btn btn-light d-none">← Anterior</button>
                <button type="button" id="nextBtn" class="btn btn-primary">Próximo →</button>
                <button id="saveInvoiceBtn" type="button" class="btn btn-success d-none">
                    <?= t('Finalizar Fatura') ?>
                </button>
            </div>

        </form>
    </div>
</main>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- Select2 (se usares) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let company = null;
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

        if (prevBtn) prevBtn.classList.toggle("d-none", step === 1);

        if (step === steps.length) {
            saveBtn?.classList.remove("d-none");
            nextBtn?.classList.add("d-none");
        } else {
            saveBtn?.classList.add("d-none");
            nextBtn?.classList.remove("d-none");
        }

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
        const bar = document.getElementById("progressBar");
        if (!bar || steps.length === 0) return;

        bar.style.width = ((currentStep / steps.length) * 100) + "%";
    }

    /* ===== VALIDATION ===== */
    function validateStep() {
        const current = document.querySelector(`.form-step[data-step="${currentStep}"]`);
        if (!current) return true;

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
        if (!form) return;

        const data = new FormData(form);
        const obj = {};

        data.forEach((v, k) => obj[k] = v);
        localStorage.setItem("invoiceDraft", JSON.stringify(obj));
    }

    function loadDraft() {
        if (!form) return;

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
            const p = parseFloat(row.querySelector(".price")?.value) || 0;
            const q = parseFloat(row.querySelector(".qty")?.value) || 0;

            total += p * q;
        });

        const totalField = document.getElementById("final_total");
        if (totalField) totalField.innerText = total.toFixed(2);
    }

    /* ===== EVENTS ===== */
    nextBtn?.addEventListener("click", () => {
        if (!validateStep()) return;

        if (currentStep < steps.length) {
            currentStep++;
            showStep(currentStep);
        }
    });

    prevBtn?.addEventListener("click", () => {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    form?.addEventListener("input", () => {
        saveDraft();
        calc();
    });

    /* ===== INIT ===== */
    document.addEventListener("DOMContentLoaded", () => {
        loadDraft();
        showStep(currentStep);
        calc();
    });

    /* ===== CURRENCY ===== */
    let userCurrency = "<?= $_SESSION['user']['iso_code'] ?>";
    let currencySymbol = "<?= $_SESSION['user']['symbol'] ?>";
    let currencyPosition = "<?= $_SESSION['user']['position'] ?>";

    /* ===== FORM CLIENT ===== */
    function formClient() {
        let current = 0;

        const steps = document.querySelectorAll(".step-contentC");
        const indicators = document.querySelectorAll(".stepC");
        const bar = document.getElementById("stepCBar");
        const prevBtn = document.getElementById("prevCBtn");
        const nextBtn = document.getElementById("nextCBtn");
        const saveBtn = document.getElementById("saveChangesContact");
        const contactForm = document.getElementById("contactForm");

        function update() {
            const lastStep = steps.length - 1;

            steps.forEach((s, i) =>
                s.classList.toggle("active", i === current)
            );

            indicators.forEach((s, i) =>
                s.classList.toggle("active", i <= current)
            );

            if (bar && lastStep > 0) {
                bar.style.width = (current / lastStep) * 100 + "%";
            }

            if (prevBtn) prevBtn.style.display = current === 0 ? "none" : "block";

            const isLast = current === lastStep;

            if (isLast) {
                nextBtn?.classList.add("d-none");
                saveBtn?.classList.remove("d-none");
            } else {
                nextBtn?.classList.remove("d-none");
                saveBtn?.classList.add("d-none");
            }
        }

        nextBtn?.addEventListener("click", () => {
            if (current < steps.length - 1) {
                current++;
                update();
            } else {
                contactForm?.submit();
            }
        });

        prevBtn?.addEventListener("click", () => {
            if (current > 0) {
                current--;
                update();
            }
        });

        update();
    }

    formClient();

    /* ===== SERIES FIELD ===== */
    const seriesField = document.getElementById("series");

    if (seriesField) {
        const year = new Date().getFullYear();
        seriesField.value = year;

        ["keydown", "paste", "drop"].forEach(evt =>
            seriesField.addEventListener(evt, e => e.preventDefault())
        );
    }

    /* ===== FETCH COMPANY ===== */
    async function fetchCompany() {
        try {
            const response = await fetch(`assets/ajax/company_data.php`);

            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }

            const data = await response.json();

            if (data?.data) {
                company = data.data;
            } else {
                console.warn("Nenhum dado encontrado");
            }

        } catch (error) {
            console.error("Erro ao buscar empresa:", error);
        }
    }

    setTimeout(fetchCompany, 100);
</script>


<script src="create_proform/create_invoices.js"></script>

<?php require_once '../app/views/footer.php'; ?>