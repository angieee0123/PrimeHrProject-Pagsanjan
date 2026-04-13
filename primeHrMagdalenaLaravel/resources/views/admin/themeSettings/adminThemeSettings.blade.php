<!-- Theme Settings FAB Button -->
<button class="theme-fab" onclick="toggleThemeSettings()" title="Theme Settings">
    <svg class="theme-fab-icon-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>
    <svg class="theme-fab-icon-close" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: none;">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
</button>

<!-- Theme Settings Panel -->
<div class="theme-settings-panel" id="themeSettingsPanel" style="display: none;">
    <div class="theme-panel-header">
        <div>
            <h3 class="theme-panel-title">Theme Settings</h3>
            <p class="theme-panel-sub">Customize your dashboard appearance</p>
        </div>
        <button class="theme-panel-close" onclick="toggleThemeSettings()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="theme-panel-body">
        <!-- Mode Selection -->
        <div class="theme-section">
            <label class="theme-section-label">Display Mode</label>
            <div class="theme-mode-grid">
                <button class="theme-mode-btn active" data-mode="light" onclick="setThemeMode('light')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <line x1="12" y1="1" x2="12" y2="3"/>
                        <line x1="12" y1="21" x2="12" y2="23"/>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/>
                        <line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <span>Light</span>
                </button>
                <button class="theme-mode-btn" data-mode="dark" onclick="setThemeMode('dark')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <span>Dark</span>
                </button>
            </div>
        </div>

        <!-- Color Theme Selection -->
        <div class="theme-section">
            <label class="theme-section-label">Accent Color</label>
            <div class="theme-color-grid">
                <button class="theme-color-btn active" data-theme="default" onclick="setColorTheme('default')" title="Default Blue">
                    <span class="theme-color-preview" style="background: linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%);"></span>
                    <span class="theme-color-name">Blue</span>
                </button>
                <button class="theme-color-btn" data-theme="green" onclick="setColorTheme('green')" title="Green">
                    <span class="theme-color-preview" style="background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);"></span>
                    <span class="theme-color-name">Green</span>
                </button>
                <button class="theme-color-btn" data-theme="red" onclick="setColorTheme('red')" title="Red">
                    <span class="theme-color-preview" style="background: linear-gradient(135deg, #8e1e18 0%, #dc2626 100%);"></span>
                    <span class="theme-color-name">Red</span>
                </button>
                <button class="theme-color-btn" data-theme="purple" onclick="setColorTheme('purple')" title="Purple">
                    <span class="theme-color-preview" style="background: linear-gradient(135deg, #6b3fa0 0%, #8b5cf6 100%);"></span>
                    <span class="theme-color-name">Purple</span>
                </button>
                <button class="theme-color-btn" data-theme="orange" onclick="setColorTheme('orange')" title="Orange">
                    <span class="theme-color-preview" style="background: linear-gradient(135deg, #c2410c 0%, #f97316 100%);"></span>
                    <span class="theme-color-name">Orange</span>
                </button>
                <button class="theme-color-btn" data-theme="teal" onclick="setColorTheme('teal')" title="Teal">
                    <span class="theme-color-preview" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);"></span>
                    <span class="theme-color-name">Teal</span>
                </button>
            </div>
        </div>

        <!-- Sidebar Colors -->
        <div class="theme-section">
            <label class="theme-section-label">Sidebar Style</label>
            <div class="theme-nav-grid">
                <button class="theme-sidebar-btn active" data-sidebar="blue" onclick="setSidebarTheme('blue')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #0b044d 0%, #2d1a8e 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Blue</span>
                </button>
                <button class="theme-sidebar-btn" data-sidebar="dark" onclick="setSidebarTheme('dark')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Dark</span>
                </button>
                <button class="theme-sidebar-btn" data-sidebar="light" onclick="setSidebarTheme('light')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #ffffff 0%, #f5f5f5 100%); border: 1px solid #e8e7f5;"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Light</span>
                </button>
                <button class="theme-sidebar-btn" data-sidebar="green" onclick="setSidebarTheme('green')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #15803d 0%, #22c55e 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Green</span>
                </button>
                <button class="theme-sidebar-btn" data-sidebar="purple" onclick="setSidebarTheme('purple')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #6b3fa0 0%, #8b5cf6 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Purple</span>
                </button>
                <button class="theme-sidebar-btn" data-sidebar="teal" onclick="setSidebarTheme('teal')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #0f766e 0%, #14b8a6 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Teal</span>
                </button>
            </div>
        </div>

        <!-- Topbar Colors -->
        <div class="theme-section">
            <label class="theme-section-label">Topbar Style</label>
            <div class="theme-nav-grid">
                <button class="theme-topbar-btn active" data-topbar="blue" onclick="setTopbarTheme('blue')">
                    <div class="theme-nav-preview topbar-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #0b044d 0%, #2d1a8e 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Blue</span>
                </button>
                <button class="theme-topbar-btn" data-topbar="dark" onclick="setTopbarTheme('dark')">
                    <div class="theme-nav-preview topbar-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Dark</span>
                </button>
                <button class="theme-topbar-btn" data-topbar="light" onclick="setTopbarTheme('light')">
                    <div class="theme-nav-preview topbar-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #ffffff 0%, #f5f5f5 100%); border: 1px solid #e8e7f5;"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Light</span>
                </button>
                <button class="theme-topbar-btn" data-topbar="green" onclick="setTopbarTheme('green')">
                    <div class="theme-nav-preview topbar-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #15803d 0%, #22c55e 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Green</span>
                </button>
                <button class="theme-topbar-btn" data-topbar="purple" onclick="setTopbarTheme('purple')">
                    <div class="theme-nav-preview topbar-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #6b3fa0 0%, #8b5cf6 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Purple</span>
                </button>
                <button class="theme-topbar-btn" data-topbar="teal" onclick="setTopbarTheme('teal')">
                    <div class="theme-nav-preview topbar-preview">
                        <div class="theme-nav-bar" style="background: linear-gradient(180deg, #0f766e 0%, #14b8a6 100%);"></div>
                        <div class="theme-nav-content"></div>
                    </div>
                    <span class="theme-color-name">Teal</span>
                </button>
            </div>
        </div>

        <!-- Body/Background Colors -->
        <div class="theme-section">
            <label class="theme-section-label">Background Style</label>
            <div class="theme-nav-grid">
                <button class="theme-body-btn active" data-body="default" onclick="setBodyTheme('default')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-content" style="background: #f7f6ff;"></div>
                    </div>
                    <span class="theme-color-name">Default</span>
                </button>
                <button class="theme-body-btn" data-body="light" onclick="setBodyTheme('light')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-content" style="background: #ffffff;"></div>
                    </div>
                    <span class="theme-color-name">Light</span>
                </button>
                <button class="theme-body-btn" data-body="gray" onclick="setBodyTheme('gray')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-content" style="background: #f5f5f5;"></div>
                    </div>
                    <span class="theme-color-name">Gray</span>
                </button>
                <button class="theme-body-btn" data-body="warm" onclick="setBodyTheme('warm')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-content" style="background: #fef9f3;"></div>
                    </div>
                    <span class="theme-color-name">Warm</span>
                </button>
                <button class="theme-body-btn" data-body="cool" onclick="setBodyTheme('cool')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-content" style="background: #f0f9ff;"></div>
                    </div>
                    <span class="theme-color-name">Cool</span>
                </button>
                <button class="theme-body-btn" data-body="dark" onclick="setBodyTheme('dark')">
                    <div class="theme-nav-preview">
                        <div class="theme-nav-content" style="background: #1a1a2e;"></div>
                    </div>
                    <span class="theme-color-name">Dark</span>
                </button>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="theme-section">
            <button class="theme-reset-btn" onclick="resetTheme()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                Reset to Default
            </button>
        </div>
    </div>
