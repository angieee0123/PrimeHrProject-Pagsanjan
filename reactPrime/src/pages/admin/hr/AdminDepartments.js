import { useState, useEffect } from 'react';
import { Icon } from '../../../components/Icons';

const departments = [
  { code: 'OM',     name: 'Office of the Mayor',                       head: 'Hon. Mayor',               personnel: 42, status: 'Active' },
  { code: 'OVM',    name: 'Office of the Vice Mayor',                  head: 'Hon. Vice Mayor',          personnel: 18, status: 'Active' },
  { code: 'SB',     name: 'Sangguniang Bayan',                         head: 'SB Secretary',             personnel: 24, status: 'Active' },
  { code: 'MTO',    name: 'Office of the Municipal Treasurer',         head: 'Municipal Treasurer',      personnel: 31, status: 'Active' },
  { code: 'MAO',    name: "Municipal Assessor's Office",               head: 'Municipal Assessor',       personnel: 14, status: 'Active' },
  { code: 'MCR',    name: 'Municipal Civil Registrar',                 head: 'Civil Registrar',          personnel: 12, status: 'Active' },
  { code: 'MHO',    name: 'Municipal Health Office',                   head: 'Municipal Health Officer', personnel: 38, status: 'Active' },
  { code: 'MSWD',   name: 'MSWD – Pagsanjan',                          head: 'MSWD Officer',             personnel: 27, status: 'Active' },
  { code: 'MPDO',   name: "Municipal Planning & Dev't Office",         head: 'MPDO Officer',             personnel: 16, status: 'Active' },
  { code: 'MEO',    name: 'Office of the Mun. Engineer',               head: 'Municipal Engineer',       personnel: 22, status: 'Active' },
  { code: 'MAGO',   name: 'Office of the Mun. Agriculturist',          head: 'Municipal Agriculturist',  personnel: 19, status: 'Active' },
  { code: 'MENRO',  name: 'Municipal Environment & Natural Resources', head: 'MENRO Officer',            personnel: 11, status: 'Active' },
  { code: 'MBDO',   name: "Municipal Business & Dev't Office",         head: 'MBDO Officer',             personnel: 9,  status: 'Active' },
  { code: 'HRMO',   name: 'Human Resource Management Office',          head: 'HRMO Officer',             personnel: 8,  status: 'Active' },
  { code: 'MDRRMO', name: 'Municipal Disaster Risk Reduction & Mgmt',  head: 'MDRRMO Officer',           personnel: 15, status: 'Active' },
  { code: 'MBO',    name: 'Office of the Mun. Budget',                 head: 'Municipal Budget Officer', personnel: 7,  status: 'Active' },
  { code: 'MCTC',   name: 'Municipal Circuit Trial Court',             head: 'Presiding Judge',          personnel: 6,  status: 'Active' },
];

const avatarColors = ['#0b044d','#8e1e18','#1a0f6e','#5a0f0b','#2d1a8e','#0b044d','#3b1a6e','#6b0f0b'];

