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
    .notificationCount {
        position: absolute;
        top: 2px;
        right: 2px;
        background: red;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 50px;
        min-width: 14px;
        text-align: center;
    }

    .notificationCount[hidden] {
        display: none;
    }

    /* ---- Botão do Assistente IA (robô piscando) ---- */
    .ai-agent-btn {
        position: relative;
    }

    .ai-agent-btn i {
        color: #6f42c1;
    }

    /* Anel de pulso: só ativo quando há insights novos (classe .has-alert) */
    .ai-agent-btn.has-alert::before {
        content: '';
        position: absolute;
        inset: 2px;
        border-radius: 10px;
        box-shadow: 0 0 0 0 rgba(111, 66, 193, 0.55);
        animation: aiPulseRing 1.8s ease-out infinite;
        pointer-events: none;
    }

    .ai-agent-btn.has-alert i {
        animation: aiIconBlink 1.8s ease-in-out infinite;
    }

    @keyframes aiPulseRing {
        0% {
            box-shadow: 0 0 0 0 rgba(111, 66, 193, 0.5);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(111, 66, 193, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(111, 66, 193, 0);
        }
    }

    @keyframes aiIconBlink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.45;
        }
    }

    .aiInsightCount {
        position: absolute;
        top: 2px;
        right: 2px;
        background: #6f42c1;
        color: #fff;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 50px;
        min-width: 14px;
        text-align: center;
    }

    .aiInsightCount[hidden] {
        display: none;
    }

    .dropdown-modern .dropdown-item.ai-insight-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        cursor: default;
        border-left: 3px solid #6f42c1;
    }

    /* Itens */
    .dropdown-modern .dropdown-item {
        padding: 10px 15px;
        transition: 0.2s;
    }

    .dropdown-modern .dropdown-item:hover {
        background: #f5f5f5;
    }

    .dropdown-modern .dropdown-item.notification-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        cursor: default;
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
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
        cursor: pointer;
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

<?php
// --- Dados de sessão sanitizados uma única vez, aqui, para todo o template ---
$currentUserId  = (int)($_SESSION['user']['id'] ?? 0);
$currentCompany = (int)($_SESSION['user']['company_id'] ?? 0);

$nomeFormatado  = formatName($_SESSION['user']['name'] ?? '');
$userRole       = t($_SESSION['user']['role'] ?? '');
$nameCompany    = $_SESSION['user']['name_company'] ?? 'Empresa';
$acronym        = strtoupper($_SESSION['user']['acronym'] ?? 'EMP');

// Evita path traversal / XSS no <img src>: só aceita nome de ficheiro simples
$profileImage = basename($_SESSION['user']['image'] ?? '');
if ($profileImage === '' || !preg_match('/^[\w.-]+\.(png|jpe?g|gif|webp)$/i', $profileImage)) {
    $profileImage = 'default.png';
}

