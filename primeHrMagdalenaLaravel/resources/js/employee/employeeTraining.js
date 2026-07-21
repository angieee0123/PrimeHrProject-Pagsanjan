if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
}

const mainEl = document.querySelector('.permanent-training');
const FISCAL_YEAR = mainEl ? mainEl.dataset.fiscalYear : String(new Date().getFullYear());
const GOAL_HOURS = 40;
const MAX_CERT_BYTES = 5 * 1024 * 1024;
let activeStatusFilter = 'all';

const positionBadgeClass = {
    Managerial: 'managerial',
    Supervisory: 'supervisory',
    Technical: 'technical',
    Clerical: 'clerical',
};

function openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
    const anyOpen = Array.from(document.querySelectorAll('.modal-overlay')).some(
        m => m.style.display === 'flex'
    );
    if (!anyOpen) document.body.style.overflow = '';
}

// The shared modal component's "close" prop is always called with no
// arguments (renders as onclick="fn()"), so each modal using the generic
// closeModal(id) needs its own zero-arg wrapper to plug into that contract.
function closeFlashSuccessModal() { closeModal('trainingFlashSuccessModal'); }
function closeTrainingSubmitModal() { closeModal('trainingSubmitModal'); }
function closePdsExportModal() { closeModal('pdsExportModal'); }
function closeViewCertModal() { closeModal('viewCertModal'); }
function closeAddTrainingModal() { closeModal('addTrainingModal'); }

