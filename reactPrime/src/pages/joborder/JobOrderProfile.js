import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';

const employeeData = {
  id: 'JO-0042',
  name: 'Juan D. Cruz',
  initials: 'JD',
  position: 'Utility Worker I',
  dept: 'General Services Office',
  empType: 'Job Order',
  status: 'Active',
  dateHired: 'Jan 1, 2025',
  contractEnd: 'Dec 31, 2025',
  gender: 'Male',
  birthday: 'Aug 12, 1992',
  contact: '09171234567',
  email: 'juan.cruz@pagsanjan.gov.ph',
  address: '789 Rizal Street, Barangay Sampaloc, Pagsanjan, Laguna',
  sss: '34-5678901-2',
  philhealth: '12-345678901-2',
  pagibig: '1234-5678-9012',
  tin: '123-456-789',
  emergencyContact: 'Maria Cruz',
  emergencyRelation: 'Spouse',
  emergencyPhone: '09181234567',
};

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

function EditProfileModal({ initial, onClose, onSave }) {
  useEscape(onClose);
  const [form, setForm] = useState(initial);
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">EDIT PROFILE</span>
            <h3 className="modal-title">Update Personal Information</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div className="modal-body pmodal-body" style={{ maxHeight: '60vh', overflowY: 'auto' }}>
          <p className="form-section-label">CONTACT INFORMATION</p>
          <div className="form-grid">
            <div className="form-field">
              <label>Contact No.</label>
              <input value={form.contact} onChange={e => set('contact', e.target.value)} placeholder="09XXXXXXXXX" />
            </div>
            <div className="form-field">
              <label>Email Address</label>
              <input value={form.email} onChange={e => set('email', e.target.value)} placeholder="name@pagsanjan.gov.ph" />
            </div>
            <div className="form-field form-full">
              <label>Address</label>
              <input value={form.address} onChange={e => set('address', e.target.value)} placeholder="Complete address" />
            </div>
          </div>

          <p className="form-section-label" style={{ marginTop: 20 }}>EMERGENCY CONTACT</p>
          <div className="form-grid">
            <div className="form-field">
              <label>Contact Person</label>
              <input value={form.emergencyContact} onChange={e => set('emergencyContact', e.target.value)} placeholder="Full name" />
            </div>
            <div className="form-field">
              <label>Relationship</label>
              <input value={form.emergencyRelation} onChange={e => set('emergencyRelation', e.target.value)} placeholder="e.g. Spouse, Parent" />
            </div>
            <div className="form-field">
              <label>Phone Number</label>
              <input value={form.emergencyPhone} onChange={e => set('emergencyPhone', e.target.value)} placeholder="09XXXXXXXXX" />
            </div>
          </div>
        </div>

        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => { onSave(form); onClose(); }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Save Changes
          </button>
        </div>
      </div>
    </div>
  );
}

