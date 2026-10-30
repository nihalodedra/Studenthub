const PAGE_SIZE = 5;
const DATA_URL = "events.json";
let events = [];
let page = 1;
let nameAscending = true;

const search = document.querySelector("#search");
const category = document.querySelector("#category");
const output = document.querySelector("#output");
const status = document.querySelector("#status");

function render() {
  const query = search.value.trim().toLowerCase();
  const selectedCategory = category.value;
  const filtered = events.filter(event =>
    (event.name.toLowerCase().includes(query) || event.category.toLowerCase().includes(query)) &&
    (!selectedCategory || event.category === selectedCategory)
  );
  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  page = Math.min(page, totalPages);
  const pageRows = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);
  output.replaceChildren();
  pageRows.forEach(event => {
    const card = document.createElement("article");
    card.className = "event-card";
    const title = document.createElement("h2");
    title.textContent = event.name;
    const meta = document.createElement("p");
    meta.textContent = `${event.category} · ${event.date}`;
    const description = document.createElement("p");
    description.textContent = event.description;
    card.append(title, meta, description);
    output.append(card);
  });
  status.textContent = filtered.length ? `${filtered.length} event${filtered.length === 1 ? "" : "s"} found.` : "No events match these filters.";
  document.querySelector("#pageNumber").textContent = `Page ${page} of ${totalPages}`;
  document.querySelector("#previous").disabled = page <= 1;
  document.querySelector("#next").disabled = page >= totalPages;
}

async function loadEvents() {
  status.textContent = "Loading events…";
  try {
    const response = await fetch(DATA_URL);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    if (!Array.isArray(data)) throw new Error("The JSON file must contain an array.");
    events = data;
    render();
  } catch (error) {
    status.textContent = `Could not load events. Start Apache in XAMPP and open this page through http://localhost/xampp/php%20programm/Practical%206/. (${error.message})`;
  }
}

search.addEventListener("input", () => { page = 1; render(); });
category.addEventListener("change", () => { page = 1; render(); });
document.querySelector("#sort").addEventListener("click", () => { events.sort((a, b) => nameAscending ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name)); nameAscending = !nameAscending; page = 1; render(); });
document.querySelector("#previous").addEventListener("click", () => { page--; render(); });
document.querySelector("#next").addEventListener("click", () => { page++; render(); });
loadEvents();
