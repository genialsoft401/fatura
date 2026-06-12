const currentYear = new Date().getFullYear();
const pg_serie = document.getElementById("pg_serie");
const get = new URLSearchParams(window.location.search).get("id");
const invoiceId = get.substring(get.lastIndexOf("/") + 1);
let document_type = "FT";

if (!invoiceId) {
  alert("Fatura não encontrada!");
}

pg_serie.value = currentYear;
function loadFaturaWithRetry(invoiceId, maxRetries = 5) {
  let attempts = 0;
  const $preloader = $("#preloader");
  const $container = $("#fatura-container");

  function tryLoad() {
    attempts++;
    $preloader.show();
    $container.hide();

    $container.load(
      "invoices/ajax/invoice_public.php?id=" + invoiceId,
      function (response, status) {
        if (status === "success") {
          $preloader.hide();
          $container.show();
        } else if (attempts <= maxRetries) {
          setTimeout(tryLoad, 1200); // espera 1.2s antes de tentar dnv
        } else {
          $preloader.hide();
          $container
            .show()
            .html(
              '<div style="color:#b12; font-weight:bold; padding: 15px;">Erro ao carregar a fatura. Tente novamente mais tarde.</div>',
            );
        }
      },
    );
  }

  tryLoad();
}

loadFaturaWithRetry(invoiceId, 5);
// ───────── invoice.js ─────────
$(function () {
  // id vindo da query‑string
  const invoiceId = new URLSearchParams(location.search)
    .get("id")
    ?.split("/")
    .pop();
  if (!invoiceId) return alert("Fatura não encontrada");

  // ---------------- VAR GLOBAL ----------------
  let currentInvoice = null; // visível a todos abaixo

  // ---------- 1) carrega HTML da fatura ----------
  $("#fatura-container").load(
    `invoices/ajax/invoice_public.php?id=${invoiceId}`,
  );

  // ---------- 2) carrega JSON da fatura ----------
  $.getJSON("invoices/ajax/get_invoice.php", { id: invoiceId })
    .done((response) => {
      // Se os dados vêm dentro de response.data
      const inv = response.data;

      if (!inv) {
        throw new Error("Dados da fatura não encontrados.");
      }

      currentInvoice = inv; // guarda para modal

      // Esconde todos os botões antes
      $(
        "#btnFinalizar, #btnEditar, #btnCloneToInvoice, #btnNotaCredito, #generatePdf, #btnEnviar",
      ).addClass("d-none");

      // Preenche formulário/UI
      $('#formPagamento [name="invoice_id"]').val(inv.id);

      $("#fatura-id").text(
        inv.status_invoice === "Rascunho"
          ? `${inv.series}/${inv.id}`
          : inv.numero_validacao,
      );

      $("#status-invoice").text(inv.status_invoice || "-");
      $("#subtitle-client").text(inv.client_name || "-");

      // Mostrar botões conforme status
      if (inv.status_invoice === "Rascunho") {
        $("#btnFinalizar").removeClass("d-none");
        $("#btnEditar").removeClass("d-none");
      } else {
        $("#btnCloneToInvoice").removeClass("d-none");
        $("#btnNotaCredito").removeClass("d-none");
        $("#generatePdf").removeClass("d-none");
        $("#btnEnviar").removeClass("d-none");
      }
    })
    .fail((xhr) => {
      console.error("Erro AJAX:", xhr);

      Swal.fire({
        icon: "error",
        title: "Erro ao carregar fatura",
        text:
          xhr.responseJSON?.message || "Não foi possível carregar os dados.",
        timer: 2000,
        showConfirmButton: false,
      });
    });

  //=============================================================
  //  Botao de gerar recibo
  //=============================================================

  $("#btnRecibo").on("click", function () {
    if (!currentInvoice || !currentInvoice.id) {
      console.error("Nenhuma fatura selecionada.");
      return;
    }

    const $btn = $(this);

    $.ajax({
      url: "invoices/ajax/get_last_receipt.php",
      type: "GET",
      dataType: "json",
      data: {
        invoice_id: currentInvoice.id,
      },

      beforeSend: function () {
        $btn.prop("disabled", true);
      },

      success: function (res) {
        if (!res || !res.success) {
          Swal.fire({
            icon: "warning",
            title: "Aviso",
            text: res?.message || "Nenhum recibo encontrado para esta fatura.",
          });
          return;
        }

        const data = res.data || {};

        if (data.length > 0) {
          renderReceipts(data);
          showReceiptsModal();
        } else {
          showPaymentModal();
        }
      },

      error: function (xhr, status, error) {
        console.error("AJAX ERROR:", xhr.responseText);

        Swal.fire({
          icon: "error",
          title: "Erro",
          text: "Não foi possível obter o recibo.",
        });
      },

      complete: function () {
        $btn.prop("disabled", false);
      },
    });
  });

  /* ======================================================
   * MODAIS
   * ====================================================== */

  // ---------- 3) abrir recibo ou modal Pagamento ----------
  function showPaymentModal() {
    new bootstrap.Modal(document.getElementById("modalPagamento")).show();
  }

  function showReceiptsModal() {
    new bootstrap.Modal(document.getElementById("modalReceipts")).show();
  }

  /* ======================================================
   * RENDER RECIBOS
   * ====================================================== */

  function renderReceipts(receipts) {
    const container = $("#receiptsList");

    container.empty();

    if (!receipts.length) {
      container.html(`
      <div class="alert alert-warning mb-0">
        Nenhum recibo encontrado.
      </div>
    `);
      return;
    }

    const html = receipts
      .map((receipt) => {
        const amount = formatCurrency(receipt.amount_paid);

        const createdAt = receipt.created_at
          ? new Date(receipt.created_at).toLocaleDateString("pt-PT")
          : "-";

        return `
        <div class="border rounded-3 p-3 mb-3 bg-light shadow-sm">
          
          <div class="d-flex justify-content-between align-items-center mb-2">
            
            <div>
              <h6 class="mb-1 fw-bold">
                Recibo #${receipt.receipt_number || receipt.id}
              </h6>

              <small class="text-muted">
                ${createdAt}
              </small>
            </div>

            <span class="badge bg-success fs-6">
              ${amount} Kz
            </span>

          </div>

          <div class="d-flex gap-2 mt-3">

            <a
              href="invoices/recibo_pdf.php?id=${receipt.id}"
              target="_blank"
              class="btn btn-sm btn-primary"
            >
              <i class="fa fa-file-pdf me-1"></i>
              Ver PDF
            </a>

            <button
              class="btn btn-sm btn-outline-secondary btnPrintReceipt"
              data-id="${receipt.id}"
            >
              <i class="fa fa-print me-1"></i>
              Imprimir
            </button>

          </div>

        </div>
      `;
      })
      .join("");

    container.html(html);
  }

  /* ======================================================
   * UTILITÁRIOS
   * ====================================================== */

  function formatCurrency(value) {
    return Number(value || 0).toLocaleString("pt-PT", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  /* ======================================================
   * EVENTO IMPRIMIR
   * ====================================================== */

  $(document).off("click", ".btnPrintReceipt");

  $(document).on("click", ".btnPrintReceipt", function () {
    const id = $(this).data("id");

    if (!id) return;

    window.open(`invoices/recibo_pdf.php?id=${id}`, "_blank");
  });

  // ---------- 4) abre modal Pagamento ----------
  $("#modalPagamento").on("show.bs.modal", function () {
    if (!currentInvoice) return alert("Fatura ainda não carregada!");

    console.log(currentInvoice);

    const total = Number(currentInvoice.final_total) || 0;
    const jaPago = Number(currentInvoice.paid_total) || 0;
    const retention = Number(currentInvoice.retention) || 0;
    const saldo = total - jaPago;

    $("#pg_valor")
      .val(saldo.toFixed(2))
      .attr("max", saldo) // HTML5 — impede submit se > max
      .data("saldo", saldo); // guarda para o listener abaixo

    $("#pg_saldo").text(
      `Kz de ${saldo.toLocaleString("pt-PT", { minimumFractionDigits: 2 })} Kz`,
    );

    // data = hoje
    $("#pg_data").val(new Date().toISOString().slice(0, 10));

    $("#pg_obs").val(retention != null ? `Retenção: ${retention}` : "");
  });

  // ---------- 4) submit do pagamento ----------
  $("#formPagamento").on("submit", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find("[type=submit]");
    $btn.prop("disabled", true);

    Swal.fire({
      title: "Processando...",
      text: "Registrando o pagamento, aguarde.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    $.post("invoices/ajax/registrar_pagamento.php", $form.serialize())
      .done((resp) => {
        Swal.close();
        bootstrap.Modal.getInstance(
          document.getElementById("modalPagamento"),
        ).hide();

        // abre o PDF do recibo gerado
        if (resp && resp.receipt_id) {
          window.open(
            "invoices/recibo_pdf.php?id=" + resp.receipt_id,
            "_blank",
          );
        }

        Swal.fire({
          icon: "success",
          title: "Sucesso",
          text: "Pagamento registrado com sucesso!",
        }).then(() => {
          location.reload();
        });
      })
      .fail((xhr) => {
        Swal.close();
        Swal.fire({
          icon: "error",
          title: "Erro",
          text:
            "Erro ao registrar: " + (xhr.responseText || "Tente novamente."),
        });
      })
      .always(() => {
        $btn.prop("disabled", false);
      });
  });

  // ---------- 5) botão PDF ----------
  /**
   * Gera um PDF (A4 – retrato) a partir de um elemento HTML.
   * @param {String|HTMLElement} el        seletor ou nó DOM com a fatura
   * @param {String}             filename  nome do arquivo .pdf
   * @param {Number}             copies    nº de vias (default = 2)
   * @returns {Promise<void>}
   */
  function gerarPdfFatura(el, filename = "fatura.pdf", copies = 2) {
    return new Promise(async (resolve, reject) => {
      try {
        const original =
          typeof el === "string" ? document.querySelector(el) : el;

        if (!original) return reject("Elemento não encontrado");

        $("#address").addClass("d-none");

        const waitImages = (container) => {
          const imgs = container.querySelectorAll("img");
          return Promise.all(
            Array.from(imgs).map((img) => {
              if (img.complete) return Promise.resolve();
              return new Promise((res) => {
                img.onload = res;
                img.onerror = res;
              });
            }),
          );
        };

        const tempDiv = document.createElement("div");
        tempDiv.style.width = "210mm";
        tempDiv.style.background = "#fff";

        for (let i = 0; i < copies; i++) {
          const clone = original.cloneNode(true);

          // garantir footer visível
          clone.querySelectorAll(".inv-footer").forEach((el) => {
            el.classList.remove("d-none");
          });

          const wrapper = document.createElement("div");

          //  CORREÇÃO PRINCIPAL: NÃO forçar altura fixa
          wrapper.style.width = "210mm";
          wrapper.style.boxSizing = "border-box";

          //  MAIS MARGEM NO CABEÇALHO
          wrapper.style.paddingTop = "25mm"; // ajusta aqui o espaço do header
          wrapper.style.paddingBottom = "10mm";

          wrapper.appendChild(clone);
          tempDiv.appendChild(wrapper);

          // ❌ REMOVIDO pageBreakAfter (causava página em branco)
        }

        document.body.appendChild(tempDiv);

        $(".action-panel").addClass("d-none");

        await waitImages(tempDiv);

        const opt = {
          margin: 0,
          filename,
          image: { type: "jpeg", quality: 1 },
          html2canvas: {
            scale: 2,
            useCORS: true,
            backgroundColor: "#ffffff",
          },
          jsPDF: {
            unit: "mm",
            format: "a4",
            orientation: "portrait",
          },
        };

        html2pdf()
          .set(opt)
          .from(tempDiv)
          .toPdf()
          .get("pdf")
          .then((pdf) => {
            const pageCount = pdf.internal.getNumberOfPages();

            pdf.setFont("helvetica", "normal");
            pdf.setFontSize(8);

            const footerText = "Powered By BXpert";

            for (let i = 1; i <= pageCount; i++) {
              pdf.setPage(i);

              pdf.text(footerText, 105, 290, { align: "center" });
              pdf.text(`${i}/${pageCount}`, 200, 293, { align: "right" });
            }
          })
          .save()
          .then(() => {
            cleanup();
            resolve();
          })
          .catch((err) => {
            cleanup();
            reject(err);
          });

        function cleanup() {
          $(".action-panel").removeClass("d-none");
          $("#address").removeClass("d-none");

          if (tempDiv && tempDiv.parentNode) {
            tempDiv.parentNode.removeChild(tempDiv);
          }
        }
      } catch (error) {
        reject(error);
      }
    });
  }

  $("#btnPdf, #generatePdf").on("click", function () {
    gerarPdfFatura(
      "#fatura-container",
      `${currentInvoice.reference}.pdf`, // nome dinâmico
      2, // nº de vias
    ).catch(console.error);
  });

  // ---------- Nota de Crédito ----------
  $("#btnNotaCredito").on("click", async function () {
    try {
      // Validação da fatura
      if (!currentInvoice?.id) {
        return Swal.fire({
          icon: "error",
          title: "Erro",
          text: "Fatura ainda não carregada.",
        });
      }

      // Verifica se já existe nota de crédito
      const verifyResponse = await $.ajax({
        url: "invoices/ajax/credit_notes.php",
        method: "GET",
        dataType: "json",
        data: {
          invoice_id: currentInvoice.id,
        },
      });

      // Se já existir nota de crédito
      if (verifyResponse?.data?.id) {
        return (window.location.href = `credit_notes/ajax/generate_pdf.php?id=${verifyResponse.data.id}`);
      }

      // Pergunta antes de emitir
      const result = await Swal.fire({
        title: "Emitir Nota de Crédito?",
        text: "A Nota de Crédito será associada a esta fatura.",
        input: "textarea",
        inputLabel: "Motivo (opcional)",
        inputPlaceholder: "Descreva o motivo da correção/anulação…",
        showCancelButton: true,
        confirmButtonText: "Emitir",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#3085d6",
      });

      if (!result.isConfirmed) return;

      // Criar nota de crédito
      const res = await $.ajax({
        url: "invoices/ajax/create_credit_note.php",
        method: "POST",
        dataType: "json",
        data: {
          invoice_id: currentInvoice.id,
          reason: result.value || "",
        },
      });

      // Sucesso
      if (res?.success && res?.credit_note_id) {
        window.location.href = `credit_notes/ajax/generate_pdf.php?id=${res.credit_note_id}`;
      } else {
        Swal.fire({
          icon: "error",
          title: "Erro",
          text: res?.error || "Não foi possível emitir a Nota de Crédito.",
        });
      }
    } catch (error) {
      console.error("Erro ao processar Nota de Crédito:", error);

      Swal.fire({
        icon: "error",
        title: "Erro",
        text: error?.responseText || "Falha ao processar a Nota de Crédito.",
      });
    }
  });

  $("#pg_valor").on("input", function () {
    const saldo = $(this).data("saldo"); // quanto ainda falta pagar
    const valor = parseFloat(this.value) || 0;
    const $submit = $("#formPagamento button[type=submit]");

    if (valor > saldo) {
      // marca o campo como inválido visualmente
      $(this).addClass("is-invalid");

      // mostra aviso (Bootstrap 5)
      if (!$("#pg_valor_feedback").length) {
        $('<div id="pg_valor_feedback" class="invalid-feedback">')
          .text(
            `O valor não pode exceder o saldo de ${saldo.toLocaleString("pt-PT", { minimumFractionDigits: 2 })} Kz.`,
          )
          .insertAfter(this);
      }

      $submit.prop("disabled", true); // impede o submit
    } else {
      $(this).removeClass("is-invalid");
      $("#pg_valor_feedback").remove();
      $submit.prop("disabled", false);
    }
  });

  /* ---------- 1. inicializa Quill ---------- */
  const quill = new Quill("#editor-container", {
    theme: "snow",
    modules: {
      toolbar: "#editor-toolbar",
    },
  });

  /* ---------- 2. abre a modal ---------- */
  $("#modalEnviarEmail").on("show.bs.modal", function () {
    if (!currentInvoice) {
      return alert("Fatura ainda não carregada!");
    }

    // Id oculto
    $("#email_invoice_id").val(currentInvoice.id);

    // Assunto default
    const codigo = `${currentInvoice.reference}`;
    $('input[name="subject"]').val(
      `Fatura #${codigo} – ${currentInvoice.company_name}`,
    );

    /* --- Corpo default (HTML) --- */
    const issue = new Intl.DateTimeFormat("pt-BR").format(
      new Date(currentInvoice.issue_date),
    );
    const dueDate = new Intl.DateTimeFormat("pt-BR").format(
      new Date(
        new Date(currentInvoice.issue_date).setDate(
          +currentInvoice.issue_date.split("-")[2] + +currentInvoice.due_date,
        ),
      ),
    );
    const total = Number(currentInvoice.final_total).toLocaleString("pt-PT", {
      minimumFractionDigits: 2,
    });

    const template = `
      <p>Prezado(a) <strong>${currentInvoice.client_name}</strong>,</p>

      <p>Segue em anexo a <strong>fatura nº ${codigo}</strong>,
      no valor de <strong>${currentInvoice.company_symbol} ${total}</strong>,
      emitida em ${issue} e com vencimento em ${dueDate}.</p>

      <p>Qualquer dúvida estou à disposição.</p>

      <p>Atenciosamente,<br>
      &nbsp;</p>`;

    quill.setContents(quill.clipboard.convert(template));
  });

  /* ---------- 3. submit ---------- */
  $("#formEnviarEmail").on("submit", function (e) {
    e.preventDefault();

    // valida Bootstrap
    if (this.checkValidity() === false) {
      this.classList.add("was-validated");
      return;
    }

    // passa o HTML do Quill para <textarea hidden>
    $("#body-hidden").val(quill.root.innerHTML);

    $.post("invoices/ajax/send_invoice.php", $(this).serialize())
      .done(() => {
        bootstrap.Modal.getInstance(
          document.getElementById("modalEnviarEmail"),
        ).hide();
        alert("E‑mail enviado com sucesso!");
      })
      .fail((xhr) => {
        alert("Erro: " + xhr.responseText);
      });
  });

  // ---------- 6) Finalizar Fatura (Rascunho -> Pendente) ----------
  // ---------- 6) Finalizar Fatura (Rascunho -> Pendente) ----------
  $("#btnFinalizar").on("click", function () {
    Swal.fire({
      title: "Finalizar Fatura?",
      text: "A fatura deixará de ser rascunho e passará para Pendente.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sim, finalizar",
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          "invoices/ajax/update_status.php",
          {
            invoice_id: currentInvoice.id,
            new_status: "Finalizada",
            document_type: document_type,
          },
          function (res) {
            if (res.success) {
              Swal.fire(
                "Sucesso",
                "Fatura finalizada com sucesso!",
                "success",
              ).then(() => location.reload());
            } else {
              Swal.fire(
                "Erro",
                res.error || "Erro ao atualizar status",
                "error",
              );
            }
          },
          "json",
        );
      }
    });
  });

  // ---------- 7) Editar Fatura (Redirecionar) ----------
  $("#btnEditar").on("click", function () {
    window.location.href = `create_${document_type === "PF" ? "proform" : "invoices"}.php?edit_id=${currentInvoice.id}`;
  });

  // ===========================================
  // CLONAR FACTURA
  // ===========================================
  $("#btnCloneToInvoice").on("click", function () {
    // ==========================================
    // VALIDAR
    // ==========================================

    if (!invoiceId) {
      return Swal.fire({
        icon: "error",
        title: "Erro",
        text: "Documento não encontrado.",
      });
    }

    // ==========================================
    // CONFIRMAR
    // ==========================================
    Swal.fire({
      icon: "question",
      title: "Clonar Factura",
      text: "Deseja clonar esta Factura Recibo?",
      showCancelButton: true,
      confirmButtonText: "Clonar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
    }).then((result) => {
      // cancelado
      if (!result.isConfirmed) {
        return;
      }

      // ==========================================
      // AJAX
      // ==========================================
      $.ajax({
        url: "invoices/ajax/clone_invoice.php",

        type: "POST",

        dataType: "json",

        data: {
          invoice_id: invoiceId,
        },

        // ==========================================
        // BEFORE SEND
        // ==========================================
        beforeSend: function () {
          $("#btnCloneToInvoice").prop("disabled", true).html(`
            <span class="spinner-border spinner-border-sm"></span>
            Clonando...
          `);
        },

        // ==========================================
        // SUCCESS
        // ==========================================
        success: function (data) {
          if (!data.success) {
            Swal.fire({
              icon: "error",
              title: "Erro",
              text: data.error || "Erro ao clonar factura.",
            });

            return;
          }

          Swal.fire({
            icon: "success",
            title: "Sucesso",
            text: "Factura clonada com sucesso!",
            timer: 1800,
            showConfirmButton: false,
          });

          // redirecionar
          setTimeout(() => {
            window.location.href = "invoice.php?id=" + data.new_invoice_id;
          }, 1500);
        },

        // ==========================================
        // ERROR
        // ==========================================
        error: function (xhr) {
          console.error(xhr);

          Swal.fire({
            icon: "error",
            title: "Erro Interno",
            text:
              xhr.responseJSON?.error ||
              xhr.responseText ||
              "Erro ao clonar factura.",
          });
        },

        // ==========================================
        // COMPLETE
        // ==========================================
        complete: function () {
          $("#btnCloneToInvoice").prop("disabled", false).html(`
            <span class="material-icons-outlined">
              content_copy
            </span>
            Clonar Factura
          `);
        },
      });
    });
  });
});

$(document).ready(function () {
  const get = new URLSearchParams(window.location.search).get("id");
  const invoiceId = get.substring(get.lastIndexOf("/") + 1);
  if (!invoiceId) {
    alert("Fatura não encontrada!");
    return;
  }

  $.ajax({
    url: "invoices/ajax/get_invoice.php",
    type: "GET",
    data: { id: invoiceId },
    dataType: "json",
    success: function (response) {
      if (response.error) {
        alert(response.error);
        return;
      }

      $("#generatePdf").on("click", function () {
        const element = document.getElementById("invoice");

        const options = {
          margin: 10,
          filename: "fatura.pdf",
          image: {
            type: "jpeg",
            quality: 1,
          },
          html2canvas: {
            scale: 2,
            useCORS: true,
          },
          jsPDF: {
            unit: "mm",
            format: "a4",
            orientation: "portrait",
          },
          pagebreak: {
            mode: ["avoid-all", "css", "legacy"],
          },
        };

        html2pdf().set(options).from(element).save();
      });
    },
    error: function () {
      alert("Erro ao carregar os dados da fatura.");
    },
  });

  function generateQRCode(text) {
    const qr = qrcode(0, "L");
    qr.addData(text);
    qr.make();
    const qrCodeImgTag = qr.createImgTag(5);
    const base64Image = qrCodeImgTag.match(/src="([^"]*)"/)[1];
    return base64Image;
  }
});
