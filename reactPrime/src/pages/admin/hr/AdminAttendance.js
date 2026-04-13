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

const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const years  = ['2025','2024','2023'];
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

const initialRecords = [
  { id: 'PGS-0041', name: 'Maria B. Santos',     position: 'Administrative Officer IV',   dept: 'Office of the Mayor',              present: 22, absent: 0, late: 1, halfday: 0, overtime: 3.5, status: 'Complete' },
  { id: 'PGS-0082', name: 'Juan P. dela Cruz',   position: 'Municipal Engineer II',        dept: 'Office of the Mun. Engineer',      present: 20, absent: 1, late: 2, halfday: 1, overtime: 0,   status: 'Complete' },
  { id: 'PGS-0115', name: 'Ana R. Reyes',        position: 'Nurse II',                    dept: 'Municipal Health Office',          present: 21, absent: 0, late: 0, halfday: 0, overtime: 8.0, status: 'Complete' },
  { id: 'PGS-0203', name: 'Carlos M. Mendoza',   position: 'Municipal Treasurer III',     dept: 'Office of the Mun. Treasurer',     present: 19, absent: 2, late: 3, halfday: 0, overtime: 0,   status: 'Incomplete' },
  { id: 'PGS-0267', name: 'Liza G. Gomez',       position: 'Social Welfare Officer II',   dept: 'MSWD – Pagsanjan',                 present: 22, absent: 0, late: 0, halfday: 0, overtime: 2.0, status: 'Complete' },
  { id: 'PGS-0310', name: 'Roberto T. Flores',   position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar',        present: 18, absent: 3, late: 1, halfday: 1, overtime: 0,   status: 'Incomplete' },
  { id: 'PGS-0342', name: 'Grace A. Villanueva', position: 'Budget Officer II',           dept: 'Office of the Mun. Budget',        present: 21, absent: 1, late: 0, halfday: 0, overtime: 1.5, status: 'Complete' },
  { id: 'PGS-0358', name: 'Ramon D. Cruz',       position: 'Agriculturist I',             dept: 'Office of the Mun. Agriculturist', present: 20, absent: 0, late: 4, halfday: 0, overtime: 0,   status: 'Complete' },
];

/* ── DTR Detail Modal ── */
function DTRModal({ row, idx, period, onClose }) {
  useEscape(onClose);
  const workingDays = row.present + row.absent + row.halfday;
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">DTR · {period.toUpperCase()}</span>
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
              <p className="modal-emp-id">{row.id}</p>
              <span className={`badge-status ${row.status === 'Complete' ? 'processed' : 'pending'}`}>{row.status}</span>
            </div>
          </div>

          <div className="modal-section-label">ATTENDANCE SUMMARY</div>
          <div className="modal-row"><span>Working Days</span><strong>{workingDays} days</strong></div>
          <div className="modal-row"><span>Days Present</span><strong style={{ color: '#15803d' }}>{row.present} days</strong></div>
          <div className="modal-row"><span>Days Absent</span><strong style={{ color: '#8e1e18' }}>{row.absent} days</strong></div>
          <div className="modal-row"><span>Late Arrivals</span><strong style={{ color: '#a16207' }}>{row.late} times</strong></div>
          <div className="modal-row"><span>Half Days</span><strong style={{ color: '#a16207' }}>{row.halfday} days</strong></div>

          <div className="modal-section-label" style={{ marginTop: 16 }}>OVERTIME</div>
          <div className="modal-row"><span>Total OT Hours</span><strong style={{ color: '#0b044d' }}>{row.overtime} hrs</strong></div>

          <div className="modal-net-row" style={{ marginTop: 16 }}>
            <span>ATTENDANCE RATE</span>
            <strong>{workingDays > 0 ? Math.round((row.present / workingDays) * 100) : 0}%</strong>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download DTR
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── Edit DTR Modal ── */
function EditDTRModal({ row, onClose, onSave }) {
  useEscape(onClose);
  const [form, setForm] = useState({ ...row });
  const set = (k, v) => setForm(f => ({ ...f, [k]: Number(v) || 0 }));
  const workingDays = form.present + form.absent + form.halfday;
  const rate = workingDays > 0 ? Math.round((form.present / workingDays) * 100) : 0;
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">EDIT DTR RECORD</span>
            <h3 className="modal-title">{row.name}</h3>
            <p className="modal-sub">{row.id}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field"><label>Days Present</label><input type="number" min="0" value={form.present} onChange={e => set('present', e.target.value)} /></div>
            <div className="form-field"><label>Days Absent</label><input type="number" min="0" value={form.absent} onChange={e => set('absent', e.target.value)} /></div>
            <div className="form-field"><label>Late Arrivals</label><input type="number" min="0" value={form.late} onChange={e => set('late', e.target.value)} /></div>
            <div className="form-field"><label>Half Days</label><input type="number" min="0" value={form.halfday} onChange={e => set('halfday', e.target.value)} /></div>
            <div className="form-field"><label>Overtime (hrs)</label><input type="number" min="0" step="0.5" value={form.overtime} onChange={e => set('overtime', e.target.value)} /></div>
            <div className="form-field">
              <label>Status</label>
              <select value={form.status} onChange={e => setForm(f => ({ ...f, status: e.target.value }))}>
                <option>Complete</option><option>Incomplete</option>
              </select>
            </div>
          </div>
          <div className="modal-net-row" style={{ marginTop: 16 }}>
            <span>ATTENDANCE RATE PREVIEW</span>
            <strong>{rate}%</strong>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => onSave(form)}>Save Changes</button>
        </div>
      </div>
    </div>
  );
}

