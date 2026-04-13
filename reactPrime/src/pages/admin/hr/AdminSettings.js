import { useState } from 'react';
import { Icon } from '../../../components/Icons';
import './AdminSettings.css';

const TABS = [
  { id: 'profile',       label: 'Profile',          icon: 'user' },
  { id: 'system',        label: 'System',           icon: 'settings' },
  { id: 'security',      label: 'Security',         icon: 'shield' },
  { id: 'notifications', label: 'Notifications',    icon: 'bell' },
  { id: 'requests',      label: 'Account Requests', icon: 'clipboard' },
];

function Section({ title, children }) {
  return (
    <div className="settings-section">
      <h3 className="settings-section-title">{title}</h3>
      <div className="settings-section-content">
        {children}
      </div>
    </div>
  );
}

function Row({ label, desc, children }) {
  return (
    <div className="settings-row">
      <div className="settings-row-label">
        <p className="settings-row-title">{label}</p>
        {desc && <p className="settings-row-desc">{desc}</p>}
      </div>
      <div className="settings-row-control">{children}</div>
    </div>
  );
}

function Toggle({ value, onChange }) {
  return (
    <button onClick={() => onChange(!value)} className={`settings-toggle ${value ? 'active' : ''}`}>
      <span className="settings-toggle-thumb" />
    </button>
  );
}

function SaveBar({ onSave, onReset }) {
  const [saved, setSaved] = useState(false);
  const handleSave = () => {
    onSave();
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  };
  return (
    <div className="settings-save-bar">
      <button onClick={onReset} className="settings-btn-reset">Reset</button>
      <button onClick={handleSave} className={`settings-btn-save ${saved ? 'saved' : ''}`}>
        {saved
          ? <><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg> Saved!</>
          : <><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Changes</>
        }
      </button>
    </div>
  );
}

function ProfileTab() {
  const init = { firstName: 'Admin', lastName: 'User', email: 'admin@pagsanjan.gov.ph', contact: '09171234567', role: 'HR Staff', dept: 'Human Resource Management Office' };
  const [form, setForm] = useState(init);
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div>
      <Section title="Account Information">
        <div className="settings-form-wrapper">
          <div className="settings-avatar-row">
            <div className="settings-avatar">AD</div>
            <div className="settings-avatar-info">
              <p className="settings-avatar-name">Admin User</p>
              <p className="settings-avatar-role">HR Staff · Human Resource Management Office</p>
            </div>
          </div>
          <div className="settings-form-grid">
            {[['First Name', 'firstName'], ['Last Name', 'lastName'], ['Email Address', 'email'], ['Contact No.', 'contact']].map(([label, key]) => (
              <div key={key} className="settings-form-field">
                <label>{label}</label>
                <input value={form[key]} onChange={e => set(key, e.target.value)} />
              </div>
            ))}
            <div className="settings-form-field settings-form-field-full">
              <label>Department / Office</label>
              <input value={form.dept} onChange={e => set('dept', e.target.value)} />
            </div>
          </div>
          <SaveBar onSave={() => {}} onReset={() => setForm(init)} />
        </div>
      </Section>
    </div>
  );
}

function SystemTab() {
  const init = { fiscalYear: '2025', payPeriod: 'Monthly', currency: 'PHP', dateFormat: 'MM/DD/YYYY' };
  const [cfg, setCfg] = useState(init);
  const set = (k, v) => setCfg(c => ({ ...c, [k]: v }));

  return (
    <div>
      <Section title="General Settings">
        <Row label="Fiscal Year" desc="Current active fiscal year">
          <select value={cfg.fiscalYear} onChange={e => set('fiscalYear', e.target.value)} className="settings-select">
            <option>2024</option><option>2025</option><option>2026</option>
          </select>
        </Row>
        <Row label="Pay Period" desc="Default payroll cycle">
          <select value={cfg.payPeriod} onChange={e => set('payPeriod', e.target.value)} className="settings-select">
            <option>Monthly</option><option>Semi-Monthly</option><option>Weekly</option>
          </select>
        </Row>
        <Row label="Currency" desc="Display currency for payroll">
          <select value={cfg.currency} onChange={e => set('currency', e.target.value)} className="settings-select">
            <option>PHP</option><option>USD</option>
          </select>
        </Row>
        <Row label="Date Format" desc="How dates are displayed">
          <select value={cfg.dateFormat} onChange={e => set('dateFormat', e.target.value)} className="settings-select">
            <option>MM/DD/YYYY</option><option>DD/MM/YYYY</option><option>YYYY-MM-DD</option>
          </select>
        </Row>
        <div className="settings-form-wrapper">
          <SaveBar onSave={() => {}} onReset={() => setCfg(init)} />
        </div>
      </Section>
    </div>
  );
}

