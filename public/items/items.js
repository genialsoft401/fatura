$(document).ready(function () {
  let table;

  $.ajax({
    url: "items/ajax/get_items.php",
    method: "GET",
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }

      renderTable(data?.data || []);
    },
    error: function (xhr, status, error) {
      console.error("❌ Erro ao buscar items:", status, error);
    },
  });

  function renderTable(data) {
    let tbody = $("#itemsTable tbody");
    tbody.empty();

    // destrói DataTable corretamente (mais seguro)
    if ($.fn.DataTable.isDataTable("#itemsTable")) {
      $("#itemsTable").DataTable().clear().destroy();
    }

    if (!data.length) {
      tbody.append(`
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            Nenhum produto ou serviço encontrado
          </td>
        </tr>
      `);
      return;
    }

    data.forEach((row) => {
      let rowData = encodeURIComponent(JSON.stringify(row));

      const description = row.name != null ? row.name : row.description;

      const price =
        (row.sale_price !== null && row.sale_price !== ""
          ? row.sale_price
          : null) ??
        (row.cost_price !== null && row.cost_price !== ""
          ? row.cost_price
          : null) ??
        row.unit_price ??
        0;
      const tax = row.tax ?? 0;

      const retention = row.retention_applicable ? "Sim" : "Não";

      tbody.append(`
    <tr data-id="${row.id}">

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

      <!-- DESCRIPTION -->
      <td class="text-truncate-custom" title="${description}">
        ${description}
      </td>

      <!-- DESCRIPTION -->
      <td class="text-truncate-custom" title="${row.description}">
        ${row.description}
      </td>

      <!-- PRICE -->
      <td class="text-success fw-bold">
        ${formatCurrency(row.unit_price, row.currency, row.position)}
      </td>

      <!-- TAX -->
      <td>
        ${row.vat_applicable ? `${tax}%` : "Isento"}
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
  `);
    });
    // reinicializa DataTable após render
    table = $("#itemsTable").DataTable({
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],

      destroy: true, // evita conflitos futuros

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

    // tooltips (evita duplicação)
    setTimeout(() => {
      $('[data-bs-toggle="tooltip"]').tooltip("dispose"); // limpa antigos
      $('[data-bs-toggle="tooltip"]').tooltip(); // recria
    }, 150);
  }

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

    const row = JSON.parse(decodeURIComponent(rawData));
    const form = document.getElementById("editItemForm");

    if (!form) return;

    // =========================
    // ID
    // =========================
    form.id.value = row.id ?? "";

    // =========================
    // CÓDIGO (hidden + display)
    // =========================
    if (form.codigo) form.codigo.value = row.code ?? "";

    const codigoDisplay = document.getElementById("codigo_display");
    if (codigoDisplay) codigoDisplay.value = row.code ?? "";

    // =========================
    // IDENTIFICAÇÃO
    // =========================
    if (form.name) form.name.value = row.name ?? "";
    if (form.descricao) form.descricao.value = row.description ?? "";

    // =========================
    // CLASSIFICAÇÃO
    // =========================
    $(form.querySelector("[name='item_type']"))
      .val(row.item_type ?? "product")
      .trigger("change");

    $(form.querySelector("[name='unit_measure']"))
      .val(row.unit_measure ?? "unit")
      .trigger("change");

    $(form.querySelector("[name='currency']"))
      .val(row.currency ?? "AOA")
      .trigger("change");

    // =========================
    // STOCK
    // =========================
    $(form.querySelector("[name='stock_id']"))
      .val(row.stock_id ?? "")
      .trigger("change");

    if (form.quantidade) form.quantidade.value = row.quantity ?? 0;
    if (form.min_stock) form.min_stock.value = row.min_quantity ?? 1;

    // =========================
    // PREÇOS
    // =========================
    if (form.unit_price) form.unit_price.value = row.unit_price ?? 0;
    if (form.cost_price) form.cost_price.value = row.cost_price ?? 0;
    if (form.sale_price) form.sale_price.value = row.sale_price ?? 0;
    if (form.pvp) form.pvp.value = row.pvp ?? 0;

    // =========================
    // FISCAL
    // =========================
    if (form.tax) form.tax.value = row.tax ?? "";

    $(form.querySelector("[name='retention']"))
      .val(row.retention ?? 0)
      .trigger("change");

    $(form.querySelector("[name='tax']"))
      .val(row.tax ?? 0)
      .trigger("change");

    // =========================
    // COMPANY
    // =========================
    if (form.id_company) form.id_company.value = row.company_id ?? "";

    // =========================
    // MODAL
    // =========================
    $("#editItemModal").modal("show");
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

  // EXCLUIR ITEM
  $("#itemsTable").on("click", ".delete-btn", function () {
    let itemId = $(this).data("id");

    Swal.fire({
      title: "Tem certeza?",
      text: "Esta ação não pode ser desfeita!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sim, deletar!",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "items/ajax/delete_item.php",
          method: "POST",
          data: { id: itemId },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              // Remove a linha visualmente ou recarrega tudo
              $(`button[data-id="${itemId}"]`).closest("tr").remove();

              Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Produto/Serviço excluído com sucesso!",
                showConfirmButton: false,
                timer: 3000,
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "Erro ao excluir o produto/serviço.",
                confirmButtonColor: "#d33",
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: "error",
              title: "Erro!",
              text: "Erro na requisição!",
              confirmButtonColor: "#d33",
            });
          },
        });
      }
    });
  });
});
