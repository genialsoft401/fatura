$(document).ready(function () {
  let countryMap = {};
  let contactId = null; // Variável global para armazenar o ID do contato

  function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  function selectCountry(selectedCountry = "", selectedCity = "") {
    return new Promise((resolve) => {
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
          countryMap = {};
          let options = '<option value="">Selecione um país</option>';

          (data.geonames || []).forEach((country) => {
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
            countrySelect.data("ignore-change", true);
            countrySelect.val(selectedCountry).trigger("change");
            countrySelect.data("ignore-change", false);
            loadCities(selectedCountry, selectedCity).then(resolve);
          } else {
            resolve();
          }
        })
        .catch((error) => {
          console.error("Erro ao carregar países:", error);
          countrySelect
            .html('<option value="">Erro ao carregar</option>')
            .trigger("change");
          resolve();
        });
    });
  }

  function loadCities(countryName, selectedCity = "") {
    return new Promise((resolve) => {
      const citySelect = $("#city");
      const countryId = countryMap[countryName];

      if (!countryId) {
        citySelect
          .html('<option value="">Selecione um país primeiro</option>')
          .trigger("change");
        resolve();
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
          let options = '<option value="">Selecione uma cidade</option>';
          (data.geonames || []).forEach((city) => {
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
          resolve();
        })
        .catch((error) => {
          console.error("Erro ao carregar cidades:", error);
          citySelect
            .html('<option value="">Erro ao carregar</option>')
            .trigger("change");
          resolve();
        });
    });
  }

  function loadDDI(selectedDDIs = {}) {
    fetch("assets/ajax/get_countries.php")
      .then((response) => response.json())
      .then((data) => {
        // Prepare data for Select2, including what to display in the list and what to display when selected
        const ddiDataForSelect2 = [
          { id: "", text: "DDI", selectedText: "DDI" },
        ].concat(
          data.map((country) => {
            return {
              id: country.phone, // The actual value of the option
              text: `${country.name} (+${country.phone})`, // What appears in the dropdown list
              selectedText: `+${country.phone}`, // What appears in the selected box
            };
          }),
        );

        const ddiFields = [
          "telephone_ddi",
          "cellphone_ddi",
          "pref_telephone_ddi",
          "pref_cellphone_ddi",
        ];
        ddiFields.forEach((fieldId) => {
          const field = $(`#${fieldId}`);

          // Select2 with 'data' option will manage the <option> elements itself.
          // No need to manually empty and append <option> tags.
          field.select2({
            width: "90px",
            placeholder: "DDI",
            allowClear: false,
            dropdownParent: field.parent(),
            data: ddiDataForSelect2, // Pass the prepared data array
            templateResult: function (option) {
              return option.text; // Displays `country.name (+country.phone)` in the list
            },
            templateSelection: function (option) {
              // Find the original data item to get the selectedText
              const selectedItem = ddiDataForSelect2.find(
                (item) => item.id === option.id,
              );
              return selectedItem ? selectedItem.selectedText : option.text; // Displays `country.phone` in the selected box
            },
          });
          if (selectedDDIs[fieldId]) {
            field.val(selectedDDIs[fieldId]).trigger("change");
          }
        });
      })
      .catch((error) => {
        console.error("Erro ao carregar DDIs:", error);
      });
  }

  function loadContactData(contactId) {
    $.ajax({
      url: "contacts/ajax/get_contact.php",
      method: "POST",
      data: { id: contactId },
      dataType: "json",
      success: function (contact) {
        console.log("Carregando dados do contato para edição...");
        // Armazena globalmente o ID do contato
        window.__CONTACT_ID__ = contact.id;

        $("#name").val(contact.name);
        $("#email").val(contact.email);
        $("#telephone").val(contact.telephone);
        $("#address").val(contact.address);
        $("#observations").val(contact.observations);
        $("#contributor").val(contact.contributor);
        $("#po_box").val(contact.po_box);
        $("#cellphone").val(contact.cellphone);
        $("#website").val(contact.website);
        $("#fax").val(contact.fax);
        $("#pref_name").val(contact.pref_name);
        $("#pref_email").val(contact.pref_email);
        $("#pref_telephone").val(contact.pref_telephone);
        $("#pref_cellphone").val(contact.pref_cellphone);

        $("#type").val(contact.type).trigger("change");
        $("#num_copias").val(contact.numberCopys).trigger("change");
        $("#due_date").val(contact.due_date).trigger("change");
        $("#language").val(contact.language).trigger("change");
        $("#payment_method").val(contact.payment_method).trigger("change");
        $("#currency").val(contact.currency).trigger("change");

        selectCountry(contact.country, contact.city).then(() => {
          // Após carregar selects (país/cidade), registra estado original para detectar mudanças
          $("#contactForm")
            .find("input, select, textarea")
            .each(function () {
              $(this).data("original", $(this).val());
            });
        });

        loadDDI({
          telephone_ddi: contact.telephone_ddi,
          cellphone_ddi: contact.cellphone_ddi,
          pref_telephone_ddi: contact.pref_telephone_ddi,
          pref_cellphone_ddi: contact.pref_cellphone_ddi,
        });

        $("#saveContactButton").text("Salvar Alterações");
      },
      error: function () {
        alert("Erro ao carregar detalhes do contato.");
      },
    });
  }
  function toggleFields() {
    let isChecked = $("#usar_definicoes").prop("checked");

    // Inputs e Textareas ficam readonly
    $("#observations").prop("readonly", isChecked);

    // Selects não podem ser editados, mas ainda serão enviados
    if (isChecked) {
      $(
        "#observations, #due_date,#numberCopys, #language, #currency, #payment_method",
      )
        .addClass("blocked-select")
        .attr("tabindex", "-1");
    } else {
      $(
        "#observations, #due_date, #numberCopys, #language, #currency, #payment_method",
      )
        .removeClass("blocked-select")
        .removeAttr("tabindex");
    }
  }

  toggleFields(); // Executa ao carregar

  $("#usar_definicoes").on("change", function () {
    toggleFields();
  });
  function initPage() {
    contactId = getQueryParam("id"); // Salva o ID globalmente

    if (contactId && !isNaN(contactId)) {
      $("#infoEdit").text("Editar Usuário");
      $("#sideContact").text("Editar Usuário");
      loadContactData(contactId);
    } else {
      selectCountry().then(() => {
        $("#contactForm")
          .find("input, select, textarea")
          .each(function () {
            $(this).data("original", $(this).val());
          });
      });
      loadDDI(); // Apenas uma consulta ao banco
    }
  }

  $(document).on("click", "#saveChangesContact", function (event) {
    event.preventDefault();
    let formData;
    let url;

    if (contactId) {
      // Se o ID existe, atualiza apenas os campos modificados
      formData = getUpdatedFields();
      formData["id"] = contactId;
      url = "contacts/ajax/update_contact.php";
    } else {
      // Se não há ID, envia todos os dados do formulário corretamente via FormData
      formData = new FormData($("#contactForm")[0]);
      url = "contacts/ajax/save_contact.php";
    }

    const validateFileds = () => {
      const fieldName = document.querySelector(
        "#contactForm input[name='name']",
      );

      if (
        fieldName.length == " " ||
        fieldName.length == 0 ||
        fieldName.length <= 5
      ) {
        Swal.fire({
          icon: "error",
          title: "Erro!",
          text: "",
        });
      }
    };

    $.ajax({
      url: url,
      method: "POST",
      data: formData,
      processData: !(formData instanceof FormData),
      contentType:
        formData instanceof FormData
          ? false
          : "application/x-www-form-urlencoded; charset=UTF-8",
      success: function (response) {
        try {
          let res = JSON.parse(response);
          if (res.status === "success") {
            Swal.fire({
              icon: "success",
              title: "Sucesso!",
              text: res.message,
            }).then(() => {
              window.location.href = "contacts.php";
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Erro!",
              text: res.message,
            });
          }
        } catch (e) {
          Swal.fire({
            icon: "error",
            title: "Erro inesperado!",
            text: "Ocorreu um erro ao processar sua solicitação.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Erro!",
          text: "Erro ao tentar salvar os dados. Tente novamente.",
        });
      },
    });
  });

  function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  function getUpdatedFields() {
    let updatedData = {};
    $("#contactForm")
      .find("input, select, textarea")
      .each(function () {
        let fieldName = $(this).attr("name");
        if ($(this).val() !== $(this).data("original")) {
          updatedData[fieldName] = $(this).val();
        }
      });
    return updatedData;
  }

  // Os valores originais são registrados após o carregamento (especialmente no modo edição)
  // (no modo novo, vamos registrar após initPage)

  // Evita que o botão dentro do formulário envie os dados automaticamente
  $("#saveChanges").attr("type", "button");

  // Botão para preencher dados de teste
  $("#btnFillContact").on("click", function () {
    $("#type").val("Normal").trigger("change");
    $("#name").val("Empresa Teste " + Math.floor(Math.random() * 1000));
    $("#contributor").val("NIF" + Math.floor(Math.random() * 1000000));
    $("#email").val(
      "contato" + Math.floor(Math.random() * 1000) + "@teste.com",
    );
    $("#address").val("Rua Exemplo, 123 - Centro");
    $("#telephone").val("222333444");
    $("#po_box").val("CP-123");
    $("#cellphone").val("923456789");
    $("#website").val("www.teste.com");
    $("#fax").val("222000000");

    $("#pref_name").val("Gerente Teste");
    $("#pref_email").val("gerente@teste.com");
    $("#pref_telephone").val("222555666");
    $("#pref_cellphone").val("912345678");

    // Preenche os DDIs (assumindo 244 para Angola)
    $("#telephone_ddi").val("244").trigger("change");
    $("#cellphone_ddi").val("244").trigger("change");
    $("#pref_telephone_ddi").val("244").trigger("change");
    $("#pref_cellphone_ddi").val("244").trigger("change");

    $("#observations").val("Cadastro de teste gerado automaticamente.");

    // Tenta selecionar Angola se já estiver carregado
    if ($("#country option[value='Angola']").length > 0) {
      $("#country").val("Angola").trigger("change");
      setTimeout(function () {
        $("#city").val("Luanda").trigger("change");
      }, 1000);
    }
  });

  $("#country").on("change", function () {
    if ($(this).data("ignore-change")) return;
    loadCities($(this).val());
  });

  initPage();
});