function SecurityTab() {
  const init = { current: '', newPw: '', confirm: '' };
  const [pw, setPw] = useState(init);
  const [twoFA, setTwoFA] = useState(false);
  const [sessionTimeout, setSessionTimeout] = useState('30');
  const [msg, setMsg] = useState('');
  const set = (k, v) => setPw(p => ({ ...p, [k]: v }));

  const handleChange = () => {
    if (!pw.current || !pw.newPw || !pw.confirm) { setMsg('Please fill in all fields.'); return; }
    if (pw.newPw !== pw.confirm) { setMsg('New passwords do not match.'); return; }
    if (pw.newPw.length < 8) { setMsg('Password must be at least 8 characters.'); return; }
    setMsg('✓ Password changed successfully.');
    setPw(init);
    setTimeout(() => setMsg(''), 3000);
  };

  return (
    <div>
      <Section title="Change Password">
        <div className="settings-form-wrapper">
          {['current', 'newPw', 'confirm'].map((key, i) => (
            <div key={key} className="settings-form-field settings-form-field-full">
              <label>{['Current Password', 'New Password', 'Confirm New Password'][i]}</label>
              <input type="password" value={pw[key]} onChange={e => set(key, e.target.value)} placeholder="••••••••" />
            </div>
          ))}
          {msg && <p className={`settings-message ${msg.startsWith('✓') ? 'success' : 'error'}`}>{msg}</p>}
          <button onClick={handleChange} className="settings-btn-primary">Change Password</button>
        </div>
      </Section>

      <Section title="Access Control">
        <Row label="Two-Factor Authentication" desc="Require OTP on login">
          <Toggle value={twoFA} onChange={setTwoFA} />
        </Row>
        <Row label="Session Timeout" desc="Auto-logout after inactivity">
          <select value={sessionTimeout} onChange={e => setSessionTimeout(e.target.value)} className="settings-select">
            <option value="15">15 minutes</option>
            <option value="30">30 minutes</option>
            <option value="60">1 hour</option>
            <option value="120">2 hours</option>
          </select>
        </Row>
      </Section>
    </div>
  );
}

function NotificationsTab() {
  const init = { payrollApproval: true, leaveRequest: true, newEmployee: true, dtrDeadline: true, systemAlert: false, emailDigest: true };
  const [prefs, setPrefs] = useState(init);
  const toggle = k => setPrefs(p => ({ ...p, [k]: !p[k] }));

  const items = [
    { key: 'payrollApproval', label: 'Payroll Approval',    desc: 'Notify when payroll records need approval' },
    { key: 'leaveRequest',    label: 'Leave Requests',      desc: 'Notify when employees file leave requests' },
    { key: 'newEmployee',     label: 'New Employee',        desc: 'Notify when a new employee account is created' },
    { key: 'dtrDeadline',     label: 'DTR Deadline',        desc: 'Remind before DTR submission deadline' },
    { key: 'systemAlert',     label: 'System Alerts',       desc: 'Critical system and security alerts' },
    { key: 'emailDigest',     label: 'Email Digest',        desc: 'Receive a daily summary via email' },
  ];

  return (
    <div>
      <Section title="In-App Notifications">
        {items.slice(0, 5).map(item => (
          <Row key={item.key} label={item.label} desc={item.desc}>
            <Toggle value={prefs[item.key]} onChange={() => toggle(item.key)} />
          </Row>
        ))}
      </Section>
      <Section title="Email Notifications">
        <Row label={items[5].label} desc={items[5].desc}>
          <Toggle value={prefs.emailDigest} onChange={() => toggle('emailDigest')} />
        </Row>
        <div className="settings-form-wrapper">
          <SaveBar onSave={() => {}} onReset={() => setPrefs(init)} />
        </div>
      </Section>
    </div>
  );
}

