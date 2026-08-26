/*
    Every Export button on the Deductions page sends the toolbar filters
    sitting above the tab it belongs to, and the endpoint re-runs the query
    server-side.

    The three exports that already existed took no parameters at all, so a file
    exported from a filtered screen silently contained every row in the system
    — and the file's own parameter block would then have contradicted the rows
    beneath it. Values are read at click time rather than from the last submit,
    because these toolbars filter the table live and never submit anything.

    This lives in its own module rather than beside `switchTab` in
    adminDeductions.js: five separate Vite entry points import it, and a module
    they all pull in must not carry side effects that would then run once per
    bundle.

    `fields` maps a query parameter to the id of the input that supplies it.
*/
export function exportWithFilters(btn, fields) {
    const url = btn?.dataset.exportUrl;
    if (!url) return;

    const params = new URLSearchParams();

    Object.entries(fields).forEach(([param, elementId]) => {
        const value = document.getElementById(elementId)?.value?.trim() || '';
        if (value) params.set(param, value);
    });

    const query = params.toString();
    window.location.href = query ? url + '?' + query : url;
}
