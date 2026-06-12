$(function () {
  // =========================
  // 1. GET ID LIMPO
  // =========================
  const invoiceId = new URLSearchParams(window.location.search)
    .get("id")
    ?.split("/")
    .pop();

  if (!invoiceId) {
    alert("Factura Proforma não encontrada!");
    return;
  }

  let currentInvoice = null;

  // =========================
  // 2. CARREGAR DADOS JSON
  // =========================
  function loadInvoice() {
    $.get("proform/ajax/get_proform.php", { id: invoiceId })
      .done((inv) => {
        currentInvoice = inv?.data;

        $("#fatura-id").text(`${inv.data?.reference}`);
        $("#status-invoice").text("PROFORMA");
        $("#subtitle-client").text(inv.data?.client_name);

        setupButtons(inv?.data);
      })
      .fail(() => {
        alert("Erro ao carregar factura proforma");
      });
  }

  // =========================
  // 3. CARREGAR HTML VISUAL
  // =========================
  $("#fatura-container").load(
    `proform/ajax/proform_public.php?id=${invoiceId}`,
  );

  // =========================
  // 4. BOTÕES CONDICIONAIS
  // =========================
  function setupButtons(inv) {
    if (inv.status_invoice === "Rascunho") {
      $("#btnFinalizar, #btnEditar").removeClass("d-none");
    }

    $("#btnPdf, #generatePdf, #btnEnviar").removeClass("d-none");
    $("#btnNotaCredito").removeClass("d-none");
  }

  // =========================
  // 5. GERAR PDF PROFORMA
  // =========================
  function gerarPdfProforma() {
    if (!currentInvoice) return;

    const el = document.querySelector("#fatura-container");

    const opt = {
      margin: 10,
      filename: `${currentInvoice.reference}.pdf`,
      image: { type: "jpeg", quality: 1 },
      html2canvas: { scale: 3, useCORS: true },
      jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
    };

    html2pdf().set(opt).from(el).save();
  }

  $("#btnPdf, #generatePdf").on("click", gerarPdfProforma);

  // =========================
  // 6. FINALIZAR PROFORMA -> FACTURA
  // =========================
  $("#btnChangeToInvoice").on("click", function () {
    const proformaId = currentInvoice?.id;

    if (!proformaId) {
      return Swal.fire({
        icon: "error",
        title: "Erro",
        text: "Proforma não encontrada.",
      });
    }

    Swal.fire({
      title: "Converter em Factura Recibo?",
      text: "A Proforma será convertida numa Factura Recibo oficial.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sim, converter",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: "proform/ajax/convert_proforma.php",
        type: "POST",
        dataType: "json",
        data: {
          invoice_id: proformaId,
        },

        beforeSend: function () {
          $("#btnChangeToInvoice").prop("disabled", true).html(`
            <span class="spinner-border spinner-border-sm"></span>
            Convertendo...
          `);
        },

        success: function (response) {
          if (!response.success) {
            Swal.fire({
              icon: "error",
              title: "Erro",
              text: response.error || "Falha ao converter a Proforma.",
            });
            return;
          }

          // Já existe uma factura gerada
          if (response.already_converted) {
            Swal.fire({
              icon: "info",
              title: "Factura já existente",
              text: `A Factura Recibo ${response.reference} já foi emitida anteriormente.`,
              confirmButtonText: "Abrir Factura",
            }).then(() => {
              window.location.href = `invoice.php?id=${response.new_invoice_id}`;
            });

            return;
          }

          // Nova factura criada
          Swal.fire({
            icon: "success",
            title: "Sucesso",
            text: `Factura Recibo ${response.reference} criada com sucesso.`,
            confirmButtonText: "Abrir Factura",
          }).then(() => {
            window.location.href = `invoice.php?id=${response.new_invoice_id}`;
          });
        },

        error: function (xhr) {
          Swal.fire({
            icon: "error",
            title: "Erro",
            text:
              xhr.responseJSON?.error ||
              "Erro interno ao converter a Proforma.",
          });
        },

        complete: function () {
          $("#btnChangeToInvoice").prop("disabled", false).html(`
            <span class="material-icons-outlined">receipt_long</span>
            Emitir Factura Recibo
          `);
        },
      });
    });
  });

  // =========================
  // 7. EDITAR PROFORMA
  // =========================
  $("#btnEditar").on("click", function () {
    window.location.href = `create_proform.php?edit_id=${currentInvoice.id}`;
  });

  // =========================
  // INIT
  // =========================
  loadInvoice();
});
