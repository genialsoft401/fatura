$(document).ready(function () {
  const vat_regime = JSON.parse(localStorage.getItem("vat_regime"));
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
          $("#contact_name").val(contato.name).prop("disabled", true);
          $("#email").val(contato.email).prop("disabled", true);
          $("#contributor").val(contato.contributor).prop("disabled", true);
          $("#po_box").val(contato.po_box).prop("disabled", true);
          $("#telephone").val(contato.telephone).prop("disabled", true);
          $("#address").val(contato.address).prop("disabled", true);
          selectCountry(contato.country, contato.city);

          $("#country").prop("disabled", true);
          $("#city").prop("disabled", true);

          $("#contact-form").show();

          console.log(" Formulário agora está visível.");
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
        "#contact-form input, #contact-form textarea, #contact-form select",
      ).prop("disabled", false);
      $(
        "#contact-form input:not(#contact_id), #contact-form textarea, #contact-form select",
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
  if (dueDateSelect) {
    if (dueDateSelect.value === "other") {
      document.getElementById("other_date_input").style.display = "block";
    }

    // Adiciona eventos para o select e input de data
    dueDateSelect.addEventListener("change", handleOtherOption);
  }
  if (customDateInput) {
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
        let options =
          '<option value="">Selecione um produto/serviço...</option>';
        $.each(data?.data, function (index, item) {
          options += `<option value="${item.id}" data-item='${JSON.stringify(
            item,
          )}'>
            ${item.item_type !== "service" ? `<b><i class="bi bi-box"></i> ${item.code}</b> -` : "<b><i class='bi bi-gear'></i></b>"} ${item.description || item.name}
            </option>`;
        });
        $("#item_select").html(options);
        $(".select2").select2();
      },
    });
  }

  async function loadContacts() {
    const select = $("#contact-select");

    try {
      // loading
      select
        .prop("disabled", true)
        .html('<option value="">Carregando contatos...</option>');

      const response = await $.ajax({
        url: "contacts/ajax/fetch_contacts.php",
        type: "GET",
        dataType: "json",
      });

      // limpa select
      select.empty();

      // opção padrão
      select.append('<option value="">Selecione um contato...</option>');

      // valida retorno
      if (
        response.success &&
        Array.isArray(response.data) &&
        response.data.length > 0
      ) {
        response.data.forEach((contato) => {
          select.append(`
          <option value="${contato.id}">
            ${contato.name} - ${contato.email}
          </option>
        `);
        });
      } else {
        select.append(`
        <option value="">
          Nenhum contato encontrado
        </option>
      `);
      }

      // atualiza select2
      if (select.hasClass("select2-hidden-accessible")) {
        select.trigger("change");
      }

      return response.data;
    } catch (xhr) {
      console.error("❌ Erro ao carregar contatos:", xhr);

      Swal.fire({
        icon: "error",
        title: "Erro",
        text: "Não foi possível carregar os contatos.",
      });

      throw xhr;
    } finally {
      select.prop("disabled", false);
    }
  }

  function showTableItemsList() {
    let element = document.querySelector("#items_list");
    if (element) {
      element.classList.remove("d-none");
    }
  }

  function calculateRowTotal(row) {
    let price = parseFloat(row.find(".field_price").val()) || 0;
    let qtd = parseFloat(row.find(".field_qtd").val()) || 0;
    let discount = parseFloat(row.find(".field_desc").val()) || 0;
    let tax = parseFloat(row.find(".field_tax").val()) || 0;

    // =====================================
    // SUBTOTAL
    // =====================================

    let subtotal = price * qtd;

    // =====================================
    // IVA
    // =====================================

    let taxValue = 0;

    if (vat_regime === "geral") {
      taxValue = subtotal * tax / 100;
    } else {
      taxValue = 0.0;
    }

    // total com IVA
    let totalWithTax = subtotal + taxValue;

    // =====================================
    // DESCONTO (%)
    // =====================================

    let totalDiscount = (discount / 100) * totalWithTax;

    // =====================================
    // TOTAL FINAL
    // =====================================

    let totalFinal = totalWithTax - totalDiscount;

    row.find(".row-total").text(totalFinal.toFixed(2));
  }

  function calculateGrandTotal() {
    let grandTotal = 0;

    $(".row-item").each(function () {
      let value = $(this).find(".row-total").text().trim();

      // remove separadores de milhares
      value = value.replace(/\./g, "");

      // troca vírgula por ponto
      value = value.replace(",", ".");

      let rowTotal = parseFloat(value) || 0;

      grandTotal += rowTotal;
    });

    $("#grand_total").text(
      grandTotal.toLocaleString("pt-PT", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }),
    );
  }

  // Adicionar item ao selecionar
  $("#item_select").on("change", function () {
    var selected = $(this).find(":selected").data("item");
    if (selected) {
      addItemRow(selected);
      $(this).val("").trigger("change"); // Opcional: limpar seleção
    }
  });

  function addItemRow(item) {
    const itemId = item?.id || item?.item_id;

    if (!itemId) return;

    // Evitar duplicação
    if ($(`#item-${itemId}`).length) {
      return;
    }

    // Dados do item
    const description = item?.name || item?.description || "";
    const code = item?.code || item?.codigo || "";
    const retention = item?.retention || 0;

    // IVA
    const itemTax = Number(item?.tax ?? 0);
    const taxValue =
      String(vat_regime).toLowerCase() === "geral" ? itemTax : 0;

    console.log({
      vat_regime,
      itemTax,
      taxValue,
    });

    // Serviço ou Produto
    const isService =
      item?.item_type === "service" ||
      String(code).toUpperCase().startsWith("SERV");

    // Quantidade máxima
    const maxQty = isService
      ? 999999
      : Number(item?.quantity ?? item?.stock_quantity ?? 9999);

    // Preço unitário
    const unitPrice = Number(
      item?.unit_price ?? item?.sale_price ?? item?.cost_price ?? 0,
    );

    // Desconto
    const discount = Number(item?.discount ?? 0);

    const itemHtml = `
    <div
        class="row row-item item-list align-items-center mb-2"
        id="item-${itemId}"
        data-id="${itemId}"
    >

        <div class="text-center" style="width:120px">
            <input
                type="text"
                class="form-control field_code"
                value="${code}"
                readonly
            >
        </div>

        <div class="d-none">
            <input
                type="hidden"
                class="field_retention"
                value="${retention}"
            >
        </div>

        <div class="col-3">
            <input
                type="text"
                class="form-control field_description"
                value="${description}"
                readonly
            >
        </div>

        <div class="col-2">
            <input
                type="number"
                class="form-control field_price"
                value="${unitPrice.toFixed(2)}"
                step="0.01"
                min="0"
            >
        </div>

        <div class="col-1">
            <input
                type="number"
                class="form-control field_qtd"
                value="1"
                min="1"
                max="${maxQty}"
            >
        </div>

        <div style="width:90px">
            <input
                type="number"
                class="form-control field_tax"
                value="${taxValue}"
                step="0.01"
                readonly
            >
        </div>

        <div class="col-1">
            <input
                type="number"
                class="form-control field_desc"
                value="${discount}"
                step="0.01"
                min="0"
            >
        </div>

        <div class="col-2 text-center row-total text-success fw-bold">
            0,00
        </div>

        <div style="width:50px" class="text-center">
            <i
                class="bi bi-trash remove-item cursor"
                data-id="${itemId}"
                role="button"
                title="Remover item"
            ></i>
        </div>

    </div>
    `;

    $("#items_list").append(itemHtml);

    const newRow = $(`#item-${itemId}`);

    newRow.find(".field_code").on("keydown paste", function (e) {
      e.preventDefault();
    });

    newRow
      .find(".field_price, .field_qtd, .field_desc")
      .on("input change", function () {
        calculateRowTotal(newRow);

        if (typeof updateInvoiceSummary === "function") {
          updateInvoiceSummary();
        } else if (typeof calculateGrandTotal === "function") {
          calculateGrandTotal();
        }
      });

    newRow.find(".remove-item").on("click", function () {
      newRow.remove();

      if (typeof updateInvoiceSummary === "function") {
        updateInvoiceSummary();
      } else if (typeof calculateGrandTotal === "function") {
        calculateGrandTotal();
      }

      if (
        $("#items_list .item-list").length === 0 &&
        typeof hideTableItemsList === "function"
      ) {
        hideTableItemsList();
      }
    });

    calculateRowTotal(newRow);

    if (typeof updateInvoiceSummary === "function") {
      updateInvoiceSummary();
    } else if (typeof calculateGrandTotal === "function") {
      calculateGrandTotal();
    }

    if (typeof showTableItemsList === "function") {
      showTableItemsList();
    }
  }

  // Remover item da lista
  $(document).on("click", ".remove-item", function () {
    let itemId = $(this).data("id");
    $(`#item-${itemId}`).remove();
  });

  $(document).on("input", ".field_price, .field_qtd, .field_desc", function () {
    let row = $(this).closest(".row-item");
    calculateRowTotal(row);
    calculateGrandTotal();
    updateInvoiceSummary();
  });

  // Buscar moeda, símbolo e posição via AJAX
  // OBS: Para emissão/edição de faturas, usamos como padrão AOA (Kz) quando possível,
  // porque em Angola a moeda padrão do sistema deve ser Kwanza.
  function fetchCurrencySymbol(selectedIso = "AOA") {
    $.ajax({
      url: "assets/ajax/get_currency.php",
      type: "GET",
      dataType: "json",
      success: function (data) {
        // tenta usar a moeda pedida; se o endpoint retornar outra, cai pra ela
        const iso = selectedIso || data.currency || "AOA";
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

  function resetInvoiceSummary() {
    // =========================
    // VALORES ZERO PADRÃO
    // =========================
    const zeroValue = 0;
    const zeroFormatted = formatCurrency(0, currencySymbol, currencyPosition);

    // =========================
    // UI PRINCIPAL
    // =========================
    $("#total_sum").text(zeroFormatted);
    $("#total_discount").text(zeroFormatted);
    $("#subtotal_without_tax").text(zeroFormatted);
    $("#total_tax").text(zeroFormatted);
    $("#retention_value").text(zeroFormatted);
    $("#final_total").text(zeroFormatted);

    // =========================
    // TABELA IMPOSTOS
    // =========================
    $("#tax_summary").html(`
        <tr>
            <td colspan="5" class="text-center text-muted">
                Nenhum item adicionado
            </td>
        </tr>
    `);

    // =========================
    // INPUTS HIDDEN (IDs)
    // =========================
    $("#total_sumInput").val(zeroValue.toFixed(2));
    $("#total_discountInput").val(zeroValue.toFixed(2));
    $("#subtotal_without_taxInput").val(zeroValue.toFixed(2));
    $("#total_taxInput").val(zeroValue.toFixed(2));
    $("#retention_valueInput").val(zeroValue.toFixed(2));
    $("#final_totalInput").val(zeroValue.toFixed(2));

    // =========================
    // INPUTS HIDDEN (NAME)
    // =========================
    $("input[name='total_sum']").val(zeroValue.toFixed(2));
    $("input[name='total_discount']").val(zeroValue.toFixed(2));
    $("input[name='subtotal_without_tax']").val(zeroValue.toFixed(2));
    $("input[name='total_tax']").val(zeroValue.toFixed(2));
    $("input[name='retention_value']").val(zeroValue.toFixed(2));
    $("input[name='final_total']").val(zeroValue.toFixed(2));

    // =========================
    // RETENÇÃO (IMPORTANTE)
    // =========================
    // NÃO forçar recalculo interno
    if (typeof updateRetention === "function") {
      updateRetention(zeroValue, zeroValue, zeroValue);
    }
  }

  let finalTotal = 0;

  // ao mudar a moeda, recalcula com o símbolo correto
  $(document).on("change", "#currency", function () {
    const iso = $(this).val() || "AOA";
    // não consulta API externa aqui; só ajusta símbolo/posição via backend padrão
    fetchCurrencySymbol(iso);
  });

  function updateInvoiceSummary() {
    let totalSum = 0;
    let totalDiscount = 0;
    let taxExemptIncidence = 0;
    let tax14Incidence = 0;
    let totalTax = 0;
    let totalRetention = 0;

    const itemList = document.querySelectorAll(".item-list");

    if (!itemList.length) {
      resetInvoiceSummary();
      return;
    }

    let grouped = {};

    itemList.forEach((row) => {
      const $row = $(row);

      const toNumber = (value) =>
        parseFloat(
          String(value || "0")
            .replace(",", ".")
            .replace("%", "")
            .trim(),
        ) || 0;

      const price = toNumber($row.find(".field_price").val());
      const qtd = toNumber($row.find(".field_qtd").val());
      const discountPercent = toNumber($row.find(".field_desc").val());
      const taxNum = toNumber($row.find(".field_tax").val());
      const retentionRate = toNumber($row.find(".field_retention").val());

      const lineTotal = price * qtd;

      const discountValue = (lineTotal * discountPercent) / 100;
      const safeDiscount = Math.min(discountValue, lineTotal);

      const lineSubtotal = lineTotal - safeDiscount;

      totalSum += lineTotal;
      totalDiscount += safeDiscount;

      // ==================================================
      // IVA
      // Regra: taxa 7 = Isento = IVA 0%
      // ==================================================
      const effectiveTaxRate = taxNum === 7 ? 0 : taxNum;
      const ivaValue = (lineSubtotal * effectiveTaxRate) / 100;

      // ==================================================
      // RETENÇÃO
      // ==================================================
      const retentionValue = (lineSubtotal * retentionRate) / 100;

      totalRetention += retentionValue;

      // ==================================================
      // TOTAL DA LINHA
      // ==================================================
      const net = lineSubtotal + ivaValue - retentionValue;

      // ==================================================
      // INCIDÊNCIA IVA
      // ==================================================
      if (taxNum === 14) {
        tax14Incidence += lineSubtotal;
      } else {
        taxExemptIncidence += lineSubtotal;
      }

      totalTax += ivaValue;

      // ==================================================
      // AGRUPAMENTO
      // Mostra 0% quando a taxa original é 7
      // ==================================================
      const groupTax = taxNum === 7 ? 0 : taxNum;

      if (!grouped[groupTax]) {
        grouped[groupTax] = {
          base: 0,
          iva: 0,
          retention: 0,
          net: 0,
        };
      }

      grouped[groupTax].base += lineSubtotal;
      grouped[groupTax].iva += ivaValue;
      grouped[groupTax].retention += retentionValue;
      grouped[groupTax].net += net;
    });

    totalSum = +totalSum.toFixed(2);
    totalDiscount = +totalDiscount.toFixed(2);
    totalTax = +totalTax.toFixed(2);
    totalRetention = +totalRetention.toFixed(2);

    const subtotalWithoutTax = +(totalSum - totalDiscount).toFixed(2);

    let finalTotal = +(subtotalWithoutTax + totalTax - totalRetention).toFixed(
      2,
    );

    if (finalTotal < 0) {
      finalTotal = 0;
    }

    $("#total_sum").text(
      formatCurrency(totalSum, currencySymbol, currencyPosition),
    );

    $("#total_discount").text(
      formatCurrency(totalDiscount, currencySymbol, currencyPosition),
    );

    $("#subtotal_without_tax").text(
      formatCurrency(subtotalWithoutTax, currencySymbol, currencyPosition),
    );

    $("#total_tax").text(
      formatCurrency(totalTax, currencySymbol, currencyPosition),
    );

    $("#retention_value").text(
      formatCurrency(totalRetention, currencySymbol, currencyPosition),
    );

    $("#final_total").text(
      formatCurrency(finalTotal, currencySymbol, currencyPosition),
    );

    // ==================================================
    // RESUMO DE IMPOSTOS
    // ==================================================
    const tax_summary = $("#tax_summary");
    let html = "";

    Object.keys(grouped).forEach((tax) => {
      const g = grouped[tax];

      html += `
      <tr>
        <td>${tax}%</td>
        <td>${formatCurrency(g.base, currencySymbol, currencyPosition)}</td>
        <td>${formatCurrency(g.iva, currencySymbol, currencyPosition)}</td>
        <td>${formatCurrency(
          g.retention,
          currencySymbol,
          currencyPosition,
        )}</td>
        <td>${formatCurrency(g.net, currencySymbol, currencyPosition)}</td>
      </tr>
    `;
    });

    tax_summary.html(html);

    const setVal = (name, value) => {
      $(`input[name='${name}']`).val(value.toFixed(2));
    };

    setVal("total_sum", totalSum);
    setVal("total_discount", totalDiscount);
    setVal("subtotal_without_tax", subtotalWithoutTax);
    setVal("total_tax", totalTax);
    setVal("retention_value", totalRetention);
    setVal("final_total", finalTotal);

    $("#total_sumInput").val(totalSum.toFixed(2));
    $("#total_discountInput").val(totalDiscount.toFixed(2));
    $("#subtotal_without_taxInput").val(subtotalWithoutTax.toFixed(2));
    $("#total_taxInput").val(totalTax.toFixed(2));
    $("#retention_valueInput").val(totalRetention.toFixed(2));
    $("#final_totalInput").val(finalTotal.toFixed(2));

    updateRetention(subtotalWithoutTax, totalTax, totalRetention);
  }

  function updateRetention(subtotal, totalTax, totalRetentionItems) {
    let perc = parseFloat($("#retention").val()) || 0;

    // base já calculada corretamente
    let base = subtotal + totalTax - totalRetentionItems;

    let extraRetention = 0;

    // retenção adicional global (opcional)
    if (perc > 0) {
      extraRetention = (base * perc) / 100;
    }

    let final = base - extraRetention;

    // UI
    if (extraRetention > 0 || totalRetentionItems > 0) {
      $("#retention_sumary").removeClass("d-none");

      $("#retention_value").text(
        formatCurrency(
          totalRetentionItems + extraRetention,
          currencySymbol,
          currencyPosition,
        ),
      );
    } else {
      $("#retention_sumary").addClass("d-none");
    }

    $("#final_total").text(
      formatCurrency(final, currencySymbol, currencyPosition),
    );

    // $("#final_totalInput").val(final.toFixed(2));

    updateConvertedTotal?.();
  }

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
            "#exchange_rate_container, #conversion_row, #exchange_rate_row",
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
          .replace(",", "."), // Substitui vírgula decimal por ponto
      ) || 0;

    let convertedTotal = parseFloat(totalValue) * parseFloat(exchangeRate);

    $("#converted_total").text(
      formatCurrency(convertedTotal, $("#currency").val()),
    );
    if ($("#manual_exchange_rate").val() > 0) {
      $("#converted_totalInput").val(
        cleanCurrencyValue(
          formatCurrency(convertedTotal, $("#currency").val()),
        ),
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
      `https://secure.geonames.org/childrenJSON?geonameId=${countryId}&username=israelsouza`,
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
          'option[data-type="other"]',
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
    event.preventDefault();

    let missingFields = [];
    let contactSelected = $("#contact-select").val();
    let isContactFormVisible = $("#contact-form").is(":visible");

    // =========================
    // VALIDAR CONTACTO
    // =========================
    if (isContactFormVisible) {
      $("#contact-form .form-control[required]").each(function () {
        if (!String($(this).val() || "").trim()) {
          let label =
            $(this).closest("div").find("label").text() || "Campo obrigatório";

          missingFields.push(label);
        }
      });
    }

    if (
      !contactSelected &&
      missingFields.length === 0 &&
      !isContactFormVisible
    ) {
      Swal.fire({
        icon: "error",
        title: "Erro",
        text: "Por favor, selecione um contato ou preencha os dados de um novo contato.",
      });
      return;
    }

    if (missingFields.length > 0) {
      Swal.fire({
        icon: "error",
        title: "Campos obrigatórios faltando:",
        html: missingFields.join("<br>"),
      });
      return;
    }

    // =========================
    // VALIDAR ITENS
    // =========================
    if ($("#items_list .item-list").length === 0) {
      Swal.fire({
        icon: "error",
        title: "Erro",
        text: "Adicione ao menos um produto/serviço antes de salvar.",
      });
      return;
    }

    // =========================
    // DADOS DA FATURA
    // =========================
    let invoiceData = $("#formFatura").serializeArray();
    let items = [];

    $("#items_list .item-list").each(function () {
      items.push({
        id: $(this).attr("id").replace("item-", ""),
        code: parseFloat($(this).find(".field_code").val()) || 1,
        quantity: parseFloat($(this).find(".field_qtd").val()) || 1,
        unit_price: parseFloat($(this).find(".field_price").val()) || 0,
        discount: parseFloat($(this).find(".field_desc").val()) || 0,
        tax: parseFloat($(this).find(".field_tax").val()) || 0,
      });
    });

    // =========================
    // AJAX
    // =========================
    $.ajax({
      url: "create_invoices/ajax/save_invoices.php",
      method: "POST",
      dataType: "json",
      data: {
        invoice: invoiceData,
        items: items,
      },
      success: function (response) {
        if (!response.success) {
          return Swal.fire({
            icon: "error",
            title: "Erro",
            text: response.message || "Erro ao salvar a fatura.",
          });
        }

        Swal.fire({
          icon: "success",
          title: "Fatura criada com sucesso!",
          text: "Clique abaixo para visualizar.",
          confirmButtonText: "Ver fatura",
          confirmButtonColor: "#007abd",
        }).then((result) => {
          if (result.isConfirmed) {
            //  usar ID dinâmico vindo do backend
            window.location.href = `invoice.php?id=${response.invoice_id}`;
          }
        });
      },
      error: function (xhr, status, error) {
        Swal.fire({
          icon: "error",
          title: "Erro",
          text: "Erro ao conectar ao servidor.",
        });

        console.log("STATUS:", status);
        console.log("ERROR:", error);
        console.log("RESPONSE:", xhr.responseText);
      },
    });
  });

  function loadInvoiceForEdit(id) {
    $.getJSON("invoices/ajax/get_invoice.php", { id: id }, function (response) {
      if (response.error) {
        Swal.fire("Erro", response.error, "error");
        return;
      }

      const data = response?.data;

      // Definir ID da fatura para edição
      $("#edit_invoice_id").val(id);

      // Preencher campos
      $("#contact-select").val(data.contact_id).trigger("change");
      $("#issue_date").val(data.issue_date);

      // Verificar se a data de vencimento existe no select, se não, adicionar
      if ($("#due_date option[value='" + data.due_date + "']").length === 0) {
        $("#due_date").append(new Option(data.due_date, data.due_date));
      }
      $("#due_date").val(data.due_date);

      $("#reference").val(data.reference);
      $("#observation").val(data.observation);

      // Verificar se a série existe no select, se não, adicionar
      if ($("#series option[value='" + data.series + "']").length === 0) {
        $("#series").append(new Option(data.series, data.series));
      }
      $("#series").val(data.series);

      $("#retention").val(data.retention);

      // moeda: prioriza a da própria fatura; fallback para moeda da empresa; default AOA
      $("#currency")
        .val(
          data.currency ||
            data.currency_items ||
            data.currency_company ||
            "AOA",
        )
        .trigger("change");
      if (data.manual_exchange_rate) {
        $("#manual_exchange_rate").val(data.manual_exchange_rate);
      }

      // Limpar itens existentes
      $("#items_list .item-list").remove();

      // Preencher itens
      if (data.items && data.items.length > 0) {
        data.items.forEach((item) => {
          addItemRow({
            id: item.item_id || item.id,
            code: item.code,
            description: item.description,
            unit_price: item.unit_price,
            quantity: item.quantity,
            tax: item.tax,
            discount: item.discount,
          });
        });
      }
      updateInvoiceSummary();

      $("#saveInvoiceBtn").text("Atualizar Fatura");
    });
  }
});
