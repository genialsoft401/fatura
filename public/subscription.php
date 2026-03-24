<?php
require_once '../app/config/db.php';
require_once '../app/helpers/functions.php';
require_once '../app/helpers/authentication.php';
require_once '../app/helpers/subscription.php';

// Importante: qualquer redirect precisa acontecer ANTES de imprimir HTML.
// layout_creation.php inclui head/nav e já envia output.

$company_id = (int)($_SESSION['user']['company_id'] ?? 0);

// A troca de empresa acontece via sessão (navbar). Esta tela sempre segue a empresa ativa.
// Se alguém chegar com company_id via GET, limpamos a URL (evita confusão e cache).
if (isset($_GET['company_id'])) {
    header('Location: subscription.php');
    exit;
}

if (!$company_id) {
    die('Empresa inválida');
}

require_once '../app/views/layout_creation.php';

$c = subscription_get_company($pdo, $company_id);
$plans = subscription_plans();
$planCode = $c['plan_code'] ?? 'BXPERT_BAZA';
$plan = $plans[$planCode] ?? $plans['BXPERT_BAZA'];
$usage = subscription_usage($pdo, $company_id);
$daysLeft = subscription_days_left($c['plan_expires_at'] ?? null);
?>

<main class="main-content">
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Assinatura / Plano</h4>
      <a class="btn btn-outline-secondary" href="list_companies.php">Voltar</a>
    </div>

    <div class="card">
      <div class="card-body">
        <?php
          $fmtExp = ($c['plan_expires_at'] ?? null) ? date('d/m/Y', strtotime($c['plan_expires_at'])) : '-';
          $fmtStart = ($c['plan_started_at'] ?? null) ? date('d/m/Y', strtotime($c['plan_started_at'])) : '-';
          $inf = '∞';
          $limInv = $plan['invoice_limit_month'] === null ? $inf : (int)$plan['invoice_limit_month'];
          $limUsers = $plan['user_limit'] === null ? $inf : (int)$plan['user_limit'];
          $limRh = $plan['rh_employee_limit'] === null ? $inf : (int)$plan['rh_employee_limit'];
          $limStock = $plan['stock_item_limit'] === null ? $inf : (int)$plan['stock_item_limit'];
        ?>

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h5 class="card-title mb-1">Plano: <?= htmlspecialchars($plan['name']) ?></h5>
            <div class="text-muted" style="font-size:0.95rem;">
              Início: <strong><?= $fmtStart ?></strong> • Vencimento: <strong><?= $fmtExp ?></strong>
              <?php if ($daysLeft !== null): ?> • <span class="badge bg-<?= $daysLeft <= 7 ? 'danger' : ($daysLeft <= 15 ? 'warning text-dark' : 'success') ?>"><?= (int)$daysLeft ?> dias restantes</span><?php endif; ?>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPlans">Alterar plano</button>
            <button class="btn btn-primary" id="btnRenew">Renovar</button>
            <button class="btn btn-outline-success" id="btnMarkPaid">Marcar como pago</button>
          </div>

          <!-- Modal: escolher método de pagamento -->
          <div class="modal fade" id="modalPayMethod" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Renovar plano</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Método de pagamento</label>
                    <select class="form-select" id="payMethod">
                      <option value="REF">REF (Referência)</option>
                      <option value="GPO">GPO (Pagamento por telefone)</option>
                    </select>
                  </div>
                  <div class="mb-3" id="boxPhone" style="display:none;">
                    <label class="form-label">Telefone (GPO)</label>
                    <input type="text" class="form-control" id="payPhone" placeholder="Ex: 9xxxxxxxx">
                    <div class="form-text">Informe um número válido para receber a cobrança no telefone.</div>
                  </div>
                  <div class="alert alert-info mb-0" style="font-size:0.9rem;">
                    Ao confirmar, vamos gerar uma cobrança no AppyPay para este plano.
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-primary" id="btnConfirmPay">Gerar cobrança</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-3 g-3">
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <h6 class="mb-2">Uso do mês (<?= htmlspecialchars($usage['ym']) ?>)</h6>
              <div class="row g-2">
                <div class="col-6"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:0.85rem">Faturas</div><div><strong><?= (int)$usage['invoice_count'] ?></strong> / <?= $limInv ?></div></div></div>
                <div class="col-6"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:0.85rem">Utilizadores</div><div><strong><?= (int)$usage['user_count'] ?></strong> / <?= $limUsers ?></div></div></div>
                <div class="col-6"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:0.85rem">RH (ativos)</div><div><strong><?= (int)$usage['employee_count'] ?></strong> / <?= $limRh ?></div></div></div>
                <div class="col-6"><div class="p-2 bg-light rounded"><div class="text-muted" style="font-size:0.85rem">Stock (itens)</div><div><strong><?= (int)$usage['stock_item_count'] ?></strong> / <?= $limStock ?></div></div></div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <h6 class="mb-2">Limites do plano</h6>
              <ul class="mb-0">
                <li>Faturas/mês: <strong><?= $limInv ?></strong></li>
                <li>Utilizadores: <strong><?= $limUsers ?></strong></li>
                <li>RH (funcionários): <strong><?= $limRh ?></strong></li>
                <li>Stock (itens): <strong><?= $limStock ?></strong></li>
              </ul>
              <div class="text-muted mt-2" style="font-size:0.85rem;">
                Integração com API de pagamento entra depois. Por enquanto, Renovar cria pedido pendente e Marcar como pago confirma manualmente.
              </div>
            </div>
          </div>
        </div>

        <!-- Modal de planos -->
        <div class="modal fade" id="modalPlans" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Alterar plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <?php foreach ($plans as $code => $p):
                    $liInv = $p['invoice_limit_month'] === null ? $inf : (int)$p['invoice_limit_month'];
                    $liUsers = $p['user_limit'] === null ? $inf : (int)$p['user_limit'];
                    $liRh = $p['rh_employee_limit'] === null ? $inf : (int)$p['rh_employee_limit'];
                    $liStock = $p['stock_item_limit'] === null ? $inf : (int)$p['stock_item_limit'];
                    $active = ($code === $planCode);
                  ?>
                  <div class="col-md-6">
                    <div class="border rounded p-3 h-100 <?= $active ? 'border-primary' : '' ?>">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <div style="font-weight:700; font-size:1.05rem;"><?= htmlspecialchars($p['name']) ?></div>
                          <div class="text-muted" style="font-size:0.9rem;"><?= htmlspecialchars($code) ?></div>
                        </div>
                        <?php if ($active): ?><span class="badge bg-primary">Atual</span><?php endif; ?>
                      </div>
                      <div class="mt-2"><strong><?= number_format((float)$p['price_quarter'], 0, ',', '.') ?> Kz</strong> <span class="text-muted">/ trimestral</span></div>
                      <ul class="mt-2 mb-0">
                        <li>Faturas/mês: <strong><?= $liInv ?></strong></li>
                        <li>Utilizadores: <strong><?= $liUsers ?></strong></li>
                        <li>RH: <strong><?= $liRh ?></strong></li>
                        <li>Stock: <strong><?= $liStock ?></strong></li>
                      </ul>
                      <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary w-100 btnChoosePlan" data-code="<?= htmlspecialchars($code) ?>" <?= $active ? 'disabled' : '' ?>>Escolher este plano</button>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              </div>
            </div>
          </div>
        </div>

      <hr class="my-4">

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Histórico de pagamentos</h5>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" id="btnSyncOrders">Atualizar status</button>
        </div>
      </div>
      <div class="text-muted" style="font-size:0.85rem;">Mostra as últimas 30 transações. Pagamentos aprovados creditam automaticamente os meses e liberam módulos/limites.</div>

      <div class="table-responsive mt-2">
        <table class="table table-sm" id="tblOrders">
          <thead>
            <tr>
              <th>#</th>
              <th>Plano</th>
              <th>Método</th>
              <th>Referência</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Transação</th>
              <th>Data</th>
              <th>Pago em</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="9" class="text-muted">Carregando...</td></tr>
          </tbody>
        </table>
      </div>

      </div>
    </div>
  </div>
