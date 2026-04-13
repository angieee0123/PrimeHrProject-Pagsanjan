import { useState } from 'react';
import { Icon } from '../../components/Icons';
import '../admin/hr/AdminSettings.css';

const TABS = [
  { id: 'profile',       label: 'Profile',        icon: 'user' },
  { id: 'security',      label: 'Security',       icon: 'shield' },
  { id: 'notifications', label: 'Notifications',  icon: 'bell' },
];

const ACCENT = '#1a6e3c';

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
    <button onClick={() => onChange(!value)} className={`settings-toggle ${value ? 'active' : ''}`} style={{ background: value ? ACCENT : '#dddcf0' }}>
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
      <button onClick={handleSave} className={`settings-btn-save ${saved ? 'saved' : ''}`} style={{ background: saved ? '#15803d' : ACCENT }}>
        {saved
          ? <><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg> Saved!</>
          : <><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save Changes</>
        }
      </button>
    </div>
  );
}

function ProfileTab() {
  const init = {
    firstName: 'Juan', lastName: 'D. Cruz',
    email: 'juan.cruz@pagsanjan.gov.ph', contact: '09181234568',
    empId: 'JO-0042', position: 'Utility Worker I',
    dept: 'General Services Office', empType: 'Job Order',
    contractStart: 'Jan 1, 2025', contractEnd: 'Dec 31, 2025',
  };
  const [form, setForm] = useState(init);
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div>
      <Section title="Personal Information">
        <div className="settings-form-wrapper">
          <div className="settings-avatar-row">
            <div className="settings-avatar" style={{ background: ACCENT }}>JD</div>
            <div className="settings-avatar-info">
              <p className="settings-avatar-name">Juan D. Cruz</p>
              <p className="settings-avatar-role">Utility Worker I · General Services Office</p>
            </div>
          </div>

          <div className="settings-form-grid">
            {[['First Name', 'firstName'], ['Last Name', 'lastName'], ['Email Address', 'email'], ['Contact No.', 'contact']].map(([label, key]) => (
              <div key={key} className="settings-form-field">
                <label>{label}</label>
                <input value={form[key]} onChange={e => set(key, e.target.value)} />
              </div>
            ))}
          </div>
          <SaveBar onSave={() => {}} onReset={() => setForm(init)} />
        </div>
      </Section>

      <Section title="Employment Details">
        {[
          ['Employee ID',      init.empId,         'Assigned by HR — not editable'],
          ['Position',         init.position,      'Assigned by HR — not editable'],
          ['Department',       init.dept,          'Assigned by HR — not editable'],
          ['Employment Type',  init.empType,       'Assigned by HR — not editable'],
          ['Contract Start',   init.contractStart, 'Assigned by HR — not editable'],
          ['Contract End',     init.contractEnd,   'Assigned by HR — not editable'],
        ].map(([label, value, note]) => (
          <Row key={label} label={label} desc={note}>
            <span style={{ fontSize: 13, fontWeight: 600, color: '#5a5888', background: '#f7f6ff', padding: '5px 12px', borderRadius: 7 }}>{value}</span>
          </Row>
        ))}
      </Section>

      <div style={{ background: '#f0fdf4', border: '1.5px solid #bbf7d0', borderRadius: 10, padding: '14px 18px', marginTop: 16, display: 'flex', gap: '12px' }}>
        <div style={{ flexShrink: 0 }}>
          <Icon name="clipboard" size={16} color="#15803d" />
        </div>
        <div>
          <p style={{ fontSize: 12.5, color: '#15803d', fontWeight: 700, marginBottom: 4 }}>Contract Renewal Notice</p>
          <p style={{ fontSize: 12, color: '#166534', lineHeight: 1.7, margin: 0 }}>
            Your Job Order contract is valid until <strong>{init.contractEnd}</strong>. Contact the HR Management Office for renewal inquiries.
          </p>
        </div>
      </div>
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
          <button onClick={handleChange} className="settings-btn-primary" style={{ background: ACCENT }}>
            Change Password
          </button>
        </div>
      </Section>

      <Section title="Login Security">
        <Row label="Two-Factor Authentication" desc="Require OTP on every login">
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
  const init = { payslipReady: true, contractRenewal: true, dtrDeadline: true, attendanceAlert: false, emailDigest: true };
  const [prefs, setPrefs] = useState(init);
  const toggle = k => setPrefs(p => ({ ...p, [k]: !p[k] }));

  const items = [
    { key: 'payslipReady',    label: 'Payslip Available',      desc: 'Notify when your payslip is ready for the pay period' },
    { key: 'contractRenewal', label: 'Contract Renewal Alert', desc: 'Notify 30 days before your contract expires' },
    { key: 'dtrDeadline',     label: 'DTR Deadline Reminder',  desc: 'Remind before DTR submission deadline' },
    { key: 'attendanceAlert', label: 'Attendance Alert',       desc: 'Notify when a late or absent entry is recorded' },
  ];

  return (
    <div>
      <Section title="In-App Notifications">
        {items.map(item => (
          <Row key={item.key} label={item.label} desc={item.desc}>
            <Toggle value={prefs[item.key]} onChange={() => toggle(item.key)} />
          </Row>
        ))}
      </Section>
      <Section title="Email Notifications">
        <Row label="Email Digest" desc="Receive a daily summary of updates via email">
          <Toggle value={prefs.emailDigest} onChange={() => toggle('emailDigest')} />
        </Row>
        <div className="settings-form-wrapper">
          <SaveBar onSave={() => {}} onReset={() => setPrefs(init)} />
        </div>
      </Section>
    </div>
  );
}

