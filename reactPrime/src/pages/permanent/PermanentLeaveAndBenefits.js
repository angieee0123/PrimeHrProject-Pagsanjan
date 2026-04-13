import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';

const emp = {
  id: 'PGS-0115',
  name: 'Ana R. Reyes',
  position: 'Nurse II',
  dept: 'Municipal Health Office',
};

const leaveTypes = ['Vacation Leave', 'Sick Leave', 'Emergency Leave', 'Special Leave'];

const initialLeaves = [
  { id: 'LV-2025-002', type: 'Sick Leave',      from: 'Jun 15, 2025', to: 'Jun 16, 2025', days: 2, reason: 'Medical consultation', status: 'Approved' },
  { id: 'LV-2025-007', type: 'Vacation Leave',   from: 'Jun 10, 2025', to: 'Jun 11, 2025', days: 2, reason: 'Rest and recreation',  status: 'Approved' },
  { id: 'LV-2025-010', type: 'Emergency Leave',  from: 'May 22, 2025', to: 'May 22, 2025', days: 1, reason: 'Family emergency',     status: 'Approved' },
  { id: 'LV-2025-013', type: 'Sick Leave',       from: 'May 5, 2025',  to: 'May 6, 2025',  days: 2, reason: 'Flu and fever',        status: 'Approved' },
  { id: 'LV-2025-018', type: 'Vacation Leave',   from: 'Apr 14, 2025', to: 'Apr 16, 2025', days: 3, reason: 'Family vacation',      status: 'Approved' },
  { id: 'LV-2025-021', type: 'Vacation Leave',   from: 'Jul 7, 2025',  to: 'Jul 9, 2025',  days: 3, reason: 'Personal trip',        status: 'Pending'  },
];

const benefits = [
  { label: 'GSIS Premium',      value: '₱3,046', sub: 'Monthly contribution',   color: '#0b044d', icon: 'building' },
  { label: 'PhilHealth',        value: '₱850',   sub: 'Monthly contribution',   color: '#15803d', icon: 'heart' },
  { label: 'Pag-IBIG',          value: '₱100',   sub: 'Monthly contribution',   color: '#8e1e18', icon: 'building' },
  { label: 'Withholding Tax',   value: '₱2,772', sub: 'Monthly deduction',      color: '#d9bb00', icon: 'fileText' },
];

const leaveCredits = [
  { label: 'Vacation Leave',  earned: 15, used: 5,  balance: 10, color: '#0b044d' },
  { label: 'Sick Leave',      earned: 15, used: 4,  balance: 11, color: '#15803d' },
  { label: 'Emergency Leave', earned: 3,  used: 1,  balance: 2,  color: '#8e1e18' },
  { label: 'Special Leave',   earned: 3,  used: 0,  balance: 3,  color: '#d9bb00' },
];

const statusColor = { Approved: 'processed', Pending: 'pending', Rejected: 'on-hold' };

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

/* ── Leave Detail Modal ── */
function LeaveDetailModal({ row, onClose }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">LEAVE REQUEST · {row.id}</span>
            <h3 className="modal-title">{row.type}</h3>
            <p className="modal-sub">{row.from} — {row.to}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-emp-row">
            <div className="emp-avatar lg" style={{ background: '#8e1e18' }}>AR</div>
            <div>
              <p className="modal-emp-id">{emp.id}</p>
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
  const [form, setForm] = useState({ type: leaveTypes[0], from: '', to: '', days: 1, reason: '' });
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));
  const valid = form.from && form.to && form.reason.trim();
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">NEW LEAVE REQUEST</span>
            <h3 className="modal-title">File a Leave</h3>
            <p className="modal-sub">{emp.name} · {emp.id}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field">
              <label>Leave Type</label>
              <select value={form.type} onChange={e => set('type', e.target.value)}>
                {leaveTypes.map(t => <option key={t}>{t}</option>)}
              </select>
            </div>
            <div className="form-field">
              <label>No. of Days</label>
              <input type="number" min="1" value={form.days} onChange={e => set('days', Number(e.target.value))} />
            </div>
            <div className="form-field">
              <label>Date From</label>
              <input type="date" value={form.from} onChange={e => set('from', e.target.value)} />
            </div>
            <div className="form-field">
              <label>Date To</label>
              <input type="date" value={form.to} onChange={e => set('to', e.target.value)} />
            </div>
          </div>
          <div className="form-field" style={{ marginTop: 12 }}>
            <label>Reason</label>
            <input value={form.reason} onChange={e => set('reason', e.target.value)} placeholder="Brief reason for leave" />
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" disabled={!valid} style={{ opacity: valid ? 1 : 0.5 }} onClick={() => valid && onSave(form)}>
            Submit Request
          </button>
        </div>
      </div>
    </div>
  );
}

