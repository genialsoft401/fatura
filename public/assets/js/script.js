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

  // =========================
  // VALIDATION HELPERS
  // =========================
  function showError(msg) {
    Swal.fire("Atenção", msg, "warning");
  }

  function validateForm() {
    const itemType = $("#category").val();
    const name = $("input[name='name']").val();
    const code = $("#codigo").val();
    const stockId = $("#stock_id").val();

    if (!name) {
      showError("Nome é obrigatório");
      return false;
    }

    // regras só para produto
    if (itemType === "product") {
      if (!code) {
        showError("Código é obrigatório para produtos");
        return false;
      }

      if (!stockId) {
        showError("Selecione um stock para produtos");
        return false;
      }
    }

    return true;
  }

  // =========================
  // SAVE ITEM
  // =========================
  $("#saveItem").click(function () {
    if (!validateForm()) return;

    let formData = $("#itemForm").serialize();

    $.ajax({
      url: "items/ajax/save_item.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        Swal.fire(
          response.status || "Info",
          response.message || "",
          response.type || "info",
        );

        if (response.type === "success") {
          $("#itemModal").modal("hide");
          $("#itemForm")[0].reset();
        }
      },
      error: function () {
        Swal.fire(
          "Erro!",
          "Ocorreu um erro ao salvar o produto/serviço.",
          "error",
        );
      },
    });
  });

  // =========================
  // ACTIVE MENU
  // =========================
  let currentUrl = window.location.pathname.split("/").pop();

  $(".menu li, .submenu li").removeClass("active");

  let activeLink = $("a[href='" + currentUrl + "']");

  if (activeLink.length) {
    activeLink.closest("li").addClass("active");

    let parentDropdown = activeLink.closest(".dropdown");

    if (parentDropdown.length) {
      parentDropdown.addClass("open");
      parentDropdown.find(".submenu").slideDown(300);
    }
  }

  // =========================
  // DROPDOWN MENU
  // =========================
  $(".dropdown > a").on("click", function (event) {
    event.preventDefault();

    let parentLi = $(this).closest("li");
    let submenu = parentLi.find(".submenu");

    if (submenu.is(":visible")) {
      submenu.slideUp(300);
      parentLi.removeClass("open");
    } else {
      $(".submenu").slideUp(300);
      $(".dropdown").removeClass("open");

      submenu.slideDown(300);
      parentLi.addClass("open");
    }
  });

  // =========================
  // FORM DIRTY TRACKING
  // =========================
  let isFormDirty = false;

  $("#itemForm input, #itemForm select, #itemForm textarea").on(
    "input change",
    function () {
      isFormDirty = true;
    },
  );

  // =========================
  // TOOLTIP
  // =========================
  $("body").tooltip({
    selector: '[data-bs-toggle="tooltip"]',
  });

  // =========================
  // TIMEZONE
  // =========================
  let userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

  $.ajax({
    url: "assets/ajax/set_timezone.php",
    method: "POST",
    data: { timezone: userTimezone },
    success: function (response) {
      console.log("Fuso horário atualizado:", response);
    },
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
  document.addEventListener("keydown", (ev) => {
    if (ev.key === "Escape") {
      if (sidebar) sidebar.classList.remove("mobile-active");
      overlay.classList.remove("show");
      document.body.classList.remove("sidebar-open");
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
