$(document).ready(function () {
  let countryMap = {};

  initializeTooltips();
  loadSelect2Items();
  fetchCurrencySymbol();
  fetchExchangeRate(userCurrency, $("#manual_exchange_rate").val());

  // Carrega contatos antes de verificar edição para garantir que o select esteja preenchido
  loadContacts().then(() => {
    const editId = new URLSearchParams(window.location.search).get("edit_id");
    if (editId) {
      loadInvoiceForEdit(editId);
    }
  });

  console.log("JS carregado, iniciando requisição AJAX...");

  // Carregar países ao iniciar a página
  selectCountry();

  // Quando um contato é selecionado, carrega os dados no formulário
  $("#contact-select").on("change", function () {
    let contatoId = $(this).val();

    if (contatoId) {
      $.ajax({
        url: "contacts/ajax/get_contact.php",
        type: "POST",
        data: { id: contatoId },
        dataType: "json",
        success: function (contato) {
          $("#contact_id").val(contato.id);
          $("#name").val(contato.name).prop("disabled", true);
          $("#email").val(contato.email).prop("disabled", true);
          $("#contributor").val(contato.contributor).prop("disabled", true);
          $("#po_box").val(contato.po_box).prop("disabled", true);
          $("#telephone").val(contato.telephone).prop("disabled", true);
          $("#address").val(contato.address).prop("disabled", true);
          selectCountry(contato.country, contato.city);

          $("#country").prop("disabled", true);
          $("#city").prop("disabled", true);

          $("#contact-form").show();

          console.log("✅ Formulário agora está visível.");
        },
        error: function (xhr, status, error) {
          console.error("❌ Erro ao buscar dados do contato:", status, error);
        },
      });
    } else {
      $("#contact-form").fadeOut();
    }
  });

  $('[data-bs-toggle="tooltip"]').tooltip();


  $("#toggle-contact-form").on("click", function () {
    if ($("#contact-form").is(":visible")) {
      $("#contact-form").hide();
      $("#select-contact-container").show();

      $("#spanIconCreateInvoices").text("person_add");
      $("#toggle-contact-form")
        .attr("data-bs-title", "Inserir novo Contato")
        .tooltip("dispose")
        .tooltip(); // Atualiza tooltip
    } else {
      $("#contact-form").show();
      $("#select-contact-container").hide();
      $(
        "#contact-form input, #contact-form textarea, #contact-form select"
      ).prop("disabled", false);
      $(
        "#contact-form input:not(#contact_id), #contact-form textarea, #contact-form select"
      ).val("");
      $("#contact_id").val("");
      $("#spanIconCreateInvoices").text("close");
      $("#toggle-contact-form")
        .attr("data-bs-title", "Fechar formulário")
        .tooltip("dispose")
        .tooltip(); // Atualiza tooltip
    }
  });

  $("#country").on("change", function () {
    if ($(this).data("ignore-change")) return;
    loadCities($(this).val());
  });

  const dueDateSelect = document.getElementById("due_date");
  const customDateInput = document.getElementById("custom_date");

  // Exibe o campo de data se "Outro" estiver selecionado ao carregar a página
  if(dueDateSelect) {
    if (dueDateSelect.value === "other") {
        document.getElementById("other_date_input").style.display = "block";
    }

    // Adiciona eventos para o select e input de data
    dueDateSelect.addEventListener("change", handleOtherOption);
  }
  if(customDateInput) {
    customDateInput.addEventListener("input", addCustomDateOption);
  }


function cleanCurrencyValue(value) {
  if (!value) return 0; // Caso o valor esteja vazio, retorna 0

  // Remove o símbolo da moeda (letras e espaços) e converte vírgula para ponto
  return parseFloat(value.replace(/[^\d,.-]/g, "").replace(",", "."));
}

function initializeTooltips() {
  document
    .querySelectorAll('[data-bs-toggle="tooltip"]')
    .forEach((el) => new bootstrap.Tooltip(el));
}

function loadSelect2Items() {
  $.ajax({
    url: "create_invoices/ajax/get_items.php",
    dataType: "json",
    success: function (data) {
      let options = '<option value="">Selecione um produto/serviço...</option>';
      $.each(data, function (index, item) {
        options += `<option value="${item.id}" data-item='${JSON.stringify(
          item
        )}'>
                                ${item.code} - ${(item.description_plain || item.description)}
                            </option>`;
      });
      $("#item_select").html(options);
      $(".select2").select2();
    },
  });
}

function loadContacts() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "contacts/ajax/fetch_contacts.php",
            type: "GET",
            dataType: "json",
            success: function (data) {
                let select = $("#contact-select");
                select.empty().append('<option value="">Selecione um contato...</option>');
                if (data.length > 0) {
                    data.forEach((contato) => {
                        select.append(`<option value="${contato.id}">${contato.name} - ${contato.email}</option>`);
                    });
                } else {
                    select.append('<option value="">Nenhum contato encontrado</option>');
                }
                resolve();
            },
            error: function (xhr, status, error) {
                console.error("❌ Erro na requisição AJAX:", status, error);
                reject(error);
            }
        });
    });
}

