/* ============================================================
 * Day 5 — Live Search & Filter for Complaint Tables
 * ============================================================
 * Standalone, drop-in script. No backend changes required.
 *
 * WHAT IT DOES:
 *   Adds a live search box above any table with class="searchable-table"
 *   (or the table with id="complaintsTable" if that's what you're using).
 *   Typing filters rows instantly — matches against all visible text
 *   in each row (complaint ID, category, status, user name, etc.),
 *   so it works regardless of your exact column layout.
 *
 * HOW TO USE IT (no PHP changes needed):
 *   1. Save this file as assets/js/search.js (or wherever your other
 *      JS lives, e.g. next to app.js).
 *   2. In dashboard.php (or admin/dashboard.php), find the closing
 *      </body> tag and add ONE line just before it:
 *
 *          <script src="assets/js/search.js"></script>
 *
 *      (adjust the path if your assets folder is elsewhere, e.g.
 *      "../assets/js/search.js" if dashboard.php is inside admin/)
 *
 *   3. Make sure your complaints table has an id or class so this
 *      script can find it. Either:
 *          <table id="complaintsTable">   ...or...
 *          <table class="searchable-table">
 *      If neither is present, the script auto-detects the first
 *      <table> on the page as a fallback.
 *
 * That's it — no PHP, no database changes, nothing else to edit.
 * ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    // Find the target table: by id, then by class, then fallback to first table on page.
    var table = document.getElementById('complaintsTable')
        || document.querySelector('.searchable-table')
        || document.querySelector('table');

    if (!table) {
        // No table on this page — nothing to attach search to, so do nothing.
        return;
    }

    // Build the search input and insert it directly above the table.
    var wrapper = document.createElement('div');
    wrapper.style.margin = '16px 0';

    var input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'Search complaints (by ID, category, status, name...)';
    input.id = 'day5-search-input';
    input.style.width = '100%';
    input.style.maxWidth = '420px';
    input.style.padding = '10px 14px';
    input.style.border = '1.5px solid #CBD5E1';
    input.style.borderRadius = '8px';
    input.style.fontSize = '0.95rem';
    input.style.fontFamily = 'inherit';

    var resultCount = document.createElement('span');
    resultCount.id = 'day5-search-count';
    resultCount.style.marginLeft = '12px';
    resultCount.style.fontSize = '0.85rem';
    resultCount.style.color = '#64748B';

    wrapper.appendChild(input);
    wrapper.appendChild(resultCount);
    table.parentNode.insertBefore(wrapper, table);

    var tbody = table.querySelector('tbody') || table;
    var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

    function updateCount(visibleCount, totalCount) {
        if (input.value.trim() === '') {
            resultCount.textContent = '';
        } else {
            resultCount.textContent = visibleCount + ' of ' + totalCount + ' complaints match';
        }
    }

    input.addEventListener('input', function () {
        var query = input.value.trim().toLowerCase();
        var visibleCount = 0;

        allRows.forEach(function (row) {
            var rowText = row.textContent.toLowerCase();
            var matches = query === '' || rowText.indexOf(query) !== -1;
            row.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        updateCount(visibleCount, allRows.length);
    });
});
