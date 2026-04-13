import { useState, useRef, useEffect } from 'react';
import './App.css';
import Landing from './pages/user/UsersLandingPage';
import Login from './pages/user/UserLogin';
import Signup from './pages/user/UserSignup';
import ForgotPassword from './pages/user/UserForgotPassword';
import Personnel from './pages/admin/hr/AdminPersonnel';
import Payroll from './pages/admin/hr/AdminPayroll';
import Attendance from './pages/admin/hr/AdminAttendance';
import LeaveAndBenefits from './pages/admin/hr/AdminLeaveAndBenefits';
import Reports from './pages/admin/hr/AdminReports';
import Settings from './pages/admin/hr/AdminSettings';
import EmployeeDashboard from './pages/permanent/PermanentDashboard';
import MyPayslip from './pages/permanent/PermanentPayslip';
import MyAttendance from './pages/permanent/PermanentAttendance';
import JobOrderAttendance from './pages/joborder/JobOrderAttendance';
import MyLeaveAndBenefits from './pages/permanent/PermanentLeaveAndBenefits';
import EmployeeSettings from './pages/permanent/PermanentSettings';
import PermanentProfile from './pages/permanent/PermanentProfile';
import PermanentTraining from './pages/permanent/PermanentTraining';
import PermanentPerformance from './pages/permanent/PermanentPerformance';
import JobOrderSettings from './pages/joborder/JobOrderSettings';
import JobOrderProfile from './pages/joborder/JobOrderProfile';
import JobOrderTraining from './pages/joborder/JobOrderTraining';
import JobOrderPerformance from './pages/joborder/JobOrderPerformance';
import Departments from './pages/admin/hr/AdminDepartments';
import AdminDashboard from './pages/admin/hr/AdminDashboard';
import AdminRecruitment from './pages/admin/hr/AdminRecruitment';
import AdminTraining from './pages/admin/hr/AdminTraining';
import AdminPerformance from './pages/admin/hr/AdminPerformance';

