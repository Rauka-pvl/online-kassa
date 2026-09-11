function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

let searchTimeout;

function renderSearchResults(data, query) {
    const container = document.getElementById('searchResults');
    if (!container) return;

    const doctors = data.doctors || [];
    const services = data.services || [];
    const hasResults = doctors.length || services.length;

    const sections = [];

    if (!hasResults) {
        sections.push(`
            <div class="search-empty">
                <i class="fas fa-search"></i>
                <div>Ничего не найдено по запросу «${escapeHtml(query)}»</div>
            </div>
        `);
    } else {
        if (doctors.length) {
            sections.push(`<div class="search-group-title">Врачи</div>`);
            doctors.forEach((d) => {
                sections.push(`
                    <a class="search-result-item" href="${escapeHtml(d.url || '/catalog')}">
                        <span class="search-result-icon"><i class="fas fa-user-md"></i></span>
                        <span>
                            <span class="search-result-name">${escapeHtml(d.name)}</span>
                            <span class="search-result-meta">${escapeHtml(d.specialization || 'Врач')}</span>
                        </span>
                    </a>
                `);
            });
        }
        if (services.length) {
            sections.push(`<div class="search-group-title">Услуги</div>`);
            services.forEach((s) => {
                const place = (s.subcatalogs && s.subcatalogs.length)
                    ? s.subcatalogs.join(', ')
                    : [s.catalog, s.subcatalog].filter(Boolean).join(' → ');
                sections.push(`
                    <a class="search-result-item" href="${escapeHtml(s.url || '/catalog')}">
                        <span class="search-result-icon is-service"><i class="fas fa-stethoscope"></i></span>
                        <span>
                            <span class="search-result-name">${escapeHtml(s.name)}</span>
                            <span class="search-result-meta">${escapeHtml(place)}${s.price ? ' · ' + escapeHtml(s.price) : ''}</span>
                        </span>
                    </a>
                `);
            });
        }
    }

    container.innerHTML = `<div class="search-dropdown">${sections.join('')}</div>`;
    container.style.display = 'block';
}

async function runLiveSearch(query) {
    const container = document.getElementById('searchResults');
    if (!container) return;

    if (query.length < 2) {
        container.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    try {
        const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
        const data = await res.json();
        renderSearchResults(data, query);
    } catch (e) {
        container.style.display = 'none';
    }
}

function setupLiveSearch() {
    const input = document.querySelector('.search-input');
    const button = document.querySelector('.search-btn');
    const container = document.getElementById('searchResults');
    if (!input || !container) return;

    const requestSearch = () => {
        const q = input.value.trim();
        clearTimeout(searchTimeout);
        if (q.length < 2) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }
        searchTimeout = setTimeout(() => runLiveSearch(q), 200);
    };

    input.addEventListener('input', requestSearch);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            runLiveSearch(input.value.trim());
        }
        if (e.key === 'Escape') {
            container.style.display = 'none';
        }
    });

    if (button) {
        button.setAttribute('type', 'button');
        button.addEventListener('click', (e) => {
            e.preventDefault();
            clearTimeout(searchTimeout);
            runLiveSearch(input.value.trim());
            input.focus();
        });
    }

    document.addEventListener('click', (e) => {
        if (!container.contains(e.target) && !input.contains(e.target) && !button?.contains(e.target)) {
            container.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', setupLiveSearch);
