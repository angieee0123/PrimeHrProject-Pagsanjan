import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';

const initials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

const employeeData = {
  id: 'PGS-0115',
  name: 'Ana R. Reyes',
  position: 'Nurse II',
  dept: 'Municipal Health Office',
  empType: 'Permanent',
  status: 'Active',
  dateHired: 'Jan 15, 2018',
  gender: 'Female',
  birthday: 'Jul 22, 1990',
  contact: '09201122334',
  email: 'ana.reyes@pagsanjan.gov.ph',
  address: '123 Rizal Street, Barangay Poblacion, Pagsanjan, Laguna',
  gsis: '3456789012',
  philhealth: '34-567890123-4',
  pagibig: '3456-7890-1234',
  tin: '345-678-901',
  emergencyContact: 'Roberto Reyes',
  emergencyRelation: 'Spouse',
  emergencyPhone: '09171234567',
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

export default function PermanentProfile() {
  const [employee, setEmployee] = useState(employeeData);
  const [tab, setTab] = useState('personal');
  const [editOpen, setEditOpen] = useState(false);

  return (
    <div>
      {editOpen && <EditProfileModal initial={employee} onClose={() => setEditOpen(false)} onSave={setEmployee} />}

      {/* Profile Header Card */}
      <div className="welcome-banner" style={{ marginBottom: 24, padding: '24px 28px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '20px', flex: 1 }}>
          <div style={{ width: 72, height: 72, borderRadius: '50%', background: '#8e1e18', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: 26, flexShrink: 0 }}>
            {initials(employee.name)}
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
              <span className="banner-badge outline">Hired: {employee.dateHired}</span>
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
          { label: 'Years of Service', value: '7.5', sub: 'Since Jan 2018', accent: '#0b044d', icon: 'calendar' },
          { label: 'Performance Rating', value: '4.9', sub: 'Latest evaluation', accent: '#15803d', icon: 'star' },
          { label: 'Leave Balance', value: '12', sub: 'Days remaining', accent: '#d9bb00', icon: 'umbrella' },
          { label: 'Trainings Completed', value: '8', sub: 'Total programs', accent: '#8e1e18', icon: 'bookOpen' },
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
              <div className="pmodal-field"><span>Date Hired</span><strong>{employee.dateHired}</strong></div>
              <div className="pmodal-field"><span>Status</span><strong>{employee.status}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Position / Designation</span><strong>{employee.position}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Department / Office</span><strong>{employee.dept}</strong></div>
            </div>
          )}

          {tab === 'government' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>GSIS No.</span><strong>{employee.gsis}</strong></div>
              <div className="pmodal-field"><span>PhilHealth No.</span><strong>{employee.philhealth}</strong></div>
              <div className="pmodal-field"><span>Pag-IBIG No.</span><strong>{employee.pagibig}</strong></div>
              <div className="pmodal-field"><span>TIN</span><strong>{employee.tin}</strong></div>
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
