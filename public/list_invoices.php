<?php
require_once '../app/views/layout_creation.php';
?>
<style>
</style>
<body>
<div id="preloader" style="display: none; position: fixed; bottom: 20px; right: 20px; width: 200px; padding: 10px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.2);">
    <p style="margin: 0; font-size: 14px;">Gerando arquivo...</p>
    <div style="height: 5px; width: 100%; background: #ddd; border-radius: 3px; overflow: hidden; margin-top: 5px;">
        <div id="progressBar" style="height: 100%; width: 0%; background: #007bff;"></div>
    </div>
</div>

    <main>
    <div class="container mt-5">
    <h2 class="mb-4"><?= t('Emissão de Fatura') ?></h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="dropdown">
    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Exportar Faturas
    </button>
    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
        <li><a class="dropdown-item" href="#" onclick="exportFile('excel')">Exportar para Excel</a></li>
        <li><a class="dropdown-item" href="#" onclick="exportFile('csv')">Exportar para CSV</a></li>
    </ul>
</div>

    </div>

    <div class="table-responsive">
        <table id="invoicesTable" class="dataTables-BXpert table table-striped table-hover display nowrap">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"> <?= t('Status') ?></th>
                    <th><?= t('Fatura') ?></th>
                    <th><?= t('Cliente') ?></th>
                    <th><?= t('Emissão') ?></th>
                    <th><?= t('Vencimento') ?></th>
                    <th><?= t('Moeda') ?></th>
                    <th><?= t('Valor Final') ?></th>
                    <th><?= t('Ações') ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Dados gerados via PHP -->
            </tbody>
        </table>
    </div>
</div>

    </main>

    <script src="invoices/list_invoices.js"></script>

    <?php require_once '../app/views/footer.php'; ?>
</body>

</html>