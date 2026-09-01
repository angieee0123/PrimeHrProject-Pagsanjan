// Attachment cards — Step 5 (Government IDs) and Step 6 (the 201 file).
//
// Both steps ask for the same thing: a scan or a form, uploaded one file per
// row. As native file inputs they were a list an admin scrolls past — nothing
// says how many are attached, a mis-picked file can only be swapped and never
// removed, and a wrong format is discovered by the server after the whole
// wizard has been submitted. Each row is now a card that reports its own state
// and checks the file the moment it is chosen.
//
// The rules are NOT restated here. Accepted extensions and the size cap are
// read off each step's container, which Blade renders from GovernmentId /
// EmployeeSupportingDocument. A second copy in JavaScript is a rule that drifts
// from the one the server enforces, and the admin would be told a file was fine
// right up until the submit rejected it. The two steps carry different lists —
// a spreadsheet is a valid PDS and never a valid ID scan — which is exactly why
// the container, not the module, is where the answer lives.

// Extension -> the family its badge is coloured by. Anything unlisted still
// gets a badge, just in the neutral tone.
const EXT_FAMILY = {
    pdf: 'pdf',
    doc: 'word', docx: 'word',
    xls: 'sheet', xlsx: 'sheet', csv: 'sheet',
    jpg: 'image', jpeg: 'image', png: 'image',
};

const UPLOAD_ICON = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><polyline points="7 9 12 4 17 9"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>';

function containers() {
    return document.querySelectorAll('[data-attachment-cards]');
}

function containerOf(el) {
    return el.closest('[data-attachment-cards]');
}

function acceptedExtensions(container) {
    return (container && container.dataset.extensions ? container.dataset.extensions : '').split(',').filter(Boolean);
}

function maxKb(container) {
    return parseInt((container && container.dataset.maxKb) || '0', 10);
}

function maxLabel(container) {
    return (container && container.dataset.maxLabel) || '';
}

// post_max_size is a property of the server, not of a step, so it is taken
// from whichever container declares it.
function postMaxKb() {
    const el = document.querySelector('[data-attachment-cards][data-post-max-kb]');
    return el ? parseInt(el.dataset.postMaxKb || '0', 10) : 0;
}

function postMaxLabel() {
    const el = document.querySelector('[data-attachment-cards][data-post-max-label]');
    return el ? (el.dataset.postMaxLabel || '') : '';
}

