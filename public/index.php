<html>
<?php
require_once '../app/views/layout_creation.php';
?>

<!-- OWL CAROUSEL -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

<style>
    .tags {
        background: none;
        border-radius: 5px;
    }

    .tag-link {
        background: #fff;
        border: none;
        box-shadow: 1px 1px 1px 1px 10px #f5f5f5;
    }

    .tag-link:hover {
        background: #fff;
    }

    .tag-link.active {
        background: #007abd;
        color: #fff;
        font-weight: bold;
    }

    .tag-wrapper {
        position: relative;
    }

    .tag-content {
        position: absolute;
        width: 100%;
        top: 0;
        left: 0;

        opacity: 0;
        transform: translateY(15px);
        filter: blur(6px);

        transition:
            opacity 0.4s ease,
            transform 0.4s ease,
            filter 0.4s ease;

        pointer-events: none;
    }

    /* Estado ativo */
    .tag-content.active {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);

        pointer-events: auto;
        position: relative;
    }

    /* Estado saindo (extra pra suavizar saída) */
    .tag-content.exit {
        opacity: 0;
        transform: translateY(-10px);
        filter: blur(4px);
    }

    .icon-box {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 12px;
        font-weight: 600;
    }

    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;

        padding: 10px;
        border-radius: 10px;

        transition: 0.2s;
    }

    .list-item:hover {
        background: #f8f9fa;
    }

    .icon-box {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .card-custom {
        border-radius: 16px;
        border: 1px solid #eee;
        transition: 0.3s;
    }

    .card-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #007abd;
    }

    .icon-box i {
        color: #fff !important;
    }

    .small-text {
        font-size: 13px;
        color: #6c757d;
    }

    .hover-row:hover {
        background: #f8f9fa;
    }

    #clients {
        height: 260px !important;
        overflow-y: hidden;
        margin-top: 5px;
    }

    #topClients {
        overflow-y: scroll;
        height: 100%;
    }

    .text-green {
        color: #28a745 !important;
    }

    .text-danger {
        color: red !important;
    }

    #low_stock_list,
    #purchase_list {
        max-height: 300px;
        overflow-y: auto;
    }

    .h-title {
        color: #007abd;
        border: 2px solid #007bbd41;
        border-radius: 8px;
        padding: 5px;
        font-weight: bolder;
        width: auto;
        margin: 0;
        font-size: 0.9rem;
    }
</style>

