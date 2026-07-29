<style>
    .app-navbar {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid #eee;
        z-index: 5;
    }

    .app-navbar.mobile-active {
        left: 250px;
        width: calc(100% - 250px);
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

    .dropdown-menu-custom {
        position: absolute;
        left: 0;
        top: 110%;
        min-width: 220px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        list-style: none;
        margin: 0;
        padding: 8px 0;
        font-size: 14px;
        z-index: 999;
    }

    .dropdown-menu-custom li {
        padding: 0;
    }

    .dropdown-menu-custom .dropdown-item {
        display: block;
        padding: 8px 15px;
        color: #212529;
        text-decoration: none;
        transition: 0.2s;
    }

    .dropdown-menu-custom .dropdown-item:hover {
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
        min-width: 200px;
        width: auto !important;
    }

    /* Botão hamburguer (apenas mobile/tablet) */
    #mobileMenuBtn {
        display: none;
        border: none;
        background: transparent;
        padding: 8px;
        border-radius: 10px;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
        color: var(--primary-color, #007abd);
    }

    #mobileMenuBtn:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    #mobileMenuBtn i {
        width: 22px;
        height: 22px;
    }

    @media (max-width: 992px) {
        #mobileMenuBtn {
            display: inline-flex;
        }
    }

    /* Em telas muito pequenas, encolhe um pouco os espaçamentos do header */
    @media (max-width: 576px) {
        .app-navbar {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .d-flex.align-items-center.gap-3 {
            gap: 0.5rem !important;
        }
    }
</style>

<header class="app-navbar px-3 py-2">

    <div class="d-flex align-items-center justify-content-between w-100">
        <input type="hidden" id="user_id" name="user_id" value="<?= $_SESSION['user']['id'] ?>">
        <input type="hidden" id="company_id" name="company_id" value="<?= $_SESSION['user']['company_id'] ?>">

        <!-- LEFT -->
        <div class="d-flex align-items-center gap-3">

            <!-- Botão de menu (mobile/tablet) -->
            <button id="mobileMenuBtn" title="Ocultar/Mostrar menu">
                <i data-lucide="panel-left-close"></i>
            </button>

            <!-- Empresa -->
            <div class="dropdown-custom position-relative">
                <button class="btn nav-pill d-flex align-items-center gap-2"
                    id="empresaDropdown" onclick="togglePopup('empresaDropdownMenu')">

                    <i data-lucide="building-2"></i>
                    <span class="d-none d-sm-inline">
                        <?= $_SESSION['user']['name_company'] ?? 'Empresa' ?>
                    </span>
                    
                    <span class="d-inline d-sm-none text-uppercase">
                        <?= $_SESSION['user']['acronym'] ?? 'EMP' ?>
                    </span>
                    <i data-lucide="chevron-down"></i>
                </button>

                <ul class="popup-menu dropdown-menu-custom text-left" id="empresaDropdownMenu">
                    <!-- Empresas serão carregadas aqui via AJAX -->
                </ul>
            </div>

        </div>

        <!-- RIGHT -->
        <div class="d-flex align-items-center gap-3">

            <!-- NOTIFICAÇÕES -->
            <div class="position-relative">
                <button class="btn nav-icon-btn position-relative" onclick="togglePopup('notif-menu')">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge">3</span>
                </button>

                <div class="popup-menu dropdown-modern" id="notif-menu">
                    <div class="dropdown-header fw-semibold">Notificações</div>

                    <a class="dropdown-item">
                        <small>Nenhuma notificação</small>
                    </a>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center text-primary">Ver todas</a>
                </div>
            </div>

            <?php
            $nomeFormatado = formatName($_SESSION['user']['name']);

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
                <div class="popup-menu text-left mt-2" id="perfil-menu">
                    <a class="popup-item p-2" href="perfil.php"><i class="bi bi-person"></i> <?= t('Perfil do utilizador') ?></a>
                    <a class="popup-item p-2" href="subscription.php"><i class="bi bi-credit-card-2-back"></i> Meu Plano</a>
                    <hr class="opacity-25">
                    <a class="popup-item p-2" href="logout.php"><i class="bi bi-box-arrow-in-left"></i> <?= t('Sair') ?></a>
                </div>
            </div>

        </div>
    </div>
</header>

<script src="assets/js/lucide.js"></script>
<script>
    lucide.createIcons();

    const userNameEl = document.getElementById("userName");
    if (userNameEl) {
        const nameAttr = userNameEl.getAttribute("data-text");
        userNameEl.innerText = nameAttr;
    }

    function togglePopup(id) {
        const current = document.getElementById(id);

        document.querySelectorAll('.popup-menu').forEach(menu => {
            if (menu !== current) menu.classList.remove('show');
        });

        current.classList.toggle('show');
    }

    document.addEventListener("click", function(e) {
        if (!e.target.closest(".perfil-container, .nav-icon-btn, .dropdown-custom")) {
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

            $('#btnRenewFromIndex').attr('href', `subscription.php?company_id=${resp.company_id}`);
        });

        // Dados da empresa (regime de IVA etc.)
        $.getJSON('assets/ajax/company_data.php', {
            company_id: <?php echo (int)$_SESSION['user']['company_id']; ?>
        }, function(resp) {
            if (!resp.success) {
                $('#subInfo').text('Não foi possível carregar os dados.');
                return;
            }

            let data = resp?.data;
            localStorage.setItem("vat_regime", JSON.stringify(data["vat_regime"]));
        });

    });

    function trocarEmpresa(empresaId, name, registration, email) {
        fetch('assets/ajax/change_company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `company_id=${empresaId}&name_company=${encodeURIComponent(name)}&registration_number=${encodeURIComponent(registration)}&email_company=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("empresaDropdown").innerText = name;
                    location.reload();
                } else {
                    alert('Erro ao trocar de empresa');
                }
            })
            .catch(error => console.error('Erro ao trocar de empresa:', error));
    }

    function carregarEmpresas() {
        fetch('assets/ajax/get_companies.php')
            .then(response => response.json())
            .then(data => {
                let dropdown = document.getElementById("empresaDropdownMenu");
                dropdown.innerHTML = '';

                data.forEach(empresa => {
                    let li = document.createElement("li");
                    li.innerHTML = `<a class="dropdown-item" href="#" onclick="trocarEmpresa(${empresa.id}, '${empresa.name}', '${empresa.registration_number}', '${empresa.email}')">
                            ${empresa.name}
                        </a>`;
                    dropdown.appendChild(li);
                });
            })
            .catch(error => console.error('Erro ao carregar empresas:', error));
    }

    carregarEmpresas();
</script>