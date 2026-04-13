import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const initialTrainings = [
  { id: 'TRN-001', title: 'Leadership Development Program', type: 'Leadership', participants: 25, capacity: 30, status: 'Ongoing', startDate: 'Jun 15, 2025', endDate: 'Jul 15, 2025', venue: 'Municipal Hall Conference Room' },
  { id: 'TRN-002', title: 'Digital Literacy Training', type: 'Technical', participants: 18, capacity: 20, status: 'Ongoing', startDate: 'Jun 20, 2025', endDate: 'Jun 30, 2025', venue: 'IT Training Center' },
  { id: 'TRN-003', title: 'Customer Service Excellence', type: 'Soft Skills', participants: 30, capacity: 30, status: 'Completed', startDate: 'May 10, 2025', endDate: 'May 20, 2025', venue: 'Municipal Hall Conference Room' },
  { id: 'TRN-004', title: 'Financial Management Workshop', type: 'Technical', participants: 12, capacity: 25, status: 'Scheduled', startDate: 'Jul 5, 2025', endDate: 'Jul 10, 2025', venue: 'Treasurer Office Training Room' },
  { id: 'TRN-005', title: 'Emergency Response Training', type: 'Safety', participants: 40, capacity: 50, status: 'Scheduled', startDate: 'Jul 20, 2025', endDate: 'Jul 22, 2025', venue: 'MDRRM Office' },
];

const trainingTypes = ['All Types', 'Leadership', 'Technical', 'Soft Skills', 'Safety', 'Compliance'];

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

const typeAccent = { Leadership: '#0b044d', Technical: '#15803d', 'Soft Skills': '#d9bb00', Safety: '#8e1e18', Compliance: '#6b3fa0' };

function TrainingModal({ training, onClose }) {
  useEscape(onClose);
  const accent = typeAccent[training.type] || '#0b044d';
  const statusClass = training.status === 'Ongoing' ? 'processed' : training.status === 'Completed' ? 'on-hold' : 'pending';
  const fillPct = Math.round((training.participants / training.capacity) * 100);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div className="pmodal-hero">
            <div style={{ width: 52, height: 52, borderRadius: 14, background: `linear-gradient(135deg, ${accent} 0%, ${accent}99 100%)`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
              <Icon name="bookOpen" size={24} color="#fff" />
            </div>
            <div>
              <span className="modal-eyebrow">TRAINING PROGRAM · {training.id}</span>
              <h3 className="modal-title">{training.title}</h3>
              <p className="modal-sub">{training.type} Training · {training.venue}</p>
              <div className="pmodal-badges">
                <span className={`badge-status ${statusClass}`}>{training.status}</span>
                <span className="badge-emptype">{training.type}</span>
              </div>
            </div>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '12px', marginBottom: '18px' }}>
            <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div style={{ width: 38, height: 38, borderRadius: 10, background: 'linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </div>
              <div>
                <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 2 }}>Participants</p>
                <p style={{ fontSize: 16, fontWeight: 800, color: '#0b044d' }}>{training.participants}<span style={{ fontSize: 12, fontWeight: 500, color: '#9999bb' }}> / {training.capacity}</span></p>
              </div>
            </div>
            <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div style={{ width: 38, height: 38, borderRadius: 10, background: `linear-gradient(135deg, ${accent} 0%, ${accent}99 100%)`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              </div>
              <div>
                <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 2 }}>Capacity Fill</p>
                <p style={{ fontSize: 16, fontWeight: 800, color: accent }}>{fillPct}%</p>
              </div>
            </div>
          </div>
          <div style={{ marginBottom: 16 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6 }}>
              <span style={{ fontSize: 11, color: '#9999bb', fontWeight: 600, letterSpacing: '0.8px' }}>ENROLLMENT PROGRESS</span>
              <span style={{ fontSize: 11, color: accent, fontWeight: 700 }}>{fillPct}%</span>
            </div>
            <div style={{ height: 8, background: '#f0effe', borderRadius: 99 }}>
              <div style={{ height: '100%', width: `${fillPct}%`, background: `linear-gradient(90deg, ${accent}, ${accent}99)`, borderRadius: 99, transition: 'width 0.4s' }} />
            </div>
          </div>
          <div className="modal-section-label">SCHEDULE & DETAILS</div>
          <div className="modal-row"><span>Start Date</span><strong>{training.startDate}</strong></div>
          <div className="modal-row"><span>End Date</span><strong>{training.endDate}</strong></div>
          <div className="modal-row"><span>Venue</span><strong>{training.venue}</strong></div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">View Participants</button>
        </div>
      </div>
    </div>
  );
}

function TrainingFormModal({ initial, onClose, onSave }) {
  useEscape(onClose);
  const isEdit = !!initial?.id;
  const [form, setForm] = useState(initial || { title: '', type: trainingTypes[1], capacity: 20, startDate: '', endDate: '', venue: '' });
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">{isEdit ? 'EDIT TRAINING' : 'NEW TRAINING'}</span>
            <h3 className="modal-title">{isEdit ? `Edit — ${initial.title}` : 'Create Training Program'}</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field form-full">
              <label>Training Title</label>
              <input value={form.title} onChange={e => set('title', e.target.value)} placeholder="e.g. Leadership Development Program" />
            </div>
            <div className="form-field">
              <label>Training Type</label>
              <select value={form.type} onChange={e => set('type', e.target.value)}>
                {trainingTypes.slice(1).map(t => <option key={t}>{t}</option>)}
              </select>
            </div>
            <div className="form-field">
              <label>Capacity</label>
              <input type="number" value={form.capacity} onChange={e => set('capacity', e.target.value)} min="1" />
            </div>
            <div className="form-field">
              <label>Start Date</label>
              <input type="date" value={form.startDate} onChange={e => set('startDate', e.target.value)} />
            </div>
            <div className="form-field">
              <label>End Date</label>
              <input type="date" value={form.endDate} onChange={e => set('endDate', e.target.value)} />
            </div>
            <div className="form-field form-full">
              <label>Venue</label>
              <input value={form.venue} onChange={e => set('venue', e.target.value)} placeholder="e.g. Municipal Hall Conference Room" />
            </div>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => { if (form.title && form.startDate && form.endDate) onSave(form); }}>
            {isEdit ? 'Save Changes' : 'Create Training'}
          </button>
        </div>
      </div>
    </div>
  );
}

