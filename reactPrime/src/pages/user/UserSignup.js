import { useState } from 'react';
import '../pages.css';

export default function Signup({ onLogin, onBack, onSubmitRequest }) {
  const [show, setShow]   = useState(false);
  const [showC, setShowC] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [form, setForm] = useState({
    firstName: '', lastName: '', employeeId: '', type: '', position: '', email: '', password: '', confirm: '',
  });

  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));

  const handleSubmit = e => {
    e.preventDefault();
    setError('');
    if (form.password !== form.confirm) {
      setError('Passwords do not match.');
      return;
    }
    if (form.password.length < 4) {
      setError('Password must be at least 4 characters.');
      return;
    }
    onSubmitRequest({
      id: Date.now(),
      firstName: form.firstName,
      lastName:  form.lastName,
      name:      `${form.firstName} ${form.lastName}`,
      employeeId: form.employeeId,
      type:      form.type,
      position:  form.position,
      email:     form.email.trim().toLowerCase(),
      password:  form.password,
      status:    'pending',
      date:      new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
    });
    setSuccess(true);
  };

  const GovBar = () => (
    <>
      <div className="pub-govbar">
        <span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ display: 'inline-block', verticalAlign: 'middle', marginRight: 4 }}>
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
          </svg>
          Republic of the Philippines &nbsp;·&nbsp; Province of Laguna
        </span>
        <span>Official Website of the Municipal Government of Pagsanjan</span>
      </div>
      <nav className="pub-nav">
        <div className="pub-logo">
          <div className="pub-logo-seal">
            <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" style={{ width: 36, height: 36, borderRadius: '50%', objectFit: 'cover' }} />
          </div>
          <div>
            <span className="pub-logo-name">Pagsanjan, Laguna</span>
            <span className="pub-logo-sub">Municipal Government</span>
          </div>
        </div>
        <button className="auth-nav-back" onClick={onBack}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
          </svg>
          Back to Portal
        </button>
      </nav>
    </>
  );

  const Footer = () => (
    <footer className="pub-footer auth-footer">
      <div className="pub-footer-inner">
        <div className="pub-footer-brand">
          <div className="pub-logo-seal sm">
            <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" style={{ width: 28, height: 28, borderRadius: '50%', objectFit: 'cover' }} />
          </div>
          <div>
            <span className="pub-footer-name">Municipal Government of Pagsanjan</span>
            <span className="pub-footer-sub">Province of Laguna · Republic of the Philippines</span>
          </div>
        </div>
        <p className="pub-footer-copy">© 2025 Municipal Government of Pagsanjan, Laguna. All rights reserved.</p>
      </div>
    </footer>
  );

  if (success) {
    return (
      <div className="auth-root">
        <GovBar />
        <div className="auth-body">
          <div className="auth-card" style={{ textAlign: 'center', borderTop: '3px solid #d9bb00', maxWidth: 460 }}>
            <div style={{ width: 60, height: 60, background: '#fefce8', border: '1.5px solid #d9bb00', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 18px' }}>
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#a16207" strokeWidth="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <h2 style={{ fontSize: 20, fontWeight: 800, color: '#0b044d', marginBottom: 8 }}>Request Submitted!</h2>
            <p style={{ fontSize: 13.5, color: '#6b6a8a', lineHeight: 1.75, marginBottom: 6 }}>
              Your account request has been sent to the <strong>HR Administrator</strong> for review.
            </p>
            <p style={{ fontSize: 13, color: '#9999bb', lineHeight: 1.7, marginBottom: 24 }}>
              You will be notified once your account is approved. This usually takes <strong>1–2 business days</strong>.
            </p>
            <div style={{ background: '#f7f6ff', border: '1px solid #eceaf8', borderRadius: 10, padding: '14px 18px', marginBottom: 24, textAlign: 'left' }}>
              <p style={{ fontSize: 11, fontWeight: 700, color: '#9999bb', textTransform: 'uppercase', letterSpacing: 1, marginBottom: 10 }}>Submitted Details</p>
              {[
                ['Name',  `${form.firstName} ${form.lastName}`],
                ['Email', form.email],
                ['Type',  form.type === 'employee' ? 'Permanent' : form.type === 'job-order' ? 'Job Order' : 'Admin / HR Staff'],
              ].map(([label, val]) => (
                <div key={label} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13, paddingBottom: 8, marginBottom: 8, borderBottom: '1px solid #f0effe' }}>
                  <span style={{ color: '#9999bb', fontWeight: 500 }}>{label}</span>
                  <strong style={{ color: '#0b044d' }}>{val}</strong>
                </div>
              ))}
            </div>
            <button className="pub-hr-btn auth-submit" onClick={onLogin} style={{ justifyContent: 'center' }}>
              Back to Sign In
            </button>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  return (
    <div className="auth-root">
      <GovBar />

      <div className="auth-body">
        <div className="auth-page-head">
          <span className="pub-eyebrow">EMPLOYEE PORTAL · PRIME HRIS</span>
          <h1 className="auth-page-title">Create an Account</h1>
          <p className="auth-page-sub">Fill in your details. Your request will be reviewed by the HR Administrator.</p>
        </div>

        <div className="auth-card auth-card-wide">
          <form className="auth-form" onSubmit={handleSubmit}>

            <div className="auth-row-2">
              <div className="auth-field">
                <label>First Name</label>
                <input type="text" placeholder="Maria" value={form.firstName} onChange={e => set('firstName', e.target.value)} required />
              </div>
              <div className="auth-field">
                <label>Last Name</label>
                <input type="text" placeholder="Santos" value={form.lastName} onChange={e => set('lastName', e.target.value)} required />
              </div>
            </div>

            <div className="auth-row-2">
              <div className="auth-field">
                <label>Employee / JO ID</label>
                <input type="text" placeholder="e.g. PGS-0001 or JO-0042" value={form.employeeId} onChange={e => set('employeeId', e.target.value)} required />
              </div>
              <div className="auth-field">
                <label>Employment Type</label>
                <select value={form.type} onChange={e => set('type', e.target.value)} required>
                  <option value="">Select type</option>
                  <option value="employee">Permanent</option>
                  <option value="job-order">Job Order</option>
                </select>
              </div>
            </div>

            <div className="auth-field">
              <label>Designation / Position</label>
              <input type="text" placeholder="e.g. Nurse II" value={form.position} onChange={e => set('position', e.target.value)} required />
            </div>

            <div className="auth-field">
              <label>Email Address</label>
              <input type="email" placeholder="your@email.com" value={form.email} onChange={e => set('email', e.target.value)} required />
            </div>

            <div className="auth-row-2">
              <div className="auth-field">
                <label>Password</label>
                <div className="auth-pw-wrap">
                  <input type={show ? 'text' : 'password'} placeholder="Create a password" value={form.password} onChange={e => set('password', e.target.value)} required />
                  <button type="button" className="auth-eye" onClick={() => setShow(!show)}>
                    {show
                      ? <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                      : <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    }
                  </button>
                </div>
              </div>
              <div className="auth-field">
                <label>Confirm Password</label>
                <div className="auth-pw-wrap">
                  <input type={showC ? 'text' : 'password'} placeholder="Repeat password" value={form.confirm} onChange={e => set('confirm', e.target.value)} required />
                  <button type="button" className="auth-eye" onClick={() => setShowC(!showC)}>
                    {showC
                      ? <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                      : <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    }
                  </button>
                </div>
              </div>
            </div>

            {error && (
              <div className="auth-error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {error}
              </div>
            )}

            <label className="auth-check">
              <input type="checkbox" required />
              <span className="auth-check-box" />
              I confirm that the information I provided is accurate and I am an employee of the Municipal Government of Pagsanjan, Laguna.
            </label>

            <button type="submit" className="pub-hr-btn auth-submit">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
              </svg>
              Submit Request
            </button>

          </form>

          <div className="auth-card-footer">
            <p className="auth-note">
              Your account will be active once approved by the <strong>HR Administrator</strong>.
            </p>
            <p className="auth-switch">
              Already have an account?{' '}
              <button className="auth-switch-btn" onClick={onLogin}>Sign in here</button>
            </p>
          </div>
        </div>

        <div className="auth-tags">
          <span className="pub-tag">✓ Pending Admin Approval</span>
          <span className="pub-tag">✓ RA 10173 Compliant</span>
          <span className="pub-tag">✓ Secure Registration</span>
        </div>
      </div>

      <Footer />
    </div>
  );
}