export default function JobOrderProfile() {
  const [employee, setEmployee] = useState(employeeData);
  const [tab, setTab] = useState('personal');
  const [editOpen, setEditOpen] = useState(false);

  const calculateDaysRemaining = () => {
    const end = new Date('2025-12-31');
    const today = new Date();
    const diff = Math.ceil((end - today) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
  };

  const calculateMonthsServed = () => {
    const start = new Date('2025-01-01');
    const today = new Date();
    const months = (today.getFullYear() - start.getFullYear()) * 12 + (today.getMonth() - start.getMonth());
    return months >= 0 ? months : 0;
  };

  return (
    <div>
      {editOpen && <EditProfileModal initial={employee} onClose={() => setEditOpen(false)} onSave={setEmployee} />}

      {/* Profile Header Card */}
      <div className="welcome-banner" style={{ marginBottom: 24, padding: '24px 28px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '20px', flex: 1 }}>
          <div style={{ width: 72, height: 72, borderRadius: '50%', background: '#1a6e3c', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: 26, flexShrink: 0 }}>
            {employee.initials}
          </div>
          <div style={{ flex: 1 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '6px', flexWrap: 'wrap' }}>
              <h2 style={{ fontSize: 20, fontWeight: 800, color: '#ffffff', margin: 0 }}>{employee.name}</h2>
              <span className="banner-badge"><span className="banner-badge-dot" />{employee.status}</span>
            </div>
            <p style={{ fontSize: 13, color: 'rgba(255,255,255,0.65)', marginBottom: '10px' }}>{employee.position} · {employee.dept}</p>
            <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
              <span className="banner-badge outline">{employee.empType}</span>
              <span className="banner-badge outline">{employee.id}</span>
              <span className="banner-badge outline">Contract: {employee.dateHired} - {employee.contractEnd}</span>
            </div>
          </div>
        </div>
        <button className="modal-btn-primary" style={{ padding: '9px 20px', fontSize: 13, background: '#fff', color: '#0b044d', border: 'none' }} onClick={() => setEditOpen(true)}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Profile
        </button>
      </div>

      {/* Stats Grid */}
      <section className="stats-grid" style={{ marginBottom: 24 }}>
        {[
          { label: 'Months Served', value: calculateMonthsServed(), sub: 'Since Jan 2025', accent: '#0b044d', icon: 'calendar' },
          { label: 'Contract Days Left', value: calculateDaysRemaining(), sub: 'Until Dec 31, 2025', accent: '#d9bb00', icon: 'clock' },
          { label: 'Attendance Rate', value: '90%', sub: '19 days present', accent: '#15803d', icon: 'checkCircle' },
          { label: 'Trainings Completed', value: '2', sub: 'Total programs', accent: '#8e1e18', icon: 'bookOpen' },
        ].map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '15' }}>
                <Icon name={s.icon} size={18} color={s.accent} />
              </div>
            </div>
            <h2 className="stat-value">{s.value}</h2>
            <div className="stat-footer">
              <span className="stat-dot" style={{ background: s.accent }} />
              <p className="stat-sub">{s.sub}</p>
            </div>
          </div>
        ))}
      </section>

      {/* Contract Status Alert */}
      <section style={{ marginBottom: 24 }}>
        <div style={{ background: 'linear-gradient(135deg, #1a6e3c 0%, #15803d 100%)', borderRadius: '14px', padding: '20px 24px', display: 'flex', alignItems: 'center', gap: '16px', boxShadow: '0 4px 12px rgba(26, 110, 60, 0.15)', flexWrap: 'wrap' }}>
          <div style={{ width: 48, height: 48, borderRadius: '12px', background: 'rgba(255,255,255,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          </div>
          <div style={{ flex: 1, minWidth: '200px' }}>
            <h4 style={{ fontSize: 14, fontWeight: 700, color: '#fff', margin: 0, marginBottom: 4 }}>Job Order Contract Status</h4>
            <p style={{ fontSize: 12, color: 'rgba(255,255,255,0.85)', margin: 0, lineHeight: '1.5' }}>Your contract is valid from {employee.dateHired} to {employee.contractEnd}. {calculateDaysRemaining()} days remaining.</p>
          </div>
          <button className="modal-btn-primary" style={{ padding: '8px 16px', fontSize: 12, background: '#fff', color: '#1a6e3c', border: 'none', whiteSpace: 'nowrap', flexShrink: 0 }}>
            View Contract
          </button>
        </div>
      </section>

      {/* Tab Content */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Profile Information</h3>
            <p className="table-sub">View and manage your personal details</p>
          </div>
        </div>

        {/* Tabs */}
        <div className="pmodal-tabs" style={{ borderBottom: '1px solid #f0effe', padding: '0 24px' }}>
          {['personal', 'employment', 'government', 'emergency'].map(t => (
            <button key={t} className={`pmodal-tab ${tab === t ? 'active' : ''}`} onClick={() => setTab(t)}>
              {t === 'personal' ? 'Personal Info' : t === 'employment' ? 'Employment' : t === 'government' ? 'Government IDs' : 'Emergency Contact'}
            </button>
          ))}
        </div>

        <div style={{ padding: '28px 32px' }}>
          {tab === 'personal' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>Full Name</span><strong>{employee.name}</strong></div>
              <div className="pmodal-field"><span>Gender</span><strong>{employee.gender}</strong></div>
              <div className="pmodal-field"><span>Date of Birth</span><strong>{employee.birthday}</strong></div>
              <div className="pmodal-field"><span>Contact No.</span><strong>{employee.contact}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Email Address</span><strong>{employee.email}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Address</span><strong>{employee.address}</strong></div>
            </div>
          )}

          {tab === 'employment' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>Employee ID</span><strong>{employee.id}</strong></div>
              <div className="pmodal-field"><span>Employment Type</span><strong>{employee.empType}</strong></div>
              <div className="pmodal-field"><span>Contract Start</span><strong>{employee.dateHired}</strong></div>
              <div className="pmodal-field"><span>Contract End</span><strong>{employee.contractEnd}</strong></div>
              <div className="pmodal-field"><span>Status</span><strong>{employee.status}</strong></div>
              <div className="pmodal-field"><span>Days Remaining</span><strong>{calculateDaysRemaining()} days</strong></div>
              <div className="pmodal-field pmodal-full"><span>Position / Designation</span><strong>{employee.position}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Department / Office</span><strong>{employee.dept}</strong></div>
            </div>
          )}

          {tab === 'government' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>SSS No.</span><strong>{employee.sss}</strong></div>
              <div className="pmodal-field"><span>PhilHealth No.</span><strong>{employee.philhealth}</strong></div>
              <div className="pmodal-field"><span>Pag-IBIG No.</span><strong>{employee.pagibig}</strong></div>
              <div className="pmodal-field"><span>TIN</span><strong>{employee.tin}</strong></div>
              <div className="pmodal-field pmodal-full" style={{ background: '#fff9e6', padding: '12px 16px', borderRadius: '8px', border: '1px solid #ffeaa7' }}>
                <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" strokeWidth="2" style={{ marginTop: 2, flexShrink: 0 }}>
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                  </svg>
                  <div>
                    <span style={{ fontSize: 11, color: '#9b8600', fontWeight: 600, display: 'block', marginBottom: 4 }}>NOTE FOR JOB ORDER EMPLOYEES</span>
                    <span style={{ fontSize: 12, color: '#6b6a8a' }}>Job Order employees use SSS instead of GSIS. Make sure your SSS contributions are up to date.</span>
                  </div>
                </div>
              </div>
            </div>
          )}

          {tab === 'emergency' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>Contact Person</span><strong>{employee.emergencyContact}</strong></div>
              <div className="pmodal-field"><span>Relationship</span><strong>{employee.emergencyRelation}</strong></div>
              <div className="pmodal-field"><span>Phone Number</span><strong>{employee.emergencyPhone}</strong></div>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
