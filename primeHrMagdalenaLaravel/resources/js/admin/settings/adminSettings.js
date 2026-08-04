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

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSavedModal(); });

// ── Theme ──
let _selectedTheme = (window.adminSettingsData || {}).activeTheme || 'default';
let _customColor   = (window.adminSettingsData || {}).customThemeColor || '#6366f1';

// Palette default vars keyed by palette key for populating the override pickers
const _paletteDefaults = {
    default:  { secondary: '#1b1464', accent: '#0b044d', muted: '#9999bb' },
    emerald:  { secondary: '#065f46', accent: '#059669', muted: '#6ee7b7' },
    crimson:  { secondary: '#991b1b', accent: '#dc2626', muted: '#f87171' },
    violet:   { secondary: '#5b21b6', accent: '#7c3aed', muted: '#a78bfa' },
    slate:    { secondary: '#334155', accent: '#475569', muted: '#94a3b8' },
    amber:    { secondary: '#92400e', accent: '#d97706', muted: '#fbbf24' },
    custom:   { secondary: '#6366f1', accent: '#6366f1', muted: '#a5b4fc' },
};

function _getOverrides() {
    return {
        secondary: document.getElementById('override-secondary')?.value || null,
        accent:    document.getElementById('override-accent')?.value    || null,
        muted:     document.getElementById('override-muted')?.value     || null,
    };
}

function _populateOverridePickers(key, savedSecondary, savedAccent, savedMuted) {
    const defaults = _paletteDefaults[key] || _paletteDefaults.default;
    const s = document.getElementById('override-secondary');
    const a = document.getElementById('override-accent');
    const m = document.getElementById('override-muted');
    if (s) s.value = savedSecondary || defaults.secondary;
    if (a) a.value = savedAccent    || defaults.accent;
    if (m) m.value = savedMuted     || defaults.muted;
}

window.resetThemeOverrides = function () {
    const defaults = _paletteDefaults[_selectedTheme] || _paletteDefaults.default;
    const s = document.getElementById('override-secondary');
    const a = document.getElementById('override-accent');
    const m = document.getElementById('override-muted');
    if (s) s.value = defaults.secondary;
    if (a) a.value = defaults.accent;
    if (m) m.value = defaults.muted;
};

window.onCustomColorChange = function (hex) {
    _customColor = hex;
    const preview = document.getElementById('custom-color-preview');
    if (preview) preview.style.background = hex;
    selectTheme('custom');
};

window.selectTheme = function (key) {
    _selectedTheme = key;
    const data = window.adminSettingsData || {};
    // Only restore saved overrides when re-selecting the already-active theme
    const isActive = key === data.activeTheme;
    _populateOverridePickers(
        key,
        isActive ? data.themeSecondary : null,
        isActive ? data.themeAccent    : null,
        isActive ? data.themeMuted     : null
    );
    document.querySelectorAll('[id^="theme-card-"]').forEach(card => {
        const cardActive = card.id === 'theme-card-' + key;
        const preview    = card.querySelector('div');
        const color      = card.id === 'theme-card-custom'
            ? (_customColor || '#6366f1')
            : (preview ? preview.style.background : '#0b044d');
        card.style.borderColor = cardActive ? color : '#e5e7eb';
        card.style.background  = cardActive ? '#fafafe' : '#fff';
        let check = card.querySelector('.theme-check');
        if (cardActive && !check) {
            check = document.createElement('span');
            check.className = 'theme-check';
            check.style.cssText = 'position:absolute;top:10px;right:10px;width:20px;height:20px;border-radius:50%;background:' + color + ';display:flex;align-items:center;justify-content:center';
            check.innerHTML = '<svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
            card.appendChild(check);
        } else if (!cardActive && check) {
            check.remove();
        }
    });
};

window.saveTheme = function () {
    const btn = document.getElementById('themeSaveBtn');
    const msg = document.getElementById('themeMsg');
    msg.classList.add('hidden');
    btn.disabled = true;
    btn.textContent = 'Applying...';
    const data = window.adminSettingsData || {};
    const overrides = _getOverrides();
    const body = {
        theme:     _selectedTheme,
        secondary: overrides.secondary,
        accent:    overrides.accent,
        muted:     overrides.muted,
    };
    if (_selectedTheme === 'custom') body.custom_color = _customColor;
    fetch(data.themeRoute, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': data.csrfToken },
        body: JSON.stringify(body),
    })
    .then(async r => {
        const json = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(json.message || 'Failed to save theme.');
        return json;
    })
    .then(() => { location.reload(); })
    .catch(err => { msg.textContent = err.message || 'Failed to save theme.'; msg.classList.remove('hidden'); })
    .finally(() => { btn.disabled = false; btn.textContent = 'Apply Theme'; });
};

// Populate pickers on page load with saved overrides
document.addEventListener('DOMContentLoaded', () => {
    const data = window.adminSettingsData || {};
    _populateOverridePickers(
        data.activeTheme || 'default',
        data.themeSecondary,
        data.themeAccent,
        data.themeMuted
    );
});
