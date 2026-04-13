import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';

const myTrainings = [
  { id: 'TRN-001', title: 'Leadership Development Program', type: 'Leadership', status: 'Enrolled', progress: 65, startDate: 'Jun 15, 2025', endDate: 'Jul 15, 2025', venue: 'Municipal Hall Conference Room', certificate: null },
  { id: 'TRN-003', title: 'Customer Service Excellence', type: 'Soft Skills', status: 'Completed', progress: 100, startDate: 'May 10, 2025', endDate: 'May 20, 2025', venue: 'Municipal Hall Conference Room', certificate: 'CERT-2025-003' },
  { id: 'TRN-002', title: 'Digital Literacy Training', type: 'Technical', status: 'Completed', progress: 100, startDate: 'Apr 5, 2025', endDate: 'Apr 15, 2025', venue: 'IT Training Center', certificate: 'CERT-2025-002' },
];

const availableTrainings = [
  { id: 'TRN-004', title: 'Financial Management Workshop', type: 'Technical', slots: 13, capacity: 25, startDate: 'Jul 5, 2025', endDate: 'Jul 10, 2025', venue: 'Treasurer Office Training Room' },
  { id: 'TRN-005', title: 'Emergency Response Training', type: 'Safety', slots: 10, capacity: 50, startDate: 'Jul 20, 2025', endDate: 'Jul 22, 2025', venue: 'MDRRM Office' },
];

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

const typeAccent = { Leadership: '#0b044d', Technical: '#15803d', 'Soft Skills': '#d9bb00', Safety: '#8e1e18', Compliance: '#6b3fa0' };

