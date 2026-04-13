import { useState, useEffect } from 'react';

const emp = {
  id: 'JO-0042',
  name: 'Juan D. Cruz',
  initials: 'JD',
  avatarColor: '#1a6e3c',
  position: 'Utility Worker I',
  dept: 'General Services Office',
  empType: 'Job Order',
  contractStart: 'Jan 1, 2025',
  contractEnd: 'Dec 31, 2025',
};

// Salary formula:
// Gross = dailyRate × daysWorked
// Late deduction = (dailyRate / 8 / 60) × totalLateMinutes
const MONTHLY_RATE = 25000;
const DAILY_RATE   = MONTHLY_RATE / 22;          // ≈ ₱1,136.36/day
const PER_MINUTE   = DAILY_RATE / 8 / 60;        // rate per late minute

// lateMinutes = total accumulated late minutes in the pay period
const payslips = [
  { period: 'Jun 16–30, 2025', payDate: 'Jun 30, 2025', status: 'Pending',   daysWorked: 10, lateMinutes: 10, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'Jun 1–15, 2025',  payDate: 'Jun 15, 2025', status: 'Processed', daysWorked: 11, lateMinutes: 20, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'May 16–31, 2025', payDate: 'May 31, 2025', status: 'Processed', daysWorked: 11, lateMinutes: 0,  philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'May 1–15, 2025',  payDate: 'May 15, 2025', status: 'Processed', daysWorked: 10, lateMinutes: 15, philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'Apr 16–30, 2025', payDate: 'Apr 30, 2025', status: 'Processed', daysWorked: 11, lateMinutes: 0,  philhealth: 375, pagibig: 100, tax: 775 },
  { period: 'Apr 1–15, 2025',  payDate: 'Apr 15, 2025', status: 'Processed', daysWorked: 11, lateMinutes: 0,  philhealth: 375, pagibig: 100, tax: 775 },
];

const fmt2       = n => n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
const fmt        = n => '\u20b1' + fmt2(n);
const grossPay   = s => parseFloat((DAILY_RATE * s.daysWorked).toFixed(2));
const lateDeduct = s => parseFloat((PER_MINUTE * s.lateMinutes).toFixed(2));
const totalDed   = s => parseFloat((s.philhealth + s.pagibig + s.tax + lateDeduct(s)).toFixed(2));
const netPay     = s => parseFloat((grossPay(s) - totalDed(s)).toFixed(2));

function PayslipModal({ slip, onClose }) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [onClose]);

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
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, background: '#f7f6ff', borderRadius: 10, padding: '12px 16px', margin: '16px 0' }}>
            <div className="emp-avatar lg" style={{ background: emp.avatarColor }}>{emp.initials}</div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: 13, fontWeight: 700, color: '#0b044d', marginBottom: 2 }}>{emp.id}</p>
              <p style={{ fontSize: 12, color: '#9999bb' }}>{emp.empType} · Contract ends {emp.contractEnd}</p>
            </div>
            <span className={`badge-status ${slip.status === 'Processed' ? 'processed' : 'pending'}`}>{slip.status}</span>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 18 }}>
            {[{ label: 'Pay Period', value: slip.period }, { label: 'Pay Date', value: slip.payDate }].map(({ label, value }) => (
              <div key={label} style={{ background: '#f7f6ff', borderRadius: 9, padding: '10px 14px' }}>
                <p style={{ fontSize: 10.5, fontWeight: 700, color: '#aaa8cc', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4 }}>{label}</p>
                <p style={{ fontSize: 13.5, fontWeight: 700, color: '#0b044d' }}>{value}</p>
              </div>
            ))}
          </div>

          <p className="modal-section-label">EARNINGS</p>
          <div className="modal-row">
            <span>Daily Rate <span style={{ fontSize: 11, color: '#9999bb' }}>(Monthly ÷ 22 days)</span></span>
            <strong>{fmt(DAILY_RATE)}</strong>
          </div>
          <div className="modal-row">
            <span>Days Worked</span>
            <strong>{slip.daysWorked} days</strong>
          </div>
          <div className="modal-row" style={{ borderTop: '1px solid #eceaf8' }}>
            <span>Gross Pay <span style={{ fontSize: 11, color: '#9999bb' }}>({slip.daysWorked} × {fmt(DAILY_RATE)})</span></span>
            <strong>{fmt(grossPay(slip))}</strong>
          </div>

          <p className="modal-section-label" style={{ marginTop: 16 }}>DEDUCTIONS</p>
          {slip.lateMinutes > 0 && (
            <div className="modal-row">
              <span>
                Late Deduction
                <span style={{ fontSize: 11, color: '#9999bb', display: 'block' }}>
                  {fmt(DAILY_RATE)} ÷ 8 ÷ 60 × {slip.lateMinutes} min
                </span>
              </span>
              <span className="modal-deduct">− {fmt(lateDeduct(slip))}</span>
            </div>
          )}
          <div className="modal-row"><span>PhilHealth</span><span className="modal-deduct">− {fmt(slip.philhealth)}</span></div>
          <div className="modal-row"><span>Pag-IBIG</span><span className="modal-deduct">− {fmt(slip.pagibig)}</span></div>
          <div className="modal-row"><span>Withholding Tax</span><span className="modal-deduct">− {fmt(slip.tax)}</span></div>
          <div className="modal-row total">
            <span>Total Deductions</span>
            <span className="modal-deduct">− {fmt(totalDed(slip))}</span>
          </div>

          <div className="modal-net-row" style={{ marginTop: 14 }}>
            <span>NET PAY</span>
            <strong>{fmt(netPay(slip))}</strong>
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