function showTableItemsList() {
  let element = document.querySelector("#items_list");
  if (element) {
    element.classList.remove("d-none");
  }
}

// Adicionar item ao selecionar
$("#item_select").on("change", function () {
  var selected = $(this).find(":selected").data("item");
  if (selected) {
    addItemRow(selected);
    // $(this).val('').trigger('change'); // Opcional: limpar seleção
  }
});

function addItemRow(item) {
    let itemId = item.id || item.item_id;
    if (!itemId) return;

    // Verifica se o item já foi adicionado
    if ($(`#item-${itemId}`).length > 0) return;

    let taxVal = item.tax;
    let is14 = (taxVal == 14 || taxVal == "14");
    let is0  = (taxVal == 0 || taxVal == "0" || taxVal == "exempt" || taxVal == "isento");

    let itemHtml = `
        <div class="row item-box align-items-center" id="item-${itemId}">
            <div class="col-1 text-center">
                <input type="text" class="form-control" value="${item.code || ''}" readonly>
            </div>
            <div class="col-4">
                <input type="text" class="form-control" value="${(item.description_plain || item.description) || ''}">
            </div>
            <div class="col-2 text-center">
                <input type="number" class="form-control" value="${item.unit_price || 0}" step="0.01">
            </div>
            <div class="col-1 text-center">
                <input type="number" class="form-control" value="${item.quantity || 1}" min="1">
            </div>
            <div class="col-2 text-center">
                <select class="form-select">
                    <option value="14" ${is14 ? "selected" : ""}>14% - Taxa14</option>
                    <option value="0" ${is0 ? "selected" : ""}>0% - Isento</option>
                </select>
            </div>
            <div class="col-1 text-center">
                <input type="number" class="form-control" value="${item.discount || 0}" step="0.01">
            </div>
            <div class="col-1 text-center">
                <span data-id="${itemId}" class="material-icons-round remove-item cursor">
                delete_forever
                </span>
            </div>
        </div>
    `;
    $("#items_list").append(itemHtml);
    showTableItemsList();
}

// Remover item da lista
$(document).on("click", ".remove-item", function () {
  let itemId = $(this).data("id");
  $(`#item-${itemId}`).remove();
});

// Buscar moeda, símbolo e posição via AJAX
// OBS: Para emissão/edição de faturas, usamos como padrão AOA (Kz) quando possível,
// porque em Angola a moeda padrão do sistema deve ser Kwanza.
function fetchCurrencySymbol(selectedIso = 'AOA') {
  $.ajax({
    url: "assets/ajax/get_currency.php",
    type: "GET",
    dataType: "json",
    success: function (data) {
      // tenta usar a moeda pedida; se o endpoint retornar outra, cai pra ela
      const iso = selectedIso || data.currency || 'AOA';
      userCurrency = iso;

      // se o endpoint retornou símbolo/posição, usa; senão mantém defaults
      if (data.symbol && data.position && data.currency === iso) {
        currencySymbol = data.symbol;
        currencyPosition = data.position;
      }

      updateInvoiceSummary();
    },
    error: function () {
      console.error("Erro ao buscar a moeda do usuário.");
    },
  });
}

let finalTotal = 0;

// ao mudar a moeda, recalcula com o símbolo correto
$(document).on('change', '#currency', function(){
  const iso = $(this).val() || 'AOA';
  // não consulta API externa aqui; só ajusta símbolo/posição via backend padrão
  fetchCurrencySymbol(iso);
});

