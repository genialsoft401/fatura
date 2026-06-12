$(document).ready(function () {
  // Configuração do DataTable

  const table = $("#invoicesTable").DataTable({
    ajax: {
      url: "invoices/ajax/fetch_invoices.php",
      type: "GET",
      dataType: "json",

      dataSrc: function (json) {
        if (Array.isArray(json)) return json;

        if (json?.data && Array.isArray(json.data)) {
          return json.data;
        }

        if (json?.invoices && Array.isArray(json.invoices)) {
          return json.invoices;
        }

        console.error("Formato inválido:", json);
        return [];
      },
    },

    language: {
      lengthMenu: "Mostrar _MENU_ registos",
      search: "",
      searchPlaceholder: "Pesquisar faturas...",
      info: "Mostrando _START_ a _END_ de _TOTAL_",
      infoEmpty: "Nenhum registo encontrado",
      emptyTable: "Nenhuma fatura encontrada",
      zeroRecords: "Nenhum resultado encontrado",
      paginate: {
        first: "Primeira",
        last: "Última",
        next: "›",
        previous: "‹",
      },
    },
    order: [[3, "desc"]], // coluna da data

    columns: [
      {
        data: null,
        orderable: true,
        searchable: false,
        width: "90px",

        render: function (data, type, row) {
          const status = row.status_invoice || "?";

          return `
            <div class="d-flex align-items-center gap-3">

              <input
                type="checkbox"
                class="invoice-check"
                data-id="${row.id}"
                value="${row.id}"
              >

              <div
                class="icon-statusFatura p-2 py-1"
                data-status="${status}"
                style="
                  background:${row.color || "#000"};
                  color:${row.text_color || "#fff"};
                "
                data-bs-toggle="tooltip"
                data-bs-title="${status}"
              >
                ${status.charAt(0).toUpperCase()}
              </div>

            </div>
          `;
        },
      },

      {
        data: "codigo",
        defaultContent: "-",
      },

      {
        data: "cliente",
        defaultContent: "-",
      },

      {
        data: "issue_date",
        render: function (data, type) {
          if (!data) return "-";

          if (type === "sort") {
            return data;
          }

          return new Date(data).toLocaleDateString("pt-BR");
        },
      },

      {
        data: "due_date",
        render: function (data, type) {
          if (!data) return "-";

          if (type === "sort") {
            return data;
          }

          const today = new Date();
          today.setHours(0, 0, 0, 0);

          const due = new Date(data);
          due.setHours(0, 0, 0, 0);

          return `
          <span class="${due < today ? "text-danger" : ""}">
            ${due.toLocaleDateString("pt-BR")}
          </span>
        `;
        },
      },

      {
        data: "currency",
        defaultContent: "-",
      },

      {
        data: null,
        render: function (data, type, row) {
          return formatCurrency(
            Number(row.final_total || 0),
            row.symbol || "",
            row.position || "left",
          );
        },
      },

      {
        data: null,
        orderable: false,
        searchable: false,

        render: function (data, type, row) {
          const id = row.id;
          const company = row.company_id;

          const invoiceUrl =
            `invoice.php?id=` +
            `${String(row.issue_date || "").replaceAll("-", "")}` +
            `/${company}/${id}`;

          let html = `
          <button
            class="btn btn-action text-primary"
            title="Ver"
            onclick="event.stopPropagation();window.location.href='${invoiceUrl}'"
          >
            <i class="bi bi-card-list"></i>
          </button>
        `;

          if ((row.status_invoice || "").toLowerCase() === "rascunho") {
            html += `
            <button
              class="btn btn-sm text-warning ms-1"
              title="Editar"
              onclick="event.stopPropagation(); window.location.href='create_invoices.php?edit_id=${id}'"
            >
              <i class="bi bi-pencil"></i>
            </button>

            <button
              class="btn btn-sm text-danger ms-1"
              title="Eliminar"
              onclick="event.stopPropagation(); deleteInvoice(${id}, ${company})"
            >
              <i class="bi bi-trash"></i>
            </button>

          `;
          } else {
            html += `
            <button
              class="btn btn-sm text-success ms-1"
              title="PDF"
              onclick="event.stopPropagation(); downloadPDF(${id})"
            >
              <i class="bi bi-file-earmark-pdf"></i>
            </button>
          `;
          }

          return `
          <div class="d-flex justify-content-end gap-2">
            ${html}
          </div>
        `;
        },
      },
    ],

    paging: true,
    searching: true,
    ordering: true,
    responsive: true,
    destroy: true,

    pageLength: 25,

    order: [[1, "desc"]],

    createdRow: function (row, data) {
      $(row)
        .addClass("invoice-row")
        .attr("data-id", data.id)
        .attr("data-cliente", data.cliente || "")
        .attr("data-status", data.status_invoice || "")
        .attr("data-issue-date", data.issue_date || "");
    },

    rowCallback: function (row, data) {
      $(row)
        .off("click")
        .on("click", function (e) {
          if (
            $(e.target).closest("button").length ||
            $(e.target).closest("input").length
          ) {
            return;
          }

          const url =
            `invoice.php?id=` +
            `${String(data.issue_date || "").replaceAll("-", "")}` +
            `/${data.company_id}/${data.id}`;

          window.location.href = url;
        });
    },

    drawCallback: function () {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        bootstrap.Tooltip.getOrCreateInstance(el);
      });
    },
  });

  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    if (settings.nTable.id !== "invoicesTable") {
      return true;
    }

    const row = table.row(dataIndex).node();

    if (!row) {
      return true;
    }

    const cliente = ($(row).attr("data-cliente") || "").toLowerCase();

    const status = ($(row).attr("data-status") || "").toLowerCase();

    const issueDate = $(row).attr("data-issue-date");

    // =====================
    // CLIENTE
    // =====================

    const clienteFiltro = ($("#filterClient").val() || "").toLowerCase().trim();

    if (clienteFiltro && !cliente.includes(clienteFiltro)) {
      return false;
    }

    // =====================
    // STATUS
    // =====================

    const statusFiltro = ($("#filterStatus").val() || "").toLowerCase().trim();

    if (statusFiltro && status !== statusFiltro) {
      return false;
    }

    // =====================
    // DATA
    // =====================

    if (issueDate) {
      const current = new Date(issueDate);

      const start = $("#filterStartDate").val();
      const end = $("#filterEndDate").val();

      if (start) {
        const startDate = new Date(start);

        if (current < startDate) {
          return false;
        }
      }

      if (end) {
        const endDate = new Date(end);
        endDate.setHours(23, 59, 59, 999);

        if (current > endDate) {
          return false;
        }
      }
    }

    return true;
  });

  $("#filterClient").on("input", function () {
    table.draw();
  });

  $("#filterStatus").on("change", function () {
    table.draw();
  });

  $("#filterStartDate").on("change", function () {
    table.draw();
  });

  $("#filterEndDate").on("change", function () {
    table.draw();
  });

  $("#btnClearFilters").on("click", function () {
    $("#filterClient").val("");
    $("#filterStatus").val("");
    $("#filterStartDate").val("");
    $("#filterEndDate").val("");

    table.search("");
    table.columns().search("");

    table.draw();
  });

  // Evento único para toda a tabela
  $("#invoicesTable tbody").on("click", "tr", function (e) {
    const $target = $(e.target);

    if ($target.closest("button").length || $target.closest("input").length) {
      return;
    }

    const row = table.row(this).data();

    if (!row) return;

    const url =
      `invoice.php?id=` +
      `${String(row.issue_date || "").replaceAll("-", "")}` +
      `/${row.company_id}/${row.id}`;

    window.location.href = url;
  });

  $("#exportCsv").on("click", function () {
    window.location.href = "export_csv.php";
  });
});

