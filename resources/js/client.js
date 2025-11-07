let searchTimeout;

function renderSearchResults(data, query) {
    const container = document.getElementById('searchResults');
    if (!container) return;
    const hasResults = (data.doctors && data.doctors.length) || (data.services && data.services.length);
    if (!hasResults) {
        container.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    const items = [];
    if (data.doctors) {
        data.doctors.forEach(d => {
            items.push(`
                <a class="result-item" href="/catalog" style="display:flex;gap:.75rem;align-items:center;padding:.5rem .75rem;text-decoration:none;color:#333;">
                    <i class="fas fa-user-md" style="color:#0d6efd;"></i>
                    <div>
                        <div style="font-weight:600;">${d.name}</div>
                        <div style="font-size:12px;color:#6c757d;">${d.specialization ?? ''}</div>
                    </div>
                </a>
            `);
        });
    }

    if (data.services) {
        data.services.forEach(s => {
            items.push(`
                <a class="result-item" href="/sub-catalog/${encodeURIComponent(s.subcatalog_id || '')}" style="display:flex;gap:.75rem;align-items:center;padding:.5rem .75rem;text-decoration:none;color:#333;">
                    <i class="fas fa-stethoscope" style="color:#20c997;"></i>
                    <div>
                        <div style="font-weight:600;">${s.name}</div>
                        <div style="font-size:12px;color:#6c757d;">${s.catalog ?? ''} ${s.subcatalog ? '• ' + s.subcatalog : ''} — ${s.price}</div>
                    </div>
                </a>
            `);
        });
    }

    container.innerHTML = `
        <div style="background:#fff;border:1px solid #e9ecef;border-radius:.5rem;box-shadow:0 10px 20px rgba(0,0,0,.06);max-height:360px;overflow:auto;">
            ${items.join('')}
        </div>
    `;
    container.style.display = 'block';
}

function setupLiveSearch() {
    const input = document.querySelector('.search-input');
    const container = document.getElementById('searchResults');
    if (!input || !container) return;

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(searchTimeout);
        if (q.length < 2) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }
        searchTimeout = setTimeout(async () => {
            try {
                const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                renderSearchResults(data, q);
            } catch (e) {
                container.style.display = 'none';
            }
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!container.contains(e.target) && !input.contains(e.target)) {
            container.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', setupLiveSearch);