function updateInvoiceSummary() {
  let totalSum = 0;
  let totalDiscount = 0;
  let taxExemptIncidence = 0;
  let tax14Incidence = 0;
  let totalTax = 0;

  // Calcula os valores base dos itens
  $(".item-box").each(function () {
    let price =
      parseFloat($(this).find("input:eq(2)").val().replace(",", ".")) || 0;
    let quantity = parseInt($(this).find("input:eq(3)").val()) || 1;
    let tax = $(this).find("select").val();
    let discount =
      parseFloat($(this).find("input:eq(4)").val().replace(",", ".")) || 0;

    let itemTotal = price * quantity;
    let discountValue = itemTotal * (discount / 100);

    totalSum += itemTotal;
    totalDiscount += discountValue;

    if (tax == "14") {
      tax14Incidence += itemTotal - discountValue;
    } else {
      taxExemptIncidence += itemTotal - discountValue;
    }
  });

  totalTax = parseFloat((tax14Incidence * 0.14).toFixed(2)); // Arredondado para 2 casas
  let subtotalWithoutTax = parseFloat((totalSum - totalDiscount).toFixed(2)); // Arredondado para 2 casas
  finalTotal = (subtotalWithoutTax + totalTax).toFixed(2); // Soma já convertida para número

  // Atualiza os valores na tabela de sumário
  $("#total_sum").text(
    formatCurrency(totalSum, currencySymbol, currencyPosition)
  );

  $("#total_discount").text(
    formatCurrency(totalDiscount, currencySymbol, currencyPosition)
  );

  $("#subtotal_without_tax").text(
    formatCurrency(subtotalWithoutTax, currencySymbol, currencyPosition)
  );

  $("#total_tax").text(
    formatCurrency(totalTax, currencySymbol, currencyPosition)
  );

  $("#final_total").text(
    formatCurrency(finalTotal, currencySymbol, currencyPosition)
  );

  $("#tax_exempt_incidence").text(
    formatCurrency(taxExemptIncidence, currencySymbol, currencyPosition)
  );
  $("#tax_exempt_value").text(
    formatCurrency(0, currencySymbol, currencyPosition)
  );
  $("#tax_14_incidence").text(
    formatCurrency(tax14Incidence, currencySymbol, currencyPosition)
  );
  $("#tax_14_value").text(
    formatCurrency(totalTax, currencySymbol, currencyPosition)
  );
  $("#total_sumInput").val(
    cleanCurrencyValue(
      formatCurrency(totalSum, currencySymbol, currencyPosition)
    )
  );
  $("#total_discountInput").val(
    cleanCurrencyValue(
      formatCurrency(totalDiscount, currencySymbol, currencyPosition)
    )
  );
  $("#subtotal_without_taxInput").val(
    cleanCurrencyValue(
      formatCurrency(subtotalWithoutTax, currencySymbol, currencyPosition)
    )
  );
  $("#total_taxInput").val(
    cleanCurrencyValue(
      formatCurrency(totalTax, currencySymbol, currencyPosition)
    )
  );

  // Atualizar retenção (recalcula sempre que os itens mudam)
  updateRetention(subtotalWithoutTax, totalTax);
}

function updateRetention(subtotalWithoutTax, totalTax) {
  let retentionPercentage = parseFloat($("#retention").val()) || 0;
  let calculatedFinalTotalAfterRetention = parseFloat(finalTotal); // Começamos com o total original (global)

  if (retentionPercentage > 0) {
    let retentionValue = parseFloat(
      (subtotalWithoutTax * (retentionPercentage / 100)).toFixed(2)
    );
    calculatedFinalTotalAfterRetention =
      parseFloat(subtotalWithoutTax) +
      parseFloat(totalTax) -
      parseFloat(retentionValue);

    // Mostrar o sumário da retenção
    $("#retention_sumary").removeClass("d-none").css("display", "table-row");
    $("#retention_value").text(formatCurrency(retentionValue, currencySymbol, currencyPosition));
    $("#retention_valueInput").val(retentionValue);

    // Atualizar o título com a porcentagem
    $("#retention_sumary td:first-child span").text(
      ` (${retentionPercentage}%)`
    );

  } else {
    // Se não houver retenção, ocultar a linha
    $("#retention_sumary").addClass("d-none").css("display", "none");
    // calculatedFinalTotalAfterRetention já contém o valor original de finalTotal
  }

  // Atualizar o total final na UI com o valor calculado após a retenção
  $("#final_total").text(formatCurrency(calculatedFinalTotalAfterRetention, currencySymbol, currencyPosition));
  $("#final_totalInput").val(
    cleanCurrencyValue(
      formatCurrency(calculatedFinalTotalAfterRetention, currencySymbol, currencyPosition)
      )
    );

  // Atualizar o total convertido com o valor final ajustado
  updateConvertedTotal(calculatedFinalTotalAfterRetention);
}

