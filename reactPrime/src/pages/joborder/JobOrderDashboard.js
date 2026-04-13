import { useState } from 'react';
import { Icon } from '../../components/Icons';

const emp = {
  id: 'JO-0042',
  name: 'Juan D. Cruz',
  initials: 'JD',
  position: 'Utility Worker I',
  dept: 'General Services Office',
  empType: 'Job Order',
  contractStart: 'Jan 1, 2025',
  contractEnd: 'Dec 31, 2025',
};

const recentPayslips = [
  { period: 'Jun 16–30, 2025', net: '₱11,250.00', status: 'Pending',   date: 'Jun 30, 2025' },
  { period: 'Jun 1–15, 2025',  net: '₱11,250.00', status: 'Processed', date: 'Jun 15, 2025' },
  { period: 'May 16–31, 2025', net: '₱11,250.00', status: 'Processed', date: 'May 31, 2025' },
];

function PayslipModal({ onClose }) {
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PAYSLIP · JUN 16–30, 2025</span>
            <h3 className="modal-title">{emp.name}</h3>
            <p className="modal-sub">{emp.position} · {emp.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-emp-row">
            <div style={{ width: 44, height: 44, borderRadius: '50%', overflow: 'hidden', flexShrink: 0, boxShadow: '0 2px 8px rgba(0,0,0,0.15)' }}>
              <img src="/municipal-of-pagsanjan-logo.jpg" alt="Profile" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            </div>
            <div>
              <p className="modal-emp-id">{emp.id}</p>
              <span className="badge-status pending">Pending</span>
            </div>
          </div>
          <div className="modal-section-label">EARNINGS</div>
          <div className="modal-row"><span>Basic Semi-Monthly Pay</span><strong>₱12,500.00</strong></div>
          <div className="modal-section-label" style={{ marginTop: 16 }}>DEDUCTIONS</div>
          <div className="modal-row"><span>PhilHealth</span><span className="modal-deduct">₱375</span></div>
          <div className="modal-row"><span>Pag-IBIG</span><span className="modal-deduct">₱100</span></div>
          <div className="modal-row"><span>Withholding Tax</span><span className="modal-deduct">₱775</span></div>
          <div className="modal-row total"><span>Total Deductions</span><span className="modal-deduct">₱1,250</span></div>
          <div className="modal-net-row"><span>NET PAY</span><strong>₱11,250.00</strong></div>
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

export default function JobOrderDashboard({ notifs, setNotifs }) {
  const [showPayslip, setShowPayslip] = useState(false);

  const unread = notifs ? notifs.filter(n => !n.read).length : 0;

  const stats = [
    { label: 'Basic Pay',      value: '₱12,500.00', sub: 'Jun 16–30, 2025',       icon: 'creditCard', accent: '#0b044d' },
    { label: 'Net Pay',        value: '₱11,250.00', sub: 'After deductions',       icon: 'checkCircle', accent: '#15803d' },
    { label: 'Contract End',   value: 'Dec 31',     sub: emp.contractEnd,          icon: 'fileText', accent: '#1a6e3c' },
    { label: 'Attendance',     value: '90%',        sub: '19 days present',        icon: 'calendar', accent: '#8e1e18' },
  ];

  const quickActions = [
    { icon: 'creditCard', label: 'View Payslip',    action: () => setShowPayslip(true) },
    { icon: 'calendar', label: 'View Attendance', action: () => {} },
    { icon: 'user', label: 'My Profile',      action: () => {} },
    { icon: 'shield', label: 'Security',        action: () => {} },
  ];

  return (
    <div>
      {showPayslip && <PayslipModal onClose={() => setShowPayslip(false)} />}

      {/* Welcome Banner */}
      <div className="welcome-banner" style={{ marginBottom: 24 }}>
        <div className="banner-left">
          <div style={{ width: 46, height: 46, borderRadius: '50%', overflow: 'hidden', flexShrink: 0, boxShadow: '0 4px 12px rgba(0,0,0,0.2)' }}>
            <img src="/municipal-of-pagsanjan-logo.jpg" alt="Profile" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
          </div>
          <div>
            <h2 style={{ fontSize: 17, fontWeight: 700, color: '#ffffff', margin: 0 }}>Welcome back, Juan!</h2>
            <p style={{ fontSize: 12, color: 'rgba(255,255,255,0.55)', margin: 0 }}>{emp.position} · {emp.dept} · {emp.id}</p>
          </div>
        </div>
        <div className="banner-right">
          <div className="banner-badge"><span className="banner-badge-dot" />June 2025 Payroll Active</div>
          <div className="banner-badge outline">Contract Until: Dec 31, 2025</div>
        </div>
      </div>

      {/* Stats */}
      <section className="stats-grid" style={{ marginBottom: 24 }}>
        {stats.map((s, i) => (
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

      {/* Bottom Grid */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20 }}>

        {/* Payslip History */}
        <section className="table-section" style={{ marginBottom: 0 }}>
          <div className="table-header" style={{ padding: '16px 20px' }}>
            <div>
              <h3 className="table-title">My Payslips</h3>
              <p className="table-sub">Recent payroll history</p>
            </div>
            <button className="btn-view" onClick={() => setShowPayslip(true)}>View Latest</button>
          </div>
          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr><th>Period</th><th>Net Pay</th><th>Pay Date</th><th>Status</th></tr>
              </thead>
              <tbody>
                {recentPayslips.map((p, i) => (
                  <tr key={i}>
                    <td style={{ fontWeight: 600, color: '#0b044d', fontSize: 13 }}>{p.period}</td>
                    <td className="net-pay">{p.net}</td>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>{p.date}</td>
                    <td><span className={`badge-status ${p.status.toLowerCase()}`}>{p.status}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>

        {/* Right column */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>

          {/* Notifications */}
          <section className="table-section" style={{ marginBottom: 0 }}>
            <div className="table-header" style={{ padding: '16px 20px' }}>
              <div>
                <h3 className="table-title">Notifications</h3>
                <p className="table-sub">{unread} unread</p>
              </div>
              {unread > 0 && (
                <button className="btn-view" onClick={() => setNotifs(notifs.map(n => ({ ...n, read: true })))}>
                  Mark all read
                </button>
              )}
            </div>
            <div style={{ padding: '0 4px 8px' }}>
              {(notifs || []).length === 0 && (
                <p style={{ fontSize: 13, color: '#aaa8cc', padding: '12px 16px' }}>No notifications.</p>
              )}
              {(notifs || []).map(n => (
                <div key={n.id} style={{ display: 'flex', gap: 12, padding: '12px 16px', borderBottom: '1px solid #f4f3ff', opacity: n.read ? 0.55 : 1 }}>
                  <div style={{ width: 8, height: 8, borderRadius: '50%', marginTop: 5, flexShrink: 0, background: n.type === 'warning' ? '#d9bb00' : '#1a6e3c' }} />
                  <div>
                    <p style={{ fontSize: 13, fontWeight: 600, color: '#0b044d', marginBottom: 2 }}>{n.title}</p>
                    <p style={{ fontSize: 12, color: '#6b6a8a', marginBottom: 3 }}>{n.desc}</p>
                    <span style={{ fontSize: 11, color: '#aaa8cc' }}>{n.time}</span>
                  </div>
                </div>
              ))}
            </div>
          </section>

          {/* Quick Actions */}
          <section className="table-section" style={{ marginBottom: 0 }}>
            <div style={{ padding: '16px 20px 8px' }}>
              <h3 className="table-title" style={{ marginBottom: 4 }}>Quick Actions</h3>
              <p className="table-sub" style={{ marginBottom: 14 }}>Common tasks</p>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, paddingBottom: 12 }}>
                {quickActions.map((a, i) => (
                  <button key={i} onClick={a.action}
                    style={{ display: 'flex', alignItems: 'center', gap: 9, padding: '10px 14px', border: '1.5px solid #eceaf8', borderRadius: 10, background: '#fafafe', cursor: 'pointer', fontSize: 13, fontWeight: 600, color: '#0b044d', fontFamily: 'Poppins, sans-serif', transition: 'border-color 0.18s' }}
                    onMouseEnter={e => e.currentTarget.style.borderColor = '#1a6e3c'}
                    onMouseLeave={e => e.currentTarget.style.borderColor = '#eceaf8'}
                  >
                    <Icon name={a.icon} size={16} color="#0b044d" />
                    {a.label}
                  </button>
                ))}
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  );
}
