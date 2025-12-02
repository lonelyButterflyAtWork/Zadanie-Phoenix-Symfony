document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('user-filter-form');
    let debounceTimeout;

    function setLoading(loading) {
        const spinnerOverlay = document.getElementById('spinner-overlay');
        const userTable = document.getElementById('user-table');
        if (spinnerOverlay) spinnerOverlay.classList.toggle('d-none', !loading);
        if (userTable) userTable.classList.toggle('table-loading', loading);
    }

    function cleanParams(formData) {
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            // Pomijaj eventy i inne niepotrzebne pola (np. isTrusted)
            if (
                value !== null &&
                value !== undefined &&
                value !== '' &&
                typeof value !== 'object' &&
                key !== 'isTrusted'
            ) {
                params.append(key, value);
            }
        }
        return params.toString();
    }

    function updateTable(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTable = doc.getElementById('user-table');
        const newPagination = doc.getElementById('pagination');
        // Zawsze pobierz nowe referencje po podmianie DOM!
        if (newTable) {
            const oldTable = document.getElementById('user-table');
            if (oldTable) oldTable.replaceWith(newTable);
        }
        if (newPagination) {
            const oldPagination = document.getElementById('pagination');
            if (oldPagination) oldPagination.replaceWith(newPagination);
        }
    }

    function getCurrentSort() {
        const active = document.querySelector('.sort-link[data-current-order]');
        if (active) {
            return {
                sort_by: active.getAttribute('data-sort-by'),
                sort_order: active.getAttribute('data-current-order')
            };
        }
        return {};
    }

    function fetchAndReplaceTable(paramsOverride = {}) {
        setLoading(true);
        const formData = new FormData(filterForm);
        Object.entries(paramsOverride).forEach(([key, value]) => formData.set(key, value));

        const currentSort = getCurrentSort();
        if (!('sort_by' in paramsOverride) && currentSort.sort_by) {
            formData.set('sort_by', currentSort.sort_by);
            formData.set('sort_order', currentSort.sort_order);
        }

        const params = cleanParams(formData);

        fetch('/?' + params, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => updateTable(html))
            .finally(() => setLoading(false));
    }

    const inputs = filterForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(fetchAndReplaceTable, 300);
        });
        input.addEventListener('change', fetchAndReplaceTable);
    });

    document.addEventListener('click', function(e) {
        const target = e.target.closest('.sort-link');
        if (target) {
            e.preventDefault();
            const sortBy = target.getAttribute('data-sort-by');
            const currentOrder = target.getAttribute('data-current-order');
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            fetchAndReplaceTable({ sort_by: sortBy, sort_order: newOrder });
            return false;
        }
    });
});