function extensionOf(name) {
    const parts = String(name || '').split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

function formatBytes(bytes) {
    if (!bytes && bytes !== 0) return '';
    if (bytes < 1024) return bytes + ' B';
    const kb = bytes / 1024;
    if (kb < 1024) return Math.round(kb) + ' KB';
    return (kb / 1024).toFixed(kb / 1024 < 10 ? 1 : 0) + ' MB';
}

// An object URL for an image preview is revoked before the next one replaces
// it — a wizard that is opened, cleared and reopened all day would otherwise
// hold every thumbnail the admin ever picked for the life of the tab.
function releaseThumb(thumb) {
    if (thumb && thumb.dataset.objectUrl) {
        URL.revokeObjectURL(thumb.dataset.objectUrl);
        delete thumb.dataset.objectUrl;
    }
}

function resetCard(card, { clearInput = false } = {}) {
    const input  = card.querySelector('.doc-input');
    const thumb  = card.querySelector('[data-doc-thumb]');
    const picked = card.querySelector('[data-doc-picked]');
    const error  = card.querySelector('[data-doc-error]');
    const meta   = card.querySelector('[data-doc-meta]');
    const cta    = card.querySelector('[data-doc-cta]');

    if (clearInput && input) input.value = '';
    card.classList.remove('doc-card-filled', 'doc-card-error', 'doc-card-drag');
    releaseThumb(thumb);
    if (thumb) {
        thumb.className = 'doc-thumb';
        thumb.innerHTML = UPLOAD_ICON;
    }
    if (picked) picked.hidden = true;
    if (error) { error.hidden = true; error.textContent = ''; }
    if (meta) meta.textContent = 'Click to browse, or drop a file here';
    if (cta) cta.textContent = 'Browse';
}

function showCardError(card, message) {
    const error = card.querySelector('[data-doc-error]');
    resetCard(card, { clearInput: true });
    card.classList.add('doc-card-error');
    if (error) {
        error.textContent = message;
        error.hidden = false;
    }
}

function showCardFile(card, file) {
    const thumb = card.querySelector('[data-doc-thumb]');
    const picked = card.querySelector('[data-doc-picked]');
    const meta = card.querySelector('[data-doc-meta]');
    const cta = card.querySelector('[data-doc-cta]');
    const error = card.querySelector('[data-doc-error]');
    const ext = extensionOf(file.name);

    // A rejected pick leaves its message on the card; the replacement that
    // fixes it has to clear the message as well as the state, or the card
    // reads as both attached and refused.
    if (error) { error.hidden = true; error.textContent = ''; }
    card.classList.remove('doc-card-error');
    card.classList.add('doc-card-filled');

    releaseThumb(thumb);
    if (thumb) {
        thumb.className = 'doc-thumb doc-thumb-' + (EXT_FAMILY[ext] || 'other');
        if (EXT_FAMILY[ext] === 'image') {
            // A scanned form or ID is only identifiable by looking at it — the
            // filename off a phone or a scanner ("IMG_4821.jpg") says nothing
            // about which document it is.
            const url = URL.createObjectURL(file);
            thumb.dataset.objectUrl = url;
            thumb.innerHTML = '';
            const img = document.createElement('img');
            img.src = url;
            img.alt = '';
            thumb.appendChild(img);
        } else {
            thumb.textContent = ext.toUpperCase();
        }
    }

    card.querySelector('[data-doc-filename]').textContent = file.name;
    card.querySelector('[data-doc-filesize]').textContent = formatBytes(file.size);
    if (picked) picked.hidden = false;
    if (meta) meta.textContent = 'Choose another file to replace this one';
    if (cta) cta.textContent = 'Replace';

    // Edit mode's "on file" line is about the copy already on the server. Once
    // a replacement is chosen it no longer describes what will be saved.
    const current = card.querySelector('.wizard-supporting-current, .wizard-govid-current');
    if (current) current.style.display = 'none';
}

// Returns true when the file was accepted onto the card.
function handlePick(input) {
    const card = input.closest('.doc-card');
    if (!card) return false;
    const container = containerOf(card);

    const file = input.files && input.files[0];
    if (!file) {
        resetCard(card);
        updateProgress(container);
        return false;
    }

    const ext = extensionOf(file.name);
    const allowed = acceptedExtensions(container);
    if (allowed.length && !allowed.includes(ext)) {
        showCardError(
            card,
            (ext ? '.' + ext : 'That file') + ' is not an accepted format. Use ' +
            allowed.filter(e => e !== 'jpeg').map(e => e.toUpperCase()).join(', ') + '.'
        );
        updateProgress(container);
        return false;
    }

    const cap = maxKb(container);
    if (cap && file.size > cap * 1024) {
        showCardError(card, 'That file is ' + formatBytes(file.size) + ' — the limit is ' + maxLabel(container) + '.');
        updateProgress(container);
        return false;
    }

    showCardFile(card, file);
    updateProgress(container);
    return true;
}

// ── Whole-submission guard ──
//
// PHP enforces post_max_size before anything of ours runs: over the limit it
// discards the entire body — the CSRF token with it — and Laravel answers 419
// Page Expired. Seven steps of typing are gone and nothing on screen explains
// it. Every file input in the wizard counts toward that total, the photo and
// the ID scans included, so the sum is taken across the whole form.
//
// The 0.9 leaves room for the text fields, the boundaries and the token; the
// limit applies to the encoded body, which is a little larger than the files.
const PAYLOAD_SAFETY_FACTOR = 0.9;

function attachedKb() {
    const form = document.getElementById('employeeWizardForm');
    if (!form) return 0;
    let bytes = 0;
    form.querySelectorAll('input[type="file"]').forEach(function(input) {
        Array.from(input.files || []).forEach(function(file) { bytes += file.size; });
    });
    return bytes / 1024;
}

// Returns a sentence to show the admin, or null when the submission will fit.
function payloadError() {
    const cap = postMaxKb();
    if (!cap) return null;
    const used = attachedKb();
    if (used <= cap * PAYLOAD_SAFETY_FACTOR) return null;

    return 'The attached files come to ' + formatBytes(used * 1024) + ', and this server accepts at most ' +
        postMaxLabel() + ' per submission. Remove or replace a file with a smaller scan before submitting — ' +
        'otherwise the whole registration is rejected and the form is lost.';
}
window.wizardDocumentsPayloadError = payloadError;

// Shown on every step that has a warning slot: the file that pushes the form
// over the limit may not be on the step the admin is looking at.
function updatePayloadWarning() {
    const message = payloadError();
    document.querySelectorAll('[data-payload-warning]').forEach(function(el) {
        el.textContent = message || '';
        el.hidden = !message;
    });
}

function updateProgress(container) {
    if (!container) return;
    const cards = container.querySelectorAll('.doc-card');
    const filled = container.querySelectorAll('.doc-card.doc-card-filled').length;
    const countEl = container.querySelector('[data-attach-count]');
    const fillEl = container.querySelector('[data-attach-fill]');
    if (countEl) countEl.textContent = filled;
    if (fillEl) fillEl.style.width = cards.length ? Math.round((filled / cards.length) * 100) + '%' : '0%';
    updatePayloadWarning();
}

function resetAllDocumentCards() {
    containers().forEach(function(container) {
        container.querySelectorAll('.doc-card').forEach(function(card) {
            resetCard(card, { clearInput: true });
        });
        updateProgress(container);
    });
    clearGovIdOcrStatuses();
}
window.resetWizardDocumentCards = resetAllDocumentCards;

// Rows for the Review step, built here so the labels and the attached files
// are read off the same cards the admin filled in — generateReview() would
// otherwise carry a second copy of the twelve document names.
window.wizardSupportingDocReviewRows = function() {
    const step = document.getElementById('wizardDocsStep');
    if (!step) return [];
    return Array.from(step.querySelectorAll('.doc-card')).map(function(card) {
        const input = card.querySelector('.doc-input');
        const file = input && input.files && input.files[0];
        const label = card.querySelector('.doc-name').textContent.trim();
        if (file && card.classList.contains('doc-card-filled')) {
            return [label, file.name + ' (' + formatBytes(file.size) + ')'];
        }
        // In edit mode a document with nothing newly picked still has one on
        // file; reporting it as blank would read as "this was never submitted".
        const current = card.querySelector('.wizard-supporting-current');
        if (current && current.style.display !== 'none' && current.textContent.trim()) {
            return [label, 'Already on file — unchanged'];
        }
        return [label, ''];
    });
};

// ── Government ID scan -> OCR auto-fill (Step 5) ──
//
// Best-effort only: a misread or an unsupported scan just leaves the number
// field for the admin to type by hand, which is why the request never blocks
// the wizard and every failure path still leaves the field editable.
//
// It lives here, rather than beside the rest of the wizard, so it runs *after*
// the card has vetted the file. Wired separately it fired first, uploading a
// file the card was about to reject and answering with an OCR failure for what
// was really a wrong format.
function setGovIdStatus(idType, message, tone) {
    const el = document.querySelector('.govid-ocr-status[data-status-for="' + idType + '"]');
    if (!el) return;
    el.textContent = message;
    el.style.color = tone === 'error' ? 'var(--theme-danger)' : (tone === 'success' ? 'var(--theme-success)' : 'var(--gp-text-mid)');
}

function clearGovIdOcrStatuses() {
    document.querySelectorAll('.govid-ocr-status').forEach(function(el) {
        el.textContent = '';
        el.style.color = '';
    });
}
window.clearGovIdOcrStatuses = clearGovIdOcrStatuses;

function readGovIdNumber(input) {
    const idType = input.dataset.idType;
    const targetField = document.querySelector('#employeeWizardForm [name="' + input.dataset.target + '"]');
    const file = input.files[0];
    if (!file || !idType) return;

    setGovIdStatus(idType, 'Reading ID scan…');

    const ocrData = new FormData();
    ocrData.append('file', file);
    ocrData.append('id_type', idType);
    const tokenField = document.querySelector('#employeeWizardForm [name="_token"]');

    fetch('/admin/personnel/government-ids/extract', {
        method: 'POST',
        headers: tokenField ? { 'X-CSRF-TOKEN': tokenField.value } : {},
        body: ocrData,
    })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (result.number && targetField) {
                targetField.value = result.number;
                setGovIdStatus(idType, result.message || 'Auto-filled from scan — please verify.', 'success');
            } else {
                setGovIdStatus(idType, result.message || 'Could not read the number automatically — please type it in.', 'error');
            }
        })
        .catch(function() {
            setGovIdStatus(idType, 'Could not read the scan automatically — please type the number in.', 'error');
        });
}

