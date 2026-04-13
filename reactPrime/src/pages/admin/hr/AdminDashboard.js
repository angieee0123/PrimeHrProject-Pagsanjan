import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#0b044d'];
const initials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

const stats = [
  { label: 'Total Personnel',      value: '348',        sub: '+6 this month',        accent: '#0b044d', icon: 'users' },
  { label: 'Semi-Monthly Payroll', value: '₱2,436,300', sub: '+1.8% vs last period', accent: '#8e1e18', icon: 'peso' },
  { label: 'Open Job Positions',   value: '8',          sub: '45 applicants',        accent: '#15803d', icon: 'user' },
  { label: 'Ongoing Trainings',    value: '5',          sub: '83 participants',      accent: '#d9bb00', icon: 'bookOpen' },
  { label: 'Pending Evaluations',  value: '12',         sub: 'Due by Jun 30',        accent: '#1a6e3c', icon: 'activity' },
  { label: 'Avg Performance',      value: '4.7',        sub: 'Out of 5.0',           accent: '#6b3fa0', icon: 'award' },
];

const payrollData = [
  { id: 'PGS-0041', name: 'Maria B. Santos',   position: 'Administrative Officer IV',   dept: 'Office of the Mayor',          basic: '₱21,079.50', deductions: '₱4,215.50', net: '₱16,864.00', status: 'Processed', gsis: '₱1,897',    philhealth: '₱525',    pagibig: '₱50', tax: '₱1,743.50' },
  { id: 'PGS-0082', name: 'Juan P. dela Cruz', position: 'Municipal Engineer II',        dept: 'Office of the Mun. Engineer',  basic: '₱19,042.50', deductions: '₱3,809.00', net: '₱15,233.50', status: 'Processed', gsis: '₱1,714',    philhealth: '₱475',    pagibig: '₱50', tax: '₱1,569.50' },
  { id: 'PGS-0115', name: 'Ana R. Reyes',      position: 'Nurse II',                    dept: 'Municipal Health Office',      basic: '₱16,921.50', deductions: '₱3,384.00', net: '₱13,537.50', status: 'Pending',   gsis: '₱1,523',    philhealth: '₱425',    pagibig: '₱50', tax: '₱1,386'    },
  { id: 'PGS-0203', name: 'Carlos M. Mendoza', position: 'Municipal Treasurer III',     dept: 'Office of the Mun. Treasurer', basic: '₱23,627.50', deductions: '₱4,725.50', net: '₱18,902.00', status: 'Processed', gsis: '₱2,126.50', philhealth: '₱575',    pagibig: '₱50', tax: '₱1,974'    },
  { id: 'PGS-0267', name: 'Liza G. Gomez',     position: 'Social Welfare Officer II',   dept: 'MSWD – Pagsanjan',             basic: '₱17,548.50', deductions: '₱3,509.50', net: '₱14,039.00', status: 'On Hold',   gsis: '₱1,579.50', philhealth: '₱437.50', pagibig: '₱50', tax: '₱1,442.50' },
  { id: 'PGS-0310', name: 'Roberto T. Flores', position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar',    basic: '₱15,265.50', deductions: '₱3,053.00', net: '₱12,212.50', status: 'Processed', gsis: '₱1,374',    philhealth: '₱387.50', pagibig: '₱50', tax: '₱1,241.50' },
];

const departments = [
  'All Departments','Office of the Mayor','Office of the Vice Mayor','Sangguniang Bayan',
  'Office of the Mun. Treasurer',"Municipal Assessor's Office",'Municipal Civil Registrar',
  'Municipal Health Office','MSWD – Pagsanjan',"Municipal Planning & Dev't Office",
  'Office of the Mun. Engineer','Office of the Mun. Agriculturist',
  'Municipal Environment & Natural Resources',"Municipal Business & Dev't Office",
  'Human Resource Management Office','Municipal Disaster Risk Reduction & Mgmt',
  'Office of the Mun. Budget','Municipal Circuit Trial Court',
];

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

function PayrollModal({ row, idx, onClose }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PAYROLL DETAIL · JUNE 2025</span>
            <h3 className="modal-title">{row.name}</h3>
            <p className="modal-sub">{row.position} · {row.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <Icon name="x" size={16} />
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-emp-row">
            <div className="emp-avatar lg" style={{ background: avatarColors[idx % avatarColors.length] }}>{initials(row.name)}</div>
            <div>
              <p className="modal-emp-id">{row.id}</p>
              <span className={`badge-status ${row.status.toLowerCase().replace(' ', '-')}`}>{row.status}</span>
            </div>
          </div>
          <div className="modal-section-label">EARNINGS</div>
          <div className="modal-row"><span>Basic Pay</span><strong>{row.basic}</strong></div>
          <div className="modal-section-label" style={{ marginTop: 16 }}>DEDUCTIONS</div>
          <div className="modal-row"><span>GSIS Premium</span><span className="modal-deduct">{row.gsis}</span></div>
          <div className="modal-row"><span>PhilHealth</span><span className="modal-deduct">{row.philhealth}</span></div>
          <div className="modal-row"><span>Pag-IBIG</span><span className="modal-deduct">{row.pagibig}</span></div>
          <div className="modal-row"><span>Withholding Tax</span><span className="modal-deduct">{row.tax}</span></div>
          <div className="modal-row total"><span>Total Deductions</span><span className="modal-deduct">{row.deductions}</span></div>
          <div className="modal-net-row"><span>NET PAY</span><strong>{row.net}</strong></div>
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

export default function AdminDashboard() {
  const [selectedRow, setSelectedRow] = useState(null);
  const [selectedIdx, setSelectedIdx] = useState(null);
  const [deptFilter, setDeptFilter]   = useState('All Departments');
  const [activeTab, setActiveTab] = useState('overview');

  const filtered = deptFilter === 'All Departments'
    ? payrollData
    : payrollData.filter(r => r.dept === deptFilter);

  return (
    <div>
      {selectedRow && (
        <PayrollModal
          row={selectedRow}
          idx={selectedIdx}
          onClose={() => { setSelectedRow(null); setSelectedIdx(null); }}
        />
      )}

      {/* Welcome Banner */}
      <div className="welcome-banner">
        <div className="banner-left">
          <div className="banner-icon">
            <Icon name="building" size={22} color="#d9bb00" />
          </div>
          <div>
            <h2>Welcome to PRIME HRIS Dashboard</h2>
            <p>Municipal Government of Pagsanjan · Human Resource Management Office</p>
          </div>
        </div>
        <div className="banner-right">
          <div className="banner-badge"><span className="banner-badge-dot" />System Active</div>
          <div className="banner-badge outline">Fiscal Year 2025</div>
        </div>
      </div>

      {/* Stats - 2 rows */}
      <section className="stats-grid" style={{ gridTemplateColumns: 'repeat(3, 1fr)' }}>
        {stats.map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '15' }}><Icon name={s.icon} size={18} color={s.accent} /></div>
            </div>
            <h2 className="stat-value">{s.value}</h2>
            <div className="stat-footer">
              <span className="stat-dot" style={{ background: s.accent }} />
              <p className="stat-sub">{s.sub}</p>
            </div>
          </div>
        ))}
      </section>

      {/* Quick Actions */}
      <section style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '14px', marginBottom: '22px' }}>
        <div className="stat-card" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
            <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'linear-gradient(135deg, #15803d 0%, #22c55e 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 12px rgba(21, 128, 61, 0.2)' }}>
              <Icon name="user" size={22} color="#fff" />
            </div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: '14px', fontWeight: '700', color: '#0b044d', marginBottom: '3px' }}>Recruitment</p>
              <p style={{ fontSize: '12px', color: '#9999bb' }}>8 open positions · 45 applicants</p>
            </div>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" strokeWidth="2"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
        </div>
        
        <div className="stat-card" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
            <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 12px rgba(217, 187, 0, 0.2)' }}>
              <Icon name="bookOpen" size={22} color="#fff" />
            </div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: '14px', fontWeight: '700', color: '#0b044d', marginBottom: '3px' }}>Training Programs</p>
              <p style={{ fontSize: '12px', color: '#9999bb' }}>5 ongoing · 83 participants</p>
            </div>
            <Icon name="chevronRight" size={18} color="#9999bb" />
          </div>
        </div>
        
        <div className="stat-card" style={{ cursor: 'pointer', transition: 'all 0.2s' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
            <div style={{ width: '48px', height: '48px', borderRadius: '12px', background: 'linear-gradient(135deg, #6b3fa0 0%, #8b5cf6 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 4px 12px rgba(107, 63, 160, 0.2)' }}>
              <Icon name="activity" size={22} color="#fff" />
            </div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: '14px', fontWeight: '700', color: '#0b044d', marginBottom: '3px' }}>Performance Reviews</p>
              <p style={{ fontSize: '12px', color: '#9999bb' }}>12 pending evaluations</p>
            </div>
            <Icon name="chevronRight" size={18} color="#9999bb" />
          </div>
        </div>
      </section>

      {/* Tabs for Dashboard Views */}
      <div style={{ display: 'flex', gap: '8px', marginBottom: '20px', borderBottom: '2px solid #f0effe', paddingBottom: '0' }}>
        {[
          { id: 'overview', label: 'Overview', icon: 'grid' },
          { id: 'payroll', label: 'Recent Payroll', icon: 'peso' },
          { id: 'activity', label: 'Recent Activity', icon: 'activity' },
        ].map(tab => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            style={{
              display: 'flex', alignItems: 'center', gap: '8px',
              padding: '10px 20px', background: 'none', border: 'none',
              borderBottom: activeTab === tab.id ? '3px solid #0b044d' : '3px solid transparent',
              color: activeTab === tab.id ? '#0b044d' : '#9999bb',
              fontWeight: activeTab === tab.id ? '700' : '500',
              fontSize: '13px', fontFamily: 'Poppins, sans-serif',
              cursor: 'pointer', transition: 'all 0.2s',
              marginBottom: '-2px'
            }}
          >
            <Icon name={tab.icon} size={16} color={activeTab === tab.id ? '#0b044d' : '#9999bb'} />
            {tab.label}
          </button>
        ))}
      </div>

      {/* Overview Tab */}
      {activeTab === 'overview' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Recent Activity</h3>
              <p className="table-sub">Latest updates and actions across all modules</p>
            </div>
          </div>
          <div style={{ padding: '20px 24px' }}>
            {[
              { type: 'success', title: 'Payroll Processed', desc: 'Jun 16-30, 2025 payroll completed for 348 employees', time: '2 hours ago', icon: 'checkCircle' },
              { type: 'info', title: 'New Job Posting', desc: 'Administrative Officer IV position opened in Office of the Mayor', time: '5 hours ago', icon: 'clipboard' },
              { type: 'warning', title: 'Pending Evaluations', desc: '12 performance evaluations due by Jun 30, 2025', time: '1 day ago', icon: 'clock' },
              { type: 'info', title: 'Training Completed', desc: '30 employees completed Customer Service Excellence training', time: '2 days ago', icon: 'bookOpen' },
              { type: 'success', title: 'New Employee Onboarded', desc: 'Roberto T. Flores (PGS-0310) successfully onboarded', time: '3 days ago', icon: 'user' },
            ].map((activity, i) => (
              <div key={i} style={{ display: 'flex', gap: '14px', padding: '14px 0', borderBottom: i < 4 ? '1px solid #f7f6ff' : 'none' }}>
                <div style={{ 
                  width: '40px', height: '40px', borderRadius: '10px', 
                  background: activity.type === 'success' ? '#e8f9ef' : activity.type === 'warning' ? '#fefce8' : '#f0effe',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontSize: '18px', flexShrink: 0, color: activity.type === 'success' ? '#15803d' : activity.type === 'warning' ? '#d9bb00' : '#0b044d'
                }}>
                  <Icon name={activity.icon} size={18} color="currentColor" />
                </div>
                <div style={{ flex: 1 }}>
                  <p style={{ fontSize: '13.5px', fontWeight: '600', color: '#0b044d', marginBottom: '3px' }}>{activity.title}</p>
                  <p style={{ fontSize: '12px', color: '#6b6a8a', lineHeight: '1.5' }}>{activity.desc}</p>
                  <p style={{ fontSize: '11px', color: '#aaa8cc', marginTop: '4px' }}>{activity.time}</p>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* Payroll Tab */}
      {activeTab === 'payroll' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Payroll Register — Jun 16–30, 2025</h3>
              <p className="table-sub">Municipal Government of Pagsanjan · Pay Date: Jun 30, 2025 · Showing {filtered.length} of {payrollData.length} personnel</p>
            </div>
            <div className="table-actions">
              <select className="filter-select" value={deptFilter} onChange={e => setDeptFilter(e.target.value)}>
                {departments.map(d => <option key={d}>{d}</option>)}
              </select>
              <select className="filter-select">
                <option>June 2025</option>
                <option>May 2025</option>
                <option>April 2025</option>
              </select>
              <button className="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
              </button>
              <button className="modal-btn-primary" style={{ padding: '7px 16px', fontSize: 12.5 }}>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Process Payroll
              </button>
            </div>
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Personnel</th>
                  <th>Position</th>
                  <th>Department / Office</th>
                  <th>Basic Pay</th>
                  <th>Deductions</th>
                  <th>Net Pay</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((row, i) => (
                  <tr key={row.id}>
                    <td>
                      <div className="emp-cell">
                        <div className="emp-avatar" style={{ background: avatarColors[payrollData.indexOf(row) % avatarColors.length] }}>
                          {initials(row.name)}
                        </div>
                        <div>
                          <p className="emp-name">{row.name}</p>
                          <p className="emp-id">{row.id}</p>
                        </div>
                      </div>
                    </td>
                    <td className="position-cell">{row.position}</td>
                    <td><span className="dept-tag">{row.dept}</span></td>
                    <td className="pay-cell">{row.basic}</td>
                    <td className="deduction">{row.deductions}</td>
                    <td className="net-pay">{row.net}</td>
                    <td><span className={`badge-status ${row.status.toLowerCase().replace(' ', '-')}`}>{row.status}</span></td>
                    <td>
                      <button className="btn-view" onClick={() => { setSelectedRow(row); setSelectedIdx(payrollData.indexOf(row)); }}>
                        View
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="table-footer">
            <p>Showing <strong>{filtered.length}</strong> of <strong>{payrollData.length}</strong> records</p>
            <div className="pagination">
              <button className="page-btn active">1</button>
              <button className="page-btn">2</button>
              <button className="page-btn">3</button>
              <button className="page-btn">›</button>
            </div>
          </div>
        </section>
      )}

      {/* Activity Tab */}
      {activeTab === 'activity' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">System Activity Log</h3>
              <p className="table-sub">Comprehensive activity tracking across all HRIS modules</p>
            </div>
          </div>
          <div style={{ padding: '20px 24px' }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '14px', marginBottom: '20px' }}>
              {[
                { module: 'Recruitment', count: 8, color: '#15803d', icon: 'users' },
                { module: 'Training', count: 5, color: '#d9bb00', icon: 'bookOpen' },
                { module: 'Performance', count: 12, color: '#6b3fa0', icon: 'star' },
                { module: 'Payroll', count: 348, color: '#8e1e18', icon: 'peso' },
              ].map((mod, i) => (
                <div key={i} style={{ background: '#f7f6ff', borderRadius: '10px', padding: '16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                  <div style={{ 
                    width: '48px', height: '48px', borderRadius: '10px',
                    background: mod.color + '15',
                    border: `2px solid ${mod.color}`,
                    display: 'flex', alignItems: 'center', justifyContent: 'center'
                  }}>
                    <Icon name={mod.icon} size={24} color={mod.color} />
                  </div>
                  <div>
                    <p style={{ fontSize: '11px', color: '#9999bb', fontWeight: '600', marginBottom: '2px' }}>{mod.module}</p>
                    <p style={{ fontSize: '20px', fontWeight: '800', color: mod.color }}>{mod.count}</p>
                  </div>
                </div>
              ))}
            </div>
            <p style={{ fontSize: '12px', color: '#9999bb', textAlign: 'center', padding: '20px' }}>Detailed activity logs and audit trails available in Reports module</p>
          </div>
        </section>
      )}
    </div>
  );
}
