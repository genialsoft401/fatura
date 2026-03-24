$(document).ready(function () {
  // Função para renderizar a tabela manualmente
  function renderTable(data) {
      let tbody = $("#itemsTable tbody");
      tbody.empty();

      if (data.length === 0) {
          tbody.append('<tr><td colspan="6" class="text-center">Nenhum produto/serviço encontrado.</td></tr>');
          return;
      }

      data.forEach(row => {
          // Serializa o objeto row para passar ao botão de editar
          // Usamos encodeURIComponent para evitar que aspas quebrem o HTML
          let rowData = encodeURIComponent(JSON.stringify(row));

          let tr = `
              <tr>
                  <td data-label="Código">${row.code}</td>
                  <td data-label="Descrição">${row.description_plain || row.description}</td>
                  <td data-label="Preço Unitário">${formatCurrency(row.unit_price, row.symbol, row.position)}</td>
                  <td data-label="Taxa/IVA">${row.tax}</td>
                  <td data-label="PVP">${formatCurrency(row.pvp, row.symbol, row.position)}</td>
                  <td data-label="Ações">
                      <div class="d-flex flex-nowrap justify-content-center">
                          <button class="btn text-dark btn-sm edit-btn" data-row="${rowData}" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar item">
                              <i class="material-icons-round">edit</i>
                          </button> 
                          <button class="btn text-danger btn-sm delete-btn" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-placement="top" title="Excluir item">
                              <i class="material-icons-round">delete</i>
                          </button>
                      </div>
                  </td>
              </tr>
          `;
          tbody.append(tr);
      });

      // Reativar os tooltips após o carregamento dos dados
      $('[data-bs-toggle="tooltip"]').tooltip();
  }

  // Carregar os dados via AJAX
  function loadItems() {
      $.ajax({
          url: "items/ajax/get_items.php",
          method: "GET",
          dataType: "json",
          success: function (data) {
              if (data.error) {
                  console.error(data.error);
                  return;
              }
              renderTable(data);
          },
          error: function (xhr, status, error) {
              console.error("Erro ao carregar os dados:", error);
          }
      });
  }

  // Carrega inicialmente
  loadItems();

  // Função simples de pesquisa (Filtro)
  // Adicione um input com id="searchInput" no seu HTML se quiser usar
  $("#searchInput").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#itemsTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
  });

  // EDITAR ITEM
  $("#itemsTable").on("click", ".edit-btn", function () {
      // Recupera os dados diretamente do atributo data-row
      let rawData = $(this).data("row");
      
      if (!rawData) {
          console.error("Dados não encontrados para edição.");
          return;
      }

      // Decodifica a string JSON
      let rowData = JSON.parse(decodeURIComponent(rawData));

      // Preencher modal com os dados do item
      $('#edit_id').val(rowData.id);
      $('#edit_codigo').val(rowData.code);
      $('#edit_descricao').val(rowData.description);
      $('#edit_preco').val(rowData.unit_price);
      $('#edit_pvp').val(rowData.pvp);
      $('#edit_currency').val(rowData.currency);
      $('#editItemForm').data('id_company', rowData.id_company);

      // Preencher selects corretamente
      $('#edit_unidade').val(rowData.unit2).trigger('change'); 
      $('#edit_retencao').val(rowData.retention2).trigger('change'); 
      $('#edit_taxa').val(rowData.tax2).trigger('change'); 

      // Abrir a modal de edição
      $('#editItemModal').modal('show');
  });

  // SALVAR EDIÇÃO
  $('#saveEdit').on('click', function () {
      // Manually construct the data object to ensure correct keys are sent
      let dataToSend = {
          id: $('#edit_id').val(),
          id_company: $('#editItemForm').data('id_company'),
          codigo: $('#edit_codigo').val(),
          descricao: $('#edit_descricao').val(),
          preco: $('#edit_preco').val(),
          pvp: $('#edit_pvp').val(),
          unidade: $('#edit_unidade').val(),
          retencao: $('#edit_retencao').val(),
          taxa: $('#edit_taxa').val(),
          currency: $('#edit_currency').val()
      };

      console.log(dataToSend); // For debugging: check what data is being sent

      $.ajax({
          url: "items/ajax/edit_item.php",
          method: "POST",
          data: dataToSend, // Changed from formData to dataToSend
          dataType: "json",
          success: function (response) {
              console.log(response); // Debugging: check server response
              if (response.success) {
                  $('#editItemModal').modal('hide');

                  Swal.fire({
                      toast: true,
                      position: "top-end",
                      icon: "success",
                      title: "Produto/Serviço atualizado com sucesso!",
                      showConfirmButton: false,
                      timer: 3000
                  });

                  // Recarregar os dados na tabela
                  loadItems();
              } else {
                  Swal.fire({
                      icon: "error",
                      title: "Erro!",
                      text: "Erro ao atualizar o produto/serviço.",
                      confirmButtonColor: "#d33"
                  });
              }
          },
          error: function () {
              Swal.fire({
                  icon: "error",
                  title: "Erro!",
                  text: "Erro na requisição!",
                  confirmButtonColor: "#d33"
              });
          }
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
          cancelButtonText: "Cancelar"
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
                              timer: 3000
                          });
                      } else {
                          Swal.fire({
                              icon: "error",
                              title: "Erro!",
                              text: "Erro ao excluir o produto/serviço.",
                              confirmButtonColor: "#d33"
                          });
                      }
                  },
                  error: function () {
                      Swal.fire({
                          icon: "error",
                          title: "Erro!",
                          text: "Erro na requisição!",
                          confirmButtonColor: "#d33"
                      });
                  }
              });
          }
      });
  });

  
});