// Token CSRF simples para as chamadas AJAX que alteram estado (ex.: trocar empresa)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<header class="app-navbar px-3 py-2">

    <div class="d-flex align-items-center justify-content-between w-100">
        <input type="hidden" id="user_id" value="<?= $currentUserId ?>">
        <input type="hidden" id="company_id" value="<?= $currentCompany ?>">
        <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

        <!-- LEFT -->
        <div class="d-flex align-items-center gap-3">

            <button id="mobileMenuBtn" type="button" title="Ocultar/Mostrar menu" aria-label="Ocultar/Mostrar menu">
                <i data-lucide="panel-left-close"></i>
            </button>

            <!-- Empresa -->
            <div class="dropdown-custom position-relative">
                <button class="btn nav-pill d-flex align-items-center gap-2"
                    type="button" id="empresaDropdown" aria-haspopup="true" aria-expanded="false">

                    <i data-lucide="building-2"></i>
                    <span class="d-none d-sm-inline"><?= htmlspecialchars($nameCompany, ENT_QUOTES) ?></span>
                    <span class="d-inline d-sm-none text-uppercase"><?= htmlspecialchars($acronym, ENT_QUOTES) ?></span>
                    <i data-lucide="chevron-down"></i>
                </button>

                <ul class="popup-menu dropdown-menu-custom text-left" id="empresaDropdownMenu">
                    <li><small class="d-block px-3 py-2 text-muted">A carregar…</small></li>
                </ul>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="d-flex align-items-center gap-3">

            <!-- NOTIFICAÇÕES -->
            <div class="position-relative">
                <button class="btn nav-icon-btn position-relative" type="button" id="notifBtn" aria-haspopup="true" aria-expanded="false" aria-label="Notificações">
                    <i class="bi bi-bell"></i>
                    <span class="notificationCount" id="notificationCount" hidden>0</span>
                </button>

                <div class="popup-menu dropdown-modern" id="notif-menu">
                    <div class="dropdown-header fw-semibold px-3 pt-2">Notificações</div>

                    <div id="notificationList">
                        <small class="d-block px-3 py-2 text-muted">Nenhuma notificação</small>
                    </div>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center text-primary" href="notificacoes.php">Ver todas</a>
                </div>
            </div>

            <!-- 🤖 ASSISTENTE IA -->
            <div class="position-relative">
                <button class="btn nav-icon-btn ai-agent-btn" type="button" id="aiAgentBtn" aria-haspopup="true" aria-expanded="false" aria-label="Assistente IA" title="Assistente IA">
                    <i class="bi bi-robot"></i>
                    <span class="aiInsightCount" id="aiInsightCount" hidden>0</span>
                </button>

                <div class="popup-menu dropdown-modern" id="ai-menu">
                    <div class="dropdown-header fw-semibold px-3 pt-2">
                        <i class="bi bi-robot text-primary"></i> Assistente IA
                    </div>

                    <div id="aiInsightList">
                        <small class="d-block px-3 py-2 text-muted">Sem novidades por agora</small>
                    </div>

                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center text-primary" href="insights.php">Ver todos os insights</a>
                </div>
            </div>

            <!-- 👤 PERFIL -->
            <div class="perfil-container position-relative">
                <button type="button" class="d-flex align-items-center gap-2 border-0 bg-transparent text-decoration-none"
                    id="perfilBtn" aria-haspopup="true" aria-expanded="false">

                    <div class="rounded-circle profile-img overflow-hidden">
                        <img class="w-100" src="assets/img/profiles/<?= htmlspecialchars($profileImage, ENT_QUOTES) ?>" alt="Foto de perfil">
                    </div>

                    <div class="d-none d-sm-flex flex-column align-items-start gap-0">
                        <strong class="mb-0"><?= htmlspecialchars($nomeFormatado, ENT_QUOTES) ?></strong>
                        <small class="text-muted opacity-50" style="margin-top: -5px;"><?= htmlspecialchars($userRole, ENT_QUOTES) ?></small>
                    </div>
                </button>

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
    (function() {
        'use strict';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const companyId = document.getElementById('company_id').value;

        lucide.createIcons();

        // ---------- Popups (toggle + fechar ao clicar fora) ----------
        const triggers = {
            'empresaDropdown': 'empresaDropdownMenu',
            'notifBtn': 'notif-menu',
            'aiAgentBtn': 'ai-menu',
            'perfilBtn': 'perfil-menu',
        };

        Object.entries(triggers).forEach(([btnId, menuId]) => {
            const btn = document.getElementById(btnId);
            const menu = document.getElementById(menuId);
            if (!btn || !menu) return;

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = menu.classList.contains('show');
                document.querySelectorAll('.popup-menu').forEach(m => m.classList.remove('show'));
                document.querySelectorAll('[aria-expanded]').forEach(b => b.setAttribute('aria-expanded', 'false'));
                if (!isOpen) {
                    menu.classList.add('show');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.perfil-container, .nav-icon-btn, .dropdown-custom')) {
                document.querySelectorAll('.popup-menu').forEach(menu => menu.classList.remove('show'));
                document.querySelectorAll('[aria-expanded]').forEach(b => b.setAttribute('aria-expanded', 'false'));
            }
        });

        // ---------- Helper de fetch com tratamento de erro comum ----------
        async function fetchJSON(url, options = {}) {
            try {
                const res = await fetch(url, options);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return await res.json();
            } catch (err) {
                console.error(`Falha ao aceder a ${url}:`, err);
                return null;
            }
        }

        // ---------- Limites / assinatura ----------
        fetchJSON(`assets/ajax/get_company_limits.php?company_id=${encodeURIComponent(companyId)}`)
            .then(resp => {
                const subInfo = document.getElementById('subInfo');
                if (!subInfo) return;
                if (!resp || !resp.success) {
                    subInfo.textContent = 'Não foi possível carregar os limites.';
                    return;
                }
                const exp = resp.plan_expires_at ?
                    new Date(resp.plan_expires_at + 'T00:00:00').toLocaleDateString('pt-PT') :
                    '-';
                const days = resp.days_left ?? '-';
                subInfo.textContent = `${resp.plan_name} • vence em ${exp} • ${days} dias restantes`;

                const renewLink = document.getElementById('btnRenewFromIndex');
                if (renewLink) renewLink.href = `subscription.php?company_id=${encodeURIComponent(resp.company_id)}`;
            });

        // ---------- Dados da empresa (regime de IVA etc.) ----------
        fetchJSON(`assets/ajax/company_data.php?company_id=${encodeURIComponent(companyId)}`)
            .then(resp => {
                if (!resp || !resp.success || !resp.data) return;
                // sessionStorage é preferível a localStorage aqui: some com o fecho da aba
                sessionStorage.setItem('vat_regime', JSON.stringify(resp.data.vat_regime));
            });

        // ---------- Troca de empresa ----------
        async function trocarEmpresa(empresaId, name, registrationNumber, email) {
            const body = new URLSearchParams({
                company_id: empresaId,
                name_company: name,
                registration_number: registrationNumber,
                email_company: email,
                csrf_token: csrfToken,
            });

            const data = await fetchJSON('assets/ajax/change_company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body,
            });

            if (data && data.success) {
                location.reload();
            } else {
                alert('Erro ao trocar de empresa.');
            }
        }

        async function carregarEmpresas() {
            const empresas = await fetchJSON('assets/ajax/get_companies.php');
            const dropdown = document.getElementById('empresaDropdownMenu');
            if (!dropdown) return;

            dropdown.innerHTML = '';

            if (!Array.isArray(empresas) || empresas.length === 0) {
                dropdown.innerHTML = '<li><small class="d-block px-3 py-2 text-muted">Nenhuma empresa encontrada</small></li>';
                return;
            }

            empresas.forEach(empresa => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = '#';
                a.className = 'dropdown-item';
                a.textContent = empresa.name; // textContent evita XSS via innerHTML

                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    trocarEmpresa(empresa.id, empresa.name, empresa.registration_number, empresa.email);
                });

                li.appendChild(a);
                dropdown.appendChild(li);
            });
        }

        // ---------- Notificações ----------
        function formatDateTimeToBrazilian(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            if (Number.isNaN(d.getTime())) return iso;
            return d.toLocaleString('pt-BR');
        }

        let isLoadingNotifications = false;

        async function ajaxLoadNotifications() {
            if (isLoadingNotifications) return; // evita corridas por chamadas concorrentes
            isLoadingNotifications = true;

            const countEl = document.getElementById('notificationCount');
            const listEl = document.getElementById('notificationList');
            if (!countEl || !listEl) {
                isLoadingNotifications = false;
                return;
            }

            try {
                // Dispara as duas verificações em paralelo; uma falhar não deve
                // impedir a outra nem impedir a leitura das notificações já existentes.
                const triggers = await Promise.allSettled([
                    fetch('index/ajax/data_user_notify.php'),
                    fetch('index/ajax/data_company_notify.php'),
                ]);

                triggers.forEach((result, i) => {
                    const endpoint = i === 0 ? 'data_user_notify.php' : 'data_company_notify.php';
                    if (result.status === 'rejected') {
                        console.warn(`Falha ao gerar notificações (${endpoint}):`, result.reason);
                    } else if (!result.value.ok) {
                        console.warn(`${endpoint} respondeu com status ${result.value.status}`);
                    }
                });

                const data = await fetchJSON('index/ajax/get_notifications.php');

                if (data?.success === false) {
                    listEl.innerHTML = `<small class="d-block px-3 py-2 text-danger">Erro: ${escapeHtml(data.error ?? 'Erro desconhecido')}</small>`;
                    countEl.hidden = true;
                    return;
                }

                const count = Number(data?.count) || 0;
                const notifications = Array.isArray(data?.notifications) ? data.notifications : [];

                countEl.textContent = count > 99 ? '99+' : String(count);
                countEl.hidden = count === 0;

                listEl.innerHTML = '';

                if (notifications.length === 0) {
                    listEl.innerHTML = '<small class="d-block px-3 py-2 text-muted">Nenhuma notificação</small>';
                    return;
                }

                const fragment = document.createDocumentFragment();

                notifications.forEach(n => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item notification-item';

                    const title = document.createElement('small');
                    title.className = 'd-block fw-bold';
                    title.textContent = n.title ?? '';
                    title.style.textWrap = 'wrap';

                    const message = document.createElement('small');
                    message.className = 'd-block';
                    message.textContent = n.message ?? '';
                    message.style.fontSize = '0.85rem';
                    message.style.color = '#555';
                    message.style.textWrap = 'wrap';

                    const date = document.createElement('small');
                    date.className = 'text-muted';
                    date.textContent = formatDateTimeToBrazilian(n.created_at);

                    item.append(title, message, date);
                    fragment.appendChild(item);
                });

                listEl.appendChild(fragment);
            } catch (err) {
                console.error('Erro ao carregar notificações:', err);
                listEl.innerHTML = '<small class="d-block px-3 py-2 text-danger">Não foi possível carregar as notificações.</small>';
                countEl.hidden = true;
            } finally {
                isLoadingNotifications = false;
            }
        }

        // ---------- Assistente IA (alert_logs) ----------
        let isLoadingAIInsights = false;

        async function ajaxLoadAIInsights() {
            if (isLoadingAIInsights) return;
            isLoadingAIInsights = true;

            const btn = document.getElementById('aiAgentBtn');
            const countEl = document.getElementById('aiInsightCount');
            const listEl = document.getElementById('aiInsightList');
            if (!btn || !countEl || !listEl) {
                isLoadingAIInsights = false;
                return;
            }

            try {
                // Endpoint real: GET /api/alert-logs (robotController.getAlertLogs)
                // Devolve o array de AlertLog diretamente, sem wrapper { success, ... }.
                const insights = await fetchJSON(`http://localhost:3000/api/alert-logs?company_id=${encodeURIComponent(companyId)}`);

                if (!Array.isArray(insights)) {
                    countEl.hidden = true;
                    btn.classList.remove('has-alert');
                    return;
                }

                const count = insights.length;

                countEl.textContent = count > 99 ? '99+' : String(count);
                countEl.hidden = count === 0;
                btn.classList.toggle('has-alert', count > 0);

                listEl.innerHTML = '';

                if (insights.length === 0) {
                    listEl.innerHTML = '<small class="d-block px-3 py-2 text-muted">Sem novidades por agora</small>';
                    return;
                }

                const fragment = document.createDocumentFragment();

                insights.forEach(n => {
                    const item = document.createElement('div');
                    item.className = 'dropdown-item ai-insight-item';

                    const title = document.createElement('small');
                    title.className = 'd-block fw-bold';
                    title.textContent = n.title ?? '';
                    title.style.textWrap = 'wrap';

                    const message = document.createElement('small');
                    message.className = 'd-block';
                    message.textContent = n.message ?? '';
                    message.style.fontSize = '0.85rem';
                    message.style.color = '#555';
                    message.style.textWrap = 'wrap';

                    const date = document.createElement('small');
                    date.className = 'text-muted';
                    date.textContent = formatDateTimeToBrazilian(n.created_at);

                    item.append(title, message, date);
                    fragment.appendChild(item);
                });

                listEl.appendChild(fragment);
            } catch (err) {
                console.error('Erro ao carregar insights do agente IA:', err);
                countEl.hidden = true;
                btn.classList.remove('has-alert');
            } finally {
                isLoadingAIInsights = false;
            }
        }

        // Helper simples para sanitizar texto inserido via innerHTML (mensagens de erro)
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', () => {
            carregarEmpresas();
            ajaxLoadNotifications();
            ajaxLoadAIInsights();
            setInterval(ajaxLoadNotifications, 60000);
            setInterval(ajaxLoadAIInsights, 60000);
        });
    })();
</script>