<body>
    <main style="background: #f7f7f7;">
        <div>
            <!-- Gráficos -->
            <div class="container-fluid">
                <div class="row g-4 mt-5">
                    <div class="col-12">
                        <div class="d-flex col-4 col-sm-12">
                            <div class="tags">
                                <a href="#" data-tag="sell" class="tag-link active btn">Vendas</a>
                                <a href="#" data-tag="stock" class="tag-link btn">Stock</a>
                                <a href="#" data-tag="rh" class="tag-link btn">Recursos Humanos</a>
                            </div>
                        </div>
                    </div>

                    <div id="tag_contents p-0 col-12">

                        <!-- Sessao Gestao de Vendas -->
                        <div class="col-12 tag-content active" data-tag-content="sell">
                            <!-- ================== CARDS ================== -->
                            <div class="row g-3 mb-4">

                                <div class="col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="d-flex justify-content-between">
                                            <div class="icon-box"><i class="bi bi-coin text-primary"></i></div>
                                        </div>
                                        <h4 class="mt-3 fw-semibold" id="trimestral_volume">AOA 98.000</h4>
                                        <div class="d-flex justify-content-between">
                                            <span class="small-text">Volume de Vendas Trimestral</span>
                                            <span id="trimestral_volume_dif" class="small"><i class="bi bi-arrow-up-right"></i>+12%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box"><i class="bi bi-graph-up text-success"></i></div>
                                        <h4 class="mt-3 fw-semibold" id="month_average">€32.666</h4>
                                        <div class="d-flex justify-content-between">
                                            <span class="small-text">Média Mensal de Vendas</span>
                                            <span id="month_average_dif" class="small"><i class="bi bi-arrow-up-right"></i>0%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box"><i class="bi bi-people text-primary"></i></div>
                                        <h4 class="mt-3 fw-semibold" id="total_customer">64</h4>
                                        <div class="d-flex justify-content-between">
                                            <span class="small-text">Total de Clientes Activos</span>
                                            <span id="total_customer_dif" class="small"><i class="bi bi-arrow-up-right"></i>0%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box"><i class="bi bi-file-earmark-text text-primary"></i></div>
                                        <h4 class="mt-3 fw-semibold" id="total_docs">157</h4>
                                        <div class="d-flex justify-content-between">
                                            <span class="small-text">Documentos Processados</span>
                                            <span id="total_doc_dif" class="small"><i class="bi bi-arrow-up-right"></i> 0</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- ================== CHART + CLIENTES ================== -->
                            <div class="row g-3 mb-4 d-flex">

                                <div class="col-lg-8">
                                    <div class="card card-custom p-4" id="chart-card">
                                        <div class="d-flex justify-content-between mb-3">
                                            <h6 class="h-title"><i class="bi bi-graph-up"></i> Evolução Trimestral</h6>
                                            <small class="text-muted d-none">Jan – Mar 2026</small>
                                        </div>
                                        <canvas id="chart"></canvas>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card card-custom p-3">
                                        <div class="d-flex justify-content-between mb-3">
                                            <h6 class="h-title"><i class="bi bi-file-earmark-text"></i> Últimas Facturas</h6>
                                            <a href="list_invoices.php" class="small text-primary">Ver todas <i class="bi bi-chevron-right"></i></a>
                                        </div>

                                        <div class="row g-3">
                                            <div class="card p-3">

                                                <div class="owl-carousel invoice-carousel" id="invoiceCarousel">

                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                    <div class="card card-custom p-3" id="clients">
                                        <div class="d-flex justify-content-between mb-3">
                                            <h6 class="mb-3 h-title"><i class="bi bi-people"></i> Principais Clientes</h6>
                                        </div>

                                        <div class="col-12" id="topClients">

                                        </div>

                                        <!-- Conteúdo javascript -->
                                    </div>

                                </div>

                            </div>

                            <!-- ================== FACTURAS POR CLIENTE ================== -->


                        </div>


                        <!-- Sessao Gestao de RH -->
                        <div class="col-12 tag-content" data-tag-content="rh">
                            <!-- CARDS -->
                            <div class="row g-3">

                                <!-- CARD 1 -->
                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3 p-3">
                                        <div class="d-flex justify-content-between">
                                            <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>

                                        <h4 class="fw-bold mt-3" id="rh_total_employees">47</h4>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Total Funcionários Activos</small>
                                            <!-- <span id="rh_total_employees_dif" class="text-primary small fw-semibold"><i class="bi bi-arrow-up-right"></i> +2</span> -->
                                        </div>
                                    </div>
                                </div>

                                <!-- CARD 2 -->
                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>

                                        <h4 class="fw-bold mt-3" id="rh_total_salary">€62.300</h4>
                                        <small class="text-muted">Folha Salárial Mensal</small>
                                    </div>
                                </div>

                                <!-- CARD 3 -->
                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>

                                        <h4 class="fw-bold mt-3" id="rh_pending_vacations">4</h4>
                                        <small class="text-muted">Pedido de Férias Pendentes</small>
                                    </div>
                                </div>

                                <!-- CARD 4 -->
                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person-x"></i>
                                        </div>

                                        <h4 class="fw-bold mt-3" id="rh_absences">12</h4>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Faltas no Mês</small>
                                            <span id="rh_absences_month_dif" class="small fw-semibold"><i class="bi bi-arrow-up-right"></i> -2</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- LISTAS -->
                            <div class="row g-3 mt-2">

                                <!-- FÉRIAS -->
                                <div class="col-12 col-lg-6">
                                    <div class="card card-custom p-3">
                                        <div class="d-flex">
                                            <h6 class="fw-semibold mb-3 h-title"><i class="bi bi-list"></i> Lista de Férias</h6>
                                        </div>

                                        <div class="col-12" id="rh_vacations_list">

                                        </div>

                                    </div>
                                </div>

                                <!-- FALTAS -->
                                <div class="col-12 col-lg-6">
                                    <div class="card card-custom p-3">
                                        <div class="d-flex">
                                            <h6 class="fw-semibold mb-3 h-title"><i class="bi bi-list"></i> Funcionários com Mais Faltas</h6>
                                        </div>
                                        <div class="col-12" id="rh_absences_list"></div>

                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Sessao Gestao de Stock -->
                        <div class="col-12 tag-content" data-tag-content="stock">
                            <!-- CARDS -->
                            <div class="row g-3">

                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <h4 class="fw-bold mt-3" id="kpi-depots">3</h4>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted ">Nº de Depósitos</small>
                                            <span id="kpi-depots-growth" class="text-primary small">+14 <i class="bi bi-arrow-up-right"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <h4 class="fw-bold mt-3" id="kpi-products">957</h4>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Total Produtos</small>
                                            <span id="kpi-products-growth" class="text-primary small">+14 <i class="bi bi-arrow-up-right"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                        <h4 class="fw-bold mt-3" id="kpi-total-value">€257.000</h4>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">Valor Total do Stock</small>
                                            <span id="kpi-total-value-growth" class="text-primary small">+3.1% <i class="bi bi-arrow-up-right"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div class="card card-custom p-3">
                                        <div class="icon-box rounded-3 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </div>
                                        <h4 class="fw-bold mt-3" id="kpi-low-stock">4</h4>
                                        <small class="text-muted">Produtos com Stock Baixo</small>
                                    </div>
                                </div>

                            </div>

                            <!-- DEPÓSITOS -->
                            <div class="card mt-4 card-custom p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-semibold h-title"><i class="bi bi-geo-alt"></i>Principais Depósitos</h6>
                                </div>

                                <div class="row g-3" id="depots_list">

                                </div>
                            </div>

                            <!-- LISTAS -->
                            <div class="row g-3 mt-2">

                                <!-- STOCK BAIXO -->
                                <div class="col-12 col-lg-6">
                                    <div class="card card-custom p-3">
                                        <div class="d-flex">
                                            <h6 class="fw-semibold mb-3 h-title"><i class="bi bi-exclamation-triangle"></i> Produtos com Stock Baixo</h6>
                                        </div>
                                        <div class="col-12" id="low_stock_list">

                                        </div>
                                    </div>
                                </div>

                                <!-- COMPRAS -->
                                <div class="col-12 col-lg-6">
                                    <div class="card card-custom p-3">
                                        <div class="d-flex justify-content-between mb-3">
                                            <h6 class="fw-semibold h-title"><i class="bi bi-list"></i> Lista de Compras</h6>
                                            <small class="text-muted">Últimos 5 itens</small>
                                        </div>

                                        <div class="col-12" id="purchase_list">
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
    </main>
