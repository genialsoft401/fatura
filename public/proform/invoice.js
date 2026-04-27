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
    $.getJSON("proform/ajax/get_invoice.php", { id: invoiceId })
      .done((inv) => {
        currentInvoice = inv;

        $("#fatura-id").text(`PROFORMA #${inv.series}/${inv.id}`);
        $("#status-invoice").text("PROFORMA");
        $("#subtitle-client").text(inv.client_name);

        setupButtons(inv);
      })
      .fail(() => {
        alert("Erro ao carregar factura proforma");
      });
  }

  // =========================
  // 3. CARREGAR HTML VISUAL
  // =========================
  $("#fatura-container").load(
    `proform/ajax/invoice_public.php?id=${invoiceId}`,
  );

  // =========================
  // 4. BOTÕES CONDICIONAIS
  // =========================
  function setupButtons(inv) {
    if (inv.status_invoice === "Rascunho") {
      $("#btnFinalizar, #btnEditar").removeClass("d-none");
    } else {
      $("#btnPdf, #generatePdf, #btnEnviar").removeClass("d-none");
      $("#btnNotaCredito").removeClass("d-none");
    }
  }

  // =========================
  // 5. GERAR PDF PROFORMA
  // =========================
  function gerarPdfProforma() {
    if (!currentInvoice) return;

    const el = document.querySelector("#fatura-container");

    const opt = {
      margin: 10,
      filename: `Proforma_${currentInvoice.codigo}.pdf`,
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
  $("#btnFinalizar").on("click", function () {
    Swal.fire({
      title: "Converter em Factura?",
      text: "A Proforma será convertida em factura oficial.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sim",
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.post("proform/ajax/update_status.php", {
        invoice_id: currentInvoice.id,
        new_status: "Emitida",
      })
        .done(() => {
          Swal.fire("Sucesso", "Factura emitida!", "success").then(() =>
            location.reload(),
          );
        })
        .fail(() => {
          Swal.fire("Erro", "Falha ao converter", "error");
        });
    });
  });

  // =========================
  // 7. EDITAR PROFORMA
  // =========================
  $("#btnEditar").on("click", function () {
    window.location.href = `create_invoices.php?edit_id=${currentInvoice.id}`;
  });

  // =========================
  // 8. NOTA DE CRÉDITO (DESABILITADO EM PROFORMA)
  // =========================
  $("#btnNotaCredito").on("click", function () {
    Swal.fire(
      "Atenção",
      "Notas de crédito só podem ser emitidas após factura oficial.",
      "info",
    );
  });

  // =========================
  // INIT
  // =========================
  loadInvoice();
});