function showTrainingToast(msg) {
    const t = document.getElementById('trainingToast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(showTrainingToast._timer);
    showTrainingToast._timer = setTimeout(() => t.classList.remove('show'), 3200);
}

function setScanLabel(text) {
    const el = document.getElementById('certScanLabel');
    if (el) el.textContent = text;
}

function openAddTrainingModal() {
    resetToStep1();
    const form = document.getElementById('addTrainingForm');
    if (form) form.reset();
    openModal('addTrainingModal');
}

function resetToStep1() {
    const step1 = document.getElementById('certStep1');
    const step2 = document.getElementById('certStep2');
    const scan = document.getElementById('certScanState');
    const submitBtn = document.getElementById('certSubmitBtn');
    const ind1 = document.getElementById('step1Indicator');
    const ind2 = document.getElementById('step2Indicator');
    const sub = document.getElementById('modalStepSub');
    if (step1) step1.style.display = '';
    if (step2) step2.style.display = 'none';
    if (scan) scan.style.display = 'none';
    if (submitBtn) submitBtn.style.display = 'none';
    if (ind1) { ind1.classList.add('active'); ind1.classList.remove('done'); }
    if (ind2) ind2.classList.remove('active', 'done');
    if (sub) sub.textContent = 'Step 1 of 2 — Upload your certificate to auto-fill details.';
    const fn = document.getElementById('trainingFileName');
    if (fn) { fn.hidden = true; fn.textContent = ''; }
    const zone = document.getElementById('trainingDropZone');
    if (zone) zone.classList.remove('has-file', 'dragover');
    const grid = document.querySelector('#certStep1 .training-form-grid');
    if (grid) grid.style.display = '';
    const certInput = document.getElementById('trainingCertificate');
    if (certInput) certInput.value = '';
    document.querySelectorAll('.cert-autofilled').forEach(el => el.classList.remove('cert-autofilled'));
}

async function handleTrainingFile(input) {
    const file = input.files ? input.files[0] : null;
    if (!file) return;

    const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const extOk = /\.(pdf|jpe?g|png)$/i.test(file.name);
    if (!allowed.includes(file.type) && !extOk) {
        showTrainingToast('Please upload a PDF, JPEG, or PNG file.');
        input.value = '';
        return;
    }
    if (file.size > MAX_CERT_BYTES) {
        showTrainingToast('File must be 5 MB or smaller.');
        input.value = '';
        return;
    }

    const fileNameEl = document.getElementById('trainingFileName');
    const dropZone = document.getElementById('trainingDropZone');
    if (fileNameEl) {
        fileNameEl.hidden = false;
        fileNameEl.textContent = file.name;
    }
    if (dropZone) dropZone.classList.add('has-file');

    document.getElementById('certStep1').querySelector('.training-form-grid').style.display = 'none';
    document.getElementById('certScanState').style.display = 'block';
    setScanLabel('Reading certificate...');

    let text = '';
    try {
        const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
        if (isPdf) {
            if (typeof pdfjsLib === 'undefined') {
                throw new Error('PDF library not loaded');
            }
            text = await extractTextFromPdf(file);
        } else {
            text = await extractTextFromImage(file);
        }
    } catch (err) {
        console.error('Certificate extraction failed:', err);
        showTrainingToast('Could not read the certificate. You can still fill the form manually.');
        text = '';
    }

    const parsed = (typeof TrainingCertificateParser !== 'undefined')
        ? TrainingCertificateParser.parse(text || '')
        : {};
    autoFillForm(parsed, file.name);
    goToStep2(file.name);
}

window.openModal = openModal;
window.closeModal = closeModal;
window.openAddTrainingModal = openAddTrainingModal;
window.resetToStep1 = resetToStep1;
window.handleTrainingFile = handleTrainingFile;
window.showTrainingToast = showTrainingToast;

function setStatusFilter(status, btn) {
    activeStatusFilter = status;
    document.querySelectorAll('.training-filter-chip').forEach(c => {
        c.classList.toggle('active', c === btn);
    });
    filterPermanentTraining();
}
window.setStatusFilter = setStatusFilter;

function filterPermanentTraining() {
    const q = (document.getElementById('employeeTrainingSearch')?.value || '').toLowerCase().trim();
    const posFilter = document.getElementById('trainingPositionFilter')?.value || '';
    const rows = document.querySelectorAll('#trainingHistoryBody tr[data-status]');
    let visible = 0;
    rows.forEach(row => {
        const status = row.dataset.status || '';
        const position = row.dataset.position || '';
        const text = row.textContent.toLowerCase();
        let show = true;
        if (activeStatusFilter !== 'all' && status !== activeStatusFilter) show = false;
        if (posFilter && position !== posFilter) show = false;
        if (q && !text.includes(q)) show = false;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const rc = document.getElementById('trainingRowCount');
    if (rc) rc.textContent = visible;
}
window.filterPermanentTraining = filterPermanentTraining;

function recalcAnnualSummary() {
    const rows = document.querySelectorAll('#trainingHistoryBody tr[data-status]');
    let verifiedHours = 0, verified = 0, pending = 0, rejected = 0;
    const catHours = { leadership: 0, technical: 0, core: 0 };
    rows.forEach(row => {
        const status = row.dataset.status;
        const hours = parseInt(row.dataset.hours || '0', 10) || 0;
        const cat = row.dataset.category || 'core';
        if (status === 'verified') {
            verified++;
            verifiedHours += hours;
            if (catHours[cat] !== undefined) catHours[cat] += hours;
        } else if (status === 'pending') pending++;
        else if (status === 'rejected') rejected++;
    });
    const pct = Math.min(100, Math.round((verifiedHours / GOAL_HOURS) * 100));
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('statTotalHours', verifiedHours);
    set('statVerifiedCount', verified);
    set('statPendingCount', pending);
    set('statRejectedCount', rejected);
    set('statGoalSub', verifiedHours + ' of ' + GOAL_HOURS + ' hrs · FY ' + FISCAL_YEAR);
    const fill = document.getElementById('trainingGoalFill');
    if (fill) fill.style.width = pct + '%';
    const bPct = document.getElementById('bannerGoalPct');
    if (bPct) bPct.textContent = pct + '%';
    const bVer = document.getElementById('bannerVerifiedCount');
    if (bVer) bVer.innerHTML = '<span class="banner-badge-dot banner-badge-dot-success"></span> ' + verified + ' Verified';
    const bPen = document.getElementById('bannerPendingCount');
    if (bPen) bPen.textContent = pending + ' Pending';
    const maxCat = Math.max(catHours.leadership, catHours.technical, catHours.core, 1);
    const setBar = (id, h) => {
        const el = document.getElementById(id);
        if (el) el.style.width = Math.round((h / maxCat) * 100) + '%';
    };
    set('hoursLeadership', catHours.leadership + ' hrs');
    set('hoursTechnical', catHours.technical + ' hrs');
    set('hoursCore', catHours.core + ' hrs');
    setBar('barLeadership', catHours.leadership);
    setBar('barTechnical', catHours.technical);
    setBar('barCore', catHours.core);
}

// ============================================================
// TEXT EXTRACTION
// ============================================================

async function extractPageTextOrdered(pdf, pageNum) {
    const page = await pdf.getPage(pageNum);
    const content = await page.getTextContent();
    const items = content.items
        .filter(it => it.str && String(it.str).trim())
        .map(it => ({
            str: String(it.str).trim(),
            x: it.transform ? it.transform[4] : 0,
            y: it.transform ? it.transform[5] : 0,
        }));
    items.sort((a, b) => {
        const yDiff = b.y - a.y;
        if (Math.abs(yDiff) > 4) return yDiff;
        return a.x - b.x;
    });
    let text = '';
    let lastY = null;
    for (const item of items) {
        if (lastY !== null && Math.abs(item.y - lastY) > 4) {
            text += '\n';
        } else if (text.length && !text.endsWith('\n')) {
            text += ' ';
        }
        text += item.str;
        lastY = item.y;
    }
    return text;
}

async function ocrPdfPage(pdf, pageNum) {
    const page = await pdf.getPage(pageNum);
    const viewport = page.getViewport({ scale: 2.5 });
    const canvas = document.createElement('canvas');
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
    const blob = await new Promise(res => canvas.toBlob(res, 'image/png'));
    return extractTextFromImage(blob);
}

async function extractTextFromPdf(file) {
    const arrayBuffer = await file.arrayBuffer();
    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    const maxPages = Math.min(pdf.numPages, 3);
    let fullText = '';

    for (let i = 1; i <= maxPages; i++) {
        fullText += (await extractPageTextOrdered(pdf, i)) + '\n\n';
    }

    const textLen = fullText.replace(/\s/g, '').length;
    if (textLen < 35) {
        setScanLabel('No readable text — scanning certificate layout...');
        let ocrCombined = '';
        for (let i = 1; i <= maxPages; i++) {
            if (maxPages > 1) setScanLabel('Scanning page ' + i + ' of ' + maxPages + '...');
            ocrCombined += (await ocrPdfPage(pdf, i)) + '\n\n';
        }
        if (ocrCombined.replace(/\s/g, '').length > textLen) {
            fullText = ocrCombined;
        }
    }

    return fullText;
}

async function extractTextFromImage(file) {
    setScanLabel('Running OCR...');
    const url = URL.createObjectURL(file instanceof Blob ? file : new Blob([file]));
    try {
        // Tesseract.js v5 API
        const worker = await Tesseract.createWorker('eng', 1, {
            logger: m => {
                if (m.status === 'recognizing text') {
                    setScanLabel('OCR: ' + Math.round(m.progress * 100) + '%');
                }
            }
        });
        const { data: { text } } = await worker.recognize(url);
        await worker.terminate();
        return text;
    } catch (e) {
        // Fallback: try simple recognize API (v4 compat)
        try {
            const result = await Tesseract.recognize(url, 'eng');
            return result.data.text;
        } catch (e2) {
            console.error('OCR failed:', e2);
            return '';
        }
    } finally {
        URL.revokeObjectURL(url);
    }
}

function autoFillForm(data, filename) {
    const fields = [
        { id: 'trainingTitle',       val: data.title },
        { id: 'trainingConductedBy', val: data.conductedBy },
        { id: 'trainingVenue',       val: data.venue },
        { id: 'trainingHours',       val: data.hours },
        { id: 'trainingDateFrom',    val: data.dateFrom },
        { id: 'trainingDateTo',      val: data.dateTo },
        { id: 'trainingCertNo',      val: data.certNo },
        { id: 'trainingRefDoc',      val: data.refDoc },
    ];
    let filledCount = 0;
    fields.forEach(({ id, val }) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (val) {
            el.value = val;
            el.classList.add('cert-autofilled');
            filledCount++;
        } else {
            el.value = '';
            el.classList.remove('cert-autofilled');
        }
    });

    // Position type dropdown
    if (data.positionType) {
        const sel = document.getElementById('trainingPositionType');
        if (sel) {
            sel.value = data.positionType;
            sel.classList.add('cert-autofilled');
            filledCount++;
        }
    }

    // Update banner
    const banner = document.getElementById('certAutofillBanner');
    const msg    = document.getElementById('certAutofillMsg');
    const meta   = data._meta || {};
    if (filledCount > 0 && !meta.lowConfidence) {
        banner.className = 'cert-autofill-banner cert-autofill-success';
        msg.innerHTML = `<strong>${filledCount} field(s)</strong> auto-filled from your certificate. Review and correct if needed, then submit.`;
    } else if (filledCount > 0 && meta.lowConfidence) {
        banner.className = 'cert-autofill-banner cert-autofill-warn';
        msg.innerHTML = `<strong>${filledCount} field(s)</strong> detected with partial confidence. Please verify all fields before submitting.`;
    } else {
        banner.className = 'cert-autofill-banner cert-autofill-warn';
        msg.textContent = 'Could not extract details from this certificate layout. Please fill in the fields below manually.';
    }

    // File preview
    const isPdf = filename.toLowerCase().endsWith('.pdf');
    document.getElementById('certFileName2').textContent = filename;
    document.getElementById('certFileIcon2').innerHTML = isPdf
        ? '<svg width="32" height="32" fill="none" stroke="#8e1e18" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'
        : '<svg width="32" height="32" fill="none" stroke="#0369a1" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';
}

function goToStep2(filename) {
    document.getElementById('certScanState').style.display = 'none';
    document.getElementById('certStep1').style.display = 'none';
    document.getElementById('certStep2').style.display = '';
    document.getElementById('certSubmitBtn').style.display = '';
    document.getElementById('step1Indicator').classList.remove('active');
    document.getElementById('step1Indicator').classList.add('done');
    document.getElementById('step2Indicator').classList.add('active');
    document.getElementById('modalStepSub').textContent = 'Step 2 of 2 — Review extracted details and submit.';
}

(function initDropZone() {
    const zone = document.getElementById('trainingDropZone');
    if (!zone) return;
    ['dragenter', 'dragover'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.remove('dragover'); });
    });
    zone.addEventListener('drop', e => {
        const input = document.getElementById('trainingCertificate');
        if (e.dataTransfer.files.length) {
            const dt = e.dataTransfer;
            const fileInput = document.getElementById('trainingCertificate');
            // Use DataTransfer to set files
            try {
                fileInput.files = dt.files;
            } catch(ex) {}
            handleTrainingFile({ files: dt.files });
        }
    });
})();

