import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
const initials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

const initialPerformance = [
  { id: 'PGS-0041', name: 'Maria B. Santos', position: 'Administrative Officer IV', dept: 'Office of the Mayor', period: 'Jan-Jun 2025', rating: 4.8, status: 'Completed', evaluator: 'Mayor Office', dueDate: 'Jun 30, 2025' },
  { id: 'PGS-0082', name: 'Juan P. dela Cruz', position: 'Municipal Engineer II', dept: 'Office of the Mun. Engineer', period: 'Jan-Jun 2025', rating: 4.5, status: 'Completed', evaluator: 'Municipal Engineer', dueDate: 'Jun 30, 2025' },
  { id: 'PGS-0115', name: 'Ana R. Reyes', position: 'Nurse II', dept: 'Municipal Health Office', period: 'Jan-Jun 2025', rating: 4.9, status: 'Completed', evaluator: 'Health Officer', dueDate: 'Jun 30, 2025' },
  { id: 'PGS-0203', name: 'Carlos M. Mendoza', position: 'Municipal Treasurer III', dept: 'Office of the Mun. Treasurer', period: 'Jan-Jun 2025', rating: 4.6, status: 'Completed', evaluator: 'Municipal Treasurer', dueDate: 'Jun 30, 2025' },
  { id: 'PGS-0267', name: 'Liza G. Gomez', position: 'Social Welfare Officer II', dept: 'MSWD – Pagsanjan', period: 'Jan-Jun 2025', rating: null, status: 'Pending', evaluator: 'MSWD Head', dueDate: 'Jun 30, 2025' },
  { id: 'PGS-0310', name: 'Roberto T. Flores', position: 'Municipal Civil Registrar I', dept: 'Municipal Civil Registrar', period: 'Jan-Jun 2025', rating: null, status: 'Pending', evaluator: 'Civil Registrar', dueDate: 'Jun 30, 2025' },
];

const departments = ['All Departments', 'Office of the Mayor', 'Office of the Mun. Engineer', 'Municipal Health Office', 'MSWD – Pagsanjan', 'Office of the Mun. Treasurer'];

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

function PerformanceModal({ perf, idx, onClose }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PERFORMANCE EVALUATION · {perf.id}</span>
            <h3 className="modal-title">{perf.name}</h3>
            <p className="modal-sub">{perf.position} · {perf.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-emp-row">
            <div className="emp-avatar lg" style={{ background: avatarColors[idx % avatarColors.length] }}>{initials(perf.name)}</div>
            <div>
              <p className="modal-emp-id">{perf.id}</p>
              <span className={`badge-status ${perf.status === 'Completed' ? 'processed' : 'pending'}`}>{perf.status}</span>
            </div>
          </div>
          <div className="modal-row"><span>Employee</span><strong>{perf.name}</strong></div>
          <div className="modal-row"><span>Position</span><strong>{perf.position}</strong></div>
          <div className="modal-row"><span>Department</span><strong>{perf.dept}</strong></div>
          <div className="modal-row"><span>Evaluation Period</span><strong>{perf.period}</strong></div>
          <div className="modal-row"><span>Evaluator</span><strong>{perf.evaluator}</strong></div>
          <div className="modal-row"><span>Due Date</span><strong>{perf.dueDate}</strong></div>
          <div className="modal-row">
            <span>Overall Rating</span>
            <strong>{perf.rating ? `${perf.rating} / 5.0` : 'Not yet rated'}</strong>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            {perf.status === 'Completed' ? 'View Full Report' : 'Start Evaluation'}
          </button>
        </div>
      </div>
    </div>
  );
}

function EvaluationFormModal({ initial, onClose, onSave }) {
  useEscape(onClose);
  const [form, setForm] = useState(initial || { rating: 4.0 });
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">EVALUATE PERFORMANCE</span>
            <h3 className="modal-title">{initial.name}</h3>
            <p className="modal-sub">{initial.position}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field form-full">
              <label>Overall Rating (1.0 - 5.0)</label>
              <input 
                type="number" 
                step="0.1" 
                min="1" 
                max="5" 
                value={form.rating} 
                onChange={e => set('rating', parseFloat(e.target.value))} 
              />
            </div>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => onSave(form)}>
            Submit Evaluation
          </button>
        </div>
      </div>
    </div>
  );
}