</main>

<script>
  const COMPANY_ID = <?= (int)$company_id ?>;
  let SELECTED_PLAN_CODE = '<?= htmlspecialchars($planCode) ?>';

  // Se veio de um módulo bloqueado, mostra aviso de upgrade
  const params = new URLSearchParams(window.location.search);
  const up = params.get('upgrade');
  if (up) {
    const label = (up === 'stock') ? 'Stock' : 'RH';
    setTimeout(() => {
      Swal.fire('Upgrade necessário', `Seu plano atual não inclui ${label}. Selecione um plano superior e realize o pagamento para liberar o acesso.`, 'info');
    }, 400);
  }

  function formatDt(x){
    if(!x) return '-';
    try { return new Date(String(x).replace(' ', 'T')).toLocaleString('pt-PT'); } catch(e){ return String(x); }
  }

  function formatDue(x){
    if(!x) return '-';
    try {
      // aceita ISO com timezone (ex: 2026-03-11T12:54:31.560507+00:00)
      const d = new Date(String(x));
      if(String(d) !== 'Invalid Date') return d.toLocaleString('pt-PT');
    } catch(e) {}
    // fallback simples
    try { return String(x).replace('T',' ').replace('+00:00',''); } catch(e){ return String(x); }
  }

  function renderOrders(rows){
    const $tb = $('#tblOrders tbody');
    $tb.empty();
    if(!rows || !rows.length){
      $tb.append('<tr><td colspan="9" class="text-muted">Nenhuma transação encontrada.</td></tr>');
      return;
    }
    rows.forEach(r => {
      const st = String(r.status || '-');
      const gw = String(r.gateway_status || '-');

      // Status interno (pt-AO)
      let stLabel = st;
      let badge = 'secondary';
      if (st === 'paid') { stLabel = 'Pago'; badge = 'success'; }
      else if (st === 'pending') { stLabel = 'Pendente'; badge = 'warning text-dark'; }
      else if (st === 'canceled') { stLabel = 'Cancelado'; badge = 'danger'; }

      // Status do gateway (pt-AO)
      let gwLabel = gw;
      const gwL = gw.toLowerCase();
      if (gwL === 'success' || gwL === 'paid' || gwL === 'completed' || gwL === 'approved' || gwL === 'succeeded') gwLabel = 'Sucesso';
      else if (gwL === 'pending') gwLabel = 'Pendente';
      else if (gwL === 'failed' || gwL === 'failure') gwLabel = 'Falhou';
      else if (gwL === 'canceled' || gwL === 'cancelled') gwLabel = 'Cancelado';

      const gwTxt = (gw !== '-' && gw !== 'null' && gw !== '') ? ` <span class="text-muted">(${gwLabel})</span>` : '';

      let refTxt = '-';
      try{
        const pr = r.payment_ref ? JSON.parse(r.payment_ref) : null;
        if(pr && (pr.entity || pr.referenceNumber)){
          const ent = pr.entity ? String(pr.entity) : '';
          const ref = pr.referenceNumber ? String(pr.referenceNumber) : '';
          const due = pr.dueDate ? formatDue(pr.dueDate) : '';
          refTxt = (ent && ref) ? `<span style="font-family:monospace">${ent} / ${ref}</span>` : `<span style="font-family:monospace">${ent || ref}</span>`;
          if(due && due !== '-') refTxt += `<div class="text-muted" style="font-size:0.75rem">Vence: ${due}</div>`;
        }
      }catch(e){}

      $tb.append(`
        <tr>
          <td>${r.id}</td>
          <td>${r.plan_code || '-'}</td>
          <td>${r.payment_method || '-'}</td>
          <td>${refTxt}</td>
          <td>${Number(r.amount || 0).toFixed(2)} AOA</td>
          <td><span class="badge bg-${badge}">${stLabel}</span>${gwTxt}</td>
          <td style="font-family:monospace; font-size:0.85rem;">${r.merchant_transaction_id || '-'}</td>
          <td>${formatDt(r.created_at)}</td>
          <td>${formatDt(r.paid_at)}</td>
        </tr>
      `);
    });
  }

  function loadOrders(){
    return $.getJSON('subscription/ajax/list_orders.php', { company_id: COMPANY_ID })
      .done(resp => {
        if(resp && resp.success){
          renderOrders(resp.orders);
        }
      });
  }

  function syncOrders(){
    return $.post('subscription/ajax/sync_orders.php', { company_id: COMPANY_ID }, null, 'json')
      .done(resp => {
        if(resp && resp.success){
          if((resp.paid||0) > 0){
            Swal.fire('Pagamento aprovado', 'Encontramos pagamento(s) aprovado(s). O plano foi creditado e os módulos/limites foram liberados.', 'success')
              .then(() => location.reload());
          }
        }
      });
  }

  $('#btnSyncOrders').on('click', function(){
    $(this).prop('disabled', true).text('Atualizando...');
    syncOrders().always(() => {
      $('#btnSyncOrders').prop('disabled', false).text('Atualizar status');
      loadOrders();
    });
  });

  // Ao abrir a página: carrega histórico primeiro (rápido), depois sincroniza em background.
  loadOrders();
  syncOrders().always(loadOrders);

  // sincronização periódica (a cada 3 min) enquanto estiver na página
  setInterval(() => { syncOrders().always(loadOrders); }, 180000);

  const payModal = new bootstrap.Modal(document.getElementById('modalPayMethod'));

  $('#payMethod').on('change', function(){
    const m = $(this).val();
    $('#boxPhone').toggle(m === 'GPO');
  });

  $('#btnRenew').on('click', function(){
    // Renovar plano atual (ou o selecionado, se tiver vindo de Alterar plano)
    $('#payMethod').val('REF').trigger('change');
    $('#payPhone').val('');
    payModal.show();
  });

  $('#btnConfirmPay').on('click', function(){
    const method = $('#payMethod').val();
    const phone = $('#payPhone').val();

    $(this).prop('disabled', true).text('Gerando...');

    $.post('subscription/ajax/create_charge.php', { company_id: COMPANY_ID, method, phone, plan_code: SELECTED_PLAN_CODE }, function(resp){
      $('#btnConfirmPay').prop('disabled', false).text('Gerar cobrança');

      if(resp.success){
        payModal.hide();

        // depois de gerar cobrança, faz sync + recarrega histórico (para aparecer imediatamente)
        syncOrders().always(loadOrders);

        let extra = '';
        if ((resp.method || '') === 'REF' && resp.ref && resp.ref.entity && resp.ref.referenceNumber) {
          extra = '<hr><div class="text-start">' +
                  '<div><b>Entidade:</b> '+ resp.ref.entity +'</div>' +
                  '<div><b>Referência:</b> '+ resp.ref.referenceNumber +'</div>' +
                  (resp.ref.dueDate ? '<div><b>Vencimento:</b> '+ formatDue(resp.ref.dueDate) +'</div>' : '') +
                  '</div>';
        }

        const details = JSON.stringify(resp.charge || {}, null, 2);
        Swal.fire({
          title: 'Cobrança gerada',
          html: '<div class="text-start"><b>Plano:</b> '+ (SELECTED_PLAN_CODE || '-') +'<br><b>Transação:</b> '+ (resp.merchantTransactionId || '-') +'<br><b>Método:</b> '+ (resp.method || '-') +'</div>' +
                extra +
                '<details class="mt-2"><summary>Detalhes técnicos</summary>'+
                '<pre style="text-align:left; max-height:220px; overflow:auto; background:#0b1220; color:#e5e7eb; padding:12px; border-radius:8px;">'+ details +'</pre>'+
                '</details>',
          icon: 'success',
          confirmButtonText: 'Ok'
        });
      } else {
        Swal.fire('Erro', resp.message || 'Falha ao gerar cobrança.', 'error');
      }
    }, 'json').fail(function(){
      $('#btnConfirmPay').prop('disabled', false).text('Gerar cobrança');
      Swal.fire('Erro', 'Falha na requisição.', 'error');
    });
  });

  $('#btnMarkPaid').on('click', function(){
    $.post('subscription/ajax/mark_paid.php', { company_id: COMPANY_ID }, function(resp){
      if(resp.success){
        Swal.fire('Ok', 'Renovação confirmada. Plano estendido.', 'success').then(()=> location.reload());
      } else {
        Swal.fire('Erro', resp.message || 'Falha ao confirmar.', 'error');
      }
    }, 'json');
  });

  $(document).on('click', '.btnChoosePlan', function(){
    const code = $(this).data('code');
    SELECTED_PLAN_CODE = code;

    // Fecha modal de planos e abre modal de pagamento (REF/GPO)
    const plansEl = document.getElementById('modalPlans');
    const plansInst = plansEl ? bootstrap.Modal.getInstance(plansEl) : null;
    if (plansInst) plansInst.hide();

    // prepara modal pagamento
    $('#payMethod').val('REF').trigger('change');
    $('#payPhone').val('');

    setTimeout(() => payModal.show(), 250);
  });
</script>

<?php require_once '../app/views/footer.php'; ?>
