// Benefits Summary Tab
let benefitsCurrentPage = 1;
let benefitsRowsPerPage = 10;
let benefitsTotalRows = 0;

window.changeBenefitsRowsPerPage = function() {
    benefitsRowsPerPage = parseInt(document.getElementById('benefitsRowsPerPage').value);
    benefitsCurrentPage = 1;
    renderBenefitsPagination();
    paginateBenefitsTable();
}

window.renderBenefitsPagination = function() {
    const totalPages = Math.ceil(benefitsTotalRows / benefitsRowsPerPage);
    const paginationControls = document.getElementById('benefitsPaginationControls');
    let html = '';

    html += `<button class="page-btn" ${benefitsCurrentPage === 1 ? 'disabled' : ''} onclick="changeBenefitsPage(${benefitsCurrentPage - 1})">‹</button>`;

    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === benefitsCurrentPage ? 'active' : ''}" onclick="changeBenefitsPage(${i})">${i}</button>`;
    }

    html += `<button class="page-btn" ${benefitsCurrentPage === totalPages ? 'disabled' : ''} onclick="changeBenefitsPage(${benefitsCurrentPage + 1})">›</button>`;

    paginationControls.innerHTML = html;
}

window.changeBenefitsPage = function(page) {
    const totalPages = Math.ceil(benefitsTotalRows / benefitsRowsPerPage);
    if (page < 1 || page > totalPages) return;
    benefitsCurrentPage = page;
    renderBenefitsPagination();
    paginateBenefitsTable();
}

window.paginateBenefitsTable = function() {
    const tbody = document.querySelector('#benefits-tab tbody');
    const rows = tbody.querySelectorAll('tr');
    const start = (benefitsCurrentPage - 1) * benefitsRowsPerPage;
    const end = start + benefitsRowsPerPage;

    rows.forEach((row, index) => {
        if (index >= start && index < end) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    const visibleCount = Math.min(end, benefitsTotalRows) - start;
    document.getElementById('benefitsRowStart').textContent = visibleCount > 0 ? start + 1 : 0;
    document.getElementById('benefitsRowEnd').textContent = start + visibleCount;
}

document.addEventListener('DOMContentLoaded', function() {
    const tab = document.getElementById('benefits-tab');
    if (tab) {
        benefitsTotalRows = parseInt(tab.dataset.totalRows, 10) || 0;
        renderBenefitsPagination();
        paginateBenefitsTable();
    }
});
