// Admin Dashboard — shared, page-wide concerns not owned by a single card/modal.
// Card- and modal-specific logic lives in the other files in this folder.

// Apply dynamic colors (avoid Blade-in-style lint issues)
document.querySelectorAll('.emp-avatar-dynamic, .event-icon-dynamic, .dept-fill').forEach(el => {
    const bg = el.dataset.bg;
    if (bg) el.style.backgroundColor = bg;
    const w = el.dataset.w;
    if (w) el.style.width = w;
});
