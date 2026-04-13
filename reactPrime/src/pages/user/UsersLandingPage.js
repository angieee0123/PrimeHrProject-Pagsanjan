import { useState, useEffect } from 'react';
import { Icon } from '../../components/Icons';
import '../pages.css';

const services = [
  { icon: 'building', title: 'Business Permits',       desc: 'Apply and renew business permits online through the Municipal Business Office.' },
  { icon: 'clipboard', title: 'Civil Registration',      desc: 'Request birth, marriage, and death certificates from the Civil Registrar.' },
  { icon: 'heart', title: 'Health Services',          desc: 'Access municipal health programs, consultations, and medical assistance.' },
  { icon: 'users', title: 'Social Welfare',           desc: 'MSWD programs for senior citizens, PWDs, and indigent families.' },
  { icon: 'leaf', title: 'Agricultural Support',     desc: 'Livelihood programs and agricultural assistance for local farmers.' },
  { icon: 'tool', title: 'Infrastructure Projects', desc: 'Updates on public works, road projects, and community infrastructure.' },
];

const announcements = [
  { date: 'Jun 20, 2025', tag: 'Advisory',   title: 'Schedule of Payment for Real Property Tax — 2nd Quarter 2025' },
  { date: 'Jun 18, 2025', tag: 'Event',      title: 'Pagsanjan Founding Anniversary Celebration — June 25, 2025' },
  { date: 'Jun 15, 2025', tag: 'Program',    title: 'MSWD Livelihood Training Program — Open for Registration' },
  { date: 'Jun 10, 2025', tag: 'Notice',     title: 'Water Interruption Advisory — Barangay Pinagsanjan Area' },
];

const chatFaqs = [
  'What are the office hours?',
  'How do I get a barangay clearance?',
  'Where is the Municipal Hall located?',
  'How do I apply for a business permit?',
];

const chatResponses = {
  'What are the office hours?': 'The Municipal Government of Pagsanjan is open Monday to Friday, 8:00 AM – 5:00 PM. Closed on weekends and public holidays.',
  'How do I get a barangay clearance?': 'Visit your respective Barangay Hall with a valid ID and proof of residency. Processing usually takes 1 business day.',
  'Where is the Municipal Hall located?': 'The Municipal Hall of Pagsanjan is located at Poblacion, Pagsanjan, Laguna. Landmark: near the Pagsanjan Church.',
  'How do I apply for a business permit?': 'Visit the Municipal Business Office with your DTI/SEC registration, barangay clearance, and filled-out application form. You may also inquire at the front desk.',
};

const contactItems = [
  { icon: 'mapPin', label: 'Address',      val: 'Poblacion, Pagsanjan, Laguna 4008' },
  { icon: 'phone', label: 'Telephone',    val: '(049) 501-0000 · (049) 501-0001' },
  { icon: 'mail', label: 'Email',        val: 'info@pagsanjan.gov.ph' },
  { icon: 'clock', label: 'Office Hours', val: 'Mon – Fri, 8:00 AM – 5:00 PM' },
];

