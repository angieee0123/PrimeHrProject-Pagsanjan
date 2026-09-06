const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function settingsPost(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    }).then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong. Please try again.');
        }
        return data;
    });
}

window.switchSettingsTab = function (tabId, btn) {
    document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#tab-profile, #tab-security, #tab-notifications, #tab-ai, #tab-theme').forEach(t => t.classList.add('hidden'));
    document.getElementById('tab-' + tabId).classList.remove('hidden');
};

window.toggleSetting = function (btn) {
    btn.classList.toggle('active');
};

function showSavedModal(message, title = 'Settings Saved!') {
    const now = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true }) +
                ', ' + new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('savedTime').textContent = now;
    document.getElementById('savedMsg').textContent = message;
    document.getElementById('savedTitle').textContent = title;
    const modal = document.getElementById('settingsSavedModal');
    modal.style.opacity = '1';
    modal.style.visibility = 'visible';
    document.getElementById('settingsSavedBox').style.transform = 'translateY(0)';
}

window.closeSavedModal = function () {
    const modal = document.getElementById('settingsSavedModal');
    modal.style.opacity = '0';
    modal.style.visibility = 'hidden';
    document.getElementById('settingsSavedBox').style.transform = 'translateY(16px)';
};

// Brief confirmation on the button itself, so the save reads as successful
// even after the modal is dismissed. The `.saved` style already existed in
// adminSettings.css but nothing ever applied it.
function flashSaved(btn, label = 'Saved') {
    if (!btn) return;
    if (btn.dataset.idleLabel === undefined) {
        btn.dataset.idleLabel = btn.textContent.trim();
    }
    clearTimeout(btn.savedTimer);
    btn.classList.add('saved');
    btn.textContent = label;
    btn.savedTimer = setTimeout(() => {
        btn.classList.remove('saved');
        btn.textContent = btn.dataset.idleLabel;
    }, 2200);
}

function showFieldError(elId, message) {
    const el = document.getElementById(elId);
    el.textContent = message;
    el.classList.remove('hidden');
}

function hideFieldError(elId) {
    document.getElementById(elId).classList.add('hidden');
}

// ── Avatar photo ──
function setAvatarDisplay(containerId, initialsId, photoUrl) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = photoUrl
        ? `<img src="${photoUrl}" alt="" class="settings-avatar-img">`
        : (document.getElementById(initialsId)?.outerHTML || container.innerHTML);
}

window.uploadAvatarPhoto = function (input) {
    hideFieldError('avatarMsg');
    const file = input.files ? input.files[0] : null;
    if (!file) return;

    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!allowed.includes(file.type)) {
        showFieldError('avatarMsg', 'Please upload a JPEG, PNG, or WebP image.');
        input.value = '';
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        showFieldError('avatarMsg', 'Image must be 5 MB or smaller.');
        input.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('photo', file);

    fetch('/admin/settings/photo', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: formData,
    })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Failed to upload photo.');
            return data;
        })
        .then(data => {
            setAvatarDisplay('sidebarAvatar', 'sidebarAvatarInitials', data.photo);
            setAvatarDisplay('mainAvatar', 'mainAvatarInitials', data.photo);
            showSavedModal('Your profile photo has been updated.');
        })
        .catch(err => showFieldError('avatarMsg', err.message))
        .finally(() => { input.value = ''; });
};

// ── Profile ──
window.resetProfile = function () {
    const data = window.adminSettingsData || {};
    document.getElementById('settingsEmail').value = data.email || '';
    document.getElementById('settingsContactNo').value = data.contactNumber || '';
    hideFieldError('profileMsg');
};

window.saveProfile = function () {
    hideFieldError('profileMsg');
    const email = document.getElementById('settingsEmail').value.trim();
    const contactNumber = document.getElementById('settingsContactNo').value.trim();

    if (!email) {
        showFieldError('profileMsg', 'Email address is required.');
        return;
    }

    const btn = document.getElementById('profileSaveBtn');
    btn.disabled = true;

    settingsPost('/admin/settings/profile', { email, contact_number: contactNumber })
        .then(() => {
            window.adminSettingsData.email = email;
            window.adminSettingsData.contactNumber = contactNumber;
            flashSaved(btn);
            showSavedModal('Your personal information has been saved successfully.');
        })
        .catch(err => showFieldError('profileMsg', err.message))
        .finally(() => { btn.disabled = false; });
};

// ── Security ──
window.changePassword = function () {
    hideFieldError('pwMsg');
    const current = document.getElementById('currentPw').value;
    const newPw = document.getElementById('newPw').value;
    const confirm = document.getElementById('confirmPw').value;

    if (!current || !newPw || !confirm) {
        showFieldError('pwMsg', 'Please fill in all password fields.');
        return;
    }
    if (newPw.length < 8) {
        showFieldError('pwMsg', 'New password must be at least 8 characters.');
        return;
    }
    if (newPw !== confirm) {
        showFieldError('pwMsg', 'New password and confirmation do not match.');
        return;
    }

    const btn = document.getElementById('pwSaveBtn');
    btn.disabled = true;

    settingsPost('/admin/settings/password', {
        current_password: current,
        new_password: newPw,
        new_password_confirmation: confirm,
    })
        .then(() => {
            document.getElementById('currentPw').value = '';
            document.getElementById('newPw').value = '';
            document.getElementById('confirmPw').value = '';
            flashSaved(btn, 'Password Changed');
            showSavedModal('Your password has been changed successfully.');
        })
        .catch(err => showFieldError('pwMsg', err.message))
        .finally(() => { btn.disabled = false; });
};

