document.addEventListener("DOMContentLoaded", () => {
  // =========================
  // ELEMENTOS
  // =========================
  const financeBlock = document.getElementById("financeBlock");
  const categorySelect = document.getElementById("category");
  const subcategorySelect = document.getElementById("subcategory");
  const depotCard = document.getElementById("depot");
  const codigoInput = document.getElementById("codigo");
  const stockSelect = document.getElementById("stock_id");

  const taxField = document.getElementById("tax");
  const retentionField = document.getElementById("retention_tax");

  const unitPriceInput = document.querySelector("[name='unit_price']");
  const costPriceInput = document.querySelector("[name='cost_price']");
  const salePriceInput = document.querySelector("[name='sale_price']");
  const pvpInput = document.querySelector("[name='pvp']");

  let ivaRegime = "geral";

  if (!categorySelect || !depotCard || !codigoInput) {
    console.error("Elementos essenciais não encontrados.");
    return;
  }

  // =========================
  // UI TOGGLE
  // =========================
  function toggleUI({
    categorySelect,
    depotCard,
    stockSelect,
    codigoInput,
  } = {}) {
    if (!categorySelect || !depotCard) return;

    const isProduct = categorySelect.value === "product";

    depotCard.classList.toggle("active", isProduct);

    if (stockSelect) {
      stockSelect.required = isProduct;
    }

    if (!isProduct) {
      if (codigoInput) codigoInput.value = "";
      if (stockSelect) stockSelect.value = "";
    }
  }

  // =========================
  // STATE ENGINE (ÚNICO)
  // =========================
  function syncUI() {
    toggleUI({
      categorySelect,
      depotCard,
      stockSelect,
      codigoInput,
    });

    toggleFinance();
    updateFiscal();
    generateCode();
  }

  // =========================
  // PROTEÇÃO INPUT TAX
  // =========================
  if (taxField) {
    ["keydown", "paste", "drop"].forEach((evt) =>
      taxField.addEventListener(evt, (e) => e.preventDefault()),
    );
  }

  // =========================
  // LOAD COMPANY
  // =========================
  async function loadCompany() {
    try {
      const id = document.querySelector("input[name='id_company']")?.value;
      if (!id) return;

      const res = await fetch(
        `assets/ajax/get_company.php?id=${encodeURIComponent(id)}`,
      );

      const data = await res.json();

      if (data?.success) {
        ivaRegime = data.data?.vat_regime || "geral";
        updateFiscal();
      }
    } catch (error) {
      console.error("Erro empresa:", error);
    }
  }

  // =========================
  // FISCAL (CORRIGIDO)
  // =========================
  function updateFiscal() {
    const taxField = document.getElementById("tax");
    const categorySelect = document.getElementById("item_type");
    const subcategorySelect = document.getElementById("subcategory");
    const unitPriceInput = document.getElementById("unit_price");
    const retentionField = document.getElementById("retention");
    const ivaRegimeField = document.getElementById("iva_regime"); // assumido

    if (!taxField || !categorySelect || !unitPriceInput) return;

    const unitPrice = parseFloat(unitPriceInput.value) || 0;

    const subcat = subcategorySelect?.value || "";
    const category = categorySelect?.value || "";
    const ivaRegime = ivaRegimeField?.value || "geral";

    // =========================
    // TAX LOGIC
    // =========================
    let tax = 0;

    if (subcat === "essential" || subcat === "agriculture") {
      tax = 5;
    } else {
      switch (ivaRegime) {
        case "geral":
          tax = 14;
          break;
        case "simplificado":
          tax = 7;
          break;
        default:
          tax = 0;
      }
    }

    taxField.value = String(tax);

    taxField.dispatchEvent(new Event("input", { bubbles: true }));
    taxField.dispatchEvent(new Event("change", { bubbles: true }));

    // =========================
    // RETENTION LOGIC
    // =========================
    if (retentionField) {
      const applyRetention = category === "service" && unitPrice >= 20000;

      retentionField.value = applyRetention ? "6.5" : "0";

      retentionField.dispatchEvent(new Event("input", { bubbles: true }));
      retentionField.dispatchEvent(new Event("change", { bubbles: true }));
    }
  }

  // =========================
  // FINANCE BLOCK
  // =========================
  function toggleFinance() {
    if (!financeBlock || !categorySelect) return;

    const hide = ["product", "service"].includes(categorySelect.value);
    financeBlock.style.display = hide ? "none" : "block";

    if (hide) {
      costPriceInput && (costPriceInput.value = "");
      salePriceInput && (salePriceInput.value = "");
      pvpInput && (pvpInput.value = "");
    }
  }

  // =========================
  // CODE GENERATION
  // =========================
  async function generateCode() {
    const stockId = stockSelect?.value;
    const itemType = categorySelect.value;

    try {
      const res = await fetch(
        `items/ajax/generate_code.php?stock_id=${encodeURIComponent(
          stockId || "",
        )}&item_type=${itemType}`,
      );

      const data = await res.json();

      if (data.success && data.generated_code) {
        codigoInput.value = data.generated_code;
      }
    } catch (err) {
      console.error("Erro ao gerar código:", err);
    }
  }

  // =========================
  // VALIDATE CODE
  // =========================
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

  // =========================
  // STOCKS
  // =========================
  async function fetchStocks() {
    if (!stockSelect) return;

    try {
      const res = await fetch("index/ajax/fetch_stocks.php");
      const data = await res.json();

      if (Array.isArray(data?.data)) {
        stockSelect.innerHTML =
          `<option value="">Selecione um stock</option>` +
          data.data
            .map((s) => `<option value="${s.id}">${s.name}</option>`)
            .join("");
      }
    } catch (err) {
      console.error("Erro stocks:", err);
    }
  }

  // =========================
  // PRICE CALC
  // =========================
  function calculatePrice() {
    if (!pvpInput) return;

    const unitPrice = parseFloat(unitPriceInput?.value) || 0;
    const costPrice = parseFloat(costPriceInput?.value) || 0;
    const salePrice = parseFloat(salePriceInput?.value) || 0;

    const tax = parseFloat(taxField?.value) || 0;
    const retention = parseFloat(retentionField?.value) || 0;

    const base = costPrice + (salePrice || unitPrice);

    const iva = (base * tax) / 100;
    const subtotal = base + iva;
    const reten = (subtotal * retention) / 100;

    pvpInput.value = (subtotal - reten).toFixed(2);
  }

  // =========================
  // EVENTS
  // =========================
  categorySelect.addEventListener("change", syncUI);

  subcategorySelect?.addEventListener("change", updateFiscal);
  stockSelect?.addEventListener("change", generateCode);
  codigoInput?.addEventListener("blur", validateCode);

  [unitPriceInput, costPriceInput, salePriceInput].forEach((input) => {
    input?.addEventListener("input", () => {
      updateFiscal();
      calculatePrice();
    });
  });

  // =========================
  // INIT
  // =========================
  syncUI();
  fetchStocks();
  loadCompany();
  calculatePrice();
});
