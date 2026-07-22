<?php
require_once '../app/helpers/translation.php';
require_once '../app/config/db.php';
require_once '../app/helpers/functions.php';
require_once '../app/helpers/authentication.php';
require_once '../app/views/head.php';

// Página atual (usada pra destacar o item ativo no menu
// e pra permitir requisições AJAX diretas na mesma rota)
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<style>
    .app-wrapper {
        display: flex;
    }

    .main-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Indicador de carregamento entre trocas de página SPA */
    #spa-loading-bar {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        width: 0%;
        background: #007abd;
        z-index: 2000;
        transition: width 0.2s ease;
    }

    #spa-loading-bar.active {
        width: 70%;
    }

    #spa-loading-bar.done {
        width: 100%;
        opacity: 0;
        transition: width 0.2s ease, opacity 0.3s ease 0.2s;
    }
</style>

<div id="spa-loading-bar"></div>

<div class="app-wrapper">

    <!-- SIDEBAR -->
    <?php require_once '../app/views/side.php'; ?>

    <div class="main-wrapper">

        <!-- NAVBAR -->
        <?php require_once '../app/views/nav.php'; ?>

        <!-- CONTEÚDO REAL DA PÁGINA - único main do projeto -->
        <main id="app-main" style="width: 100% !important;" data-page="<?php echo htmlspecialchars($currentPage); ?>">
            <?php
            // Se a própria página que chamou o layout já definiu
            // $content, ele é injetado aqui. Isso permite tanto
            // load normal (F5 / primeira visita) quanto SPA.
            if (isset($content)) {
                echo $content;
            }
            ?>
        </main>
    </div>
</div>

<script src="assets/js/spa-router.js"></script>

<!-- Script central de layout: coordena sidebar + header + main -->
<script>
    const init = () => {
        const sidebar = document.getElementById('sidebar');
        const mainEl = document.querySelector('.main-wrapper');
        const headerEl = document.querySelector('.app-navbar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');

        if (!sidebar) return;

        let overlay = document.querySelector('.overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'overlay';
            document.body.appendChild(overlay);
        }

        function applySidebarState(collapsed) {
            sidebar.classList.toggle('collapsed', collapsed);
            mainEl?.classList.toggle('collapsed', collapsed);
            headerEl?.classList.toggle('collapsed', collapsed);

            // Com a classe já mesclada corretamente no HTML
            // (side.php) e a regra CSS ".sidebar.collapsed .menu-link-icon"
            // já cuidando da ocultação, este loop em JS é apenas
            // um reforço redundante e opcional. Mantido caso você
            // precise de lógica adicional além do display:none.
            const menuIcons = sidebar.querySelectorAll('.menu-link-icon');
            menuIcons.forEach(icon => {
                icon.style.display = collapsed ? 'none' : '';
            });

            const icon = toggleBtn?.querySelector('i, svg');
            if (icon) {
                icon.setAttribute('data-lucide', collapsed ? 'panel-left-open' : 'panel-left-close');
                lucide.createIcons();
            }
        }

        const savedState = localStorage.getItem('sidebarCollapsed') === 'true';
        applySidebarState(savedState);

        toggleBtn?.addEventListener('click', function(e) {
            e.stopPropagation();
            const isCollapsed = sidebar.classList.contains('collapsed');
            applySidebarState(!isCollapsed);
            localStorage.setItem('sidebarCollapsed', !isCollapsed);
        });

        function openMobileMenu() {
            sidebar.classList.add('mobile-active');
            overlay.classList.add('show');
        }

        function closeMobileMenu() {
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('show');
        }

        mobileMenuBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        overlay.addEventListener('click', closeMobileMenu);

        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                closeMobileMenu();
            }
        });

        window.__closeMobileMenu = closeMobileMenu;
    }

    document.addEventListener('DOMContentLoaded', function() {
        init();

        // Marca o item ativo do menu na carga inicial da página
        if (window.SpaRouter) {
            window.SpaRouter.highlightActiveLink();
        }
    });
</script>