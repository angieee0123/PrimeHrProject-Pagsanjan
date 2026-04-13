import { useState } from 'react';
import { Icon } from '../../components/Icons';
import '../pages.css';

export default function UserForgotPassword({ onBack, onBackToLogin }) {
  const [step, setStep] = useState(1);
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showNew, setShowNew] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleSendCode = e => {
    e.preventDefault();
    setError('');
    if (!email.trim()) {
      setError('Please enter your email address.');
      return;
    }
    setSuccess('Verification code sent to your email!');
    setTimeout(() => {
      setSuccess('');
      setStep(2);
    }, 1500);
  };

  const handleVerifyCode = e => {
    e.preventDefault();
    setError('');
    if (!code.trim()) {
      setError('Please enter the verification code.');
      return;
    }
    if (code !== '123456') {
      setError('Invalid verification code. Please try again.');
      return;
    }
    setStep(3);
  };

  const handleResetPassword = e => {
    e.preventDefault();
    setError('');
    if (!newPassword || !confirmPassword) {
      setError('Please fill in all password fields.');
      return;
    }
    if (newPassword.length < 8) {
      setError('Password must be at least 8 characters long.');
      return;
    }
    if (newPassword !== confirmPassword) {
      setError('Passwords do not match.');
      return;
    }
    setSuccess('Password reset successfully!');
    setTimeout(() => {
      onBackToLogin();
    }, 2000);
  };

  const handleResendCode = () => {
    setSuccess('Verification code resent to your email!');
    setTimeout(() => setSuccess(''), 2000);
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
          <span className="pub-eyebrow">EMPLOYEE PORTAL · PASSWORD RECOVERY</span>
          <h1 className="auth-page-title">
            {step === 1 && 'Reset your password'}
            {step === 2 && 'Verify your email'}
            {step === 3 && 'Create new password'}
          </h1>
          <p className="auth-page-sub">
            {step === 1 && 'Enter your email address and we\'ll send you a verification code.'}
            {step === 2 && 'Enter the 6-digit code we sent to your email address.'}
            {step === 3 && 'Choose a strong password to secure your account.'}
          </p>
        </div>

        {/* Step Progress */}
        <div style={{ maxWidth: 480, margin: '0 auto 32px', display: 'flex', alignItems: 'center', gap: '12px' }}>
          {[1, 2, 3].map(s => (
            <div key={s} style={{ flex: 1, display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div style={{ 
                width: 32, 
                height: 32, 
                borderRadius: '50%', 
                background: step >= s ? 'linear-gradient(135deg, #0b044d 0%, #1a0f7a 100%)' : '#e5e4f5',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: step >= s ? '#fff' : '#9999bb',
                fontSize: 13,
                fontWeight: 700,
                flexShrink: 0,
                transition: 'all 0.3s'
              }}>
                {step > s ? <Icon name="checkCircle" size={16} color="#fff" /> : s}
              </div>
              {s < 3 && (
                <div style={{ 
                  flex: 1, 
                  height: 3, 
                  background: step > s ? 'linear-gradient(90deg, #0b044d 0%, #1a0f7a 100%)' : '#e5e4f5',
                  borderRadius: 99,
                  transition: 'all 0.3s'
                }} />
              )}
            </div>
          ))}
        </div>

        <div className="auth-card">

          {/* Step 1: Email */}
          {step === 1 && (
            <form className="auth-form" onSubmit={handleSendCode}>
              <div className="auth-field">
                <label>Email Address</label>
                <div style={{ position: 'relative' }}>
                  <input
                    type="email"
                    placeholder="e.g. juan.cruz@pagsanjan.gov.ph"
                    value={email}
                    onChange={e => { setEmail(e.target.value); setError(''); }}
                    required
                    style={{ paddingLeft: 40 }}
                  />
                  <div style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }}>
                    <Icon name="mail" size={16} color="#9999bb" />
                  </div>
                </div>
              </div>

              {error && (
                <div className="auth-error">
                  <Icon name="info" size={14} color="#8e1e18" />
                  {error}
                </div>
              )}

              {success && (
                <div style={{ background: '#f0fdf4', border: '1.5px solid #bbf7d0', borderRadius: 10, padding: '12px 16px', display: 'flex', alignItems: 'center', gap: '10px', marginBottom: 16 }}>
                  <Icon name="checkCircle" size={16} color="#15803d" />
                  <span style={{ fontSize: 13, color: '#15803d', fontWeight: 600 }}>{success}</span>
                </div>
              )}

              <button type="submit" className="pub-hr-btn auth-submit">
                <Icon name="mail" size={15} color="#fff" />
                Send Verification Code
              </button>

              <div className="auth-card-footer">
                <p className="auth-switch">
                  Remember your password?{' '}
                  <button type="button" className="auth-switch-btn" onClick={onBackToLogin}>Back to Sign In</button>
                </p>
              </div>
            </form>
          )}

          {/* Step 2: Verification Code */}
          {step === 2 && (
            <form className="auth-form" onSubmit={handleVerifyCode}>
              <div style={{ background: '#f7f6ff', borderRadius: 12, padding: '16px 18px', marginBottom: 20, display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                <Icon name="mail" size={18} color="#0b044d" />
                <div>
                  <p style={{ fontSize: 12.5, color: '#0b044d', fontWeight: 600, marginBottom: 4 }}>Code sent to:</p>
                  <p style={{ fontSize: 13, color: '#6b6a8a', margin: 0 }}>{email}</p>
                </div>
              </div>

              <div className="auth-field">
                <label>Verification Code</label>
                <input
                  type="text"
                  placeholder="Enter 6-digit code"
                  value={code}
                  onChange={e => { setCode(e.target.value.replace(/\D/g, '').slice(0, 6)); setError(''); }}
                  maxLength={6}
                  required
                  style={{ letterSpacing: '4px', fontSize: 18, fontWeight: 700, textAlign: 'center' }}
                />
              </div>

              {error && (
                <div className="auth-error">
                  <Icon name="info" size={14} color="#8e1e18" />
                  {error}
                </div>
              )}

              {success && (
                <div style={{ background: '#f0fdf4', border: '1.5px solid #bbf7d0', borderRadius: 10, padding: '12px 16px', display: 'flex', alignItems: 'center', gap: '10px', marginBottom: 16 }}>
                  <Icon name="checkCircle" size={16} color="#15803d" />
                  <span style={{ fontSize: 13, color: '#15803d', fontWeight: 600 }}>{success}</span>
                </div>
              )}

              <button type="submit" className="pub-hr-btn auth-submit">
                <Icon name="checkCircle" size={15} color="#fff" />
                Verify Code
              </button>

              <div className="auth-card-footer">
                <p className="auth-switch">
                  Didn't receive the code?{' '}
                  <button type="button" className="auth-switch-btn" onClick={handleResendCode}>Resend Code</button>
                </p>
                <p className="auth-switch">
                  <button type="button" className="auth-switch-btn" onClick={() => setStep(1)}>Change Email Address</button>
                </p>
              </div>
            </form>
          )}

          {/* Step 3: New Password */}
          {step === 3 && (
            <form className="auth-form" onSubmit={handleResetPassword}>
              <div className="auth-field">
                <label>New Password</label>
                <div className="auth-pw-wrap">
                  <input
                    type={showNew ? 'text' : 'password'}
                    placeholder="Enter new password"
                    value={newPassword}
                    onChange={e => { setNewPassword(e.target.value); setError(''); }}
                    required
                  />
                  <button type="button" className="auth-eye" onClick={() => setShowNew(!showNew)}>
                    {showNew
                      ? <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                      : <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    }
                  </button>
                </div>
              </div>

              <div className="auth-field">
                <label>Confirm New Password</label>
                <div className="auth-pw-wrap">
                  <input
                    type={showConfirm ? 'text' : 'password'}
                    placeholder="Re-enter new password"
                    value={confirmPassword}
                    onChange={e => { setConfirmPassword(e.target.value); setError(''); }}
                    required
                  />
                  <button type="button" className="auth-eye" onClick={() => setShowConfirm(!showConfirm)}>
                    {showConfirm
                      ? <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                      : <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    }
                  </button>
                </div>
              </div>

              <div style={{ background: '#f7f6ff', borderRadius: 10, padding: '14px 16px', marginBottom: 16 }}>
                <p style={{ fontSize: 11.5, color: '#6b6a8a', lineHeight: 1.6, margin: 0 }}>
                  <strong style={{ color: '#0b044d' }}>Password Requirements:</strong><br/>
                  • At least 8 characters long<br/>
                  • Mix of uppercase and lowercase letters<br/>
                  • Include numbers and special characters
                </p>
              </div>

              {error && (
                <div className="auth-error">
                  <Icon name="info" size={14} color="#8e1e18" />
                  {error}
                </div>
              )}

              {success && (
                <div style={{ background: '#f0fdf4', border: '1.5px solid #bbf7d0', borderRadius: 10, padding: '12px 16px', display: 'flex', alignItems: 'center', gap: '10px', marginBottom: 16 }}>
                  <Icon name="checkCircle" size={16} color="#15803d" />
                  <span style={{ fontSize: 13, color: '#15803d', fontWeight: 600 }}>{success}</span>
                </div>
              )}

              <button type="submit" className="pub-hr-btn auth-submit">
                <Icon name="shield" size={15} color="#fff" />
                Reset Password
              </button>
            </form>
          )}

        </div>

        <div className="auth-tags">
          <span className="pub-tag"><Icon name="checkCircle" size={12} color="#15803d" /> Secure Recovery</span>
          <span className="pub-tag"><Icon name="shield" size={12} color="#15803d" /> Encrypted</span>
          <span className="pub-tag"><Icon name="mail" size={12} color="#15803d" /> Email Verified</span>
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