</div>

<script>
// Theme color mappings
const colorThemes = {
    default: {
        primary: '#0b044d',
        primaryLight: '#2d1a8e',
        primaryBg: '#f0effe'
    },
    green: {
        primary: '#15803d',
        primaryLight: '#22c55e',
        primaryBg: '#e8f9ef'
    },
    red: {
        primary: '#8e1e18',
        primaryLight: '#dc2626',
        primaryBg: '#fee8e8'
    },
    purple: {
        primary: '#6b3fa0',
        primaryLight: '#8b5cf6',
        primaryBg: '#f3e8ff'
    },
    orange: {
        primary: '#c2410c',
        primaryLight: '#f97316',
        primaryBg: '#ffedd5'
    },
    teal: {
        primary: '#0f766e',
        primaryLight: '#14b8a6',
        primaryBg: '#ccfbf1'
    }
};

const sidebarThemes = {
    blue: {
        bg: 'linear-gradient(180deg, #0b044d 0%, #2d1a8e 100%)',
        text: '#ffffff',
        activeText: '#ffffff',
        activeBg: 'rgba(255, 255, 255, 0.1)',
        border: 'rgba(255, 255, 255, 0.1)',
        hoverBg: 'rgba(255, 255, 255, 0.08)',
        hoverText: '#ffffff'
    },
    dark: {
        bg: 'linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%)',
        text: '#e5e5e5',
        activeText: '#ffffff',
        activeBg: 'rgba(255, 255, 255, 0.1)',
        border: 'rgba(255, 255, 255, 0.1)',
        hoverBg: 'rgba(255, 255, 255, 0.08)',
        hoverText: '#ffffff'
    },
    light: {
        bg: 'linear-gradient(180deg, #ffffff 0%, #f5f5f5 100%)',
        text: '#333333',
        activeText: '#0b044d',
        activeBg: '#f0effe',
        border: '#e8e7f5',
        hoverBg: '#f7f6ff',
        hoverText: '#0b044d'
    },
    green: {
        bg: 'linear-gradient(180deg, #15803d 0%, #22c55e 100%)',
        text: '#ffffff',
        activeText: '#ffffff',
        activeBg: 'rgba(255, 255, 255, 0.15)',
        border: 'rgba(255, 255, 255, 0.1)',
        hoverBg: 'rgba(255, 255, 255, 0.08)',
        hoverText: '#ffffff'
    },
    purple: {
        bg: 'linear-gradient(180deg, #6b3fa0 0%, #8b5cf6 100%)',
        text: '#ffffff',
        activeText: '#ffffff',
        activeBg: 'rgba(255, 255, 255, 0.15)',
        border: 'rgba(255, 255, 255, 0.1)',
        hoverBg: 'rgba(255, 255, 255, 0.08)',
        hoverText: '#ffffff'
    },
    teal: {
        bg: 'linear-gradient(180deg, #0f766e 0%, #14b8a6 100%)',
        text: '#ffffff',
        activeText: '#ffffff',
        activeBg: 'rgba(255, 255, 255, 0.15)',
        border: 'rgba(255, 255, 255, 0.1)',
        hoverBg: 'rgba(255, 255, 255, 0.08)',
        hoverText: '#ffffff'
    }
};

