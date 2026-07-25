<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id']) && !isset($_GET['public'])) {
    header('Location: admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePulse — Advanced Healthcare For a Better Tomorrow</title>
    <link rel="icon" type="image/png" href="favicon.png">
    
    <!-- Google Fonts - Outfit for Headings, Plus Jakarta Sans for Body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: { DEFAULT: '#3b82f6', light: '#60a5fa', dark: '#1d4ed8', '50': '#eff6ff' },
                        secondary: { DEFAULT: '#0d9488', light: '#14b8a6', dark: '#0f766e', '50': '#f0fdfa' },
                        dark: { DEFAULT: '#080c14', light: '#0f172a', lighter: '#1e293b' },
                    }
                }
            }
        }
    </script>
    
    <style>
        html {
            scroll-behavior: smooth;
        }

        ::selection {
            background: #3b82f6;
            color: #fff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            background-color: #fcfdff;
        }

        /* --- Premium Custom Scrollbar --- */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #080c14;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
            border: 2px solid #080c14;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3b82f6;
        }

        /* --- Custom Keyframes & Animations --- */
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
        }
        @keyframes float-mid {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-22px) rotate(-3deg); }
        }
        @keyframes pulse-soft {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.05); }
        }
        @keyframes rotate-slow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes glow-shift {
            0%, 100% { filter: hue-rotate(0deg) saturate(1); }
            50% { filter: hue-rotate(30deg) saturate(1.2); }
        }

        .animate-float-1 { animation: float-slow 6s ease-in-out infinite; }
        .animate-float-2 { animation: float-mid 8s ease-in-out infinite 0.5s; }
        .animate-float-3 { animation: float-slow 7s ease-in-out infinite 1s; }
        .animate-pulse-soft { animation: pulse-soft 4s ease-in-out infinite; }
        .animate-rotate-slow { animation: rotate-slow 20s linear infinite; }
        .animate-glow-shift { animation: glow-shift 10s ease-in-out infinite; }

        /* --- Glassmorphism --- */
        .glass-panel {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .glass-panel-dark {
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 40px -15px rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.25);
        }

        /* --- Custom Grid Borders & Accents --- */
        .border-glow-hover {
            position: relative;
            z-index: 1;
        }
        .border-glow-hover::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.5px;
            background: linear-gradient(135deg, #3b82f6, #0d9488);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
            z-index: -1;
        }
        .border-glow-hover:hover::after {
            opacity: 1;
        }

        /* --- FAQ height transition --- */
        .faq-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
        }
        .faq-body.open {
            max-height: 300px;
        }
        .faq-plus {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .faq-plus.open {
            transform: rotate(45deg);
        }
    </style>
</head>

<body class="text-slate-600">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect, useRef } = React;

        /* ─── Premium Real Image Assets ─── */
        const IMG = {
            hero: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=700&h=850&fit=crop&q=80',
            about: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=700&h=520&fit=crop&q=80',
            whyChoose: 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=620&h=420&fit=crop&q=80',
            doc1: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&h=500&fit=crop&q=80',
            doc2: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&h=500&fit=crop&q=80',
            doc3: 'https://images.unsplash.com/photo-1594824476967-48c8b964ac31?w=400&h=500&fit=crop&q=80',
            doc4: 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=400&h=500&fit=crop&q=80',
            pat1: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=140&h=140&fit=crop&q=80',
            pat2: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=140&h=140&fit=crop&q=80',
            pat3: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=140&h=140&fit=crop&q=80',
            pat4: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=140&h=140&fit=crop&q=80',
            emerg: 'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?w=640&h=400&fit=crop&q=80',
            surgery: 'https://images.unsplash.com/photo-1551601651-2a8555f1a136?w=640&h=400&fit=crop&q=80',
        };

        /* ─── Intersection Observer Hook for Animations ─── */
        function useInView(opts = {}) {
            const ref = useRef(null);
            const [v, setV] = useState(false);
            useEffect(() => {
                const el = ref.current;
                if (!el) return;
                const o = new IntersectionObserver(([e]) => {
                    if (e.isIntersecting) {
                        setV(true);
                        o.unobserve(el);
                    }
                }, { threshold: 0.1, ...opts });
                o.observe(el);
                return () => o.disconnect();
            }, []);
            return [ref, v];
        }

        /* ─── Count-Up Animation Hook ─── */
        function useCounter(target, dur = 2000) {
            const [c, setC] = useState(0);
            const [ref, iv] = useInView();
            const ran = useRef(false);
            useEffect(() => {
                if (iv && !ran.current) {
                    ran.current = true;
                    const s = performance.now();
                    const step = (n) => {
                        const p = Math.min((n - s) / dur, 1);
                        const e = 1 - Math.pow(1 - p, 3); // Cubic Ease Out
                        setC(Math.floor(e * target));
                        if (p < 1) requestAnimationFrame(step);
                    };
                    requestAnimationFrame(step);
                }
            }, [iv, target, dur]);
            return [ref, c];
        }

        /* ─── Scroll Y Tracker Hook ─── */
        function useScrollY() {
            const [y, setY] = useState(0);
            useEffect(() => {
                const h = () => setY(window.scrollY);
                window.addEventListener('scroll', h, { passive: true });
                return () => window.removeEventListener('scroll', h);
            }, []);
            return y;
        }

        /* ─── Standard Fade-In Reveal Component ─── */
        function FadeIn({ children, className = '', delay = 0, dir = 'up' }) {
            const [ref, iv] = useInView();
            const t = { 
                up: 'translate-y-12', 
                down: '-translate-y-12', 
                left: '-translate-x-12', 
                right: 'translate-x-12', 
                none: '' 
            };
            return (
                <div 
                    ref={ref} 
                    className={`transition-all duration-1000 cubic-bezier(0.16, 1, 0.3, 1) ${iv ? 'opacity-100 translate-x-0 translate-y-0' : `opacity-0 ${t[dir]}`} ${className}`} 
                    style={{ transitionDelay: `${delay}ms` }}
                >
                    {children}
                </div>
            );
        }

        /* ─── Global System Data arrays ─── */
        const depts = [
            { name: 'Cardiology', icon: 'fa-heart-pulse', desc: 'Advanced diagnostics, state-of-the-art interventional suites, and critical care from leading heart specialists.', c: 'bg-rose-50/80 text-rose-500 border-rose-100' },
            { name: 'Neurology', icon: 'fa-brain', desc: 'Expert treatment for epilepsy, stroke, and spinal disorders with modern neuro-imaging support.', c: 'bg-violet-50/80 text-violet-500 border-violet-100' },
            { name: 'Orthopedics', icon: 'fa-bone', desc: 'Robotic joint replacements, reconstructive surgery, and sports medicine for structural restoration.', c: 'bg-sky-50/80 text-sky-500 border-sky-100' },
            { name: 'Pediatrics', icon: 'fa-baby', desc: 'Compassionate pediatric care, neonatal intensive services, and growth monitoring programs.', c: 'bg-pink-50/80 text-pink-500 border-pink-100' },
            { name: 'Gynecology', icon: 'fa-venus', desc: 'Specialized healthcare from prenatal management to keyhole laparoscopic gynecological operations.', c: 'bg-teal-50/80 text-teal-500 border-teal-100' },
            { name: 'Dermatology', icon: 'fa-hand-dots', desc: 'Advanced clinical treatments for complex skin disorders and laser-based cosmetic therapies.', c: 'bg-amber-50/80 text-amber-500 border-amber-100' },
            { name: 'General Medicine', icon: 'fa-stethoscope', desc: 'Thorough preventive screenings, chronic disease management, and complete family healthcare.', c: 'bg-emerald-50/80 text-emerald-500 border-emerald-100' },
            { name: 'Emergency Care', icon: 'fa-truck-medical', desc: 'Accredited Level-1 emergency and trauma facility open 24/7/365 with on-site surgical dispatch.', c: 'bg-red-50/80 text-red-500 border-red-100' },
        ];

        const docs = [
            { name: 'Dr. Sarah Mitchell', spec: 'Cardiologist', exp: '18 Years', rating: 4.9, img: IMG.doc1 },
            { name: 'Dr. James Rodriguez', spec: 'Neurologist', exp: '15 Years', rating: 4.8, img: IMG.doc2 },
            { name: 'Dr. Emily Chen', spec: 'Orthopedic Surgeon', exp: '12 Years', rating: 4.9, img: IMG.doc3 },
            { name: 'Dr. Michael Patel', spec: 'Pediatrician', exp: '20 Years', rating: 4.7, img: IMG.doc4 },
        ];

        const svcs = [
            { title: 'Emergency Care', icon: 'fa-kit-medical', desc: 'Accredited Level-1 trauma response team on-site 24/7 with immediate resuscitation bays and critical response workflows.', img: IMG.emerg, big: true },
            { title: 'Intensive Care Unit (ICU)', icon: 'fa-bed-pulse', desc: 'Advanced real-time monitor systems with dedicated 1:1 nurse-to-patient ratio support.', big: false },
            { title: 'Diagnostic Laboratory', icon: 'fa-flask-vial', desc: 'NABL accredited automated processing unit delivering precision results for 2000+ tests.', big: false },
            { title: '24/7 Portal Pharmacy', icon: 'fa-prescription-bottle-medical', desc: 'In-house digital prescription tracking system ensuring fast, error-free medicine dispensing.', big: false },
            { title: 'Ambulance Networks', icon: 'fa-truck-medical', desc: 'GPS-integrated mobile life support vehicles with live clinical feed updates back to the hospital.', big: false },
            { title: 'Radiology Workstation', icon: 'fa-x-ray', desc: 'Equipped with 3T MRI, 128-slice CT scans, and direct link to the central PACS workstation.', big: false },
            { title: 'Advanced Robotics Surgery', icon: 'fa-syringe', desc: 'Minimally invasive operations performing surgical maneuvers with absolute millimeter accuracy.', img: IMG.surgery, big: true },
            { title: 'Telehealth Consultations', icon: 'fa-laptop-medical', desc: 'Secure, encrypted high-definition clinical video consultations, digital prescriptions, and EHR tracking from home.', big: false, full: true },
        ];

        const whyItems = [
            { title: 'Board-Certified Medical Specialists', desc: 'Every specialist physician is internationally board-certified with extensive clinical experience and clinical research credentials.' },
            { title: 'Next-Generation Medical Technology', desc: 'Investing in cutting-edge technology including AI-assisted diagnostic tools, 3D imaging, and robotic surgical units.' },
            { title: 'Patient-First Empathetic Healthcare', desc: 'Formulating customized treatment plans designed specifically around your physical wellness and comfort.' },
            { title: 'Integrated Continuum of Care', desc: 'From diagnosis through operation to outpatient rehab, your history is tracked seamlessly in our database portal.' },
        ];

        const testis = [
            { name: 'Rebecca Thompson', text: 'The cardiology team at CarePulse saved my life. From the emergency room to recovery, every moment was handled with incredible professionalism and genuine compassion. I will never forget their kindness.', rating: 5, img: IMG.pat1 },
            { name: 'David Kim', text: 'After years of chronic back pain, Dr. Chen performed a minimally invasive spine surgery. I was walking the next day and fully recovered within weeks. The orthopedic team here is absolutely world-class.', rating: 5, img: IMG.pat2 },
            { name: 'Susan Clarke', text: 'The maternity care exceeded all expectations. The team made what could have been stressful feel safe, comfortable, and even joyful. I recommend their gynecology department to every expecting mother.', rating: 5, img: IMG.pat3 },
            { name: 'Robert Hayes', text: 'Online consultation was a game-changer during my recovery. Speaking with my neurologist from home while getting thorough medical advice is the future of healthcare. Brilliant system.', rating: 4, img: IMG.pat4 },
        ];

        const faqs = [
            { q: 'How do I book an appointment?', a: 'You can book an appointment easily by submitting the Contact/Booking form at the bottom of this page, calling our direct line +1 (555) 234-5678, or using our client portal.' },
            { q: 'What insurance plans do you accept?', a: 'CarePulse works with major insurance carriers including Blue Cross Blue Shield, Aetna, UnitedHealthcare, Cigna, and Medicare. Our billing office will verify your coverage prior to arrival.' },
            { q: 'Are emergency services available 24/7?', a: 'Yes. Our Level-1 Trauma Emergency Department operates 24/7/365 with specialty physicians on-site. Immediate ambulance coordination can be reached via +1 (555) 911.' },
            { q: 'How do I access my diagnostic records?', a: 'You can access all laboratory reports, radiology scans, and prescriptions securely via our Patient Portal. Simply log in with your credentials, or request physical records at front desk.' },
            { q: 'Do you provide international patient services?', a: 'Yes. CarePulse coordinate visas, translations, direct flight medical transport, and specialized lodging support for international visitors.' },
        ];

        /* ─── Scroll Progress Indicator ─── */
        function ScrollProgress() {
            const y = useScrollY();
            const h = typeof document !== 'undefined' ? document.documentElement.scrollHeight - window.innerHeight : 1;
            const pct = h > 0 ? (y / h) * 100 : 0;
            return (
                <div className="fixed top-0 left-0 right-0 z-[100] h-[4px]">
                    <div 
                        className="h-full bg-gradient-to-r from-primary via-secondary to-primary-light transition-all duration-75" 
                        style={{ width: `${pct}%` }}
                    ></div>
                </div>
            );
        }

        /* ─── Navigation Header Component ─── */
        function Navbar() {
            const scrollY = useScrollY();
            const [mob, setMob] = useState(false);
            const sc = scrollY > 40;
            
            const links = ['Home', 'About', 'Departments', 'Doctors', 'Services', 'Testimonials', 'Contact'];
            
            useEffect(() => {
                document.body.style.overflow = mob ? 'hidden' : '';
                return () => { document.body.style.overflow = ''; }
            }, [mob]);

            const go = (id) => {
                setMob(false);
                document.getElementById(id.toLowerCase())?.scrollIntoView({ behavior: 'smooth' });
            };

            return (
                <>
                    <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${sc ? 'py-2 bg-dark/85 backdrop-blur-xl border-b border-white/5 shadow-xl' : 'py-5 bg-transparent'}`}>
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <div className="flex items-center justify-between">
                                {/* Brand Logo */}
                                <a href="#" className="flex items-center gap-3 group" aria-label="CarePulse Home">
                                    <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/20 group-hover:scale-105 transition-transform duration-300">
                                        <i className="fa-solid fa-heart-pulse text-white text-xl animate-pulse-soft"></i>
                                    </div>
                                    <div>
                                        <span className="font-outfit font-extrabold text-lg tracking-tight text-white uppercase block leading-tight">
                                            Care<span className="text-primary-light">Pulse</span>
                                        </span>
                                        <span className="text-[9px] font-bold text-slate-400 tracking-[0.25em] uppercase leading-none block">
                                            Clinical Portal
                                        </span>
                                    </div>
                                </a>

                                {/* Desktop Navigation Links */}
                                <div className="hidden lg:flex items-center gap-1.5">
                                    {links.map(l => (
                                        <button 
                                            key={l} 
                                            onClick={() => go(l)} 
                                            className="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-300"
                                        >
                                            {l}
                                        </button>
                                    ))}
                                </div>

                                {/* Actions */}
                                <div className="hidden lg:flex items-center gap-3">
                                    <a 
                                        href="login.php" 
                                        className="flex items-center gap-2 border border-white/10 hover:border-primary/50 text-white bg-white/5 hover:bg-primary px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-300"
                                    >
                                        <i className="fa-solid fa-right-to-bracket text-[10px]"></i> Portal Login
                                    </a>
                                    <button 
                                        onClick={() => go('Contact')} 
                                        className="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-lg shadow-primary/25 hover:shadow-primary/45 hover:scale-[1.03] active:scale-[0.98] transition-all duration-300"
                                    >
                                        <i className="fa-regular fa-calendar-check text-[10px]"></i> Book Appointment
                                    </button>
                                </div>

                                {/* Mobile Toggle */}
                                <button 
                                    className="lg:hidden w-10 h-10 rounded-xl flex items-center justify-center bg-white/5 hover:bg-white/10 border border-white/10 text-white transition-colors" 
                                    onClick={() => setMob(true)} 
                                    aria-label="Open navigation menu"
                                >
                                    <i className="fa-solid fa-bars-staggered"></i>
                                </button>
                            </div>
                        </div>
                    </nav>

                    {/* Mobile Menu Drawer */}
                    {mob && (
                        <div className="fixed inset-0 z-[60] flex justify-end">
                            <div className="absolute inset-0 bg-dark/60 backdrop-blur-md transition-opacity" onClick={() => setMob(false)}></div>
                            <div className="relative w-80 max-w-[85vw] bg-dark-light border-l border-white/5 shadow-2xl flex flex-col p-6 animate-slide-in-right">
                                <div className="flex items-center justify-between pb-6 border-b border-white/5">
                                    <span className="font-outfit font-bold text-white text-base tracking-wide uppercase">Menu</span>
                                    <button 
                                        onClick={() => setMob(false)} 
                                        className="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-slate-400 hover:text-white"
                                        aria-label="Close menu"
                                    >
                                        <i className="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div className="flex-1 overflow-y-auto py-6 space-y-2">
                                    {links.map(l => (
                                        <button 
                                            key={l} 
                                            onClick={() => go(l)} 
                                            className="w-full text-left px-4 py-3.5 rounded-xl text-slate-300 font-semibold text-sm hover:bg-white/5 hover:text-white transition-colors"
                                        >
                                            {l}
                                        </button>
                                    ))}
                                </div>
                                <div className="pt-6 border-t border-white/5 flex flex-col gap-3">
                                    <a 
                                        href="login.php" 
                                        className="w-full text-center border border-white/10 text-white bg-white/5 py-3 rounded-xl font-bold text-sm"
                                    >
                                        Portal Login
                                    </a>
                                    <button 
                                        onClick={() => go('Contact')} 
                                        className="w-full bg-gradient-to-r from-primary to-primary-dark text-white py-3.5 rounded-xl font-bold text-sm shadow-xl shadow-primary/20"
                                    >
                                        Book Appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </>
            );
        }

        /* ─── Hero Section Component ─── */
        function Hero() {
            return (
                <section id="home" className="relative min-h-screen flex items-center bg-dark pt-32 pb-24 overflow-hidden">
                    {/* Glowing Mesh Orbs */}
                    <div className="absolute top-[-10%] right-[-10%] w-[600px] sm:w-[800px] h-[600px] sm:h-[800px] bg-primary/10 rounded-full blur-[140px] animate-pulse-soft -z-10"></div>
                    <div className="absolute bottom-[-10%] left-[-10%] w-[500px] sm:w-[700px] h-[500px] sm:h-[700px] bg-secondary/15 rounded-full blur-[140px] animate-pulse-soft -z-10"></div>
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] h-[1000px] border border-white/[0.02] rounded-full -z-10 animate-rotate-slow"></div>
                    
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full relative z-10">
                        <div className="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                            
                            {/* Left Text Block */}
                            <div className="lg:col-span-7 max-w-2xl text-left">
                                <FadeIn>
                                    <div className="inline-flex items-center gap-2.5 bg-primary/10 border border-primary/20 rounded-full px-4.5 py-2 mb-7">
                                        <span className="relative flex h-2 w-2">
                                            <span className="pulse-ring absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75 animate-ping"></span>
                                            <span className="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
                                        </span>
                                        <span className="text-[11px] font-bold text-primary-light tracking-[0.1em] uppercase">
                                            Trusted by 1M+ Patients Worldwide
                                        </span>
                                    </div>
                                </FadeIn>

                                <FadeIn delay={100}>
                                    <h1 className="font-outfit font-black text-4xl sm:text-5xl lg:text-[4rem] text-white leading-[1.05] tracking-tight mb-6">
                                        Advanced Clinical Care For a{' '}
                                        <span className="relative inline-block mt-1 sm:mt-0">
                                            <span className="bg-gradient-to-r from-primary-light via-primary to-secondary bg-clip-text text-transparent animate-glow-shift">
                                                Better Tomorrow
                                            </span>
                                            <svg className="absolute -bottom-3 left-0 w-full opacity-60" viewBox="0 0 300 14" fill="none">
                                                <path d="M2 10C60 2 140 2 160 8C180 14 260 4 298 10" stroke="#0d9488" strokeWidth="4" strokeLinecap="round" />
                                            </svg>
                                        </span>
                                    </h1>
                                </FadeIn>

                                <FadeIn delay={200}>
                                    <p className="text-slate-400 text-base sm:text-lg leading-relaxed mb-9 max-w-xl">
                                        Experience next-generation medicine combining internationally board-certified specialists, digital workflow automation, and compassionate care designed around your physical well-being.
                                    </p>
                                </FadeIn>

                                <FadeIn delay={300}>
                                    <div className="flex flex-wrap gap-4 mb-12">
                                        <button 
                                            onClick={() => document.getElementById('contact').scrollIntoView({ behavior: 'smooth' })} 
                                            className="group bg-gradient-to-r from-primary to-primary-dark text-white px-7 py-4.5 rounded-2xl font-bold shadow-xl shadow-primary/20 hover:shadow-primary/45 hover:scale-[1.03] active:scale-[0.97] transition-all flex items-center gap-3"
                                        >
                                            <i className="fa-regular fa-calendar-check text-sm"></i> Book Appointment
                                            <i className="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                                        </button>
                                        
                                        <a 
                                            href="login.php" 
                                            className="group bg-white/5 border border-white/10 hover:border-primary/45 text-white px-7 py-4.5 rounded-2xl font-bold hover:bg-white/10 transition-all flex items-center gap-2.5"
                                        >
                                            <i className="fa-solid fa-user-lock text-sm text-primary-light"></i> Staff Portal
                                        </a>

                                        <a 
                                            href="tel:+1555911" 
                                            className="group bg-white/5 border border-white/5 hover:border-red-500/20 text-white px-7 py-4.5 rounded-2xl font-bold hover:bg-red-500/10 transition-all flex items-center gap-2.5"
                                        >
                                            <span className="w-6 h-6 rounded bg-red-500/25 flex items-center justify-center text-red-500 animate-pulse-soft">
                                                <i className="fa-solid fa-phone text-xs"></i>
                                            </span>
                                            Emergency Hot Line
                                        </a>
                                    </div>
                                </FadeIn>

                                <FadeIn delay={400}>
                                    <div className="flex flex-wrap items-center gap-x-6 gap-y-3.5">
                                        {[['JCI Accredited Facility', 'fa-shield-halved'], ['NABH Certified', 'fa-certificate'], ['ISO 9001 Guidelines', 'fa-award']].map(([t, ic], i) => (
                                            <div key={t} className="flex items-center gap-2">
                                                <i className={`fa-solid ${ic} text-secondary text-sm`}></i>
                                                <span className="text-xs font-bold text-slate-400 tracking-wider uppercase">{t}</span>
                                                {i < 2 && <span className="w-[1px] h-4 bg-white/10 hidden sm:block"></span>}
                                            </div>
                                        ))}
                                    </div>
                                </FadeIn>
                            </div>

                            {/* Right Image Canvas & Floating HUDs */}
                            <div className="lg:col-span-5 relative hidden lg:block">
                                <FadeIn delay={200} dir="right">
                                    <div className="relative max-w-sm mx-auto">
                                        {/* Outer Glow Backdrops */}
                                        <div className="absolute -inset-8 bg-gradient-to-br from-primary/15 to-secondary/15 rounded-[3rem] blur-3xl -z-10 animate-pulse-soft"></div>
                                        
                                        {/* Masked Hero Doctor Image */}
                                        <div className="relative rounded-[2.5rem] overflow-hidden border border-white/10 shadow-2xl shadow-slate-950/50">
                                            <img src={IMG.hero} alt="Clinical specialist at CarePulse" className="w-full aspect-[4/5] object-cover hover:scale-[1.02] transition-transform duration-700" loading="eager" />
                                            <div className="absolute inset-0 bg-gradient-to-t from-dark via-transparent to-transparent opacity-60"></div>
                                        </div>

                                        {/* HUD A: Operational Stats */}
                                        <div className="absolute -left-12 top-20 glass-panel-dark rounded-2xl p-4.5 shadow-2xl shadow-dark/50 animate-float-1 w-52">
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary border border-secondary/20">
                                                    <i className="fa-solid fa-chart-pie text-sm"></i>
                                                </div>
                                                <div>
                                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Queue status</p>
                                                    <p className="text-sm font-black text-white">Stable (0-5m wait)</p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* HUD B: Specialist Vitals */}
                                        <div className="absolute -right-12 top-[45%] glass-panel-dark rounded-2xl p-4.5 shadow-2xl shadow-dark/50 animate-float-2 w-52">
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary-light border border-primary/20">
                                                    <i className="fa-solid fa-user-doctor text-sm"></i>
                                                </div>
                                                <div>
                                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active Staff</p>
                                                    <p className="text-sm font-black text-white">500+ Specialists</p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* HUD C: Realtime Patient Feeds */}
                                        <div className="absolute -left-8 bottom-12 glass-panel-dark rounded-2xl p-4.5 shadow-2xl shadow-dark/50 animate-float-3 w-52">
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20">
                                                    <i className="fa-solid fa-heart-circle-check text-sm animate-pulse-soft"></i>
                                                </div>
                                                <div>
                                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Clinical Vitals</p>
                                                    <p className="text-sm font-black text-white">100% Online HUD</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </FadeIn>
                            </div>

                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Stats Counter Component ─── */
        function Stats() {
            const items = [
                { num: 25, suf: '+', label: 'Years Experience', icon: 'fa-award', c: 'text-primary-light' },
                { num: 500, suf: '+', label: 'Expert Doctors', icon: 'fa-user-doctor', c: 'text-secondary-light' },
                { num: 1, suf: 'M+', label: 'Happy Patients', icon: 'fa-face-smile', c: 'text-amber-400' },
                { num: 50, suf: 'K+', label: 'Surgeries Done', icon: 'fa-hand-holding-medical', c: 'text-rose-400' },
                { num: 100, suf: '%', label: 'Operational Uptime', icon: 'fa-microchip', c: 'text-emerald-400' },
            ];
            return (
                <section className="relative z-20 -mt-10 lg:-mt-12">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="glass-panel-dark rounded-[2rem] p-1.5 shadow-2xl shadow-dark/30">
                            <div className="bg-dark/65 rounded-[1.8rem] p-8 lg:p-10 border border-white/5">
                                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 lg:gap-4">
                                    {items.map((s, i) => <Stat key={i} {...s} delay={i * 100} />)}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        function Stat({ num, suf, label, icon, delay, c }) {
            const [ref, count] = useCounter(num);
            return (
                <FadeIn ref={ref} delay={delay} className="text-center group">
                    <div className="w-12 h-12 mx-auto mb-4 rounded-xl bg-white/5 border border-white/5 group-hover:border-primary/20 flex items-center justify-center transition-all duration-300">
                        <i className={`fa-solid ${icon} ${c} text-lg`}></i>
                    </div>
                    <p className="font-outfit font-black text-3xl sm:text-4xl text-white tracking-tight tabular-nums">
                        {count}{suf}
                    </p>
                    <p className="text-slate-400 text-xs font-semibold uppercase tracking-wider mt-1.5">
                        {label}
                    </p>
                </FadeIn>
            );
        }

        /* ─── Wave Section Divider ─── */
        function Wave({ color = '#f4f7fc', flip = false }) {
            return (
                <div className={`w-full overflow-hidden leading-[0] ${flip ? 'rotate-180' : ''}`} style={{ marginTop: '-1.5px', marginBottom: '-1.5px' }}>
                    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" className="w-full h-[35px] sm:h-[55px] lg:h-[75px]">
                        <path d="M0,50 C240,10 480,80 720,40 C960,0 1200,70 1440,30 L1440,80 L0,80 Z" fill={color} />
                    </svg>
                </div>
            );
        }

        /* ─── About Us Component ─── */
        function About() {
            return (
                <section id="about" className="pt-10 pb-20 bg-[#f4f7fc] relative overflow-hidden">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid lg:grid-cols-12 gap-12 lg:gap-20 items-center">
                            
                            {/* Left Overlapping Images Canvas */}
                            <div className="lg:col-span-5 relative">
                                <FadeIn dir="left">
                                    <div className="relative max-w-md mx-auto sm:mx-0">
                                        <div className="absolute -top-5 -left-5 w-24 h-24 rounded-2xl border-2 border-secondary/20 -z-10 animate-float-1"></div>
                                        <div className="absolute -bottom-5 -right-5 w-20 h-20 rounded-full bg-secondary/5 -z-10 animate-float-2"></div>
                                        
                                        {/* Main Large Image */}
                                        <img src={IMG.about} alt="CarePulse Clinical Corridor" className="rounded-3xl shadow-2xl shadow-slate-900/10 w-full object-cover aspect-[4/3] relative z-[1] border border-white" loading="lazy" />
                                        
                                        {/* Floating Badge overlay */}
                                        <div className="absolute -bottom-8 -right-6 sm:-right-8 bg-gradient-to-br from-dark to-slate-900 border border-white/10 rounded-2xl shadow-2xl p-6 z-[2] animate-float-3 text-left w-56">
                                            <div className="flex items-center gap-4">
                                                <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/20 flex-shrink-0">
                                                    <i className="fa-solid fa-trophy text-white text-lg"></i>
                                                </div>
                                                <div>
                                                    <p className="font-outfit font-black text-2xl text-white">25+</p>
                                                    <p className="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Years of Trust</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </FadeIn>
                            </div>

                            {/* Right Content */}
                            <div className="lg:col-span-7 text-left">
                                <FadeIn>
                                    <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                        About CarePulse
                                    </span>
                                    <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-6 leading-tight">
                                        A Legacy of Clinical Excellence & Innovation
                                    </h2>
                                    <p className="text-slate-500 leading-relaxed mb-9 text-base">
                                        Founded over two decades ago, CarePulse has evolved from a local health clinic into a comprehensive, multi-specialty clinical hub. We have earned the trust of over a million patients by integrating board-certified expertise with advanced diagnostics and digitized patient tracking.
                                    </p>
                                </FadeIn>

                                {/* Mission and Vision Cards */}
                                <div className="grid sm:grid-cols-2 gap-5 mb-10">
                                    {[
                                        { t: 'Our Mission', d: 'Deliver accessible, premium-quality healthcare that improves lives through clinical innovation, cutting-edge tech, and genuine patient compassion.', ic: 'fa-bullseye', g: 'bg-primary/5 border-primary/20 text-primary' },
                                        { t: 'Our Vision', d: 'Be the most reliable healthcare ecosystem globally, leading in precision diagnostics, telemedicine access, and patient satisfaction.', ic: 'fa-eye', g: 'bg-secondary/5 border-secondary/20 text-secondary' }
                                    ].map((m, i) => (
                                        <FadeIn key={i} delay={i * 150}>
                                            <div className="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow h-full flex flex-col items-start text-left border-glow-hover">
                                                <div className={`w-11 h-11 rounded-xl ${m.g} border flex items-center justify-center mb-4 shadow-sm`}>
                                                    <i className={`fa-solid ${m.ic} text-base`}></i>
                                                </div>
                                                <h3 className="font-outfit font-bold text-dark text-base mb-2">{m.t}</h3>
                                                <p className="text-slate-500 text-xs leading-relaxed">{m.d}</p>
                                            </div>
                                        </FadeIn>
                                    ))}
                                </div>

                                <FadeIn delay={300}>
                                    <div className="flex flex-wrap gap-2.5">
                                        {['Joint Commission Accredited', 'Modern Medical Equipments', 'Specialist Doctor Registry', 'Affordable Clinical Care', '24/7 Hotline Support'].map(t => (
                                            <span key={t} className="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-500 hover:border-primary/25 hover:text-primary transition-colors cursor-default">
                                                <i className="fa-solid fa-check text-secondary text-[9px] w-3 h-3 rounded-full bg-secondary/10 flex items-center justify-center"></i>{t}
                                            </span>
                                        ))}
                                    </div>
                                </FadeIn>
                            </div>

                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Departments Section Component ─── */
        function Departments() {
            const gridSpans = [
                'lg:col-span-4 md:col-span-6',
                'lg:col-span-4 md:col-span-6',
                'lg:col-span-4 md:col-span-6',
                'lg:col-span-6 md:col-span-6',
                'lg:col-span-6 md:col-span-6',
                'lg:col-span-4 md:col-span-6',
                'lg:col-span-4 md:col-span-6',
                'lg:col-span-4 md:col-span-12'
            ];
            
            return (
                <section id="departments" className="py-20 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-16">
                            <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                Areas of Expertise
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-4 leading-tight">
                                Medical Departments
                            </h2>
                            <p className="text-slate-500">
                                Comprehensive specialized clinical setups staffed by highly experienced medical practitioners dedicated to optimal outcomes.
                            </p>
                        </FadeIn>

                        <div className="grid grid-cols-12 gap-5">
                            {depts.map((d, i) => (
                                <FadeIn key={d.name} delay={i * 80} className={gridSpans[i]}>
                                    <div className={`group rounded-[2rem] p-8 h-full transition-all duration-300 border-glow-hover flex flex-col justify-between text-left ${i === 7 ? 'bg-gradient-to-br from-primary to-secondary text-white border-0 shadow-lg shadow-primary/10' : 'bg-[#f4f7fc]/50 hover:bg-white border border-slate-200/60 shadow-sm'}`}>
                                        <div>
                                            <div className={`w-14 h-14 rounded-2xl flex items-center justify-center mb-6 shadow-sm border ${i === 7 ? 'bg-white/15 border-white/10 text-white' : `${d.c} border-current/10`}`}>
                                                <i className={`fa-solid ${d.icon} text-xl`}></i>
                                            </div>
                                            <h3 className={`font-outfit font-black text-xl mb-3 ${i === 7 ? 'text-white' : 'text-dark'}`}>
                                                {d.name}
                                            </h3>
                                            <p className={`text-xs leading-relaxed mb-6 ${i === 7 ? 'text-white/80' : 'text-slate-500'}`}>
                                                {d.desc}
                                            </p>
                                        </div>
                                        <button 
                                            onClick={() => document.getElementById('contact').scrollIntoView({ behavior: 'smooth' })}
                                            className={`inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider transition-colors ${i === 7 ? 'text-white hover:text-white/80' : 'text-primary hover:text-primary-dark'}`}
                                        >
                                            Consult Department <i className="fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1.5"></i>
                                        </button>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Doctors Component (Dark Editorial Showcase) ─── */
        function Doctors() {
            return (
                <section id="doctors" className="py-24 bg-dark relative overflow-hidden">
                    {/* Glowing Mesh Orbs */}
                    <div className="absolute top-0 right-0 w-[450px] h-[450px] bg-primary/5 rounded-full blur-[110px]"></div>
                    <div className="absolute bottom-0 left-0 w-[450px] h-[450px] bg-secondary/5 rounded-full blur-[110px]"></div>
                    
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-16">
                            <span className="text-secondary font-bold text-xs tracking-[0.25em] uppercase">
                                Expert Practitioners
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-white mt-2.5 mb-4 leading-tight">
                                Meet Our Doctors
                            </h2>
                            <p className="text-slate-400">
                                Leading clinical professionals with years of research, board-certifications, and a unified patient-centered commitment.
                            </p>
                        </FadeIn>

                        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            {docs.map((d, i) => (
                                <FadeIn key={d.name} delay={i * 120}>
                                    <div className="group bg-dark-light rounded-[2rem] overflow-hidden border border-white/5 hover:border-primary/30 transition-all duration-500 hover:shadow-2xl hover:shadow-primary/5 flex flex-col justify-between">
                                        <div className="relative aspect-[3/4] overflow-hidden">
                                            <img src={d.img} alt={d.name} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" />
                                            <div className="absolute inset-0 bg-gradient-to-t from-dark via-transparent to-transparent opacity-80"></div>
                                            
                                            {/* Custom Rating Badge */}
                                            <div className="absolute top-4 right-4 glass-panel-dark rounded-xl px-3 py-1.5 flex items-center gap-1.5">
                                                <i className="fa-solid fa-star text-amber-400 text-xs"></i>
                                                <span className="text-white text-xs font-bold">{d.rating}</span>
                                            </div>

                                            {/* Editorial Slide-Up Actions Panel */}
                                            <div className="absolute inset-0 bg-primary/80 opacity-0 group-hover:opacity-100 transition-opacity duration-400 backdrop-blur-md flex flex-col items-center justify-center p-6">
                                                <button 
                                                    onClick={() => document.getElementById('contact').scrollIntoView({ behavior: 'smooth' })} 
                                                    className="bg-white text-primary px-6 py-3 rounded-xl font-bold text-xs hover:bg-slate-50 transition-colors shadow-lg mb-4 hover:scale-[1.02] active:scale-[0.98] transition-transform"
                                                >
                                                    Book Consultation
                                                </button>
                                                <div className="flex gap-2">
                                                    {['fa-brands fa-linkedin-in', 'fa-solid fa-envelope', 'fa-solid fa-phone'].map((ic, idx) => (
                                                        <button 
                                                            key={idx} 
                                                            className="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/30 flex items-center justify-center text-white transition-colors"
                                                            aria-label="Doctor Contact Link"
                                                        >
                                                            <i className={`${ic} text-xs`}></i>
                                                        </button>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="p-6 text-left">
                                            <h3 className="font-outfit font-black text-white text-base">
                                                {d.name}
                                            </h3>
                                            <p className="text-primary-light text-xs font-bold uppercase tracking-wider mt-1">
                                                {d.spec}
                                            </p>
                                            <div className="flex items-center gap-2 mt-4 pt-4 border-t border-white/5 text-[11px] text-slate-400 font-semibold uppercase tracking-wider">
                                                <i className="fa-solid fa-graduation-cap text-secondary"></i>
                                                {d.exp} Practice Exp.
                                            </div>
                                        </div>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Services Component (Bento Grid layout) ─── */
        function Services() {
            const getSpan = (s) => s.full ? 'lg:col-span-12' : s.big ? 'lg:col-span-8 md:col-span-6' : 'lg:col-span-4 md:col-span-6';
            return (
                <section id="services" className="py-20 bg-[#f4f7fc]">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-16">
                            <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                Care Capabilities
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-4 leading-tight">
                                Our Services
                            </h2>
                            <p className="text-slate-500">
                                Integrated medical services ranging from diagnostics to specialized clinical care, running on high-definition records.
                            </p>
                        </FadeIn>

                        <div className="grid grid-cols-1 md:grid-cols-12 gap-5">
                            {svcs.map((s, i) => {
                                const isImg = !!s.img;
                                return (
                                    <FadeIn key={s.title} delay={i * 60} className={getSpan(s)}>
                                        <div className={`group rounded-[2rem] overflow-hidden h-full relative transition-all duration-300 ${isImg ? 'min-h-[280px] shadow-sm' : 'p-8 bg-white border border-slate-200/50 shadow-sm'} ${s.full ? 'bg-gradient-to-br from-primary via-primary-dark to-secondary p-8 lg:p-12' : 'hover:shadow-xl'}`}>
                                            {isImg ? (
                                                <>
                                                    <img src={s.img} alt={s.title} className="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" loading="lazy" />
                                                    <div className="absolute inset-0 bg-gradient-to-r from-dark/90 via-dark/60 to-transparent"></div>
                                                    <div className="relative z-10 flex flex-col justify-end h-full p-6 sm:p-8 text-left">
                                                        <div className="w-12 h-12 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center mb-4 backdrop-blur-md">
                                                            <i className={`fa-solid ${s.icon} text-white text-lg`}></i>
                                                        </div>
                                                        <h3 className="font-outfit font-black text-white text-xl mb-2">{s.title}</h3>
                                                        <p className="text-white/80 text-xs leading-relaxed max-w-md">{s.desc}</p>
                                                    </div>
                                                </>
                                            ) : s.full ? (
                                                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 text-left">
                                                    <div className="flex flex-col md:flex-row items-start md:items-center gap-6">
                                                        <div className="w-16 h-16 rounded-2xl bg-white/15 border border-white/10 flex items-center justify-center flex-shrink-0 backdrop-blur-md text-white">
                                                            <i className={`fa-solid ${s.icon} text-2xl`}></i>
                                                        </div>
                                                        <div>
                                                            <h3 className="font-outfit font-black text-white text-2xl mb-2">{s.title}</h3>
                                                            <p className="text-white/80 text-xs leading-relaxed max-w-2xl">{s.desc}</p>
                                                        </div>
                                                    </div>
                                                    <button 
                                                        onClick={() => document.getElementById('contact').scrollIntoView({ behavior: 'smooth' })}
                                                        className="bg-white text-primary hover:bg-slate-50 px-6 py-3.5 rounded-xl text-xs font-bold shadow-lg flex-shrink-0 hover:scale-[1.02] transition-transform"
                                                    >
                                                        Launch Virtual Consultation
                                                    </button>
                                                </div>
                                            ) : (
                                                <div className="text-left flex flex-col justify-between h-full">
                                                    <div>
                                                        <div className="w-12 h-12 rounded-xl bg-primary/[0.07] border border-primary/10 flex items-center justify-center mb-6 group-hover:bg-primary group-hover:shadow-lg group-hover:shadow-primary/30 transition-all duration-300">
                                                            <i className={`fa-solid ${s.icon} text-primary text-lg group-hover:text-white transition-colors duration-300`}></i>
                                                        </div>
                                                        <h3 className="font-outfit font-bold text-dark text-base mb-2 group-hover:text-primary transition-colors">{s.title}</h3>
                                                        <p className="text-slate-500 text-xs leading-relaxed">{s.desc}</p>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </FadeIn>
                                );
                            })}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Why Choose Us Component (Visual Timeline) ─── */
        function WhyChooseUs() {
            return (
                <section className="py-20 bg-white relative overflow-hidden">
                    <div className="absolute top-0 left-0 w-[400px] h-[400px] bg-primary/[0.02] rounded-full blur-[100px]"></div>
                    
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                        <div className="grid lg:grid-cols-12 gap-12 lg:gap-20 items-start">
                            
                            {/* Sticky Left Editorial Header */}
                            <div className="lg:col-span-5 lg:sticky lg:top-28 text-left">
                                <FadeIn>
                                    <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                        Why Choose Us
                                    </span>
                                    <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-6 leading-tight">
                                        Setting Standards in Patient Care
                                    </h2>
                                    <p className="text-slate-500 leading-relaxed mb-9">
                                        We go beyond standard procedures. We establish deep relationship bridges with our patients to deliver diagnostic transparency and real-world treatment efficacy.
                                    </p>
                                </FadeIn>
                                
                                <FadeIn delay={200}>
                                    <div className="relative rounded-3xl overflow-hidden shadow-2xl shadow-slate-900/10 border border-slate-100">
                                        <img src={IMG.whyChoose} alt="CarePulse Operating Theater" className="w-full object-cover aspect-[4/3] hover:scale-102 transition-transform duration-700" loading="lazy" />
                                        <div className="absolute inset-0 bg-gradient-to-tr from-dark/60 via-transparent to-transparent"></div>
                                        <div className="absolute bottom-5 left-5 right-5 glass-panel-dark rounded-2xl px-5 py-4 border border-white/5">
                                            <p className="text-white font-bold text-sm">State-of-the-Art Operations</p>
                                            <p className="text-white/60 text-[11px] mt-0.5">Equipped with robotic and micro-surgical instruments</p>
                                        </div>
                                    </div>
                                </FadeIn>
                            </div>

                            {/* Right Vertical Interactive Timeline */}
                            <div className="lg:col-span-7 relative text-left">
                                <div className="absolute left-[29px] top-4 bottom-4 w-[2px] bg-gradient-to-b from-primary via-secondary to-transparent hidden lg:block"></div>
                                <div className="space-y-6">
                                    {whyItems.map((it, i) => (
                                        <FadeIn key={i} delay={i * 100}>
                                            <div className="group flex gap-6">
                                                <div className="hidden lg:flex flex-col items-center flex-shrink-0 relative z-10">
                                                    <div className="w-14 h-14 rounded-2xl bg-white border border-slate-200 group-hover:border-primary flex items-center justify-center font-outfit font-black text-sm text-slate-400 group-hover:text-primary transition-all duration-300 shadow-sm">
                                                        0{i + 1}
                                                    </div>
                                                </div>
                                                <div className="bg-[#f4f7fc]/50 hover:bg-white rounded-3xl p-6.5 border border-slate-200/50 hover:border-primary/20 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all flex-1">
                                                    <h3 className="font-outfit font-black text-dark text-base mb-2 group-hover:text-primary transition-colors">
                                                        {it.title}
                                                    </h3>
                                                    <p className="text-slate-500 text-xs leading-relaxed">
                                                        {it.desc}
                                                    </p>
                                                </div>
                                            </div>
                                        </FadeIn>
                                    ))}
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Testimonials Carousel Component ─── */
        function Testimonials() {
            const [active, setActive] = useState(0);
            const sliderRef = useRef(null);

            useEffect(() => {
                sliderRef.current = setInterval(() => {
                    setActive(prev => (prev + 1) % testis.length);
                }, 6000);
                return () => clearInterval(sliderRef.current);
            }, []);

            const manualSelect = (idx) => {
                setActive(idx);
                clearInterval(sliderRef.current);
                sliderRef.current = setInterval(() => {
                    setActive(prev => (prev + 1) % testis.length);
                }, 6000);
            };

            const t = testis[active];

            return (
                <section id="testimonials" className="py-24 bg-[#f4f7fc]">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-16">
                            <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                Verified Testimonials
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-4 leading-tight">
                                What Our Patients Say
                            </h2>
                            <p className="text-slate-500">
                                Real clinical recovery narratives shared by patients who received treatments inside our multi-specialty setups.
                            </p>
                        </FadeIn>

                        <FadeIn>
                            <div className="max-w-3xl mx-auto">
                                <div className="relative bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-900/5 p-8 sm:p-14 text-left">
                                    <div className="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/25">
                                        <i className="fa-solid fa-quote-left text-white text-base"></i>
                                    </div>
                                    
                                    <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6 mb-8">
                                        <div className="w-20 h-20 rounded-2xl overflow-hidden shadow-md flex-shrink-0 ring-4 ring-primary/10">
                                            <img src={t.img} alt={t.name} className="w-full h-full object-cover" />
                                        </div>
                                        <div className="text-center sm:text-left">
                                            <div className="flex items-center justify-center sm:justify-start gap-1 mb-2.5">
                                                {Array.from({ length: 5 }).map((_, j) => (
                                                    <i key={j} className={`fa-solid fa-star text-xs ${j < t.rating ? 'text-amber-400' : 'text-slate-200'}`}></i>
                                                ))}
                                            </div>
                                            <p className="font-outfit font-black text-dark text-lg">{t.name}</p>
                                            <p className="text-slate-400 text-xs font-semibold tracking-wider uppercase">Verified Recovery</p>
                                        </div>
                                    </div>
                                    
                                    <p className="text-slate-600 text-[17px] leading-relaxed italic">
                                        "{t.text}"
                                    </p>
                                    
                                    {/* Slider Controls */}
                                    <div className="flex items-center justify-between mt-10 pt-8 border-t border-slate-100">
                                        <button 
                                            onClick={() => manualSelect((active - 1 + testis.length) % testis.length)} 
                                            className="w-11 h-11 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all"
                                            aria-label="Previous Testimonial"
                                        >
                                            <i className="fa-solid fa-chevron-left text-xs"></i>
                                        </button>
                                        <div className="flex gap-2">
                                            {testis.map((_, idx) => (
                                                <button 
                                                    key={idx} 
                                                    onClick={() => manualSelect(idx)} 
                                                    className={`h-2.5 rounded-full transition-all duration-300 ${idx === active ? 'w-8 bg-primary' : 'w-2.5 bg-slate-200 hover:bg-slate-300'}`}
                                                    aria-label={`Testimonial ${idx + 1}`}
                                                ></button>
                                            ))}
                                        </div>
                                        <button 
                                            onClick={() => manualSelect((active + 1) % testis.length)} 
                                            className="w-11 h-11 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all"
                                            aria-label="Next Testimonial"
                                        >
                                            <i className="fa-solid fa-chevron-right text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </FadeIn>
                    </div>
                </section>
            );
        }

        /* ─── Ready to Consult Banner Component ─── */
        function AppointmentCTA() {
            return (
                <section className="py-24 relative overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-br from-primary-dark via-primary to-secondary animate-glow-shift"></div>
                    <div className="absolute inset-0 opacity-[0.06]" style={{ backgroundImage: 'radial-gradient(circle at 10% 50%, white 1px, transparent 1px), radial-gradient(circle at 90% 10%, white 1px, transparent 1px), radial-gradient(circle at 50% 90%, white 1px, transparent 1px)', backgroundSize: '70px 70px' }}></div>
                    
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                        <FadeIn className="mb-14">
                            <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md rounded-full px-4.5 py-2 mb-6 border border-white/10">
                                <i className="fa-solid fa-sparkles text-amber-300 text-xs animate-pulse-soft"></i>
                                <span className="text-white text-xs font-bold uppercase tracking-wider">Appointment Center</span>
                            </div>
                            <h2 className="font-outfit font-black text-3xl sm:text-5xl text-white mb-5 leading-tight">
                                Ready to Consult Our Specialists?
                            </h2>
                            <p className="text-white/70 text-base max-w-xl mx-auto">
                                Schedule your physical checkout or secure virtual consultation today. We process submissions within 2 hours.
                            </p>
                        </FadeIn>

                        <div className="grid sm:grid-cols-3 gap-5 max-w-3xl mx-auto">
                            {[
                                { icon: 'fa-calendar-check', label: 'Online Scheduler', sub: 'Fill standard form below', act: () => document.getElementById('contact').scrollIntoView({ behavior: 'smooth' }) },
                                { icon: 'fa-phone', label: 'Call Patient Services', sub: '+1 (555) 234-5678', act: () => window.location.href = 'tel:+15552345678' },
                                { icon: 'fa-envelope', label: 'Email Help Desk', sub: 'care@carepulse.com', act: () => window.location.href = 'mailto:care@carepulse.com' }
                            ].map((c, i) => (
                                <FadeIn key={i} delay={i * 120}>
                                    <button 
                                        onClick={c.act} 
                                        className="group w-full bg-white/[0.06] hover:bg-white/[0.12] border border-white/10 hover:border-white/20 rounded-2xl p-6.5 text-left hover:-translate-y-1 transition-all duration-300"
                                    >
                                        <div className="w-12 h-12 rounded-xl bg-white/10 group-hover:scale-105 transition-all flex items-center justify-center mb-4 text-white">
                                            <i className={`fa-solid ${c.icon} text-lg`}></i>
                                        </div>
                                        <p className="text-white font-bold mb-1 text-sm">{c.label}</p>
                                        <p className="text-white/50 text-[11px] font-medium tracking-wide">{c.sub}</p>
                                    </button>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Frequently Asked Questions Component ─── */
        function FAQ() {
            const [openIndex, setOpenIndex] = useState(null);
            const toggleFaq = (idx) => setOpenIndex(openIndex === idx ? null : idx);
            
            return (
                <section className="py-20 bg-white">
                    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center mb-16">
                            <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                Help & Support
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-4 leading-tight">
                                Frequently Asked Questions
                            </h2>
                            <p className="text-slate-500">
                                Clear answers about insurance coverage, diagnostic report accesses, and booking mechanisms.
                            </p>
                        </FadeIn>

                        <div className="space-y-4">
                            {faqs.map((f, i) => (
                                <FadeIn key={i} delay={i * 50}>
                                    <div className={`rounded-2xl overflow-hidden transition-all duration-300 ${openIndex === i ? 'bg-primary/[0.02] border-primary/20 shadow-lg shadow-primary/5' : 'bg-[#f4f7fc]/40 hover:bg-[#f4f7fc]/80 border-slate-100'} border text-left`}>
                                        <button 
                                            onClick={() => toggleFaq(i)} 
                                            className="w-full flex items-center justify-between p-5 text-left gap-4" 
                                            aria-expanded={openIndex === i}
                                        >
                                            <span className={`font-outfit font-bold text-sm sm:text-base transition-colors ${openIndex === i ? 'text-primary' : 'text-dark'}`}>
                                                {f.q}
                                            </span>
                                            <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors ${openIndex === i ? 'bg-primary text-white' : 'bg-slate-200/60 text-slate-400'}`}>
                                                <i className={`fa-solid fa-plus faq-plus text-xs ${openIndex === i ? 'open text-white' : ''}`}></i>
                                            </div>
                                        </button>
                                        <div className={`faq-body ${openIndex === i ? 'open' : ''}`}>
                                            <p className="px-5 pb-5 text-slate-500 text-xs sm:text-sm leading-relaxed border-t border-slate-100/50 pt-4">
                                                {f.a}
                                            </p>
                                        </div>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Contact & Scheduling Component ─── */
        function Contact() {
            const [form, setForm] = useState({ name: '', email: '', phone: '', dept: '', msg: '' });
            const [submitting, setSubmitting] = useState(false);
            const [success, setSuccess] = useState(false);
            
            const updateField = (key, val) => setForm(prev => ({ ...prev, [key]: val }));
            
            const handleFormSubmit = (e) => {
                e.preventDefault();
                setSubmitting(true);
                // Simulate backend transmission delay
                setTimeout(() => {
                    setSubmitting(false);
                    setSuccess(true);
                    setForm({ name: '', email: '', phone: '', dept: '', msg: '' });
                    setTimeout(() => setSuccess(false), 5000);
                }, 1500);
            };

            return (
                <section id="contact" className="py-24 bg-[#f4f7fc] relative overflow-hidden">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-16">
                            <span className="text-primary font-bold text-xs tracking-[0.25em] uppercase">
                                Connect with Us
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-dark mt-2.5 mb-4 leading-tight">
                                Contact & Appointment Booking
                            </h2>
                            <p className="text-slate-500">
                                Send a message or request a specific physician consultation. We will coordinate details with you.
                            </p>
                        </FadeIn>

                        <div className="grid lg:grid-cols-12 gap-10 items-stretch">
                            {/* Left Side: Booking Form */}
                            <div className="lg:col-span-7 flex">
                                <FadeIn className="w-full flex">
                                    <div className="bg-white rounded-[2rem] border border-slate-200/60 shadow-2xl shadow-slate-900/5 p-6 sm:p-10 w-full text-left">
                                        {success ? (
                                            <div className="text-center py-20">
                                                <div className="w-16 h-16 mx-auto mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500">
                                                    <i className="fa-solid fa-check-double text-2xl"></i>
                                                </div>
                                                <h3 className="font-outfit font-black text-dark text-xl mb-3">Appointment Requested</h3>
                                                <p className="text-slate-500 text-sm max-w-sm mx-auto">
                                                    Your clinical routing record is generated. Our scheduling nurse will contact you within 2 hours.
                                                </p>
                                            </div>
                                        ) : (
                                            <form onSubmit={handleFormSubmit}>
                                                <h3 className="font-outfit font-black text-dark text-xl mb-6">
                                                    Request Patient Diagnostics Slot
                                                </h3>
                                                
                                                <div className="grid sm:grid-cols-2 gap-5 mb-5">
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Patient Full Name</label>
                                                        <input 
                                                            type="text" 
                                                            required 
                                                            value={form.name} 
                                                            onChange={e => updateField('name', e.target.value)} 
                                                            className="w-full px-4 py-3.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all bg-[#f4f7fc]/50 focus:bg-white" 
                                                            placeholder="John Doe" 
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Email Address</label>
                                                        <input 
                                                            type="email" 
                                                            required 
                                                            value={form.email} 
                                                            onChange={e => updateField('email', e.target.value)} 
                                                            className="w-full px-4 py-3.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all bg-[#f4f7fc]/50 focus:bg-white" 
                                                            placeholder="johndoe@email.com" 
                                                        />
                                                    </div>
                                                </div>

                                                <div className="grid sm:grid-cols-2 gap-5 mb-5">
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Mobile Number</label>
                                                        <input 
                                                            type="tel" 
                                                            required 
                                                            value={form.phone} 
                                                            onChange={e => updateField('phone', e.target.value)} 
                                                            className="w-full px-4 py-3.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all bg-[#f4f7fc]/50 focus:bg-white" 
                                                            placeholder="+1 (555) 000-0000" 
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Clinical Department</label>
                                                        <div className="relative">
                                                            <select 
                                                                required 
                                                                value={form.dept} 
                                                                onChange={e => updateField('dept', e.target.value)} 
                                                                className="w-full px-4 py-3.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all bg-[#f4f7fc]/50 focus:bg-white appearance-none"
                                                            >
                                                                <option value="">Select Specialty</option>
                                                                {depts.map(d => <option key={d.name} value={d.name}>{d.name}</option>)}
                                                            </select>
                                                            <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                                                <i className="fa-solid fa-chevron-down text-xs"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="mb-6">
                                                    <label className="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Brief Clinical Concern / History Notes</label>
                                                    <textarea 
                                                        rows="3" 
                                                        value={form.msg} 
                                                        onChange={e => updateField('msg', e.target.value)} 
                                                        className="w-full px-4 py-3.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary transition-all resize-none bg-[#f4f7fc]/50 focus:bg-white" 
                                                        placeholder="Describe symptoms, requirements, or preferred diagnostic slot timings..."
                                                    ></textarea>
                                                </div>

                                                <button 
                                                    type="submit" 
                                                    disabled={submitting}
                                                    className="w-full bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white py-4 rounded-xl font-bold shadow-xl shadow-primary/20 hover:shadow-primary/35 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2.5"
                                                >
                                                    {submitting ? (
                                                        <>
                                                            <i className="fa-solid fa-circle-notch animate-spin"></i> Processing Request...
                                                        </>
                                                    ) : (
                                                        <>
                                                            <i className="fa-regular fa-paper-plane text-sm"></i> Submit Request
                                                        </>
                                                    )}
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                </FadeIn>
                            </div>

                            {/* Right Side: Emergency Contact Info & Map */}
                            <div className="lg:col-span-5 flex flex-col justify-between gap-5">
                                <FadeIn delay={100} className="w-full h-full flex flex-col gap-5">
                                    {/* Glass Contact Info Cards */}
                                    <div className="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm p-6 text-left space-y-6 flex-1 flex flex-col justify-center">
                                        <h3 className="font-outfit font-black text-dark text-lg mb-2">Hospital Directory</h3>
                                        
                                        <div className="space-y-5">
                                            {[
                                                { icon: 'fa-location-dot', cl: 'bg-primary/10 text-primary border-primary/20', lb: 'Main Address', val: '1234 Healthcare Boulevard, Medical District, New York, NY 10001' },
                                                { icon: 'fa-phone', cl: 'bg-secondary/10 text-secondary border-secondary/20', lb: 'Direct Helpdesk', val: '+1 (555) 234-5678', link: 'tel:+15552345678' },
                                                { icon: 'fa-envelope', cl: 'bg-primary/10 text-primary border-primary/20', lb: 'Direct Email Support', val: 'care@carepulse.com', link: 'mailto:care@carepulse.com' },
                                                { icon: 'fa-clock', cl: 'bg-secondary/10 text-secondary border-secondary/20', lb: 'OPD Hours', val: 'Mon – Sat: 8:00 AM – 8:00 PM\nSunday: Emergency Only' },
                                            ].map((c, idx) => (
                                                <div key={idx} className="flex gap-4 items-start">
                                                    <div className={`w-10 h-10 rounded-xl ${c.cl} border flex items-center justify-center flex-shrink-0 shadow-sm`}>
                                                        <i className={`fa-solid ${c.icon} text-sm`}></i>
                                                    </div>
                                                    <div>
                                                        <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{c.lb}</p>
                                                        {c.link ? (
                                                            <a href={c.link} className="text-sm font-semibold text-dark hover:text-primary transition-colors">{c.val}</a>
                                                        ) : (
                                                            <p className="text-sm font-semibold text-dark whitespace-pre-line leading-relaxed">{c.val}</p>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Emergency Hotline Panel */}
                                    <div className="bg-gradient-to-br from-red-600 to-rose-700 rounded-[2rem] p-6 text-white shadow-xl shadow-red-600/25 relative overflow-hidden text-left">
                                        <div className="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
                                        <div className="relative">
                                            <div className="flex items-center gap-3 mb-3">
                                                <div className="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm shadow-md">
                                                    <i className="fa-solid fa-truck-medical text-lg"></i>
                                                </div>
                                                <div>
                                                    <p className="font-outfit font-black text-sm text-white">Emergency Trauma Line</p>
                                                    <p className="text-white/70 text-xs">Available 24/7/365</p>
                                                </div>
                                            </div>
                                            <a href="tel:+1555911" className="font-outfit font-black text-3xl tracking-wide hover:text-white/80 transition-colors block">
                                                +1 (555) 911
                                            </a>
                                        </div>
                                    </div>

                                    {/* Leaflet/OpenStreetMap Map Area */}
                                    <div className="rounded-[2rem] overflow-hidden border border-slate-200 shadow-sm h-48 bg-slate-200 relative">
                                        <iframe 
                                            title="CarePulse Hospital Location Map" 
                                            src="https://www.openstreetmap.org/export/embed.html?bbox=-74.01,40.75,-73.97,40.77&layer=mapnik" 
                                            className="w-full h-full border-none filter grayscale opacity-90 hover:grayscale-0 transition-all duration-500" 
                                            loading="lazy"
                                        ></iframe>
                                    </div>
                                </FadeIn>
                            </div>
                        </div>

                    </div>
                </section>
            );
        }

        /* ─── Footer Section Component ─── */
        function Footer() {
            const scrollToElement = id => {
                document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
            };
            
            return (
                <footer className="bg-dark relative" role="contentinfo">
                    {/* Glowing Accent Line */}
                    <div className="h-[4px] bg-gradient-to-r from-primary via-secondary to-primary-light"></div>
                    
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 text-left">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8">
                            
                            {/* Column 1: Brand & Socials */}
                            <div className="lg:col-span-4 space-y-6">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                                        <i className="fa-solid fa-heart-pulse text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <span className="font-outfit font-black text-lg block leading-tight text-white uppercase">
                                            Care<span className="text-primary-light">Pulse</span>
                                        </span>
                                        <span className="text-[9px] font-bold text-slate-500 tracking-[0.25em] uppercase leading-none">
                                            Healthcare
                                        </span>
                                    </div>
                                </div>
                                <p className="text-slate-400 text-sm leading-relaxed max-w-sm">
                                    Establishing global standards in clinical safety, medical diagnostics, and digitized care records with unwavering patient commitment.
                                </p>
                                <div className="flex gap-2.5">
                                    {[
                                        { ic: 'fa-facebook-f', l: 'Facebook' }, 
                                        { ic: 'fa-x-twitter', l: 'Twitter' }, 
                                        { ic: 'fa-instagram', l: 'Instagram' }, 
                                        { ic: 'fa-linkedin-in', l: 'LinkedIn' }
                                    ].map(s => (
                                        <a 
                                            key={s.l} 
                                            href="#" 
                                            aria-label={s.l} 
                                            className="w-10 h-10 rounded-xl bg-white/5 border border-white/5 flex items-center justify-center text-slate-500 hover:bg-primary hover:border-primary hover:text-white hover:-translate-y-1 transition-all duration-300"
                                        >
                                            <i className={`fa-brands ${s.ic} text-xs`}></i>
                                        </a>
                                    ))}
                                </div>
                            </div>

                            {/* Column 2: Quick Links */}
                            <div className="lg:col-span-2.5 space-y-6">
                                <h4 className="font-outfit font-bold text-xs uppercase tracking-[0.2em] text-white">Quick Links</h4>
                                <ul className="space-y-3.5">
                                    {[
                                        ['Home Page', 'home'], 
                                        ['About CarePulse', 'about'], 
                                        ['Medical Specialties', 'departments'], 
                                        ['Expert Doctors', 'doctors'], 
                                        ['Offered Services', 'services'], 
                                        ['Patient Stories', 'testimonials'], 
                                        ['Get in Touch', 'contact']
                                    ].map(([l, id]) => (
                                        <li key={id}>
                                            <button 
                                                onClick={() => scrollToElement(id)} 
                                                className="text-slate-400 text-xs hover:text-primary-light transition-colors flex items-center gap-2 group"
                                            >
                                                <i className="fa-solid fa-chevron-right text-[8px] text-primary/30 group-hover:text-primary-light group-hover:translate-x-1 transition-all"></i>
                                                {l}
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Column 3: Specialties */}
                            <div className="lg:col-span-2.5 space-y-6">
                                <h4 className="font-outfit font-bold text-xs uppercase tracking-[0.2em] text-white">Clinical Specialties</h4>
                                <ul className="space-y-3.5">
                                    {depts.slice(0, 5).map(d => (
                                        <li key={d.name}>
                                            <button 
                                                onClick={() => scrollToElement('departments')} 
                                                className="text-slate-400 text-xs hover:text-primary-light transition-colors flex items-center gap-2 group"
                                            >
                                                <i className="fa-solid fa-chevron-right text-[8px] text-primary/30 group-hover:text-primary-light group-hover:translate-x-1 transition-all"></i>
                                                {d.name}
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Column 4: Contact Center */}
                            <div className="lg:col-span-3 space-y-6">
                                <h4 className="font-outfit font-bold text-xs uppercase tracking-[0.2em] text-white">Clinical Coordinates</h4>
                                <div className="space-y-4 text-slate-400 text-xs">
                                    <div className="flex gap-3">
                                        <i className="fa-solid fa-location-dot text-primary-light mt-0.5"></i>
                                        <p className="leading-relaxed">1234 Healthcare Blvd, Medical District, New York, NY 10001</p>
                                    </div>
                                    <div className="flex gap-3">
                                        <i className="fa-solid fa-phone text-primary-light mt-0.5"></i>
                                        <div>
                                            <a href="tel:+15552345678" className="hover:text-primary-light transition-colors block">+1 (555) 234-5678</a>
                                            <a href="tel:+1555911" className="text-red-400 font-bold hover:text-red-300 transition-colors block mt-1">Trauma Emergency: +1 (555) 911</a>
                                        </div>
                                    </div>
                                    <div className="flex gap-3">
                                        <i className="fa-solid fa-envelope text-primary-light mt-0.5"></i>
                                        <a href="mailto:care@carepulse.com" className="hover:text-primary-light transition-colors">care@carepulse.com</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {/* Bottom Copyright Bar */}
                    <div className="border-t border-white/5">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p className="text-slate-500 text-xs">
                                &copy; {new Date().getFullYear()} CarePulse Health Systems. All rights reserved.
                            </p>
                            <div className="flex gap-6 text-xs text-slate-500">
                                {['Privacy Policy', 'Terms of Use', 'Sitemap Guidelines'].map(l => (
                                    <a key={l} href="#" className="hover:text-slate-300 transition-colors">{l}</a>
                                ))}
                            </div>
                        </div>
                    </div>
                </footer>
            );
        }

        /* ─── Scroll to Top Button Component ─── */
        function ScrollTop() {
            const y = useScrollY();
            return (
                <button 
                    onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })} 
                    className={`fixed bottom-6 right-6 z-50 w-12 h-12 rounded-2xl bg-primary text-white shadow-xl shadow-primary/25 flex items-center justify-center hover:bg-primary-dark hover:scale-105 active:scale-95 transition-all duration-300 ${y > 500 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6 pointer-events-none'}`} 
                    aria-label="Scroll back to top"
                >
                    <i className="fa-solid fa-arrow-up text-sm"></i>
                </button>
            );
        }

        /* ─── Main Application Container Component ─── */
        function App() {
            return (
                <div className="min-h-screen flex flex-col justify-between">
                    <ScrollProgress />
                    <Navbar />
                    <main className="flex-grow">
                        <Hero />
                        <Stats />
                        <Wave color="#f4f7fc" />
                        <About />
                        <Wave color="#f4f7fc" flip />
                        <Departments />
                        <Doctors />
                        <Services />
                        <WhyChooseUs />
                        <Wave color="#f4f7fc" />
                        <Testimonials />
                        <AppointmentCTA />
                        <FAQ />
                        <Wave color="#f4f7fc" flip />
                        <Contact />
                    </main>
                    <Footer />
                    <ScrollTop />
                </div>
            );
        }

        ReactDOM.createRoot(document.getElementById('root')).render(<App />);
    </script>
</body>

</html>