export default function JobOrderPayslip({ searchQuery = '' }) {
  const [selected, setSelected] = useState(null);
  const [statusFilter, setStatusFilter] = useState('All');

  const q = searchQuery.toLowerCase();
  const filtered = payslips.filter(s => {
    const matchQ = !q || s.period.toLowerCase().includes(q) || s.status.toLowerCase().includes(q) || s.payDate.toLowerCase().includes(q);
    const matchStatus = statusFilter === 'All' || s.status === statusFilter;
    return matchQ && matchStatus;
  });

  const latest = payslips[0];

  return (
    <div>
      {selected && <PayslipModal slip={selected} onClose={() => setSelected(null)} />}

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

      {/* Latest Summary Card */}
      <div style={{ background: '#0b044d', borderRadius: 14, padding: '24px 28px', marginBottom: 24, display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 20 }}>
        <div>
          <p style={{ fontSize: 10.5, fontWeight: 700, color: 'rgba(255,255,255,0.4)', letterSpacing: 1.5, textTransform: 'uppercase', marginBottom: 6 }}>Latest Payslip · {latest.period}</p>
          <p style={{ fontSize: 32, fontWeight: 800, color: '#d9bb00', marginBottom: 4 }}>{fmt(netPay(latest))}</p>
          <p style={{ fontSize: 12.5, color: 'rgba(255,255,255,0.45)' }}>Net Pay · Pay Date: {latest.payDate}</p>
        </div>
        <div style={{ display: 'flex', gap: 24, flexWrap: 'wrap' }}>
          {[
            { label: 'Daily Rate',   value: fmt(DAILY_RATE),        color: 'rgba(255,255,255,0.9)' },
            { label: 'Gross Pay',    value: fmt(grossPay(latest)),   color: 'rgba(255,255,255,0.9)' },
            { label: 'Deductions',   value: fmt(totalDed(latest)),   color: '#f87171' },
            { label: 'Net Pay',      value: fmt(netPay(latest)),     color: '#d9bb00' },
          ].map(({ label, value, color }) => (
            <div key={label} style={{ textAlign: 'center' }}>
              <p style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: 0.8, marginBottom: 4 }}>{label}</p>
              <p style={{ fontSize: 16, fontWeight: 700, color }}>{value}</p>
            </div>
          ))}
        </div>
        <button
          onClick={() => setSelected(latest)}
          style={{ background: 'rgba(255,255,255,0.1)', border: '1px solid rgba(255,255,255,0.2)', color: '#fff', padding: '10px 20px', borderRadius: 9, fontSize: 13, fontWeight: 600, cursor: 'pointer', fontFamily: 'Poppins, sans-serif', display: 'flex', alignItems: 'center', gap: 8 }}
          onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.18)'}
          onMouseLeave={e => e.currentTarget.style.background = 'rgba(255,255,255,0.1)'}
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Payslip
        </button>
      </div>

      {/* Salary formula note */}
      <div style={{ background: '#f0fdf4', border: '1.5px solid #bbf7d0', borderRadius: 10, padding: '14px 18px', marginBottom: 20 }}>
        <p style={{ fontSize: 12.5, color: '#15803d', fontWeight: 700, marginBottom: 6 }}>&#x2139;&#xFE0F; Job Order Salary Computation</p>
        <p style={{ fontSize: 12, color: '#166534', lineHeight: 1.8, margin: 0 }}>
          <strong>Gross Pay</strong> = Daily Rate ({fmt(DAILY_RATE)}) &times; Days Worked<br />
          <strong>Late Deduction</strong> = Daily Rate &divide; 8 &divide; 60 &times; Total Late Minutes<br />
          No GSIS. Deductions: PhilHealth, Pag-IBIG, Withholding Tax &amp; Late.
        </p>
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
                <th>Days Worked</th>
                <th>Gross Pay</th>
                <th>Late Deduction</th>
                <th>Total Deductions</th>
                <th>Net Pay</th>
                <th>Pay Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={9} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No payslips match your search.</td></tr>
              )}
              {filtered.map((slip, i) => {
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
                    <td style={{ fontSize: 13, color: '#0b044d', fontWeight: 600 }}>{slip.daysWorked} days</td>
                    <td className="pay-cell">{fmt(grossPay(slip))}</td>
                    <td style={{ fontSize: 13, color: slip.lateMinutes > 0 ? '#a16207' : '#c0bedd', fontWeight: slip.lateMinutes > 0 ? 600 : 400 }}>
                      {slip.lateMinutes > 0 ? `− ${fmt(lateDeduct(slip))}` : '—'}
                    </td>
                    <td className="deduction">− {fmt(totalDed(slip))}</td>
                    <td className="net-pay">{fmt(netPay(slip))}</td>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>{slip.payDate}</td>
                    <td>
                      <span className={`badge-status ${slip.status === 'Processed' ? 'processed' : 'pending'}`}>{slip.status}</span>
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
