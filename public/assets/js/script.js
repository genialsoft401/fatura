if (!("fetch" in window)) {
  var script = document.createElement("script");
  script.src =
    "https://cdnjs.cloudflare.com/ajax/libs/core-js/3.29.1/minified.js";
  script.defer = true;
  document.head.appendChild(script);
}
 

$(document).ready(function () {
  $("#unidade, #retencao, #taxa").select2({
    width: "100%",
  });

  $("#saveItem").click(function () {
    let formData = $("#itemForm").serialize();
    $.ajax({
      url: "items/ajax/save_item.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        Swal.fire(response.status || "", response.message || "", response.type || "info");
        $("#itemModal").modal("hide");
        $("#itemForm")[0].reset();
      },
      error: function () {
        Swal.fire("Erro!", "Ocorreu um erro ao salvar o produto/serviço.", "error");
      },
    });
  });

  
 
  // Obtém a URL atual (excluindo parâmetros GET)
  let currentUrl = window.location.pathname.split("/").pop();

  // Remove qualquer active existente
  $(".menu li, .submenu li").removeClass("active");


  let activeLink = $("a[href='" + currentUrl + "']");
  if (activeLink.length) {
    activeLink.closest("li").addClass("active");

    // Se o item estiver dentro de um dropdown, mantém o dropdown aberto
    let parentDropdown = activeLink.closest(".dropdown");
    if (parentDropdown.length) {
      parentDropdown.addClass("open");
      parentDropdown.find(".submenu").slideDown(300);
    }
  }

  // Dropdown de Itens (permite abrir/fechar clicando em qualquer dropdown)
  $(".dropdown > a").on("click", function (event) {
    event.preventDefault();
    let parentLi = $(this).closest("li");
    let submenu = parentLi.find(".submenu");

    if (submenu.is(":visible")) {
      submenu.slideUp(300);
      parentLi.removeClass("open");
    } else {
      // Fecha outros dropdowns abertos antes de abrir o novo
      $(".submenu").slideUp(300);
      $(".dropdown").removeClass("open");

      submenu.slideDown(300);
      parentLi.addClass("open");
    }
  });


 



  let isFormDirty = false;

  document.querySelectorAll("#itemForm input, #itemForm select, #itemForm textarea").forEach((element) => {
    element.addEventListener("input", function () {
      isFormDirty = true;
    });
  });

  // document.getElementById("modalCloseButton").addEventListener("click", function () {
  //   confirmarFechamento('itemModal'); 
  // });

  // document.getElementById("editModalCloseButton").addEventListener("click", function () {
  //   confirmarFechamento('editItemModal');
  // });

  // document.getElementById("createModalCloseButton").addEventListener("click", function () {
  //   confirmarFechamento('createContactModal');
    
  // });


  // Função para confirmar o fechamento com SweetAlert2
  function confirmarFechamento(modalID) {
    if (isFormDirty) {
      Swal.fire({
        title: "Tem certeza que deseja fechar?",
        text: "Todas as informações preenchidas serão perdidas.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sim, fechar",
        cancelButtonText: "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
          // Fecha o modal
          let modal = bootstrap.Modal.getInstance(document.getElementById(modalID));
          modal.hide();
        }
      });
    } else {
      // Fecha o modal se não houver alterações
      let modal = bootstrap.Modal.getInstance(document.getElementById(modalID));
      modal.hide();
    }
  }

 

   
 $("body").tooltip({
  selector: '[data-bs-toggle="tooltip"]'
}); 

// Captura o fuso horário local do usuário
let userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

// Envia para o servidor
$.ajax({
    url: "assets/ajax/set_timezone.php",
    method: "POST",
    data: { timezone: userTimezone },
    success: function (response) {
        console.log("Fuso horário atualizado:", response);
    }
});




});

function formatDateTimeToBrazilian(dateTimeString) {
  if (!dateTimeString) return "Data inválida"; // Verifica se a string está vazia ou indefinida
  
  const [date, time] = dateTimeString.split(" "); // Separa a data da hora
  const [year, month, day] = date.split("-"); // Separa ano, mês e dia
  return `${day}/${month}/${year} ${time}`; // Retorna no formato brasileiro
}

// Formatar os valores com a moeda na posição correta
function formatCurrency(value, currencySymbol, currencyPosition) {
  let formattedValue = value.toLocaleString(undefined, {
      minimumFractionDigits: 2,
  });

  return currencyPosition === "left"
      ? `${currencySymbol} ${formattedValue}`
      : `${formattedValue} ${currencySymbol}`;
}

document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("mobileMenuBtn");
  const sidebar = document.getElementById("sidebar");
  let overlay = document.querySelector(".overlay");

  // Cria o overlay se ele não existir
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.classList.add("overlay");
    document.body.appendChild(overlay);
  }

  if (btn && sidebar) {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      sidebar.classList.toggle("mobile-active");
      overlay.classList.toggle("show");
      document.body.classList.toggle("sidebar-open");
    });
  }

  overlay.addEventListener("click", () => {
    if (sidebar) sidebar.classList.remove("mobile-active");
    overlay.classList.remove("show");
    document.body.classList.remove("sidebar-open");
  });

  // Fecha com ESC
  document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape') {
      if (sidebar) sidebar.classList.remove('mobile-active');
      overlay.classList.remove('show');
      document.body.classList.remove('sidebar-open');
    }
  });
});


// btn.addEventListener("click", () => {
//   sidebar.classList.toggle("mobile-active");
//   overlay.classList.toggle("show");
//   document.body.classList.toggle("sidebar-open");
// });

// overlay.addEventListener("click", () => {
//   sidebar.classList.remove("mobile-active");
//   overlay.classList.remove("show");
//   document.body.classList.remove("sidebar-open");
// });