const topbarThemes = {
    blue: {
        bg: 'linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%)',
        text: '#ffffff',
        border: 'rgba(255, 255, 255, 0.1)',
        subtitleText: 'rgba(255, 255, 255, 0.7)',
        iconBg: 'rgba(255, 255, 255, 0.1)',
        iconText: '#ffffff',
        iconHoverBg: 'rgba(255, 255, 255, 0.2)'
    },
    dark: {
        bg: 'linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%)',
        text: '#e5e5e5',
        border: 'rgba(255, 255, 255, 0.1)',
        subtitleText: 'rgba(255, 255, 255, 0.6)',
        iconBg: 'rgba(255, 255, 255, 0.1)',
        iconText: '#e5e5e5',
        iconHoverBg: 'rgba(255, 255, 255, 0.2)'
    },
    light: {
        bg: '#ffffff',
        text: '#0b044d',
        border: '#f0effe',
        subtitleText: '#9999bb',
        iconBg: '#f7f6ff',
        iconText: '#6b6a8a',
        iconHoverBg: '#e8e7f5'
    },
    green: {
        bg: 'linear-gradient(180deg, #15803d 0%, #22c55e 100%)',
        text: '#ffffff',
        border: 'rgba(255, 255, 255, 0.1)',
        subtitleText: 'rgba(255, 255, 255, 0.7)',
        iconBg: 'rgba(255, 255, 255, 0.1)',
        iconText: '#ffffff',
        iconHoverBg: 'rgba(255, 255, 255, 0.2)'
    },
    purple: {
        bg: 'linear-gradient(180deg, #6b3fa0 0%, #8b5cf6 100%)',
        text: '#ffffff',
        border: 'rgba(255, 255, 255, 0.1)',
        subtitleText: 'rgba(255, 255, 255, 0.7)',
        iconBg: 'rgba(255, 255, 255, 0.1)',
        iconText: '#ffffff',
        iconHoverBg: 'rgba(255, 255, 255, 0.2)'
    },
    teal: {
        bg: 'linear-gradient(180deg, #0f766e 0%, #14b8a6 100%)',
        text: '#ffffff',
        border: 'rgba(255, 255, 255, 0.1)',
        subtitleText: 'rgba(255, 255, 255, 0.7)',
        iconBg: 'rgba(255, 255, 255, 0.1)',
        iconText: '#ffffff',
        iconHoverBg: 'rgba(255, 255, 255, 0.2)'
    }
};

