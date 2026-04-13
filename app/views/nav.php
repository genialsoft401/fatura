<style>
    .app-navbar {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid #eee;
    }

    /* Botões */
    .nav-icon-btn {
        border: none;
        background: transparent;
        padding: 8px;
        border-radius: 10px;
        transition: 0.2s;
    }

    .nav-icon-btn:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    /* Pill empresa */
    .nav-pill {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 6px 12px;
    }

    /* Perfil */
    .profile-img {
        width: 36px;
        height: 36px;
        border: 2px solid var(--primary-color);
    }

    /* Dropdown moderno */
    .dropdown-modern {
        position: absolute;
        right: 0;
        top: 110%;
        min-width: 260px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: none;
        animation: fadeIn 0.2s ease;
        overflow: hidden;
        z-index: 999;
    }

    .dropdown-modern.show {
        display: block;
    }

    /* Badge */
    .notif-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: red;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 50px;
    }

    /* Itens */
    .dropdown-modern .dropdown-item {
        padding: 10px 15px;
        transition: 0.2s;
    }

    .dropdown-modern .dropdown-item:hover {
        background: #f5f5f5;
    }

    /* Animação */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #perfil-menu {
        width: 80px !important;
    }
</style>

<header class="app-navbar px-3 py-2">

    <div class="d-flex align-items-center justify-content-between w-100">
        <input type="hidden" id="user_id" name="user_id" value="<?= $_SESSION['user']['id'] ?>">
        <input type="hidden" id="company_id" name="company_id" value="<?= $_SESSION['user']['company_id'] ?>">

        <!-- LEFT -->
        <div class="d-flex align-items-center gap-3">

            <!-- Mobile -->
            <button id="mobileMenuBtn" class="btn d-lg-none nav-icon-btn">
                <i class="material-icons-round">menu</i>
            </button>

            <!-- Empresa -->
            <div class="dropdown">
                <button class="btn nav-pill dropdown-toggle d-flex align-items-center gap-2"
                    id="empresaDropdown" data-bs-toggle="dropdown">

                    <span class="material-icons-round text-primary">business</span>

                    <span class="d-none d-sm-inline fw-semibold">
                        <?= $_SESSION['user']['name_company'] ?? 'Empresa' ?>
                    </span>

                    <span class="d-inline d-sm-none fw-bold text-uppercase">
                        <?= $_SESSION['user']['acronym'] ?? 'EMP' ?>
                    </span>
                </button>

                <ul class="dropdown-menu aling-items-center text-center" id="empresaDropdownMenu" aria-labelledby="empresaDropdown" style="font-size: 14px!important;">
                    <!-- Empresas serão carregadas aqui via AJAX -->
                </ul>
            </div>

            <!-- <div class="col-12">
                <div class="border-0 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="card-title mb-1">Plano &nbsp;</h5>
                            <div id="subInfo" class="text-muted" style="font-size:0.95rem;">Carregando...</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap" id="subActions">
                            <a class="btn btn-warning" id="btnRenewFromIndex" href="subscription.php">Renovar</a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row" id="subMetrics"></div>
                    </div>
                </div>
            </div> -->
        </div>

        <!-- RIGHT -->
        <div class="d-flex align-items-center gap-3">

            <!-- 🔔 NOTIFICAÇÕES -->
            <div class="position-relative">
                <button class="btn nav-icon-btn position-relative" onclick="togglePopup('notif-menu')">
                    <i class="bi bi-bell"></i>

                    <!-- Badge -->
                    <span class="notif-badge">3</span>
                </button>

                <div class="popup-menu dropdown-modern" id="notif-menu">
                    <div class="dropdown-header fw-semibold">Notificações</div>

                    <a class="dropdown-item">
                        <strong>Novo pagamento recebido</strong>
                        <small class="d-block text-muted">há 2 min</small>
                    </a>

                    <a class="dropdown-item">
                        <strong>Aluno registado</strong>
                        <small class="d-block text-muted">há 10 min</small>
                    </a>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center text-primary">Ver todas</a>
                </div>
            </div>

            <?php
            $nomeFormatado = formatName($_SESSION['user']['name']);

            // Exemplo (ajusta com teus dados reais)
            $plano = $_SESSION['user']['plan'] ?? 'Pro';
            $expira = $_SESSION['user']['plan_expiration'] ?? '2026-12-31';
            ?>

            <!-- 👤 PERFIL -->
            <div class="perfil-container position-relative">
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" onclick="togglePopup('perfil-menu')">

                    <div class="rounded-circle profile-img overflow-hidden">
                        <img class="w-100" src="assets/img/profiles/<?= $_SESSION['user']['image'] ?>">
                    </div>

                    <div class="d-none d-sm-flex flex-column align-items-start gap-0">
                        <strong id="userName" class="mb-0" data-text="<?= htmlspecialchars($nomeFormatado) ?>"></strong>
                        <small class="text-muted opacity-50" style="margin-top: -5px;"><?= t($_SESSION['user']['role']) ?></small>
                    </div>
                </a>
                <div class="popup-menu text-left mt-2" style="width: 80px !important;" id="perfil-menu">
                    <a class="popup-item p-2" href="perfil.php"><?= t('Perfil do utilizador') ?></a>
                    <hr class="opacity-25">
                    <a class="popup-item p-2" href="logout.php"><?= t('Sair') ?></a>
                </div>
            </div>

        </div>
    </div>
</header>

<script>
    const formatName = (name) => {
        if (!name) return null;
        name.split("")[0];
    }

    const userName = document.getElementById("userName");
    const nameAttr = userName.getAttribute("data-text");
    userName.innerText = nameAttr


    function togglePopup(id) {
        const current = document.getElementById(id);

        document.querySelectorAll('.popup-menu').forEach(menu => {
            if (menu !== current) menu.classList.remove('show');
        });

        current.classList.toggle('show');
    }

    document.addEventListener("click", function(e) {
        if (!e.target.closest(".perfil-container, .nav-icon-btn")) {
            document.querySelectorAll('.popup-menu')
                .forEach(menu => menu.classList.remove('show'));
        }
    });

    $(document).ready(function() {

        // Assinatura / limites
        $.getJSON('assets/ajax/get_company_limits.php', {
            company_id: <?php echo (int)$_SESSION['user']['company_id']; ?>
        }, function(resp) {
            if (!resp.success) {
                $('#subInfo').text('Não foi possível carregar os limites.');
                return;
            }
            const expIso = resp.plan_expires_at || '';
            const exp = expIso ? new Date(expIso + 'T00:00:00').toLocaleDateString('pt-PT') : '-';
            const days = (resp.days_left === null) ? '-' : resp.days_left;
            $('#subInfo').text(`${resp.plan_name} • vence em ${exp} • ${days} dias restantes`);

            const limInv = resp.limits.invoice_limit_month === null ? '∞' : resp.limits.invoice_limit_month;
            const limUsers = resp.limits.user_limit === null ? '∞' : resp.limits.user_limit;

            $('#btnRenewFromIndex').attr('href', `subscription.php?company_id=${resp.company_id}`);
        });

    });
</script>