export default function Departments({ searchQuery = '' }) {
  const [selected, setSelected] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 10;

  const filtered = departments.filter(d =>
    d.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    d.code.toLowerCase().includes(searchQuery.toLowerCase()) ||
    d.head.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const totalPages = Math.ceil(filtered.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentItems = filtered.slice(startIndex, endIndex);

  useEffect(() => { setCurrentPage(1); }, [searchQuery]);

  const totalPersonnel = departments.reduce((s, d) => s + d.personnel, 0);
  const largestDept = departments.reduce((a, b) => a.personnel > b.personnel ? a : b);

  return (
    <div className="page-content">
      {/* Stats */}
      <div className="stats-grid" style={{ marginBottom: 20 }}>
        {[
          { label: 'Total Departments', value: departments.length,                                    sub: 'All offices',           accent: '#0b044d', icon: 'building' },
          { label: 'Total Personnel',   value: totalPersonnel,                                        sub: 'Across all offices',    accent: '#15803d', icon: 'users' },
          { label: 'Active Offices',    value: departments.filter(d => d.status === 'Active').length, sub: 'Operational units',      accent: '#d9bb00', icon: 'checkCircle' },
          { label: 'Largest Office',    value: largestDept.personnel,                                 sub: largestDept.code,        accent: '#8e1e18', icon: 'trendingUp' },
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

      {/* Table */}
      <section className="table-section">
        <div className="table-header">
          <div>
            <h3 className="table-title">Departments & Offices</h3>
            <p className="table-sub">Municipal Government of Pagsanjan · Province of Laguna · {filtered.length} offices</p>
          </div>
          <div className="table-actions">
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
                <th>Department / Office</th>
                <th>Code</th>
                <th>Department Head</th>
                <th>Personnel</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {currentItems.map((dept, i) => (
                <tr key={dept.code}>
                  <td>
                    <div className="emp-cell">
                      <div className="emp-avatar" style={{ background: avatarColors[(startIndex + i) % avatarColors.length], fontSize: 11 }}>
                        {dept.code.slice(0, 2)}
                      </div>
                      <p className="emp-name">{dept.name}</p>
                    </div>
                  </td>
                  <td><span className="dept-tag">{dept.code}</span></td>
                  <td className="position-cell">{dept.head}</td>
                  <td className="pay-cell">{dept.personnel}</td>
                  <td><span className="badge-status processed">{dept.status}</span></td>
                  <td>
                    <button className="btn-view" onClick={() => setSelected(dept)}>View</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="table-footer">
          <p>Showing <strong>{startIndex + 1}-{Math.min(endIndex, filtered.length)}</strong> of <strong>{filtered.length}</strong> offices</p>
          <div className="pagination">
            <button 
              className="page-btn" 
              onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
              disabled={currentPage === 1}
              style={{ opacity: currentPage === 1 ? 0.5 : 1, cursor: currentPage === 1 ? 'not-allowed' : 'pointer' }}
            >
              ‹
            </button>
            {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
              <button
                key={page}
                className={`page-btn ${currentPage === page ? 'active' : ''}`}
                onClick={() => setCurrentPage(page)}
              >
                {page}
              </button>
            ))}
            <button 
              className="page-btn" 
              onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
              disabled={currentPage === totalPages}
              style={{ opacity: currentPage === totalPages ? 0.5 : 1, cursor: currentPage === totalPages ? 'not-allowed' : 'pointer' }}
            >
              ›
            </button>
          </div>
        </div>
      </section>

      {/* Detail Modal */}
      {selected && (
        <div className="modal-overlay" onClick={() => setSelected(null)}>
          <div className="modal-box" onClick={e => e.stopPropagation()}>
            <div className="modal-header">
              <div className="pmodal-hero">
                <div className="emp-avatar xl" style={{ background: avatarColors[departments.indexOf(selected) % avatarColors.length], fontSize: 13 }}>
                  {selected.code.slice(0, 2)}
                </div>
                <div>
                  <span className="modal-eyebrow">DEPARTMENT DETAIL · {selected.code}</span>
                  <h3 className="modal-title">{selected.name}</h3>
                  <p className="modal-sub">Municipal Government of Pagsanjan</p>
                  <div className="pmodal-badges">
                    <span className="badge-status processed">{selected.status}</span>
                    <span className="badge-emptype">{selected.personnel} Personnel</span>
                  </div>
                </div>
              </div>
              <button className="modal-close" onClick={() => setSelected(null)}>
                <Icon name="x" size={16} />
              </button>
            </div>
            <div className="modal-body">
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: '12px', marginBottom: '16px' }}>
                <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                  <div style={{ width: '38px', height: '38px', borderRadius: '10px', background: 'linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                    <Icon name="building" size={16} color="#fff" />
                  </div>
                  <div>
                    <p style={{ fontSize: '11px', color: '#9999bb', marginBottom: '2px' }}>Office Code</p>
                    <p style={{ fontSize: '15px', fontWeight: '800', color: '#0b044d' }}>{selected.code}</p>
                  </div>
                </div>
                <div style={{ background: '#f7f6ff', borderRadius: '10px', padding: '14px 16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                  <div style={{ width: '38px', height: '38px', borderRadius: '10px', background: 'linear-gradient(135deg, #15803d 0%, #22c55e 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                    <Icon name="users" size={16} color="#fff" />
                  </div>
                  <div>
                    <p style={{ fontSize: '11px', color: '#9999bb', marginBottom: '2px' }}>Total Personnel</p>
                    <p style={{ fontSize: '15px', fontWeight: '800', color: '#15803d' }}>{selected.personnel}</p>
                  </div>
                </div>
              </div>
              <div className="modal-section-label">OFFICE INFORMATION</div>
              <div className="modal-row"><span>Department Head</span><strong>{selected.head}</strong></div>
              <div className="modal-row"><span>Status</span><span className="badge-status processed">{selected.status}</span></div>
            </div>
            <div className="modal-footer">
              <button className="modal-btn-ghost" onClick={() => setSelected(null)}>Close</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