const NavIcon = ({ id, role }) => {
  const s = { width: 17, height: 17, fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' };
  const icons = {
    dashboard:   <svg {...s} viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>,
    profile:     <svg {...s} viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>,
    recruitment: <svg {...s} viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>,
    personnel:   <svg {...s} viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>,
    training:    <svg {...s} viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>,
    performance: <svg {...s} viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>,
    attendance:  <svg {...s} viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>,
    leave:       <svg {...s} viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>,
    payroll:     <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" stroke="none"><text x="3" y="19" fontSize="17" fontWeight="bold" fontFamily="Arial, sans-serif">₱</text></svg>,
    departments: <svg {...s} viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>,
    reports:     <svg {...s} viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>,
    settings:    <svg {...s} viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>,
  };
  return icons[id] || null;
};

const adminNavItems = [
  { label: 'Dashboard',              id: 'dashboard' },
  { label: 'Recruitment',            id: 'recruitment' },
  { label: 'Personnel',              id: 'personnel' },
  { label: 'Training & Development', id: 'training' },
  { label: 'Performance Management', id: 'performance' },
  { label: 'Attendance',             id: 'attendance' },
  { label: 'Leave & Benefits',       id: 'leave' },
  { label: 'Payroll',                id: 'payroll' },
  { label: 'Departments',            id: 'departments' },
  { label: 'Reports',                id: 'reports' },
  { label: 'Settings',               id: 'settings' },
];

const employeeNavItems = [
  { label: 'Dashboard',        id: 'dashboard' },
  { label: 'My Profile',       id: 'profile' },
  { label: 'My Payslip',       id: 'payroll' },
  { label: 'My Attendance',    id: 'attendance' },
  { label: 'Leave & Benefits', id: 'leave' },
  { label: 'My Training',      id: 'training' },
  { label: 'My Performance',   id: 'performance' },
  { label: 'Settings',         id: 'settings' },
];

const jobOrderNavItems = [
  { label: 'Dashboard',      id: 'dashboard' },
  { label: 'My Profile',     id: 'profile' },
  { label: 'My Payslip',     id: 'payroll' },
  { label: 'My Attendance',  id: 'attendance' },
  { label: 'My Training',    id: 'training' },
  { label: 'My Performance', id: 'performance' },
  { label: 'Settings',       id: 'settings' },
];

const adminNotifications = [
  { id: 1, type: 'urgent',  title: 'Payroll Approval Required',    desc: '4 payroll records need your approval before Jun 30.',        time: '2 hrs ago',  read: false },
  { id: 2, type: 'warning', title: 'Leave Request Pending',         desc: 'Carlos M. Mendoza filed a 3-day sick leave request.',        time: '4 hrs ago',  read: false },
  { id: 3, type: 'info',    title: 'New Employee Onboarded',        desc: 'Roberto T. Flores (PGS-0310) completed onboarding.',         time: 'Yesterday',  read: false },
  { id: 4, type: 'warning', title: 'Incomplete DTR Records',        desc: '3 employees have missing DTR entries for June 2025.',        time: 'Yesterday',  read: false },
  { id: 5, type: 'info',    title: 'DTR Submission Deadline',       desc: 'June 2025 DTR submission closes on June 28 for all staff.',  time: '2 days ago', read: true  },
  { id: 6, type: 'info',    title: 'Payroll Period Opening',        desc: 'June 2025 payroll period is now open for processing.',       time: '3 days ago', read: true  },
];

const employeeNotifications = [
  { id: 1, type: 'info',    title: 'Jun 16–30 Payslip Ready',       desc: 'Your payslip for Jun 16–30, 2025 is now available. Net pay: ₱13,537.50.', time: '2 hrs ago',  read: false },
  { id: 2, type: 'warning', title: 'DTR Submission Deadline',        desc: 'Please submit your June 2025 DTR before June 28, 2025.',                  time: '5 hrs ago',  read: false },
  { id: 3, type: 'info',    title: 'Leave Request Approved',         desc: 'Your Vacation Leave (Jun 10–11, 2 days) has been approved.',               time: '3 days ago', read: false },
  { id: 4, type: 'warning', title: 'Pending Leave Application',      desc: 'Your Vacation Leave request (Jul 7–9) is awaiting approval.',              time: '4 days ago', read: true  },
  { id: 5, type: 'info',    title: 'Attendance Recorded',            desc: 'Your attendance for June 24, 2025 has been logged.',                      time: '5 days ago', read: true  },
];

const jobOrderNotifications = [
  { id: 1, type: 'info',    title: 'Jun 16–30 Payslip Ready',       desc: 'Your payslip for Jun 16–30, 2025 is now available. Net pay: ₱11,250.00.', time: '2 hrs ago',  read: false },
  { id: 2, type: 'warning', title: 'DTR Submission Deadline',        desc: 'Please submit your June 2025 DTR before June 28, 2025.',                  time: '5 hrs ago',  read: false },
  { id: 3, type: 'info',    title: 'Contract Renewal Notice',        desc: 'Your Job Order contract is due for renewal on July 1, 2025.',             time: '2 days ago', read: false },
  { id: 4, type: 'info',    title: 'Attendance Recorded',            desc: 'Your attendance for June 24, 2025 has been logged.',                      time: '3 days ago', read: true  },
  { id: 5, type: 'warning', title: 'Incomplete DTR Entry',           desc: 'Missing time-out entry detected for June 20, 2025.',                      time: '5 days ago', read: true  },
];


/* ── Process Payroll Modal ── */
function ProcessPayrollModal({ onClose, onProcess }) {
  const [step, setStep] = useState(1);
  const [period, setPeriod] = useState('2nd');
  const [month, setMonth] = useState('June');
  const [year, setYear] = useState('2025');
  const [processing, setProcessing] = useState(false);
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    const handler = e => { if (e.key === 'Escape' && !processing) onClose(); };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, [onClose, processing]);

  const handleProcess = () => {
    setProcessing(true);
    setProgress(0);
    const interval = setInterval(() => {
      setProgress(prev => {
        if (prev >= 100) {
          clearInterval(interval);
          setTimeout(() => {
            setProcessing(false);
            setStep(3);
          }, 500);
          return 100;
        }
        return prev + 10;
      });
    }, 300);
  };

  const periodLabel = period === '1st' ? '1–15' : '16–30';
  const payDate = period === '1st' ? `${month.slice(0, 3)} 15, ${year}` : `${month.slice(0, 3)} 30, ${year}`;

  return (
    <div className="modal-overlay" onClick={!processing ? onClose : undefined}>
      <div className="modal-box modal-lg" onClick={e => e.stopPropagation()} style={{ maxWidth: 600 }}>
        <div className="modal-header">
          <div>
            <span className="modal-eyebrow">PAYROLL PROCESSING</span>
            <h3 className="modal-title">
              {step === 1 && 'Select Payroll Period'}
              {step === 2 && 'Confirm & Process'}
              {step === 3 && 'Processing Complete'}
            </h3>
            <p className="modal-sub">
              {step === 1 && 'Choose the payroll period to process'}
              {step === 2 && `${month} ${periodLabel}, ${year} · Pay Date: ${payDate}`}
              {step === 3 && 'Payroll has been successfully processed'}
            </p>
          </div>
          {!processing && (
            <button className="modal-close" onClick={onClose}>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          )}
        </div>

        <div className="modal-body" style={{ minHeight: 280 }}>
          {step === 1 && (
            <div>
              <div className="form-grid">
                <div className="form-field">
                  <label>Pay Period</label>
                  <select value={period} onChange={e => setPeriod(e.target.value)} className="settings-select">
                    <option value="1st">1st Half (1–15)</option>
                    <option value="2nd">2nd Half (16–30)</option>
                  </select>
                </div>
                <div className="form-field">
                  <label>Month</label>
                  <select value={month} onChange={e => setMonth(e.target.value)} className="settings-select">
                    {MONTHS.map(m => <option key={m}>{m}</option>)}
                  </select>
                </div>
                <div className="form-field">
                  <label>Year</label>
                  <select value={year} onChange={e => setYear(e.target.value)} className="settings-select">
                    <option>2025</option>
                    <option>2024</option>
                    <option>2023</option>
                  </select>
                </div>
              </div>

              <div style={{ background: '#f7f6ff', borderRadius: 12, padding: '16px 20px', marginTop: 20 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                  <div style={{ width: 40, height: 40, borderRadius: 10, background: 'linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                  </div>
                  <div style={{ flex: 1 }}>
                    <p style={{ fontSize: 13, fontWeight: 700, color: '#0b044d', marginBottom: 2 }}>Selected Period</p>
                    <p style={{ fontSize: 12, color: '#6b6a8a' }}>{month} {periodLabel}, {year}</p>
                  </div>
                </div>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, paddingTop: 12, borderTop: '1px solid #eceaf8' }}>
                  <div>
                    <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4 }}>Pay Date</p>
                    <p style={{ fontSize: 13, fontWeight: 600, color: '#0b044d' }}>{payDate}</p>
                  </div>
                  <div>
                    <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4 }}>Employees</p>
                    <p style={{ fontSize: 13, fontWeight: 600, color: '#0b044d' }}>348 personnel</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {step === 2 && (
            <div>
              {!processing ? (
                <div>
                  <div style={{ background: '#fff5e6', border: '1.5px solid #fbbf24', borderRadius: 12, padding: '16px 20px', marginBottom: 20 }}>
                    <div style={{ display: 'flex', gap: 12 }}>
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" strokeWidth="2.5" style={{ flexShrink: 0, marginTop: 2 }}><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                      <div>
                        <p style={{ fontSize: 13, fontWeight: 700, color: '#a16207', marginBottom: 4 }}>Confirm Payroll Processing</p>
                        <p style={{ fontSize: 12, color: '#6b6a8a', lineHeight: 1.6 }}>You are about to process payroll for <strong>{month} {periodLabel}, {year}</strong>. This will calculate salaries, deductions, and generate payslips for all 348 employees. This action cannot be undone.</p>
                      </div>
                    </div>
                  </div>

                  <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 12 }}>
                    {[
                      { label: 'Total Employees', value: '348', icon: 'users', color: '#0b044d' },
                      { label: 'Gross Payroll', value: '₱2.4M', icon: 'dollarSign', color: '#15803d' },
                      { label: 'Total Deductions', value: '₱486K', icon: 'minus', color: '#8e1e18' },
                      { label: 'Net Payroll', value: '₱1.9M', icon: 'checkCircle', color: '#d9bb00' },
                    ].map((item, i) => (
                      <div key={i} style={{ background: '#f7f6ff', borderRadius: 10, padding: '14px 16px', display: 'flex', alignItems: 'center', gap: 12 }}>
                        <div style={{ width: 36, height: 36, borderRadius: 9, background: item.color + '18', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke={item.color} strokeWidth="2.5">
                            {item.icon === 'users' && <><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></>}
                            {item.icon === 'dollarSign' && <><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></>}
                            {item.icon === 'minus' && <line x1="5" y1="12" x2="19" y2="12"/>}
                            {item.icon === 'checkCircle' && <><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></>}
                          </svg>
                        </div>
                        <div>
                          <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 2 }}>{item.label}</p>
                          <p style={{ fontSize: 15, fontWeight: 800, color: item.color }}>{item.value}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ) : (
                <div style={{ textAlign: 'center', padding: '40px 20px' }}>
                  <div style={{ width: 80, height: 80, borderRadius: '50%', background: 'linear-gradient(135deg, #0b044d 0%, #2d1a8e 100%)', margin: '0 auto 20px', display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative' }}>
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" style={{ animation: 'spin 2s linear infinite' }}><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                  </div>
                  <h3 style={{ fontSize: 16, fontWeight: 700, color: '#0b044d', marginBottom: 8 }}>Processing Payroll...</h3>
                  <p style={{ fontSize: 13, color: '#6b6a8a', marginBottom: 20 }}>Calculating salaries and generating payslips</p>
                  <div style={{ width: '100%', height: 8, background: '#f0effe', borderRadius: 99, overflow: 'hidden' }}>
                    <div style={{ width: `${progress}%`, height: '100%', background: 'linear-gradient(90deg, #0b044d, #2d1a8e)', borderRadius: 99, transition: 'width 0.3s' }} />
                  </div>
                  <p style={{ fontSize: 12, color: '#9999bb', marginTop: 12 }}>{progress}% complete</p>
                </div>
              )}
            </div>
          )}

          {step === 3 && (
            <div style={{ textAlign: 'center', padding: '20px' }}>
              <div style={{ width: 80, height: 80, borderRadius: '50%', background: 'linear-gradient(135deg, #15803d 0%, #22c55e 100%)', margin: '0 auto 20px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="3"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <h3 style={{ fontSize: 18, fontWeight: 700, color: '#0b044d', marginBottom: 8 }}>Payroll Processed Successfully!</h3>
              <p style={{ fontSize: 13, color: '#6b6a8a', marginBottom: 24 }}>Payslips have been generated for all employees</p>
              
              <div style={{ background: '#f7f6ff', borderRadius: 12, padding: '20px', marginBottom: 20 }}>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 16 }}>
                  <div>
                    <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4 }}>Period</p>
                    <p style={{ fontSize: 14, fontWeight: 700, color: '#0b044d' }}>{month} {periodLabel}, {year}</p>
                  </div>
                  <div>
                    <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4 }}>Pay Date</p>
                    <p style={{ fontSize: 14, fontWeight: 700, color: '#0b044d' }}>{payDate}</p>
                  </div>
                  <div>
                    <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4 }}>Employees</p>
                    <p style={{ fontSize: 14, fontWeight: 700, color: '#15803d' }}>348 processed</p>
                  </div>
                  <div>
                    <p style={{ fontSize: 11, color: '#9999bb', marginBottom: 4 }}>Net Payroll</p>
                    <p style={{ fontSize: 14, fontWeight: 700, color: '#15803d' }}>₱1,914,300</p>
                  </div>
                </div>
              </div>

              <div style={{ display: 'flex', gap: 8, justifyContent: 'center' }}>
                <button className="modal-btn-ghost" onClick={onClose} style={{ flex: 1 }}>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  Download Report
                </button>
                <button className="modal-btn-primary" onClick={onClose} style={{ flex: 1 }}>Done</button>
              </div>
            </div>
          )}
        </div>

        {step < 3 && (
          <div className="modal-footer">
            {step === 1 && (
              <>
                <button className="modal-btn-ghost" onClick={onClose}>Cancel</button>
                <button className="modal-btn-primary" onClick={() => setStep(2)}>
                  Continue
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
              </>
            )}
            {step === 2 && !processing && (
              <>
                <button className="modal-btn-ghost" onClick={() => setStep(1)}>Back</button>
                <button className="modal-btn-primary" onClick={handleProcess} style={{ background: '#15803d' }}>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Process Payroll
                </button>
              </>
            )}
          </div>
        )}
      </div>

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}