function wireDocumentCards() {
    containers().forEach(function(container) {
        container.querySelectorAll('.doc-card').forEach(function(card) {
            const input = card.querySelector('.doc-input');
            if (!input) return;

            input.addEventListener('change', function() {
                const accepted = handlePick(input);
                if (accepted && input.classList.contains('govid-file-input')) {
                    readGovIdNumber(input);
                } else if (!accepted && input.dataset.idType) {
                    setGovIdStatus(input.dataset.idType, '');
                }
            });

            // The input is stretched over the card face, so the browser handles
            // both the click and the drop natively; these only paint the hover
            // state a bare file input has no way to show.
            ['dragenter', 'dragover'].forEach(function(evt) {
                input.addEventListener(evt, function() { card.classList.add('doc-card-drag'); });
            });
            ['dragleave', 'drop', 'blur'].forEach(function(evt) {
                input.addEventListener(evt, function() { card.classList.remove('doc-card-drag'); });
            });

            const remove = card.querySelector('[data-doc-remove]');
            if (remove) {
                remove.addEventListener('click', function() {
                    resetCard(card, { clearInput: true });
                    if (input.dataset.idType) setGovIdStatus(input.dataset.idType, '');
                    updateProgress(container);
                    input.focus();
                });
            }
        });

        updateProgress(container);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    wireDocumentCards();

    // Any other file input — the photo on Step 1 — counts toward
    // post_max_size too, so the running total has to react to it as well.
    const wizardForm = document.getElementById('employeeWizardForm');
    if (wizardForm) {
        wizardForm.addEventListener('change', function(e) {
            if (e.target && e.target.type === 'file' && !e.target.classList.contains('doc-input')) {
                updatePayloadWarning();
            }
        });

        // closeEmployeeWizard() and startOverEmployeeWizard() both call
        // form.reset(), which clears the inputs but leaves the cards showing
        // the filenames that are no longer attached. `reset` fires before the
        // fields are cleared, hence the deferral.
        wizardForm.addEventListener('reset', function() {
            setTimeout(resetAllDocumentCards, 0);
        });
    }
});
