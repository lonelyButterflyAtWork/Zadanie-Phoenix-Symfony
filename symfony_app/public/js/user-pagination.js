document.addEventListener('DOMContentLoaded', function() {
    const rows = Array.from(document.querySelectorAll('#user-table tbody tr'));
    const pageSize = 10;
    let currentPage = 1;
    const pagination = document.getElementById('pagination');

    function renderTable(page) {
        rows.forEach((row, i) => {
            row.style.display = (i >= (page - 1) * pageSize && i < page * pageSize) ? '' : 'none';
        });
    }

    function createPageLink(page, label, disabled = false, active = false) {
        return `<li class="page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}">
                    <a class="page-link" href="#" data-page="${page}">${label}</a>
                </li>`;
    }

    function renderPagination() {
        const totalPages = Math.ceil(rows.length / pageSize);
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        let html = '<nav><ul class="pagination">';
        html += createPageLink(currentPage - 1, '&laquo;', currentPage === 1);
        for (let p = 1; p <= totalPages; p++) {
            html += createPageLink(p, p, false, p === currentPage);
        }
        html += createPageLink(currentPage + 1, '&raquo;', currentPage === totalPages);
        html += '</ul></nav>';
        pagination.innerHTML = html;
        pagination.querySelectorAll('a.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page >= 1 && page <= totalPages && page !== currentPage) {
                    currentPage = page;
                    renderTable(currentPage);
                    renderPagination();
                }
            });
        });
    }

    if (rows.length > 0) {
        renderTable(currentPage);
        renderPagination();
    }
});