/* ── Notifications Dropdown ── */
function NotifDropdown({ notifs, onClose, onMarkRead }) {
  const ref = useRef();
  useEffect(() => {
    const handler = e => { if (ref.current && !ref.current.contains(e.target)) onClose(); };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [onClose]);

  return (
    <div className="notif-dropdown" ref={ref}>
      <div className="notif-drop-header">
        <span className="notif-drop-title">Notifications</span>
        <button className="notif-mark-all" onClick={onMarkRead}>Mark all read</button>
      </div>
      <div className="notif-list">
        {notifs.map(n => (
          <div key={n.id} className={`notif-item ${n.read ? 'read' : ''} ${n.type}`}>
            <div className={`notif-dot ${n.type}`} />
            <div className="notif-content">
              <p className="notif-item-title">{n.title}</p>
              <p className="notif-item-desc">{n.desc}</p>
              <span className="notif-time">{n.time}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

/* ── Real-time clock ── */
function useNow() {
  const [now, setNow] = useState(new Date());
  useEffect(() => {
    const t = setInterval(() => setNow(new Date()), 60000);
    return () => clearInterval(t);
  }, []);
  return now;
}

const DAYS   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function formatTopbarDate(d) {
  return `${DAYS[d.getDay()]}, ${MONTHS[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()} · Fiscal Year ${d.getFullYear()}`;
}

/* ── Main App ── */
function App() {
  const now                         = useNow();
  const [view, setView]             = useState('landing');
  const [role, setRole]             = useState('admin');
  const [active, setActive]         = useState('dashboard');
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [showNotif, setShowNotif]   = useState(false);
  const [notifs, setNotifs]         = useState(adminNotifications);
  const [searchQuery, setSearchQuery] = useState('');
  const [pendingRegs, setPendingRegs]   = useState([]);
  const [chatOpen, setChatOpen]     = useState(false);
  const [chatMessages, setChatMessages] = useState([
    { from: 'bot', text: 'Hello! I\'m the PRIME HRIS Assistant. How can I help you today? You can ask me about payroll, attendance, leave management, or any HR-related queries.' }
  ]);
  const [chatInput, setChatInput]   = useState('');
  const [showProcessPayroll, setShowProcessPayroll] = useState(false);
  const [accounts, setAccounts]         = useState({
    'admin@gmail.com':     { role: 'admin',     password: 'pass' },
    'permanent@gmail.com': { role: 'employee',  password: 'pass' },
    'joborder@gmail.com':  { role: 'job-order', password: 'pass' },
  });

  useEffect(() => {
    setNotifs(role === 'admin' ? adminNotifications : role === 'job-order' ? jobOrderNotifications : employeeNotifications);
    setShowNotif(false);
    setSearchQuery('');
    setMobileMenuOpen(false);
    setChatOpen(false);
    setChatMessages([
      { from: 'bot', text: role === 'admin' 
        ? 'Hello! I\'m the PRIME HRIS Assistant. How can I help you today? You can ask me about payroll, attendance, leave management, or any HR-related queries.'
        : role === 'job-order'
        ? 'Hello! I\'m the PRIME HRIS Assistant. How can I help you today? You can ask me about your payslip, contract status, attendance records, or training programs.'
        : 'Hello! I\'m the PRIME HRIS Assistant. How can I help you today? You can ask me about your payslip, leave requests, attendance records, or any employee-related queries.'
      }
    ]);
  }, [role]);

  useEffect(() => {
    setSearchQuery('');
    setMobileMenuOpen(false);
  }, [active]);

  const unreadCount = notifs.filter(n => !n.read).length;

  const navItems = role === 'admin' ? adminNavItems : role === 'job-order' ? jobOrderNavItems : employeeNavItems;

  const handleLogin = (email, password) => {
    const account = accounts[email];
    if (!account || account.password !== password) return 'invalid';
    setRole(account.role);
    setActive('dashboard');
    setView('dashboard');
    return 'ok';
  };

  const handleApprove = id => {
    const reg = pendingRegs.find(r => r.id === id);
    if (!reg) return;
    setAccounts(prev => ({ ...prev, [reg.email]: { role: reg.type, password: reg.password } }));
    setPendingRegs(prev => prev.map(r => r.id === id ? { ...r, status: 'approved' } : r));
    setNotifs(prev => [{ id: Date.now(), type: 'info', title: 'Account Approved', desc: `${reg.name}'s account has been approved and is now active.`, time: 'Just now', read: false }, ...prev]);
  };

  const handleReject = id => {
    const reg = pendingRegs.find(r => r.id === id);
    setPendingRegs(prev => prev.map(r => r.id === id ? { ...r, status: 'rejected' } : r));
    if (reg) setNotifs(prev => [{ id: Date.now(), type: 'warning', title: 'Account Rejected', desc: `${reg.name}'s account request has been rejected.`, time: 'Just now', read: false }, ...prev]);
  };

  const handleSubmitRequest = reg => {
    setPendingRegs(prev => [...prev, reg]);
    if (role === 'admin') {
      setNotifs(prev => [{ id: Date.now(), type: 'warning', title: 'New Account Request', desc: `${reg.name} submitted a registration request.`, time: 'Just now', read: false }, ...prev]);
    }
  };

  const chatFaqs = role === 'admin' 
    ? [
        'How do I process payroll?',
        'How to approve leave requests?',
        'View employee attendance records',
        'Generate payroll reports',
      ]
    : role === 'job-order'
    ? [
        'How do I view my payslip?',
        'Check my contract status',
        'View my attendance record',
        'How to enroll in training?',
      ]
    : [
        'How do I view my payslip?',
        'How to file a leave request?',
        'Check my attendance record',
        'View my leave balance',
      ];

  const chatResponses = role === 'admin'
    ? {
        'How do I process payroll?': 'To process payroll: 1) Go to Payroll section, 2) Click "Process Payroll" button, 3) Review employee records, 4) Approve and generate payslips. The system automatically calculates deductions.',
        'How to approve leave requests?': 'Navigate to Leave & Benefits section. You\'ll see pending requests with employee details. Click "View" to review, then "Approve" or "Reject" with optional comments.',
        'View employee attendance records': 'Go to Attendance section. Use the search bar to find specific employees or filter by date range. You can export records for reporting purposes.',
        'Generate payroll reports': 'Visit the Reports section. Select "Payroll Reports", choose the period, and click "Generate". You can export to PDF or Excel format.',
      }
    : role === 'job-order'
    ? {
        'How do I view my payslip?': 'Go to "My Payslip" section from the navigation menu. You\'ll see your payslip history with daily rate calculations. Click "View" on any payslip to see detailed breakdown. You can download it as PDF.',
        'Check my contract status': 'Visit "My Profile" section. Your contract details including start date, end date, and days remaining are displayed prominently. You can also view your contract document.',
        'View my attendance record': 'Navigate to "My Attendance" section. You can view your daily time-in/time-out records and total days worked. This affects your payroll calculation based on daily rate.',
        'How to enroll in training?': 'Go to "My Training" section. Browse available training programs and click "Enroll" on programs you\'re interested in. You\'ll see enrollment status and progress for your trainings.',
      }
    : {
        'How do I view my payslip?': 'Go to "My Payslip" section from the navigation menu. You\'ll see your payslip history. Click "View" on any payslip to see detailed breakdown of earnings and deductions. You can also download it as PDF.',
        'How to file a leave request?': 'Navigate to "Leave & Benefits" section. Click "File Leave Request" button, select leave type, choose dates, provide reason, and submit. You\'ll receive notification once approved.',
        'Check my attendance record': 'Visit "My Attendance" section. You can view your daily time-in/time-out records, total hours worked, and attendance summary. Use filters to view specific date ranges.',
        'View my leave balance': 'Go to "Leave & Benefits" section. Your current leave balances (Vacation, Sick, Emergency) are displayed at the top. You can also see your leave history and pending requests.',
      };

  const sendChatMessage = (text) => {
    const userMsg = text || chatInput.trim();
    if (!userMsg) return;
    const reply = chatResponses[userMsg] || 'Thank you for your question. For detailed assistance, please refer to the user manual or contact the system administrator.';
    setChatMessages(prev => [...prev, { from: 'user', text: userMsg }, { from: 'bot', text: reply }]);
    setChatInput('');
  };

  if (view === 'landing') return <Landing onLogin={() => setView('login')} />;
  if (view === 'login')   return <Login   onLogin={handleLogin} onBack={() => setView('landing')} onSignup={() => setView('signup')} onForgotPassword={() => setView('forgot-password')} />;
  if (view === 'signup')  return <Signup  onLogin={() => setView('login')} onBack={() => setView('landing')} onSubmitRequest={handleSubmitRequest} />;
  if (view === 'forgot-password') return <ForgotPassword onBack={() => setView('landing')} onBackToLogin={() => setView('login')} />;

  return (
    <div className="app-layout">

      {/* Mobile Menu Button */}
      <button 
        className="mobile-menu-btn" 
        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
        aria-label="Toggle menu"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
          {mobileMenuOpen ? (
            <>
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </>
          ) : (
            <>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </>
          )}
        </svg>
      </button>

      {/* Mobile Overlay */}
      <div 
        className={`mobile-overlay ${mobileMenuOpen ? 'active' : ''}`}
        onClick={() => setMobileMenuOpen(false)}
      />

      {/* ── Sidebar ── */}
      <aside className={`sidebar ${sidebarOpen ? '' : 'collapsed'} ${mobileMenuOpen ? 'mobile-open' : ''}`}>
        <div className="sidebar-header">
          <div className="logo">
            <div className="logo-mark">
              <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" style={{ width: 32, height: 32, borderRadius: '50%', objectFit: 'cover' }} />
            </div>
            {sidebarOpen && (
              <div className="logo-text-wrap">
                <span className="logo-text">PRIME HRIS</span>
                <span className="logo-sub">Pagsanjan, Laguna</span>
              </div>
            )}
          </div>
          <button className="toggle-btn" onClick={() => setSidebarOpen(!sidebarOpen)}>
            {sidebarOpen ? '‹' : '›'}
          </button>
        </div>

        {sidebarOpen && <p className="nav-section-label">NAVIGATION</p>}

        <nav className="sidebar-nav">
          {navItems.map(item => (
            <button
              key={item.id}
              className={`nav-item ${active === item.id ? 'active' : ''}`}
              onClick={() => {
                setActive(item.id);
                setMobileMenuOpen(false);
              }}
              title={!sidebarOpen ? item.label : ''}
            >
              <span className="nav-icon"><NavIcon id={item.id} role={role} /></span>
              {sidebarOpen && <span className="nav-label">{item.label}</span>}
              {sidebarOpen && active === item.id && <span className="nav-active-bar" />}
            </button>
          ))}
        </nav>

        <div className={`sidebar-footer ${!sidebarOpen ? 'collapsed-footer' : ''}`}>
          <div className="user-avatar-wrap">
            <div className="user-avatar" style={{ background: role === 'employee' ? '#8e1e18' : role === 'job-order' ? '#1a6e3c' : undefined }}>
              {role === 'employee' ? 'AR' : role === 'job-order' ? 'JO' : 'AD'}
            </div>
            <span className="user-status-dot" />
          </div>
          {sidebarOpen && (
            <div className="user-info">
              <p className="user-name">{role === 'employee' ? 'Ana R. Reyes' : role === 'job-order' ? 'Juan D. Cruz' : 'Admin User'}</p>
              <p className="user-role">{role === 'employee' ? 'Permanent · PGS-0115' : role === 'job-order' ? 'Job Order · JO-0042' : 'HR Staff'}</p>
            </div>
          )}
          {sidebarOpen && (
            <button className="logout-btn" onClick={() => setView('landing')} title="Logout">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
          )}
        </div>
      </aside>

      {/* ── Main ── */}
      <main className="main-content">

        {/* Topbar */}
        <header className="topbar">
          <div className="topbar-left">
            <h1 className="page-title">{navItems.find(n => n.id === active)?.label}</h1>
            <p className="page-subtitle">{formatTopbarDate(now)}</p>
          </div>
          <div className="topbar-right">
{!['dashboard','settings','reports','profile','training','performance'].includes(active) && (
              <div className="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9999bb" strokeWidth="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input
                  type="text"
                  placeholder={
                    (role === 'employee' || role === 'job-order')
                      ? active === 'payroll'    ? 'Search by period or status...'
                      : active === 'attendance' ? 'Search by date or status...'
                      : active === 'leave'      ? 'Search by type, reason, status...'
                      : 'Search...'
                      : active === 'personnel'    ? 'Search name, ID, position...'
                      : active === 'payroll'      ? 'Search name or ID...'
                      : active === 'attendance'   ? 'Search name or ID...'
                      : active === 'leave'        ? 'Search name or ID...'
                      : active === 'departments'  ? 'Search department or code...'
                      : 'Search...'
                  }
                  value={searchQuery}
                  onChange={e => setSearchQuery(e.target.value)}
                />
              </div>
            )}

            {/* Notification Bell */}
            <div style={{ position: 'relative' }}>
              <button className="icon-btn" title="Notifications" onClick={() => setShowNotif(v => !v)}>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                {unreadCount > 0 && <span className="notif-badge">{unreadCount}</span>}
              </button>
              {showNotif && (
                <NotifDropdown
                  notifs={notifs}
                  onClose={() => setShowNotif(false)}
                  onMarkRead={() => setNotifs(notifs.map(n => ({ ...n, read: true })))}
                />
              )}
            </div>

            {role === 'admin' && (active === 'dashboard' || active === 'payroll') && (
              <button className="btn-run-payroll" onClick={() => setShowProcessPayroll(true)}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Process Payroll
              </button>
            )}
          </div>
        </header>

        {active === 'dashboard' && (role === 'employee' || role === 'job-order') && <EmployeeDashboard role={role} notifs={notifs} setNotifs={setNotifs} />}
        {active === 'dashboard' && role === 'admin' && <AdminDashboard />}

        {/* ── My Profile ── */}
        {active === 'profile' && role === 'employee' && <PermanentProfile />}
        {active === 'profile' && role === 'job-order' && <JobOrderProfile />}

        {/* ── My Training ── */}
        {active === 'training' && role === 'employee' && <PermanentTraining />}
        {active === 'training' && role === 'job-order' && <JobOrderTraining />}

        {/* ── My Performance ── */}
        {active === 'performance' && role === 'employee' && <PermanentPerformance />}
        {active === 'performance' && role === 'job-order' && <JobOrderPerformance />}

        {/* ── Personnel ── */}
        {active === 'personnel' && <Personnel searchQuery={searchQuery} />}

        {/* ── Payroll ── */}
        {active === 'payroll' && role === 'admin' && <Payroll searchQuery={searchQuery} />}
        {active === 'payroll' && (role === 'employee' || role === 'job-order') && <MyPayslip role={role} searchQuery={searchQuery} />}

        {/* ── Attendance ── */}
        {active === 'attendance' && role === 'admin'    && <Attendance searchQuery={searchQuery} />}
        {active === 'attendance' && role === 'employee' && <MyAttendance role={role} searchQuery={searchQuery} />}
        {active === 'attendance' && role === 'job-order' && <JobOrderAttendance searchQuery={searchQuery} />}

        {/* ── Leave & Benefits ── */}
        {active === 'leave' && role === 'admin'    && <LeaveAndBenefits searchQuery={searchQuery} />}
        {active === 'leave' && role === 'employee' && <MyLeaveAndBenefits searchQuery={searchQuery} />}
        {active === 'leave' && role === 'job-order' && (
          <div className="placeholder-section">
            <div style={{ marginBottom: 12 }}>
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9999bb" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
            </div>
            <h2 className="placeholder-title">Not Available</h2>
            <p className="placeholder-desc">Leave & Benefits are not applicable for Job Order personnel.</p>
          </div>
        )}

        {/* ── Departments ── */}
        {active === 'departments' && role === 'admin' && <Departments searchQuery={searchQuery} />}

        {/* ── Reports ── */}
        {active === 'reports' && <Reports />}

        {/* ── Settings ── */}
        {active === 'settings' && role === 'admin'    && <Settings requests={pendingRegs} onApprove={handleApprove} onReject={handleReject} />}
        {active === 'settings' && role === 'employee' && <EmployeeSettings />}
        {active === 'settings' && role === 'job-order' && <JobOrderSettings />}

        {/* ── Recruitment / Training / Performance (placeholder) ── */}
        {active === 'recruitment' && role === 'admin' && <AdminRecruitment searchQuery={searchQuery} />}
        {active === 'training' && role === 'admin' && <AdminTraining searchQuery={searchQuery} />}
        {active === 'performance' && role === 'admin' && <AdminPerformance searchQuery={searchQuery} />}

        {/* ── Other Sections (placeholder) ── */}
        {active !== 'dashboard' && active !== 'recruitment' && active !== 'personnel' && active !== 'training' && active !== 'performance' && active !== 'payroll' && active !== 'attendance' && active !== 'leave' && active !== 'departments' && active !== 'reports' && active !== 'settings' && (
          <div className="placeholder-section">
            <div className="placeholder-icon">{navItems.find(n => n.id === active)?.icon}</div>
            <h2 className="placeholder-title">{navItems.find(n => n.id === active)?.label}</h2>
            <p className="placeholder-desc">This module is under development for the prototype.</p>
          </div>
        )}

      </main>

      {/* ── AI Chatbot (Admin Only) ── */}
      {role === 'admin' && (
        <>
          <button 
            className={`chat-fab ${chatOpen ? 'open' : ''}`} 
            onClick={() => setChatOpen(!chatOpen)} 
            title="HRIS Assistant"
            style={{ position: 'fixed', bottom: '28px', right: '28px', zIndex: 999 }}
          >
            {chatOpen
              ? <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              : <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            }
            {!chatOpen && <span className="chat-fab-badge">AI</span>}
          </button>

          {chatOpen && (
            <div className="chatbot-window" style={{ position: 'fixed', bottom: '96px', right: '28px', zIndex: 998 }}>
              <div className="chatbot-header">
                <div className="chatbot-header-left">
                  <div className="chatbot-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  </div>
                  <div>
                    <p className="chatbot-name">PRIME HRIS Assistant</p>
                    <p className="chatbot-status">● Online</p>
                  </div>
                </div>
                <button className="chatbot-close" onClick={() => setChatOpen(false)}>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </div>

              <div className="chatbot-messages">
                {chatMessages.map((m, i) => (
                  <div key={i} className={`chat-msg ${m.from}`}>
                    {m.from === 'bot' && (
                      <div className="chat-msg-avatar">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      </div>
                    )}
                    <div className="chat-msg-bubble">{m.text}</div>
                  </div>
                ))}
              </div>

              <div className="chatbot-faqs">
                {chatFaqs.map((q, i) => (
                  <button key={i} className="chatbot-faq-btn" onClick={() => sendChatMessage(q)}>{q}</button>
                ))}
              </div>

              <div className="chatbot-input-row">
                <input
                  type="text"
                  placeholder="Type your question..."
                  value={chatInput}
                  onChange={e => setChatInput(e.target.value)}
                  onKeyDown={e => e.key === 'Enter' && sendChatMessage()}
                />
                <button className="chatbot-send" onClick={() => sendChatMessage()}>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
              </div>
            </div>
          )}
        </>
      )}

      {/* ── AI Chatbot (Permanent Employee) ── */}
      {role === 'employee' && (
        <>
          <button 
            className={`chat-fab ${chatOpen ? 'open' : ''}`} 
            onClick={() => setChatOpen(!chatOpen)} 
            title="HRIS Assistant"
            style={{ position: 'fixed', bottom: '28px', right: '28px', zIndex: 999 }}
          >
            {chatOpen
              ? <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              : <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            }
            {!chatOpen && <span className="chat-fab-badge">AI</span>}
          </button>

          {chatOpen && (
            <div className="chatbot-window" style={{ position: 'fixed', bottom: '96px', right: '28px', zIndex: 998 }}>
              <div className="chatbot-header">
                <div className="chatbot-header-left">
                  <div className="chatbot-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  </div>
                  <div>
                    <p className="chatbot-name">PRIME HRIS Assistant</p>
                    <p className="chatbot-status">● Online</p>
                  </div>
                </div>
                <button className="chatbot-close" onClick={() => setChatOpen(false)}>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </div>

              <div className="chatbot-messages">
                {chatMessages.map((m, i) => (
                  <div key={i} className={`chat-msg ${m.from}`}>
                    {m.from === 'bot' && (
                      <div className="chat-msg-avatar">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      </div>
                    )}
                    <div className="chat-msg-bubble">{m.text}</div>
                  </div>
                ))}
              </div>

              <div className="chatbot-faqs">
                {chatFaqs.map((q, i) => (
                  <button key={i} className="chatbot-faq-btn" onClick={() => sendChatMessage(q)}>{q}</button>
                ))}
              </div>

              <div className="chatbot-input-row">
                <input
                  type="text"
                  placeholder="Type your question..."
                  value={chatInput}
                  onChange={e => setChatInput(e.target.value)}
                  onKeyDown={e => e.key === 'Enter' && sendChatMessage()}
                />
                <button className="chatbot-send" onClick={() => sendChatMessage()}>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
              </div>
            </div>
          )}
        </>
      )}

      {/* ── AI Chatbot (Job Order) ── */}
      {role === 'job-order' && (
        <>
          <button 
            className={`chat-fab ${chatOpen ? 'open' : ''}`} 
            onClick={() => setChatOpen(!chatOpen)} 
            title="HRIS Assistant"
            style={{ position: 'fixed', bottom: '28px', right: '28px', zIndex: 999 }}
          >
            {chatOpen
              ? <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              : <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            }
            {!chatOpen && <span className="chat-fab-badge">AI</span>}
          </button>

          {chatOpen && (
            <div className="chatbot-window" style={{ position: 'fixed', bottom: '96px', right: '28px', zIndex: 998 }}>
              <div className="chatbot-header">
                <div className="chatbot-header-left">
                  <div className="chatbot-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  </div>
                  <div>
                    <p className="chatbot-name">PRIME HRIS Assistant</p>
                    <p className="chatbot-status">● Online</p>
                  </div>
                </div>
                <button className="chatbot-close" onClick={() => setChatOpen(false)}>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </div>

              <div className="chatbot-messages">
                {chatMessages.map((m, i) => (
                  <div key={i} className={`chat-msg ${m.from}`}>
                    {m.from === 'bot' && (
                      <div className="chat-msg-avatar">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      </div>
                    )}
                    <div className="chat-msg-bubble">{m.text}</div>
                  </div>
                ))}
              </div>

              <div className="chatbot-faqs">
                {chatFaqs.map((q, i) => (
                  <button key={i} className="chatbot-faq-btn" onClick={() => sendChatMessage(q)}>{q}</button>
                ))}
              </div>

              <div className="chatbot-input-row">
                <input
                  type="text"
                  placeholder="Type your question..."
                  value={chatInput}
                  onChange={e => setChatInput(e.target.value)}
                  onKeyDown={e => e.key === 'Enter' && sendChatMessage()}
                />
                <button className="chatbot-send" onClick={() => sendChatMessage()}>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
              </div>
            </div>
          )}
        </>
      )}

      {/* ── Process Payroll Modal ── */}
      {showProcessPayroll && (
        <ProcessPayrollModal
          onClose={() => setShowProcessPayroll(false)}
          onProcess={() => {
            setShowProcessPayroll(false);
            setNotifs(prev => [{ id: Date.now(), type: 'info', title: 'Payroll Processed', desc: 'Payroll has been successfully processed for all employees.', time: 'Just now', read: false }, ...prev]);
          }}
        />
      )}
    </div>
  );
}

export default App;
