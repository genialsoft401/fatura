<style>
    :root {
        --sidebar-bg: #007abd;
        --hover-bg: rgba(255, 255, 255, 0.12);
        --active-bg: rgba(255, 255, 255, 0.18);
        --text: #ffffff;
        --muted: rgba(255, 255, 255, 0.75);
        --border: rgba(255, 255, 255, 0.15);
    }

    body {
        margin: 0;
        font-family: sans-serif;
    }

    /* SIDEBAR */
    .sidebar {
        width: 260px;
        background: var(--sidebar-bg);
        height: 100vh;
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--border);
        position: fixed;
        z-index: 999;
    }

    .brand {
        padding: 14px;
        border-bottom: 1px solid var(--border);
    }

    .brand img {
        width: 120px;
    }

    .nav-section {
        padding: 10px;
        overflow-y: auto;
        flex: 1;
    }

    /* SCROLL */
    .nav-section::-webkit-scrollbar {
        width: 6px;
    }

    .nav-section::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 10px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        color: var(--muted);
        text-decoration: none;
        font-weight: 500;
        transition: .2s;
        cursor: pointer;
        width: 100%;
        border: none;
        background: transparent;
        background: none;
    }

    /* TEXT + ICON */
    .nav-item span {
        display: flex;
        align-items: center;
        gap: 10px;
        color: inherit;
    }

    /* ICON */
    .nav-item i {
        color: inherit;
    }

    /* HOVER */
    .nav-item:hover {
        background: var(--hover-bg);
        color: var(--text);
        transform: translateX(2px);
    }

    /* ACTIVE */
    .nav-item.active {
        background: var(--active-bg);
        color: var(--text);
        font-weight: 600;
    }

    /* ACTIVE BAR */
    .nav-item.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 15%;
        height: 70%;
        width: 3px;
        background: #ffffff;
        border-radius: 3px;
    }

    /* ICON ACTIVE */
    .nav-item.active i {
        color: #ffffff;
    }

    .nav-icon {
        width: 18px;
        height: 18px;
    }

    .section-title {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--muted);
        padding: 12px 12px 6px;
        font-weight: bold;
        margin-top: 10px;
    }

    .submenu {
        overflow: hidden;
        max-height: 0;
        display: block;
        opacity: 0;
        transform: translateY(15px);
        filter: blur(4px);
        pointer-events: none;
        transition:
            max-height 0.4s ease,
            opacity 0.4s ease,
            transform 0.4s ease,
            filter 0.4s ease;
        padding-left: 12px;
    }

    .submenu.open {
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
        pointer-events: auto;
    }


    /* SUBMENU ITEMS */
    .submenu a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        color: var(--muted);
        text-decoration: none;
        font-size: 14px;
        transition: .2s;
    }

    /* HOVER SUBMENU */
    .submenu a:hover {
        background: var(--hover-bg);
        color: var(--text);
    }

    /* ICON SIZE */
    .nav-section .submenu svg {
        width: 15px !important;
        /* height: 15px !important; */
    }


    .nav-item i:last-child {
        transition: transform .3s;
    }

    .nav-item.active i:last-child {
        transform: rotate(180deg);
    }

    .footer {
        padding: 12px;
    }

    .footer .above {
        border-top: 1px solid var(--border);
    }

    .bg-plan {
        background: rgba(255, 255, 255, 0.15) !important;
    }
</style>

