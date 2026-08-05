$(document).ready(function () {
  let contactTable;
  let currentFilter = "active";

  // =====================================
  // DATATABLE
  // =====================================

  function initializeDataTable(statusFilter = "active") {
    const TABLE_ID = "#contactTable";

    // =====================================
    // DESTROY DATATABLE
    // =====================================

    if ($.fn.DataTable.isDataTable(TABLE_ID)) {
      $(TABLE_ID).DataTable().clear().destroy();
      $(`${TABLE_ID} tbody`).empty();
    }

    // =====================================
    // HELPERS
    // =====================================

    const renderEmpty = (val) => val || "Não informado";

    const renderIconText = (icon, text, extraClass = "") => `
    <div class="d-flex align-items-center gap-2 ${extraClass}">
      <i class="bi ${icon}"></i>
      <span>${text}</span>
    </div>
  `;

    const renderLink = (href, title, content) => `
    <a 
      href="${href}" 
      class="text-decoration-none text-body"
      data-bs-toggle="tooltip" 
      title="${title}"
    >
      ${content}
    </a>
  `;

    const renderLocation = (city, country) => {
      const location = [city, country].filter(Boolean).join(", ");

      if (!location) {
        return "-";
      }

      return renderIconText("bi-geo-alt", location, "text-muted");
    };

    // =====================================
    // AÇÕES
    // =====================================

    const renderActions = (id, is_active) => {
      let buttons = `
      <div class="d-flex justify-content-start gap-3">

        <button 
          class="btn p-0 edit-contact"
          data-id="${id}"
          data-bs-toggle="tooltip"
          title="Editar"
        >
          <i class="bi bi-pencil text-muted"></i>
        </button>
    `;

      // =====================================
      // CONTATO ATIVO
      // =====================================

      if (parseInt(is_active, 10) === 1) {
        let status = is_active === 1 ? 0 : 1;
        buttons += `
        <button
          class="btn p-0 archive-contact"
          data-id="${id}"
          data-bs-toggle="tooltip"
          title="Arquivar"
          onclick="changeStatus(${id}, ${status})"
        >
          <i class="bi bi-archive text-warning"></i>
        </button>
      `;

        document.changeStatus = (contactId, status) => {
          $.ajax({
            url: "contacts/ajax/change_status.php",
            type: "POST",
            data: { id: contactId, status: status }, // objeto simples: deixa o jQuery serializar (não use processData:false/contentType:false aqui)
            dataType: "json",
            success: function (response) {
              if (response.status === "success") {
                Swal.fire({
                  icon: "success",
                  title: "Sucesso!",
                  text:
                    status === 1
                      ? "Contato arquivado com sucesso."
                      : "Contato reativado com sucesso.",
                  timer: 2000,
                  showConfirmButton: false,
                }).then(() => {
                  location.reload();
                });
              } else {
                Swal.fire({
                  icon: "error",
                  title: "Erro!",
                  text:
                    response.message ||
                    "Houve um problema ao atualizar o perfil.",
                });
              }
            },
            error: function () {
              console.log("ERRO BRUTO:", xhr.responseText); // <-- útil se o JSON vier quebrado (ex: erro PHP misturado no output)
              Swal.fire({
                icon: "error",
                title: "Erro!",
                text: "Erro inesperado. Tente novamente.",
              });
            },
          });
        };
      }

      // =====================================
      // CONTATO ARQUIVADO
      // =====================================
      else {
        buttons += `
        <button 
          class="btn p-0 restore-contact"
          data-id="${id}"
          data-bs-toggle="tooltip"
          title="Restaurar"
        >
          <i class="bi bi-arrow-counterclockwise text-success"></i>
        </button>
      `;
      }

      buttons += `
        <button 
          class="btn p-0 delete-contact"
          data-id="${id}"
          data-bs-toggle="tooltip"
          title="Excluir"
        >
          <i class="bi bi-trash text-danger"></i>
        </button>

        <i 
          class="bi bi-card-list view-contact table-icon"
          data-id="${id}"
          data-bs-toggle="tooltip"
          title="Ver detalhes"
          style="cursor:pointer;"
        ></i>

      </div>
    `;

      return buttons;
    };

    // =====================================
    // DATATABLE
    // =====================================

    contactTable = $(TABLE_ID).DataTable({
      processing: true,
      destroy: true,
      responsive: false,
      autoWidth: false,
      deferRender: true,

      ajax: {
        url: "contacts/ajax/fetch_contacts.php",
        type: "GET",

        data: function (d) {
          d.archived = statusFilter === "archived" ? 1 : 0;
        },

        dataSrc: function (json) {
          console.log("CONTACTS:", json);

          if (json.success && Array.isArray(json.data)) {
            return json.data;
          }

          return [];
        },

        error: function (xhr, status, error) {
          console.log("STATUS:", status);
          console.log("ERROR:", error);
          console.log("RESPONSE:", xhr.responseText);
        },
      },

      // =====================================
      // COLUMNS
      // =====================================

      columns: [
        // =====================================
        // NOME
        // =====================================

        {
          data: null,

          render: (_, __, row) => {
            const name = renderEmpty(row.name);

            const email = row.email
              ? `
              <small class="text-muted d-block">
                ${row.email}
              </small>
            `
              : `
              <small class="text-muted d-block">
                Sem email
              </small>
            `;

            return `
            <div class="d-flex align-items-center gap-3">

              <svg xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="blue"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="opacity-75 lucide lucide-building2 h-4 w-4 text-primary">

                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                <path d="M10 6h4"></path>
                <path d="M10 10h4"></path>
                <path d="M10 14h4"></path>
                <path d="M10 18h4"></path>
              </svg>

              <div>
                <div class="fw-semibold">
                  ${name}
                </div>

                ${email}
              </div>

            </div>
          `;
          },
        },

        // =====================================
        // TELEFONE
        // =====================================

        {
          data: "telephone",

          render: (data) => {
            if (!data) {
              return renderEmpty();
            }

            return renderLink(
              `tel:${data}`,
              `Ligar para ${data}`,
              renderIconText("bi-telephone", data),
            );
          },
        },

        // =====================================
        // LOCALIZAÇÃO
        // =====================================

        {
          data: null,

          render: (_, __, row) => renderLocation(row.city, row.country),
        },

        // =====================================
        // AÇÕES
        // =====================================

        {
          data: null,
          orderable: false,
          searchable: false,

          render: (_, __, row) => renderActions(row.id, row.is_active),
        },
      ],

      // =====================================
      // DEFINIÇÕES
      // =====================================

      columnDefs: [
        {
          targets: "_all",
          className: "text-start",
        },
      ],

      createdRow: function (row) {
        const labels = ["Nome", "Telefone", "País/Cidade", "Ações"];

        $("td", row).each((i, td) => {
          $(td).attr("data-label", labels[i]);
        });
      },

      pageLength: 25,

      lengthMenu: [10, 25, 50, 100],

      language: {
        search: "",
        searchPlaceholder: "Pesquisar contatos...",

        lengthMenu: "Mostrar _MENU_",

        zeroRecords: "Nenhum contato encontrado",

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

      // =====================================
      // SEARCH CUSTOM
      // =====================================

      initComplete: function () {
        const wrapper = $(TABLE_ID).closest(".dataTables_wrapper");

        const searchInput = wrapper.find(".dataTables_filter input");

        searchInput.addClass("form-control rounded-3 shadow-sm ps-5");

        if (!wrapper.find(".search-icon").length) {
          wrapper.find(".dataTables_filter").css("position", "relative");

          wrapper.find(".dataTables_filter").append(`
            <i class="bi bi-search search-icon"></i>
          `);
        }
      },

      // =====================================
      // TOOLTIPS
      // =====================================

      drawCallback: function () {
        document
          .querySelectorAll('[data-bs-toggle="tooltip"]')
          .forEach((el) => {
            bootstrap.Tooltip.getOrCreateInstance(el);
          });
      },
    });
  }

  // =====================================
  // FILTRO ATIVOS
  // =====================================

  $("#filterActive").on("click", function () {
    // alert("ok")
    currentFilter = "active";

    $("#filterActive")
      .removeClass("btn-outline-primary")
      .addClass("btn-primary");

    $("#filterArchived")
      .removeClass("btn-primary")
      .addClass("btn-outline-primary");

    initializeDataTable(currentFilter);
  });

  // =====================================
  // FILTRO ARQUIVADOS
  // =====================================

  $("#filterArchived").on("click", function () {
    currentFilter = "archived";

    $("#filterArchived")
      .removeClass("btn-outline-primary")
      .addClass("btn-primary");

    $("#filterActive")
      .removeClass("btn-primary")
      .addClass("btn-outline-primary");

    initializeDataTable(currentFilter);
  });

  // =====================================
  // LOAD INICIAL
  // =====================================

  initializeDataTable(currentFilter);

  // --- Manipuladores de Eventos ---

  $(document).on("click", ".edit-contact", function () {
    let contactId = $(this).data("id");
    window.location.href = "register_contact.php?id=" + contactId;
  });

  $("#usar_definicoes").on("change", function () {
    toggleFields();
  });

  function getContactDetails(contactId) {
    $.ajax({
      url: "contacts/ajax/details_contact.php",
      method: "POST",
      data: { id: contactId },
      dataType: "json",
      success: function (contact) {
        // Helpers para popular o modal de forma segura e limpa
        const populateText = (id, value, fallback = "Não informado") => {
          $(id).text(value || fallback);
        };

        const populateLink = (id, value, type) => {
          const element = $(id);
          if (!value) {
            element.text("Não informado");
            return;
          }
          let href =
            type === "mailto"
              ? `mailto:${value}`
              : value.startsWith("http")
                ? value
                : `https://${value}`;
          element.html(
            `${value} <a href="${href}" target="_blank" class="ms-1 text-decoration-none"><span class="material-icons-round table-icon">open_in_new</span></a>`,
          );
        };

        const populatePhoneCard = (linkId, spanId, data) => {
          const linkElement = $(linkId);
          if (data) {
            $(spanId).text(data);
            linkElement
              .attr("href", `tel:${data}`)
              .css("display", "inline-flex");
          } else {
            linkElement.hide();
          }
        };

        // Popular dados da empresa
        populateText("#contactName", contact.name);
        populateText("#contactType", contact.type);
        populateText("#contactContributor", contact.contributor);
        populateLink("#contactEmail", contact.email, "mailto");
        populateLink("#contactWebsite", contact.website, "url");

        // Popular contatos telefônicos
        populatePhoneCard(
          "#contactTelephoneLink",
          "#contactTelephone",
          contact.telephone,
        );
        populatePhoneCard(
          "#contactCellphoneLink",
          "#contactCellphone",
          contact.cellphone,
        );

        // Popular localização
        populateText("#contactAddress", contact.address);
        const location = [contact.country, contact.city]
          .filter(Boolean)
          .join(" / ");
        populateText("#contactLocation", location);
        populateText("#contactPoBox", contact.po_box);
        populateText("#contactFax", contact.fax);

        // Popular contato preferencial
        populateText("#contactPrefName", contact.pref_name);
        populateLink("#contactPrefEmail", contact.pref_email, "mailto");
        populatePhoneCard(
          "#contactPrefTelephoneLink",
          "#contactPrefTelephone",
          contact.pref_telephone,
        );
        populatePhoneCard(
          "#contactPrefCellphoneLink",
          "#contactPrefCellphone",
          contact.pref_cellphone,
        );

        // Popular configurações
        populateText("#contactNumberCopys", contact.numberCopys);
        populateText("#contactdue_date", contact.due_date);
        populateText("#contactLanguage", contact.language);
        populateText("#contactPaymentMethod", contact.payment_method);
        populateText("#contactCurrency", contact.currency);
        populateText(
          "#contactObservations",
          contact.observations,
          "Nenhuma observação",
        );

        const updatedAt = contact.updated_at
          ? formatDateTimeToBrazilian(contact.updated_at)
          : null;
        populateText("#contactUpdatedAt", updatedAt);

        $("#contactModal").modal("show");
      },
      error: function () {
        alert("Erro ao carregar detalhes do contato.");
      },
    });
  }

  $(document).on("click", ".view-contact", function (e) {
    e.preventDefault();
    let contactId = $(this).data("id");
    getContactDetails(contactId);
  });

  // Torna o card inteiro clicável no mobile para ver detalhes
  $("#contactTable tbody").on("click", "tr", function (e) {
    // Evita abrir o modal se o clique foi em um botão, link ou ícone de ação
    if (
      $(e.target).closest("button, a, .edit-contact, .delete-contact").length >
      0
    ) {
      return;
    }

    // Só ativa em telas mobile (quando o card é exibido)
    if (window.innerWidth > 768) {
      return;
    }

    const rowData = contactTable.row(this).data();
    if (rowData && rowData.id) {
      getContactDetails(rowData.id);
    }
  });

  $(document).on("click", ".delete-contact", function () {
    let contactId = $(this).data("id");

    Swal.fire({
      title: "Tem certeza?",
      text: "Essa ação não pode ser desfeita!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sim, excluir!",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "contacts/ajax/delete_contact.php",
          method: "POST",
          data: { id: contactId },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              const msg = response.message
                ? response.message
                : response.archived
                  ? "Registo arquivado."
                  : "O contacto foi removido com sucesso.";

              Swal.fire({
                title: response.archived ? "Arquivado!" : "Excluído!",
                text: msg,
                icon: "success",
                timer: 2200,
                showConfirmButton: false,
              });
              contactTable.ajax.reload(null, false);
            } else {
              Swal.fire({
                title: "Erro!",
                text: response.message,
                icon: "error",
              });
            }
          },
          error: function () {
            Swal.fire({
              title: "Erro!",
              text: "Erro na requisição. Tente novamente.",
              icon: "error",
            });
          },
        });
      }
    });
  });

  // Botões de Exportação
  $("#downloadCSV").click(function () {
    window.location.href = "contacts/ajax/export_contacts.php?type=csv";
  });

  $("#downloadExcel").click(function () {
    window.location.href = "contacts/ajax/export_contacts.php?type=excel";
  });

  $("#downloadPDF").click(function () {
    window.location.href = "contacts/ajax/export_contacts.php?type=pdf";
  });
});
