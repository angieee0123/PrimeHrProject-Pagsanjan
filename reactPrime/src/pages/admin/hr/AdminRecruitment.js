import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const initialJobs = [
  { id: 'JOB-001', title: 'Administrative Officer IV', dept: 'Office of the Mayor', type: 'Permanent', slots: 1, applicants: 12, status: 'Open', posted: 'Jun 1, 2025', deadline: 'Jun 30, 2025' },
  { id: 'JOB-002', title: 'Municipal Engineer II', dept: 'Office of the Mun. Engineer', type: 'Permanent', slots: 1, applicants: 8, status: 'Open', posted: 'Jun 5, 2025', deadline: 'Jul 5, 2025' },
  { id: 'JOB-003', title: 'Nurse II', dept: 'Municipal Health Office', type: 'Permanent', slots: 2, applicants: 24, status: 'Closed', posted: 'May 15, 2025', deadline: 'Jun 15, 2025' },
  { id: 'JOB-004', title: 'Social Welfare Officer', dept: 'MSWD – Pagsanjan', type: 'Casual', slots: 1, applicants: 15, status: 'Open', posted: 'Jun 10, 2025', deadline: 'Jul 10, 2025' },
];

const departments = ['All Departments', 'Office of the Mayor', 'Office of the Mun. Engineer', 'Municipal Health Office', 'MSWD – Pagsanjan', 'Office of the Mun. Treasurer'];

