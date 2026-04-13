import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';

const emp = {
  id: 'JO-0042',
  name: 'Juan D. Cruz',
  initials: 'JD',
  avatarColor: '#1a6e3c',
  position: 'Utility Worker I',
  dept: 'General Services Office',
};

const myEvaluations = [
  { id: 'EVAL-2025-01', period: 'Apr-Jun 2025', rating: 4.5, status: 'Completed', evaluator: 'General Services Head', completedDate: 'Jun 28, 2025', feedback: 'Good performance and dedication to assigned tasks.', strengths: ['Reliability', 'Teamwork', 'Punctuality'], improvements: ['Technical Skills'] },
  { id: 'EVAL-2025-02', period: 'Jan-Mar 2025', rating: 4.3, status: 'Completed', evaluator: 'General Services Head', completedDate: 'Mar 30, 2025', feedback: 'Satisfactory performance with consistent attendance.', strengths: ['Work Ethic', 'Cooperation'], improvements: ['Initiative', 'Communication'] },
];

const performanceGoals = [
  { id: 'GOAL-001', title: 'Complete Safety Training Program', category: 'Professional Development', progress: 40, target: 'Jun 22, 2025', status: 'In Progress' },
  { id: 'GOAL-002', title: 'Improve Task Completion Rate', category: 'Efficiency', progress: 75, target: 'Jul 31, 2025', status: 'In Progress' },
];

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

function EvaluationModal({ evaluation, onClose }) {
  useEscape(onClose);
  const ratingColor = evaluation.rating >= 4.5 ? '#15803d' : evaluation.rating >= 4.0 ? '#d9bb00' : '#8e1e18';
  
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div className="pmodal-hero">
            <div style={{ width: 52, height: 52, borderRadius: 14, background: `linear-gradient(135deg, ${ratingColor} 0%, ${ratingColor}99 100%)`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div>
              <span className="modal-eyebrow">PERFORMANCE EVALUATION · {evaluation.id}</span>
              <h3 className="modal-title">Evaluation Report</h3>
              <p className="modal-sub">{evaluation.period} · Completed on {evaluation.completedDate}</p>
              <div className="pmodal-badges">
                <span className="badge-status on-hold">{evaluation.status}</span>
                <span className="badge-emptype" style={{ background: ratingColor + '15', color: ratingColor }}>Rating: {evaluation.rating}</span>
              </div>
            </div>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div style={{ background: '#f7f6ff', borderRadius: '12px', padding: '18px 20px', marginBottom: '20px', display: 'flex', alignItems: 'center', gap: '16px' }}>
            <div style={{ width: 48, height: 48, borderRadius: 12, background: `linear-gradient(135deg, ${ratingColor} 0%, ${ratingColor}99 100%)`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
              <span style={{ fontSize: 20, fontWeight: 800, color: '#fff' }}>{evaluation.rating}</span>
            </div>
            <div style={{ flex: 1 }}>
              <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4, fontWeight: 600, letterSpacing: '0.8px' }}>OVERALL RATING</p>
              <p style={{ fontSize: 16, fontWeight: 800, color: '#0b044d' }}>{evaluation.rating} out of 5.0</p>
            </div>
          </div>

          <div className="modal-section-label">EVALUATION DETAILS</div>
          <div className="modal-row"><span>Evaluation Period</span><strong>{evaluation.period}</strong></div>
          <div className="modal-row"><span>Evaluator</span><strong>{evaluation.evaluator}</strong></div>
          <div className="modal-row"><span>Completed Date</span><strong>{evaluation.completedDate}</strong></div>
          
          <div className="modal-section-label" style={{ marginTop: 20 }}>FEEDBACK</div>
          <p style={{ fontSize: 13, color: '#6b6a8a', lineHeight: '1.6', marginBottom: 16 }}>{evaluation.feedback}</p>

          <div className="modal-section-label">STRENGTHS</div>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginBottom: 16 }}>
            {evaluation.strengths.map((s, i) => (
              <span key={i} className="badge-emptype" style={{ background: '#15803d15', color: '#15803d' }}>{s}</span>
            ))}
          </div>

          <div className="modal-section-label">AREAS FOR IMPROVEMENT</div>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px' }}>
            {evaluation.improvements.map((imp, i) => (
              <span key={i} className="badge-emptype" style={{ background: '#d9bb0015', color: '#d9bb00' }}>{imp}</span>
            ))}
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Report
          </button>
        </div>
      </div>
    </div>
  );
}

