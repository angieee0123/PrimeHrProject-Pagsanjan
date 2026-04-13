import { useState, useEffect } from 'react';

const employees = {
  employee: {
    id: 'PGS-0115',
    name: 'Ana R. Reyes',
    initials: 'AR',
    avatarColor: '#8e1e18',
    position: 'Nurse II',
    dept: 'Municipal Health Office',
    empType: 'Permanent',
    dateHired: 'Jan 15, 2018',
  },
  'job-order': {
    id: 'JO-0042',
    name: 'Juan D. Cruz',
    initials: 'JD',
    avatarColor: '#1a6e3c',
    position: 'Utility Worker I',
    dept: 'General Services Office',
    empType: 'Job Order',
    dateHired: 'Mar 1, 2024',
  },
};

// Permanent: has GSIS, PhilHealth, Pag-IBIG, tax
const permanentPayslips = [
  { period: 'Jun 16–30, 2025', payDate: 'Jun 30, 2025', status: 'Pending',   basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'Jun 1–15, 2025',  payDate: 'Jun 15, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'May 16–31, 2025', payDate: 'May 31, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'May 1–15, 2025',  payDate: 'May 15, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'Apr 16–30, 2025', payDate: 'Apr 30, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'Apr 1–15, 2025',  payDate: 'Apr 15, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'Mar 16–31, 2025', payDate: 'Mar 31, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
  { period: 'Mar 1–15, 2025',  payDate: 'Mar 15, 2025', status: 'Processed', basic: 16921.50, gsis: 1523, philhealth: 425, pagibig: 50, tax: 1386 },
];

// Job Order: no GSIS, only PhilHealth, Pag-IBIG, tax (lower rate)
const jobOrderPayslips = [
  { period: 'Jun 16–30, 2025', payDate: 'Jun 30, 2025', status: 'Pending',   basic: 12500, gsis: 0, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'Jun 1–15, 2025',  payDate: 'Jun 15, 2025', status: 'Processed', basic: 12500, gsis: 0, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'May 16–31, 2025', payDate: 'May 31, 2025', status: 'Processed', basic: 12500, gsis: 0, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'May 1–15, 2025',  payDate: 'May 15, 2025', status: 'Processed', basic: 12500, gsis: 0, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'Apr 16–30, 2025', payDate: 'Apr 30, 2025', status: 'Processed', basic: 12500, gsis: 0, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'Apr 1–15, 2025',  payDate: 'Apr 15, 2025', status: 'Processed', basic: 12500, gsis: 0, philhealth: 375, pagibig: 100, tax: 775 },
];

const fmt = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 });

