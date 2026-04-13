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

// Semi-monthly amounts (monthly basic ÷ 2, deductions ÷ 2)
const initialPayroll = [
  { id: 'PGS-0041', name: 'Maria B. Santos',    position: 'Administrative Officer IV',   dept: 'Office of the Mayor',              basic: 21079.50, gsis: 1897,    philhealth: 525,    pagibig: 50, tax: 1743.50, status: 'Processed' },
  { id: 'PGS-0082', name: 'Juan P. dela Cruz',  position: 'Municipal Engineer II',        dept: 'Office of the Mun. Engineer',      basic: 19042.50, gsis: 1714,    philhealth: 475,    pagibig: 50, tax: 1569.50, status: 'Processed' },
  { id: 'PGS-0115', name: 'Ana R. Reyes',       position: 'Nurse II',                    dept: 'Municipal Health Office',          basic: 16921.50, gsis: 1523,    philhealth: 425,    pagibig: 50, tax: 1386,    status: 'Pending'   },
  { id: 'PGS-0203', name: 'Carlos M. Mendoza',  position: 'Municipal Treasurer III',     dept: 'Office of the Mun. Treasurer',     basic: 23627.50, gsis: 2126.50, philhealth: 575,    pagibig: 50, tax: 1974,    status: 'Processed' },
  { id: 'PGS-0267', name: 'Liza G. Gomez',      position: 'Social Welfare Officer II',   dept: 'MSWD – Pagsanjan',                 basic: 17548.50, gsis: 1579.50, philhealth: 437.50, pagibig: 50, tax: 1442.50, status: 'On Hold'   },
  { id: 'PGS-0310', name: 'Roberto T. Flores',  position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar',        basic: 15265.50, gsis: 1374,    philhealth: 387.50, pagibig: 50, tax: 1241.50, status: 'Processed' },
  { id: 'PGS-0342', name: 'Grace A. Villanueva',position: 'Budget Officer II',           dept: 'Office of the Mun. Budget',        basic: 14500,    gsis: 1305,    philhealth: 362.50, pagibig: 50, tax: 1100,    status: 'Pending'   },
  { id: 'PGS-0358', name: 'Ramon D. Cruz',      position: 'Agriculturist I',             dept: 'Office of the Mun. Agriculturist', basic: 13500,    gsis: 1215,    philhealth: 337.50, pagibig: 50, tax: 990,     status: 'Processed' },
];

const peso = n => '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const years  = ['2025','2024','2023'];
const semiPeriods = ['1st (1–15)', '2nd (16–30)'];
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

/* ── Payslip Modal ── */
function PayslipModal({ row, idx, period, payDate, onClose }) {
  useEscape(onClose);
  const deductions = row.gsis + row.philhealth + row.pagibig + row.tax;
  const net = row.basic - deductions;
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PAYSLIP · {period.toUpperCase()}</span>
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
              <span className={`badge-status ${row.status === 'Processed' ? 'processed' : row.status === 'Pending' ? 'pending' : 'on-hold'}`}>{row.status}</span>
            </div>
          </div>

          <div className="modal-section-label">PAY PERIOD</div>
          <div className="modal-row"><span>Period</span><strong>{period}</strong></div>
          <div className="modal-row"><span>Pay Date</span><strong>{payDate}</strong></div>

          <div className="modal-section-label" style={{ marginTop: 16 }}>EARNINGS</div>
          <div className="modal-row"><span>Basic Semi-Monthly Pay</span><strong>{peso(row.basic)}</strong></div>

          <div className="modal-section-label" style={{ marginTop: 16 }}>DEDUCTIONS</div>
          <div className="modal-row"><span>GSIS Premium</span><span className="modal-deduct">{peso(row.gsis)}</span></div>
          <div className="modal-row"><span>PhilHealth</span><span className="modal-deduct">{peso(row.philhealth)}</span></div>
          <div className="modal-row"><span>Pag-IBIG</span><span className="modal-deduct">{peso(row.pagibig)}</span></div>
          <div className="modal-row"><span>Withholding Tax</span><span className="modal-deduct">{peso(row.tax)}</span></div>
          <div className="modal-row total"><span>Total Deductions</span><span className="modal-deduct">{peso(deductions)}</span></div>

          <div className="modal-net-row">
            <span>NET PAY</span>
            <strong>{peso(net)}</strong>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Payslip
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── Run Payroll Confirm Modal ── */
function RunPayrollModal({ period, payDate, total, gross, onClose, onConfirm }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-sm" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PAYROLL PROCESSING</span>
            <h3 className="modal-title">Process {period} Payroll?</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-confirm-info">
            <div className="modal-row"><span>Pay Period</span><strong>{period}</strong></div>
            <div className="modal-row"><span>Total Personnel</span><strong>{total}</strong></div>
            <div className="modal-row"><span>Gross Payroll</span><strong>{peso(gross)}</strong></div>
            <div className="modal-row"><span>Pay Date</span><strong>{payDate}</strong></div>
          </div>
          <p className="modal-warn">⚠ This will finalize payroll for all listed employees. Ensure all DTR and leave records are updated before proceeding.</p>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={onConfirm}>Confirm & Process</button>
        </div>
      </div>
    </div>
  );
}

/* ── Success Modal ── */
function SuccessModal({ period, payDate, total, onClose }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-sm" onClick={e => e.stopPropagation()}>
        <div className="modal-success-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div className="modal-body" style={{ textAlign: 'center' }}>
          <h3 className="modal-title" style={{ marginBottom: 8 }}>Payroll Processed!</h3>
          <p style={{ fontSize: 13.5, color: '#6b6a8a', marginBottom: 16 }}>
            {period} payroll for <strong>{total} employees</strong> has been successfully processed.
          </p>
          <div className="modal-confirm-info">
            <div className="modal-row"><span>Reference No.</span><strong>PAY-2025-06-002</strong></div>
            <div className="modal-row"><span>Pay Date</span><strong>{payDate}</strong></div>
            <div className="modal-row"><span>Processed by</span><strong>Admin User</strong></div>
            <div className="modal-row"><span>Date & Time</span><strong>Jun 25, 2025 · 10:42 AM</strong></div>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-primary" style={{ width: '100%', justifyContent: 'center' }} onClick={onClose}>Done</button>
        </div>
      </div>
    </div>
  );
}