/* ── Main Page ── */
export default function MyLeaveAndBenefits({ searchQuery = '' }) {
  const [tab, setTab]         = useState('leave');
  const [leaves, setLeaves]   = useState(initialLeaves);
  const [typeFilter, setTypeFilter] = useState('All Types');
  const [statusFilter, setStatusFilter] = useState('All');
  const [viewRow, setViewRow] = useState(null);
  const [showFile, setShowFile] = useState(false);

  const q = searchQuery.toLowerCase();
  const filtered = leaves.filter(r => {
    const matchQ      = !q || r.id.toLowerCase().includes(q) || r.type.toLowerCase().includes(q) || r.reason.toLowerCase().includes(q) || r.from.toLowerCase().includes(q) || r.to.toLowerCase().includes(q) || r.status.toLowerCase().includes(q);
    const matchType   = typeFilter === 'All Types' || r.type === typeFilter;
    const matchStatus = statusFilter === 'All' || r.status === statusFilter;
    return matchQ && matchType && matchStatus;
  });

  const totalUsed    = leaves.reduce((s, r) => s + r.days, 0);
  const totalPending = leaves.filter(r => r.status === 'Pending').length;
  const vlBalance    = leaveCredits.find(l => l.label === 'Vacation Leave').balance;
  const slBalance    = leaveCredits.find(l => l.label === 'Sick Leave').balance;

  const handleSave = form => {
    const newId = `LV-2025-${String(leaves.length + 100).padStart(3, '0')}`;
    setLeaves(l => [{ ...form, id: newId, status: 'Pending' }, ...l]);
    setShowFile(false);
  };

  return (
    <div>
      {viewRow  && <LeaveDetailModal row={viewRow} onClose={() => setViewRow(null)} />}
      {showFile && <FileLeaveModal onClose={() => setShowFile(false)} onSave={handleSave} />}

      {/* Welcome Banner */}
      <div className="welcome-banner" style={{ marginBottom: 24 }}>
        <div className="banner-left">
          <div style={{ width: 46, height: 46, borderRadius: '50%', background: '#8e1e18', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: 16, flexShrink: 0 }}>AR</div>
          <div>
            <h2 style={{ fontSize: 16, fontWeight: 700, color: '#fff', margin: 0 }}>{emp.name}</h2>
            <p style={{ fontSize: 12, color: 'rgba(255,255,255,0.55)', margin: 0 }}>{emp.position} · {emp.dept} · {emp.id}</p>
          </div>
        </div>
        <div className="banner-right">
          <div className="banner-badge"><span className="banner-badge-dot" />VL: {vlBalance} days</div>
          <div className="banner-badge outline">SL: {slBalance} days</div>
        </div>
      </div>

      {/* Stats */}
      <div className="stats-grid" style={{ marginBottom: 24 }}>
        {[
          { label: 'Total Leave Filed',  value: leaves.length,  sub: 'All time',          accent: '#0b044d', icon: 'fileText' },
          { label: 'Total Days Used',    value: totalUsed,      sub: 'Across all types',  accent: '#8e1e18', icon: 'calendar' },
          { label: 'Pending Requests',   value: totalPending,   sub: 'Awaiting approval', accent: '#d9bb00', icon: 'clock' },
          { label: 'VL + SL Balance',    value: vlBalance + slBalance, sub: `${vlBalance} VL · ${slBalance} SL`, accent: '#15803d', icon: 'checkCircle' },
        ].map((s, i) => (
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
      </div>

      {/* Tabs */}
      <div style={{ display: 'flex', gap: 4, marginBottom: 24, borderBottom: '1.5px solid #eceaf8' }}>
        {[{ id: 'leave', label: 'My Leave Requests' }, { id: 'credits', label: 'Leave Credits' }, { id: 'benefits', label: 'My Benefits' }].map(t => (
          <button key={t.id} onClick={() => setTab(t.id)} style={{
            background: 'none', border: 'none', cursor: 'pointer',
            padding: '10px 20px', fontFamily: 'Poppins, sans-serif',
            fontSize: 13, fontWeight: 600,
            color: tab === t.id ? '#0b044d' : '#9999bb',
            borderBottom: tab === t.id ? '2.5px solid #0b044d' : '2.5px solid transparent',
            marginBottom: -1.5, transition: 'all 0.2s'
          }}>{t.label}</button>
        ))}
      </div>

      {/* ── Leave Requests Tab ── */}
      {tab === 'leave' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">My Leave Requests</h3>
              <p className="table-sub">{filtered.length} of {leaves.length} records</p>
            </div>
            <div className="table-actions">
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
            </div>
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Leave ID</th>
                  <th>Leave Type</th>
                  <th>Date From</th>
                  <th>Date To</th>
                  <th>Days</th>
                  <th>Reason</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {filtered.length === 0 && (
                  <tr><td colSpan={8} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No records found.</td></tr>
                )}
                {filtered.map(row => (
                  <tr key={row.id}>
                    <td style={{ fontSize: 12, color: '#9999bb', fontWeight: 500 }}>{row.id}</td>
                    <td style={{ fontSize: 13, color: '#0b044d', fontWeight: 600 }}>{row.type}</td>
                    <td style={{ fontSize: 13 }}>{row.from}</td>
                    <td style={{ fontSize: 13 }}>{row.to}</td>
                    <td style={{ fontWeight: 700, color: '#0b044d' }}>{row.days}</td>
                    <td style={{ fontSize: 12.5, color: '#5a5888', maxWidth: 180 }}>{row.reason}</td>
                    <td><span className={`badge-status ${statusColor[row.status]}`}>{row.status}</span></td>
                    <td><button className="btn-view" onClick={() => setViewRow(row)}>View</button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="table-footer">
            <p>Showing <strong>{filtered.length}</strong> of <strong>{leaves.length}</strong> records</p>
          </div>
        </section>
      )}

      {/* ── Leave Credits Tab ── */}
      {tab === 'credits' && (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
          {leaveCredits.map((lc, i) => (
            <div key={i} className="table-section" style={{ padding: 22 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 }}>
                <div>
                  <p style={{ fontSize: 12, color: '#9999bb', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.5px', marginBottom: 4 }}>{lc.label}</p>
                  <h2 style={{ fontSize: 28, fontWeight: 800, color: lc.color, margin: 0 }}>{lc.balance}</h2>
                  <p style={{ fontSize: 12, color: '#9999bb', marginTop: 2 }}>days remaining</p>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <p style={{ fontSize: 11.5, color: '#9999bb', marginBottom: 4 }}>Earned: <strong style={{ color: '#0b044d' }}>{lc.earned}</strong></p>
                  <p style={{ fontSize: 11.5, color: '#9999bb' }}>Used: <strong style={{ color: '#8e1e18' }}>{lc.used}</strong></p>
                </div>
              </div>
              {/* Progress bar */}
              <div style={{ height: 8, background: '#f0effe', borderRadius: 4, overflow: 'hidden' }}>
                <div style={{ width: `${(lc.balance / lc.earned) * 100}%`, height: '100%', background: lc.color, borderRadius: 4, transition: 'width 0.4s' }} />
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 6 }}>
                <span style={{ fontSize: 11, color: '#9999bb' }}>0</span>
                <span style={{ fontSize: 11, color: '#9999bb' }}>{lc.earned} days max</span>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* ── Benefits Tab ── */}
      {tab === 'benefits' && (
        <div>
          {/* Benefit cards */}
          <div className="stats-grid" style={{ marginBottom: 24 }}>
            {benefits.map((b, i) => (
              <div className="stat-card" key={i}>
                <div className="stat-top">
                  <p className="stat-label">{b.label}</p>
                  <div className="stat-icon-wrap" style={{ background: b.color + '15' }}>
                    <Icon name={b.icon} size={18} color={b.color} />
                  </div>
                </div>
                <h2 className="stat-value">{b.value}</h2>
                <div className="stat-footer">
                  <span className="stat-dot" style={{ background: b.color }} />
                  <p className="stat-sub">{b.sub}</p>
                </div>
              </div>
            ))}
          </div>

          {/* Benefits detail table */}
          <section className="table-section">
            <div className="table-header">
              <div>
                <h3 className="table-title">Benefits Breakdown — June 2025</h3>
                <p className="table-sub">Government-mandated contributions and deductions</p>
              </div>
            </div>
            <div className="table-wrapper">
              <table className="payroll-table">
                <thead>
                  <tr>
                    <th>Benefit / Contribution</th>
                    <th>Type</th>
                    <th>Monthly Amount</th>
                    <th>Annual Estimate</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {[
                    { name: 'GSIS Premium',    type: 'Retirement & Insurance', monthly: '₱3,046',  annual: '₱36,552', status: 'Active' },
                    { name: 'PhilHealth',      type: 'Health Insurance',       monthly: '₱850',    annual: '₱10,200', status: 'Active' },
                    { name: 'Pag-IBIG',        type: 'Housing Fund',           monthly: '₱100',    annual: '₱1,200',  status: 'Active' },
                    { name: 'Withholding Tax', type: 'Government Tax',         monthly: '₱2,772',  annual: '₱33,264', status: 'Active' },
                  ].map((row, i) => (
                    <tr key={i}>
                      <td style={{ fontWeight: 600, color: '#0b044d', fontSize: 13 }}>{row.name}</td>
                      <td><span className="dept-tag">{row.type}</span></td>
                      <td className="deduction">{row.monthly}</td>
                      <td style={{ fontWeight: 600, color: '#5a5888' }}>{row.annual}</td>
                      <td><span className="badge-status processed">{row.status}</span></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="table-footer">
              <p style={{ fontSize: 11.5, color: '#9999bb' }}>
                🔒 Benefits data is confidential and visible only to you.
              </p>
            </div>
          </section>
        </div>
      )}
    </div>
  );
}