function GoalModal({ goal, onClose }) {
  useEscape(onClose);
  const statusColor = goal.status === 'Achieved' ? '#15803d' : goal.status === 'In Progress' ? '#d9bb00' : '#8e1e18';
  
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PERFORMANCE GOAL · {goal.id}</span>
            <h3 className="modal-title">{goal.title}</h3>
            <p className="modal-sub">{goal.category} · Target: {goal.target}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div style={{ marginBottom: 20 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
              <span style={{ fontSize: 11, color: '#9999bb', fontWeight: 600, letterSpacing: '0.8px' }}>PROGRESS</span>
              <span style={{ fontSize: 11, color: statusColor, fontWeight: 700 }}>{goal.progress}%</span>
            </div>
            <div style={{ height: 8, background: '#f0effe', borderRadius: 99 }}>
              <div style={{ height: '100%', width: `${goal.progress}%`, background: `linear-gradient(90deg, ${statusColor}, ${statusColor}99)`, borderRadius: 99, transition: 'width 0.4s' }} />
            </div>
          </div>
          
          <div className="modal-row"><span>Category</span><strong>{goal.category}</strong></div>
          <div className="modal-row"><span>Target Date</span><strong>{goal.target}</strong></div>
          <div className="modal-row"><span>Status</span><span className={`badge-status ${goal.status === 'Achieved' ? 'on-hold' : goal.status === 'In Progress' ? 'processed' : 'pending'}`}>{goal.status}</span></div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
        </div>
      </div>
    </div>
  );
}

export default function JobOrderPerformance() {
  const [viewEvaluation, setViewEvaluation] = useState(null);
  const [viewGoal, setViewGoal] = useState(null);

  const avgRating = myEvaluations.reduce((sum, e) => sum + e.rating, 0) / myEvaluations.length;
  const completedGoals = performanceGoals.filter(g => g.status === 'Achieved').length;
  const latestRating = myEvaluations[0]?.rating || 0;

  return (
    <div>
      {viewEvaluation && <EvaluationModal evaluation={viewEvaluation} onClose={() => setViewEvaluation(null)} />}
      {viewGoal && <GoalModal goal={viewGoal} onClose={() => setViewGoal(null)} />}

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
          <div className="banner-badge"><span className="banner-badge-dot" />Performance Management</div>
          <div className="banner-badge outline">Latest Rating: {latestRating.toFixed(1)} / 5.0</div>
        </div>
      </div>

      {/* Stats Grid */}
      <section className="stats-grid" style={{ marginBottom: 24 }}>
        {[
          { label: 'Latest Rating', value: latestRating.toFixed(1), sub: 'Apr-Jun 2025', accent: '#0b044d', icon: 'award' },
          { label: 'Average Rating', value: avgRating.toFixed(1), sub: 'All evaluations', accent: '#15803d', icon: 'trendingUp' },
          { label: 'Total Evaluations', value: myEvaluations.length, sub: 'Completed reviews', accent: '#d9bb00', icon: 'clipboard' },
          { label: 'Active Goals', value: performanceGoals.length, sub: `${completedGoals} achieved`, accent: '#8e1e18', icon: 'target' },
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
      </section>

      {/* Performance Trend Chart */}
      <section className="table-section" style={{ marginBottom: 24 }}>
        <div className="table-header">
          <div>
            <h3 className="table-title">Performance Trend</h3>
            <p className="table-sub">Your rating history over time</p>
          </div>
        </div>
        <div style={{ padding: '28px 32px' }}>
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: '16px', height: '200px' }}>
            {myEvaluations.slice().reverse().map((evaluation, i) => {
              const height = (evaluation.rating / 5.0) * 100;
              const color = evaluation.rating >= 4.5 ? '#15803d' : evaluation.rating >= 4.0 ? '#d9bb00' : '#8e1e18';
              return (
                <div key={evaluation.id} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '12px' }}>
                  <div style={{ width: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-end', height: '100%' }}>
                    <div 
                      style={{ 
                        width: '100%', 
                        height: `${height}%`, 
                        background: `linear-gradient(180deg, ${color} 0%, ${color}99 100%)`, 
                        borderRadius: '8px 8px 0 0',
                        display: 'flex',
                        alignItems: 'flex-start',
                        justifyContent: 'center',
                        paddingTop: '8px',
                        minHeight: '40px',
                        transition: 'all 0.3s'
                      }}
                    >
                      <span style={{ fontSize: 13, fontWeight: 700, color: '#fff' }}>{evaluation.rating}</span>
                    </div>
                  </div>
                  <div style={{ textAlign: 'center' }}>
                    <p style={{ fontSize: 11, color: '#6b6a8a', fontWeight: 600, whiteSpace: 'nowrap' }}>{evaluation.period}</p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Performance Goals */}
      <section className="table-section" style={{ marginBottom: 24 }}>
        <div className="table-header">
          <div>
            <h3 className="table-title">Performance Goals</h3>
            <p className="table-sub">Track your progress towards set objectives</p>
          </div>
        </div>

        <div style={{ padding: '16px 20px' }}>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '16px' }}>
            {performanceGoals.map(goal => {
              const statusColor = goal.status === 'Achieved' ? '#15803d' : goal.status === 'In Progress' ? '#d9bb00' : '#8e1e18';
              return (
                <div key={goal.id} className="stat-card" style={{ cursor: 'pointer' }} onClick={() => setViewGoal(goal)}>
                  <div style={{ marginBottom: '12px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '8px' }}>
                      <div style={{ flex: 1 }}>
                        <p style={{ fontSize: '10px', color: '#9999bb', fontWeight: '600', marginBottom: '4px' }}>{goal.id}</p>
                        <h4 style={{ fontSize: '14px', fontWeight: '700', color: '#0b044d', margin: 0, lineHeight: '1.3', marginBottom: '4px' }}>{goal.title}</h4>
                        <p style={{ fontSize: '12px', color: '#6b6a8a' }}>{goal.category}</p>
                      </div>
                      <span className={`badge-status ${goal.status === 'Achieved' ? 'on-hold' : goal.status === 'In Progress' ? 'processed' : 'pending'}`} style={{ fontSize: '10px', padding: '4px 10px' }}>{goal.status}</span>
                    </div>
                    <div style={{ marginBottom: '12px' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                        <span style={{ fontSize: 10, color: '#9999bb', fontWeight: 600 }}>PROGRESS</span>
                        <span style={{ fontSize: 10, color: statusColor, fontWeight: 700 }}>{goal.progress}%</span>
                      </div>
                      <div style={{ height: 6, background: '#f0effe', borderRadius: 99 }}>
                        <div style={{ height: '100%', width: `${goal.progress}%`, background: `linear-gradient(90deg, ${statusColor}, ${statusColor}99)`, borderRadius: 99 }} />
                      </div>
                    </div>
                  </div>
                  <div style={{ borderTop: '1px solid #f0effe', paddingTop: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div>
                      <p style={{ fontSize: '10px', color: '#9999bb', marginBottom: '2px' }}>Target Date</p>
                      <p style={{ fontSize: '12px', color: '#0b044d', fontWeight: '600' }}>{goal.target}</p>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Evaluation History */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Evaluation History</h3>
            <p className="table-sub">Your complete performance evaluation records</p>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Evaluation ID</th>
                <th>Period</th>
                <th>Evaluator</th>
                <th>Completed Date</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {myEvaluations.length === 0 && (
                <tr><td colSpan={7} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No evaluations found.</td></tr>
              )}
              {myEvaluations.map(evaluation => {
                const ratingColor = evaluation.rating >= 4.5 ? '#15803d' : evaluation.rating >= 4.0 ? '#d9bb00' : '#8e1e18';
                return (
                  <tr key={evaluation.id}>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a', fontWeight: 500 }}>{evaluation.id}</td>
                    <td style={{ fontSize: 12.5, color: '#0b044d', fontWeight: 600 }}>{evaluation.period}</td>
                    <td><span className="dept-tag">{evaluation.evaluator}</span></td>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{evaluation.completedDate}</td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        <span style={{ fontSize: 14, fontWeight: 700, color: ratingColor }}>{evaluation.rating}</span>
                        <span style={{ fontSize: 11, color: '#9999bb' }}>/ 5.0</span>
                      </div>
                    </td>
                    <td><span className="badge-status on-hold">{evaluation.status}</span></td>
                    <td>
                      <div className="row-actions">
                        <button className="btn-view" onClick={() => setViewEvaluation(evaluation)}>View</button>
                        <button className="btn-edit">Download</button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{myEvaluations.length}</strong> evaluation records</p>
        </div>
      </section>
    </div>
  );
}