/* ── Edit Row Modal ── */
function EditPayrollModal({ row, onClose, onSave }) {
  useEscape(onClose);
  const [form, setForm] = useState({ ...row });
  const set = (k, v) => setForm(f => ({ ...f, [k]: Number(v) || 0 }));
  const deductions = form.gsis + form.philhealth + form.pagibig + form.tax;
  const net = form.basic - deductions;
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">EDIT PAYROLL RECORD</span>
            <h3 className="modal-title">{row.name}</h3>
            <p className="modal-sub">{row.id}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field form-full">
              <label>Basic Semi-Monthly Pay (₱)</label>
              <input type="number" value={form.basic} onChange={e => set('basic', e.target.value)} />
            </div>
            <div className="form-field"><label>GSIS (₱)</label><input type="number" value={form.gsis} onChange={e => set('gsis', e.target.value)} /></div>
            <div className="form-field"><label>PhilHealth (₱)</label><input type="number" value={form.philhealth} onChange={e => set('philhealth', e.target.value)} /></div>
            <div className="form-field"><label>Pag-IBIG (₱)</label><input type="number" value={form.pagibig} onChange={e => set('pagibig', e.target.value)} /></div>
            <div className="form-field"><label>Withholding Tax (₱)</label><input type="number" value={form.tax} onChange={e => set('tax', e.target.value)} /></div>
            <div className="form-field">
              <label>Status</label>
              <select value={form.status} onChange={e => setForm(f => ({ ...f, status: e.target.value }))}>
                <option>Processed</option><option>Pending</option><option>On Hold</option>
              </select>
            </div>
          </div>
          <div className="modal-net-row" style={{ marginTop: 16 }}>
            <span>NET PAY PREVIEW</span>
            <strong>{peso(net)}</strong>
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

/* ── Payroll Page ── */
export default function Payroll({ searchQuery = '' }) {
  const [records, setRecords]           = useState(initialPayroll);
  const [deptFilter, setDeptFilter]     = useState('All Departments');
  const [statusFilter, setStatusFilter] = useState('All');
  const [monthFilter, setMonthFilter]   = useState('June');
  const [yearFilter, setYearFilter]     = useState('2025');
  const [semiFilter, setSemiFilter]     = useState('2nd (16–30)');
  const [viewRow, setViewRow]           = useState(null);
  const [editRow, setEditRow]           = useState(null);
  const [showConfirm, setShowConfirm]   = useState(false);
  const [showSuccess, setShowSuccess]   = useState(false);

  const isFirst = semiFilter === '1st (1–15)';
  const periodLabel = `${monthFilter} ${isFirst ? '1–15' : '16–30'}, ${yearFilter}`;
  const payDate = `${monthFilter.slice(0, 3)} ${isFirst ? '15' : '30'}, ${yearFilter}`;

  const filtered = records.filter(r => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || r.name.toLowerCase().includes(q) || r.id.toLowerCase().includes(q);
    const matchDept   = deptFilter === 'All Departments' || r.dept === deptFilter;
    const matchStatus = statusFilter === 'All' || r.status === statusFilter;
    return matchSearch && matchDept && matchStatus;
  });

  const grossPayroll   = records.reduce((s, r) => s + r.basic, 0);
  const totalNet       = records.reduce((s, r) => s + (r.basic - r.gsis - r.philhealth - r.pagibig - r.tax), 0);
  const totalDeduct    = grossPayroll - totalNet;
  const processedCount = records.filter(r => r.status === 'Processed').length;
  const pendingCount   = records.filter(r => r.status === 'Pending').length;

  const handleRunPayroll = () => {
    setRecords(r => r.map(e => e.status === 'Pending' ? { ...e, status: 'Processed' } : e));
    setShowConfirm(false);
    setShowSuccess(true);
  };

  const handleSaveEdit = form => {
    setRecords(r => r.map(e => e.id === form.id ? { ...e, ...form } : e));
    setEditRow(null);
  };

  return (
    <div>
      {/* Modals */}
      {viewRow && (
        <PayslipModal
          row={viewRow}
          idx={records.findIndex(r => r.id === viewRow.id)}
          period={periodLabel}
          payDate={payDate}
          onClose={() => setViewRow(null)}
        />
      )}
      {editRow && (
        <EditPayrollModal
          row={editRow}
          onClose={() => setEditRow(null)}
          onSave={handleSaveEdit}
        />
      )}
      {showConfirm && (
        <RunPayrollModal
          period={periodLabel}
          payDate={payDate}
          total={records.length}
          gross={grossPayroll}
          onClose={() => setShowConfirm(false)}
          onConfirm={handleRunPayroll}
        />
      )}
      {showSuccess && (
        <SuccessModal
          period={periodLabel}
          payDate={payDate}
          total={records.length}
          onClose={() => setShowSuccess(false)}
        />
      )}

      {/* Stats */}
      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'Gross Payroll',    value: peso(grossPayroll), sub: periodLabel,              accent: '#0b044d', icon: 'peso' },
          { label: 'Total Net Pay',    value: peso(totalNet),     sub: 'After deductions',       accent: '#15803d', icon: 'creditCard' },
          { label: 'Total Deductions', value: peso(totalDeduct),  sub: 'GSIS, PhilHealth etc',   accent: '#8e1e18', icon: 'minus' },
          { label: 'Pending Records',  value: pendingCount,       sub: `${processedCount} processed`, accent: '#d9bb00', icon: 'clock' },
        ].map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '18' }}>
                <Icon name={s.icon} size={16} color={s.accent} />
              </div>
            </div>
            <h2 className="stat-value" style={{ fontSize: 18 }}>{s.value}</h2>
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
            <h3 className="table-title">Payroll Register — {periodLabel}</h3>
            <p className="table-sub">Municipal Government of Pagsanjan · Pay Date: {payDate} · {filtered.length} of {records.length} records</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={semiFilter} onChange={e => setSemiFilter(e.target.value)}>
              {semiPeriods.map(p => <option key={p}>{p}</option>)}
            </select>
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
              <option>Processed</option><option>Pending</option><option>On Hold</option>
            </select>
            <button className="btn-export">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export
            </button>
            <button className="modal-btn-primary" style={{ padding: '7px 16px', fontSize: 12.5 }} onClick={() => setShowConfirm(true)}>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Run Payroll
            </button>
          </div>
        </div>

        {/* Summary Bar */}
        <div className="payroll-summary-bar" style={{ marginTop: 0, marginBottom: 16 }}>
          <div className="psummary-item">
            <span>Gross Total</span>
            <strong>{peso(filtered.reduce((s, r) => s + r.basic, 0))}</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Total Deductions</span>
            <strong className="deduction">{peso(filtered.reduce((s, r) => s + r.gsis + r.philhealth + r.pagibig + r.tax, 0))}</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Total Net Pay</span>
            <strong className="net-pay">{peso(filtered.reduce((s, r) => s + (r.basic - r.gsis - r.philhealth - r.pagibig - r.tax), 0))}</strong>
          </div>
          <div className="psummary-divider" />
          <div className="psummary-item">
            <span>Pay Date</span>
            <strong>{payDate}</strong>
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
                <th>Basic Pay</th>
                <th>GSIS</th>
                <th>PhilHealth</th>
                <th>Pag-IBIG</th>
                <th>Tax</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={10} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No records found.</td></tr>
              )}
              {filtered.map((row) => {
                const deductions = row.gsis + row.philhealth + row.pagibig + row.tax;
                const net = row.basic - deductions;
                const realIdx = records.findIndex(r => r.id === row.id);
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
                    <td className="pay-cell">{peso(row.basic)}</td>
                    <td className="deduction">{peso(row.gsis)}</td>
                    <td className="deduction">{peso(row.philhealth)}</td>
                    <td className="deduction">{peso(row.pagibig)}</td>
                    <td className="deduction">{peso(row.tax)}</td>
                    <td className="net-pay">{peso(net)}</td>
                    <td><span className={`badge-status ${row.status.toLowerCase().replace(' ', '-')}`}>{row.status}</span></td>
                    <td>
                      <div className="row-actions">
                        <button className="btn-view" onClick={() => setViewRow(row)}>Payslip</button>
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
