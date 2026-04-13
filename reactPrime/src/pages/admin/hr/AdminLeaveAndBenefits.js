import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
const initials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

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

const leaveTypes = ['Vacation Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave', 'Emergency Leave', 'Special Leave'];

const initialLeaves = [
  { id: 'LV-2025-001', empId: 'PGS-0041', name: 'Maria B. Santos',   position: 'Administrative Officer IV',   dept: 'Office of the Mayor',          type: 'Vacation Leave',  from: 'Jun 10, 2025', to: 'Jun 12, 2025', days: 3, reason: 'Family vacation', status: 'Approved' },
  { id: 'LV-2025-002', empId: 'PGS-0115', name: 'Ana R. Reyes',      position: 'Nurse II',                    dept: 'Municipal Health Office',       type: 'Sick Leave',      from: 'Jun 15, 2025', to: 'Jun 16, 2025', days: 2, reason: 'Medical consultation', status: 'Approved' },
  { id: 'LV-2025-003', empId: 'PGS-0203', name: 'Carlos M. Mendoza', position: 'Municipal Treasurer III',     dept: 'Office of the Mun. Treasurer',  type: 'Sick Leave',      from: 'Jun 20, 2025', to: 'Jun 22, 2025', days: 3, reason: 'Flu and fever', status: 'Pending' },
  { id: 'LV-2025-004', empId: 'PGS-0267', name: 'Liza G. Gomez',     position: 'Social Welfare Officer II',  dept: 'MSWD – Pagsanjan',              type: 'Emergency Leave', from: 'Jun 18, 2025', to: 'Jun 18, 2025', days: 1, reason: 'Family emergency', status: 'Approved' },
  { id: 'LV-2025-005', empId: 'PGS-0082', name: 'Juan P. dela Cruz', position: 'Municipal Engineer II',       dept: 'Office of the Mun. Engineer',   type: 'Vacation Leave',  from: 'Jul 1, 2025',  to: 'Jul 3, 2025',  days: 3, reason: 'Rest and recreation', status: 'Pending' },
  { id: 'LV-2025-006', empId: 'PGS-0310', name: 'Roberto T. Flores', position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar',     type: 'Vacation Leave',  from: 'Jun 25, 2025', to: 'Jun 25, 2025', days: 1, reason: 'Personal errand', status: 'Rejected' },
];

const benefitsData = [
  { empId: 'PGS-0041', name: 'Maria B. Santos',   gsis: '₱3,794', philhealth: '₱1,050', pagibig: '₱100', vlBalance: 15, slBalance: 15 },
  { empId: 'PGS-0082', name: 'Juan P. dela Cruz', gsis: '₱3,428', philhealth: '₱950',  pagibig: '₱100', vlBalance: 12, slBalance: 13 },
  { empId: 'PGS-0115', name: 'Ana R. Reyes',      gsis: '₱3,046', philhealth: '₱850',  pagibig: '₱100', vlBalance: 13, slBalance: 11 },
  { empId: 'PGS-0203', name: 'Carlos M. Mendoza', gsis: '₱4,253', philhealth: '₱1,150', pagibig: '₱100', vlBalance: 10, slBalance: 9 },
  { empId: 'PGS-0267', name: 'Liza G. Gomez',     gsis: '₱3,159', philhealth: '₱875',  pagibig: '₱100', vlBalance: 14, slBalance: 14 },
  { empId: 'PGS-0310', name: 'Roberto T. Flores', gsis: '₱2,748', philhealth: '₱775',  pagibig: '₱100', vlBalance: 8,  slBalance: 10 },
];

const statusColor = { Approved: 'processed', Pending: 'pending', Rejected: 'on-hold' };

/* ── Leave Detail Modal ── */
function LeaveModal({ row, idx, onClose }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">LEAVE REQUEST · {row.id}</span>
            <h3 className="modal-title">{row.name}</h3>
            <p className="modal-sub">{row.position} · {row.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-emp-row">
            <div className="emp-avatar lg" style={{ background: avatarColors[idx % avatarColors.length] }}>{initials(row.name)}</div>
            <div>
              <p className="modal-emp-id">{row.empId}</p>
              <span className={`badge-status ${statusColor[row.status]}`}>{row.status}</span>
            </div>
          </div>
          <div className="modal-section-label">LEAVE DETAILS</div>
          <div className="modal-row"><span>Leave Type</span><strong>{row.type}</strong></div>
          <div className="modal-row"><span>Date From</span><strong>{row.from}</strong></div>
          <div className="modal-row"><span>Date To</span><strong>{row.to}</strong></div>
          <div className="modal-row"><span>No. of Days</span><strong>{row.days} day{row.days > 1 ? 's' : ''}</strong></div>
          <div className="modal-section-label" style={{ marginTop: 16 }}>REASON</div>
          <div className="modal-row"><span>{row.reason}</span></div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── File Leave Modal ── */
function FileLeaveModal({ onClose, onSave }) {
  useEscape(onClose);
  const [form, setForm] = useState({ empId: '', name: '', dept: departments[1], type: leaveTypes[0], from: '', to: '', days: 1, reason: '' });
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">NEW LEAVE REQUEST</span>
            <h3 className="modal-title">File a Leave</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field"><label>Employee ID</label><input value={form.empId} onChange={e => set('empId', e.target.value)} placeholder="e.g. PGS-0041" /></div>
            <div className="form-field"><label>Full Name</label><input value={form.name} onChange={e => set('name', e.target.value)} placeholder="Last, First M." /></div>
            <div className="form-field">
              <label>Department</label>
              <select value={form.dept} onChange={e => set('dept', e.target.value)}>
                {departments.slice(1).map(d => <option key={d}>{d}</option>)}
              </select>
            </div>
            <div className="form-field">
              <label>Leave Type</label>
              <select value={form.type} onChange={e => set('type', e.target.value)}>
                {leaveTypes.map(t => <option key={t}>{t}</option>)}
              </select>
            </div>
            <div className="form-field"><label>Date From</label><input type="date" value={form.from} onChange={e => set('from', e.target.value)} /></div>
            <div className="form-field"><label>Date To</label><input type="date" value={form.to} onChange={e => set('to', e.target.value)} /></div>
            <div className="form-field"><label>No. of Days</label><input type="number" min="1" value={form.days} onChange={e => set('days', Number(e.target.value))} /></div>
          </div>
          <div className="form-field" style={{ marginTop: 8 }}>
            <label>Reason</label>
            <input value={form.reason} onChange={e => set('reason', e.target.value)} placeholder="Brief reason for leave" />
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => { if (form.name && form.empId) onSave(form); }}>Submit Request</button>
        </div>
      </div>
    </div>
  );
}

/* ── Leave & Benefits Page ── */
export default function LeaveAndBenefits({ searchQuery = '' }) {
  const [tab, setTab]               = useState('leave');
  const [leaves, setLeaves]         = useState(initialLeaves);
  const [deptFilter, setDeptFilter] = useState('All Departments');
  const [typeFilter, setTypeFilter] = useState('All Types');
  const [statusFilter, setStatusFilter] = useState('All');
  const [viewRow, setViewRow]       = useState(null);
  const [showFile, setShowFile]     = useState(false);

  const filtered = leaves.filter(r => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || r.name.toLowerCase().includes(q) || r.empId.toLowerCase().includes(q) || r.id.toLowerCase().includes(q);
    const matchDept   = deptFilter === 'All Departments' || r.dept === deptFilter;
    const matchType   = typeFilter === 'All Types' || r.type === typeFilter;
    const matchStatus = statusFilter === 'All' || r.status === statusFilter;
    return matchSearch && matchDept && matchType && matchStatus;
  });

  const totalApproved = leaves.filter(r => r.status === 'Approved').length;
  const totalPending  = leaves.filter(r => r.status === 'Pending').length;
  const totalDays     = leaves.reduce((s, r) => s + r.days, 0);

  const handleSaveLeave = form => {
    const newLeave = {
      ...form,
      id: `LV-2025-${String(leaves.length + 1).padStart(3, '0')}`,
      position: '—',
      status: 'Pending',
    };
    setLeaves(l => [newLeave, ...l]);
    setShowFile(false);
  };

  const handleStatusChange = (id, status) => {
    setLeaves(l => l.map(r => r.id === id ? { ...r, status } : r));
  };

  return (
    <div>
      {viewRow && (
        <LeaveModal
          row={viewRow}
          idx={leaves.findIndex(r => r.id === viewRow.id)}
          onClose={() => setViewRow(null)}
        />
      )}
      {showFile && (
        <FileLeaveModal
          onClose={() => setShowFile(false)}
          onSave={handleSaveLeave}
        />
      )}

      {/* Stats */}
      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'Total Leave Requests', value: leaves.length,  sub: 'All time',              accent: '#0b044d', icon: 'clipboard' },
          { label: 'Approved',             value: totalApproved,  sub: 'This period',            accent: '#15803d', icon: 'checkCircle' },
          { label: 'Pending Approval',     value: totalPending,   sub: 'Needs action',           accent: '#d9bb00', icon: 'clock' },
          { label: 'Total Leave Days',     value: `${totalDays}`, sub: 'Across all employees',   accent: '#8e1e18', icon: 'calendar' },
        ].map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '18' }}>
                <Icon name={s.icon} size={16} color={s.accent} />
              </div>
            </div>
            <h2 className="stat-value" style={{ fontSize: 22 }}>{s.value}</h2>
            <div className="stat-footer">
              <span className="stat-dot" style={{ background: s.accent }} />
              <p className="stat-sub">{s.sub}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Tabs */}
      <div style={{ display: 'flex', gap: 4, marginBottom: 20, borderBottom: '1.5px solid #eceaf8', paddingBottom: 0 }}>
        {[{ id: 'leave', label: 'Leave Requests' }, { id: 'benefits', label: 'Benefits Summary' }].map(t => (
          <button
            key={t.id}
            onClick={() => setTab(t.id)}
            style={{
              background: 'none', border: 'none', cursor: 'pointer',
              padding: '10px 20px', fontFamily: 'Poppins, sans-serif',
              fontSize: 13.5, fontWeight: 600,
              color: tab === t.id ? '#0b044d' : '#9999bb',
              borderBottom: tab === t.id ? '2.5px solid #0b044d' : '2.5px solid transparent',
              marginBottom: -1.5,
            }}
          >{t.label}</button>
        ))}
      </div>

      {/* ── Leave Requests Tab ── */}
      {tab === 'leave' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Leave Requests — June 2025</h3>
              <p className="table-sub">Municipal Government of Pagsanjan · {filtered.length} of {leaves.length} records</p>
            </div>
            <div className="table-actions">
              <select className="filter-select" value={deptFilter} onChange={e => setDeptFilter(e.target.value)}>
                {departments.map(d => <option key={d}>{d}</option>)}
              </select>
              <select className="filter-select" value={typeFilter} onChange={e => setTypeFilter(e.target.value)}>
                <option>All Types</option>
                {leaveTypes.map(t => <option key={t}>{t}</option>)}
              </select>
              <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
                <option value="All">All Status</option>
                <option>Approved</option><option>Pending</option><option>Rejected</option>
              </select>
              <button className="btn-export" onClick={() => setShowFile(true)} style={{ background: '#0b044d', color: '#fff', borderColor: '#0b044d' }}>
                + File Leave
              </button>
              <button className="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
              </button>
            </div>
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Department</th>
                  <th>Leave Type</th>
                  <th>Date From</th>
                  <th>Date To</th>
                  <th>Days</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {filtered.length === 0 && (
                  <tr><td colSpan={8} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No records found.</td></tr>
                )}
                {filtered.map(row => {
                  const realIdx = leaves.findIndex(r => r.id === row.id);
                  return (
                    <tr key={row.id}>
                      <td>
                        <div className="emp-cell">
                          <div className="emp-avatar" style={{ background: avatarColors[realIdx % avatarColors.length] }}>
                            {initials(row.name)}
                          </div>
                          <div>
                            <p className="emp-name">{row.name}</p>
                            <p className="emp-id">{row.empId}</p>
                          </div>
                        </div>
                      </td>
                      <td><span className="dept-tag">{row.dept}</span></td>
                      <td style={{ fontSize: 13, color: '#0b044d', fontWeight: 500 }}>{row.type}</td>
                      <td style={{ fontSize: 13 }}>{row.from}</td>
                      <td style={{ fontSize: 13 }}>{row.to}</td>
                      <td style={{ fontWeight: 600, color: '#0b044d' }}>{row.days}</td>
                      <td><span className={`badge-status ${statusColor[row.status]}`}>{row.status}</span></td>
                      <td>
                        <div className="row-actions">
                          <button className="btn-view" onClick={() => setViewRow(row)}>View</button>
                          {row.status === 'Pending' && (
                            <>
                              <button className="btn-edit" style={{ background: '#e8f9ef', color: '#15803d', border: '1px solid #bbf7d0' }} onClick={() => handleStatusChange(row.id, 'Approved')}>Approve</button>
                              <button className="btn-edit" style={{ background: '#fdf0ef', color: '#8e1e18', border: '1px solid #fecaca' }} onClick={() => handleStatusChange(row.id, 'Rejected')}>Reject</button>
                            </>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>

          <div className="table-footer">
            <p>Showing <strong>{filtered.length}</strong> of <strong>{leaves.length}</strong> records</p>
            <div className="pagination">
              <button className="page-btn active">1</button>
              <button className="page-btn">›</button>
            </div>
          </div>
        </section>
      )}

      {/* ── Benefits Summary Tab ── */}
      {tab === 'benefits' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Benefits Summary — June 2025</h3>
              <p className="table-sub">GSIS · PhilHealth · Pag-IBIG · Leave Credits</p>
            </div>
            <div className="table-actions">
              <button className="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
              </button>
            </div>
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>GSIS Premium</th>
                  <th>PhilHealth</th>
                  <th>Pag-IBIG</th>
                  <th>VL Balance</th>
                  <th>SL Balance</th>
                </tr>
              </thead>
              <tbody>
                {benefitsData.map((row, i) => (
                  <tr key={row.empId}>
                    <td>
                      <div className="emp-cell">
                        <div className="emp-avatar" style={{ background: avatarColors[i % avatarColors.length] }}>
                          {initials(row.name)}
                        </div>
                        <div>
                          <p className="emp-name">{row.name}</p>
                          <p className="emp-id">{row.empId}</p>
                        </div>
                      </div>
                    </td>
                    <td className="deduction">{row.gsis}</td>
                    <td className="deduction">{row.philhealth}</td>
                    <td className="deduction">{row.pagibig}</td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <div style={{ flex: 1, height: 6, background: '#f0effe', borderRadius: 3, minWidth: 50 }}>
                          <div style={{ width: `${(row.vlBalance / 15) * 100}%`, height: '100%', background: '#0b044d', borderRadius: 3 }} />
                        </div>
                        <span style={{ fontSize: 12, fontWeight: 600, color: '#0b044d' }}>{row.vlBalance} days</span>
                      </div>
                    </td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <div style={{ flex: 1, height: 6, background: '#f0effe', borderRadius: 3, minWidth: 50 }}>
                          <div style={{ width: `${(row.slBalance / 15) * 100}%`, height: '100%', background: '#15803d', borderRadius: 3 }} />
                        </div>
                        <span style={{ fontSize: 12, fontWeight: 600, color: '#15803d' }}>{row.slBalance} days</span>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="table-footer">
            <p>Showing <strong>{benefitsData.length}</strong> of <strong>{benefitsData.length}</strong> records</p>
          </div>
        </section>
      )}
    </div>
  );
}
