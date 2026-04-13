$(document).ready(function () {
  // Configuração do DataTable
  $("#invoicesTable").DataTable({
    ajax: {
      url: "invoices/ajax/fetch_invoices.php",
      dataSrc: "",
    },
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json",
      lengthMenu: "Mostrar  _MENU_",
    },
    columns: [
      {
        data: null,
        orderable: false,
        render: function (data, type, row) {
          return `
                    <div class="d-flex flex-wrap justify-content-evenly">
                        <input 
                            name="${row.id}" 
                            data-id="${row.id}" 
                            type="checkbox" 
                            value="${row.id}">
                        <div 
                            class="px-2 icon-statusFatura" 
                            style="background-color:${row.color};color:${row.text_color};font-weight:900; cursor: default;" 
                            data-bs-title="${row.status_invoice}" 
                            data-bs-toggle="tooltip" data-bs-placement="top">
                            ${row.status_invoice.trim().charAt(0)}
                        </div>
                    </div>
                    `;
        },
      },
      { data: "codigo" },
      { data: "cliente" },
      {
        data: "issue_date",
        render: function (data) {
          const date = new Date(data);
          return date.toLocaleDateString("pt-BR", { timeZone: "UTC" });
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          const hoje = new Date();
          const date = new Date(row.due_date);
          return `<span style="color: ${date < hoje ? "red" : "inherit"};" ${date < hoje && row.status_invoice == "Rascunho" ? ' data-bs-title="Aguardando Finalizar Fatura"  data-bs-toggle="tooltip"  ' : ""}>${date.toLocaleDateString("pt-BR", { timeZone: "UTC" })}</span>`;
        },
      },
      { data: "currency" },
      {
        data: null,
        render: function (data, type, row) {
          return formatCurrency(row.final_total, row.symbol, row.position);
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          let btns = `<button  data-bs-toggle="tooltip" title="Ver detalhes" class="btn text-primary btn-sm" onclick="window.location.href='invoice.php?id=${data.issue_date.replace("-", "").replace("-", "")}/${data.company_id}/${data.id}'; event.stopPropagation();"><i class="bi bi-card-list"></i></button>`;
          if (row.status_invoice === "Rascunho") {
            btns += ` <button  data-bs-toggle="tooltip" title="Editar" class="btn text-danger btn-sm ms-1" onclick="window.location.href='create_invoices.php?edit_id=${row.id}'; event.stopPropagation();"><i class="bi bi-pencil"></i></button>`;
            btns += ` <button  data-bs-toggle="tooltip" title="Eliminar" class="btn text-danger btn-sm ms-1" onclick="deleteInvoice(${row.id}, ${row.company_id}); event.stopPropagation();"><i class="bi bi-trash"></i></button>`;
          } else {
            btns += ` <button  data-bs-toggle="tooltip" title="Baixar PDF" class="btn text-success btn-sm ms-1" onclick="downloadPDF(${row.id}); event.stopPropagation();"><i class="bi bi-file-earmark-pdf"></i></button>`;
          }
          return btns;
        },
      },
    ],
    paging: true,
    searching: true,
    ordering: true,
    responsive: true,
    destroy: true,
    pageLength: 25,
    lengthMenu: [
      [10, 25, 50, 100, -1],
      ["10 linhas", "25 linhas", "50 linhas", "100 linhas", "Tudo"],
    ],
    order: [[1, "desc"]],
    rowCallback: function (row, data) {
      $(row).css("cursor", "pointer");
      $(row).on("click", function (event) {
        const cellIndex = $(event.target).closest("td").index();

        if (cellIndex === 0) {
          const checkbox = $(row).find('input[type="checkbox"]');
          checkbox.prop("checked", !checkbox.prop("checked"));
        } else if (cellIndex !== 7) {
          window.location.href = `invoice.php?id=${data.issue_date.replace("-", "").replace("-", "")}/${data.company_id}/${data.id}`;
        }
      });
    },

    drawCallback: function () {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        bootstrap.Tooltip.getOrCreateInstance(el);
      });
    },
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
