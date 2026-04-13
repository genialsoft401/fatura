<?php
require_once '../app/views/layout_creation.php';
?>
<!-- DataTables CSS com Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">

<style>
    /* Estilos para transformar a tabela em cards no mobile */

    /* ===== TABELA ESTILO ===== */
    #itemsTable {
        border-collapse: separate;
        border-spacing: 0 12px;
        width: 100%;
    }

    /* HEADER */
    #itemsTable thead th {
        border: none;
        font-size: 12px;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 16px;
        text-align: left;
    }


    /* ROW */
    #itemsTable tbody tr {
        background: #fff !important;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        text-align: left !important;
    }


    /* HOVER PRO */
    #itemsTable tbody tr:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    /* CELLS */
    #itemsTable tbody td {
        border: none;
        padding: 18px 16px;
        vertical-align: middle;
        font-size: 0.95rem;
        background: #fff !important;
        text-align: left !important;
    }

    #itemsTable thead td {
        background: #111 !important;
        display: none;
        max-width: 80px !important;
    }

    /* BORDAS ARREDONDADAS */
    #itemsTable tbody td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
        background: #fff !important;
    }

    #itemsTable tbody th {
        text-align: left !important;
    }

    #itemsTable tbody td:last-child {
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
        text-align: right;
        padding-right: 24px;
    }

    /* ===== NOME (PRINCIPAL) ===== */
    #itemsTable tbody td:first-child {
        font-weight: 600;
        color: #111;
    }

    /* SUBINFO */
    #itemsTable tbody td small {
        display: block;
        color: #6b7280;
    }

    /* ===== ÍCONES ===== */
    .table-icon {
        font-size: 1.2rem;
        color: #9ca3af;
        transition: all 0.2s;
    }

    .table-icon:hover {
        color: #111;
        transform: scale(1.1);
    }

    /* ===== AÇÕES ===== */
    .edit-contact i,
    .delete-contact i {
        transition: all 0.2s ease;
    }

    .edit-contact:hover i {
        color: #2563eb;
        transform: scale(1.2);
    }

    .delete-contact:hover i {
        color: #dc2626;
        transform: scale(1.2);
    }

    /* ===== MODAL MAIS PREMIUM ===== */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    /* ===== CARDS DO MODAL ===== */
    .card-header.bg-primary,
    .card-header.bg-info,
    .card-header.bg-secondary,
    .card-header.bg-success,
    .card-header.bg-warning {
        border-radius: 12px 12px 0 0;
        font-size: 0.95rem;
    }

    /* ===== PHONE CARDS ===== */
    .phone-card {
        display: inline-flex;
        align-items: center;
        background: #f3f4f6;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .phone-card:hover {
        background: #e5e7eb;
    }

    #dt-length-0 {
        background: #fff !important;
        border-radius: 8px;
        padding: 5px;
        border: 0.5px solid #e5e7eb;
    }

    .dt-search {
        position: relative;
        margin-bottom: 15px;
    }

    .dt-search label {
        display: none !important;
    }

    /* ÍCONE */
    .dt-search-0 .search-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    /* INPUT */
    #dt-search-0 {
        padding-left: 15px !important;
        border-radius: 12px !important;
        border: 2px solid #ddd !important;
    }

    /* FOCUS */
    #dt-search-0:focus {
        border-color: #16a34a !important;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15) !important;
    }

    .text-truncate-custom {
        max-width: 280px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* 
    @media (max-width: 768px) {


        #itemsTable tbody td:last-child {
            border-bottom: none;
            flex-direction: row;
            justify-content: flex-end;
            padding-top: 15px;
        }

        #itemsTable tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        #itemsTable tbody td[data-label="Descrição"] {
            font-weight: bold;
            color: #212529;
            font-size: 1.1rem;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 8px;
        }
    } */
</style>

<body>
    <main>
        <div class="container mt-5">
            <h2 class="mb-4">Lista de Produtos/Serviços</h2>
            <table id="itemsTable" class="table nowrap w-100">
                <div class="mb-3 col-lg-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar produtos/serviços...">
                </div>

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Preço Unitário</th>
                        <th>Taxa/IVA</th>
                        <th>PVP</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Aqui o JS vai inserir os dados -->
                </tbody>
            </table>
        </div>

        <?php require_once '../app/models/modal_editItem.php'; ?>
    </main>

    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.min.js"></script>
    <script src="items/items.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>