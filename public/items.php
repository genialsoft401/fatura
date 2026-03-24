<?php
require_once '../app/views/layout_creation.php';
?>
<!-- DataTables CSS com Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.min.css">
 
 <style>
    /* Estilos para transformar a tabela em cards no mobile */
    @media (max-width: 768px) {
        #itemsTable thead { display: none; }
        
        #itemsTable, #itemsTable tbody, #itemsTable tr, #itemsTable td {
            display: block;
            width: 100%;
        }
        
        #itemsTable tbody tr {
            margin-bottom: 1rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 15px;
            border: 1px solid #eff2f5;
        }

        #itemsTable tbody td {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            padding: 8px 0;
            border: none;
            border-bottom: 1px solid #f1f1f1;
            word-break: break-word;
        }

        #itemsTable tbody td:last-child { border-bottom: none; flex-direction: row; justify-content: flex-end; padding-top: 15px; }
        
        #itemsTable tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        #itemsTable tbody td[data-label="Descrição"] { font-weight: bold; color: #212529; font-size: 1.1rem; border-bottom: 2px solid #e9ecef; margin-bottom: 8px; }
    }
</style>

<body>
<main>
    <div class="container mt-5">
        <h2 class="mb-4">Lista de Produtos/Serviços</h2>
        <table id="itemsTable" class="table table-striped table-hover dt-responsive nowrap w-100">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar produtos/serviços...">
            </div>
            
            <thead class="table-light">
                <tr>
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