export default function Landing({ onLogin }) {
  const [chatOpen, setChatOpen]   = useState(false);
  const [fabDark, setFabDark]     = useState(false);
  const [messages, setMessages]   = useState([
    { from: 'bot', text: 'Hello! I\'m the Pagsanjan LGU Assistant. How can I help you today? You can ask me about municipal services, office hours, or requirements.' }
  ]);
  const [input, setInput]         = useState('');

  const handleScroll = () => {
    const scrollY = window.scrollY + window.innerHeight;
    const docH = document.documentElement.scrollHeight;
    setFabDark(scrollY > docH - 400);
  };

  useEffect(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const sendMessage = (text) => {
    const userMsg = text || input.trim();
    if (!userMsg) return;
    const reply = chatResponses[userMsg] || 'Thank you for your message. For detailed inquiries, please visit the Municipal Hall or call our hotline at (049) 501-0000.';
    setMessages(prev => [...prev, { from: 'user', text: userMsg }, { from: 'bot', text: reply }]);
    setInput('');
  };

  return (
    <div className="pub-root">

      {/* ── Gov Bar ── */}
      <div className="pub-govbar">
        <span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ display: 'inline-block', verticalAlign: 'middle', marginRight: 4 }}>
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
          </svg>
          Republic of the Philippines &nbsp;·&nbsp; Province of Laguna
        </span>
        <span>Official Website of the Municipal Government of Pagsanjan</span>
      </div>

      {/* ── Navbar ── */}
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
        <div className="pub-nav-links">
          <a href="#services">Services</a>
          <a href="#announcements">Announcements</a>
          <a href="#about">About</a>
          <a href="#contact">Contact</a>
        </div>
        <button className="pub-hr-btn" onClick={onLogin}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          Employee Portal
        </button>
      </nav>

      {/* ── Hero ── */}
      <section className="pub-hero">
        <div className="pub-hero-inner">
          <div className="pub-hero-text">
            <div className="pub-hero-badge">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: 6 }}>
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              Official Municipal Government Portal
            </div>
            <h1 className="pub-hero-title">
              Serving the People of<br />
              <span className="pub-hero-highlight">Pagsanjan, Laguna</span>
            </h1>
            <p className="pub-hero-sub">
              Access municipal services, announcements, and government information
              from the Municipal Government of Pagsanjan, Province of Laguna.
            </p>
            <div className="pub-hero-actions">
              <a href="#services" className="pub-btn-primary">Explore Services</a>
              <button className="pub-btn-ghost" onClick={() => setChatOpen(true)}>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Ask Our AI Assistant
              </button>
            </div>
          </div>
          <div className="pub-hero-card">
            <div className="pub-hero-card-header">
              <div className="pub-hero-card-dot active" />
              <span>Municipal Services Portal</span>
            </div>
            <div className="pub-hero-stats">
              <div className="pub-hstat">
                <span className="pub-hstat-val">17</span>
                <span className="pub-hstat-label">Offices & Departments</span>
              </div>
              <div className="pub-hstat-divider" />
              <div className="pub-hstat">
                <span className="pub-hstat-val">348</span>
                <span className="pub-hstat-label">Government Personnel</span>
              </div>
              <div className="pub-hstat-divider" />
              <div className="pub-hstat">
                <span className="pub-hstat-val">24/7</span>
                <span className="pub-hstat-label">AI Chatbot Support</span>
              </div>
            </div>
            <div className="pub-hero-card-tags">
              <span className="pub-tag">✓ BIR Compliant</span>
              <span className="pub-tag">✓ GSIS Ready</span>
              <span className="pub-tag">✓ CSC Accredited</span>
              <span className="pub-tag">✓ ARTA Compliant</span>
            </div>
          </div>
        </div>
      </section>

      {/* ── Services ── */}
      <section className="pub-section" id="services">
        <div className="pub-section-inner">
          <div className="pub-section-head">
            <span className="pub-eyebrow">MUNICIPAL SERVICES</span>
            <h2>What can we help you with?</h2>
            <p>Access the services offered by the Municipal Government of Pagsanjan, Laguna.</p>
          </div>
          <div className="pub-services-grid">
            {services.map((s, i) => (
              <div className="pub-service-card" key={i}>
                <div className="pub-service-icon"><Icon name={s.icon} size={32} /></div>
                <h4>{s.title}</h4>
                <p>{s.desc}</p>
                <span className="pub-service-link">Learn more →</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Announcements ── */}
      <section className="pub-section alt" id="announcements">
        <div className="pub-section-inner">
          <div className="pub-section-head">
            <span className="pub-eyebrow">LATEST UPDATES</span>
            <h2>Announcements & Advisories</h2>
            <p>Stay informed with the latest news from the Municipal Government.</p>
          </div>
          <div className="pub-announcements">
            {announcements.map((a, i) => (
              <div className="pub-announce-item" key={i}>
                <div className="pub-announce-left">
                  <span className={`pub-announce-tag ${a.tag.toLowerCase()}`}>{a.tag}</span>
                  <p className="pub-announce-title">{a.title}</p>
                </div>
                <span className="pub-announce-date">{a.date}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── About ── */}
      <section className="pub-section" id="about">
        <div className="pub-section-inner">
          <div className="pub-section-head">
            <span className="pub-eyebrow">ABOUT THE MUNICIPALITY</span>
            <h2>Municipal Government of Pagsanjan</h2>
            <p>A brief overview of the municipality, its leadership, and its commitment to public service.</p>
          </div>

          {/* Hero intro row */}
          <div className="pub-about-hero">
            <div className="pub-about-hero-text">
              <div className="pub-about-hero-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginRight: 6 }}>
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Pagsanjan, Laguna
              </div>
              <h3>Home of the Famous<br /><span>Pagsanjan Falls</span></h3>
              <p>
                Pagsanjan is a <strong>first-class municipality</strong> in the Province of Laguna, Philippines —
                known as the <strong>"Shooting the Rapids" capital</strong>. Composed of 16 barangays, it serves
                a population of over 40,000 residents across Region IV-A (CALABARZON).
              </p>
              <p>
                The Municipal Government is committed to transparent, efficient, and responsive governance
                through its 17 offices and departments, serving every Pagsanjeño.
              </p>
            </div>
            <div className="pub-about-hero-stats">
              {[['16','Barangays'],['17','Offices & Depts'],['40K+','Residents'],['348',"Gov't Personnel"]].map(([val, label]) => (
                <div className="pub-about-stat" key={label}>
                  <span className="pub-about-stat-val">{val}</span>
                  <span className="pub-about-stat-label">{label}</span>
                </div>
              ))}
            </div>
          </div>

          {/* 3 cards row */}
          <div className="pub-about-cards">
            <div className="pub-about-card2">
              <div className="pub-about-card2-icon"><Icon name="target" size={28} /></div>
              <h4>Vision</h4>
              <p>A progressive, peaceful, and self-reliant municipality with empowered citizens enjoying a high quality of life under a transparent and accountable local government.</p>
            </div>
            <div className="pub-about-card2">
              <div className="pub-about-card2-icon"><Icon name="flag" size={28} /></div>
              <h4>Mission</h4>
              <p>To deliver efficient, effective, and equitable public services through good governance, community participation, and sustainable development programs for all Pagsanjeños.</p>
            </div>
            <div className="pub-about-card2">
              <div className="pub-about-card2-icon"><Icon name="barChart" size={28} /></div>
              <h4>Key Facts</h4>
              <ul className="pub-about-list">
                <li><span>Classification</span><strong>1st Class Municipality</strong></li>
                <li><span>Province</span><strong>Laguna</strong></li>
                <li><span>Region</span><strong>IV-A (CALABARZON)</strong></li>
                <li><span>Barangays</span><strong>16 Barangays</strong></li>
                <li><span>Departments</span><strong>17 Offices</strong></li>
                <li><span>Personnel</span><strong>348 Employees</strong></li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* ── Contact ── */}
      <section className="pub-section alt" id="contact">
        <div className="pub-section-inner">
          <div className="pub-section-head">
            <span className="pub-eyebrow">GET IN TOUCH</span>
            <h2>Contact Us</h2>
            <p>Reach out to the Municipal Government of Pagsanjan for inquiries, concerns, or assistance.</p>
          </div>
          <div className="pub-contact-grid">

            {/* Left: info panel */}
            <div className="pub-contact-panel">
              <div className="pub-contact-panel-header">
                <div className="pub-contact-panel-icon"><Icon name="building" size={24} /></div>
                <div>
                  <p className="pub-contact-panel-title">Municipal Hall</p>
                  <p className="pub-contact-panel-sub">Pagsanjan, Laguna</p>
                </div>
              </div>
              <div className="pub-contact-items">
                {contactItems.map(({ icon, label, val }) => (
                  <div className="pub-contact-item" key={label}>
                    <div className="pub-contact-icon"><Icon name={icon} size={20} /></div>
                    <div>
                      <p className="pub-contact-label">{label}</p>
                      <p className="pub-contact-val">{val}</p>
                    </div>
                  </div>
                ))}
              </div>
              <div className="pub-contact-note">Closed on weekends &amp; public holidays</div>
            </div>

            {/* Right: form */}
            <form className="pub-contact-form" onSubmit={e => { e.preventDefault(); alert('Message sent! We will get back to you within 1–2 business days.'); e.target.reset(); }}>
              <div className="pub-contact-form-head">
                <p className="pub-contact-form-title">Send us a Message</p>
                <p className="pub-contact-form-sub">We'll respond within 1–2 business days.</p>
              </div>
              <div className="pub-contact-row">
                <div className="pub-contact-field">
                  <label>Full Name</label>
                  <input type="text" placeholder="Your full name" required />
                </div>
                <div className="pub-contact-field">
                  <label>Email Address</label>
                  <input type="email" placeholder="your@email.com" required />
                </div>
              </div>
              <div className="pub-contact-field">
                <label>Subject</label>
                <input type="text" placeholder="e.g. Business Permit Inquiry" required />
              </div>
              <div className="pub-contact-field">
                <label>Message</label>
                <textarea rows={5} placeholder="Type your message here..." required />
              </div>
              <button type="submit" className="pub-btn-primary" style={{ width: '100%', justifyContent: 'center' }}>
                Send Message
              </button>
            </form>

          </div>
        </div>
      </section>

      {/* ── About / HR Portal CTA ── */}
      <section className="pub-cta-section">
        <div className="pub-cta-inner">
          <div className="pub-cta-text">
            <span className="pub-eyebrow light">PRIME HRIS</span>
            <h2>Are you a Municipal Government Employee?</h2>
            <p>The PRIME HRIS portal is exclusively for authorized employees of the Municipal Government of Pagsanjan, Laguna. Access your payroll, leave, and personnel records here.</p>
            <button className="pub-cta-btn" onClick={onLogin}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              Sign In to PRIME HRIS
            </button>
            <p className="pub-cta-note">Municipal Government employees only · Contact your administrator for access</p>
          </div>
          <div className="pub-cta-card">
            <div className="pub-cta-card-label">PRIME HRIS</div>
            <p className="pub-cta-card-sub">Personnel Records &amp; Information Management for Employees</p>
            <div className="pub-cta-features">
              <div className="pub-cta-feat">✓ Payroll Processing</div>
              <div className="pub-cta-feat">✓ 201 File Management</div>
              <div className="pub-cta-feat">✓ Leave & Benefits</div>
              <div className="pub-cta-feat">✓ DTR Monitoring</div>
              <div className="pub-cta-feat">✓ BIR / GSIS / PhilHealth</div>
              <div className="pub-cta-feat">✓ Payroll Reports</div>
            </div>
          </div>
        </div>
      </section>

      {/* ── Footer ── */}
      <footer className="pub-footer">
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
          <div className="pub-footer-links">
            <a href="#privacy">Privacy Policy</a>
            <a href="#terms">Terms of Use</a>
            <a href="#contact">Contact Us</a>
            <a href="#sitemap">Sitemap</a>
          </div>
          <p className="pub-footer-copy">© 2025 Municipal Government of Pagsanjan, Laguna. All rights reserved.</p>
        </div>
      </footer>

      {/* ── AI Chatbot ── */}
      <button className={`chat-fab${fabDark ? ' chat-fab-light' : ''}`} onClick={() => setChatOpen(!chatOpen)} title="AI Assistant">
        {chatOpen
          ? <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          : <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        }
        {!chatOpen && <span className="chat-fab-badge">AI</span>}
      </button>

      {chatOpen && (
        <div className="chatbot-window">
          <div className="chatbot-header">
            <div className="chatbot-header-left">
              <div className="chatbot-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
              <div>
                <p className="chatbot-name">Pagsanjan LGU Assistant</p>
                <p className="chatbot-status">● Online</p>
              </div>
            </div>
            <button className="chatbot-close" onClick={() => setChatOpen(false)}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div className="chatbot-messages">
            {messages.map((m, i) => (
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
              <button key={i} className="chatbot-faq-btn" onClick={() => sendMessage(q)}>{q}</button>
            ))}
          </div>

          <div className="chatbot-input-row">
            <input
              type="text"
              placeholder="Type your question..."
              value={input}
              onChange={e => setInput(e.target.value)}
              onKeyDown={e => e.key === 'Enter' && sendMessage()}
            />
            <button className="chatbot-send" onClick={() => sendMessage()}>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
          </div>
        </div>
      )}

    </div>
  );
}
