import '../css/pretty-select.css';

const enhanced = new WeakMap();

function shouldEnhance(select) {
    if (!(select instanceof HTMLSelectElement)) return false;
    if (select.multiple || select.size > 1) return false;
    if (select.hasAttribute('data-native')) return false;
    if (select.closest('.pretty-select')) return false;
    return true;
}

function selectedLabel(select) {
    const option = select.options[select.selectedIndex];
    return option ? option.textContent.trim() : '';
}

function isPlaceholder(select) {
    const option = select.options[select.selectedIndex];
    return !option || option.value === '';
}

function enhance(select) {
    if (!shouldEnhance(select) || enhanced.has(select)) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'pretty-select';
    if (select.classList.contains('form-select-sm')) {
        wrapper.classList.add('is-sm');
    }
    if (select.disabled) {
        wrapper.classList.add('is-disabled');
    }

    select.classList.add('pretty-select-native');
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'pretty-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.innerHTML = '<span class="pretty-select-value"></span><span class="pretty-select-caret"></span>';

    const menu = document.createElement('div');
    menu.className = 'pretty-select-menu';
    menu.innerHTML = '<input type="text" class="pretty-select-filter" placeholder="Поиск..." autocomplete="off"><ul class="pretty-select-options"></ul>';

    wrapper.appendChild(trigger);
    wrapper.appendChild(menu);

    const valueEl = trigger.querySelector('.pretty-select-value');
    const filter = menu.querySelector('.pretty-select-filter');
    const list = menu.querySelector('.pretty-select-options');

    const state = {
        wrapper,
        trigger,
        menu,
        filter,
        list,
        valueEl,
        open: false,
    };
    enhanced.set(select, state);

    function close() {
        state.open = false;
        wrapper.classList.remove('is-open');
        filter.value = '';
    }

    function positionMenu() {
        const rect = trigger.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const openUp = spaceBelow < 220 && rect.top > spaceBelow;
        menu.style.width = Math.max(rect.width, 180) + 'px';
        menu.style.left = Math.min(rect.left, window.innerWidth - rect.width - 8) + 'px';
        if (openUp) {
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.top + 6) + 'px';
        } else {
            menu.style.bottom = 'auto';
            menu.style.top = (rect.bottom + 6) + 'px';
        }
    }

    function syncLabel() {
        const label = selectedLabel(select) || '— Выбрать —';
        valueEl.textContent = label;
        valueEl.classList.toggle('is-placeholder', isPlaceholder(select));
        wrapper.classList.toggle('is-disabled', select.disabled);
        trigger.disabled = select.disabled;
    }

    function renderOptions() {
        const query = filter.value.trim().toLowerCase();
        const showFilter = select.options.length > 8;
        filter.style.display = showFilter ? 'block' : 'none';

        list.innerHTML = '';
        let visible = 0;

        Array.from(select.options).forEach((option) => {
            const text = option.textContent.trim();
            if (query && !text.toLowerCase().includes(query)) {
                return;
            }
            visible += 1;
            const item = document.createElement('li');
            item.className = 'pretty-select-option';
            item.dataset.value = option.value;
            item.textContent = text || '—';
            if (option.selected) item.classList.add('is-selected');
            if (option.disabled) {
                item.style.opacity = '0.5';
                item.style.pointerEvents = 'none';
            }
            item.addEventListener('click', () => {
                if (select.value !== option.value) {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                syncLabel();
                close();
            });
            list.appendChild(item);
        });

        if (!visible) {
            const empty = document.createElement('li');
            empty.className = 'pretty-select-empty';
            empty.textContent = 'Ничего не найдено';
            list.appendChild(empty);
        }
    }

    function open() {
        if (select.disabled) return;
        document.querySelectorAll('.pretty-select.is-open').forEach((el) => {
            if (el !== wrapper) el.classList.remove('is-open');
        });
        state.open = true;
        wrapper.classList.add('is-open');
        renderOptions();
        positionMenu();
        if (filter.style.display !== 'none') {
            filter.focus();
        }
    }

    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        if (state.open) close();
        else open();
    });

    filter.addEventListener('input', renderOptions);

    select.addEventListener('change', () => {
        syncLabel();
        if (state.open) renderOptions();
    });

    const observer = new MutationObserver(() => {
        syncLabel();
        if (state.open) renderOptions();
    });
    observer.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'value'] });

    syncLabel();
}

function enhanceAll(root = document) {
    root.querySelectorAll('select').forEach(enhance);
}

document.addEventListener('click', (e) => {
    document.querySelectorAll('.pretty-select.is-open').forEach((wrapper) => {
        if (!wrapper.contains(e.target) && !wrapper.querySelector('.pretty-select-menu').contains(e.target)) {
            wrapper.classList.remove('is-open');
        }
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.pretty-select.is-open').forEach((el) => el.classList.remove('is-open'));
    }
});

window.addEventListener('scroll', () => {
    document.querySelectorAll('.pretty-select.is-open').forEach((wrapper) => {
        const trigger = wrapper.querySelector('.pretty-select-trigger');
        const menu = wrapper.querySelector('.pretty-select-menu');
        if (!trigger || !menu) return;
        const rect = trigger.getBoundingClientRect();
        menu.style.width = Math.max(rect.width, 180) + 'px';
        menu.style.left = rect.left + 'px';
        menu.style.top = (rect.bottom + 6) + 'px';
        menu.style.bottom = 'auto';
    });
}, true);

const mo = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (!(node instanceof HTMLElement)) return;
            if (node.matches?.('select')) enhance(node);
            node.querySelectorAll?.('select').forEach(enhance);
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    enhanceAll();
    mo.observe(document.body, { childList: true, subtree: true });
});

export { enhanceAll };