function downloadPDF(invoiceId) {
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

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      // Adicionar logo com proporção ajustada
      if (response.logo_url) {
        const img = new Image();
        img.src = `assets/img/companies/${response.logo_url}`;
        doc.addImage(img, "PNG", 10, 10, 50, 15); // Largura e altura ajustada
      }

      // Informações da Empresa
      let currentY = 10;
      doc.setFontSize(12);
      doc.setFont("helvetica", "bold");
      doc.text(response.company_name, 70, currentY);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(10);
      currentY += 5;
      doc.text(response.company_address.replace(/\n/g, " "), 70, currentY);
      currentY += 5;
      doc.text(`Tel: ${response.company_phone}`, 70, currentY);
      currentY += 5;
      doc.text(`E-mail: ${response.company_email}`, 70, currentY);
      currentY += 5;
      doc.text(`Contribuinte: ${response.registration_number}`, 70, currentY);

      // Função para gerar uma hash aleatória
      function generateRandomHash(length = 70) {
        const characters =
          "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let hash = "";
        for (let i = 0; i < length; i++) {
          hash += characters.charAt(
            Math.floor(Math.random() * characters.length),
          );
        }
        return hash;
      }
      // QR Code posicionado sem sobrepor texto
      const qrSize = 40; // Tamanho do QR Code
      const qrX = 150; // Posição no lado direito
      const qrY = Math.max(currentY - 15, 35); // Alinha o QR Code abaixo do texto
      const qrBase64 = generateQRCode(
        "../public/invoice_public.php?id=" +
          generateRandomHash() +
          "_" +
          response.company_id +
          "/" +
          response.id,
      );
      doc.addImage(qrBase64, "PNG", qrX, qrY, qrSize, qrSize);

      // Informações do Cliente
      currentY = Math.max(currentY + 10, qrY - 50); // Garante que o texto fique abaixo do QR Code
      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text(`Exmo.(s) Sr.(s):`, 10, currentY);
      doc.setFont("helvetica", "normal");
      currentY += 5;
      doc.text(response.client_name, 10, currentY);
      currentY += 5;
      doc.text(response.client_address.replace(/\n/g, " "), 10, currentY);
      currentY += 5;
      doc.text(`Contribuinte: ${response.client_contributor}`, 10, currentY);
      // Função para formatar a data no formato 'DD Mês YYYY'
      const formatDate = (date) => {
        return new Intl.DateTimeFormat("pt-BR", {
          day: "2-digit",
          month: "short",
          year: "numeric",
        })
          .format(date)
          .replace(/ de /g, " ") // Remove os "de"
          .replace(/\.$/, "") // Remove o ponto no final do mês
          .replace(/\b[a-z]/, (char) => char.toUpperCase()); // Deixa a primeira letra do mês maiúscula
      };
      // Converte as datas para objetos Date e calcula a data de vencimento
      const issueDate = new Date(response.issue_date);
      const dueDateObj = new Date(issueDate);
      dueDateObj.setDate(issueDate.getDate() + response.due_date);

      // Formata as datas no formato 'DD Mês YYYY'
      const issueDateFormatted = formatDate(issueDate);
      const dueDateFormatted = formatDate(dueDateObj);

      // Detalhes da Fatura
      currentY += 10;
      doc.setFont("helvetica", "bold");
      doc.text(`Fatura n.º ${response.codigo}`, 10, currentY);
      doc.setFont("helvetica", "normal");
      currentY += 5;
      doc.text(`Data de emissão: ${issueDateFormatted}`, 10, currentY);
      currentY += 5;
      doc.text(`Vencimento: ${dueDateFormatted}`, 10, currentY);
      currentY += 5;
      doc.text(
        `Referência: ${response.reference || "Não especificada"}`,
        10,
        currentY,
      );

      // Linha divisória
      doc.setDrawColor(400, 200, 200);
      doc.line(10, currentY + 5, 200, currentY + 5);

      // Tabela de Itens
      doc.autoTable({
        startY: currentY + 10,
        margin: { left: 10 },
        pageBreak: "auto",
        head: [
          [
            "Código",
            "Descrição",
            "Preço Unitário",
            "Qtd",
            "Taxa/IVA %",
            "Desc. %",
            "Total",
          ],
        ],
        body: response.items.map((item) => [
          item.code,
          item.description,
          formatCurrency(
            item.unit_price,
            response.company_symbol,
            response.company_position,
          ),
          item.quantity,
          item.tax,
          item.discount,
          formatCurrency(
            item.unit_price * item.quantity,
            response.company_symbol,
            response.company_position,
          ),
        ]),
        theme: "striped",
        styles: { fontSize: 10, halign: "center" },
        headStyles: { fillColor: [100, 100, 255], textColor: 255 },
        alternateRowStyles: { fillColor: [240, 240, 240] },
        didDrawPage: function (data) {
          currentY = data.cursor.y; // Atualiza a posição Y após o final da tabela
        },
      });

      // Resumo

      // Tabela de Taxas com Retenção
      const taxDetails = response.tax_details.map((tax) => [
        `${tax.tax_rate}%`,
        formatCurrency(
          tax.tax_base,
          response.company_symbol,
          response.company_position,
        ),
        formatCurrency(
          tax.tax_value,
          response.company_symbol,
          response.company_position,
        ),
      ]);

      // Adiciona a retenção como última linha, caso exista
      if (response.tax_details[0]?.retention_rate) {
        taxDetails.push([
          `Retenção (${response.tax_details[0].retention_rate}%)`,
          formatCurrency(
            response.tax_details[0].total_sum,
            response.company_symbol,
            response.company_position,
          ),
          formatCurrency(
            response.tax_details[0].retention_value,
            response.company_symbol,
            response.company_position,
          ),
        ]);
      }

      doc.autoTable({
        startY: doc.lastAutoTable.finalY + 10,
        margin: { left: 10 },
        pageBreak: "auto",
        head: [["Taxa/Imposto", "Base", "Valor"]],
        body: taxDetails,
        theme: "grid",
        styles: { fontSize: 10, halign: "center" },
        headStyles: { fillColor: [100, 100, 255], textColor: 255 },
      });

      // Ajustar Resumo para incluir Retenção, caso exista
      const resumoBody = [
        [
          "Total líquido",
          formatCurrency(
            response.total_sum,
            response.company_symbol,
            response.company_position,
          ),
        ],
        [
          "Desconto",
          formatCurrency(
            response.total_discount,
            response.company_symbol,
            response.company_position,
          ),
        ],
        [
          "Sem Impostos/IVA c/ Desc.",
          formatCurrency(
            response.total_sum - response.total_discount,
            response.company_symbol,
            response.company_position,
          ),
        ],
        [
          "Imposto/IVA:",
          formatCurrency(
            response.total_tax,
            response.company_symbol,
            response.company_position,
          ),
        ],
      ];

      // Adiciona retenção ao resumo, se existir

      resumoBody.push([
        "Retenção",
        formatCurrency(
          response.retention_value,
          response.company_symbol,
          response.company_position,
        ),
      ]);

      // Adiciona o Total Geral ao final do resumo
      resumoBody.push([
        "Total Geral:",
        formatCurrency(
          response.final_total,
          response.company_symbol,
          response.company_position,
        ),
      ]);

      if (response.currency_company !== response.currency_items) {
        resumoBody.push([
          "Total Convertido:",
          `${formatCurrency(response.converted_total, response.symbol, response.position)} (${response.currency_items})`,
        ]);
      }

      doc.autoTable({
        startY: doc.lastAutoTable.finalY + 10,
        margin: { left: 10 },
        pageBreak: "auto",
        head: [["Descrição", "Valor"]],
        body: resumoBody,
        theme: "grid",
        styles: { fontSize: 10, halign: "center" },
        headStyles: { fillColor: [100, 100, 255], textColor: 255 },
      });

      // Verifica o espaço após a tabela para adicionar Observações
      if (currentY + 30 > doc.internal.pageSize.height) {
        doc.addPage();
        currentY = 20; // Reinicia o Y na nova página
      }

      // Observações com verificação de espaço na página
      doc.setFontSize(10);
      doc.setFont("helvetica", "bold");
      doc.text("Observações:", 10, doc.lastAutoTable.finalY + 20);

      // Verifica se há espaço suficiente na página atual
      const pageHeight = doc.internal.pageSize.height; // Altura da página
      currentY = doc.lastAutoTable.finalY + 25;
      const textHeight =
        doc.splitTextToSize(
          response.observation || "Nenhuma observação adicionada.",
          180,
        ).length * 10;

      if (currentY + textHeight > pageHeight) {
        doc.addPage(); // Adiciona uma nova página
        currentY = 20; // Reinicia o Y na nova página
        doc.text("Observações (continuação):", 10, currentY);
        currentY += 5;
      }

      // Adiciona o texto das observações
      doc.setFont("helvetica", "normal");
      doc.text(
        doc.splitTextToSize(
          response.observation || "Nenhuma observação adicionada.",
          180,
        ),
        10,
        currentY,
      );

      // Salvar PDF
      doc.save(`Fatura_${response.company_name}_${response.codigo}.pdf`);
      // Renderizar o PDF na tela
      // const pdfData = doc.output("datauristring");
      // const iframe = `<iframe width="100%" height="600px" src="${pdfData}"></iframe>`;
      // document.body.innerHTML = iframe;
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
}