// Sempre que o campo de retenção for alterado, recalcular
$("#retention").on("input", function () {
  updateInvoiceSummary(); // Chama a função principal de resumo para recalcular tudo
});

// Monitorar mudanças na lista de itens (adição/remoção)
const observer = new MutationObserver(() => {
  updateInvoiceSummary();
});

observer.observe(document.getElementById("items_list"), {
  childList: true,
  subtree: true,
});

// Atualizar sempre que um input ou select for alterado
$(document).on(
  "input change",
  ".item-box input, .item-box select",
  function () {
    updateInvoiceSummary();
  }
);

let exchangeRate = 1; // Padrão: 1 para mesma moeda

// Buscar taxa de câmbio via API
function fetchExchangeRate(baseCurrency, targetCurrency) {
  if (baseCurrency === targetCurrency) {
    $("#exchange_rate_container").hide();
    $("#conversion_row, #exchange_rate_row").hide();
    return;
  }

  const apiUrl = `https://api.exchangerate-api.com/v4/latest/${baseCurrency}`;

  $.ajax({
    url: apiUrl,
    type: "GET",
    dataType: "json",
    success: function (data) {
      if (data.rates && data.rates[targetCurrency]) {
        exchangeRate = data.rates[targetCurrency];

        $("#manual_exchange_rate").val(exchangeRate.toFixed(6));
        $("#exchange_rate").text(exchangeRate.toFixed(6));
        $("#currency_pair").text(`${baseCurrency}/${targetCurrency}`);

        updateConvertedTotal();
        $(
          "#exchange_rate_container, #conversion_row, #exchange_rate_row"
        ).show();
      }
    },
    error: function () {
      console.error("Erro ao buscar a taxa de câmbio.");
      $("#exchange_rate").text("Erro ao buscar taxa.");
    },
  });
}

// Atualizar o total convertido para a moeda selecionada
function updateConvertedTotal(finalTotal) {
  let totalValue =
    parseFloat(
      $("#final_total")
        .text()
        .replace(/[^\d,.-]/g, "") // Remove caracteres não numéricos
        .replace(/\./g, "") // Remove pontos separadores de milhar
        .replace(",", ".") // Substitui vírgula decimal por ponto
    ) || 0;

  let convertedTotal = parseFloat(totalValue) * parseFloat(exchangeRate);

  $("#converted_total").text(
    formatCurrency(convertedTotal, $("#currency").val())
  );
  if ($("#manual_exchange_rate").val() > 0) {
    $("#converted_totalInput").val(
      cleanCurrencyValue(formatCurrency(convertedTotal, $("#currency").val()))
    );
  }
}

$("#currency").on("change", function () {
  let selectedCurrency = $(this).val();
  $("#selected_currency").text(selectedCurrency);

  fetchExchangeRate(userCurrency, selectedCurrency);
  updateExchangeRateText();
});

$("#manual_exchange_rate").on("input", function () {
  exchangeRate = parseFloat($(this).val()) || 1;
  updateConvertedTotal();
});

$("#currency").on("change", function () {
  let selectedCurrency = $(this).val();
  $("#selected_currency").text(selectedCurrency);

  fetchExchangeRate(userCurrency, selectedCurrency);
});

$("#manual_exchange_rate").on("input", function () {
  exchangeRate = parseFloat($(this).val()) || 1;
  updateConvertedTotal();
  updateExchangeRateText();
});

// Atualizar o texto da taxa de câmbio no sumário
function updateExchangeRateText() {
  let selectedCurrency = $("#currency").val();
  $("#exchange_rate").text(exchangeRate.toFixed(6));
  $("#currency_pair").text(`AOA/${selectedCurrency}`);
}

