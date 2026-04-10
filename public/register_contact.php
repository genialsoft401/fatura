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
    from { opacity:0; transform: translateX(15px); }
    to { opacity:1; transform: translateX(0); }
}

/* Progress */
.step-progress {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    position: relative;
}

.step-progress::before {
    content:'';
    position:absolute;
    top:50%;
    width:100%;
    height:3px;
    background:#e5e7eb;
    transform: translateY(-50%);
}

.step-bar {
    position:absolute;
    top:50%;
    height:3px;
    background:#4f46e5;
    width:0%;
    transform: translateY(-50%);
    transition:.4s;
}

.step {
    z-index:2;
    background:white;
    border:2px solid #e5e7eb;
    width:38px;
    height:38px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.step.active {
    background:#4f46e5;
    color:white;
    border-color:#4f46e5;
}

/* Aside fixo */
.sticky-aside {
    position: sticky;
    top: 20px;
}

/* Buttons */
.step-actions {
    display:flex;
    justify-content:space-between;
    margin-top:30px;
}

.btn-step {
    border:none;
    padding:10px 20px;
    border-radius:8px;
}

.btn-next { background:#4f46e5; color:white; }
.btn-prev { background:#e5e7eb; }
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
    <?php /* Dados da Empresa */ ?>
    <div class="row g-3">
        <!-- mantém exatamente teus inputs -->
        <div class="col-md-6">
            <label><?= t('Tipo de Cliente') ?></label>
            <select class="form-select" name="type">
                <option><?= t('Normal') ?></option>
                <option><?= t('Autofaturação') ?></option>
            </select>
        </div>

        <div class="col-md-6">
            <label><?= t('NIF') ?></label>
            <input class="form-control" name="contributor">
        </div>

        <div class="col-12">
            <input class="form-control" name="name" placeholder="Nome">
        </div>

        <div class="col-md-6">
            <input class="form-control" name="email" placeholder="Email">
        </div>

        <div class="col-md-6">
            <input class="form-control" name="website" placeholder="Website">
        </div>
    </div>
</div>

<!-- STEP 2 -->
<div class="step-content">
    <?php /* Localização */ ?>
    <div class="row g-3">
        <div class="col-md-6">
            <select class="form-select" id="country" name="country"></select>
        </div>

        <div class="col-md-6">
            <select class="form-select" id="city" name="city"></select>
        </div>

        <div class="col-12">
            <textarea class="form-control" name="address"></textarea>
        </div>
    </div>
</div>

<!-- STEP 3 -->
<div class="step-content">
    <?php /* Contato */ ?>
    <input class="form-control mb-2" name="pref_name" placeholder="Nome">
    <input class="form-control mb-2" name="pref_email" placeholder="Email">
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
    steps.forEach((s,i)=>s.classList.toggle("active", i===current));
    indicators.forEach((s,i)=>s.classList.toggle("active", i<=current));
    bar.style.width = (current/(steps.length-1))*100+"%";

    prevBtn.style.display = current === 0 ? "none" : "block";
    nextBtn.innerText = current === steps.length-1 ? "Finalizar" : "Próximo";
}

nextBtn.onclick = ()=>{
    if(current < steps.length-1){
        current++;
        update();
    } else {
        contactForm.submit();
    }
};

prevBtn.onclick = ()=>{
    current--;
    update();
};

update();
</script>

<script src="contacts/register_contact.js"></script>

<?php require_once '../app/views/footer.php'; ?>
</body>
</html>