let invoiceToDelete = null;
let invoice_companyId = null;

const deleteInvoice = (id, companyId) => {
  invoiceToDelete = id;
  invoice_companyId = companyId;

  const modal = new bootstrap.Modal(document.getElementById("deleteModal"));
  modal.show();
};

// Confirma Delete
$("#confirmDelete")
  .off("click")
  .on("click", function () {
    if (!invoiceToDelete) return;

    $.ajax({
      url: "invoices/ajax/delete_invoice.php",
      type: "POST",
      data: { invoice_id: invoiceToDelete, company_id: invoice_companyId },

      success: function (response) {
        const modalEl = document.getElementById("deleteModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Fatura eliminada!",
            timer: 1500,
            showConfirmButton: false,
          });
          $("#invoicesTable").DataTable().ajax.reload();
        } else {
          Swal.fire({
            icon: "error",
            title: "Erro ao eliminar!",
            timer: 1500,
            showConfirmButton: false,
          });
        }
      },

      error: function () {
        alert("Erro na requisição. Tente novamente.");
      },
    });
  });

// HTML Invoice
function renderInvoiceHTML(data) {
  const html = `
    <div>
      <h2>${data.company_name}</h2>
      <p>${data.company_address}</p>
      <p>Tel: ${data.company_phone}</p>

      <hr>

      <h3>Fatura nº ${data.codigo}</h3>
      <p>Cliente: ${data.client_name}</p>

      <table border="1" width="100%" cellspacing="0" cellpadding="5">
        <thead>
          <tr>
            <th>Descrição</th>
            <th>Qtd</th>
            <th>Preço</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          ${data.items
            .map(
              (item) => `
              <tr>
                <td>${item.description}</td>
                <td>${item.quantity}</td>
                <td>${item.unit_price}</td>
                <td>${item.unit_price * item.quantity}</td>
              </tr>
            `,
            )
            .join("")}
        </tbody>
      </table>

      <h3>Total: ${data.final_total}</h3>
    </div>
  `;

  $("#fatura-container").html(html);
}

