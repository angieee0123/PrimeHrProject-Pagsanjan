import { useState } from 'react';
import '../pages.css';

export default function Login({ onLogin, onBack, onSignup, onForgotPassword }) {
  const [show, setShow]         = useState(false);
  const [email, setEmail]       = useState('');
  const [password, setPassword] = useState('');
  const [error, setError]       = useState('');

  const handleSubmit = e => {
    e.preventDefault();
    setError('');
    const result = onLogin(email.trim().toLowerCase(), password);
    if (result === 'invalid') {
      setError('Invalid email or password. Please try again.');
    }
  };

  return (
    <div className="auth-root">

      {/* Gov Bar */}
      <div className="pub-govbar">
        <span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ display: 'inline-block', verticalAlign: 'middle', marginRight: 4 }}>
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
          </svg>
          Republic of the Philippines &nbsp;·&nbsp; Province of Laguna
        </span>
        <span>Official Website of the Municipal Government of Pagsanjan</span>
      </div>

      {/* Nav */}
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

      {/* Body */}
      <div className="auth-body">

        <div className="auth-page-head">
          <span className="pub-eyebrow">EMPLOYEE PORTAL · PRIME HRIS</span>
          <h1 className="auth-page-title">Sign in to your account</h1>
          <p className="auth-page-sub">Enter your email and password to access the portal.</p>
        </div>

        <div className="auth-card">
          <form className="auth-form" onSubmit={handleSubmit}>

            <div className="auth-field">
              <label>Email Address</label>
              <input
                type="email"
                placeholder="e.g. admin@gmail.com"
                value={email}
                onChange={e => { setEmail(e.target.value); setError(''); }}
                required
              />
            </div>

            <div className="auth-field">
              <div className="auth-field-row">
                <label>Password</label>
                <button type="button" className="auth-link-sm" onClick={onForgotPassword}>Forgot password?</button>
              </div>
              <div className="auth-pw-wrap">
                <input
                  type={show ? 'text' : 'password'}
                  placeholder="Enter your password"
                  value={password}
                  onChange={e => { setPassword(e.target.value); setError(''); }}
                  required
                />
                <button type="button" className="auth-eye" onClick={() => setShow(!show)}>
                  {show
                    ? <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    : <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  }
                </button>
              </div>
            </div>

            {error && (
              <div className="auth-error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {error}
              </div>
            )}

            <label className="auth-check">
              <input type="checkbox" />
              <span className="auth-check-box" />
              Keep me signed in on this device
            </label>

            <button type="submit" className="pub-hr-btn auth-submit">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
              </svg>
              Sign In
            </button>

          </form>

          <div className="auth-card-footer">
            <p className="auth-note">
              No account? Contact your <strong>System Administrator</strong> or the Human Resource Management Office.
            </p>
            <p className="auth-switch">
              Need to register?{' '}
              <button className="auth-switch-btn" onClick={onSignup}>Create an account</button>
            </p>
          </div>
        </div>

        <div className="auth-tags">
          <span className="pub-tag">✓ BIR Compliant</span>
          <span className="pub-tag">✓ GSIS Ready</span>
          <span className="pub-tag">✓ RA 10173 Compliant</span>
          <span className="pub-tag">✓ CSC Accredited</span>
        </div>

      </div>

      {/* Footer */}
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

    </div>
  );
}
