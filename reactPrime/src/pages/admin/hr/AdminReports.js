import { useState } from 'react';
import { Icon } from '../../../components/Icons';

const peso = n => '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });

// Semi-monthly amounts (monthly ÷ 2)
const reportData = [
  { id: 'PGS-0041', name: 'Maria B. Santos',    dept: 'Office of the Mayor',              basic: 21079.50, gsis: 1897,    philhealth: 525,    pagibig: 50, tax: 1743.50, status: 'Processed' },
  { id: 'PGS-0082', name: 'Juan P. dela Cruz',  dept: 'Office of the Mun. Engineer',      basic: 19042.50, gsis: 1714,    philhealth: 475,    pagibig: 50, tax: 1569.50, status: 'Processed' },
  { id: 'PGS-0115', name: 'Ana R. Reyes',       dept: 'Municipal Health Office',          basic: 16921.50, gsis: 1523,    philhealth: 425,    pagibig: 50, tax: 1386,    status: 'Pending'   },
  { id: 'PGS-0203', name: 'Carlos M. Mendoza',  dept: 'Office of the Mun. Treasurer',     basic: 23627.50, gsis: 2126.50, philhealth: 575,    pagibig: 50, tax: 1974,    status: 'Processed' },
  { id: 'PGS-0267', name: 'Liza G. Gomez',      dept: 'MSWD – Pagsanjan',                 basic: 17548.50, gsis: 1579.50, philhealth: 437.50, pagibig: 50, tax: 1442.50, status: 'On Hold'   },
  { id: 'PGS-0310', name: 'Roberto T. Flores',  dept: 'Municipal Civil Registrar',        basic: 15265.50, gsis: 1374,    philhealth: 387.50, pagibig: 50, tax: 1241.50, status: 'Processed' },
  { id: 'PGS-0342', name: 'Grace A. Villanueva',dept: 'Office of the Mun. Budget',        basic: 14500,    gsis: 1305,    philhealth: 362.50, pagibig: 50, tax: 1100,    status: 'Pending'   },
  { id: 'PGS-0358', name: 'Ramon D. Cruz',      dept: 'Office of the Mun. Agriculturist', basic: 13500,    gsis: 1215,    philhealth: 337.50, pagibig: 50, tax: 990,     status: 'Processed' },
];

const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const years  = ['2025','2024','2023'];

const reportTypes = [
  { id: 'payroll',      label: 'Payroll Summary',       icon: 'creditCard' },
  { id: 'department',   label: 'Department Breakdown',  icon: 'building' },
  { id: 'deductions',   label: 'Deductions Report',     icon: 'trendingUp' },
  { id: 'headcount',    label: 'Headcount Report',      icon: 'users' },
  { id: 'recruitment',  label: 'Recruitment Report',    icon: 'clipboard' },
  { id: 'training',     label: 'Training Report',       icon: 'bookOpen' },
  { id: 'performance',  label: 'Performance Report',    icon: 'star' },
];