export default function JobOrderSettings() {
  const [tab, setTab] = useState('profile');

  return (
    <div className="settings-container">
      <div className="settings-sidebar">
        <div className="settings-profile-card" style={{ background: 'linear-gradient(135deg, #1a6e3c 0%, #22c55e 100%)' }}>
          <div className="settings-profile-avatar" style={{ color: '#1a6e3c' }}>JD</div>
          <h3 className="settings-profile-name">Juan D. Cruz</h3>
          <p className="settings-profile-role">JO-0042</p>
          <div className="settings-profile-info">
            <div className="settings-profile-info-item">
              <p className="settings-profile-info-label">POSITION</p>
              <p className="settings-profile-info-value">Utility Worker I</p>
            </div>
            <div className="settings-profile-info-item">
              <p className="settings-profile-info-label">DEPARTMENT</p>
              <p className="settings-profile-info-value">General Services Office</p>
            </div>
            <div className="settings-profile-info-item">
              <p className="settings-profile-info-label">TYPE</p>
              <p className="settings-profile-info-value">Job Order</p>
            </div>
            <div className="settings-profile-info-item pending">
              <p className="settings-profile-info-label">CONTRACT ENDS</p>
              <p className="settings-profile-info-value">Dec 31, 2025</p>
            </div>
          </div>
        </div>

        <div className="settings-nav">
          {TABS.map(t => (
            <button
              key={t.id}
              onClick={() => setTab(t.id)}
              className={`settings-nav-item ${tab === t.id ? 'active' : ''}`}
              style={tab === t.id ? { background: ACCENT } : {}}
            >
              <span className="settings-nav-icon"><Icon name={t.icon} size={16} color={tab === t.id ? '#fff' : '#6b6a8a'} /></span>
              <span className="settings-nav-label">{t.label}</span>
              {tab === t.id && (
                <svg className="settings-nav-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              )}
            </button>
          ))}
        </div>

        <div className="settings-tip" style={{ background: 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)', border: '1.5px solid #bbf7d0' }}>
          <div className="settings-tip-header">
            <span className="settings-tip-icon"><Icon name="clipboard" size={16} color="#15803d" /></span>
            <p className="settings-tip-title" style={{ color: '#15803d' }}>CONTRACT INFO</p>
          </div>
          <p className="settings-tip-text" style={{ color: '#166534' }}>Your contract is valid until Dec 31, 2025. Contact HR for renewal.</p>
        </div>
      </div>

      <div className="settings-content">
        {tab === 'profile'       && <ProfileTab />}
        {tab === 'security'      && <SecurityTab />}
        {tab === 'notifications' && <NotificationsTab />}
      </div>
    </div>
  );
}
