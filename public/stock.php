<?php
require_once '../app/views/layout_creation.php';
?>

<!-- =========================
     SCRIPTS BASE (ORDEM CORRETA)
========================= -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<main>

    <style>
        .card-custom {
            border-radius: 12px;
            transition: 0.3s;
        }

        .card-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .icon-box {
            background: rgba(13, 110, 253, 0.1);
            padding: 5px;
            border-radius: 10px;
            font-size: x-large;
            height: 45px;
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="container py-4 mt-5">

        <div class="d-flex justify-content-between mb-4">
            <div>
                <h3>Inventário / Armazém</h3>
                <small class="text-muted">Gestão de stocks</small>
            </div>

            <button class="btn btn-primary" id="createStock">
                Novo Depósito
            </button>
        </div>

        <div class="row g-4" id="stockContainer">
            <h1>Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita facere impedit velit magnam quam vitae magni deleniti molestiae, quisquam dolores in recusandae minima suscipit! Neque veniam praesentium consectetur! Libero, amet!</h1>
        </div>

    </div>

</main>

<!-- ================= MODAIS ================= -->

<!-- NOVO -->
<!-- <div class="modal fade" id="createStock">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    
                    </div>
                </div>
            </div> -->

<!-- VER -->
<div class="modal fade" id="modalVer">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Produtos</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Lista de produtos aqui...
            </div>
        </div>
    </div>
</div>

<!-- INSIGHTS -->
<div class="modal fade" id="modalInsights">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Insights</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <canvas id="pieChart" class="mb-4"></canvas>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- EDITAR -->
<div class="modal fade" id="modalEditar">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Editar</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input class="form-control mb-2" id="editNome">
                <input class="form-control mb-2" id="editDesc">
                <input class="form-control" id="editLocal">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- DELETE -->
<div class="modal fade" id="modalDelete">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Confirmar</h5>
            </div>
            <div class="modal-body">
                Deseja eliminar este depósito?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="./assets/js/lucide.js"></script>
<!-- <script src="./stock/stock.js"></script> -->

<script>
    const formatCurrencySafe = (val) => {
        const number = parseFloat(val);
        if (isNaN(number)) return "0,00";
        return `${number.toLocaleString("pt-AO", { minimumFractionDigits: 2 })}`;
    };

    /* =========================
   GLOBAL STATE
========================= */
    let stocksData = [];

    /* =========================
       INIT
    ========================= */
    document.addEventListener("DOMContentLoaded", () => {
        initSelect2();
        carregarEstoques();
        initEvents();
    });

    /* =========================
       FETCH STOCKS (ROBUSTO)
    ========================= */
    async function carregarEstoques() {

        const container = document.getElementById("stockContainer");
        if (!container) return;

        try {

            const res = await fetch("index/ajax/fetch_stocks.php");

            const text = await res.text();

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Resposta inválida:", text);
                return;
            }

            stocksData = data?.data || [];

            container.innerHTML = "";

            if (!stocksData.length) {
                container.innerHTML = `<p class="text-muted">Nenhum stock encontrado</p>`;
                return;
            }

            const fragment = document.createDocumentFragment();

            stocksData.forEach((d, i) => {

                const col = document.createElement("div");
                col.className = "col-md-6";

                col.innerHTML = `
            <div class="card card-custom p-3">

                <div class="d-flex justify-content-between mb-3">

                    <div class="d-flex gap-3">
                        <div class="icon-box">
                            <i class="bi bi-${d.icon || 'box'} text-info"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">${d.name || ''}</h6>
                            <small class="text-muted">${d.description || ''}</small>
                        </div>
                    </div>

                    <div class="d-flex gap-1">

                        <a class="btn btn-sm btn-light text-info"
                           href="stock_view.php?id=${d.id}">
                           👁
                        </a>

                        <button class="btn btn-sm btn-light text-success btn-edit"
                                data-index="${i}">
                            ✏️
                        </button>

                        <button class="btn btn-sm btn-light text-danger btn-delete"
                                data-id="${d.id}">
                            🗑
                        </button>

                    </div>

                </div>

                <small class="text-muted">
                    📍 ${d.city || ''}
                </small>

                <hr>

                <div class="d-flex justify-content-between">
                    <div>
                        <small>Itens</small>
                        <div class="fw-bold">${d.total_itens || 0}</div>
                    </div>

                    <div class="text-end">
                        <small>Valor</small>
                        <div class="fw-bold text-success">
                            ${formatCurrencySafe(d.valor_total)}
                        </div>
                    </div>
                </div>

            </div>
            `;

                fragment.appendChild(col);
            });

            container.appendChild(fragment);

        } catch (err) {
            console.error("Erro:", err);
        }
    }

    /* =========================
       SELECT2 SAFE
    ========================= */
    function initSelect2() {
        if (window.jQuery && $.fn.select2) {
            $(".select2").select2();
        }
    }

    /* =========================
       EVENTS
    ========================= */
    function initEvents() {

        document.addEventListener("click", (e) => {

            const del = e.target.closest(".btn-delete");
            const edit = e.target.closest(".btn-edit");

            if (del) {
                console.log("Delete:", del.dataset.id);
            }

            if (edit) {
                const data = stocksData[edit.dataset.index];
                if (!data) return;

                console.log("Edit:", data);
            }

        });

    }

    setTimeout(() => {
        console.log("stockContainer:", document.getElementById("stockContainer"));
    }, 2000);
</script>

<?php require_once '../app/views/footer.php'; ?>