function TrainingModal({ training, onClose, isEnrolled }) {
  useEscape(onClose);
  const accent = typeAccent[training.type] || '#0b044d';
  const statusClass = training.status === 'Enrolled' ? 'processed' : training.status === 'Completed' ? 'on-hold' : 'pending';

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div className="pmodal-hero">
            <div style={{ width: 52, height: 52, borderRadius: 14, background: `linear-gradient(135deg, ${accent} 0%, ${accent}99 100%)`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <div>
              <span className="modal-eyebrow">TRAINING PROGRAM · {training.id}</span>
              <h3 className="modal-title">{training.title}</h3>
              <p className="modal-sub">{training.type} Training · {training.venue}</p>
              {isEnrolled && (
                <div className="pmodal-badges">
                  <span className={`badge-status ${statusClass}`}>{training.status}</span>
                  <span className="badge-emptype">{training.type}</span>
                </div>
              )}
            </div>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          {isEnrolled && training.progress !== undefined && (
            <div style={{ marginBottom: 20 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                <span style={{ fontSize: 11, color: '#9999bb', fontWeight: 600, letterSpacing: '0.8px' }}>TRAINING PROGRESS</span>
                <span style={{ fontSize: 11, color: accent, fontWeight: 700 }}>{training.progress}%</span>
              </div>
              <div style={{ height: 8, background: '#f0effe', borderRadius: 99 }}>
                <div style={{ height: '100%', width: `${training.progress}%`, background: `linear-gradient(90deg, ${accent}, ${accent}99)`, borderRadius: 99, transition: 'width 0.4s' }} />
              </div>
            </div>
          )}
          {!isEnrolled && (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '12px', marginBottom: '18px' }}>
              <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                <div style={{ width: 38, height: 38, borderRadius: 10, background: 'linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div>
                  <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 2 }}>Available Slots</p>
                  <p style={{ fontSize: 16, fontWeight: 800, color: '#0b044d' }}>{training.slots}<span style={{ fontSize: 12, fontWeight: 500, color: '#9999bb' }}> / {training.capacity}</span></p>
                </div>
              </div>
            </div>
          )}
          <div className="modal-section-label">SCHEDULE & DETAILS</div>
          <div className="modal-row"><span>Start Date</span><strong>{training.startDate}</strong></div>
          <div className="modal-row"><span>End Date</span><strong>{training.endDate}</strong></div>
          <div className="modal-row"><span>Venue</span><strong>{training.venue}</strong></div>
          {isEnrolled && training.certificate && (
            <div className="modal-row"><span>Certificate No.</span><strong>{training.certificate}</strong></div>
          )}
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          {isEnrolled && training.certificate && (
            <button className="modal-btn-primary">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Download Certificate
            </button>
          )}
          {!isEnrolled && (
            <button className="modal-btn-primary">Enroll Now</button>
          )}
        </div>
      </div>
    </div>
  );
}

export default function PermanentTraining() {
  const [viewTraining, setViewTraining] = useState(null);
  const [isEnrolled, setIsEnrolled] = useState(false);

  const totalCompleted = myTrainings.filter(t => t.status === 'Completed').length;
  const totalEnrolled = myTrainings.filter(t => t.status === 'Enrolled').length;

  const handleViewMy = (training) => {
    setViewTraining(training);
    setIsEnrolled(true);
  };

  const handleViewAvailable = (training) => {
    setViewTraining(training);
    setIsEnrolled(false);
  };

  return (
    <div>
      {viewTraining && <TrainingModal training={viewTraining} onClose={() => setViewTraining(null)} isEnrolled={isEnrolled} />}

      {/* Stats Grid */}
      <section className="stats-grid" style={{ marginBottom: 24 }}>
        {[
          { label: 'Total Trainings', value: myTrainings.length, sub: 'All programs', accent: '#0b044d', icon: 'bookOpen' },
          { label: 'Completed', value: totalCompleted, sub: 'Finished programs', accent: '#15803d', icon: 'checkCircle' },
          { label: 'Enrolled', value: totalEnrolled, sub: 'Currently active', accent: '#d9bb00', icon: 'clipboard' },
          { label: 'Available', value: availableTrainings.length, sub: 'Open for enrollment', accent: '#8e1e18', icon: 'calendar' },
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

      {/* My Trainings */}
      <section className="table-section" style={{ marginBottom: 24 }}>
        <div className="table-header">
          <div>
            <h3 className="table-title">My Trainings</h3>
            <p className="table-sub">Your enrolled and completed training programs</p>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Training ID</th>
                <th>Program Title</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {myTrainings.length === 0 && (
                <tr><td colSpan={8} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No trainings found.</td></tr>
              )}
              {myTrainings.map(training => (
                <tr key={training.id}>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', fontWeight: 500 }}>{training.id}</td>
                  <td className="position-cell">{training.title}</td>
                  <td><span className="badge-emptype">{training.type}</span></td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{training.startDate}</td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{training.endDate}</td>
                  <td>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                      <div style={{ flex: 1, height: 6, background: '#f0effe', borderRadius: 99, minWidth: 60 }}>
                        <div style={{ height: '100%', width: `${training.progress}%`, background: training.progress === 100 ? '#15803d' : '#d9bb00', borderRadius: 99 }} />
                      </div>
                      <span style={{ fontSize: 12, fontWeight: 600, color: '#6b6a8a', minWidth: 35 }}>{training.progress}%</span>
                    </div>
                  </td>
                  <td><span className={`badge-status ${training.status === 'Enrolled' ? 'processed' : 'on-hold'}`}>{training.status}</span></td>
                  <td>
                    <div className="row-actions">
                      <button className="btn-view" onClick={() => handleViewMy(training)}>View</button>
                      {training.certificate && (
                        <button className="btn-edit">Certificate</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{myTrainings.length}</strong> training programs</p>
        </div>
      </section>

      {/* Available Trainings */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Available Trainings</h3>
            <p className="table-sub">Open programs you can enroll in</p>
          </div>
        </div>

        <div style={{ padding: '16px 20px' }}>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))', gap: '16px' }}>
            {availableTrainings.map(training => {
              const accent = typeAccent[training.type] || '#0b044d';
              const fillPct = Math.round(((training.capacity - training.slots) / training.capacity) * 100);
              return (
                <div key={training.id} className="stat-card" style={{ cursor: 'pointer', transition: 'all 0.2s', position: 'relative' }}>
                  <div style={{ position: 'absolute', top: '16px', right: '16px' }}>
                    <span className="badge-emptype">{training.type}</span>
                  </div>
                  <div style={{ marginBottom: '12px' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '8px' }}>
                      <div style={{ width: '44px', height: '44px', borderRadius: '10px', background: `linear-gradient(135deg, ${accent} 0%, ${accent}99 100%)`, display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontSize: '18px', fontWeight: '700' }}>
                        {training.slots}
                      </div>
                      <div style={{ flex: 1 }}>
                        <p style={{ fontSize: '10px', color: '#9999bb', fontWeight: '600', marginBottom: '2px' }}>{training.id}</p>
                        <h4 style={{ fontSize: '14px', fontWeight: '700', color: '#0b044d', margin: 0, lineHeight: '1.3' }}>{training.title}</h4>
                      </div>
                    </div>
                    <p style={{ fontSize: '12px', color: '#6b6a8a', marginBottom: '8px' }}>{training.venue}</p>
                    <div style={{ marginBottom: '12px' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                        <span style={{ fontSize: 10, color: '#9999bb', fontWeight: 600 }}>CAPACITY</span>
                        <span style={{ fontSize: 10, color: accent, fontWeight: 700 }}>{fillPct}% filled</span>
                      </div>
                      <div style={{ height: 6, background: '#f0effe', borderRadius: 99 }}>
                        <div style={{ height: '100%', width: `${fillPct}%`, background: `linear-gradient(90deg, ${accent}, ${accent}99)`, borderRadius: 99 }} />
                      </div>
                    </div>
                  </div>
                  <div style={{ borderTop: '1px solid #f0effe', paddingTop: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div>
                      <p style={{ fontSize: '10px', color: '#9999bb', marginBottom: '2px' }}>Start Date</p>
                      <p style={{ fontSize: '12px', color: '#0b044d', fontWeight: '600' }}>{training.startDate}</p>
                    </div>
                    <div style={{ display: 'flex', gap: '6px' }}>
                      <button className="btn-view" onClick={() => handleViewAvailable(training)}>View</button>
                      <button className="btn-edit">Enroll</button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </section>
    </div>
  );
}