<div class="sidebar">
    <div class="brand">
        <a href="index.php">
            <img src="assets/img/logo/BXpert2-Branca.png" alt="logo">
        </a>
    </div>
    <div class="nav-section">
        <br>

        <a href="index.php" class="nav-item" data-link><span><i data-lucide="layout-dashboard" class="nav-icon"></i> Painel de contole</span></a>

        <div class="section-title">Gestão</div>

        <button class="nav-item" data-submenu="#clientes">
            <span><i data-lucide="building-2"></i> Empresa/Cliente</span>
            <i class="text-white" data-lucide="chevron-down"></i>
        </button>
        <div class="submenu" id="clientes">
            <a href="register_contact.php" data-link><i data-lucide="plus"></i> Adicionar Cliente/Empresa</a>
            <a href="contacts.php" data-link><i data-lucide="list"></i> Lista de Clientes/Empresas</a>
        </div>

        <button class="nav-item" data-submenu="#produtos">
            <span><i data-lucide="package"></i> Produtos/Serviços</span>
            <i class="text-white" data-lucide="chevron-down"></i>
        </button>
        <div class="submenu" id="produtos">
            <a href="#" data-bs-toggle="modal" data-bs-target="#itemModal">
                <i class="text-white" data-lucide="plus"></i> Adicionar
            </a>
            <a href="items.php" data-link> <i data-lucide="list"></i> Meus Itens </a>
        </div>

        <div class="section-title">Operações</div>

        <button class="nav-item" data-submenu="#vendas">
            <span><i data-lucide="receipt"></i> Vendas</span>
            <i class="text-white" data-lucide="chevron-down"></i>
        </button>

        <div class="submenu" id="vendas">

            <button class="nav-item" data-submenu="#proformas">
                <span><i data-lucide="file-text"></i> Proformas</span>
                <i class="text-white" data-lucide="chevron-down"></i>
            </button>
            <div class="submenu" id="proformas">
                <a href="create_proform.php" data-link><i data-lucide="plus"></i> Emitir</a>
                <a href="proformas.php" data-link><i data-lucide="list"></i> Listar</a>
            </div>

            <button class="nav-item" data-submenu="#facturas">
                <span><i data-lucide="file-check"></i> Facturas</span>
                <i class="text-white" data-lucide="chevron-down"></i>
            </button>
            <div class="submenu" id="facturas">
                <a href="create_invoices.php" data-link><i data-lucide="plus"></i> Emitir</a>
                <a href="list_invoices.php" data-link><i data-lucide="list"></i> Listar</a>
            </div>

        </div>

        <!-- <button class="nav-item" data-submenu="#stock">
            <span><i data-lucide="boxes"></i> Stock</span>
            <i class="text-white" data-lucide="chevron-down"></i>
        </button>
        <div class="submenu" id="stock">
            <a href="stock.php" data-link><i data-lucide="database"></i> Inventário</a>
            <a href="purchases.php" data-link><i data-lucide="shopping-cart"></i> Compras</a>
        </div> -->

        <button class="nav-item" data-submenu="#rh">
            <span><i data-lucide="users"></i> Recursos Humanos</span>
            <i class="text-white" data-lucide="chevron-down"></i>
        </button>
        <div class="submenu" id="rh">
            <a href="employees.php" data-link><i data-lucide="users"></i> Funcionários</a>
            <a href="ponto.php" data-link><i data-lucide="clock"></i> Registro de Pontos</a>
            <a href="vacations.php" data-link><i data-lucide="calendar"></i> Férias / Licenças</a>
            <a href="positions.php" data-link><i data-lucide="briefcase"></i> Cargos / Salários</a>
            <a href="payroll.php" data-link><i data-lucide="file-text"></i> Folha de Pagamento</a>
        </div>

    </div>

    <div class="footer">

        <a href="list_companies.php" class="nav-item">
            <span><i data-lucide="settings"></i> Definições</span>
        </a>

        <a href="subscription.php" class="nav-item">
            <span><i data-lucide="credit-card"></i> Meu Plano</span>
        </a>

        <a href="help.php" target="_blank" class="nav-item">
            <span><i data-lucide="help-circle"></i> Ajuda</span>
        </a>

        <div class="col-12 above">
            <div class="card border-0 rounded-xl bg-plan p-3">
                <!-- Informações do Plano -->
                <div class="d-flex justify-content-between align-items-center align-content-center flex-wrap gap-3 mb-2">
                    <div>
                        <h5 class="mb-1 fw-bolder text-white" style="font-size: 12pt;">Plano &nbsp;</h5>
                        <div id="subInfo" class="text-white" style="font-size:0.85rem;">Carregando...</div>
                    </div>
                    <a href="subscription.php" class="btn bg-light text-dark w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                        Renovar
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="assets/js/lucide.js"></script>
<script>
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

    lucide.createIcons();

    document.querySelectorAll('.nav-item[data-submenu]').forEach(button => {
        button.addEventListener('click', e => {
            e.stopPropagation();

            const submenu = document.querySelector(button.dataset.submenu);
            if (!submenu) return;

            const isOpen = submenu.classList.contains('open');

            // Fecha todos os submenus irmãos
            const parent = button.parentElement;
            Array.from(parent.children).forEach(el => {
                if (el.classList.contains('submenu') && el !== submenu) {
                    el.classList.remove('open');
                }
                if (el.classList.contains('nav-item') && el !== button) {
                    el.classList.remove('active');
                }
            });

            // Toggle submenu atual
            if (isOpen) {
                submenu.classList.remove('open');
                button.classList.remove('active');
            } else {
                submenu.classList.add('open');
                button.classList.add('active');
            }
        });
    });

    // Fecha tudo ao clicar fora
    document.addEventListener('click', e => {
        document.querySelectorAll('.submenu').forEach(sm => sm.classList.remove('open'));
        document.querySelectorAll('.nav-item.active').forEach(btn => btn.classList.remove('active'));
    });
</script>