export default function AdminPerformance({ searchQuery = '' }) {
  const [performance, setPerformance] = useState(initialPerformance);
  const [deptFilter, setDeptFilter] = useState('All Departments');
  const [statusFilter, setStatusFilter] = useState('All');
  const [viewPerf, setViewPerf] = useState(null);
  const [evaluatePerf, setEvaluatePerf] = useState(null);

  const filtered = performance.filter(p => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || p.name.toLowerCase().includes(q) || p.id.toLowerCase().includes(q);
    const matchDept = deptFilter === 'All Departments' || p.dept === deptFilter;
    const matchStatus = statusFilter === 'All' || p.status === statusFilter;
    return matchSearch && matchDept && matchStatus;
  });

  const handleEvaluate = form => {
    setPerformance(p => p.map(perf => 
      perf.id === evaluatePerf.id 
        ? { ...perf, rating: form.rating, status: 'Completed' } 
        : perf
    ));
    setEvaluatePerf(null);
  };

  const totalCompleted = performance.filter(p => p.status === 'Completed').length;
  const totalPending = performance.filter(p => p.status === 'Pending').length;
  const avgRating = performance.filter(p => p.rating).reduce((sum, p) => sum + p.rating, 0) / performance.filter(p => p.rating).length || 0;

  return (
    <div>
      {viewPerf && <PerformanceModal perf={viewPerf} idx={performance.findIndex(p => p.id === viewPerf.id)} onClose={() => setViewPerf(null)} />}
      {evaluatePerf && <EvaluationFormModal initial={evaluatePerf} onClose={() => setEvaluatePerf(null)} onSave={handleEvaluate} />}

      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'Total Evaluations', value: performance.length, sub: 'All employees', accent: '#0b044d', icon: 'barChart' },
          { label: 'Completed', value: totalCompleted, sub: 'Finished evaluations', accent: '#15803d', icon: 'checkCircle' },
          { label: 'Pending', value: totalPending, sub: 'Awaiting evaluation', accent: '#d9bb00', icon: 'clock' },
          { label: 'Average Rating', value: avgRating.toFixed(1), sub: 'Out of 5.0', accent: '#8e1e18', icon: 'star' },
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
      </div>

      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Performance Evaluations</h3>
            <p className="table-sub">Municipal Government of Pagsanjan · {filtered.length} of {performance.length} evaluations</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={deptFilter} onChange={e => setDeptFilter(e.target.value)}>
              {departments.map(d => <option key={d}>{d}</option>)}
            </select>
            <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
              <option value="All">All Status</option>
              <option>Completed</option>
              <option>Pending</option>
            </select>
            <button className="btn-export">
              <Icon name="download" size={13} />
              Export
            </button>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Position</th>
                <th>Department</th>
                <th>Period</th>
                <th>Evaluator</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={8} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No evaluations found.</td></tr>
              )}
              {filtered.map((perf, i) => (
                <tr key={perf.id}>
                  <td>
                    <div className="emp-cell">
                      <div className="emp-avatar" style={{ background: avatarColors[performance.indexOf(perf) % avatarColors.length] }}>
                        {initials(perf.name)}
                      </div>
                      <div>
                        <p className="emp-name">{perf.name}</p>
                        <p className="emp-id">{perf.id}</p>
                      </div>
                    </div>
                  </td>
                  <td className="position-cell">{perf.position}</td>
                  <td><span className="dept-tag">{perf.dept}</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{perf.period}</td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a' }}>{perf.evaluator}</td>
                  <td>
                    {perf.rating ? (
                      <span style={{ fontSize: 13, color: '#0b044d', fontWeight: 600 }}>
                        {perf.rating} / 5.0
                      </span>
                    ) : (
                      <span style={{ fontSize: 12.5, color: '#9999bb' }}>Not rated</span>
                    )}
                  </td>
                  <td><span className={`badge-status ${perf.status === 'Completed' ? 'processed' : 'pending'}`}>{perf.status}</span></td>
                  <td>
                    <div className="row-actions">
                      <button className="btn-view" onClick={() => setViewPerf(perf)}>View</button>
                      {perf.status === 'Pending' && (
                        <button className="btn-edit" onClick={() => setEvaluatePerf(perf)}>Evaluate</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{filtered.length}</strong> of <strong>{performance.length}</strong> evaluations</p>
          <div className="pagination">
            <button className="page-btn active">1</button>
            <button className="page-btn">›</button>
          </div>
        </div>
      </section>
    </div>
  );
}
