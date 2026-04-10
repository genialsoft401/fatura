<?php
require_once '../app/views/layout_creation.php';
?>

<style>
    /* ===== STEP UI ===== */

    .step-content {
        display: none;
        animation: fadeSlide .3s ease;
    }

    .step-content.active {
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
    .step-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        position: relative;
    }

    .step-progress::before {
        content: '';
        position: absolute;
        top: 50%;
        width: 100%;
        height: 3px;
        background: #e5e7eb;
        transform: translateY(-50%);
    }

    .step-bar {
        position: absolute;
        top: 50%;
        height: 3px;
        background: #4f46e5;
        width: 0%;
        transform: translateY(-50%);
        transition: .4s;
    }

    .step {
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

    .step.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }

    /* Aside fixo */
    .sticky-aside {
        position: sticky;
        top: 20px;
    }

    /* Buttons */
    .step-actions {
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
        background: #4f46e5;
        color: white;
    }

    .btn-prev {
        background: #e5e7eb;
    }
</style>

<body>
    <main class="bg-light min-vh-100 py-4">
        <div class="container">

            <form id="contactForm">

                <!-- HEADER -->
                <div class="mt-4 pt-4 d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold"><?= t("Adicionar Nova Empresa"); ?></h3>
                        <p class="text-muted"><?= t("Cadastro por etapas"); ?></p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <?php if ($isLocal): // Renderiza o botão apenas se estiver em localhost 
                        ?>
                            <button type="button" id="btnFillContact" class="btn btn-outline-warning btn-sm">
                                <i class="material-icons-round align-middle fs-6">science</i> Preencher (Teste)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <input type="hidden" name="company_id" value="<?= $_SESSION['user']['company_id'] ?>">
                <input type="hidden" name="<?= isset($_GET['id']) ? 'updated_at' : 'created_at'; ?>" value="<?= $dateAtual ?>">

                <div class="row g-4">

                    <!-- LEFT (STEPS) -->
                    <div class="col-lg-8">

                        <!-- PROGRESS -->
                        <div class="step-progress">
                            <div class="step-bar" id="stepBar"></div>
                            <div class="step active">1</div>
                            <div class="step">2</div>
                            <div class="step">3</div>
                        </div>

                        <!-- STEP 1 -->
                        <div class="step-content active">
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
                                        <div class="col-12">
                                            <label for="name"
                                                class="form-label text-muted small fw-bold required"><?= t('Nome da Empresa') ?></label>
                                            <input type="text" class="form-control form-control-lg" id="name" name="name"
                                                placeholder="Nome comercial completo" required>
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div class="step-content">
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

                        </div>

                        <!-- STEP 3 -->
                        <div class="step-content">
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
                                        <input type="text" class="form-control" id="pref_name" name="pref_name" >
                                    </div>
                                    <div class="mb-3">
                                        <label for="pref_email"
                                            class="form-label text-muted small fw-bold"><?= t('Email Pessoal') ?></label>
                                        <input type="email" class="form-control" id="pref_email" name="pref_email" >
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

                        <!-- Botões de Ação -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <div id="divsaveContact" class="w-100 w-md-auto">
                                <!-- O botão de salvar será injetado aqui pelo JavaScript -->
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <div class="step-actions">
                            <button type="button" class="btn-step btn-prev" id="prevBtn">Voltar</button>
                            <button type="button" class="btn-step btn-next" id="nextBtn">Próximo</button>
                        </div>

                    </div>

                    <!-- RIGHT (ASIDE ORIGINAL) -->
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

            </form>
        </div>
    </main>

    <script>
        let current = 0;

        const steps = document.querySelectorAll(".step-content");
        const indicators = document.querySelectorAll(".step");
        const bar = document.getElementById("stepBar");

        function update() {
            steps.forEach((s, i) => s.classList.toggle("active", i === current));
            indicators.forEach((s, i) => s.classList.toggle("active", i <= current));
            bar.style.width = (current / (steps.length - 1)) * 100 + "%";

            prevBtn.style.display = current === 0 ? "none" : "block";
            nextBtn.innerText = current === steps.length - 1 ? "Finalizar" : "Próximo";
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
    </script>

    <script src="contacts/register_contact.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>