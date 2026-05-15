$(document).ready(function () {
  let items = [];
  const modalEl = document.getElementById("itemModal");
  const modalTitle = modalEl.querySelector(".modal-title");

  const defaultTitle = "Adicionar Novo Produto/Serviço";
  let table;

  $.ajax({
    url: "items/ajax/get_items.php",
    method: "GET",
    dataType: "json",

    success: function (response) {
      console.log("RESPONSE:", response?.data);

      // garante array correto
      if (Array.isArray(response?.data)) {
        items = response?.data;
      } else if (Array.isArray(response)) {
        items = response;
      } else {
        items = [];
      }

      renderTable(items);
    },

    error: function (xhr, status, error) {
      console.error("Erro:", error);
    },
  });

  function renderTable(data) {
    const $table = $("#itemsTable");
    const $tbody = $table.find("tbody");

    // destruir DataTable antes de mexer no DOM
    if ($.fn.DataTable.isDataTable($table)) {
      $table.DataTable().clear().destroy();
    }

    $tbody.empty();

    // =========================
    // SEM DADOS
    // =========================
    if (!data || !data.length) {
      $tbody.append(`
      <tr>
        <td class="text-center text-muted py-4">
          Nenhum produto ou serviço encontrado
        </td>
      </tr>
    `);
      return;
    }

    // =========================
    // BUILD ROWS (mais performático)
    // =========================
    let rowsHtml = "";

    data.forEach((row) => {
      const rowData = encodeURIComponent(JSON.stringify(row));

      const description = row.name || row.description || "-";

      const price = row.unit_price || row.cost_price;

      const tax = row.tax || 0;

      rowsHtml += `
      <tr data-id="${row.id}">
        
        <!-- CHECKBOX -->
        <td>
          <input type="checkbox" class="item-checkbox" value="${row.id}">
        </td>

        <!-- ICON -->
        <td>
          <i class="${
            row.item_type === "service"
              ? "bi bi-tag fw-bold fs-5"
              : "bi bi-box-seam fw-bold fs-5"
          }"></i>
        </td>

        <!-- CODE -->
        <td>${row.code || "-"}</td>

        <!-- NAME -->
        <td class="text-truncate-custom" title="${description}">
          ${description}
        </td>

        <!-- DESCRIPTION -->
        <td class="text-truncate-custom" title="${row.description || ""}">
          ${row.description || "-"}
        </td>

        <!-- PRICE -->
        <td class="text-success fw-bold">
          ${formatCurrency(price, row.currency, row.position)}
        </td>

        <!-- TAX -->
        <td>
          ${`${tax}%`}
        </td>

        <!-- PVP -->
        <td class="text-primary fw-bold">
          ${formatCurrency(row.pvp ?? 0, row.currency, row.position)}
        </td>

        <!-- ACTIONS -->
        <td>
          <div class="d-flex justify-content-center gap-2">

            <button class="btn btn-sm text-dark edit-btn"
              data-row="${rowData}"
              data-bs-toggle="tooltip"
              title="Editar">
              <i class="bi bi-pencil fs-6"></i>
            </button>

            <button class="btn btn-sm text-danger delete-btn"
              data-id="${row.id}"
              data-bs-toggle="tooltip"
              title="Excluir">
              <i class="bi bi-trash fs-6"></i>
            </button>

          </div>
        </td>

      </tr>
    `;
    });

    $tbody.html(rowsHtml);

    // =========================
    // REINICIAR DATATABLE
    // =========================
    $table.DataTable({
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      destroy: true,
      autoWidth: false,

      columnDefs: [
        { orderable: false, targets: [0, 1, 8] }, // checkbox, icon, actions
      ],

      language: {
        search: "",
        searchPlaceholder: "Pesquisar produtos...",
        lengthMenu: "Mostrar _MENU_",
        zeroRecords: "Nenhum registro encontrado",
        info: "_START_–_END_ de _TOTAL_",
        infoEmpty: "Sem dados",
        infoFiltered: "(filtrado de _MAX_)",
        paginate: {
          first: "«",
          last: "»",
          next: "›",
          previous: "‹",
        },
      },
    });

    // =========================
    // TOOLTIP (fix duplicação)
    // =========================
    $('[data-bs-toggle="tooltip"]').tooltip("dispose");
    $('[data-bs-toggle="tooltip"]').tooltip();
  }

  // ========================================
  // FORMATA MOEDA
  // ========================================

  function formatCurrency(value) {
    return new Intl.NumberFormat("pt-PT", {
      style: "currency",
      currency: "AOA",
    }).format(Number(value || 0));
  }

  // ========================================
  // PREPARA DADOS
  // ========================================

  function prepareData(data = []) {
    return data.map((item) => ({
      ID: item.id,
      Código: item.code || "-",
      Nome: item.name || "-",
      Tipo: item.item_type || "-",
      Categoria: item.category || "-",
      Unidade: item.unit_measure || "-",
      Preço: formatCurrency(item.unit_price),
      PVP: formatCurrency(item.pvp),
      Quantidade: item.quantity || 0,
      Stock: item.stock_name || "-",
      Estado: item.status || "-",
      Moeda: item.currency || "AOA",
    }));
  }

  // ========================================
  // GERA NOME
  // ========================================

  function generateFileName(type) {
    const date = new Date().toISOString().split("T")[0];

    return `produtos_${date}.${type}`;
  }

  // ========================================
  // EXPORTAR CSV
  // ========================================

  function exportCSV(data = []) {
    if (!data.length) {
      return alert("Nenhum dado encontrado");
    }

    const rows = prepareData(data);

    const headers = Object.keys(rows[0]).join(";");

    const csvContent = rows.map((row) =>
      Object.values(row)
        .map((value) => `"${String(value).replace(/"/g, '""')}"`)
        .join(";"),
    );

    const csv = [headers, ...csvContent].join("\n");

    const blob = new Blob(["\uFEFF" + csv], {
      type: "text/csv;charset=utf-8;",
    });

    const link = document.createElement("a");

    link.href = URL.createObjectURL(blob);

    link.download = generateFileName("csv");

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);
  }

  // ========================================
  // EXPORTAR EXCEL
  // ========================================

  function exportExcel(data = []) {
    if (!data.length) {
      return alert("Nenhum dado encontrado");
    }

    const rows = prepareData(data);

    const worksheet = XLSX.utils.json_to_sheet(rows);

    // largura colunas
    worksheet["!cols"] = [
      { wch: 8 },
      { wch: 20 },
      { wch: 35 },
      { wch: 15 },
      { wch: 15 },
      { wch: 12 },
      { wch: 18 },
      { wch: 18 },
      { wch: 12 },
      { wch: 30 },
      { wch: 12 },
      { wch: 10 },
    ];

    const workbook = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(workbook, worksheet, "Produtos");

    XLSX.writeFile(workbook, generateFileName("xlsx"));
  }

  // ========================================
  // EXPORTAR PDF
  // ========================================

  function exportPDF(data = []) {
    if (!data.length) {
      return alert("Nenhum dado encontrado");
    }

    const rows = prepareData(data);

    const { jsPDF } = window.jspdf;

    const doc = new jsPDF({
      orientation: "landscape",
    });

    doc.setFontSize(16);

    doc.text("Relatório de Produtos", 14, 15);

    const tableColumn = Object.keys(rows[0]);

    const tableRows = rows.map((row) => Object.values(row));

    doc.autoTable({
      head: [tableColumn],

      body: tableRows,

      startY: 25,

      styles: {
        fontSize: 8,
        cellPadding: 2,
      },

      headStyles: {
        fillColor: [41, 128, 185],
        textColor: 255,
        fontStyle: "bold",
      },

      alternateRowStyles: {
        fillColor: [245, 245, 245],
      },
    });

    doc.save(generateFileName("pdf"));
  }

  // ========================================
  // EVENTOS
  // ========================================

  document.getElementById("downloadCSV").addEventListener("click", () => {
    exportCSV(items.data || items);
  });

  document.getElementById("downloadExcel").addEventListener("click", () => {
    exportExcel(items.data || items);
  });

  document.getElementById("downloadPDF").addEventListener("click", () => {
    exportPDF(items.data || items);
  });

  $("#itemsTable").on("click", ".delete-btn", function () {
    const itemId = $(this).data("id");
    const $row = $(this).closest("tr");

    deleteWithUndo(itemId, $row);
  });

  function deleteWithUndo(itemId, $row) {
    let timeout;
    let cancelled = false;

    Swal.fire({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 5000,
      html: `
      <div class="d-flex align-items-center gap-2">
        <span>Item removido</span>
        <button id="undoBtn" class="btn btn-sm btn-light">
          Desfazer
        </button>
      </div>
    `,
      didOpen: () => {
        const undoBtn = document.getElementById("undoBtn");

        undoBtn.addEventListener("click", () => {
          cancelled = true;
          clearTimeout(timeout);

          $row.fadeIn(200);

          Swal.fire({
            toast: true,
            icon: "info",
            position: "top-end",
            title: "Ação cancelada",
            showConfirmButton: false,
            timer: 2000,
          });
        });
      },
    });

    // só executa DELETE depois do tempo
    timeout = setTimeout(() => {
      if (cancelled) return;

      $.ajax({
        url: "items/ajax/delete_item.php",
        method: "POST",
        data: { id: itemId },
        dataType: "json",
      })

        .done(function (res) {
          if (!res.success) {
            // rollback visual se falhar no backend
            $row.fadeIn(200);

            Swal.fire({
              icon: "warning",
              title: "Não eliminado",
              text: res.message || "Não foi possível eliminar o item",
            });
          }
        })

        .fail(function (xhr) {
          $row.fadeIn(200);

          let msg = "Erro ao eliminar item";

          try {
            msg = JSON.parse(xhr.responseText)?.message || msg;
          } catch (e) {}

          Swal.fire({
            icon: "error",
            title: "Erro",
            text: msg,
          });
        });
    }, 5000);
  }

  // selecionar todos
  $("#selectAll").on("change", function () {
    $(".item-checkbox").prop("checked", $(this).is(":checked"));
  });

  $("#deleteSelected").on("click", function () {
    const selected = $(".item-checkbox:checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    if (selected.length === 0) {
      return Swal.fire("Atenção", "Selecione pelo menos um item.", "warning");
    }

    Swal.fire({
      title: `Eliminar ${selected.length} item(s)?`,
      text: "Esta ação não pode ser desfeita!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      confirmButtonText: "Sim, eliminar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (!result.isConfirmed) return;

      Swal.fire({
        title: "A eliminar...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      $.ajax({
        url: "items/ajax/delete_items_bulk.php",
        method: "POST",
        data: { ids: selected },
        dataType: "json",
      })

        .done(function (res) {
          if (res.success) {
            selected.forEach((id) => {
              $(`.item-checkbox[value="${id}"]`)
                .closest("tr")
                .fadeOut(200, function () {
                  $(this).remove();
                });
            });

            Swal.fire({
              toast: true,
              position: "top-end",
              icon: "success",
              title: res.message || `${selected.length} item(s) eliminados`,
              showConfirmButton: false,
              timer: 2500,
            });
          } else {
            Swal.fire({
              icon: "warning",
              title: "Atenção",
              text: res.error || "Não foi possível eliminar os itens.",
            });
          }
        })

        .fail(function (xhr) {
          let msg = "Erro inesperado.";

          try {
            const res = JSON.parse(xhr.responseText);
            msg = res.error || msg;
          } catch (e) {}

          Swal.fire({
            icon: "error",
            title: "Erro",
            text: msg,
          });
        });
    });
  });

  // Função simples de pesquisa (Filtro)
  // Adicione um input com id="searchInput" no seu HTML se quiser usar
  $("#searchInput").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#itemsTable tbody tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });

  $("#itemsTable").on("click", ".edit-btn", function () {
    const rawData = $(this).data("row");

    if (!rawData) {
      console.error("Dados não encontrados para edição.");
      return;
    }

    let row;

    try {
      row = JSON.parse(decodeURIComponent(rawData));
    } catch (e) {
      console.error("Erro ao parse JSON:", e, rawData);
      return;
    }

    const form = document.getElementById("itemForm");
    if (!form) return;

    console.log("EDIT ROW:", row);

    // =========================
    // ID
    // =========================
    if (!form.product_id) {
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = "product_id";
      form.appendChild(hidden);
    }
    form.product_id.value = row.id ?? "";

    // =========================
    // IDENTIFICAÇÃO
    // =========================
    form.codigo.value = row.code ?? "";
    form.name.value = row.name ?? "";
    form.descricao.value = row.description ?? "";

    // =========================
    // CLASSIFICAÇÃO
    // =========================
    $("[name='item_type']", form).val(row.item_type ?? "product");
    $("[name='subcategory']", form).val(row.subcategory ?? "");
    $("[name='unit_measure']", form).val(row.unit_measure ?? "unit");
    $("[name='currency']", form).val(row.currency ?? "AOA");

    // =========================
    // STOCK
    // =========================
    $("[name='stock_id']", form).val(row.stock_id ?? "");

    form.quantidade.value = row.quantity ?? 0;
    form.min_stock.value = row.min_quantity ?? 1;

    // =========================
    // PREÇOS
    // =========================
    form.unit_price.value = row.unit_price ?? 0;
    form.cost_price.value = row.cost_price ?? 0;
    form.sale_price.value = row.sale_price ?? 0;
    form.pvp.value = row.pvp ?? 0;

    // =========================
    // FISCAL
    // =========================
    form.tax.value = row.tax ?? "";

    $("[name='retention']", form).val(row.retention ?? 0);

    // =========================
    // COMPANY
    // =========================
    form.id_company.value = row.company_id ?? "";

    // =========================
    // FORÇA SINCRONIZAÇÃO DA UI
    // =========================

    // =========================
    // MODAL
    // =========================
    const modal = new bootstrap.Modal(modalEl);

    modalTitle.innerHTML = "Editar Produto/Serviço";

    modal.show();

    const isProduct = row.item_type === "product";

    setTimeout(() => {
      !isProduct
        ? $("#depot").removeClass("active")
        : $("#depot").addClass("active");
    }, 200);
  });

  function resetItemForm() {
    const form = document.getElementById("itemForm");
    if (!form) return;

    // limpar inputs
    form.reset();

    // limpar ID (CRÍTICO)
    const idField = form.querySelector("[name='product_id']");
    if (idField) idField.remove();

    // limpar selects jQuery (caso uses select2/bootstrap selects)
    $("[name='item_type']", form).val("product").trigger("change");
    $("[name='subcategory']", form).val("").trigger("change");
    $("[name='stock_id']", form).val("").trigger("change");
    $("[name='currency']", form).val("AOA").trigger("change");

    // limpar campos manuais
    const tax = form.querySelector("#tax");
    if (tax) tax.value = "";

    const retention = form.querySelector("#retention_tax");
    if (retention) retention.value = "0";

    // restaurar título
    modalTitle.innerHTML = defaultTitle;

    // se tiver syncUI global
    if (typeof syncUI === "function") {
      syncUI();
    }
  }

  modalEl.addEventListener("hidden.bs.modal", () => {
    resetItemForm();
  });



  $("#saveEdit").on("click", function () {
    const form = $("#editItemForm");

    let formData = form.serialize(); //
    $.ajax({
      url: "items/ajax/edit_item.php",
      method: "POST",
      data: formData,
      dataType: "json",

      success: function (response) {
        if (response.success) {
          $("#editItemModal").modal("hide");

          Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: "Item atualizado com sucesso!",
            showConfirmButton: false,
            timer: 3000,
          });

          loadItems();
        } else {
          Swal.fire("Erro", response.message || "Erro ao atualizar", "error");
        }
      },

      error: function () {
        Swal.fire("Erro!", "Erro na requisição!", "error");
      },
    });
  });

  $(document).on("change", ".item-checkbox", function () {
    const selectedItems = $(".item-checkbox:checked");

    $("#deleteSelected").html(`
    <i class="bi bi-trash"></i> Eliminar ${selectedItems.length > 0 ? selectedItems.length + " item(s)" : ""}
  `);
  });
});
