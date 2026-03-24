$(document).ready(function () {
  carregarItens();

    



$("#addRow").on("click", function () {
  $('#formNovoItem')[0].reset()
  carregarMoedas('AOA').then(options => {
    $('#selectMoeda').html(options)
  })
  const modal = new bootstrap.Modal(document.getElementById('modalNovoItem'))
  modal.show()
})

$('#btnSalvarItem').on('click', function () {
  const $btn = $(this)
  if ($btn.prop('disabled')) return

  $btn.prop('disabled', true).text('Salvando...')

  const form = $('#formNovoItem')
  const dados = {
    id: null,
    code: form.find('[name="code"]').val().trim(),
    name: form.find('[name="name"]').val().trim(),
    category: form.find('[name="category"]').val().trim(),
    quantity: parseInt(form.find('[name="quantity"]').val()) || 0,
    min_quantity: parseInt(form.find('[name="min_quantity"]').val()) || 0,
    unit_price: parseFloat(form.find('[name="unit_price"]').val()) || 0,
    currency: form.find('[name="currency"]').val() || 'AOA'
  }

  if (!dados.name) {
    Swal.fire('Preencha o nome do produto/serviço.', '', 'warning')
    $btn.prop('disabled', false).text('Salvar Produto/Serviço')
    return
  }

  mostrarStatusSalvando()

  $.post(
    "stock/ajax/stock_items.php",
    {
      action: "save",
      stock_id: STOCK_ID,
      items: JSON.stringify([dados])
    },
    function (res) {
      if (res.success) {
        $('#modalNovoItem').modal('hide')
        carregarItens(() => {
          const ultimaLinha = $('#stockTable tbody tr').last()
          ultimaLinha.addClass('tr-recente')
          $('html, body').animate({ scrollTop: ultimaLinha.offset().top - 100 }, 600)
          setTimeout(() => ultimaLinha.removeClass('tr-recente'), 2000)
        })
        mostrarStatusSalvo()
      } else {
        Swal.fire('Erro ao salvar o produto/serviço.', '', 'error')
      }

      $btn.prop('disabled', false).text('Salvar Produto/Serviço')
    },
    "json"
  )
})




  $("#saveAll").on("click", function () {
    const dados = [];

    $("#stockTable tbody tr").each(function () {
      const id = $(this).data("id") || null;
      const code = $(this).find(".item-code").val().trim();
      const name = $(this).find(".item-name").val().trim();
      const category = $(this).find(".item-category").val().trim();
      const quantity = parseInt($(this).find(".item-quantity").val()) || 0;
      const min_quantity = parseInt($(this).find(".item-min").val()) || 0;
      const price = parseFloat($(this).find(".item-price").val()) || 0;
      const currency = $(this).find(".item-currency").val() || 'AOA';

      if (!name) return;

      dados.push({ id, code, name, category, quantity, min_quantity, unit_price: price, currency });
    });
console.log(dados);
    $.post(
      "stock/ajax/stock_items.php",
      {
        action: "save",
        stock_id: STOCK_ID,
        items: JSON.stringify(dados),
      },
      function (res) {
        if (res.success) {
          Swal.fire(t("Produtos/Serviços salvos com sucesso!"), "", "success");
          carregarItens();
        } else {
          Swal.fire(t("Erro ao salvar os produtos/serviços."), "", "error");
        }
      },
      "json"
    );
  });
});