export default function Reports() {
  const [activeReport, setActiveReport] = useState('payroll');
  const [month, setMonth]       = useState('June');
  const [year, setYear]         = useState('2025');
  const [semi, setSemi]         = useState('2nd (16–30)');
  const [search, setSearch]     = useState('');

  const isFirst = semi === '1st (1–15)';
  const period  = `${month} ${isFirst ? '1–15' : '16–30'}, ${year}`;
  const payDate = `${month.slice(0,3)} ${isFirst ? '15' : '30'}, ${year}`;

  const filtered = reportData.filter(r =>
    r.name.toLowerCase().includes(search.toLowerCase()) ||
    r.id.toLowerCase().includes(search.toLowerCase()) ||
    r.dept.toLowerCase().includes(search.toLowerCase())
  );

  const totalBasic      = filtered.reduce((s, r) => s + r.basic, 0);
  const totalGsis       = filtered.reduce((s, r) => s + r.gsis, 0);
  const totalPhilhealth = filtered.reduce((s, r) => s + r.philhealth, 0);
  const totalPagibig    = filtered.reduce((s, r) => s + r.pagibig, 0);
  const totalTax        = filtered.reduce((s, r) => s + r.tax, 0);
  const totalDeductions = totalGsis + totalPhilhealth + totalPagibig + totalTax;
  const totalNet        = totalBasic - totalDeductions;

  // Department breakdown
  const deptMap = {};
  filtered.forEach(r => {
    if (!deptMap[r.dept]) deptMap[r.dept] = { count: 0, basic: 0, net: 0 };
    deptMap[r.dept].count++;
    deptMap[r.dept].basic += r.basic;
    deptMap[r.dept].net   += r.basic - r.gsis - r.philhealth - r.pagibig - r.tax;
  });
  const deptRows = Object.entries(deptMap).sort((a, b) => b[1].basic - a[1].basic);

  const summaryStats = [
    { label: 'Gross Payroll',    value: peso(totalBasic),      sub: `${filtered.length} employees · ${period}`, accent: '#0b044d', icon: 'peso' },
    { label: 'Total Net Pay',    value: peso(totalNet),        sub: 'After all deductions',         accent: '#15803d', icon: 'peso' },
    { label: 'Total Deductions', value: peso(totalDeductions), sub: 'GSIS, PhilHealth, Pag-IBIG, Tax', accent: '#8e1e18', icon: 'creditCard' },
    { label: 'Processed',        value: filtered.filter(r => r.status === 'Processed').length, sub: `${filtered.filter(r => r.status !== 'Processed').length} pending/on-hold`, accent: '#d9bb00', icon: 'checkCircle' },
  ];

  return (
    <div>
      {/* Report Type Tabs */}
      <div style={{ display: 'flex', gap: 10, marginBottom: 20, flexWrap: 'wrap' }}>
        {reportTypes.map(rt => (
          <button
            key={rt.id}
            onClick={() => setActiveReport(rt.id)}
            style={{
              display: 'flex', alignItems: 'center', gap: 8,
              padding: '9px 18px', borderRadius: 10, cursor: 'pointer',
              fontSize: 13, fontWeight: 600, fontFamily: 'Poppins, sans-serif',
              border: activeReport === rt.id ? '2px solid #0b044d' : '1.5px solid #e4e3f0',
              background: activeReport === rt.id ? '#0b044d' : '#ffffff',
              color: activeReport === rt.id ? '#ffffff' : '#5a5888',
              transition: 'all 0.18s',
            }}
          >
            <Icon name={rt.icon} size={16} color={activeReport === rt.id ? '#fff' : '#5a5888'} /> {rt.label}
          </button>
        ))}
      </div>

      {/* Filters */}
      <section className="table-section" style={{ marginBottom: 20 }}>
        <div className="table-header">
          <div>
            <h3 className="table-title">
              {reportTypes.find(r => r.id === activeReport)?.label} — {period}
            </h3>
            <p className="table-sub">Municipal Government of Pagsanjan · Fiscal Year {year}</p>
          </div>
          <div className="table-actions">
            <div className="search-box" style={{ padding: '7px 12px' }}>
              <Icon name="search" size={13} color="#9999bb" />
              <input
                type="text" placeholder="Search..."
                value={search} onChange={e => setSearch(e.target.value)}
                style={{ width: 160 }}
              />
            </div>
            <select className="filter-select" value={semi} onChange={e => setSemi(e.target.value)}>
              <option>1st (1–15)</option>
              <option>2nd (16–30)</option>
            </select>
            <select className="filter-select" value={month} onChange={e => setMonth(e.target.value)}>
              {months.map(m => <option key={m}>{m}</option>)}
            </select>
            <select className="filter-select" value={year} onChange={e => setYear(e.target.value)}>
              {years.map(y => <option key={y}>{y}</option>)}
            </select>
            <button className="btn-export" onClick={() => window.print()}>
              <Icon name="download" size={13} />
              Export / Print
            </button>
          </div>
        </div>
      </section>

      {/* Summary Stats */}
      <section className="stats-grid" style={{ marginBottom: 20 }}>
        {summaryStats.map((s, i) => (
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
      </section>

      {/* ── Payroll Summary Report ── */}
      {activeReport === 'payroll' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Payroll Summary Register</h3>
              <p className="table-sub">{filtered.length} records · {period} · Pay Date: {payDate}</p>
            </div>
          </div>
          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Basic Pay</th>
                  <th>Total Deductions</th>
                  <th>Net Pay</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map(r => {
                  const ded = r.gsis + r.philhealth + r.pagibig + r.tax;
                  return (
                    <tr key={r.id}>
                      <td className="emp-id">{r.id}</td>
                      <td className="emp-name">{r.name}</td>
                      <td><span className="dept-tag">{r.dept}</span></td>
                      <td className="pay-cell">{peso(r.basic)}</td>
                      <td className="deduction">{peso(ded)}</td>
                      <td className="net-pay">{peso(r.basic - ded)}</td>
                      <td><span className={`badge-status ${r.status.toLowerCase().replace(' ', '-')}`}>{r.status}</span></td>
                    </tr>
                  );
                })}
              </tbody>
              <tfoot>
                <tr style={{ fontWeight: 700, background: '#f7f6ff' }}>
                  <td colSpan={3} style={{ padding: '10px 14px', fontSize: 13 }}>TOTAL ({filtered.length} employees)</td>
                  <td className="pay-cell">{peso(totalBasic)}</td>
                  <td className="deduction">{peso(totalDeductions)}</td>
                  <td className="net-pay">{peso(totalNet)}</td>
                  <td />
                </tr>
              </tfoot>
            </table>
          </div>
        </section>
      )}

      {/* ── Department Breakdown ── */}
      {activeReport === 'department' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Department Payroll Breakdown</h3>
              <p className="table-sub">{deptRows.length} departments · {period}</p>
            </div>
          </div>
          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Department / Office</th>
                  <th>Headcount</th>
                  <th>Gross Payroll</th>
                  <th>Net Payroll</th>
                  <th>% of Total</th>
                </tr>
              </thead>
              <tbody>
                {deptRows.map(([dept, d]) => (
                  <tr key={dept}>
                    <td><span className="dept-tag">{dept}</span></td>
                    <td style={{ fontWeight: 600, color: '#0b044d' }}>{d.count}</td>
                    <td className="pay-cell">{peso(d.basic)}</td>
                    <td className="net-pay">{peso(d.net)}</td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <div style={{ flex: 1, height: 6, background: '#eceaf8', borderRadius: 4, overflow: 'hidden' }}>
                          <div style={{ width: `${Math.round((d.basic / totalBasic) * 100)}%`, height: '100%', background: '#0b044d', borderRadius: 4 }} />
                        </div>
                        <span style={{ fontSize: 12, color: '#6b6a8a', minWidth: 32 }}>
                          {Math.round((d.basic / totalBasic) * 100)}%
                        </span>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr style={{ fontWeight: 700, background: '#f7f6ff' }}>
                  <td style={{ padding: '10px 14px', fontSize: 13 }}>TOTAL</td>
                  <td style={{ padding: '10px 14px', fontWeight: 700 }}>{filtered.length}</td>
                  <td className="pay-cell">{peso(totalBasic)}</td>
                  <td className="net-pay">{peso(totalNet)}</td>
                  <td style={{ padding: '10px 14px', fontSize: 13, color: '#6b6a8a' }}>100%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </section>
      )}

      {/* ── Deductions Report ── */}
      {activeReport === 'deductions' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Deductions Breakdown Report</h3>
              <p className="table-sub">{filtered.length} employees · {period}</p>
            </div>
          </div>
          {/* Deduction breakdown summary cards */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: 14, padding: '0 0 20px' }}>
            {[
              { label: 'GSIS Premium',     value: totalGsis,       pct: Math.round((totalGsis / totalDeductions) * 100),       color: '#0b044d' },
              { label: 'PhilHealth',        value: totalPhilhealth, pct: Math.round((totalPhilhealth / totalDeductions) * 100), color: '#8e1e18' },
              { label: 'Pag-IBIG',          value: totalPagibig,    pct: Math.round((totalPagibig / totalDeductions) * 100),    color: '#d9bb00' },
              { label: 'Withholding Tax',   value: totalTax,        pct: Math.round((totalTax / totalDeductions) * 100),        color: '#15803d' },
            ].map((d, i) => (
              <div key={i} style={{ background: '#fff', border: '1.5px solid #eceaf8', borderRadius: 12, padding: '16px 18px' }}>
                <p style={{ fontSize: 11.5, color: '#9999bb', fontWeight: 600, marginBottom: 6 }}>{d.label}</p>
                <p style={{ fontSize: 17, fontWeight: 800, color: d.color, marginBottom: 8 }}>{peso(d.value)}</p>
                <div style={{ height: 5, background: '#eceaf8', borderRadius: 4, overflow: 'hidden' }}>
                  <div style={{ width: `${d.pct}%`, height: '100%', background: d.color, borderRadius: 4 }} />
                </div>
                <p style={{ fontSize: 11, color: '#aaa8cc', marginTop: 5 }}>{d.pct}% of total deductions</p>
              </div>
            ))}
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>GSIS</th>
                  <th>PhilHealth</th>
                  <th>Pag-IBIG</th>
                  <th>Withholding Tax</th>
                  <th>Total Deductions</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map(r => (
                  <tr key={r.id}>
                    <td>
                      <p className="emp-name">{r.name}</p>
                      <p className="emp-id">{r.id}</p>
                    </td>
                    <td className="deduction">{peso(r.gsis)}</td>
                    <td className="deduction">{peso(r.philhealth)}</td>
                    <td className="deduction">{peso(r.pagibig)}</td>
                    <td className="deduction">{peso(r.tax)}</td>
                    <td className="deduction" style={{ fontWeight: 700 }}>{peso(r.gsis + r.philhealth + r.pagibig + r.tax)}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr style={{ fontWeight: 700, background: '#f7f6ff' }}>
                  <td style={{ padding: '10px 14px', fontSize: 13 }}>TOTAL</td>
                  <td className="deduction">{peso(totalGsis)}</td>
                  <td className="deduction">{peso(totalPhilhealth)}</td>
                  <td className="deduction">{peso(totalPagibig)}</td>
                  <td className="deduction">{peso(totalTax)}</td>
                  <td className="deduction" style={{ fontWeight: 700 }}>{peso(totalDeductions)}</td>
                </tr>
              </tfoot>
            </table>
          </div>

        </section>
      )}

      {/* ── Headcount Report ── */}
      {activeReport === 'headcount' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Headcount Report</h3>
              <p className="table-sub">{filtered.length} total personnel · {period}</p>
            </div>
          </div>

          {/* Status breakdown */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 14, padding: '0 0 20px' }}>
            {[
              { label: 'Total Personnel', value: filtered.length,                                          color: '#0b044d' },
              { label: 'Processed',       value: filtered.filter(r => r.status === 'Processed').length,   color: '#15803d' },
              { label: 'Pending',         value: filtered.filter(r => r.status === 'Pending').length,     color: '#d9bb00' },
              { label: 'On Hold',         value: filtered.filter(r => r.status === 'On Hold').length,     color: '#8e1e18' },
            ].map((s, i) => (
              <div key={i} style={{ background: '#fff', border: '1.5px solid #eceaf8', borderRadius: 12, padding: '16px 18px' }}>
                <p style={{ fontSize: 11.5, color: '#9999bb', fontWeight: 600, marginBottom: 6 }}>{s.label}</p>
                <p style={{ fontSize: 28, fontWeight: 800, color: s.color }}>{s.value}</p>
              </div>
            ))}
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Basic Pay</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map(r => (
                  <tr key={r.id}>
                    <td className="emp-id">{r.id}</td>
                    <td className="emp-name">{r.name}</td>
                    <td><span className="dept-tag">{r.dept}</span></td>
                    <td className="pay-cell">{peso(r.basic)}</td>
                    <td><span className={`badge-status ${r.status.toLowerCase().replace(' ', '-')}`}>{r.status}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="table-footer">
            <p>Showing <strong>{filtered.length}</strong> of <strong>{reportData.length}</strong> records</p>
          </div>
        </section>
      )}

      {/* ── Recruitment Report ── */}
      {activeReport === 'recruitment' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Recruitment Report</h3>
              <p className="table-sub">Job postings and applicant statistics · {year}</p>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: 14, padding: '0 0 20px' }}>
            {[
              { label: 'Total Job Postings', value: '12', color: '#0b044d' },
              { label: 'Open Positions', value: '8', color: '#15803d' },
              { label: 'Total Applicants', value: '145', color: '#d9bb00' },
              { label: 'Hired', value: '6', color: '#1a6e3c' },
            ].map((s, i) => (
              <div key={i} style={{ background: '#fff', border: '1.5px solid #eceaf8', borderRadius: 12, padding: '16px 18px' }}>
                <p style={{ fontSize: 11.5, color: '#9999bb', fontWeight: 600, marginBottom: 6 }}>{s.label}</p>
                <p style={{ fontSize: 28, fontWeight: 800, color: s.color }}>{s.value}</p>
              </div>
            ))}
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Job ID</th>
                  <th>Position</th>
                  <th>Department</th>
                  <th>Applicants</th>
                  <th>Status</th>
                  <th>Posted Date</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td className="emp-id">JOB-001</td>
                  <td className="emp-name">Administrative Officer IV</td>
                  <td><span className="dept-tag">Office of the Mayor</span></td>
                  <td style={{ fontWeight: 600, color: '#0b044d', textAlign: 'center' }}>12</td>
                  <td><span className="badge-status processed">Open</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jun 1, 2025</td>
                </tr>
                <tr>
                  <td className="emp-id">JOB-002</td>
                  <td className="emp-name">Municipal Engineer II</td>
                  <td><span className="dept-tag">Office of the Mun. Engineer</span></td>
                  <td style={{ fontWeight: 600, color: '#0b044d', textAlign: 'center' }}>8</td>
                  <td><span className="badge-status processed">Open</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jun 5, 2025</td>
                </tr>
                <tr>
                  <td className="emp-id">JOB-003</td>
                  <td className="emp-name">Nurse II</td>
                  <td><span className="dept-tag">Municipal Health Office</span></td>
                  <td style={{ fontWeight: 600, color: '#0b044d', textAlign: 'center' }}>24</td>
                  <td><span className="badge-status on-hold">Closed</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>May 15, 2025</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      )}

      {/* ── Training Report ── */}
      {activeReport === 'training' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Training & Development Report</h3>
              <p className="table-sub">Training programs and participation · {year}</p>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: 14, padding: '0 0 20px' }}>
            {[
              { label: 'Total Programs', value: '15', color: '#0b044d' },
              { label: 'Ongoing', value: '5', color: '#15803d' },
              { label: 'Total Participants', value: '283', color: '#d9bb00' },
              { label: 'Completed', value: '8', color: '#1a6e3c' },
            ].map((s, i) => (
              <div key={i} style={{ background: '#fff', border: '1.5px solid #eceaf8', borderRadius: 12, padding: '16px 18px' }}>
                <p style={{ fontSize: 11.5, color: '#9999bb', fontWeight: 600, marginBottom: 6 }}>{s.label}</p>
                <p style={{ fontSize: 28, fontWeight: 800, color: s.color }}>{s.value}</p>
              </div>
            ))}
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Training ID</th>
                  <th>Program Title</th>
                  <th>Type</th>
                  <th>Participants</th>
                  <th>Status</th>
                  <th>Duration</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td className="emp-id">TRN-001</td>
                  <td className="emp-name">Leadership Development Program</td>
                  <td><span className="badge-emptype">Leadership</span></td>
                  <td style={{ fontWeight: 600, color: '#0b044d', textAlign: 'center' }}>25 / 30</td>
                  <td><span className="badge-status processed">Ongoing</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jun 15 - Jul 15</td>
                </tr>
                <tr>
                  <td className="emp-id">TRN-002</td>
                  <td className="emp-name">Digital Literacy Training</td>
                  <td><span className="badge-emptype">Technical</span></td>
                  <td style={{ fontWeight: 600, color: '#0b044d', textAlign: 'center' }}>18 / 20</td>
                  <td><span className="badge-status processed">Ongoing</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jun 20 - Jun 30</td>
                </tr>
                <tr>
                  <td className="emp-id">TRN-003</td>
                  <td className="emp-name">Customer Service Excellence</td>
                  <td><span className="badge-emptype">Soft Skills</span></td>
                  <td style={{ fontWeight: 600, color: '#0b044d', textAlign: 'center' }}>30 / 30</td>
                  <td><span className="badge-status on-hold">Completed</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>May 10 - May 20</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      )}

      {/* ── Performance Report ── */}
      {activeReport === 'performance' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Performance Evaluation Report</h3>
              <p className="table-sub">Employee performance ratings · Jan-Jun {year}</p>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: 14, padding: '0 0 20px' }}>
            {[
              { label: 'Total Evaluations', value: '348', color: '#0b044d' },
              { label: 'Completed', value: '336', color: '#15803d' },
              { label: 'Pending', value: '12', color: '#d9bb00' },
              { label: 'Average Rating', value: '4.7', color: '#1a6e3c' },
            ].map((s, i) => (
              <div key={i} style={{ background: '#fff', border: '1.5px solid #eceaf8', borderRadius: 12, padding: '16px 18px' }}>
                <p style={{ fontSize: 11.5, color: '#9999bb', fontWeight: 600, marginBottom: 6 }}>{s.label}</p>
                <p style={{ fontSize: 28, fontWeight: 800, color: s.color }}>{s.value}</p>
              </div>
            ))}
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Department</th>
                  <th>Rating</th>
                  <th>Status</th>
                  <th>Period</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td className="emp-id">PGS-0041</td>
                  <td className="emp-name">Maria B. Santos</td>
                  <td><span className="dept-tag">Office of the Mayor</span></td>
                  <td style={{ fontWeight: 700, color: '#15803d', textAlign: 'center' }}>4.8 / 5.0</td>
                  <td><span className="badge-status processed">Completed</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jan-Jun 2025</td>
                </tr>
                <tr>
                  <td className="emp-id">PGS-0082</td>
                  <td className="emp-name">Juan P. dela Cruz</td>
                  <td><span className="dept-tag">Office of the Mun. Engineer</span></td>
                  <td style={{ fontWeight: 700, color: '#15803d', textAlign: 'center' }}>4.5 / 5.0</td>
                  <td><span className="badge-status processed">Completed</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jan-Jun 2025</td>
                </tr>
                <tr>
                  <td className="emp-id">PGS-0115</td>
                  <td className="emp-name">Ana R. Reyes</td>
                  <td><span className="dept-tag">Municipal Health Office</span></td>
                  <td style={{ fontWeight: 700, color: '#15803d', textAlign: 'center' }}>4.9 / 5.0</td>
                  <td><span className="badge-status processed">Completed</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jan-Jun 2025</td>
                </tr>
                <tr>
                  <td className="emp-id">PGS-0267</td>
                  <td className="emp-name">Liza G. Gomez</td>
                  <td><span className="dept-tag">MSWD – Pagsanjan</span></td>
                  <td style={{ fontSize: 12.5, color: '#9999bb', textAlign: 'center' }}>Not rated</td>
                  <td><span className="badge-status pending">Pending</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>Jan-Jun 2025</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      )}
    </div>
  );
}