function selectCountry(selectedCountry = "", selectedCity = "") {
  const username = "israelsouza";
  const countrySelect = $("#country");
  const citySelect = $("#city");

  countrySelect
    .html('<option value="">Carregando lista de países...</option>')
    .trigger("change");
  citySelect
    .html('<option value="">Selecione um país primeiro</option>')
    .trigger("change");

  fetch(`https://secure.geonames.org/countryInfoJSON?username=${username}`)
    .then((response) => response.json())
    .then((data) => {
      if (!data.geonames) throw new Error("API retornou dados inválidos");

      countryMap = {};
      let options = '<option value="">Selecione um país</option>';

      data.geonames.forEach((country) => {
        countryMap[country.countryName] = country.geonameId;
        options += `<option value="${country.countryName}">${country.countryName}</option>`;
      });

      countrySelect.html(options).trigger("change");
      countrySelect.select2({
        width: "100%",
        placeholder: "Selecione um país",
        allowClear: false,
        dropdownParent: countrySelect.parent(),
      });

      if (selectedCountry) {
        countrySelect.val(selectedCountry).trigger("change");
        loadCities(selectedCountry, selectedCity);
      }
    })
    .catch((error) => {
      console.error("❌ Erro ao carregar países:", error);
      countrySelect
        .html('<option value="">Erro ao carregar</option>')
        .trigger("change");
    });
}

function loadCities(countryName, selectedCity = "") {
  const citySelect = $("#city");
  const countryId = countryMap[countryName];

  if (!countryId) {
    citySelect
      .html('<option value="">Selecione um país primeiro</option>')
      .trigger("change");
    return;
  }

  citySelect
    .html('<option value="">Carregando cidades...</option>')
    .trigger("change");

  fetch(
    `https://secure.geonames.org/childrenJSON?geonameId=${countryId}&username=israelsouza`
  )
    .then((response) => response.json())
    .then((data) => {
      if (!data.geonames) throw new Error("API retornou dados inválidos");

      let options = '<option value="">Selecione uma cidade</option>';
      data.geonames.forEach((city) => {
        let isSelected = city.name === selectedCity ? "selected" : "";
        options += `<option value="${city.name}" ${isSelected}>${city.name}</option>`;
      });

      citySelect.html(options).trigger("change");
      citySelect.select2({
        width: "100%",
        placeholder: "Selecione uma cidade",
        allowClear: false,
        dropdownParent: citySelect.parent(),
      });

      if (selectedCity) {
        citySelect.val(selectedCity).trigger("change");
      }
    })
    .catch((error) => {
      console.error("❌ Erro ao carregar cidades:", error);
      citySelect
        .html('<option value="">Erro ao carregar</do not get translated>')
        .trigger("change");
    });
}

// Função para lidar com a seleção da opção "Outro"
function handleOtherOption() {
  const dueDateSelect = document.getElementById("due_date");
  const otherDateInput = document.getElementById("other_date_input");

  // Exibe o campo de data quando a opção "Outro" for selecionada
  if (dueDateSelect.value === "other") {
    otherDateInput.style.display = "block"; // Mostra o campo de data
  } else {
    otherDateInput.style.display = "none"; // Oculta o campo de data
  }
}

// Função para calcular a diferença de dias e atualizar o valor do "Outro"
function addCustomDateOption() {
  const customDateInput = document.getElementById("custom_date");
  const dueDateSelect = document.getElementById("due_date");

  if (customDateInput.value) {
    const today = new Date();
    const selectedDate = new Date(customDateInput.value);
    const timeDifference = selectedDate.getTime() - today.getTime();
    const daysDifference = Math.ceil(timeDifference / (1000 * 3600 * 24)); // Diferença em dias

    if (daysDifference > 0) {
      // Tenta encontrar a opção "Outro", ou cria se não existir
      let otherOption = dueDateSelect.querySelector(
        'option[data-type="other"]'
      );

      if (!otherOption) {
        otherOption = document.createElement("option");
        otherOption.dataset.type = "other"; // Marca a opção como "Outro"
        dueDateSelect.appendChild(otherOption); // Adiciona a opção ao select
      }

      // Atualiza o texto e valor da opção "Outro"
      otherOption.value = daysDifference; // Define o valor como número de dias
      otherOption.text = `Customizado - (${daysDifference} Dias)`; // Atualiza o texto exibido
      dueDateSelect.value = daysDifference; // Seleciona automaticamente a opção
    } else {
      alert("Selecione uma data futura.");
    }
  }
}

