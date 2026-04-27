$(document).ready(function () {
  $("#createStock").on("click", function () {
    const icones = [
      "box",
      "gear",
      "tools",
      "cpu",
      "basket",
      "truck",
      "clipboard",
      "calendar",
      "archive",
      "building",
      "layers",
      "boxes",
      "bag-check",
      "cart",
      "cart4",
      "door-closed",
      "door-open",
      "house",
      "house-door",
      "pin-map",
      "truck-front",
      "shop",
      "shop-window",
      "briefcase",
      "inboxes",
      "folder",
      "grid-3x3-gap",
      "globe2",
      "clipboard-data",
      "clipboard-check",
      "clipboard-plus",
      "bag-fill",
      "tags",
      "safe",
      "battery-full",
      "pencil-square",
      "camera-video",
      "cloud",
      "cloud-upload",
      "cloud-check",
      "box-seam",
      "map",
      "map-fill",
      "pin",
      "pin-angle",
    ];

    Swal.fire({
      title: "Criar Novo Stock",
      width: "800px",
      html: `<div class="row g-3">
  <!-- Nome e Cor -->
  <div class="col-md-8">
    <label for="stockName" class="form-label fw-semibold">Nome do Stock</label>
    <input type="text" id="stockName" class="form-control" placeholder="Ex: Almoxarifado Central">
  </div>
  <div class="col-md-4">
    <label for="stockColor" class="form-label fw-semibold">Cor do Stock</label>
    <input type="color" id="stockColor" class="form-control form-control-color w-100" value="#4e73df">
  </div>

  <!-- Descrição -->
  <div class="col-12">
    <label for="stockDesc" class="form-label fw-semibold">Descrição</label>
    <textarea id="stockDesc" class="form-control" rows="2" placeholder="Ex: Galpão Norte, Setor 2..."></textarea>
  </div>

  <hr class="m-2">

  <!-- Ícones -->
  <div class="col-12">
    <label class="form-label fw-bold">Ícone do Stock:</label>
    <div class="d-flex flex-wrap gap-2" id="iconOptions">
      ${icones.map((i) => `<div class="icon-box" data-icon="${i}"><i class="bi bi-${i}"></i></div>`).join("")}
    </div>
  </div>

  <hr class="m-2">

  <!-- Localização Detalhada -->
  <div class="col-12 fw-bold mb-1">Localização</div>

  <div class="col-md-8">
    <input type="text" id="address" class="form-control" placeholder="Rua">
  </div>
  <div class="col-md-4">
    <input type="text" id="address_number" class="form-control" placeholder="Número">
  </div>

  <div class="col-md-6">
    <input type="text" id="neighborhood" class="form-control" placeholder="Bairro/Subdistrito">
  </div>
  <div class="col-md-6">
    <input type="text" id="city" class="form-control" placeholder="Cidade / Vila / Aldeia">
  </div>

  <div class="col-md-6">
    <input type="text" id="state" class="form-control" placeholder="Estado / Província">
  </div>
  <div class="col-md-6">
    <input type="text" id="county" class="form-control" placeholder="Município (county)">
  </div>

  <div class="col-md-6">
    <input type="hidden" id="state_district" class="form-control" placeholder="Distrito (state_district)">
  </div>
  <div class="col-md-6">
    <input type="hidden" id="region" class="form-control" placeholder="Região (region)">
  </div>
 

  <!-- Coords -->
  <div class="col-md-6">
    <input type="text" id="latitude" class="form-control" placeholder="Latitude" readonly>
  </div>
  <div class="col-md-6">
    <input type="text" id="longitude" class="form-control" placeholder="Longitude" readonly>
  </div>

  <!-- Descrição e busca -->
  <div class="col-12">
    <input type="text" id="location_detail" class="form-control" placeholder="Digite o endereço aqui para pesquisar">
  </div>
  
    <input type="hidden" id="zip_code">
    <input type="hidden" id="country">
    <input type="hidden" id="continent">
    <input type="hidden" id="iso_region_code">
    <input type="hidden" id="osm_type">
    <input type="hidden" id="osm_id">
    <input type="hidden" id="boundingbox">
    <input type="hidden" id="display_name">
    <input type="hidden" id="place_type">
    <input type="hidden" id="place_class">


  <div class="col-12">
    <button type="button" id="searchAddress" class="btn btn-sm btn-outline-primary w-100">
      <i class="bi bi-search me-1"></i> Buscar pelo Endereço
    </button>
  </div>

  <!-- Mapa -->
  <div class="col-12 mt-2">
    <div id="mapPreview" class="rounded border" style="height: 200px;"></div>
  </div>
</div>

`,

      showCancelButton: true,
      confirmButtonText: "Salvar",
      cancelButtonText: "Cancelar",
      didOpen: () => {
        let map, marker;

        // seleção de ícone
        $(document)
          .off("click", ".icon-box")
          .on("click", ".icon-box", function () {
            $(".icon-box").removeClass("selected");
            $(this).addClass("selected");
          });

        // inicializa o mapa após o DOM da modal estar disponível
        setTimeout(() => {
          const mapEl = document.getElementById("mapPreview");
          if (mapEl) {
            map = L.map(mapEl).setView([-8.838333, 13.234444], 12);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
              attribution: "&copy; OpenStreetMap contributors",
            }).addTo(map);

            map.on("click", function (e) {
              const lat = e.latlng.lat.toFixed(6);
              const lng = e.latlng.lng.toFixed(6);

              $("#latitude").val(lat);
              $("#longitude").val(lng);

              // Atualiza ou adiciona o marcador
              if (marker) {
                marker.setLatLng(e.latlng);
              } else {
                marker = L.marker(e.latlng).addTo(map);
              }

              // Faz busca reversa do endereço (geocoding reverso)
              preencherCamposEndereco(lat, lng);
            });
          }
        }, 250);

        function preencherCamposEndereco(lat, lng) {
          fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
          )
            .then((res) => res.json())
            .then((data) => {
              const addr = data.address || {};

              // Dados básicos
              const rua = addr.road || "";
              const numero = addr.house_number || "";
              const bairro = addr.neighbourhood || addr.suburb || "";
              const cidade = addr.city || addr.town || addr.village || "";
              const estado = addr.state || "";
              const municipio = addr.county || "";
              const cep = addr.postcode || "";
              const pais = addr.country || "";
              const codigo_pais = addr.country_code || "";

              // Dados adicionais
              const distrito = addr.state_district || "";
              const regiao = addr.region || "";
              const subdistrito = addr.suburb || "";
              const iso_region_code = addr["ISO3166-2-lvl4"] || "";
              const continente = addr.continent || "";

              // Extras do objeto principal
              const nome_exibicao = data.display_name || "";
              const tipo_lugar = data.type || "";
              const classificacao = data.class || "";
              const limite = Array.isArray(data.boundingbox)
                ? data.boundingbox.join(",")
                : "";
              const osm_type = data.osm_type || "";
              const osm_id = data.osm_id || "";

              // Preenche os campos visuais
              $("#address").val(rua);
              $("#address_number").val(numero);
              $("#neighborhood").val(bairro);
              $("#city").val(cidade);
              $("#state").val(estado);
              $("#county").val(municipio);
              $("#country").val(pais);
              $("#zip_code").val(cep);
              $("#latitude").val(lat);
              $("#longitude").val(lng);
              $("#location_detail").val(nome_exibicao);

              $("#state_district").val(distrito);
              $("#region").val(regiao);
              $("#continent").val(continente);
              $("#iso_region_code").val(iso_region_code);

              // Preenche os hidden
              $("#osm_type").val(osm_type);
              $("#osm_id").val(osm_id);
              $("#boundingbox").val(limite);
              $("#display_name").val(nome_exibicao);
              $("#place_type").val(tipo_lugar);
              $("#place_class").val(classificacao);
            });
        }

        // busca endereço via Nominatim
        $("#searchAddress").on("click", function () {
          const address = $("#address").val().trim();
          if (!address) return;

          fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
              address,
            )}`,
          )
            .then((res) => res.json())
            .then((data) => {
              if (!data.length) {
                toastr.warning("Endereço não encontrado!");
                return;
              }

              const { lat, lon } = data[0];
              const latLng = [parseFloat(lat), parseFloat(lon)];

              $("#latitude").val(lat);
              $("#longitude").val(lon);

              map.setView(latLng, 16);

              if (marker) {
                marker.setLatLng(latLng);
              } else {
                marker = L.marker(latLng).addTo(map);
              }
              preencherCamposEndereco(lat, lon);
            });
        });
      },
      preConfirm: () => {
        const name = $("#stockName").val().trim();
        const desc = $("#stockDesc").val().trim();
        const color = $("#stockColor").val();
        const icon = $(".icon-box.selected").data("icon") || "box";
        const address = $("#address").val().trim();
        const latitude = $("#latitude").val().trim();
        const longitude = $("#longitude").val().trim();

        if (!name) {
          Swal.showValidationMessage("O nome do Stock é obrigatório.");
          return false;
        }

        return { name, desc, color, icon, address, latitude, longitude };
      },
    }).then((result) => {
      if (result.isConfirmed) {
        const data = result.value;
        $.post(
          "stock/ajax/stock_controller.php",
          {
            action: "create",
            name: $("#stockName").val().trim(),
            description: $("#stockDesc").val().trim(),
            color: $("#stockColor").val(),
            icon: $(".icon-box.selected").data("icon") || "box",
            location_type: "interna", // ou pega de um select se tiver
            location_detail: $("#location_detail").val(),

            display_name: $("#display_name").val(),
            address: $("#address").val(),
            address_number: $("#address_number").val(),
            neighborhood: $("#neighborhood").val(),
            city: $("#city").val(),
            county: $("#county").val(),
            state: $("#state").val(),
            state_district: $("#state_district").val(),
            region: $("#region").val(),
            country: $("#country").val(),
            continent: $("#continent").val(),
            iso_region_code: $("#iso_region_code").val(),
            zip_code: $("#zip_code").val(),

            latitude: $("#latitude").val(),
            longitude: $("#longitude").val(),

            osm_type: $("#osm_type").val(),
            osm_id: $("#osm_id").val(),
            boundingbox: $("#boundingbox").val(),
            place_class: $("#place_class").val(),
            place_type: $("#place_type").val(),
          },
          function (res) {
            if (res.success) {
              Swal.fire("Stock criado com sucesso!", "", "success");
              carregarEstoques();
            } else {
              Swal.fire("Erro ao criar o stock.", "", "error");
            }
          },
          "json",
        );
      }
    });
  });
});

function abrirEstoque(id) {
  // Mostra o overlay e o loader antes de redirecionar
  document.getElementById("overlay-preloader").style.display = "flex";

  // Dá um pequeno tempo pra mostrar o loader antes do redirect
  setTimeout(() => {
    window.location.href = `stock_view.php?id=${id}`;
  }, 500);
}

function editarEstoque(id) {
  $.get(
    "stock/ajax/stock_controller.php",
    { action: "get", id },
    function (res) {
      if (!res.success) {
        Swal.fire("Erro ao carregar dados do stock", "", "error");
        return;
      }

      // Preenche os campos e reabre a modal
      const estoque = res.estoque;

      // Chama a mesma função de criação, mas muda o título e preenche os campos
      $("#createStock").trigger("click");

      // Espera a modal abrir
      setTimeout(() => {
        $("#stockName").val(estoque.name);
        $("#stockDesc").val(estoque.description);
        $("#stockColor").val(estoque.color);
        $("#address").val(estoque.address);
        $("#latitude").val(estoque.latitude);
        $("#longitude").val(estoque.longitude);

        $("#address").val(estoque.address || "");
        $("#address_number").val(estoque.address_number || "");
        $("#neighborhood").val(estoque.neighborhood || "");
        $("#city").val(estoque.city || "");
        $("#state").val(estoque.state || "");
        $("#zip_code").val(estoque.zip_code || "");
        $("#country").val(estoque.country || "");
        $("#county").val(estoque.county || "");
        $("#state_district").val(estoque.state_district || "");
        $("#region").val(estoque.region || "");
        $("#continent").val(estoque.continent || "");
        $("#iso_region_code").val(estoque.iso_region_code || "");
        $("#location_detail").val(estoque.location_detail || "");

        $(`.icon-box[data-icon="${estoque.icon}"]`).addClass("selected");
      }, 500);

      // Altera o evento de confirmação da modal para UPDATE
      Swal.getConfirmButton().onclick = () => {
        const name = $("#stockName").val().trim();
        const desc = $("#stockDesc").val().trim();
        const color = $("#stockColor").val();
        const icon = $(".icon-box.selected").data("icon") || "box";
        const address = $("#address").val().trim();
        const latitude = $("#latitude").val().trim();
        const longitude = $("#longitude").val().trim();

        if (!name) {
          Swal.showValidationMessage("O nome do estoque é obrigatório.");
          return;
        }

        $.post(
          "stock/ajax/stock_controller.php",
          {
            action: "update",
            id,
            name,
            description: desc,
            color,
            icon,
            address,
            latitude,
            longitude,
          },
          function (resp) {
            if (resp.success) {
              Swal.fire("Stock atualizado!", "", "success");
              carregarEstoques();
            } else {
              Swal.fire("Erro ao atualizar stock", "", "error");
            }
          },
          "json",
        );
      };
    },
    "json",
  );
}

function verMapa(lat, lng, nome) {
  Swal.fire({
    title: nome,
    html: `<div id="mapModal" style="height: 300px;"></div>`,
    width: 600,
    showCloseButton: true,
    didOpen: () => {
      const map = L.map("mapModal").setView([lat, lng], 16);
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
        map,
      );
      L.marker([lat, lng]).addTo(map);
    },
  });
}

function deletarEstoque(stockId) {
  $.get(
    "stock/ajax/stock_controller.php",
    { action: "verificar_vazio", stock_id: stockId },
    function (res) {
      if (res.success) {
        if (res.vazio) {
          Swal.fire({
            title: "Tem certeza?",
            text: "Essa ação não poderá ser desfeita.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sim, deletar",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              $.post(
                "stock/ajax/stock_controller.php",
                {
                  action: "delete",
                  stock_id: stockId,
                },
                function (delRes) {
                  if (delRes.success) {
                    Swal.fire("Deletado!", "", "success");
                    carregarEstoques();
                  } else {
                    Swal.fire("Erro ao deletar o stock.", "", "error");
                  }
                },
                "json",
              );
            }
          });
        } else {
          Swal.fire(
            "Não é possível deletar!",
            "Esse stock contém produtos/serviços cadastrados.",
            "info",
          );
        }
      } else {
        Swal.fire("Erro ao verificar stock.", "", "error");
      }
    },
    "json",
  );
}

function gerarCoresDiferentes(qtd) {
  const paleta = [
    "#4e73df",
    "#1cc88a",
    "#36b9cc",
    "#f6c23e",
    "#e74a3b",
    "#fd7e14",
    "#20c997",
    "#6f42c1",
    "#343a40",
    "#17a2b8",
    "#6610f2",
    "#ffc107",
  ];
  const cores = [];
  for (let i = 0; i < qtd; i++) {
    cores.push(paleta[i % paleta.length]);
  }
  return cores;
}

async function exportGeneralStocks(type) {
  const data = window.stocksData || [];
  if (!data.length) {
    Swal.fire("Sem dados", "Não há stocks para exportar.", "info");
    return;
  }

  // Prepara os dados para exportação
  const exportData = data.map((s) => ({
    ID: s.id,
    Nome: s.name,
    Descrição: s.description || "",
    Endereço: s.address || "",
    "Total Produtos/Serviços": s.total_itens,
    "Valor Total": parseFloat(s.valor_total).toLocaleString("pt-AO", {
      style: "currency",
      currency: "AOA",
    }),
    "Atualizado Em": s.updated_at
      ? new Date(s.updated_at).toLocaleDateString("pt-BR")
      : "-",
    "Atualizado Por": s.updated_by_name || s.updated_by || "-",
  }));

  if (type === "excel") {
    const ws = XLSX.utils.json_to_sheet(exportData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Stocks");
    XLSX.writeFile(wb, "Lista_Stocks.xlsx");
  } else if (type === "pdf") {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    // Busca logo da empresa (server-side -> dataURL)
    let logoDataUrl = null;
    try {
      const resp = await fetch("stock/ajax/company_logo.php");
      const j = await resp.json();
      logoDataUrl = j?.dataUrl || null;
    } catch (e) {}

    // Cabeçalho (logo no início)
    let headerY = 15;
    if (logoDataUrl) {
      try {
        const fmt =
          logoDataUrl.startsWith("data:image/jpeg") ||
          logoDataUrl.startsWith("data:image/jpg")
            ? "JPEG"
            : "PNG";
        doc.addImage(logoDataUrl, fmt, 14, 10, 28, 12);
        headerY = 28;
      } catch (e) {}
    }

    doc.setFontSize(12);
    doc.setTextColor(0);
    doc.text("Relatório Geral de Stocks", 14, headerY);
    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.text(
      `Gerado em: ${new Date().toLocaleString("pt-BR")}`,
      14,
      headerY + 7,
    );

    const headers = [
      [
        "ID",
        "Nome",
        "Descrição",
        "Endereço",
        "Produtos/Serviços",
        "Valor Total",
      ],
    ];
    const rows = exportData.map((s) => [
      s.ID,
      s.Nome,
      s.Descrição,
      s.Endereço,
      s["Total Produtos/Serviços"],
      s["Valor Total"],
    ]);

    // Reserva espaço no rodapé para paginação e no topo pro cabeçalho
    doc.autoTable({
      head: headers,
      body: rows,
      startY: headerY + 12,
      margin: { top: 10, bottom: 18 },
    });

    const pageCount = doc.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);

      // Paginação centralizada
      doc.setFontSize(9);
      doc.setTextColor(120);
      const text = `Página ${i} de ${pageCount}`;
      doc.text(text, pageWidth / 2, pageHeight - 10, { align: "center" });
    }

    doc.save("Lista_Stocks.pdf");
  }
}
