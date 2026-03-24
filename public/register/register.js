$(document).ready(function () {
  $("#registerForm").submit(function (e) {
    e.preventDefault();
    let formData = $(this).serialize();

    $.ajax({
      url: "register/ajax/process_register.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Cadastro realizado com sucesso!",
            showConfirmButton: false,
            timer: 1500,
          }).then(() => {
            window.location.href = "login.php";
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Erro",
            text: response.message,
          });
        }
      },
    });
  });

  let country = $("#country").val();

  function atualizarMascaras() {
    aplicarMascaraTelefone("#phone", country);
    aplicarMascaraTelefone("#company_phone", country);
    aplicarMascaraCnpjNif("#registration_number", country);
  }

  atualizarMascaras();

 
  function verificarCampos() {
    let usernameValido = $("#username").hasClass("is-valid");
    let emailValido = $("#email").val().trim().length > 5;
    let senhaValida = $("#password").hasClass("is-valid");

    if (usernameValido && emailValido && senhaValida) {
      $("#submitBtn").prop("disabled", false);
    } else {
      $("#submitBtn").prop("disabled", true);
    }
  }

  $("#username").on("input", function () {
    let username = $(this).val().trim();
    let feedback = $("#usernameFeedback");
    let inputField = $(this); // Captura o próprio input

    // Reseta a mensagem e a borda se o campo estiver vazio
    if (username.length === 0) {
      feedback.text("").removeClass("text-danger text-success");
      inputField.css("border-color", ""); // Remove qualquer borda colorida
      return;
    }

    // Evita requisições desnecessárias
    if (username.length < 3) {
      feedback
        .text("O nome de usuário deve ter pelo menos 3 caracteres.")
        .removeClass("text-success")
        .addClass("text-danger");
      inputField.css("border-color", "red");
      inputField.removeClass("is-valid").addClass("is-invalid");
      
      return;
    }

    $.post(
      "register/ajax/verifica_username.php",
      { username: username },
      function (response) {
        if (response.error) {
          feedback
            .text(response.error)
            .removeClass("text-success")
            .addClass("text-danger");
          inputField.css("border-color", "red"); // Borda vermelha
          inputField.removeClass("is-valid").addClass("is-invalid");
          return;
        }

        if (response.existe) {
          feedback
            .text("Nome de usuário já está em uso!")
            .removeClass("text-success")
            .addClass("text-danger");
          inputField.css("border-color", "red"); // Borda vermelha
          inputField.removeClass("is-valid").addClass("is-invalid");
        } else {
          feedback
            .text("Nome de usuário disponível!")
            .removeClass("text-danger")
            .addClass("text-success");
          inputField.css("border-color", "green"); // Borda verde
          inputField.removeClass("is-invalid").addClass("is-valid");
        }
      },
      "json"
    ).fail(function () {
      feedback
        .text("Erro ao verificar usuário. Tente novamente.")
        .removeClass("text-success")
        .addClass("text-danger");
      inputField.css("border-color", "red"); // Borda vermelha em caso de erro
      inputField.removeClass("is-valid").addClass("is-invalid");
    });
  });

  $('#password').on('input focus', function() {
    let senha = $(this).val();
    let regras = $('#passwordRules');

    // Exibe as regras enquanto o usuário digita
    if (senha.length > 0) {
        regras.removeClass('d-none');
    }

    let regrasValidas = 0;

    if (senha.length >= 6) {
        $('#rule-length').removeClass('text-danger').addClass('text-success');
        regrasValidas++;
    } else {
        $('#rule-length').removeClass('text-success').addClass('text-danger');
    }

    if (/[A-Z]/.test(senha)) {
        $('#rule-uppercase').removeClass('text-danger').addClass('text-success');
        regrasValidas++;
    } else {
        $('#rule-uppercase').removeClass('text-success').addClass('text-danger');
    }

    if (/[a-z]/.test(senha)) {
        $('#rule-lowercase').removeClass('text-danger').addClass('text-success');
        regrasValidas++;
    } else {
        $('#rule-lowercase').removeClass('text-success').addClass('text-danger');
    }

    if (/[!@#$%^&*]/.test(senha)) {
        $('#rule-special').removeClass('text-danger').addClass('text-success');
        regrasValidas++;
    } else {
        $('#rule-special').removeClass('text-success').addClass('text-danger');
    }

    if (regrasValidas === 4) {
        $(this).removeClass('is-invalid').addClass('is-valid');
    } else {
        $(this).removeClass('is-valid').addClass('is-invalid');
    }

    verificarCampos();
});

 
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#password, #passwordRules').length) {
            $('#passwordRules').addClass('d-none');
        }
    });

    

function aplicarMascaraTelefone(input, country) {
  let mask = country === "angola" ? "+244 000 000 000" : "+55 (00) 00000-0000";
  let placeholder =
    country === "angola" ? "+244 ___ ___ ___" : "+55 (__) _____-____";

  $(input).mask(mask, { placeholder: placeholder });
}

function aplicarMascaraCpfBi(input, country) {
  let mask = country === "angola" ? "000000000AA000" : "000.000.000-00";
  $(input).mask(mask);
}

function aplicarMascaraCnpjNif(input, country) {
  let mask = country === "angola" ? "0000000000" : "00.000.000/0000-00";
  $(input).mask(mask);
}
});