function submitTraining(e) {
    const step2 = document.getElementById('certStep2');
    if (step2 && step2.style.display === 'none') {
        e.preventDefault();
        showTrainingToast('Please upload your certificate first.');
        return;
    }
    const dateFrom     = document.getElementById('trainingDateFrom').value;
    const dateTo       = document.getElementById('trainingDateTo').value;
    const certInput    = document.getElementById('trainingCertificate');
    const positionType = document.getElementById('trainingPositionType').value;
    const refDoc       = document.getElementById('trainingRefDoc').value.trim();
    const title        = document.getElementById('trainingTitle').value.trim();

    if (!title) {
        e.preventDefault();
        showTrainingToast('Please enter the training title.');
        return;
    }
    if (!positionType) {
        e.preventDefault();
        showTrainingToast('Please select a Type of Position.');
        return;
    }
    if (!dateFrom || !dateTo) {
        e.preventDefault();
        showTrainingToast('Please enter inclusive dates.');
        return;
    }
    if (dateTo < dateFrom) {
        e.preventDefault();
        showTrainingToast('Inclusive Date (To) must be on or after the start date.');
        return;
    }
    if (!refDoc) {
        e.preventDefault();
        showTrainingToast('Please enter the Reference Document Number.');
        return;
    }
    if (!certInput || !certInput.files.length) {
        e.preventDefault();
        showTrainingToast('Please attach your Certificate of Completion.');
        return;
    }
    // Valid — allow form POST to backend
}
window.submitTraining = submitTraining;


