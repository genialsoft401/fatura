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
</style>

<body>
    <main>
        <div class="container mt-5">

            <h2 class="text-center"><?= t('Emissão de Fatura') ?></h2>

            <form id="formFatura">

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
                    <div class="step" data-step="4">
                        <div class="circle">4</div><span>Resumo</span>
                    </div>
                </div>

                <div class="progress mb-4" style="height:6px;">
                    <div id="progressBar" class="progress-bar" style="width:25%"></div>
                </div>

                <!-- ================= STEP 1 ================= -->
                <div class="form-step active" data-step="1">

                    <div class="d-flex gap-8 mb-3">
                        <h4><?= t('Dados do Cliente') ?></h4>
                        <button type="btn btn-primary" style="cursor: pointer; width: 140px !important; padding: 5px 10px !important; border-radius: 20px; background: #007abd !important; font-size: 10pt !important; border: 0px solid #000;" class="bg-plan" id="toggle-contact-form" data-bs-title="Inserir novo Contato" data-bs-toggle="tooltip" data-bs-placement="top">
                            <span class="ml-4">Novo cliente</span>
                        </button>
                    </div>
                    <hr>

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

                <!-- ================= STEP 2 ================= -->
                <div class="form-step" data-step="2">

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
                <div class="form-step" data-step="3">

                    <div class="d-flex gap-2 mb-2 justify-content-between">
                        <h4><?= t('Itens') ?></h4>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
                            <i data-lucide="plus"></i> Novo Produto/Serviço
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

                <!-- ================= STEP 4 ================= -->
                <div class="form-step" data-step="4">
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
                </div>

                <!-- BOTÕES -->
                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" id="prevBtn" class="btn btn-light d-none">← Anterior</button>
                    <button type="button" id="nextBtn" class="btn btn-primary">Próximo →</button>
                    <button id="saveInvoiceBtn" type="button" class="btn btn-primary d-none"><?= t('Salvar Rascunho') ?></button>

                </div>

            </form>
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
    </script>


    <script src="create_invoices/create_invoices.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>