// ── Notifications ──
window.saveNotificationPrefs = function () {
    hideFieldError('notifMsg');
    const prefs = {};
    document.querySelectorAll('#tab-notifications .settings-toggle[data-pref]').forEach(toggle => {
        prefs[toggle.dataset.pref] = toggle.classList.contains('active');
    });

    const btn = document.getElementById('notifSaveBtn');
    btn.disabled = true;

    settingsPost('/admin/settings/notifications', prefs)
        .then(() => {
            flashSaved(btn);
            showSavedModal('Your notification preferences have been saved.');
        })
        .catch(err => showFieldError('notifMsg', err.message))
        .finally(() => { btn.disabled = false; });
};

// ── AI / Chatbot ──
window.onAiProviderChange = function () {
    const provider = document.getElementById('aiProvider').value || 'groq';
    const defaults = (window.adminSettingsData || {}).aiDefaultModels || {};
    document.getElementById('aiModel').placeholder = defaults[provider] || '';
};

window.saveAiSettings = function () {
    hideFieldError('aiMsg');
    const provider = document.getElementById('aiProvider').value;
    const model = document.getElementById('aiModel').value.trim();
    const apiKey = document.getElementById('aiApiKey').value.trim();

    const btn = document.getElementById('aiSaveBtn');
    btn.disabled = true;

    settingsPost('/admin/settings/ai', { provider, model, api_key: apiKey || null })
        .then(() => {
            document.getElementById('aiApiKey').value = '';
            flashSaved(btn);
            showSavedModal(provider ? 'Your AI provider settings have been saved.' : 'Reverted to the system default AI provider.');
        })
        .catch(err => showFieldError('aiMsg', err.message))
        .finally(() => { btn.disabled = false; });
};

window.revertAiSettings = function () {
    document.getElementById('aiProvider').value = '';
    document.getElementById('aiModel').value = '';
    document.getElementById('aiApiKey').value = '';
    onAiProviderChange();
    saveAiSettings();
};

// ── AI / Chatbot — System Default (admin-only) ──
window.onSystemAiProviderChange = function () {
    const el = document.getElementById('systemAiProvider');
    if (!el) return;
    const provider = el.value || 'groq';
    const defaults = (window.adminSettingsData || {}).aiDefaultModels || {};
    document.getElementById('systemAiModel').placeholder = defaults[provider] || '';
};

window.saveSystemAiSettings = function () {
    hideFieldError('systemAiMsg');
    const provider = document.getElementById('systemAiProvider').value;
    const model = document.getElementById('systemAiModel').value.trim();
    const apiKey = document.getElementById('systemAiApiKey').value.trim();

    const btn = document.getElementById('systemAiSaveBtn');
    btn.disabled = true;

    settingsPost('/admin/settings/system-ai', { provider, model, api_key: apiKey || null })
        .then(() => {
            document.getElementById('systemAiApiKey').value = '';
            flashSaved(btn);
            showSavedModal(provider ? 'The system default AI settings have been saved.' : 'System default cleared.');
        })
        .catch(err => showFieldError('systemAiMsg', err.message))
        .finally(() => { btn.disabled = false; });
};

// ── Citizen's Charter knowledge base (admin-only) ──
window.uploadCharter = function () {
    hideFieldError('charterMsg');
    const input = document.getElementById('charterFileInput');
    const file = input && input.files ? input.files[0] : null;

    if (!file) {
        showFieldError('charterMsg', 'Choose a PDF or DOCX file first.');
        return;
    }

    const name = (file.name || '').toLowerCase();
    if (!name.endsWith('.pdf') && !name.endsWith('.docx')) {
        showFieldError('charterMsg', 'Only PDF or DOCX files are accepted.');
        input.value = '';
        return;
    }

    const btn = document.getElementById('charterSaveBtn');
    btn.disabled = true;

    const formData = new FormData();
    formData.append('charter', file);

    fetch('/admin/settings/charter', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: formData,
    })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Failed to import the charter.');
            return data;
        })
        .then(data => {
            input.value = '';
            flashSaved(btn, 'Imported');
            refreshCharterStatus(data.charter);
            showSavedModal(data.message || 'Citizen\'s Charter imported.');
        })
        .catch(err => showFieldError('charterMsg', err.message))
        .finally(() => { btn.disabled = false; });
};

window.removeCharter = function () {
    hideFieldError('charterMsg');

    const btn = document.getElementById('charterRemoveBtn');
    if (btn) btn.disabled = true;

    fetch('/admin/settings/charter', {
        method: 'DELETE',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Failed to remove the charter.');
            return data;
        })
        .then(data => {
            const input = document.getElementById('charterFileInput');
            if (input) input.value = '';
            refreshCharterStatus(data.charter);
            showSavedModal(data.message || 'Citizen\'s Charter removed.');
        })
        .catch(err => showFieldError('charterMsg', err.message))
        .finally(() => { if (btn) btn.disabled = false; });
};

function refreshCharterStatus(charter) {
    if (!charter) return;
    const nameEl = document.getElementById('charterFileName');
    const metaEl = document.getElementById('charterFileMeta');
    const badgeEl = document.getElementById('charterStatusBadge');
    if (nameEl) nameEl.textContent = charter.exists ? (charter.name || 'Charter file') : 'No file imported';
    if (metaEl) metaEl.textContent = charter.message || '';
    if (badgeEl) badgeEl.textContent = charter.usable ? 'Active' : 'Needs attention';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSavedModal(); });

// Appearance now lives in resources/js/shared/appearance.js — the employee
// settings page needs the same picker, and the admin page shows two of them.