$("#saveInvoiceBtn").on("click", function (event) {
  event.preventDefault(); // Evita o comportamento padrão do botão (submit)

  let missingFields = [];
  let contactSelected = $("#contact-select").val(); // Verifica se um contato foi selecionado
  let isContactFormVisible = $("#contact-form").is(":visible"); // Verifica se o formulário de novo contato está visível

  // Validação dos campos obrigatórios do formulário de contato (caso esteja visível)
  if (isContactFormVisible) {
    $("#contact-form .form-control[required]").each(function () {
      if (!$(this).val().trim()) {
        let label =
          $(this).closest("div").find("label").text() || "Campo obrigatório";
        missingFields.push(label);
      }
    });
  }

  // Validação se um contato foi selecionado ou se o formulário de contato está preenchido
  if (!contactSelected && missingFields.length === 0 && !isContactFormVisible) {
    Swal.fire({
      icon: "error",
      title: "Erro",
      text: "Por favor, selecione um contato ou preencha os dados de um novo contato.",
    });
    return;
  }

  // Verifica se há campos obrigatórios faltando
  if (missingFields.length > 0) {
    Swal.fire({
      icon: "error",
      title: "Campos obrigatórios faltando:",
      html: missingFields.join("<br>"), // Mostra os campos em lista
    });
    return;
  }

  // Verifica se tem ao menos um item na lista
  if ($("#items_list .item-box").length === 0) {
    Swal.fire({
      icon: "error",
      title: "Erro",
      text: "Adicione ao menos um produto/serviço antes de salvar.",
    });
    return;
  }

  // Continua com o salvamento se todas as validações passarem
  let invoiceData = $("#formFatura").serializeArray();
  let items = [];

  $("#items_list .item-box").each(function () {
    let item = {
      id: $(this).attr("id").replace("item-", ""),
      quantity: $(this).find("input:eq(3)").val(),
      unit_price: $(this).find("input:eq(2)").val(),
      discount: $(this).find("input:eq(4)").val(),
      tax: $(this).find("select").val(),
    };
    items.push(item);
  });

  $.ajax({
    url: "create_invoices/ajax/save_invoices.php",
    method: "POST",
    data: {
      invoice: invoiceData,
      items: items,
    },
    success: function (response) {
      if (response.success) {
        Swal.fire({
          icon: "success",
          title: "Sucesso",
          text: "Fatura salva com sucesso!",
          showCancelButton: true,
          confirmButtonText: "Criar nova factura",
          cancelButtonText: "Minhas Faturas",
          reverseButtons: true,
        }).then((result) => {
          if (result.isConfirmed) {
            // Recarrega a página para criar nova fatura
            window.location.reload();
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Vai para a lista de faturas
            window.location.href = "list_invoices.php";
          }
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Erro",
          text: "Erro ao salvar a fatura.",
        });
      }
    },
    error: function (xhr, status, error) {
      Swal.fire({
        icon: "error",
        title: "Erro",
        text: "Erro ao conectar ao servidor.",
      });
    },
  });
});

function loadInvoiceForEdit(id) {
    $.getJSON('invoices/ajax/get_invoice.php', { id: id }, function(data) {
        if (data.error) {
            Swal.fire('Erro', data.error, 'error');
            return;
        }

        // Definir ID da fatura para edição
        $('#edit_invoice_id').val(id);

        // Preencher campos
        $('#contact-select').val(data.contact_id).trigger('change');
        $('#issue_date').val(data.issue_date);

        // Verificar se a data de vencimento existe no select, se não, adicionar
        if ($("#due_date option[value='" + data.due_date + "']").length === 0) {
            $('#due_date').append(new Option(data.due_date, data.due_date));
        }
        $('#due_date').val(data.due_date);

        $('#reference').val(data.reference);
        $('#observation').val(data.observation);

        // Verificar se a série existe no select, se não, adicionar
        if ($("#series option[value='" + data.series + "']").length === 0) {
            $('#series').append(new Option(data.series, data.series));
        }
        $('#series').val(data.series);

        $('#retention').val(data.retention);

        // moeda: prioriza a da própria fatura; fallback para moeda da empresa; default AOA
        $('#currency').val(data.currency || data.currency_items || data.currency_company || 'AOA').trigger('change');
        if (data.manual_exchange_rate) {
             $('#manual_exchange_rate').val(data.manual_exchange_rate);
        }

        // Limpar itens existentes
        $("#items_list .item-box").remove();

        // Preencher itens
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                addItemRow({
                    id: item.item_id || item.id,
                    code: item.code,
                    description: item.description,
                    unit_price: item.unit_price,
                    quantity: item.quantity,
                    tax: item.tax,
                    discount: item.discount
                });
            });
        }
        updateInvoiceSummary();

        $('#saveInvoiceBtn').text('Atualizar Fatura');
    });
}
});