/* ── Payslip Detail Modal ── */
function PayslipModal({ slip, emp, onClose }) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [onClose]);

  const totalDeductions = slip.gsis + slip.philhealth + slip.pagibig + slip.tax;
  const netPay = slip.basic - totalDeductions;
  const isJobOrder = emp.empType === 'Job Order';

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" style={{ maxWidth: 520 }} onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">OFFICIAL PAYSLIP · {slip.period.toUpperCase()}</span>
            <h3 className="modal-title">{emp.name}</h3>
            <p className="modal-sub">{emp.position} · {emp.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div className="modal-body" style={{ padding: '0 24px 20px' }}>
          {/* Employee info strip */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, background: '#f7f6ff', borderRadius: 10, padding: '12px 16px', margin: '16px 0' }}>
            <div className="emp-avatar lg" style={{ background: emp.avatarColor }}>{emp.initials}</div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: 13, fontWeight: 700, color: '#0b044d', marginBottom: 2 }}>{emp.id}</p>
              <p style={{ fontSize: 12, color: '#9999bb' }}>{emp.empType} · Hired {emp.dateHired}</p>
            </div>
            <span className={`badge-status ${slip.status === 'Processed' ? 'processed' : 'pending'}`}>{slip.status}</span>
          </div>

          {/* Pay period info */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 18 }}>
            {[{ label: 'Pay Period', value: slip.period }, { label: 'Pay Date', value: slip.payDate }].map(({ label, value }) => (
              <div key={label} style={{ background: '#f7f6ff', borderRadius: 9, padding: '10px 14px' }}>
                <p style={{ fontSize: 10.5, fontWeight: 700, color: '#aaa8cc', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4 }}>{label}</p>
                <p style={{ fontSize: 13.5, fontWeight: 700, color: '#0b044d' }}>{value}</p>
              </div>
            ))}
          </div>

          <p className="modal-section-label">EARNINGS</p>
          <div className="modal-row"><span>Basic Semi-Monthly Pay</span><strong>{fmt(slip.basic)}</strong></div>

          <p className="modal-section-label" style={{ marginTop: 16 }}>DEDUCTIONS</p>
          {!isJobOrder && <div className="modal-row"><span>GSIS Premium</span><span className="modal-deduct">− {fmt(slip.gsis)}</span></div>}
          <div className="modal-row"><span>PhilHealth</span><span className="modal-deduct">− {fmt(slip.philhealth)}</span></div>
          <div className="modal-row"><span>Pag-IBIG</span><span className="modal-deduct">− {fmt(slip.pagibig)}</span></div>
          <div className="modal-row"><span>Withholding Tax</span><span className="modal-deduct">− {fmt(slip.tax)}</span></div>
          <div className="modal-row total">
            <span>Total Deductions</span>
            <span className="modal-deduct">− {fmt(totalDeductions)}</span>
          </div>

          <div className="modal-net-row" style={{ marginTop: 14 }}>
            <span>NET PAY</span>
            <strong>{fmt(netPay)}</strong>
          </div>

          <p style={{ fontSize: 11, color: '#aaa8cc', textAlign: 'center', marginTop: 14, lineHeight: 1.6 }}>
            Municipal Government of Pagsanjan · Human Resource Management Office<br />
            This is a system-generated payslip. No signature required.
          </p>
        </div>

        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download PDF
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── My Payslip Page ── */
export default function MyPayslip({ role = 'employee', searchQuery = '' }) {
  const [selected, setSelected] = useState(null);
  const [statusFilter, setStatusFilter] = useState('All');

  const emp = employees[role] || employees.employee;
  const payslips = role === 'job-order' ? jobOrderPayslips : permanentPayslips;

  const q = searchQuery.toLowerCase();
  const filtered = payslips.filter(s => {
    const matchQ = !q || s.period.toLowerCase().includes(q) || s.status.toLowerCase().includes(q) || s.payDate.toLowerCase().includes(q);
    const matchStatus = statusFilter === 'All' || s.status === statusFilter;
    return matchQ && matchStatus;
  });

  const latest = payslips[0];
  const latestTotalDed = latest.gsis + latest.philhealth + latest.pagibig + latest.tax;
  const latestNet = latest.basic - latestTotalDed;

  return (
    <div>
      {selected && <PayslipModal slip={selected} emp={emp} onClose={() => setSelected(null)} />}

      {/* Banner */}
      <div className="welcome-banner" style={{ marginBottom: 24 }}>
        <div className="banner-left">
          <div style={{ width: 46, height: 46, borderRadius: '50%', background: emp.avatarColor, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: 16, flexShrink: 0 }}>{emp.initials}</div>
          <div>
            <h2 style={{ fontSize: 16, fontWeight: 700, color: '#fff', margin: 0 }}>{emp.name}</h2>
            <p style={{ fontSize: 12, color: 'rgba(255,255,255,0.55)', margin: 0 }}>{emp.position} · {emp.dept} · {emp.id}</p>
          </div>
        </div>
        <div className="banner-right">
          <div className="banner-badge"><span className="banner-badge-dot" />Jun 16–30, 2025 Payroll Active</div>
          <div className="banner-badge outline">Pay Date: Jun 30</div>
        </div>
      </div>

      {/* Latest Payslip Summary Card */}
      <div style={{ background: '#0b044d', borderRadius: 14, padding: '24px 28px', marginBottom: 24, display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 20 }}>
        <div>
          <p style={{ fontSize: 10.5, fontWeight: 700, color: 'rgba(255,255,255,0.4)', letterSpacing: 1.5, textTransform: 'uppercase', marginBottom: 6 }}>Latest Payslip · {latest.period}</p>
          <p style={{ fontSize: 32, fontWeight: 800, color: '#d9bb00', marginBottom: 4 }}>{fmt(latestNet)}</p>
          <p style={{ fontSize: 12.5, color: 'rgba(255,255,255,0.45)' }}>Net Pay · Pay Date: {latest.payDate}</p>
        </div>
        <div style={{ display: 'flex', gap: 24, flexWrap: 'wrap' }}>
          {[
            { label: 'Basic Pay',   value: fmt(latest.basic),    color: 'rgba(255,255,255,0.9)' },
            { label: 'Deductions', value: fmt(latestTotalDed),   color: '#f87171' },
            { label: 'Net Pay',    value: fmt(latestNet),        color: '#d9bb00' },
          ].map(({ label, value, color }) => (
            <div key={label} style={{ textAlign: 'center' }}>
              <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: 0.8, marginBottom: 4 }}>{label}</p>
              <p style={{ fontSize: 16, fontWeight: 700, color }}>{value}</p>
            </div>
          ))}
        </div>
        <button
          onClick={() => setSelected(latest)}
          style={{ background: 'rgba(255,255,255,0.1)', border: '1px solid rgba(255,255,255,0.2)', color: '#fff', padding: '10px 20px', borderRadius: 9, fontSize: 13, fontWeight: 600, cursor: 'pointer', fontFamily: 'Poppins, sans-serif', display: 'flex', alignItems: 'center', gap: 8, transition: 'background 0.2s' }}
          onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.18)'}
          onMouseLeave={e => e.currentTarget.style.background = 'rgba(255,255,255,0.1)'}
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Payslip
        </button>
      </div>

      {/* Payslip History Table */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Payslip History</h3>
            <p className="table-sub">{emp.name} · {emp.id} · Showing {filtered.length} of {payslips.length} records</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
              <option value="All">All Status</option>
              <option>Processed</option>
              <option>Pending</option>
            </select>
            <button className="btn-export">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              Export All
            </button>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Pay Period</th>
                <th>Basic Pay</th>
                <th>Total Deductions</th>
                <th>Net Pay</th>
                <th>Pay Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={7} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No payslips match your search.</td></tr>
              )}
              {filtered.map((slip, i) => {
                const totalDed = slip.gsis + slip.philhealth + slip.pagibig + slip.tax;
                const net = slip.basic - totalDed;
                const isLatest = payslips.indexOf(slip) === 0;
                return (
                  <tr key={i}>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 8, height: 8, borderRadius: '50%', background: isLatest ? '#d9bb00' : '#e4e3f0', flexShrink: 0 }} />
                        <span style={{ fontWeight: 600, color: '#0b044d', fontSize: 13 }}>{slip.period}</span>
                        {isLatest && <span style={{ fontSize: 10, fontWeight: 700, background: '#fefce8', color: '#a16207', padding: '2px 8px', borderRadius: 20, border: '1px solid #fde68a' }}>LATEST</span>}
                      </div>
                    </td>
                    <td className="pay-cell">{fmt(slip.basic)}</td>
                    <td className="deduction">− {fmt(totalDed)}</td>
                    <td className="net-pay">{fmt(net)}</td>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>{slip.payDate}</td>
                    <td>
                      <span className={`badge-status ${slip.status === 'Processed' ? 'processed' : 'pending'}`}>
                        {slip.status}
                      </span>
                    </td>
                    <td>
                      <button className="btn-view" onClick={() => setSelected(slip)}>View</button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{filtered.length}</strong> of <strong>{payslips.length}</strong> payslips for <strong>{emp.name}</strong></p>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9999bb" strokeWidth="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <p style={{ fontSize: 11.5, color: '#9999bb' }}>Only your payslips are visible. Data is confidential.</p>
          </div>
        </div>
      </section>
    </div>
  );
}