function carregarItens(callback = null) {
  $.get(
    "stock/ajax/stock_items.php",
    { action: "list", stock_id: STOCK_ID },
    function (res) {
      $("#stockName")
        .text(res.stock?.name || "")
        .css("color", res.stock?.color || "#007abd") // aplica a cor no texto
      $('.cor-estoque').each(function () {
        this.style.setProperty('background-color', res.stock?.color, 'important')
        this.style.setProperty('border-color', res.stock?.color, 'important')
      })
      
      // Atualiza info de modificação no cabeçalho
      if (res.stock?.updated_at) {
        const d = new Date(res.stock.updated_at);
        $('#lastUpdate').text(d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'}));
      }
      if (res.stock?.updated_by) {
        $('#lastUser').text(res.stock.updated_by);
      }

      $(' #stockTable thead th').each(function () {
        this.style.setProperty('background-color', res.stock?.color, 'important')
        this.style.setProperty('border-color', res.stock?.color, 'important')
      })
     


      const tbody = $("#stockTable tbody")
      tbody.empty()

      res.items.forEach((item) => {
        const row = $(gerarLinha(item))
        tbody.append(row)

        carregarMoedas(item?.iso_code || '').then(options => {
          row.find('.currency-select').html(options)
        })
      })

      updateGrandTotal();

      if (callback) callback()
    },
    "json"
  )
}


function gerarLinha(item) {
  const id = item?.id || ''
  const code = item?.code || ''
  const name = item?.name || ''
  const category = item?.category || ''
  const quantity = item?.quantity || 0
  const min_quantity = item?.min_quantity ?? 1
  const price = item?.unit_price || 0
  const iso_code = item?.iso_code  || 'AOA'
  const symbol = item?.symbol  || 'Kz'
  const position = item?.position || 'left'
  const total = formatCurrency((quantity * price), symbol, position)

  return `
    <tr data-id="${id}">
      <td data-label="Código">
        <div class="celula-stack">
          <label>Código</label>
          <input type="text" class="item-code" value="${code}">
        </div>
      </td>

      <td data-label="Nome">
        <div class="celula-stack">
          <label>Nome</label>
          <input type="text" class="item-name" value="${name}">
        </div>
      </td>

      <td data-label="Categoria">
        <div class="celula-stack">
          <label>Categoria</label>
          <input type="text" class="item-category" value="${category}">
        </div>
      </td>

      <td data-label="Quantidade">
        <div class="celula-stack">
          <label>Quantidade</label>
          <div class="qtd-control">
            <button type="button" class="btn-qtd" onclick="alterarQtd(this, -1)">−</button>
            <input type="number" class="item-quantity" value="${quantity}" oninput="atualizarTotal(this, '${symbol}', '${position}')">
            <button type="button" class="btn-qtd" onclick="alterarQtd(this, 1)">+</button>
          </div>
        </div>
      </td>

      <td data-label="Stock mín.">
        <div class="celula-stack">
          <label>Stock mín.</label>
          <input type="number" class="item-min" value="${min_quantity}" min="0">
        </div>
      </td>

      <td data-label="Preço Unitário">
        <div class="celula-stack">
          <label>Preço Unitário</label>
          <input type="number" class="item-price" value="${price}" step="0.01" oninput="atualizarTotal(this, '${symbol}', '${position}')">
        </div>
      </td>
      <!-- <td data-label="Moeda">
        <div class="celula-stack">
          <label>Moeda</label>
          <select class="currency-select item-currency" name="currency"></select>
        </div>
      </td> -->


       <td data-label="Total">
        <div class="celula-stack">
          <label>Total</label>
          <div class="item-total">${total}</div>
 
        </div>
      </td> 

      <td data-label="Ações" class="d-flex flex-wrap">
        <button class="btn btn-primary btn-sm btn-mover btn-stock" data-id="${id}"><span class="material-icons-round">
        exit_to_app
        </span></button>

        <button class="btn btn-danger btn-sm btn-deletar btn-stock" data-id="${id}"><span class="material-icons-round">
        delete_forever
</span></button>
      </td>
    </tr>
  `
}

$(document).on('click', '.btn-mover', function () {
  const itemId = $(this).data('id')
  const $linha = $(this).closest('tr')

  if (!itemId) {
    Swal.fire('Produto/Serviço ainda não foi salvo.', '', 'info')
    return
  }

  const nomeItem = $linha.find('.item-name').val().trim()
  const qtdDisponivel = parseInt($linha.find('.item-quantity').val()) || 0

  $.getJSON('stock/ajax/stock_controller.php', { action: 'list_transfer', current_stock: STOCK_ID }, function (res) {
    if (!res.success || !res.estoques.length) {
      Swal.fire('Nenhum stock disponível para transferência.', '', 'info')
      return
    }

    const options = res.estoques.map(e => `<option value="${e.id}">${e.name}</option>`).join('')

    Swal.fire({
      title: 'Transferir Produto/Serviço',
      html: `
        <p>Produto/Serviço: <b>"${nomeItem}"</b><br>Quantidade disponível: <b>${qtdDisponivel}</b></p>
        <label for="quantidadeMover">Quantas unidades deseja mover?</label>
        <input type="number" id="quantidadeMover" class="form-control mb-3" value="${qtdDisponivel}" min="1" max="${qtdDisponivel}">
        
        <label for="destinoEstoque">Stock de destino:</label>
        <select id="destinoEstoque" class="form-select mt-1">${options}</select>
      `,
      showCancelButton: true,
      confirmButtonText: 'Mover',
      cancelButtonText: 'Cancelar',
      preConfirm: () => {
        const destino = $('#destinoEstoque').val()
        const qtdMover = parseInt($('#quantidadeMover').val())

        if (!destino) {
          Swal.showValidationMessage('Escolha um stock de destino.')
          return false
        }

        if (!qtdMover || qtdMover < 1 || qtdMover > qtdDisponivel) {
          Swal.showValidationMessage(`Quantidade deve ser entre 1 e ${qtdDisponivel}`)
          return false
        }

        return { destino, qtdMover }
      }
    }).then(result => {
      if (!result.isConfirmed) return

      const destino = result.value.destino
      const qtdMover = result.value.qtdMover

     $.post('stock/ajax/move_item.php', {
  item_id: itemId,
  from_stock: STOCK_ID,
  to_stock: destino,
  quantidade: qtdMover
}, function (res) {
  if (res.success) {
    if (parseInt(destino) === parseInt(STOCK_ID)) {
      // Atualiza a tabela porque o estoque de destino é o atual
      carregarItens(() => {
        const ultimaLinha = $('#stockTable tbody tr').last()
        ultimaLinha.addClass('tr-recente')
        $('html, body').animate({ scrollTop: ultimaLinha.offset().top - 100 }, 600)
        setTimeout(() => ultimaLinha.removeClass('tr-recente'), 2000)
      })
    } else {
      // Remove visualmente do estoque de origem
      $linha.fadeOut(300, () => {
        $linha.remove();
        updateGrandTotal();
      });
    }

    mostrarStatusSalvo()
  } else {
    Swal.fire('Erro ao mover o produto/serviço.', res.message || '', 'error')
  }
}, 'json')

    })
  })
})



$(document).on('click', '.btn-deletar', function () {
  const $tr = $(this).closest('tr')
  const id = $(this).data('id')

  if (!id) {
    // Se não tem ID ainda, só remove visualmente
    $tr.remove();
    updateGrandTotal();
    return
  }

  Swal.fire({
    title: 'Tem certeza?',
    text: 'Você quer remover este item do stock?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, remover',
    cancelButtonText: 'Cancelar'
  }).then(result => {
    if (result.isConfirmed) {
      $.post('stock/ajax/delete_item.php', {
        stock_id: STOCK_ID,
        id: id
      }, function (res) {
        if (res.success) {
          $tr.fadeOut(300, () => {
            $tr.remove();
            updateGrandTotal();
          });
          mostrarStatusSalvo()
        } else {
          Swal.fire('Erro ao remover o produto/serviço.', '', 'error')
        }
      }, 'json')
    }
  })
})


function alterarQtd(btn, delta) {
  const input = $(btn).siblings("input");
  let val = parseInt(input.val()) || 0;
  val = Math.max(0, val + delta);
  input.val(val).trigger("input");
}

function atualizarTotal(input, symbol, position) {
  const row = $(input).closest("tr");
  const quantity = parseInt(row.find(".item-quantity").val()) || 0;
  const price = parseFloat(row.find(".item-price").val()) || 0;
  row
    .find(".item-total")
    .text(formatCurrency((quantity * price), symbol, position));
  updateGrandTotal();
}

function removerLinha(btn) {
  $(btn).closest("tr").remove();
  updateGrandTotal();
}

function carregarMoedas(selectedIso = 'AOA') {
  const cacheKey = 'moedasCache'

  if (sessionStorage.getItem(cacheKey)) {
    const data = JSON.parse(sessionStorage.getItem(cacheKey))
    return Promise.resolve(gerarOpcoesMoeda(data, selectedIso))
  }

  return $.getJSON('assets/ajax/get_currencies.php').then(data => {
    sessionStorage.setItem(cacheKey, JSON.stringify(data))
    return gerarOpcoesMoeda(data, selectedIso)
  })
}

function gerarOpcoesMoeda(data, selectedIso) {
  return data.map(row => {
    const selected = row.iso_code === selectedIso ? 'selected' : ''
    const label = `(${row.iso_code}) ${row.currency}`
    return `<option value="${row.iso_code}" ${selected}>${label}</option>`
  }).join('')
}



let saveTimeout = null
let statusAtual = 'salvo'
let mostrarBotaoTimeout = null

function marcarComoNaoSalvo() {
  if (statusAtual === 'nao-salvo') return
  statusAtual = 'nao-salvo'

  $('#saveStatus')
    .removeClass('saving')
    .html('<i class="bi bi-cloud-slash"></i> <span class="status-text">Não salvo</span>')
    
  $('#saveAllManual').addClass('d-none').removeClass('d-inline-flex')
}

function mostrarStatusSalvando() {
  statusAtual = 'salvando'
  $('#saveStatus')
    .removeClass('saving')
    .html('<i class="bi bi-cloud-upload"></i> <span class="status-text">Salvando<span class="dots"></span></span>')
}

function mostrarStatusSalvo() {
  statusAtual = 'salvo'
  $('#saveStatus')
    .removeClass('saving')
    .html('<i class="bi bi-cloud-check"></i> <span class="status-text">Salvo</span>')
 
  clearTimeout(mostrarBotaoTimeout)
  mostrarBotaoTimeout = setTimeout(() => {
    if (statusAtual === 'nao-salvo') {
      $('#saveAllManual').removeClass('d-none').addClass('d-inline-flex')
    }
  }, 3000)
  
}


function mostrarErroAoSalvar() {
  statusAtual = 'nao-salvo'
  $('#saveStatus')
    .removeClass('saving')
    .html('<i class="bi bi-cloud-slash"></i> <span class="status-text">Não salvo</span>')

  $('#saveAllManual').removeClass('d-none').addClass('d-inline-flex')
}


function saveAutomaticamente() {
  const dados = []

  $("#stockTable tbody tr").each(function () {
    const id = $(this).data("id") || null;
    const name = $(this).find(".item-name").val().trim();
    const category = $(this).find(".item-category").val().trim();
    const quantity = parseInt($(this).find(".item-quantity").val()) || 0;
    const price = parseFloat($(this).find(".item-price").val()) || 0;
    const currency = $(this).find(".item-currency").val() || 'AOA';

    if (!name) return;
    const code = $(this).find(".item-code").val().trim();
    const min_quantity = parseInt($(this).find(".item-min").val()) || 0;
    dados.push({id, code, name, category, quantity, min_quantity, unit_price: price, currency});
  })

  mostrarStatusSalvando()

  $.post(
    "stock/ajax/stock_items.php",
    {
      action: "save",
      stock_id: STOCK_ID,
      items: JSON.stringify(dados),
    },
    function (res) {
  if (res.success) {
    // Atualiza data-id das linhas recém-criadas
    if (res.items && Array.isArray(res.items)) {
      $('#stockTable tbody tr').each(function () {
        const $tr = $(this)
        const id = $tr.data('id')
        if (!id || id === '') {
          const itemAtualizado = res.items.find(i => i.temp_id === null || i.temp_id === '' || i.temp_id === 0)
          if (itemAtualizado) {
            $tr.attr('data-id', itemAtualizado.id)
          }
        }
      })
    }

    // Atualiza info de modificação após salvar
    if (res.stock_update) {
        const d = new Date(res.stock_update.updated_at);
        $('#lastUpdate').text(d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'}));
        $('#lastUser').text(res.stock_update.updated_by);
    }

    mostrarStatusSalvo()
  } else {
      mostrarErroAoSalvar()
  }
}
,
    "json"
  )
}
$(document).on('input change', '.item-code, .item-name, .item-category, .item-min, .item-quantity, .item-price, .item-currency', function () {
 

  clearTimeout(saveTimeout)
  saveTimeout = setTimeout(() => {
    saveAutomaticamente()
  }, 500)
 
  $('#saveAllManual').on('click', function () {
    saveAutomaticamente()
  })

  // Ctrl+S
  $(document).on('keydown', function (e) {
    if (e.ctrlKey && e.key === 's') {
      e.preventDefault()
      saveAutomaticamente()
    }
  })


})

function updateGrandTotal() {
    const tbody = $("#stockTable tbody");
    let totalQuantidade = 0;
    let totalGeral = 0;
    
    let symbol = 'Kz';
    let position = 'left';

    const firstRow = tbody.find("tr:first");
    if (firstRow.length) {
        const oninputAttr = firstRow.find('.item-quantity').attr('oninput');
        if (oninputAttr) {
            const matches = oninputAttr.match(/'(.*?)',\s*'(.*?)'/);
            if (matches && matches.length > 2) {
                symbol = matches[1];
                position = matches[2];
            }
        }
    }

    tbody.find("tr").each(function() {
        const row = $(this);
        const quantity = parseInt(row.find(".item-quantity").val()) || 0;
        const price = parseFloat(row.find(".item-price").val()) || 0;
        
        totalQuantidade += quantity;
        totalGeral += quantity * price;
    });

    const tfoot = $("#stockTable tfoot");
    tfoot.empty();
    
    const totalRow = `
        <tr style="font-weight: bold;">
            <td colspan="2" class="text-end"><strong>Total Geral</strong></td>
            <td><strong>${totalQuantidade}</strong></td>
            <td></td>
            <td><strong>${formatCurrency(totalGeral, symbol, position)}</strong></td>
            <td></td>
        </tr>
    `;
    tfoot.append(totalRow);
}