const bodyThemes = {
    default: {
        bg: '#f7f6ff',
        cardBg: '#ffffff',
        cardBorder: '#f0effe',
        textColor: '#0b044d',
        isDark: false
    },
    light: {
        bg: '#ffffff',
        cardBg: '#fafafa',
        cardBorder: '#e8e7f5',
        textColor: '#0b044d',
        isDark: false
    },
    gray: {
        bg: '#f5f5f5',
        cardBg: '#ffffff',
        cardBorder: '#e0e0e0',
        textColor: '#0b044d',
        isDark: false
    },
    warm: {
        bg: '#fef9f3',
        cardBg: '#ffffff',
        cardBorder: '#f5e6d3',
        textColor: '#0b044d',
        isDark: false
    },
    cool: {
        bg: '#f0f9ff',
        cardBg: '#ffffff',
        cardBorder: '#dbeafe',
        textColor: '#0b044d',
        isDark: false
    },
    dark: {
        bg: '#1a1a2e',
        cardBg: '#16213e',
        cardBorder: '#2a3a5a',
        textColor: '#e8e7f5',
        isDark: true
    }
};

function toggleThemeSettings() {
    const panel = document.getElementById('themeSettingsPanel');
    const fab = document.querySelector('.theme-fab');
    const openIcon = document.querySelector('.theme-fab-icon-open');
    const closeIcon = document.querySelector('.theme-fab-icon-close');

    if (panel.style.display === 'none') {
        panel.style.display = 'flex';
        fab.classList.add('open');
        openIcon.style.display = 'none';
        closeIcon.style.display = 'block';
    } else {
        panel.style.display = 'none';
        fab.classList.remove('open');
        openIcon.style.display = 'block';
        closeIcon.style.display = 'none';
    }
}