function useEscape(fn) {
  useEffect(() => {
    const h = e => { if (e.key === 'Escape') fn(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [fn]);
}

function JobModal({ job, onClose }) {
  useEscape(onClose);
  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">JOB POSTING · {job.id}</span>
            <h3 className="modal-title">{job.title}</h3>
            <p className="modal-sub">{job.dept}</p>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="modal-row"><span>Position</span><strong>{job.title}</strong></div>
          <div className="modal-row"><span>Department</span><strong>{job.dept}</strong></div>
          <div className="modal-row"><span>Employment Type</span><strong>{job.type}</strong></div>
          <div className="modal-row"><span>Available Slots</span><strong>{job.slots}</strong></div>
          <div className="modal-row"><span>Total Applicants</span><strong>{job.applicants}</strong></div>
          <div className="modal-row"><span>Posted Date</span><strong>{job.posted}</strong></div>
          <div className="modal-row"><span>Deadline</span><strong>{job.deadline}</strong></div>
          <div className="modal-row"><span>Status</span><span className={`badge-status ${job.status === 'Open' ? 'processed' : 'on-hold'}`}>{job.status}</span></div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Close</button>
          <button className="modal-btn-primary">View Applicants</button>
        </div>
      </div>
    </div>
  );
}

function JobFormModal({ initial, onClose, onSave }) {
  useEscape(onClose);
  const isEdit = !!initial?.id;
  const [form, setForm] = useState(initial || { title: '', dept: departments[1], type: 'Permanent', slots: 1, deadline: '' });
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={e => e.stopPropagation()}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">{isEdit ? 'EDIT JOB POSTING' : 'NEW JOB POSTING'}</span>
            <h3 className="modal-title">{isEdit ? `Edit — ${initial.title}` : 'Create Job Posting'}</h3>
          </div>
          <button className="modal-close" onClick={onClose}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div className="modal-body">
          <div className="form-grid">
            <div className="form-field form-full">
              <label>Position Title</label>
              <input value={form.title} onChange={e => set('title', e.target.value)} placeholder="e.g. Administrative Officer IV" />
            </div>
            <div className="form-field form-full">
              <label>Department</label>
              <select value={form.dept} onChange={e => set('dept', e.target.value)}>
                {departments.slice(1).map(d => <option key={d}>{d}</option>)}
              </select>
            </div>
            <div className="form-field">
              <label>Employment Type</label>
              <select value={form.type} onChange={e => set('type', e.target.value)}>
                <option>Permanent</option><option>Casual</option><option>Contractual</option><option>Job Order</option>
              </select>
            </div>
            <div className="form-field">
              <label>Available Slots</label>
              <input type="number" value={form.slots} onChange={e => set('slots', e.target.value)} min="1" />
            </div>
            <div className="form-field">
              <label>Application Deadline</label>
              <input type="date" value={form.deadline} onChange={e => set('deadline', e.target.value)} />
            </div>
          </div>
        </div>
        <div className="modal-footer">
          <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
          <button className="modal-btn-primary" onClick={() => { if (form.title && form.deadline) onSave(form); }}>
            {isEdit ? 'Save Changes' : 'Post Job'}
          </button>
        </div>
      </div>
    </div>
  );
}

export default function AdminRecruitment({ searchQuery = '' }) {
  const [jobs, setJobs] = useState(initialJobs);
  const [deptFilter, setDeptFilter] = useState('All Departments');
  const [statusFilter, setStatusFilter] = useState('All');
  const [viewJob, setViewJob] = useState(null);
  const [editJob, setEditJob] = useState(null);
  const [addOpen, setAddOpen] = useState(false);
  const [viewMode, setViewMode] = useState('grid');

  const filtered = jobs.filter(j => {
    const q = searchQuery.toLowerCase();
    const matchSearch = !q || j.title.toLowerCase().includes(q) || j.id.toLowerCase().includes(q);
    const matchDept = deptFilter === 'All Departments' || j.dept === deptFilter;
    const matchStatus = statusFilter === 'All' || j.status === statusFilter;
    return matchSearch && matchDept && matchStatus;
  });

  const handleAdd = form => {
    const newId = `JOB-${String(jobs.length + 1).padStart(3, '0')}`;
    setJobs(j => [...j, { ...form, id: newId, status: 'Open', applicants: 0, posted: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }]);
    setAddOpen(false);
  };

  const handleEdit = form => {
    setJobs(j => j.map(job => job.id === form.id ? { ...job, ...form } : job));
    setEditJob(null);
  };

  const totalOpen = jobs.filter(j => j.status === 'Open').length;
  const totalApplicants = jobs.reduce((sum, j) => sum + j.applicants, 0);
  const totalSlots = jobs.reduce((sum, j) => sum + j.slots, 0);

  return (
    <div>
      {viewJob && <JobModal job={viewJob} onClose={() => setViewJob(null)} />}
      {(addOpen || editJob) && <JobFormModal initial={editJob} onClose={() => { setAddOpen(false); setEditJob(null); }} onSave={editJob ? handleEdit : handleAdd} />}

      <div className="stats-grid" style={{ marginBottom: 24 }}>
        {[
          { label: 'Total Job Postings', value: jobs.length, sub: 'All positions', accent: '#0b044d', icon: 'briefcase' },
          { label: 'Open Positions', value: totalOpen, sub: 'Currently accepting', accent: '#15803d', icon: 'checkCircle' },
          { label: 'Total Applicants', value: totalApplicants, sub: 'All applications', accent: '#d9bb00', icon: 'users' },
          { label: 'Available Slots', value: totalSlots, sub: 'Positions to fill', accent: '#8e1e18', icon: 'target' },
        ].map((s, i) => (
          <div className="stat-card" key={i} style={{ '--accent-color': s.accent }}>
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

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '22px', flexWrap: 'wrap', gap: '14px' }}>
        <div style={{ display: 'flex', gap: '8px' }}>
          <button
            onClick={() => setViewMode('grid')}
            style={{
              padding: '8px 16px', borderRadius: '8px', border: viewMode === 'grid' ? '2px solid #0b044d' : '1.5px solid #e4e3f0',
              background: viewMode === 'grid' ? '#0b044d' : '#fff', color: viewMode === 'grid' ? '#fff' : '#6b6a8a',
              fontSize: '12.5px', fontWeight: '600', cursor: 'pointer', fontFamily: 'Poppins, sans-serif',
              display: 'flex', alignItems: 'center', gap: '6px', transition: 'all 0.2s'
            }}
          >
            <Icon name="grid" size={14} />
            Grid View
          </button>
          <button
            onClick={() => setViewMode('table')}
            style={{
              padding: '8px 16px', borderRadius: '8px', border: viewMode === 'table' ? '2px solid #0b044d' : '1.5px solid #e4e3f0',
              background: viewMode === 'table' ? '#0b044d' : '#fff', color: viewMode === 'table' ? '#fff' : '#6b6a8a',
              fontSize: '12.5px', fontWeight: '600', cursor: 'pointer', fontFamily: 'Poppins, sans-serif',
              display: 'flex', alignItems: 'center', gap: '6px', transition: 'all 0.2s'
            }}
          >
            <Icon name="list" size={14} />
            List View
          </button>
        </div>
        <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
          <select className="filter-select" value={deptFilter} onChange={e => setDeptFilter(e.target.value)}>
            {departments.map(d => <option key={d}>{d}</option>)}
          </select>
          <select className="filter-select" value={statusFilter} onChange={e => setStatusFilter(e.target.value)}>
            <option value="All">All Status</option>
            <option>Open</option>
            <option>Closed</option>
          </select>
          <button className="btn-export">
            <Icon name="download" size={13} color="currentColor" />
            Export
          </button>
          <button className="modal-btn-primary" style={{ padding: '7px 16px', fontSize: 12.5 }} onClick={() => setAddOpen(true)}>
            <Icon name="plus" size={13} color="currentColor" />
            Post Job
          </button>
        </div>
      </div>

      {viewMode === 'grid' && (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(320px, 1fr))', gap: '16px', marginBottom: '22px' }}>
          {filtered.map(job => (
            <div key={job.id} className="stat-card" style={{ cursor: 'pointer', transition: 'all 0.2s', position: 'relative' }}>
              <div style={{ position: 'absolute', top: '16px', right: '16px' }}>
                <span className={`badge-status ${job.status === 'Open' ? 'processed' : 'on-hold'}`}>{job.status}</span>
              </div>
              <div style={{ marginBottom: '12px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '8px' }}>
                  <div style={{ width: '44px', height: '44px', borderRadius: '10px', background: 'linear-gradient(135deg, #15803d 0%, #22c55e 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontSize: '18px', fontWeight: '700' }}>
                    {job.slots}
                  </div>
                  <div style={{ flex: 1 }}>
                    <p style={{ fontSize: '10px', color: '#9999bb', fontWeight: '600', marginBottom: '2px' }}>{job.id}</p>
                    <h4 style={{ fontSize: '14px', fontWeight: '700', color: '#0b044d', margin: 0, lineHeight: '1.3' }}>{job.title}</h4>
                  </div>
                </div>
                <p style={{ fontSize: '12px', color: '#6b6a8a', marginBottom: '8px' }}>{job.dept}</p>
                <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap', marginBottom: '12px' }}>
                  <span className="badge-emptype">{job.type}</span>
                  <span style={{ fontSize: '11px', color: '#9999bb', background: '#f7f6ff', padding: '3px 10px', borderRadius: '20px', fontWeight: '600' }}>
                    {job.applicants} applicants
                  </span>
                </div>
              </div>
              <div style={{ borderTop: '1px solid #f0effe', paddingTop: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div>
                  <p style={{ fontSize: '10px', color: '#9999bb', marginBottom: '2px' }}>Deadline</p>
                  <p style={{ fontSize: '12px', color: '#0b044d', fontWeight: '600' }}>{job.deadline}</p>
                </div>
                <div style={{ display: 'flex', gap: '6px' }}>
                  <button className="btn-view" onClick={() => setViewJob(job)}>View</button>
                  <button className="btn-edit" onClick={() => setEditJob(job)}>Edit</button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {viewMode === 'table' && (
        <section className="table-section">
          <div className="table-header">
            <div>
              <h3 className="table-title">Job Postings</h3>
              <p className="table-sub">Municipal Government of Pagsanjan · {filtered.length} of {jobs.length} postings</p>
            </div>
          </div>

          <div className="table-wrapper">
            <table className="payroll-table">
              <thead>
                <tr>
                  <th>Job ID</th>
                  <th>Position</th>
                  <th>Department</th>
                  <th>Type</th>
                  <th>Slots</th>
                  <th>Applicants</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {filtered.length === 0 && (
                  <tr><td colSpan={9} style={{ textAlign: 'center', padding: 40, color: '#9999bb' }}>No job postings found.</td></tr>
                )}
                {filtered.map(job => (
                  <tr key={job.id}>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a', fontWeight: 500 }}>{job.id}</td>
                    <td className="position-cell">{job.title}</td>
                    <td><span className="dept-tag">{job.dept}</span></td>
                    <td><span className="badge-emptype">{job.type}</span></td>
                    <td style={{ fontSize: 13, color: '#6b6a8a', textAlign: 'center' }}>{job.slots}</td>
                    <td style={{ fontSize: 13, color: '#0b044d', fontWeight: 600, textAlign: 'center' }}>{job.applicants}</td>
                    <td style={{ fontSize: 12.5, color: '#6b6a8a', whiteSpace: 'nowrap' }}>{job.deadline}</td>
                    <td><span className={`badge-status ${job.status === 'Open' ? 'processed' : 'on-hold'}`}>{job.status}</span></td>
                    <td>
                      <div className="row-actions">
                        <button className="btn-view" onClick={() => setViewJob(job)}>View</button>
                        <button className="btn-edit" onClick={() => setEditJob(job)}>Edit</button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="table-footer">
            <p>Showing <strong>{filtered.length}</strong> of <strong>{jobs.length}</strong> postings</p>
            <div className="pagination">
              <button className="page-btn active">1</button>
              <button className="page-btn">›</button>
            </div>
          </div>
        </section>
      )}
    </div>
  );
}
