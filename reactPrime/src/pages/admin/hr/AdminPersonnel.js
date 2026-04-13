import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
const initials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

const initialPersonnel = [
  { id: 'PGS-0041', name: 'Maria B. Santos',    position: 'Administrative Officer IV',   dept: 'Office of the Mayor',          status: 'Active',    empType: 'Permanent', dateHired: 'Mar 12, 2015', gender: 'Female', birthday: 'Apr 5, 1982',  contact: '09171234567', email: 'maria.santos@pagsanjan.gov.ph',   gsis: '1234567890', philhealth: '12-345678901-2', pagibig: '1234-5678-9012', tin: '123-456-789' },
  { id: 'PGS-0082', name: 'Juan P. dela Cruz',  position: 'Municipal Engineer II',        dept: 'Office of the Mun. Engineer',  status: 'Active',    empType: 'Permanent', dateHired: 'Jun 1, 2012',  gender: 'Male',   birthday: 'Sep 14, 1979', contact: '09189876543', email: 'juan.delacruz@pagsanjan.gov.ph',  gsis: '2345678901', philhealth: '23-456789012-3', pagibig: '2345-6789-0123', tin: '234-567-890' },
  { id: 'PGS-0115', name: 'Ana R. Reyes',       position: 'Nurse II',                    dept: 'Municipal Health Office',      status: 'Active',    empType: 'Permanent', dateHired: 'Jan 15, 2018', gender: 'Female', birthday: 'Jul 22, 1990', contact: '09201122334', email: 'ana.reyes@pagsanjan.gov.ph',      gsis: '3456789012', philhealth: '34-567890123-4', pagibig: '3456-7890-1234', tin: '345-678-901' },
  { id: 'PGS-0203', name: 'Carlos M. Mendoza',  position: 'Municipal Treasurer III',     dept: 'Office of the Mun. Treasurer', status: 'Active',    empType: 'Permanent', dateHired: 'Aug 3, 2009',  gender: 'Male',   birthday: 'Feb 28, 1975', contact: '09155544332', email: 'carlos.mendoza@pagsanjan.gov.ph', gsis: '4567890123', philhealth: '45-678901234-5', pagibig: '4567-8901-2345', tin: '456-789-012' },
  { id: 'PGS-0267', name: 'Liza G. Gomez',      position: 'Social Welfare Officer II',   dept: 'MSWD – Pagsanjan',             status: 'Inactive',  empType: 'Permanent', dateHired: 'Nov 20, 2016', gender: 'Female', birthday: 'Dec 10, 1988', contact: '09276677889', email: 'liza.gomez@pagsanjan.gov.ph',     gsis: '5678901234', philhealth: '56-789012345-6', pagibig: '5678-9012-3456', tin: '567-890-123' },
  { id: 'PGS-0310', name: 'Roberto T. Flores',  position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar',    status: 'Active',    empType: 'Permanent', dateHired: 'Feb 7, 2020',  gender: 'Male',   birthday: 'Jun 3, 1993',  contact: '09309988776', email: 'roberto.flores@pagsanjan.gov.ph', gsis: '6789012345', philhealth: '67-890123456-7', pagibig: '6789-0123-4567', tin: '678-901-234' },
  { id: 'PGS-0342', name: 'Grace A. Villanueva',position: 'Budget Officer II',           dept: 'Office of the Mun. Budget',    status: 'Active',    empType: 'Casual',    dateHired: 'Apr 1, 2022',  gender: 'Female', birthday: 'Mar 17, 1995', contact: '09181234321', email: 'grace.villanueva@pagsanjan.gov.ph',gsis: '7890123456', philhealth: '78-901234567-8', pagibig: '7890-1234-5678', tin: '789-012-345' },
  { id: 'PGS-0358', name: 'Ramon D. Cruz',      position: 'Agriculturist I',             dept: 'Office of the Mun. Agriculturist', status: 'Active', empType: 'Casual',    dateHired: 'Jul 15, 2023', gender: 'Male',   birthday: 'Aug 9, 1997',  contact: '09224433221', email: 'ramon.cruz@pagsanjan.gov.ph',     gsis: '8901234567', philhealth: '89-012345678-9', pagibig: '8901-2345-6789', tin: '890-123-456' },
];

const departments = [
  'All Departments',
  'Office of the Mayor',
  'Office of the Vice Mayor',
  'Sangguniang Bayan',
  'Office of the Mun. Treasurer',
  "Municipal Assessor's Office",
  'Municipal Civil Registrar',
  'Municipal Health Office',
  'MSWD – Pagsanjan',
  "Municipal Planning & Dev't Office",
  'Office of the Mun. Engineer',
  'Office of the Mun. Agriculturist',
  'Municipal Environment & Natural Resources',
  "Municipal Business & Dev't Office",
  'Human Resource Management Office',
  'Municipal Disaster Risk Reduction & Mgmt',
  'Office of the Mun. Budget',
  'Municipal Circuit Trial Court',
];

const emptyForm = { name: '', position: '', dept: departments[1], empType: 'Permanent', gender: 'Female', birthday: '', contact: '', email: '', gsis: '', philhealth: '', pagibig: '', tin: '' };

/* ── Escape hook ── */
function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

/* ── View Profile Modal ── */
function ProfileModal({ emp, idx, onClose, onEdit, onToggleStatus }) {
  useEscape(onClose);
  const [tab, setTab] = useState('info');
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>

        <div className="modal-header">
          <div className="pmodal-hero">
            <div className="emp-avatar xl" style={{ background: avatarColors[idx % avatarColors.length] }}>
              {initials(emp.name)}
            </div>
            <div>
              <span className="modal-eyebrow">EMPLOYEE PROFILE · {emp.id}</span>
              <h3 className="modal-title">{emp.name}</h3>
              <p className="modal-sub">{emp.position} · {emp.dept}</p>
              <div className="pmodal-badges">
                <span className={`badge-status ${emp.status === 'Active' ? 'processed' : 'on-hold'}`}>{emp.status}</span>
                <span className="badge-emptype">{emp.empType}</span>
              </div>
            </div>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div className="pmodal-tabs">
          {['info', 'employment', 'gov-ids', 'development'].map(t => (
            <button key={t} className={`pmodal-tab ${tab === t ? 'active' : ''}`} onClick={() => setTab(t)}>
              {t === 'info' ? 'Personal Info' : t === 'employment' ? 'Employment' : t === 'gov-ids' ? 'Gov. IDs' : 'Development'}
            </button>
          ))}
        </div>

        <div className="modal-body pmodal-body">
          {tab === 'info' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>Full Name</span><strong>{emp.name}</strong></div>
              <div className="pmodal-field"><span>Gender</span><strong>{emp.gender}</strong></div>
              <div className="pmodal-field"><span>Date of Birth</span><strong>{emp.birthday}</strong></div>
              <div className="pmodal-field"><span>Contact No.</span><strong>{emp.contact}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Email Address</span><strong>{emp.email}</strong></div>
            </div>
          )}
          {tab === 'employment' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>Employee ID</span><strong>{emp.id}</strong></div>
              <div className="pmodal-field"><span>Employment Type</span><strong>{emp.empType}</strong></div>
              <div className="pmodal-field"><span>Date Hired</span><strong>{emp.dateHired}</strong></div>
              <div className="pmodal-field"><span>Status</span><strong>{emp.status}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Position / Designation</span><strong>{emp.position}</strong></div>
              <div className="pmodal-field pmodal-full"><span>Department / Office</span><strong>{emp.dept}</strong></div>
            </div>
          )}
          {tab === 'gov-ids' && (
            <div className="pmodal-grid">
              <div className="pmodal-field"><span>GSIS No.</span><strong>{emp.gsis}</strong></div>
              <div className="pmodal-field"><span>PhilHealth No.</span><strong>{emp.philhealth}</strong></div>
              <div className="pmodal-field"><span>Pag-IBIG No.</span><strong>{emp.pagibig}</strong></div>
              <div className="pmodal-field"><span>TIN</span><strong>{emp.tin}</strong></div>
            </div>
          )}
          {tab === 'development' && (
            <div>
              <p style={{ fontSize: '10.5px', fontWeight: '700', color: '#aaa8cc', letterSpacing: '1.2px', marginBottom: '12px' }}>PERFORMANCE</p>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px', marginBottom: '20px' }}>
                <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                  <div style={{ width: '40px', height: '40px', borderRadius: '10px', background: 'linear-gradient(135deg, #6b3fa0 0%, #8b5cf6 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  </div>
                  <div>
                    <p style={{ fontSize: '11px', color: '#9999bb', marginBottom: '2px' }}>Latest Rating</p>
                    <p style={{ fontSize: '16px', fontWeight: '800', color: '#6b3fa0' }}>4.8 / 5.0</p>
                  </div>
                </div>
                <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                  <div style={{ width: '40px', height: '40px', borderRadius: '10px', background: 'linear-gradient(135deg, #15803d 0%, #22c55e 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                  </div>
                  <div>
                    <p style={{ fontSize: '11px', color: '#9999bb', marginBottom: '2px' }}>Evaluation Period</p>
                    <p style={{ fontSize: '13px', fontWeight: '700', color: '#0b044d' }}>Jan–Jun 2025</p>
                    <span className="badge-status processed" style={{ fontSize: '10px', marginTop: '4px', display: 'inline-block' }}>Completed</span>
                  </div>
                </div>
              </div>
              <p style={{ fontSize: '10.5px', fontWeight: '700', color: '#aaa8cc', letterSpacing: '1.2px', marginBottom: '10px' }}>TRAINING HISTORY</p>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {[
                  { title: 'Leadership Development Program', date: 'Jun 2025' },
                  { title: 'Customer Service Excellence', date: 'May 2025' },
                ].map((t, i) => (
                  <div key={i} style={{ background: '#f7f6ff', borderRadius: '10px', padding: '12px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <div style={{ width: '36px', height: '36px', borderRadius: '9px', background: 'linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <div style={{ flex: 1 }}>
                      <p style={{ fontSize: '12.5px', fontWeight: '600', color: '#0b044d', marginBottom: '2px' }}>{t.title}</p>
                      <p style={{ fontSize: '11px', color: '#9999bb' }}>Completed · {t.date}</p>
                    </div>
                    <span className="badge-status processed" style={{ fontSize: '10px' }}>Done</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        <div className="modal-footer">
          <button
            className={`modal-btn-ghost ${emp.status === 'Active' ? 'btn-danger' : 'btn-success'}`}
            onClick={() => onToggleStatus(emp.id)}
          >
            {emp.status === 'Active' ? 'Deactivate' : 'Activate'}
          </button>
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary" onClick={() => onEdit(emp)}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── Add / Edit Modal ── */
function EmpFormModal({ initial, onClose, onSave }) {
  useEscape(onClose);
  const isEdit = !!initial?.id;
  const [form, setForm] = useState(initial || emptyForm);
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">{isEdit ? 'EDIT EMPLOYEE' : 'ADD EMPLOYEE'}</span>
            <h3 className="modal-title">{isEdit ? `Edit — ${initial.name}` : 'New Employee Record'}</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div className="modal-body pmodal-body" style={{ maxHeight: '60vh', overflowY: 'auto' }}>
          <p className="form-section-label">PERSONAL INFORMATION</p>
          <div className="form-grid">
            <div className="form-field form-full">
              <label>Full Name</label>
              <input value={form.name} onChange={e => set('name', e.target.value)} placeholder="e.g. Maria B. Santos" />
            </div>
            <div className="form-field">
              <label>Gender</label>
              <select value={form.gender} onChange={e => set('gender', e.target.value)}>
                <option>Female</option><option>Male</option>
              </select>
            </div>
            <div className="form-field">
              <label>Date of Birth</label>
              <input type="date" value={form.birthday} onChange={e => set('birthday', e.target.value)} />
            </div>
            <div className="form-field">
              <label>Contact No.</label>
              <input value={form.contact} onChange={e => set('contact', e.target.value)} placeholder="09XXXXXXXXX" />
            </div>
            <div className="form-field">
              <label>Email Address</label>
              <input value={form.email} onChange={e => set('email', e.target.value)} placeholder="name@pagsanjan.gov.ph" />
            </div>
          </div>

          <p className="form-section-label" style={{ marginTop: 20 }}>EMPLOYMENT DETAILS</p>
          <div className="form-grid">
            <div className="form-field form-full">
              <label>Position / Designation</label>
              <input value={form.position} onChange={e => set('position', e.target.value)} placeholder="e.g. Administrative Officer IV" />
            </div>
            <div className="form-field form-full">
              <label>Department / Office</label>
              <select value={form.dept} onChange={e => set('dept', e.target.value)}>
                {departments.slice(1).map(d => <option key={d}>{d}</option>)}
              </select>
            </div>
            <div className="form-field">
              <label>Employment Type</label>
              <select value={form.empType} onChange={e => set('empType', e.target.value)}>
                <option>Permanent</option><option>Casual</option><option>Contractual</option><option>Job Order</option>
              </select>
            </div>
          </div>

          <p className="form-section-label" style={{ marginTop: 20 }}>GOVERNMENT IDs</p>
          <div className="form-grid">
            <div className="form-field"><label>GSIS No.</label><input value={form.gsis} onChange={e => set('gsis', e.target.value)} placeholder="0000000000" /></div>
            <div className="form-field"><label>PhilHealth No.</label><input value={form.philhealth} onChange={e => set('philhealth', e.target.value)} placeholder="00-000000000-0" /></div>
            <div className="form-field"><label>Pag-IBIG No.</label><input value={form.pagibig} onChange={e => set('pagibig', e.target.value)} placeholder="0000-0000-0000" /></div>
            <div className="form-field"><label>TIN</label><input value={form.tin} onChange={e => set('tin', e.target.value)} placeholder="000-000-000" /></div>
          </div>
        </div>

        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => { if (form.name && form.position) onSave(form); }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            {isEdit ? 'Save Changes' : 'Add Employee'}
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── Delete Confirm Modal ── */
function DeleteModal({ emp, onClose, onConfirm }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-sm" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">CONFIRM ACTION</span>
            <h3 className="modal-title">Deactivate Employee?</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-confirm-info">
            <div className="modal-row"><span>Employee</span><strong>{emp.name}</strong></div>
            <div className="modal-row"><span>ID</span><strong>{emp.id}</strong></div>
            <div className="modal-row"><span>Department</span><strong>{emp.dept}</strong></div>
          </div>
          <p className="modal-warn">⚠ This will mark the employee as Inactive. Their records will be retained but access will be disabled.</p>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" style={{ background: '#8e1e18' }} onClick={onConfirm}>Confirm Deactivate</button>
        </div>
      </div>
    </div>
  );
}

/* ── Personnel Page ── */
export default function Personnel({ searchQuery = '' }) {
  const [personnel, setPersonnel] = useState(initialPersonnel);
  const [deptFilter, setDeptFilter] = useState('All Departments');
  const [statusFilter, setStatusFilter] = useState('All');
  const [viewEmp, setViewEmp]     = useState(null);
  const [editEmp, setEditEmp]     = useState(null);
  const [addOpen, setAddOpen]     = useState(false);
  const [deleteEmp, setDeleteEmp] = useState(null);

  const filtered = personnel.filter(e => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || e.name.toLowerCase().includes(q) || e.id.toLowerCase().includes(q) || e.position.toLowerCase().includes(q);
    const matchDept   = deptFilter === 'All Departments' || e.dept === deptFilter;
    const matchStatus = statusFilter === 'All' || e.status === statusFilter;
    return matchSearch && matchDept && matchStatus;
  });

  const handleAdd = form => {
    const newId = `PGS-${String(personnel.length + 400).padStart(4, '0')}`;
    setPersonnel(p => [...p, { ...form, id: newId, status: 'Active', dateHired: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }]);
    setAddOpen(false);
  };

  const handleEdit = form => {
    setPersonnel(p => p.map(e => e.id === form.id ? { ...e, ...form } : e));
    setEditEmp(null);
    setViewEmp(null);
  };

  const handleToggleStatus = id => {
    setPersonnel(p => p.map(e => e.id === id ? { ...e, status: e.status === 'Active' ? 'Inactive' : 'Active' } : e));
    setViewEmp(v => v ? { ...v, status: v.status === 'Active' ? 'Inactive' : 'Active' } : null);
  };

  const handleDeactivate = () => {
    handleToggleStatus(deleteEmp.id);
    setDeleteEmp(null);
  };

  const totalActive   = personnel.filter(e => e.status === 'Active').length;
  const totalInactive = personnel.filter(e => e.status === 'Inactive').length;
  const totalPermanent = personnel.filter(e => e.empType === 'Permanent').length;

  return (
    <div>
      {/* Modals */}
      {viewEmp && (
        <ProfileModal
          emp={viewEmp}
          idx={personnel.findIndex(e => e.id === viewEmp.id)}
          onClose={() => setViewEmp(null)}
          onEdit={emp => { setEditEmp(emp); setViewEmp(null); }}
          onToggleStatus={handleToggleStatus}
        />
      )}
      {(addOpen || editEmp) && (
        <EmpFormModal
          initial={editEmp}
          onClose={() => { setAddOpen(false); setEditEmp(null); }}
          onSave={editEmp ? handleEdit : handleAdd}
        />
      )}
      {deleteEmp && (
        <DeleteModal
          emp={deleteEmp}
          onClose={() => setDeleteEmp(null)}
          onConfirm={handleDeactivate}
        />
      )}

      {/* Stats */}
      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'Total Personnel',  value: personnel.length, sub: 'All records',          accent: '#0b044d', icon: 'users' },
          { label: 'Active',           value: totalActive,      sub: 'Currently active',     accent: '#15803d', icon: 'checkCircle' },
          { label: 'Inactive',         value: totalInactive,    sub: 'Deactivated accounts', accent: '#8e1e18', icon: 'xCircle' },
          { label: 'Permanent',        value: totalPermanent,   sub: 'Permanent employees',  accent: '#d9bb00', icon: 'briefcase' },
        ].map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '15', color: s.accent }}><Icon name={s.icon} size={18} /></div>
            </div>
            <h2 className="stat-value">{s.value}</h2>
            <div className="stat-footer">
              <span className="stat-dot" style={{ background: s.accent }} />
              <p className="stat-sub">{s.sub}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Table Card */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Employee Records</h3>
            <p className="table-sub">Municipal Government of Pagsanjan · {filtered.length} of {personnel.length} records</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={deptFilter} onChange={e => setDeptFilter(e.target.value)}>
              {departments.map(d => <option key={d}>{d}</option>)}
            </select>
            <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
              <option value="All">All Status</option>
              <option>Active</option>
              <option>Inactive</option>
            </select>
            <button className="btn-export">
              <Icon name="download" size={13} />
              Export
            </button>
            <button className="modal-btn-primary" style={{ padding: '8px 18px', fontSize: 12.5, display: 'flex', alignItems: 'center', gap: '6px' }} onClick={() => setAddOpen(true)}>
              <Icon name="plus" size={13} />
              Add Employee
            </button>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Position</th>
                <th>Department / Office</th>
                <th>Type</th>
                <th>Date Hired</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={7} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No records found.</td></tr>
              )}
              {filtered.map((emp, i) => (
                <tr key={emp.id}>
                  <td>
                    <div className="emp-cell">
                      <div className="emp-avatar" style={{ background: avatarColors[personnel.indexOf(emp) % avatarColors.length] }}>
                        {initials(emp.name)}
                      </div>
                      <div>
                        <p className="emp-name">{emp.name}</p>
                        <p className="emp-id">{emp.id}</p>
                      </div>
                    </div>
                  </td>
                  <td className="position-cell">{emp.position}</td>
                  <td><span className="dept-tag">{emp.dept}</span></td>
                  <td><span className="badge-emptype">{emp.empType}</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{emp.dateHired}</td>
                  <td><span className={`badge-status ${emp.status === 'Active' ? 'processed' : 'on-hold'}`}>{emp.status}</span></td>
                  <td>
                    <div className="row-actions">
                      <button className="btn-view" onClick={() => setViewEmp(emp)}>View</button>
                      <button className="btn-edit" onClick={() => setEditEmp(emp)}>Edit</button>
                      {emp.status === 'Active' && (
                        <button className="btn-deactivate" onClick={() => setDeleteEmp(emp)}>Deactivate</button>
                      )}
                      {emp.status === 'Inactive' && (
                        <button className="btn-activate" onClick={() => handleToggleStatus(emp.id)}>Activate</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{filtered.length}</strong> of <strong>{personnel.length}</strong> records</p>
          <div className="pagination">
            <button className="page-btn active">1</button>
            <button className="page-btn">2</button>
            <button className="page-btn">›</button>
          </div>
        </div>
      </section>
    </div>
  );
}