/* ── Attendance Page ── */
export default function Attendance({ searchQuery = '' }) {
  const [records, setRecords]           = useState(initialRecords);
  const [deptFilter, setDeptFilter]     = useState('All Departments');
  const [statusFilter, setStatusFilter] = useState('All');
  const [monthFilter, setMonthFilter]   = useState('June');
  const [yearFilter, setYearFilter]     = useState('2025');
  const [viewRow, setViewRow]           = useState(null);
  const [editRow, setEditRow]           = useState(null);

  const period = `${monthFilter} ${yearFilter}`;

  const filtered = records.filter(r => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || r.name.toLowerCase().includes(q) || r.id.toLowerCase().includes(q);
    const matchDept   = deptFilter === 'All Departments' || r.dept === deptFilter;
    const matchStatus = statusFilter === 'All' || r.status === statusFilter;
    return matchSearch && matchDept && matchStatus;
  });

  const totalPresent    = filtered.reduce((s, r) => s + r.present, 0);
  const totalAbsent     = filtered.reduce((s, r) => s + r.absent, 0);
  const totalLate       = filtered.reduce((s, r) => s + r.late, 0);
  const totalOT         = filtered.reduce((s, r) => s + r.overtime, 0);
  const completeCount   = records.filter(r => r.status === 'Complete').length;
  const incompleteCount = records.filter(r => r.status === 'Incomplete').length;

  const handleSaveEdit = form => {
    setRecords(r => r.map(e => e.id === form.id ? { ...e, ...form } : e));
    setEditRow(null);
  };

  return (
    <div>
      {viewRow && (
        <DTRModal
          row={viewRow}
          idx={records.findIndex(r => r.id === viewRow.id)}
          period={period}
          onClose={() => setViewRow(null)}
        />
      )}
      {editRow && (
        <EditDTRModal
          row={editRow}
          onClose={() => setEditRow(null)}
          onSave={handleSaveEdit}
        />
      )}

      {/* Stats */}
      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'DTR Submitted',    value: completeCount,   sub: `${incompleteCount} incomplete`,  accent: '#0b044d', icon: 'calendar' },
          { label: 'Total Present',    value: totalPresent,    sub: `${period}`,                      accent: '#15803d', icon: 'checkCircle' },
          { label: 'Total Absences',   value: totalAbsent,     sub: 'Across all personnel',           accent: '#8e1e18', icon: 'xCircle' },
          { label: 'Overtime Hours',   value: `${totalOT} hrs`,sub: `${totalLate} late arrivals`,     accent: '#d9bb00', icon: 'clock' },
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

      {/* Table */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Daily Time Record — {period}</h3>
            <p className="table-sub">Municipal Government of Pagsanjan · {filtered.length} of {records.length} records</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={monthFilter} onChange={e => setMonthFilter(e.target.value)}>
              {months.map(m => <option key={m}>{m}</option>)}
            </select>
            <select className="filter-select" value={yearFilter} onChange={e => setYearFilter(e.target.value)}>
              {years.map(y => <option key={y}>{y}</option>)}
            </select>
            <select className="filter-select" value={deptFilter} onChange={e => setDeptFilter(e.target.value)}>
              {departments.map(d => <option key={d}>{d}</option>)}
            </select>
            <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
              <option value="All">All Status</option>
              <option>Complete</option><option>Incomplete</option>
            </select>
            <button className="btn-export">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export
            </button>
          </div>
        </div>

        {/* Summary Bar */}
        <div className="payroll-summary-bar" style={{ marginTop: 0, marginBottom: 16 }}>
          <div className="psummary-item">
            <span>Total Present</span>
            <strong style={{ color: '#15803d' }}>{totalPresent} days</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Total Absent</span>
            <strong style={{ color: '#8e1e18' }}>{totalAbsent} days</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Late Arrivals</span>
            <strong style={{ color: '#a16207' }}>{totalLate} times</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Overtime</span>
            <strong>{totalOT} hrs</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Records</span>
            <strong>{filtered.length}</strong>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Half Day</th>
                <th>OT Hours</th>
                <th>Rate</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={10} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No records found.</td></tr>
              )}
              {filtered.map((row) => {
                const realIdx = records.findIndex(r => r.id === row.id);
                const workingDays = row.present + row.absent + row.halfday;
                const rate = workingDays > 0 ? Math.round((row.present / workingDays) * 100) : 0;
                return (
                  <tr key={row.id}>
                    <td>
                      <div className="emp-cell">
                        <div className="emp-avatar" style={{ background: avatarColors[realIdx % avatarColors.length] }}>
                          {initials(row.name)}
                        </div>
                        <div>
                          <p className="emp-name">{row.name}</p>
                          <p className="emp-id">{row.id}</p>
                        </div>
                      </div>
                    </td>
                    <td><span className="dept-tag">{row.dept}</span></td>
                    <td style={{ color: '#15803d', fontWeight: 600 }}>{row.present}</td>
                    <td style={{ color: row.absent > 0 ? '#8e1e18' : '#9999bb', fontWeight: row.absent > 0 ? 600 : 400 }}>{row.absent}</td>
                    <td style={{ color: row.late > 0 ? '#a16207' : '#9999bb', fontWeight: row.late > 0 ? 600 : 400 }}>{row.late}</td>
                    <td style={{ color: row.halfday > 0 ? '#a16207' : '#9999bb' }}>{row.halfday}</td>
                    <td style={{ color: row.overtime > 0 ? '#0b044d' : '#9999bb', fontWeight: row.overtime > 0 ? 600 : 400 }}>{row.overtime > 0 ? `${row.overtime} hrs` : '—'}</td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <div style={{ flex: 1, height: 6, background: '#f0effe', borderRadius: 3, minWidth: 50 }}>
                          <div style={{ width: `${rate}%`, height: '100%', background: rate >= 90 ? '#15803d' : rate >= 75 ? '#d9bb00' : '#8e1e18', borderRadius: 3 }} />
                        </div>
                        <span style={{ fontSize: 12, fontWeight: 600, color: '#0b044d', whiteSpace: 'nowrap' }}>{rate}%</span>
                      </div>
                    </td>
                    <td><span className={`badge-status ${row.status === 'Complete' ? 'processed' : 'pending'}`}>{row.status}</span></td>
                    <td>
                      <div className="row-actions">
                        <button className="btn-view" onClick={() => setViewRow(row)}>DTR</button>
                        <button className="btn-edit" onClick={() => setEditRow(row)}>Edit</button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{filtered.length}</strong> of <strong>{records.length}</strong> records</p>
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
