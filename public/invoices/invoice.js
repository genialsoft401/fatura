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
        "#btnFinalizar, #btnEditar, #btnCloneToInvoice, #btnNotaCredito, #generatePdf, #btnEnviar, #btnDeleteInvoice, #btnRecibo",
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
        $("#generatePdf").removeClass("d-none");
        $("#btnEnviar").removeClass("d-none");
        $("#btnCloneToInvoice").removeClass("d-none");
        $("#btnDeleteInvoice").removeClass("d-none");
      } else {
        $("#btnRecibo").removeClass("d-none");
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

  $("#btnDeleteInvoice")
    .off("click")
    .on("click", function () {
      Swal.fire({
        title: "Tem a certeza?",
        text: "Se a fatura estiver em rascunho será eliminada. Caso contrário será cancelada.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sim, continuar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
      }).then((result) => {
        if (!result.isConfirmed) {
          return;
        }

        const $btn = $("#btnDeleteInvoice");
        $btn.prop("disabled", true);

        $.ajax({
          url: "invoices/ajax/delete_invoice.php",
          type: "POST",
          dataType: "json",
          data: {
            invoice_id: invoiceId,
          },

          success: function (response) {
            if (response.success) {
              Swal.fire({
                icon: "success",
                title: "Sucesso",
                text: response.message,
                confirmButtonText: "OK",
              }).then(() => {
                // Atualiza a tabela sem voltar à primeira página
                $("#invoicesTable").DataTable().ajax.reload(null, false);

                if (typeof loadDashboardCards === "function") {
                  loadDashboardCards();
                }

                // Se estiver na página de edição pode recarregar
                location.replace("list_invoices.php");
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Erro",
                text:
                  response.message || "Não foi possível concluir a operação.",
              });
            }
          },

          error: function (xhr) {
            Swal.fire({
              icon: "error",
              title: "Erro",
              text:
                xhr.responseJSON?.message ||
                "Erro de comunicação com o servidor.",
            });
          },

          complete: function () {
            $btn.prop("disabled", false);
          },
        });
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

  /*=============================================================================================
                                  FUNÇÃO GERAR PDF DA FACTURA
============================================================================================= */

  /**
   * gerarPdfFatura.js
   * -----------------------------------------------------------------------
   * Geração de PDF de fatura 100% orientada a DADOS — não depende de nenhum
   * elemento HTML renderizado na página.
   *
   * Usa jsPDF (client-side), com posicionamento MANUAL (x/y) igual ao layout
   * original — dá controle total pixel a pixel. A tabela de itens pagina
   * automaticamente: a cada linha checamos se ainda cabe na página atual;
   * se não couber, abrimos uma nova página e repetimos o cabeçalho da tabela.
   *
   * Dependências (CDN — coloque antes deste arquivo):
   *   <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
   *   <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
   *   (qrcodejs só é necessário se você quiser gerar o QR no próprio browser,
   *   ver gerarQrCodeDataURL() mais abaixo)
   *
   * -----------------------------------------------------------------------
   * FORMATO DOS DADOS ESPERADOS (invoiceData) — igual ao da versão anterior
   * -----------------------------------------------------------------------
   * {
   *   document_type: 'PF' | 'FT',
   *   reference:     'PF 2026/000005',
   *   issue_date:    '2026-06-24',
   *   due_date:      '2026-06-24',
   *   observation:   '-',
   *   company: { name, address, phone, email, website, registration_number,
   *              logoImage: 'data:image/png;base64,....' },
   *   client:  { name, contributor, address },
   *   items:   [ { code, name, description, unit_price, quantity, tax, discount } ],
   *   totals:  { total_sum, total_discount, total_tax, retention_value, final_total }, // opcional
   *   moneySymbol: 'Kz', moneyPos: 'right',
   *   vat_regime:  'geral' | 'simplificado',
   *   iban:        'AO06.0006.0000.1234.5678.9012.3',
   *   qrImage:     'data:image/png;base64,....'
   * }
   * -----------------------------------------------------------------------
   */

  // ---------------------------------------------------------------------------
  // Constantes de layout (pt — A4 = 595.28 x 841.89)
  // ---------------------------------------------------------------------------
  const PAGE_WIDTH = 595.28;
  const PAGE_HEIGHT = 841.89;
  const MARGIN_LEFT = 40;
  const MARGIN_RIGHT = 40;
  const MARGIN_TOP = 40;
  const MARGIN_BOTTOM = 70;
  const CONTENT_RIGHT = PAGE_WIDTH - MARGIN_RIGHT; // 555.28
  const GRAY = [139, 139, 139]; // #8b8b8b — linhas divisórias
  const GRAY_LABEL = [102, 102, 102]; // #666666 — rótulos "opacos"
  const GRAY_FOOTER = [150, 150, 150];
  const BLACK = [0, 0, 0];

  // colunas da tabela de itens
  // 🔧 Reformuladas com folgas seguras entre colunas — antes "Descrição" (até x=385)
  // ficava a poucos pontos de "Preço Uni." (right-align x=390), e valores grandes
  // (ex: "3.916.666,67 Kz") invadiam o espaço da descrição e sobrepunham o texto.
  const COL_CODE_X = MARGIN_LEFT; // 40
  const COL_CODE_MAX_WIDTH = 60; // largura máx. antes de encolher a fonte

  const COL_DESC_X = 105;
  const COL_DESC_WIDTH = 130; // termina em x=235 (antes: 385)

  const COL_PRECO_RIGHT_X = 335; // valor termina aqui (right-align)
  const COL_PRECO_MAX_WIDTH = 85; // início mín. em x=250 → folga de 15pt da Descrição

  const COL_QTD_CENTER_X = 365;
  const COL_TAXA_CENTER_X = 405;
  const COL_DESCPCT_CENTER_X = 440;

  const COL_TOTAL_RIGHT_X = CONTENT_RIGHT; // 555.28
  const COL_TOTAL_MAX_WIDTH = 95; // início mín. em x=460 → folga de 17pt da coluna Desc.

  // ---------------------------------------------------------------------------
  // Utils
  // ---------------------------------------------------------------------------

  function formatCurrency(value, symbol = "Kz", position = "right") {
    const n = Number(value) || 0;
    const fixed = n.toFixed(2);
    const [intPart, decPart] = fixed.split(".");
    const withThousands = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    const formatted = `${withThousands},${decPart}`;
    return position === "left"
      ? `${symbol} ${formatted}`
      : `${formatted} ${symbol}`;
  }

  function dateBr(dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    if (isNaN(d)) return "-";
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    return `${dd}/${mm}/${d.getFullYear()}`;
  }

  function calcItemTotal(it) {
    const base = it.unit_price * it.quantity;
    const discount = base * ((it.discount || 0) / 100);
    const tax = (base - discount) * ((it.tax || 0) / 100);
    return { base, discount, tax, total: base - discount + tax };
  }

  async function imageUrlToDataURL(url) {
    const res = await fetch(url);
    const blob = await res.blob();
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }

  function gerarQrCodeDataURL(text, size = 200) {
    return new Promise((resolve, reject) => {
      if (typeof QRCode === "undefined") {
        reject(
          new Error("Biblioteca qrcodejs não carregada (ver topo do arquivo)."),
        );
        return;
      }
      const div = document.createElement("div");
      div.style.display = "none";
      document.body.appendChild(div);
      new QRCode(div, {
        text,
        width: size,
        height: size,
        correctLevel: QRCode.CorrectLevel.L,
      });
      setTimeout(() => {
        const el = div.querySelector("canvas") || div.querySelector("img");
        const dataUrl =
          el.tagName === "CANVAS" ? el.toDataURL("image/png") : el.src;
        document.body.removeChild(div);
        resolve(dataUrl);
      }, 50);
    });
  }

  /** Carrega uma dataURL como HTMLImageElement (para saber a proporção real). */
  function loadImage(src) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = src;
    });
  }

  /** Ajusta um retângulo maxW x maxH mantendo a proporção original da imagem. */
  function fitBox(imgW, imgH, maxW, maxH) {
    const ratio = Math.min(maxW / imgW, maxH / imgH);
    return { w: imgW * ratio, h: imgH * ratio };
  }

  function getImageFormat(dataUrl) {
    const match = /^data:image\/(\w+);/.exec(dataUrl || "");
    if (!match) return "PNG";
    const ext = match[1].toUpperCase();
    return ext === "JPG" ? "JPEG" : ext;
  }

  /**
   * 🔧 Remove tags HTML e decodifica entidades comuns (&nbsp;, &amp;, etc.)
   * Corrige itens vindos com HTML bruto, ex: "<p>venda de telemoveis</p>"
   * ou "Contabilidade&nbsp; - Avença Mensal&nbsp;".
   */
  function stripHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/<[^>]*>/g, " ")
      .replace(/&nbsp;/gi, " ")
      .replace(/&amp;/gi, "&")
      .replace(/&lt;/gi, "<")
      .replace(/&gt;/gi, ">")
      .replace(/&quot;/gi, '"')
      .replace(/&#39;/gi, "'")
      .replace(/\s+/g, " ")
      .trim();
  }

  /**
   * 🔧 Desenha texto respeitando uma largura máxima: se o texto (ex: um valor
   * monetário grande) for mais largo que o espaço disponível na coluna, a
   * fonte é reduzida progressivamente até caber — evita qualquer sobreposição
   * com as colunas vizinhas, independentemente da magnitude do valor.
   */
  function drawFittedText(
    doc,
    text,
    x,
    y,
    maxWidth,
    align = "left",
    baseSize = 8,
  ) {
    let size = baseSize;
    doc.setFontSize(size);
    while (doc.getTextWidth(text) > maxWidth && size > 6) {
      size -= 0.5;
      doc.setFontSize(size);
    }
    doc.text(text, x, y, { align });
    doc.setFontSize(baseSize);
  }

  // ---------------------------------------------------------------------------
  // Adaptador: converte o JSON "achatado" da sua API para o formato esperado
  // ---------------------------------------------------------------------------

  async function prepareInvoiceData(api, options = {}) {
    // reproduz literalmente a concatenação do PHP original:
    // "{endereco}, {cidade} - {país}" — mesmo que cidade/país venham vazios
    const juntarEndereco = (endereco, cidade, pais) =>
      `${endereco || ""}, ${cidade || ""} - ${pais || ""}`;

    const issueDate = new Date(api.issue_date);
    const dueDate = new Date(issueDate);
    dueDate.setDate(dueDate.getDate() + (Number(api.due_date) || 0));

    const invoiceData = {
      document_type: api.document_type,
      reference: api.reference,
      issue_date: api.issue_date,
      due_date: dueDate.toISOString().slice(0, 10),
      observation: api.observation,

      company: {
        name: api.company_name,
        address: juntarEndereco(
          api.company_address,
          api.company_city,
          api.company_country,
        ),
        phone: api.company_phone,
        email: api.company_email,
        website: api.website,
        registration_number: api.registration_number,
        logoImage: null,
      },

      client: {
        name: api.client_name,
        contributor: api.client_contributor,
        address: juntarEndereco(
          api.client_address,
          api.client_city,
          api.client_country,
        ),
      },

      items: api.items,

      totals: {
        total_sum: api.total_sum,
        total_discount: api.total_discount,
        total_tax: api.total_tax,
        retention_value: api.retention_value,
        final_total: api.final_total,
      },

      moneySymbol: api.symbol,
      moneyPos: api.position,
      vat_regime: api.vat_regime,
      iban: api.bank_details || "-",
      qrImage: null,
    };

    if (api.logo_url && options.logoBaseUrl) {
      try {
        invoiceData.company.logoImage = await imageUrlToDataURL(
          options.logoBaseUrl + api.logo_url,
        );
      } catch (e) {
        console.warn("Não foi possível carregar o logo:", e);
      }
    }

    if (api.id && options.qrBaseUrl) {
      try {
        invoiceData.qrImage = await gerarQrCodeDataURL(
          options.qrBaseUrl + api.id,
        );
      } catch (e) {
        console.warn("Não foi possível gerar o QR code:", e);
      }
    }

    return invoiceData;
  }

  // ---------------------------------------------------------------------------
  // Desenho — cabeçalho (empresa + logo)
  // ---------------------------------------------------------------------------

  function drawCompanyHeader(doc, company, logoImg) {
    let y = MARGIN_TOP + 12;

    doc.setFont("helvetica", "bold");
    doc.setFontSize(13);
    doc.setTextColor(...BLACK);
    doc.text((company.name || "").toUpperCase(), MARGIN_LEFT, y);
    y += 15;

    doc.setFont("helvetica", "normal");
    doc.setFontSize(8.5);
    const enderecoLinhas = doc.splitTextToSize(
      String(company.address || ""),
      260,
    );
    enderecoLinhas.forEach((linha) => {
      doc.text(linha, MARGIN_LEFT, y);
      y += 10;
    });
    y += 2;

    [
      `Tel: ${company.phone || "-"}`,
      `E-mail: ${company.email || "-"}`,
      `Website: ${company.website || "-"}`,
      `Contribuinte: ${company.registration_number || "-"}`,
    ].forEach((linha) => {
      doc.text(linha, MARGIN_LEFT, y);
      y += 10;
    });

    // logo / imagem à direita
    let imgBottom = MARGIN_TOP;
    if (logoImg) {
      const box = 105;
      const { w, h } = fitBox(
        logoImg.naturalWidth,
        logoImg.naturalHeight,
        box,
        box,
      );
      const x = CONTENT_RIGHT - w;
      doc.addImage(logoImg, getImageFormat(logoImg.src), x, MARGIN_TOP, w, h);
      imgBottom = MARGIN_TOP + h;
    }

    return Math.max(y, imgBottom) + 28; // espaço antes do rótulo de via
  }

  // ---------------------------------------------------------------------------
  // Desenho — meta (Via / Título / Cliente / Datas)
  // ---------------------------------------------------------------------------

  function drawMeta(doc, invoiceData, y, viaLabel = "Original") {
    const {
      document_type,
      reference,
      issue_date,
      due_date,
      observation,
      client,
    } = invoiceData;

    doc.setFont("helvetica", "normal");
    doc.setFontSize(9);
    doc.setTextColor(...BLACK);
    // 🔧 antes era fixo "Original" — agora reflete a via atual (Original/Duplicado)
    doc.text(viaLabel, MARGIN_LEFT, y);
    y += 17;

    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    const docTitle = document_type === "PF" ? "Proforma" : "Factura";
    doc.text(`${docTitle} n.º ${reference || ""}`, MARGIN_LEFT, y);
    y += 20;

    const leftLabelX = MARGIN_LEFT;
    const leftValueX = MARGIN_LEFT + 72;
    const rightLabelX = MARGIN_LEFT + 260;
    const rightValueX = rightLabelX + 95;
    const rowGap = 13;

    doc.setFontSize(9);

    // ----- coluna esquerda -----
    let ly = y;
    doc.setFont("helvetica", "normal");
    doc.text("Cliente:", leftLabelX, ly);
    doc.setFont("helvetica", "bold");
    doc.text((client.name || "").toUpperCase(), leftValueX, ly);
    ly += rowGap;

    doc.setFont("helvetica", "normal");
    doc.text("Contribuinte:", leftLabelX, ly);
    doc.text(client.contributor || "-", leftValueX, ly);
    ly += rowGap;

    doc.text("Endereço:", leftLabelX, ly);
    const enderecoWidth = rightLabelX - leftValueX - 10;
    const enderecoLinhas = doc.splitTextToSize(
      client.address || "-",
      enderecoWidth,
    );
    doc.text(enderecoLinhas, leftValueX, ly);
    const leftBottom = ly + enderecoLinhas.length * 11;

    // ----- coluna direita -----
    let ry = y;
    doc.text("Data de emissão:", rightLabelX, ry);
    doc.text(dateBr(issue_date), rightValueX, ry);
    ry += rowGap;

    doc.text("Vencimento:", rightLabelX, ry);
    doc.text(dateBr(due_date), rightValueX, ry);
    ry += rowGap;

    doc.text("Observações:", rightLabelX, ry);
    const obsWidth = CONTENT_RIGHT - rightValueX;
    const obsLinhas = doc.splitTextToSize(observation || "-", obsWidth);
    doc.text(obsLinhas, rightValueX, ry);
    const rightBottom = ry + obsLinhas.length * 11;

    return Math.max(leftBottom, rightBottom) + 14;
  }

  // ---------------------------------------------------------------------------
  // Desenho — tabela de itens (com paginação manual)
  // ---------------------------------------------------------------------------

  function drawItemsTableHeader(doc, y) {
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.setTextColor(...GRAY_LABEL);
    doc.text("Código", COL_CODE_X, y);
    doc.text("Descrição", 100, y);
    doc.text("Preço Uni.", COL_PRECO_RIGHT_X, y, { align: "right" });
    doc.text("Qtd.", COL_QTD_CENTER_X, y, { align: "center" });
    doc.text("Taxa/IVA", COL_TAXA_CENTER_X, y, { align: "center" });
    doc.text("Desc.", COL_DESCPCT_CENTER_X, y, { align: "center" });
    doc.text("Total", COL_TOTAL_RIGHT_X, y, { align: "right" });
    doc.setTextColor(...BLACK);
    return y + 14;
  }

  function drawTopBorder(doc, y) {
    doc.setDrawColor(...GRAY);
    doc.setLineWidth(1.6);
    doc.line(MARGIN_LEFT, y, CONTENT_RIGHT, y);
  }

  function drawItemsTable(doc, invoiceData, y) {
    const { items, moneySymbol = "Kz", moneyPos = "right" } = invoiceData;
    const bottomLimit = PAGE_HEIGHT - MARGIN_BOTTOM - 90; // reserva espaço p/ sumário

    drawTopBorder(doc, y);
    y += 10;
    y = drawItemsTableHeader(doc, y);
    y += 5;

    items.forEach((it) => {
      // 🔧 limpa HTML/entidades antes de calcular a quebra de linha
      const nomeLimpo = stripHtml(it.name || it.description || "");
      const codigoLimpo = stripHtml(it.code || "");

      const descLinhas = doc.splitTextToSize(nomeLimpo, COL_DESC_WIDTH);
      const rowHeight = Math.max(14, descLinhas.length * 10 + 4);

      if (y + rowHeight > bottomLimit) {
        // fecha o bloco atual e continua numa nova página
        doc.setDrawColor(...GRAY);
        doc.setLineWidth(1.3);
        doc.line(MARGIN_LEFT, y, CONTENT_RIGHT, y);

        doc.addPage();
        y = MARGIN_TOP + 20;
        drawTopBorder(doc, y);
        y += 10;
        y = drawItemsTableHeader(doc, y);
        y += 5;
      }

      const { total } = calcItemTotal(it);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(8);
      doc.setTextColor(...BLACK);

      // 🔧 código e valores monetários usam drawFittedText: encolhem a fonte
      // automaticamente se não couberem na largura da coluna, em vez de
      // sobrepor a coluna vizinha (era o bug visto nos preços grandes)
      drawFittedText(
        doc,
        codigoLimpo,
        COL_CODE_X,
        y,
        COL_CODE_MAX_WIDTH,
        "left",
      );
      doc.text(descLinhas, COL_DESC_X, y);
      drawFittedText(
        doc,
        formatCurrency(it.unit_price, moneySymbol, moneyPos),
        COL_PRECO_RIGHT_X,
        y,
        COL_PRECO_MAX_WIDTH,
        "right",
      );
      doc.text(String(it.quantity), COL_QTD_CENTER_X, y, { align: "center" });
      doc.text(`${it.tax || 0}%`, COL_TAXA_CENTER_X, y, { align: "center" });
      doc.text(`${it.discount || 0}%`, COL_DESCPCT_CENTER_X, y, {
        align: "center",
      });
      drawFittedText(
        doc,
        formatCurrency(total, moneySymbol, moneyPos),
        COL_TOTAL_RIGHT_X,
        y,
        COL_TOTAL_MAX_WIDTH,
        "right",
      );

      y += rowHeight;
    });

    // barra grossa final do bloco de itens
    doc.setDrawColor(...GRAY);
    doc.setLineWidth(1.5);
    doc.line(MARGIN_LEFT, y, CONTENT_RIGHT, y);
    y += 18;

    return y;
  }

  // ---------------------------------------------------------------------------
  // Desenho — Dados fiscais/bancários + Sumário
  // ---------------------------------------------------------------------------

  function drawTotalsSection(doc, invoiceData, y) {
    const {
      vat_regime,
      iban,
      moneySymbol = "Kz",
      moneyPos = "right",
    } = invoiceData;

    const calcTotals = () => {
      let total_sum = 0,
        total_discount = 0,
        total_tax = 0;
      invoiceData.items.forEach((it) => {
        const { base, discount, tax } = calcItemTotal(it);
        total_sum += base;
        total_discount += discount;
        total_tax += tax;
      });
      return {
        total_sum,
        total_discount,
        total_tax,
        retention_value: 0,
        final_total: total_sum - total_discount + total_tax,
      };
    };
    const totals = invoiceData.totals || calcTotals();

    const leftX = MARGIN_LEFT;
    const leftWidth = 260;
    const rightX = MARGIN_LEFT + 300;
    const rightValueRightX = CONTENT_RIGHT;

    // ----- ESQUERDA: Dados fiscais e bancários -----
    let ly = y;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.setTextColor(...GRAY_LABEL);
    doc.text("Dados fiscais e bancários", leftX, ly);
    ly += 14;

    doc.setFont("helvetica", "normal");
    doc.setTextColor(...BLACK);
    doc.text(
      `Regime de IVA: ${vat_regime === "simplificado" ? "Regime Simplificado" : "Regime Geral"}`,
      leftX,
      ly,
    );
    ly += 12;

    const bensLinhas = doc.splitTextToSize(
      "Bens e serviços: Os bens e serviços foram colocados à disposição do adquirente na data do documento.",
      leftWidth,
    );
    doc.text(bensLinhas, leftX, ly);
    ly += bensLinhas.length * 10 + 2;

    doc.text(`Dados bancários: ${iban || "-"}`, leftX, ly);
    ly += 8;

    doc.setDrawColor(...GRAY);
    doc.setLineWidth(1);
    doc.line(leftX, ly, leftX + leftWidth, ly);
    ly += 4;

    // ----- DIREITA: Sumário -----
    let ry = y;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(8);
    doc.setTextColor(...GRAY_LABEL);
    doc.text("Sumário", rightX, ry);
    ry += 14;

    doc.setFont("helvetica", "normal");
    doc.setTextColor(...BLACK);

    const rows = [
      [
        "Total ilíquido:",
        formatCurrency(totals.total_sum, moneySymbol, moneyPos),
      ],
      [
        "Desconto:",
        formatCurrency(totals.total_discount, moneySymbol, moneyPos),
      ],
      [
        "Sem Imposto/IVA c Desc.:",
        formatCurrency(
          totals.total_sum - totals.total_discount,
          moneySymbol,
          moneyPos,
        ),
      ],
      ["Imposto/IVA:", formatCurrency(totals.total_tax, moneySymbol, moneyPos)],
      [
        "Retenção:",
        formatCurrency(totals.retention_value, moneySymbol, moneyPos),
      ],
    ];
    rows.forEach(([label, value]) => {
      doc.text(label, rightX, ry);
      doc.text(value, rightValueRightX, ry, { align: "right" });
      ry += 12;
    });

    // barra grossa acima do Total
    doc.setDrawColor(...GRAY);
    doc.setLineWidth(1.5);
    doc.line(rightX, ry, rightValueRightX, ry);
    ry += 15;

    doc.setFont("helvetica", "bold");
    doc.setFontSize(11);
    doc.text("Total:", rightX, ry);
    doc.text(
      formatCurrency(totals.final_total, moneySymbol, moneyPos),
      rightValueRightX,
      ry,
      { align: "right" },
    );

    return Math.max(ly, ry) + 20;
  }

  // ---------------------------------------------------------------------------
  // Desenho — rodapé (aplicado em todas as páginas, no final)
  // ---------------------------------------------------------------------------

  /**
   * 🔧 Corrigido: antes, o número da página só era desenhado quando NÃO havia
   * QR code (o QR "escondia" a numeração). Agora ambos aparecem sempre,
   * em posições que não se sobrepõem. `page`/`totalPages` passados aqui já
   * são relativos à VIA atual (ver gerarPdfFatura), não ao documento inteiro.
   */
  function drawFooter(doc, qrImg, qrDataUrl, page, totalPages) {
    const y = PAGE_HEIGHT - 32;

    doc.setFont("helvetica", "normal");
    doc.setFontSize(7);
    doc.setTextColor(...GRAY_FOOTER);
    doc.text("Powered By BXpert", MARGIN_LEFT, y);

    // número de página sempre visível, centralizado
    doc.text(`Página ${page} / ${totalPages}`, PAGE_WIDTH / 2, y, {
      align: "center",
    });

    if (qrImg) {
      const size = 58;
      const x = CONTENT_RIGHT - size;
      doc.addImage(
        qrImg,
        getImageFormat(qrDataUrl),
        x,
        y - size + 12,
        size,
        size,
      );
    }
    doc.setTextColor(...BLACK);
  }

  // ---------------------------------------------------------------------------
  // Uma via completa da fatura
  // ---------------------------------------------------------------------------

  function drawInvoicePage(doc, invoiceData, assets, viaLabel = "Original") {
    let y = drawCompanyHeader(doc, invoiceData.company, assets.logoImg);
    y = drawMeta(doc, invoiceData, y, viaLabel);
    y = drawItemsTable(doc, invoiceData, y);
    drawTotalsSection(doc, invoiceData, y);
  }

  // ---------------------------------------------------------------------------
  // Função principal
  // ---------------------------------------------------------------------------

  async function gerarPdfFatura(
    invoiceData,
    filename = "fatura.pdf",
    copies = 2,
  ) {
    try {
      // Garante que copies seja enviado para o backend
      const payload = {
        ...invoiceData,
        copies: Number(copies) || 1,
      };

      const response = await fetch(
        "https://www.api-sandibox.bxpert.co.ao/invoices/pdf",
        // "http://localhost:5301/invoices/pdf",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/pdf",
          },
          body: JSON.stringify(payload),
        },
      );

      // Se o backend retornar erro, tentar ler a mensagem JSON
      if (!response.ok) {
        let errorMessage = `Erro HTTP ${response.status}`;

        try {
          const errorData = await response.json();

          if (errorData?.message) {
            errorMessage = errorData.message;
          }

          if (errorData?.error) {
            errorMessage += `: ${errorData.error}`;
          }
        } catch (_) {
          // A resposta não era JSON
        }

        throw new Error(errorMessage);
      }

      // Recebe o PDF binário
      const blob = await response.blob();

      if (!blob || blob.size === 0) {
        throw new Error("O servidor devolveu um PDF vazio.");
      }

      // Cria URL temporária para o PDF
      const url = window.URL.createObjectURL(blob);

      // Cria link temporário
      const link = document.createElement("a");
      link.href = url;
      link.download = filename;

      document.body.appendChild(link);
      link.click();

      // Limpeza
      link.remove();
      window.URL.revokeObjectURL(url);

      return true;
    } catch (error) {
      console.error("Erro ao gerar PDF da factura:", error);

      alert("Não foi possível gerar o PDF da factura.\n\n" + error.message);

      return false;
    }
  }

  // ---------------------------------------------------------------------------
  // Exemplo de uso (substitui o antigo #btnPdf / #generatePdf)
  // ---------------------------------------------------------------------------
  //

  $("#btnPdf, #generatePdf").on("click", async function () {
    try {
      // Monta os dados da factura
      const invoiceData = await prepareInvoiceData(currentInvoice, {
        logoBaseUrl: "https://SEU-DOMINIO/sistema/assets/img/companies/",

        qrBaseUrl: "https://app.bxpert.co.ao/sistema/invoice_public.php?id=",
      });

      // Número de cópias
      const copies = Number(currentInvoice.copies) || 2;

      // Nome do arquivo
      const filename = `${currentInvoice.reference || "fatura"}.pdf`;

      // Envia para o Node.js
      await gerarPdfFatura(invoiceData, filename, copies);
    } catch (error) {
      console.error("Erro ao preparar factura para PDF:", error);

      alert("Erro ao preparar a factura:\n\n" + error.message);
    }
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