function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

let _currentCertFile = '';

function viewCertificate(btn) {
    const row = btn.closest('tr');
    if (!row) return;

    const title = row.querySelector('.training-title-text')?.textContent?.trim() || '—';
    const ref = row.dataset.ref || '—';
    const hours = row.querySelector('.training-hours-pill')?.textContent?.trim() || '—';
    const position = row.dataset.position || '—';
    const conductedBy = row.cells[4]?.textContent?.trim() || '—';
    const dateFrom = row.querySelector('.training-date-from')?.textContent?.trim() || '—';
    const dateTo = row.querySelector('.training-date-to')?.textContent?.trim() || '—';
    const filename = btn.dataset.certFile || 'certificate.pdf';
    const isPdf = filename.toLowerCase().endsWith('.pdf');
    const badgeClass = positionBadgeClass[position] || 'technical';

    _currentCertFile = filename;

    document.getElementById('vcTitle').textContent = title;
    document.getElementById('vcSubtitle').textContent = dateFrom + ' – ' + dateTo + ' · ' + hours + ' hrs';
    document.getElementById('vcBadges').innerHTML =
        '<span class="verify-badge verified">Verified</span>' +
        '<span class="type-badge ' + badgeClass + '">' + escapeHtml(position) + '</span>';

    document.getElementById('vcDetails').innerHTML =
        '<div class="modal-row"><span>Inclusive Dates</span><strong>' + escapeHtml(dateFrom) + ' – ' + escapeHtml(dateTo) + '</strong></div>' +
        '<div class="modal-row"><span>Number of Hours</span><strong>' + escapeHtml(hours) + '</strong></div>' +
        '<div class="modal-row"><span>Reference Document</span><strong>' + escapeHtml(ref) + '</strong></div>' +
        '<div class="modal-row training-modal-row-last"><span>Conducted / Sponsored By</span><strong>' + escapeHtml(conductedBy) + '</strong></div>';

    document.getElementById('vcFile').textContent = filename;
    document.getElementById('vcFileIcon').innerHTML = isPdf
        ? '<svg width="40" height="40" fill="none" stroke="#8e1e18" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'
        : '<svg width="40" height="40" fill="none" stroke="#0369a1" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';

    openModal('viewCertModal');
}

const vcDownloadBtn = document.getElementById('vcDownloadBtn');
if (vcDownloadBtn) {
    vcDownloadBtn.addEventListener('click', () => {
        showTrainingToast('Downloading: ' + _currentCertFile);
        closeModal('viewCertModal');
    });
}

function exportToPds() {
    const verified = document.querySelectorAll('#trainingHistoryBody tr[data-status="verified"]').length;
    document.getElementById('pdsRecordCount').textContent = verified + ' verified record(s)';
    openModal('pdsExportModal');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        document.body.style.overflow = '';
    }
});

const goalFillEl = document.getElementById('trainingGoalFill');
if (goalFillEl && goalFillEl.dataset.goalWidth) {
    goalFillEl.style.width = goalFillEl.dataset.goalWidth + '%';
}

['barLeadership', 'barTechnical', 'barCore'].forEach(function (id) {
    const bar = document.getElementById(id);
    if (bar && bar.dataset.barWidth !== undefined) {
        bar.style.width = bar.dataset.barWidth + '%';
    }
});

if (mainEl && mainEl.dataset.flashSuccess === '1') {
    openModal('trainingFlashSuccessModal');
}

recalcAnnualSummary();
filterPermanentTraining();
