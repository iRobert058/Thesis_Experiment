const STORAGE_KEY = "webshop_experiment_logs";
const CONDITIONS = new Set(["control", "minimal", "promo"]);

const conditionSelect = document.getElementById("condition-select");
const eventPreview = document.getElementById("event-preview");
const productGrid = document.getElementById("product-grid");
const checkoutBtn = document.getElementById("checkout-btn");
const exportLogsBtn = document.getElementById("export-logs-btn");
const clearLogsBtn = document.getElementById("clear-logs-btn");

function nowIso() {
  return new Date().toISOString();
}

function getLogs() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
  } catch {
    return [];
  }
}

function saveLogs(logs) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(logs));
}

function addLog(eventType, details = {}) {
  const logs = getLogs();
  const entry = {
    timestamp: nowIso(),
    condition: document.body.dataset.condition || "control",
    eventType,
    ...details,
  };
  logs.push(entry);
  saveLogs(logs);
  renderPreview(logs);
}

function renderPreview(logs = getLogs()) {
  eventPreview.textContent = JSON.stringify(logs.slice(-8), null, 2);
}

function setCondition(condition, source = "unknown") {
  const selected = CONDITIONS.has(condition) ? condition : "control";
  document.body.dataset.condition = selected;
  conditionSelect.value = selected;
  const url = new URL(window.location.href);
  url.searchParams.set("condition", selected);
  window.history.replaceState({}, "", url.toString());
  addLog("condition_set", { source, selected });
}

function parseInitialCondition() {
  const url = new URL(window.location.href);
  return url.searchParams.get("condition") || "control";
}

function exportLogs() {
  const blob = new Blob([JSON.stringify(getLogs(), null, 2)], {
    type: "application/json",
  });
  const fileName = `webshop-experiment-logs-${Date.now()}.json`;
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = fileName;
  link.click();
  URL.revokeObjectURL(link.href);
  addLog("logs_exported", { fileName });
}

function clearLogs() {
  saveLogs([]);
  renderPreview([]);
}

conditionSelect.addEventListener("change", (event) => {
  setCondition(event.target.value, "selector");
});

productGrid.addEventListener("click", (event) => {
  const card = event.target.closest(".product-card");
  if (!card) return;
  const productId = card.dataset.productId;

  if (event.target.classList.contains("add-to-cart")) {
    addLog("add_to_cart_clicked", { productId });
    return;
  }

  addLog("product_card_clicked", { productId });
});

checkoutBtn.addEventListener("click", () => {
  addLog("checkout_clicked");
});

exportLogsBtn.addEventListener("click", exportLogs);
clearLogsBtn.addEventListener("click", clearLogs);

setCondition(parseInitialCondition(), "query_param");
renderPreview();
