document.addEventListener("DOMContentLoaded", () => {
  const financeBlock = document.getElementById("financeBlock");
  const categorySelect = document.getElementById("category");
  const subcatSelect = document.getElementById("subcategory").value;
  const depotCard = document.getElementById("depot");
  const codigoInput = document.getElementById("codigo");
  const stockSelect = document.getElementById("stock_id");
  let taxVal = "geral"; // default
  let ivaRegime = "geral"; // default, será atualizado ao carregar empresa

  const taxField = document.getElementById("tax");
  taxField.addEventListener("keydown", (e) => e.preventDefault());
  taxField.addEventListener("paste", (e) => e.preventDefault());

  // segurança básica
  if (!categorySelect || !depotCard || !codigoInput || !stockSelect) {
    console.error("Elementos do DOM não encontrados.");
    return;
  }

  // buscar config da empresa
  async function loadCompany() {
    try {
      const id = document.querySelector("input[name='id_company']").value;

      const res = await fetch(
        `assets/ajax/get_company.php?id=${encodeURIComponent(id)}`,
        {
          method: "GET",
          headers: {
            Accept: "application/json",
          },
        },
      );

      const data = await res.json();

      if (data.success) {
        const companyData = data?.data;
        taxVal = data.data.vat_regime || "geral";
        ivaRegime = taxVal;
        updateFiscal(); // Atualiza IVA após carregar a empresa
      } else {
        console.error(data.message || "Erro ao carregar empresa");
      }
    } catch (error) {
      console.error("Erro na requisição da empresa:", error);
    }
  }

  function updateFiscal() {
    const fieldTax = document.getElementById("tax");
    const fieldRetention = document.getElementById("retention");

    if (!fieldTax || !fieldRetention) return;

    const unitPrice = parseFloat(
      document.querySelector("input[name='unit_price']")?.value || 0,
    );
    const category = categorySelect.value;
    const subcatSelect = document.getElementById("subcategory")?.value;

    // =========================
    // Regra de IVA por subcategoria
    // =========================
    if (subcatSelect === "essential" || subcatSelect === "agriculture") {
      taxVal = 5;
    } else {
      if (ivaRegime === "geral") taxVal = 14;
      else if (ivaRegime === "simplificado") taxVal = 7;
      else taxVal = "exempt";
    }

    fieldTax.value = taxVal;

    // =========================
    // Retenção automática
    // =========================
    const applyRetention = category === "service" && unitPrice >= 20000;
    fieldRetention.value = applyRetention ? "apply" : "do_not_apply";
  }
  /*
  |--------------------------------------------------------------------------
  | TOGGLE UI (PRODUTO vs SERVIÇO)
  |--------------------------------------------------------------------------
  */
  function toggleUI() {
    const isProduct = categorySelect.value === "product";

    // card stock
    depotCard.classList.toggle("active", isProduct);

    if (isProduct) {
      stockSelect.required = true; // se existir
    } else {
      codigoInput.value = "";
      stockSelect.required = false;
    }
  }

  function toggleFinance() {
    const value = categorySelect.value;

    const hide = value === "product" || value === "service";

    if (hide) {
      financeBlock.style.display = "none";

      // opcional: limpar valores
      document.querySelector("[name='cost_price']").value = "";
      document.querySelector("[name='sale_price']").value = "";
      document.querySelector("[name='pvp']").value = "";
    } else {
      financeBlock.style.display = "block";
    }
  }

  categorySelect.addEventListener("change", toggleFinance);

  // init
  toggleFinance();

  /*
  |--------------------------------------------------------------------------
  | GERAR CÓDIGO AUTOMÁTICO
  |--------------------------------------------------------------------------
  */
  async function generateCode() {
    const stockId = stockSelect.value;
    const itemType = categorySelect.value;

    // if (itemType !== "product") return;
    // if (!stockId) return;

    try {
      const res = await fetch(
        `items/ajax/generate_code.php?stock_id=${encodeURIComponent(stockId)}&item_type=${itemType}`,
      );

      const data = await res.json();

      if (data.success && data.generated_code) {
        codigoInput.value = data.generated_code;
      } else {
        console.warn(data.message || "Falha ao gerar código");
      }
    } catch (err) {
      console.error("Erro ao gerar código:", err);
    }
  }

  /*
  |--------------------------------------------------------------------------
  | VALIDAR CÓDIGO
  |--------------------------------------------------------------------------
  */
  async function validateCode() {
    const codigo = codigoInput.value.trim();

    if (!codigo) return;

    try {
      const res = await fetch("items/ajax/check_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ codigo }),
      });

      const data = await res.json();

      if (data.exists) {
        alert("Este código já existe.");
        codigoInput.value = "";
        codigoInput.focus();
      }
    } catch (err) {
      console.error("Erro ao validar código:", err);
    }
  }

  /*
  |--------------------------------------------------------------------------
  | STOCKS
  |--------------------------------------------------------------------------
  */
  async function fetchStocks() {
    try {
      const res = await fetch("index/ajax/fetch_stocks.php");
      const data = await res.json();

      if (data && Array.isArray(data.data)) {
        renderStocks(data.data);
      }
    } catch (err) {
      console.error("Erro ao buscar stocks:", err);
    }
  }

  function renderStocks(stocks) {
    stockSelect.innerHTML = `<option value="">Selecione um stock</option>`;

    stockSelect.insertAdjacentHTML(
      "beforeend",
      stocks.map((s) => `<option value="${s.id}">${s.name}</option>`).join(""),
    );
  }

  /*
  |--------------------------------------------------------------------------
  | EVENTS
  |--------------------------------------------------------------------------
  */
  categorySelect.addEventListener("change", () => {
    toggleUI();
    generateCode();
  });

  stockSelect.addEventListener("change", generateCode);
  codigoInput.addEventListener("blur", validateCode);

  function calculatePrice() {
    const unitPrice = parseFloat(
      document.querySelector("input[name='unit_price']")?.value || 0,
    );
    const costPrice = parseFloat(
      document.querySelector("[name='cost_price']").value || 0,
    );

    const salePrice = parseFloat(
      document.querySelector("[name='sale_price']").value || 0,
    );

    const tax = parseFloat(document.getElementById("tax").value || 0);

    const retention =
      document.getElementById("retention").value === "apply" ? 6.5 : 0;

    /*
  |--------------------------------------------------------------------------
  | Regra:
  | O preço final (PVP) deve depender do cost_price
  | e do lucro definido em sale_price
  |--------------------------------------------------------------------------
  */

    // Preço base = custo + margem/lucro
    const basePrice =
      salePrice == 0 ? costPrice + unitPrice : costPrice + salePrice;

    // IVA
    const iva = (basePrice * tax) / 100;

    // Subtotal com IVA
    const subtotal = basePrice + iva;

    // Retenção na fonte
    const reten = (subtotal * retention) / 100;

    // Preço final de venda
    const finalPrice = subtotal - reten;

    document.querySelector("[name='pvp']").value = finalPrice.toFixed(2);
  }

  /*
  |--------------------------------------------------------------------------
  | INIT
  |--------------------------------------------------------------------------
  */

  setInterval(async () => {
    calculatePrice();
    await loadCompany();
  }, 1000);
  toggleUI();
  fetchStocks();
});