export default function AdminTraining({ searchQuery = '' }) {
  const [trainings, setTrainings] = useState(initialTrainings);
  const [typeFilter, setTypeFilter] = useState('All Types');
  const [statusFilter, setStatusFilter] = useState('All');
  const [viewTraining, setViewTraining] = useState(null);
  const [editTraining, setEditTraining] = useState(null);
  const [addOpen, setAddOpen] = useState(false);

  const filtered = trainings.filter(t => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || t.title.toLowerCase().includes(q) || t.id.toLowerCase().includes(q);
    const matchType = typeFilter === 'All Types' || t.type === typeFilter;
    const matchStatus = statusFilter === 'All' || t.status === statusFilter;
    return matchSearch && matchType && matchStatus;
  });

  const handleAdd = form => {
    const newId = `TRN-${String(trainings.length + 1).padStart(3, '0')}`;
    setTrainings(t => [...t, { ...form, id: newId, status: 'Scheduled', participants: 0 }]);
    setAddOpen(false);
  };

  const handleEdit = form => {
    setTrainings(t => t.map(training => training.id === form.id ? { ...training, ...form } : training));
    setEditTraining(null);
  };

  const totalOngoing = trainings.filter(t => t.status === 'Ongoing').length;
  const totalParticipants = trainings.reduce((sum, t) => sum + t.participants, 0);
  const totalCompleted = trainings.filter(t => t.status === 'Completed').length;

  return (
    <div>
      {viewTraining && <TrainingModal training={viewTraining} onClose={() => setViewTraining(null)} />}
      {(addOpen || editTraining) && <TrainingFormModal initial={editTraining} onClose={() => { setAddOpen(false); setEditTraining(null); }} onSave={editTraining ? handleEdit : handleAdd} />}

      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'Total Programs',     value: trainings.length,  sub: 'All training programs', accent: '#0b044d', icon: 'bookOpen' },
          { label: 'Ongoing',            value: totalOngoing,       sub: 'Currently active',      accent: '#15803d', icon: 'play' },
          { label: 'Total Participants', value: totalParticipants,  sub: 'All enrollments',       accent: '#d9bb00', icon: 'users' },
          { label: 'Completed',          value: totalCompleted,     sub: 'Finished programs',     accent: '#8e1e18', icon: 'checkCircle' },
        ].map((s, i) => (
          <div className="stat-card" key={i}>
            <div className="stat-top">
              <p className="stat-label">{s.label}</p>
              <div className="stat-icon-wrap" style={{ background: s.accent + '15', color: s.accent }}><Icon name={s.icon} size={18} /></div>
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
            <h3 className="table-title">Training Programs</h3>
            <p className="table-sub">Municipal Government of Pagsanjan · {filtered.length} of {trainings.length} programs</p>
          </div>
          <div className="table-actions">
            <select className="filter-select" value={typeFilter} onChange={e => setTypeFilter(e.target.value)}>
              {trainingTypes.map(t => <option key={t}>{t}</option>)}
            </select>
            <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
              <option value="All">All Status</option>
              <option>Scheduled</option>
              <option>Ongoing</option>
              <option>Completed</option>
            </select>
            <button className="btn-export">
              <Icon name="download" size={13} />
              Export
            </button>
            <button className="modal-btn-primary" style={{ padding: '8px 18px', fontSize: 12.5, display: 'flex', alignItems: 'center', gap: '6px' }} onClick={() => setAddOpen(true)}>
              <Icon name="plus" size={13} />
              Add Training
            </button>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="payroll-table">
            <thead>
              <tr>
                <th>Training ID</th>
                <th>Program Title</th>
                <th>Type</th>
                <th>Participants</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Venue</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 && (
                <tr><td colSpan={9} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No training programs found.</td></tr>
              )}
              {filtered.map(training => (
                <tr key={training.id}>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', fontWeight: 500 }}>{training.id}</td>
                  <td className="position-cell">{training.title}</td>
                  <td><span className="badge-emptype">{training.type}</span></td>
                  <td style={{ fontSize: 13, color: '#0b044d', fontWeight: 600, textAlign: 'center' }}>{training.participants} / {training.capacity}</td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{training.startDate}</td>
                  <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{training.endDate}</td>
                  <td><span className="dept-tag">{training.venue}</span></td>
                  <td><span className={`badge-status ${training.status === 'Ongoing' ? 'processed' : training.status === 'Completed' ? 'on-hold' : 'pending'}`}>{training.status}</span></td>
                  <td>
                    <div className="row-actions">
                      <button className="btn-view" onClick={() => setViewTraining(training)}>View</button>
                      <button className="btn-edit" onClick={() => setEditTraining(training)}>Edit</button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{filtered.length}</strong> of <strong>{trainings.length}</strong> programs</p>
          <div className="pagination">
            <button className="page-btn active">1</button>
            <button className="page-btn">›</button>
          </div>
        </div>
      </section>
    </div>
  );
}
