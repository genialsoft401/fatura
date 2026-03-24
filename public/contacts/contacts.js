$(document).ready(function () {
  let contactTable;

  function initializeDataTable() {
    // Destrói a tabela existente se já foi inicializada
    if ($.fn.DataTable.isDataTable('#contactTable')) {
        $('#contactTable').DataTable().destroy();
    }

    contactTable = $("#contactTable").DataTable({
      // Usa a funcionalidade AJAX nativa do DataTables para mais performance
      ajax: {
        url: "contacts/ajax/fetch_contacts.php",
        dataSrc: "", // Indica que os dados são um array direto
      },
      // Define como cada coluna será renderizada
      columns: [
        {
          data: "name",
          render: function (data, type, row) {
            return `
              <div class="d-flex align-items-center justify-content-between">
                ${data} 
                <span data-id="${row.id}" data-bs-toggle="tooltip" title="Ver detalhes" class="material-icons-round view-contact table-icon ms-1" style="cursor: pointer;">list_alt</span>
              </div>`;
          },
        },
        {
          data: "email",
          render: function (data, type, row) {
            if (!data) return 'Não informado';
            return `
              <a href="mailto:${data}" class="text-decoration-none text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Enviar e-mail para ${data}">
                <div class="d-flex align-items-center gap-2">
                  <i class="material-icons-round align-middle" style="font-size: 1.1rem;">email</i>
                  <span>${data}</span>
                </div>
              </a>`;
          },
        },
        {
          data: "telephone",
          render: function (data, type, row) {
            if (!data) return 'Não informado';
            return `
              <a href="tel:${data}" class="text-decoration-none text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Ligar para ${data}">
                <div class="d-flex align-items-center gap-2">
                    <i class="material-icons-round align-middle">call</i>
                    <span>${data}</span>
                </div>
              </a>`;
          },
        },
        {
          data: null, // Combina cidade e país
          render: function (data, type, row) {
            const city = row.city || '';
            const country = row.country || '';
            let location = '';
            if (city && country) {
              location = `${city}, ${country}`;
            } else {
              location = city || country;
            }
            if (!location) return '';

            return `
              <div class="d-flex align-items-center gap-2 text-muted">
                <i class="material-icons-round align-middle" style="font-size: 1.1rem;">place</i>
                <span>${location}</span>
              </div>`;
          },
        },
        {
          data: "id",
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return `
              <div class="d-flex flex-wrap justify-content-end gap-3">
                <button class="btn btn-link p-0 edit-contact" data-id="${data}" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar Contato"> <i class="material-icons-round text-success">edit</i></button>
                <button class="btn btn-link p-0 delete-contact" data-id="${data}" data-bs-toggle="tooltip" data-bs-placement="top" title="Excluir Contato"> <i class="material-icons-round text-danger">delete</i></button>
              </div>`;
          },
        },
      ],
      // Adiciona o atributo 'data-label' em cada célula, usado pelo CSS responsivo
      createdRow: function (row, data, dataIndex) {
        $('td', row).eq(0).attr('data-label', 'Nome');
        $('td', row).eq(1).attr('data-label', 'Email');
        $('td', row).eq(2).attr('data-label', 'Telefone');
        $('td', row).eq(3).attr('data-label', 'País/Cidade');
        $('td', row).eq(4).attr('data-label', 'Ações');
      },
      destroy: true,
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      language: {
        search: "Pesquisar:",
        lengthMenu: "Mostrar _MENU_ registros por página",
        zeroRecords: "Nenhum contato encontrado",
        info: "Mostrando _START_ a _END_ de _TOTAL_ contatos",
        infoEmpty: "Nenhum contato disponível",
        infoFiltered: "(filtrado de _MAX_ contatos no total)",
        paginate: {
          first: "Primeiro",
          last: "Último",
          next: "Próximo",
          previous: "Anterior",
        },
      },
      // Reinicializa os tooltips do Bootstrap após cada redesenho da tabela
      drawCallback: function (settings) {
        var tooltipTriggerList = [].slice.call(
          document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          var tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
          if (tooltip) {
            tooltip.dispose();
          }
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      },
      responsive: false, 
      autoWidth: false,
    });
  }

  initializeDataTable();

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
            let href = (type === 'mailto') ? `mailto:${value}` : (value.startsWith('http') ? value : `https://${value}`);
            element.html(`${value} <a href="${href}" target="_blank" class="ms-1 text-decoration-none"><span class="material-icons-round table-icon">open_in_new</span></a>`);
        };

        const populatePhoneCard = (linkId, spanId, data) => {
            const linkElement = $(linkId);
            if (data) {
                $(spanId).text(data);
                linkElement.attr('href', `tel:${data}`).css('display', 'inline-flex');
            } else {
                linkElement.hide();
            }
        };

        // Popular dados da empresa
        populateText("#contactName", contact.name);
        populateText("#contactType", contact.type);
        populateText("#contactContributor", contact.contributor);
        populateLink("#contactEmail", contact.email, 'mailto');
        populateLink("#contactWebsite", contact.website, 'url');

        // Popular contatos telefônicos
        populatePhoneCard('#contactTelephoneLink', '#contactTelephone', contact.telephone);
        populatePhoneCard('#contactCellphoneLink', '#contactCellphone', contact.cellphone);

        // Popular localização
        populateText("#contactAddress", contact.address);
        const location = [contact.country, contact.city].filter(Boolean).join(' / ');
        populateText("#contactLocation", location);
        populateText("#contactPoBox", contact.po_box);
        populateText("#contactFax", contact.fax);

        // Popular contato preferencial
        populateText("#contactPrefName", contact.pref_name);
        populateLink("#contactPrefEmail", contact.pref_email, 'mailto');
        populatePhoneCard('#contactPrefTelephoneLink', '#contactPrefTelephone', contact.pref_telephone);
        populatePhoneCard('#contactPrefCellphoneLink', '#contactPrefCellphone', contact.pref_cellphone);

        // Popular configurações
        populateText("#contactNumberCopys", contact.numberCopys);
        populateText("#contactdue_date", contact.due_date);
        populateText("#contactLanguage", contact.language);
        populateText("#contactPaymentMethod", contact.payment_method);
        populateText("#contactCurrency", contact.currency);
        populateText("#contactObservations", contact.observations, "Nenhuma observação");
        
        const updatedAt = contact.updated_at ? formatDateTimeToBrazilian(contact.updated_at) : null;
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
  $('#contactTable tbody').on('click', 'tr', function (e) {
    // Evita abrir o modal se o clique foi em um botão, link ou ícone de ação
    if ($(e.target).closest('button, a, .edit-contact, .delete-contact').length > 0) {
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
                : (response.archived ? "Registo arquivado." : "O contacto foi removido com sucesso.");

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