$("#selectAll").on("click", function () {
  const isChecked = $(this).is(":checked");
  $('#invoicesTable tbody input[type="checkbox"]').prop("checked", isChecked);
});

$("#invoicesTable tbody").on("change", 'input[type="checkbox"]', function () {
  const totalCheckboxes = $(
    '#invoicesTable tbody input[type="checkbox"]',
  ).length;
  const checkedCheckboxes = $(
    '#invoicesTable tbody input[type="checkbox"]:checked',
  ).length;

  $("#selectAll").prop("checked", totalCheckboxes === checkedCheckboxes);
});

function exportFile(format) {
  const preloader = document.getElementById("preloader");
  const progressBar = document.getElementById("progressBar");

  // Exibe o preloader
  preloader.style.display = "block";
  progressBar.style.width = "0%";

  // Inicia o monitoramento do progresso
  let checkProgress = setInterval(() => {
    fetch(`invoices/ajax/faturas_export.php?status=1`)
      .then((res) => res.json())
      .then((data) => {
        progressBar.style.width = data.progress + "%";

        if (data.progress >= 100) {
          clearInterval(checkProgress);
          setTimeout(() => {
            preloader.style.display = "none";
          }, 500);
        }
      });
  }, 1000);

  // Aguarda um pequeno tempo para garantir que o progresso começou
  setTimeout(() => {
    window.location.href = `invoices/ajax/faturas_export.php?formato=${format}`;
  }, 2000);
}