function setThemeMode(mode) {
    // Update active button
    document.querySelectorAll('.theme-mode-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const selectedBtn = document.querySelector(`.theme-mode-btn[data-mode="${mode}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    // Apply theme mode
    if (mode === 'dark') {
        document.body.classList.add('dark-mode');
        // Keep body background from body theme if set
        const savedBody = localStorage.getItem('bodyTheme');
        if (!savedBody || savedBody === 'default') {
            document.body.style.background = '#1a1a2e';
            document.body.style.color = '#e8e7f5';
        } else {
            const colors = bodyThemes[savedBody];
            if (colors && !colors.isDark) {
                document.body.style.color = '#e8e7f5';
            }
        }
    } else {
        document.body.classList.remove('dark-mode');
        // Restore body background from body theme
        const savedBody = localStorage.getItem('bodyTheme') || 'default';
        const colors = bodyThemes[savedBody];
        if (colors) {
            document.body.style.background = colors.bg;
            document.body.style.color = colors.textColor;
        }
    }

    // Save to localStorage
    localStorage.setItem('themeMode', mode);
}

function setColorTheme(theme) {
    // Update active button
    document.querySelectorAll('.theme-color-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const selectedBtn = document.querySelector(`.theme-color-btn[data-theme="${theme}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    // Apply color theme
    const colors = colorThemes[theme];
    if (colors) {
        document.documentElement.style.setProperty('--primary-color', colors.primary);
        document.documentElement.style.setProperty('--primary-light', colors.primaryLight);
        document.documentElement.style.setProperty('--primary-bg', colors.primaryBg);

        // Save to localStorage
        localStorage.setItem('colorTheme', theme);
    }
}

function setSidebarTheme(theme) {
    document.querySelectorAll('.theme-sidebar-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const selectedBtn = document.querySelector(`.theme-sidebar-btn[data-sidebar="${theme}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    const colors = sidebarThemes[theme];
    if (colors) {
        document.documentElement.style.setProperty('--sidebar-bg', colors.bg);
        document.documentElement.style.setProperty('--sidebar-text', colors.text);
        document.documentElement.style.setProperty('--sidebar-active-text', colors.activeText);
        document.documentElement.style.setProperty('--sidebar-active-bg', colors.activeBg);
        document.documentElement.style.setProperty('--sidebar-border', colors.border);
        document.documentElement.style.setProperty('--sidebar-hover-bg', colors.hoverBg);
        document.documentElement.style.setProperty('--sidebar-hover-text', colors.hoverText);

        localStorage.setItem('sidebarTheme', theme);
    }
}

function setTopbarTheme(theme) {
    document.querySelectorAll('.theme-topbar-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const selectedBtn = document.querySelector(`.theme-topbar-btn[data-topbar="${theme}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    const colors = topbarThemes[theme];
    if (colors) {
        document.documentElement.style.setProperty('--topbar-bg', colors.bg);
        document.documentElement.style.setProperty('--topbar-text', colors.text);
        document.documentElement.style.setProperty('--topbar-border', colors.border);
        document.documentElement.style.setProperty('--topbar-subtitle-text', colors.subtitleText);
        document.documentElement.style.setProperty('--topbar-icon-bg', colors.iconBg);
        document.documentElement.style.setProperty('--topbar-icon-text', colors.iconText);
        document.documentElement.style.setProperty('--topbar-icon-hover-bg', colors.iconHoverBg);

        localStorage.setItem('topbarTheme', theme);
    }
}

function resetTheme() {
    setThemeMode('light');
    setColorTheme('default');
    setSidebarTheme('blue');
    setTopbarTheme('blue');
    setBodyTheme('default');
    localStorage.removeItem('themeMode');
    localStorage.removeItem('colorTheme');
    localStorage.removeItem('sidebarTheme');
    localStorage.removeItem('topbarTheme');
    localStorage.removeItem('bodyTheme');
}

function setBodyTheme(theme) {
    document.querySelectorAll('.theme-body-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const selectedBtn = document.querySelector(`.theme-body-btn[data-body="${theme}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    const colors = bodyThemes[theme];
    if (colors) {
        document.body.style.background = colors.bg;
        document.body.style.color = colors.textColor;
        document.documentElement.style.setProperty('--body-bg', colors.bg);
        document.documentElement.style.setProperty('--card-bg', colors.cardBg);
        document.documentElement.style.setProperty('--card-border', colors.cardBorder);

        // Apply dark mode styling for dark backgrounds
        if (colors.isDark) {
            document.body.classList.add('dark-mode');
        } else {
            // Check if Display Mode is set to dark
            const savedMode = localStorage.getItem('themeMode');
            if (savedMode !== 'dark') {
                document.body.classList.remove('dark-mode');
            }
        }

        localStorage.setItem('bodyTheme', theme);
    }
}

// Load saved theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedMode = localStorage.getItem('themeMode') || 'light';
    const savedTheme = localStorage.getItem('colorTheme') || 'default';
    const savedSidebar = localStorage.getItem('sidebarTheme') || 'blue';
    const savedTopbar = localStorage.getItem('topbarTheme') || 'blue';
    const savedBody = localStorage.getItem('bodyTheme') || 'default';

    setThemeMode(savedMode);
    setColorTheme(savedTheme);
    setSidebarTheme(savedSidebar);
    setTopbarTheme(savedTopbar);
    setBodyTheme(savedBody);
});
</script>