function RequestsTab({ requests, onApprove, onReject }) {
  const pending  = requests.filter(r => r.status === 'pending');
  const resolved = requests.filter(r => r.status !== 'pending');

  const typeLabel = t => t === 'employee' ? 'Permanent' : t === 'job-order' ? 'Job Order' : 'Admin';
  const typeColor = t => t === 'employee' ? '#8e1e18' : t === 'job-order' ? '#1a6e3c' : '#0b044d';

  const Card = ({ r }) => (
    <div className="request-card">
      <div className="request-avatar" style={{ background: typeColor(r.type) }}>
        {r.firstName[0]}{r.lastName[0]}
      </div>
      <div className="request-info">
        <p className="request-name">{r.name}</p>
        <p className="request-details">{r.position} · {r.email}</p>
        <div className="request-badges">
          <span className="request-badge" style={{ background: typeColor(r.type) + '15', color: typeColor(r.type) }}>{typeLabel(r.type)}</span>
          <span className="request-meta">{r.employeeId}</span>
          <span className="request-meta">Submitted {r.date}</span>
        </div>
      </div>
      {r.status === 'pending' ? (
        <div className="request-actions">
          <button onClick={() => onReject(r.id)} className="request-btn-reject">Reject</button>
          <button onClick={() => onApprove(r.id)} className="request-btn-approve">Approve</button>
        </div>
      ) : (
        <span className={`request-status ${r.status}`}>
          {r.status === 'approved' ? '✓ Approved' : '✗ Rejected'}
        </span>
      )}
    </div>
  );

  return (
    <div>
      <div className="requests-section">
        <p className="requests-section-title">
          Pending Requests {pending.length > 0 && <span className="requests-badge">{pending.length}</span>}
        </p>
        {pending.length === 0 ? (
          <div className="requests-empty">
            <p className="requests-empty-icon">✅</p>
            <p className="requests-empty-title">All caught up!</p>
            <p className="requests-empty-desc">No pending account requests at this time.</p>
          </div>
        ) : (
          <div className="requests-list">
            {pending.map(r => <Card key={r.id} r={r} />)}
          </div>
        )}
      </div>

      {resolved.length > 0 && (
        <div className="requests-section">
          <p className="requests-section-title">Resolved</p>
          <div className="requests-list">
            {resolved.map(r => <Card key={r.id} r={r} />)}
          </div>
        </div>
      )}
    </div>
  );
}

export default function Settings({ requests = [], onApprove, onReject }) {
  const pendingCount = requests.filter(r => r.status === 'pending').length;
  const [tab, setTab] = useState('profile');

  return (
    <div className="settings-container">
      <div className="settings-sidebar">
        <div className="settings-profile-card">
          <div className="settings-profile-avatar admin">AD</div>
          <h3 className="settings-profile-name">Admin User</h3>
          <p className="settings-profile-role">HR Staff</p>
          <div className="settings-profile-info">
            <div className="settings-profile-info-item">
              <p className="settings-profile-info-label">ROLE</p>
              <p className="settings-profile-info-value">Administrator</p>
            </div>
            <div className="settings-profile-info-item">
              <p className="settings-profile-info-label">DEPARTMENT</p>
              <p className="settings-profile-info-value">Human Resource Mgmt</p>
            </div>
            {pendingCount > 0 && (
              <div className="settings-profile-info-item pending">
                <p className="settings-profile-info-label">PENDING</p>
                <p className="settings-profile-info-value">{pendingCount} request{pendingCount > 1 ? 's' : ''}</p>
              </div>
            )}
          </div>
        </div>

        <div className="settings-nav">
          {TABS.map(t => (
            <button
              key={t.id}
              onClick={() => setTab(t.id)}
              className={`settings-nav-item ${tab === t.id ? 'active' : ''}`}
            >
              <span className="settings-nav-icon"><Icon name={t.icon} size={16} color={tab === t.id ? '#fff' : '#6b6a8a'} /></span>
              <span className="settings-nav-label">{t.label}</span>
              {t.id === 'requests' && pendingCount > 0 && (
                <span className="settings-nav-badge">{pendingCount}</span>
              )}
              {tab === t.id && (
                <svg className="settings-nav-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              )}
            </button>
          ))}
        </div>

        <div className="settings-tip">
          <div className="settings-tip-header">
            <span className="settings-tip-icon"><Icon name="shield" size={16} color="#6b3fa0" /></span>
            <p className="settings-tip-title">SECURITY TIP</p>
          </div>
          <p className="settings-tip-text">Enable 2FA and use a strong password to protect your admin account.</p>
        </div>
      </div>

      <div className="settings-content">
        {tab === 'profile'       && <ProfileTab />}
        {tab === 'system'        && <SystemTab />}
        {tab === 'security'      && <SecurityTab />}
        {tab === 'notifications' && <NotificationsTab />}
        {tab === 'requests'      && <RequestsTab requests={requests} onApprove={onApprove} onReject={onReject} />}
      </div>
    </div>
  );
}
