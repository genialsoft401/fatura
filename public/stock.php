<?php
require_once '../app/config/db.php';
require_once '../app/helpers/authentication.php';
require_once '../app/helpers/subscription.php';

try {
    subscription_require_feature($pdo, (int)($_SESSION['user']['company_id'] ?? 0), 'stock');
} catch (Exception $e) {
    $cid = (int)($_SESSION['user']['company_id'] ?? 0);
    header('Location: subscription.php?company_id=' . $cid . '&upgrade=stock');
    exit;
}

require_once '../app/views/layout_creation.php';
?>

<body>
<main>
    
<link rel="stylesheet" href="stock/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <div class="container py-4 mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold text-primary"><?= t('Meus Stocks') ?></h2> 
        <div class="d-flex gap-2">
            <div class="btn-group">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportGeneralStocks('excel')"><i class="bi bi-file-earmark-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportGeneralStocks('pdf')"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="window.print()"><i class="bi bi-printer me-2"></i>Imprimir</a></li>
                </ul>
            </div>
            <button class="btn btn-primary  d-flex justify-content-center aling-items-center" id="createStock">
                <span class="material-icons-round me-2">add_circle</span> <?= t('Criar Novo Stock') ?></button>
        </div>
    </div>
    
    <div class="row" id="estoquesContainer">
        <!-- JS vai carregar os cards aqui -->
    </div>


    
    </div>

    <div class="modal fade" id="modalDetalhesEstoque" tabindex="-1" aria-labelledby="modalEstoqueLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEstoqueLabel">Detalhes do Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p><strong>Descrição:</strong> <span id="detalheDescricao"></span></p>
        <p><strong>Endereço:</strong> <span id="detalheEndereco"></span></p>
        <p><strong>Produtos/Serviços:</strong> <span id="detalheItens"></span></p>
        <p><strong>Valor Total:</strong> <span id="detalheValor"></span></p>
        <div class="mt-4">
          <canvas id="detalheGrafico" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

</main>

<div id="overlay-preloader" style="display: none;">
  <div class="loader"></div>
</div>


<script src="stock/stock.js"></script>


<?php require_once '../app/views/footer.php'; ?>
</body>
</html>
