import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';

const emp = {
  id: 'JO-0042',
  name: 'Juan D. Cruz',
  initials: 'JD',
  avatarColor: '#1a6e3c',
  position: 'Utility Worker I',
  dept: 'General Services Office',
  empType: 'Job Order',
  schedule: '8:00 AM – 5:00 PM',
};

const attendanceData = {
  'June 2025': [
    { date: 'Jun 2',  day: 'Mon', timeIn: '7:55 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 3',  day: 'Tue', timeIn: '8:02 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 4',  day: 'Wed', timeIn: '8:20 AM',  timeOut: '5:00 PM',  status: 'Late',    ot: 0,   lateMinutes: 20 },
    { date: 'Jun 5',  day: 'Thu', timeIn: '8:00 AM',  timeOut: '6:30 PM',  status: 'Present', ot: 1.5 },
    { date: 'Jun 6',  day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 9',  day: 'Mon', timeIn: '7:58 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 10', day: 'Tue', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 11', day: 'Wed', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 12', day: 'Thu', timeIn: '—',        timeOut: '—',        status: 'Holiday', ot: 0   },
    { date: 'Jun 13', day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 16', day: 'Mon', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 17', day: 'Tue', timeIn: '8:10 AM',  timeOut: '5:00 PM',  status: 'Late',    ot: 0,   lateMinutes: 10 },
    { date: 'Jun 18', day: 'Wed', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 19', day: 'Thu', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 20', day: 'Fri', timeIn: '8:00 AM',  timeOut: '—',        status: 'Absent',  ot: 0   },
    { date: 'Jun 23', day: 'Mon', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 24', day: 'Tue', timeIn: '7:50 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 25', day: 'Wed', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 26', day: 'Thu', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'Jun 27', day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
  ],
  'May 2025': [
    { date: 'May 2',  day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 5',  day: 'Mon', timeIn: '8:05 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 6',  day: 'Tue', timeIn: '8:15 AM',  timeOut: '5:00 PM',  status: 'Late',    ot: 0,   lateMinutes: 15 },
    { date: 'May 7',  day: 'Wed', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 8',  day: 'Thu', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 9',  day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 12', day: 'Mon', timeIn: '—',        timeOut: '—',        status: 'Holiday', ot: 0   },
    { date: 'May 13', day: 'Tue', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 14', day: 'Wed', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 15', day: 'Thu', timeIn: '8:00 AM',  timeOut: '6:00 PM',  status: 'Present', ot: 1   },
    { date: 'May 16', day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 19', day: 'Mon', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 20', day: 'Tue', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 21', day: 'Wed', timeIn: '—',        timeOut: '—',        status: 'Absent',  ot: 0   },
    { date: 'May 22', day: 'Thu', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 23', day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 26', day: 'Mon', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 27', day: 'Tue', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 28', day: 'Wed', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 29', day: 'Thu', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
    { date: 'May 30', day: 'Fri', timeIn: '8:00 AM',  timeOut: '5:00 PM',  status: 'Present', ot: 0   },
  ],
};

const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const years  = ['2025','2024'];

const statusStyle = {
  Present: { bg: '#e8f9ef', color: '#15803d' },
  Late:    { bg: '#fefce8', color: '#a16207' },
  Absent:  { bg: '#fdf0ef', color: '#8e1e18' },
  Holiday: { bg: '#f7f6ff', color: '#6b6a8a' },
};

function DTRModal({ period, summary, onClose }) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [onClose]);

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" style={{ maxWidth: 500 }} onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">DAILY TIME RECORD · {period.toUpperCase()}</span>
            <h3 className="modal-title">{emp.name}</h3>
            <p className="modal-sub">{emp.position} · {emp.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div className="modal-body">
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, background: '#f7f6ff', borderRadius: 10, padding: '12px 16px', marginBottom: 18 }}>
            <div className="emp-avatar lg" style={{ background: emp.avatarColor }}>{emp.initials}</div>
            <div>
              <p style={{ fontSize: 13, fontWeight: 700, color: '#0b044d', marginBottom: 2 }}>{emp.id}</p>
              <p style={{ fontSize: 12, color: '#9999bb' }}>Schedule: {emp.schedule}</p>
            </div>
            <span className="badge-status processed" style={{ marginLeft: 'auto' }}>Complete</span>
          </div>

          <div className="modal-section-label">ATTENDANCE SUMMARY</div>
          <div className="modal-row"><span>Working Days</span><strong>{summary.workingDays} days</strong></div>
          <div className="modal-row"><span>Days Present</span><strong style={{ color: '#15803d' }}>{summary.present} days</strong></div>
          <div className="modal-row"><span>Days Absent</span><strong style={{ color: '#8e1e18' }}>{summary.absent} days</strong></div>
          <div className="modal-row"><span>Late Arrivals</span><strong style={{ color: '#a16207' }}>{summary.late} times</strong></div>
          <div className="modal-row"><span>Holidays</span><strong style={{ color: '#6b6a8a' }}>{summary.holiday} days</strong></div>

          <div className="modal-section-label" style={{ marginTop: 16 }}>OVERTIME</div>
          <div className="modal-row"><span>Total OT Hours</span><strong style={{ color: '#0b044d' }}>{summary.ot} hrs</strong></div>

          <div className="modal-net-row" style={{ marginTop: 16 }}>
            <span>ATTENDANCE RATE</span>
            <strong>{summary.rate}%</strong>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download DTR
          </button>
        </div>
      </div>
    </div>
  );
}

export default function JobOrderAttendance({ searchQuery = '' }) {
  const [month, setMonth]           = useState('June');
  const [year, setYear]             = useState('2025');
  const [showDTR, setShowDTR]       = useState(false);
  const [filterStatus, setFilterStatus] = useState('All');
  const [page, setPage]             = useState(1);
  const PAGE_SIZE = 10;

  const period = `${month} ${year}`;
  const logs   = attendanceData[period] || [];

  const q = searchQuery.toLowerCase();
  const filtered = logs
    .filter(l => filterStatus === 'All' || l.status === filterStatus)
    .filter(l => !q || l.date.toLowerCase().includes(q) || l.day.toLowerCase().includes(q) || l.status.toLowerCase().includes(q) || l.timeIn.toLowerCase().includes(q) || l.timeOut.toLowerCase().includes(q));

  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const paginated  = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

  useEffect(() => { setPage(1); }, [searchQuery]);

  const present     = logs.filter(l => l.status === 'Present').length;
  const late        = logs.filter(l => l.status === 'Late').length;
  const absent      = logs.filter(l => l.status === 'Absent').length;
  const holiday     = logs.filter(l => l.status === 'Holiday').length;
  const totalOT     = logs.reduce((s, l) => s + l.ot, 0);
  const workingDays = present + late + absent;
  const rate        = workingDays > 0 ? Math.round(((present + late) / workingDays) * 100) : 0;
  const summary     = { present, late, absent, holiday, ot: totalOT, workingDays, rate };

  return (
    <div>
      {showDTR && <DTRModal period={period} summary={summary} onClose={() => setShowDTR(false)} />}

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
          <div className="banner-badge"><span className="banner-badge-dot" />Schedule: {emp.schedule}</div>
          <div className="banner-badge outline">{period}</div>
        </div>
      </div>

      {/* Stats */}
      <section className="stats-grid" style={{ marginBottom: 24 }}>
        {[
          { label: 'Days Present',    value: present + late, sub: `${late} late arrival${late !== 1 ? 's' : ''}`, accent: '#15803d', icon: 'checkCircle' },
          { label: 'Days Absent',     value: absent,         sub: absent === 0 ? 'Perfect record' : 'This month', accent: '#8e1e18', icon: 'clock' },
          { label: 'Overtime Hours',  value: `${totalOT}h`,  sub: `${holiday} holiday${holiday !== 1 ? 's' : ''}`, accent: '#0b044d', icon: 'activity' },
          { label: 'Attendance Rate', value: `${rate}%`,     sub: `${workingDays} working days`,                   accent: rate >= 90 ? '#15803d' : '#d9bb00', icon: 'barChart' },
        ].map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '18' }}>
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

      {/* Attendance Rate Bar */}
      <div style={{ background: '#fff', border: '1.5px solid #eceaf8', borderRadius: 12, padding: '16px 22px', marginBottom: 24, display: 'flex', alignItems: 'center', gap: 20 }}>
        <div style={{ flex: 1 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
            <span style={{ fontSize: 12.5, fontWeight: 600, color: '#0b044d' }}>Monthly Attendance Rate</span>
            <span style={{ fontSize: 13, fontWeight: 700, color: rate >= 90 ? '#15803d' : '#a16207' }}>{rate}%</span>
          </div>
          <div style={{ height: 10, background: '#f0fdf4', borderRadius: 10, overflow: 'hidden' }}>
            <div style={{ width: `${rate}%`, height: '100%', background: rate >= 90 ? '#1a6e3c' : '#d9bb00', borderRadius: 10, transition: 'width 0.4s ease' }} />
          </div>
        </div>
        <div style={{ display: 'flex', gap: 16, flexShrink: 0 }}>
          {[
            { label: 'Present', count: present, color: '#15803d', bg: '#e8f9ef' },
            { label: 'Late',    count: late,    color: '#a16207', bg: '#fefce8' },
            { label: 'Absent',  count: absent,  color: '#8e1e18', bg: '#fdf0ef' },
          ].map(({ label, count, color, bg }) => (
            <div key={label} style={{ textAlign: 'center', background: bg, borderRadius: 9, padding: '8px 14px' }}>
              <p style={{ fontSize: 18, fontWeight: 800, color, marginBottom: 2 }}>{count}</p>
              <p style={{ fontSize: 10.5, fontWeight: 600, color, opacity: 0.8 }}>{label}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Daily Log Table */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Daily Time Record — {period}</h3>
            <p className="table-sub">{emp.name} · {emp.id} · {filtered.length} entries</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={month} onChange={e => { setMonth(e.target.value); setPage(1); }}>
              {months.map(m => <option key={m}>{m}</option>)}
            </select>
            <select className="filter-select" value={year} onChange={e => { setYear(e.target.value); setPage(1); }}>
              {years.map(y => <option key={y}>{y}</option>)}
            </select>
            <select className="filter-select" value={filterStatus} onChange={e => { setFilterStatus(e.target.value); setPage(1); }}>
              <option value="All">All Status</option>
              <option>Present</option>
              <option>Late</option>
              <option>Absent</option>
              <option>Holiday</option>
            </select>
            <button className="btn-view" onClick={() => setShowDTR(true)}>View DTR Summary</button>
            <button className="btn-export">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              Export
            </button>
          </div>
        </div>

        {/* Summary strip — no Leave for JO */}
        <div className="payroll-summary-bar" style={{ marginTop: 0, marginBottom: 16 }}>
          {[
            { label: 'Present',  value: present,        color: '#15803d' },
            { label: 'Late',     value: late,           color: '#a16207' },
            { label: 'Absent',   value: absent,         color: '#8e1e18' },
            { label: 'OT Hours', value: `${totalOT}h`,  color: '#0b044d' },
          ].map(({ label, value, color }, i, arr) => (
            <>
              <div className="psummary-item" key={label}>
                <span>{label}</span>
                <strong style={{ color }}>{value}</strong>
              </div>
              {i < arr.length - 1 && <div className="psummary-divider" key={label + '-div'} />}
            </>
          ))}
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Date</th><th>Day</th><th>Time In</th><th>Time Out</th><th>Late (min)</th><th>OT Hours</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              {paginated.length === 0 && (
                <tr><td colSpan={6} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No records found for {period}.</td></tr>
              )}
              {paginated.map((log, i) => {
                const s = statusStyle[log.status] || statusStyle.Present;
                return (
                  <tr key={i}>
                    <td style={{ fontWeight: 600, color: '#0b044d', fontSize: 13 }}>{log.date}</td>
                    <td style={{ fontSize: 12.5, color: '#9999bb' }}>{log.day}</td>
                    <td style={{ fontSize: 13, color: log.timeIn === '—' ? '#c0bedd' : '#0b044d', fontWeight: log.timeIn !== '—' ? 500 : 400 }}>
                      {log.timeIn}
                      {log.status === 'Late' && (
                        <span style={{ marginLeft: 6, fontSize: 10, background: '#fefce8', color: '#a16207', padding: '2px 7px', borderRadius: 20, fontWeight: 700 }}>LATE</span>
                      )}
                    </td>
                    <td style={{ fontSize: 13, color: log.timeOut === '—' ? '#c0bedd' : '#0b044d', fontWeight: log.timeOut !== '—' ? 500 : 400 }}>{log.timeOut}</td>
                    <td style={{ fontSize: 13, color: log.lateMinutes > 0 ? '#a16207' : '#c0bedd', fontWeight: log.lateMinutes > 0 ? 700 : 400 }}>
                      {log.lateMinutes > 0 ? `${log.lateMinutes} min` : '—'}
                    </td>
                    <td style={{ fontSize: 13, color: log.ot > 0 ? '#0b044d' : '#c0bedd', fontWeight: log.ot > 0 ? 600 : 400 }}>
                      {log.ot > 0 ? `+${log.ot}h` : '—'}
                    </td>
                    <td>
                      <span style={{ background: s.bg, color: s.color, padding: '4px 11px', borderRadius: 20, fontSize: 11, fontWeight: 600 }}>
                        {log.status}
                      </span>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{Math.min(page * PAGE_SIZE, filtered.length)}</strong> of <strong>{filtered.length}</strong> entries</p>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
            <button className="page-btn" onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1} style={{ opacity: page === 1 ? 0.4 : 1 }}>‹</button>
            {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
              <button key={p} className={`page-btn ${page === p ? 'active' : ''}`} onClick={() => setPage(p)}>{p}</button>
            ))}
            <button className="page-btn" onClick={() => setPage(p => Math.min(totalPages, p + 1))} disabled={page === totalPages} style={{ opacity: page === totalPages ? 0.4 : 1 }}>›</button>
          </div>
        </div>
      </section>
    </div>
  );
}