</body>

<?php
require_once '../app/views/footer.php';
?>
<!-- jQuery (necessário para plugins como OwlCarousel e Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- OwlCarousel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- FontAwesome -->
<script src="../vendor/fontawesome-free-5.15.4-web/js/all.js"></script>

<!-- Seus scripts -->
<script src="./assets/js/script.js"></script>
<!-- ================== CHART JS PRO ================== -->
<script>
    /* =============================
   🔹 INIT VARS
============================= */
    const userEl = document.getElementById("user_id");
    const companyEl = document.getElementById("company_id");

    const user_id = userEl?.value || null;
    const company_id = companyEl?.value || null;

    let chartInstance = null;
    let carousel = null;

    /* =============================
       🔹 HELPERS
    ============================= */
    function formatCurrency(value, currency = "AOA", locale = "pt-AO") {
        return new Intl.NumberFormat(locale, {
            style: "currency",
            currency
        }).format(Number(value) || 0);
    }

    function getBadge(status) {
        switch (status) {
            case 'pago':
                return '<span class="badge bg-success">Pago</span>';
            case 'parcial':
                return '<span class="badge bg-info">Parcial</span>';
            default:
                return '<span class="badge bg-warning text-dark">Pendente</span>';
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('pt-PT', {
            day: '2-digit',
            month: 'short'
        });
    }

    function getInitials(name = "") {
        return name
            .split(' ')
            .map(n => n[0])
            .slice(0, 2)
            .join('')
            .toUpperCase();
    }

    function formatDateRange(start, end) {
        const s = new Date(start);
        const e = new Date(end);

        const opt = {
            day: '2-digit',
            month: 'short'
        };
        return `${s.toLocaleDateString('pt-PT', opt)}–${e.toLocaleDateString('pt-PT', opt)}`;
    }

    /* =============================
       🔹 TABS
    ============================= */
    const tagsLink = document.querySelectorAll(".tag-link");
    const tagsContent = document.querySelectorAll(".tag-content");

    tagsLink.forEach(t => {
        t.addEventListener("click", () => {
            tagsLink.forEach(x => x.classList.remove("active"));
            t.classList.add("active");

            showAndHideTag(t.getAttribute("data-tag"));
        });
    });

    function showAndHideTag(tag) {
        tagsContent.forEach(tgc => {
            const attr = tgc.getAttribute("data-tag-content");

            if (attr === tag) {
                tgc.classList.add("active");
                tgc.classList.remove("exit");
            } else {
                if (tgc.classList.contains("active")) {
                    tgc.classList.add("exit");

                    setTimeout(() => {
                        tgc.classList.remove("active", "exit");
                    }, 300);
                }
            }
        });
    }

    /* =============================
       🔹 LOAD INVOICES
    ============================= */
    function loadInvoices() {
        $.ajax({
            url: 'invoices/ajax/get_invoices_feed.php',
            method: 'GET',
            dataType: 'json',
            success: function(res) {

                if (!res?.success || !Array.isArray(res.data)) return;

                let html = '';

                res.data.forEach(inv => {
                    html += `
                    <div class="item">
                        <div class="card p-2 hover-row">
                            <div class="d-flex justify-content-between">
                                <small class="text-primary">${inv.reference ?? ''}</small>
                                ${getBadge(inv.status)}
                            </div>
                            <strong>${inv.client_name ?? ''}</strong>
                            <div class="d-flex justify-content-between">
                                <small>${formatDate(inv.issue_date)}</small>
                                <strong class="text-success">
                                    ${formatCurrency(inv.final_total)}
                                </strong>
                            </div>
                        </div>
                    </div>`;
                });

                if (carousel) {
                    try {
                        carousel.trigger('destroy.owl.carousel');
                    } catch (e) {}
                    $('#invoiceCarousel').html('');
                }

                $('#invoiceCarousel').html(html);

                carousel = $('#invoiceCarousel').owlCarousel({
                    items: 1.2,
                    margin: 10,
                    loop: true,
                    autoplay: true,
                    autoplayTimeout: 3000,
                    autoplayHoverPause: true,
                    dots: false,
                    nav: false
                });
            },
            error: function(xhr) {
                console.error("AJAX ERROR invoices:", xhr.responseText);
            }
        });
    }

    /* =============================
       🔹 KPI DATA
    ============================= */
    function getInsightsNumber() {
        if (!company_id || !user_id) return;

        $.ajax({
            url: `index/ajax/get_sell_insights.php`,
            method: 'GET',
            data: {
                company_id,
                user_id
            },
            dataType: 'json',

            success: function(res) {
                if (res?.success) renderNumbers(res.data);
            },
            error: function(xhr) {
                console.error("AJAX ERROR KPI:", xhr.responseText);
            }
        });
    }

    function renderNumbers(data) {
        const kpis = data?.kpis || {};

        $("#trimestral_volume").text(formatCurrency(kpis.volume_trimestral));
        $("#month_average").text(formatCurrency(kpis.media_mensal));
        $("#month_average_dif").text(0);
        $("#total_customer").text(kpis.clientes || 0);
        $("#total_docs").text(kpis.documentos || 0);
        setDif("trimestral_volume_dif", kpis.crescimento || 0);
        setDif("month_average_dif", kpis.media_mensal_dif || 0);
        setDif("total_customer_dif", kpis.crescimento_clientes || 0);
        setDif("total_doc_dif", kpis.crescimento_documentos || 0);

        function setDif(id, value) {
            const el = document.getElementById(id);
            if (!el) return;

            el.innerHTML = value > 0 ? `<i class="bi bi-arrow-up-right"></i>${value}${(id === 'total_customer_dif' || id === 'total_doc_dif') ? '' : '%'}` : `<i class="bi bi-arrow-down-right"></i>${value}%`;
            el.className = value > 0 ? "text-success" : "text-danger";
        }

        renderGraphics(data);
        renderTopClients(data);
    }

    /* =============================
       🔹 CHART
    ============================= */
    function renderGraphics(apiData) {
        const evolucao = apiData?.evolucao || [];

        const getLast3Months = () => {
            const meses = [];
            const hoje = new Date();

            for (let i = 3; i >= 0; i--) {
                const d = new Date(hoje.getFullYear(), hoje.getMonth() - i, 1);
                meses.push(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`);
            }
            return meses;
        };

        const nomes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        const map = {};
        evolucao.forEach(i => map[i.mes] = Number(i.total));

        const meses = getLast3Months();
        const labels = meses.map(m => nomes[parseInt(m.split('-')[1]) - 1]);
        const valores = meses.map(m => map[m] || 0);

        const canvas = document.getElementById('chart');
        if (!canvas) return;

        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Receita',
                    data: valores,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    /* =============================
       🔹 TOP CLIENTS
    ============================= */
    function renderTopClients(apiData) {
        const el = document.getElementById("topClients");
        if (!el) return;

        const list = apiData?.top_clients || [];

        el.innerHTML = list.map(c => `
        <div class="hover-row d-flex justify-content-between p-2 rounded">
            <div>
                <strong>${c.cliente ?? ''}</strong><br>
                <small class="text-muted">${c.total_faturas || 0} facturas</small>
            </div>
            <span class="text-success fw-semibold">
                ${formatCurrency(c.total_faturado)}
            </span>
        </div>
    `).join('');
    }

    /* =============================
       🔹 RH MODULE
    ============================= */
    function loadRHData() {

        $.ajax({
            url: `index/ajax/get_hr_insights.php`,
            method: "GET",
            data: {
                company_id
            },
            dataType: "json",

            success: function(res) {
                if (!res?.success) return;

                const data = res.data || {};
                const k = data.kpis || {};

                $("#rh_total_employees").text(k.total_employes || 0);
                $("#rh_total_salary").text(formatCurrency(k.total_salary));
                $("#rh_pending_vacations").text(k.pending_vacations || 0);
                $("#rh_absences").text(k.absences_year || 0);
                $("#rh_absences_month_dif").text(k.absences_year || 0);
                setDif("rh_total_employees_dif", k.increase_employes || 0);
                setDif("rh_total_salary_dif", k.increase_salary || 0);
                setDif("rh_pending_vacations_dif", k.increase_vacations || 0);

                function setDif(id, value) {
                    const el = document.getElementById(id);
                    if (!el) return;

                    el.innerHTML = value > 0 ? `<i class="bi bi-arrow-up-right"></i>${value}%` : `<i class="bi bi-arrow-down-right"></i>${value}%`;
                    el.className = value > 0 ? "text-success" : "text-danger";
                }

                /* Vacations */
                const vacHtml = (data.vacations || []).map(v => {
                    const badgeClass = v.status === 'pendente' ?
                        'bg-warning-subtle text-warning' :
                        'bg-success-subtle text-success';

                    const badgeText = v.status === 'pendente' ? 'Pendente' : 'Aprovadas';

                    return `
                    <div class="list-item d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar bg-primary-subtle text-primary">
                                ${getInitials(v.name)}
                            </div>
                            <div>
                                <div class="fw-medium">${v.name}</div>
                                <small class="text-muted">
                                    ${formatDateRange(v.start_date, v.end_date)}
                                </small>
                                </div>
                                </div>
                                <span class="badge ${badgeClass}">${badgeText}</span>
                    </div>`;
                }).join('');

                $("#rh_vacations_list").html(vacHtml);

                /* Absences */
                const absHtml = (data.top_absences || []).map(a => `
                <div class="list-item d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                <div class="avatar bg-danger-subtle text-danger">
                ${getInitials(a.name)}
                </div>
                <div>
                <div class="fw-medium">${a.name}</div>
                <small class="text-muted">${a.tipo_falta}</small>
                </div>
                </div>
                <span class="fw-bold">${a.total_absences} faltas</span>
                </div>
                `).join('');

                $("#rh_absences_list").html(absHtml);
            },
            error: function(xhr) {
                console.error("RH AJAX ERROR:", xhr.responseText);
            }
        });
    }

    /* ==========================================
          STOCK DASHBOARD
    ========================================== */

    function loadStockDashboard() {

        if (!company_id) return;

        $.ajax({
            url: "index/ajax/get_stock_dashboard.php",
            method: "GET",
            data: {
                company_id
            },
            dataType: "json",

            success: function(res) {

                if (!res || !res.success) {
                    console.warn("Sem dados de stock");
                    return;
                }


                const data = res.data || {};
                const kpis = data.kpis || {};

                console.log(data)

                /* =========================
                   🔹 KPIs
                ========================= */
                document.getElementById("kpi-depots").textContent = kpis.depots ?? 0;
                document.getElementById("kpi-products").textContent = kpis.products ?? 0;
                document.getElementById("kpi-total-value").textContent = formatCurrency(kpis.total_value);
                document.getElementById("kpi-low-stock").textContent = kpis.low_stock ?? 0;

                setDif('kpi-depots-growth', kpis.increase_depots ?? 0);
                setDif('kpi-products-growth', kpis.increase_products ?? 0);
                setDif('kpi-total-value-growth', kpis.increase_total_value.toFixed(2) ?? 0);

                function setDif(id, value) {
                    const el = document.getElementById(id);
                    if (!el) return;

                    el.innerHTML = value > 0 ? `<i class="bi bi-arrow-up-right"></i>${(id === 'kpi-depots-growth' || id === 'kpi-products-growth')  ? '+' : ''}${value}${(id === 'kpi-depots-growth' || id === 'kpi-products-growth') ? '' : '%'}` : `<i class="bi bi-arrow-down-right"></i>${value}%`;
                    el.className = value > 0 ? "text-success" : "text-danger";

                }


                /* =========================
                   🔹 DEPÓSITOS
                ========================= */
                const depositsContainer = document.getElementById("depots_list");

                if (depositsContainer) {
                    let html = "";

                    (data.depots || []).forEach(dep => {
                        html += `
                    <div class="col-12 col-sm-4">
                        <div class="p-3 border rounded-3 depo-item">
                            <h6 class="fw-semibold">${dep.name}</h6>
                            <small class="text-muted">${dep.city ?? ''}</small>

                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">${dep.total_items || 0} itens</small>
                                <span class="fw-bold text-success">
                                    ${formatCurrency(dep.total_value)}
                                </span>
                            </div>

                           <a href="stock-depot.php?depot_id=${dep.id}" class="stretched-link">
                            <small class="text-primary d-block mt-2">
                                Ver detalhes <i class="bi bi-chevron-right"></i>
                            </small>
                            </a>
                        </div>
                    </div>`;
                    });

                    depositsContainer.innerHTML = html || `<small class="text-muted">Sem depósitos</small>`;
                }

                /* =========================
                   🔹 STOCK BAIXO
                ========================= */
                const lowStockContainer = document.getElementById("low_stock_list");

                if (lowStockContainer) {
                    let html = "";

                    (data.low_stock || []).forEach(item => {
                        html += `
                    <div class="list-item">
                        <div>
                            <div class="fw-medium">${item.name}</div>
                            <small class="text-muted">
                                ${item.quantity}/${item.min_quantity}
                            </small>
                        </div>
                        <button class="btn btn-sm text-primary">
                            <i class="bi bi-plus"></i> Lista
                        </button>
                    </div>`;
                    });

                    lowStockContainer.innerHTML = html || `<small class="text-muted">Sem alertas</small>`;
                }

                /* =========================
                   🔹 COMPRAS (SUGESTÃO)
                ========================= */
                const purchaseContainer = document.getElementById("purchase_list");

                if (purchaseContainer) {
                    let html = "";

                    (data.purchases || []).forEach(p => {
                        html += `
                    <div class="list-item">
                        <div>
                            <div class="fw-medium">${p.name}</div>
                            <small class="text-muted">
                                Sugerido: ${p.suggested_qty}
                            </small>
                        </div>
                        <small class="text-muted">Auto</small>
                    </div>`;
                    });

                    purchaseContainer.innerHTML = html || `<small class="text-muted">Sem sugestões</small>`;
                }
            },

            error: function(xhr) {
                console.error("Erro STOCK AJAX:", xhr.responseText);
            }
        });
    }

    /* =============================
       🔹 INIT
    ============================= */
    $(document).ready(function() {

        loadInvoices();
        getInsightsNumber();
        loadRHData();
        loadStockDashboard();

        // ⚠️ cuidado: 10s pode ser pesado em produção
        setInterval(() => {
            getInsightsNumber();
            loadInvoices();
            loadRHData();
            loadStockDashboard();
        }, 10000);
    });
</script>

</html>