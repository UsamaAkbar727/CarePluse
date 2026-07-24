<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Plus — Advanced Healthcare For a Better Tomorrow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#2563EB', light: '#3B82F6', dark: '#1D4ED8', '50': '#EFF6FF' },
                        secondary: { DEFAULT: '#14B8A6', light: '#2DD4BF', dark: '#0D9488', '50': '#F0FDFA' },
                        dark: { DEFAULT: '#0F172A', light: '#1E293B', lighter: '#334155' },
                    }
                }
            }
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth
        }

        ::selection {
            background: #2563EB;
            color: #fff
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden
        }

        @keyframes float1 {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-14px)
            }
        }

        @keyframes float2 {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-20px)
            }
        }

        @keyframes float3 {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-11px)
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(.9);
                opacity: .7
            }

            50% {
                transform: scale(1.15);
                opacity: .3
            }

            100% {
                transform: scale(.9);
                opacity: .7
            }
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1)
            }

            50% {
                opacity: .4;
                transform: scale(1.6)
            }
        }

        @keyframes grad-shift {
            0% {
                background-position: 0% 50%
            }

            50% {
                background-position: 100% 50%
            }

            100% {
                background-position: 0% 50%
            }
        }

        @keyframes slideR {
            from {
                transform: translateX(100%);
                opacity: 0
            }

            to {
                transform: translateX(0);
                opacity: 1
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0
            }

            100% {
                background-position: 200% 0
            }
        }

        .float-1 {
            animation: float1 4.5s ease-in-out infinite
        }

        .float-2 {
            animation: float2 5.5s ease-in-out infinite .4s
        }

        .float-3 {
            animation: float3 3.8s ease-in-out infinite .8s
        }

        .pulse-dot {
            animation: pulse-dot 1.8s ease-in-out infinite
        }

        .pulse-ring {
            animation: pulse-ring 2s ease-in-out infinite
        }

        .grad-animate {
            background-size: 200% 200%;
            animation: grad-shift 8s ease infinite
        }

        .slide-r {
            animation: slideR .3s ease-out
        }

        .glass {
            background: rgba(255, 255, 255, .7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, .4)
        }

        .glass-dark {
            background: rgba(15, 23, 42, .6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, .08)
        }

        .gborder {
            position: relative;
            z-index: 1
        }

        .gborder::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.5px;
            background: linear-gradient(135deg, #2563EB, #14B8A6);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity .35s ease;
            pointer-events: none;
            z-index: -1
        }

        .gborder:hover::after {
            opacity: 1
        }

        .dept-card {
            transition: all .4s cubic-bezier(.4, 0, .2, 1)
        }

        .dept-card:hover {
            transform: translateY(-8px)
        }

        .dept-card:hover .dept-icon {
            transform: scale(1.15) rotate(6deg)
        }

        .dept-icon {
            transition: all .4s cubic-bezier(.4, 0, .2, 1)
        }

        .doc-card:hover .doc-img {
            transform: scale(1.08)
        }

        .doc-card:hover .doc-ov {
            opacity: 1
        }

        .doc-img {
            transition: transform .6s cubic-bezier(.4, 0, .2, 1)
        }

        .doc-ov {
            transition: opacity .35s ease
        }

        .svc-card {
            transition: all .35s cubic-bezier(.4, 0, .2, 1)
        }

        .svc-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, .1)
        }

        .tl-dot {
            transition: all .3s ease
        }

        .tl-item:hover .tl-dot {
            background: #2563EB;
            transform: scale(1.4);
            box-shadow: 0 0 0 8px rgba(37, 99, 235, .12)
        }

        .faq-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height .4s ease, padding .3s ease
        }

        .faq-body.open {
            max-height: 300px
        }

        .faq-chev {
            transition: transform .3s ease
        }

        .faq-chev.open {
            transform: rotate(180deg)
        }

        .scroll-btn {
            transition: all .3s ease
        }

        .scroll-btn.vis {
            opacity: 1;
            transform: translateY(0)
        }

        .scroll-btn.hid {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none
        }

        .img-skel {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite
        }

        @media(max-width:1024px) {
            .hero-float {
                display: none
            }
        }
    </style>
</head>

<body class="bg-white text-slate-600">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect, useRef, useCallback } = React;

        /* ─── Images ─── */
        const IMG = {
            hero: 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=620&h=780&fit=crop&q=80',
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
            emerg: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=640&h=400&fit=crop&q=80',
            surgery: 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=640&h=400&fit=crop&q=80',
        };

        /* ─── Hooks ─── */
        function useInView(opts = {}) {
            const ref = useRef(null), [v, setV] = useState(false);
            useEffect(() => { const el = ref.current; if (!el) return; const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { setV(true); o.unobserve(el) } }, { threshold: .1, ...opts }); o.observe(el); return () => o.disconnect() }, []);
            return [ref, v];
        }
        function useCounter(target, dur = 2000) {
            const [c, setC] = useState(0), [ref, iv] = useInView(), ran = useRef(false);
            useEffect(() => { if (iv && !ran.current) { ran.current = true; const s = performance.now(); const step = (n) => { const p = Math.min((n - s) / dur, 1), e = 1 - Math.pow(1 - p, 3); setC(Math.floor(e * target)); if (p < 1) requestAnimationFrame(step) }; requestAnimationFrame(step) } }, [iv, target, dur]);
            return [ref, c];
        }
        function useScrollY() { const [y, setY] = useState(0); useEffect(() => { const h = () => setY(window.scrollY); window.addEventListener('scroll', h, { passive: true }); return () => window.removeEventListener('scroll', h) }, []); return y }

        /* ─── FadeIn ─── */
        function FadeIn({ children, className = '', delay = 0, dir = 'up' }) {
            const [ref, iv] = useInView();
            const t = { up: 'translate-y-10', left: '-translate-x-8', right: 'translate-x-8', none: '' };
            return <div ref={ref} className={`transition-all duration-700 ease-out ${iv ? 'opacity-100 translate-x-0 translate-y-0' : `opacity-0 ${t[dir]}`} ${className}`} style={{ transitionDelay: `${delay}ms` }}>{children}</div>;
        }

        /* ─── Data ─── */
        const depts = [
            { name: 'Cardiology', icon: 'fa-heart-pulse', desc: 'Advanced cardiac diagnostics, interventional procedures, and comprehensive heart care from leading cardiologists.', c: 'bg-rose-50 text-rose-500' },
            { name: 'Neurology', icon: 'fa-brain', desc: 'Expert treatment for stroke, epilepsy, neurodegenerative diseases with state-of-the-art neurological imaging.', c: 'bg-violet-50 text-violet-500' },
            { name: 'Orthopedics', icon: 'fa-bone', desc: 'Joint replacements, sports medicine, and fracture care using minimally invasive surgical techniques.', c: 'bg-sky-50 text-sky-500' },
            { name: 'Pediatrics', icon: 'fa-baby', desc: 'Child-friendly comprehensive care from newborn screening to adolescent medicine by dedicated specialists.', c: 'bg-pink-50 text-pink-500' },
            { name: 'Gynecology', icon: 'fa-venus', desc: 'Women\'s health including prenatal care, fertility treatments, and advanced laparoscopic surgeries.', c: 'bg-teal-50 text-teal-500' },
            { name: 'Dermatology', icon: 'fa-hand-dots', desc: 'Medical and cosmetic dermatology with laser therapy, skin cancer screening, and aesthetic procedures.', c: 'bg-amber-50 text-amber-500' },
            { name: 'General Medicine', icon: 'fa-stethoscope', desc: 'Primary healthcare with thorough diagnostics and personalized treatment plans for diverse conditions.', c: 'bg-emerald-50 text-emerald-500' },
            { name: 'Emergency Care', icon: 'fa-truck-medical', desc: '24/7 rapid-response trauma center with fully equipped resuscitation bays and critical care access.', c: 'bg-red-50 text-red-500' },
        ];
        const docs = [
            { name: 'Dr. Sarah Mitchell', spec: 'Cardiologist', exp: '18 Years', rating: 4.9, img: IMG.doc1 },
            { name: 'Dr. James Rodriguez', spec: 'Neurologist', exp: '15 Years', rating: 4.8, img: IMG.doc2 },
            { name: 'Dr. Emily Chen', spec: 'Orthopedic Surgeon', exp: '12 Years', rating: 4.9, img: IMG.doc3 },
            { name: 'Dr. Michael Patel', spec: 'Pediatrician', exp: '20 Years', rating: 4.7, img: IMG.doc4 },
        ];
        const svcs = [
            { title: 'Emergency Care', icon: 'fa-kit-medical', desc: '24/7 trauma services with rapid-response teams, resuscitation bays, and direct ICU access for critical patients.', img: IMG.emerg, big: true },
            { title: 'Intensive Care Unit', icon: 'fa-bed-pulse', desc: 'Advanced ICUs with real-time monitoring, ventilator support, and round-the-clock intensivist coverage.', big: false },
            { title: 'Diagnostic Laboratory', icon: 'fa-flask-vial', desc: 'NABL-accredited lab with 2000+ tests, molecular diagnostics, and rapid turnaround times.', big: false },
            { title: '24/7 Pharmacy', icon: 'fa-prescription-bottle-medical', desc: 'In-house pharmacy with automated dispensing, essential medications, and pharmacist consultation.', big: false },
            { title: 'Ambulance Services', icon: 'fa-truck-medical', desc: 'GPS-tracked advanced life-support ambulances with trained paramedics and hospital communication.', big: false },
            { title: 'Radiology & Imaging', icon: 'fa-x-ray', desc: '3T MRI, 128-slice CT, digital X-ray, ultrasound, and PET-CT for precise diagnostic accuracy.', big: false },
            { title: 'Advanced Surgery', icon: 'fa-syringe', desc: 'Robotic and minimally invasive surgery across specialties ensuring faster recovery and minimal scarring.', img: IMG.surgery, big: true },
            { title: 'Online Consultation', icon: 'fa-laptop-medical', desc: 'Video consultations with specialists, e-prescriptions, and digital health records — all from home.', big: false, full: true },
        ];
        const whyItems = [
            { title: 'Board-Certified Specialists', desc: 'Every physician is board-certified with training from world-renowned medical institutions and years of clinical experience.' },
            { title: 'Cutting-Edge Technology', desc: 'From robotic surgery to AI-assisted diagnostics, we invest in the latest medical technology for superior outcomes.' },
            { title: 'Patient-Centered Approach', desc: 'Treatment plans tailored to your lifestyle, preferences, and well-being — because you are more than your diagnosis.' },
            { title: 'Research & Innovation', desc: 'Active clinical trials and research programs keep us at the forefront of medical breakthroughs and new treatments.' },
            { title: 'Seamless Continuity of Care', desc: 'From prevention through rehabilitation, integrated care across all specialties ensures nothing falls through the cracks.' },
        ];
        const testis = [
            { name: 'Rebecca Thompson', text: 'The cardiology team at MediCare Plus saved my life. From the emergency room to recovery, every moment was handled with incredible professionalism and genuine compassion. I will never forget their kindness.', rating: 5, img: IMG.pat1 },
            { name: 'David Kim', text: 'After years of chronic back pain, Dr. Chen performed a minimally invasive spine surgery. I was walking the next day and fully recovered within weeks. The orthopedic team here is absolutely world-class.', rating: 5, img: IMG.pat2 },
            { name: 'Susan Clarke', text: 'The maternity care exceeded all expectations. The team made what could have been stressful feel safe, comfortable, and even joyful. I recommend their gynecology department to every expecting mother.', rating: 5, img: IMG.pat3 },
            { name: 'Robert Hayes', text: 'Online consultation was a game-changer during my recovery. Speaking with my neurologist from home while getting thorough medical advice is the future of healthcare. Brilliant system.', rating: 4, img: IMG.pat4 },
        ];
        const faqs = [
            { q: 'How do I book an appointment?', a: 'Book online through our website by clicking "Book Appointment", call +1 (555) 234-5678, or visit our reception desk. Online booking is available 24/7 with instant confirmation and reminders.' },
            { q: 'What insurance plans do you accept?', a: 'We accept most major plans including Blue Cross Blue Shield, Aetna, UnitedHealthcare, Cigna, and Medicare. Our billing team verifies coverage before your visit at no charge.' },
            { q: 'Is emergency care available 24/7?', a: 'Yes. Our Emergency Department operates 24/7/365 with dedicated trauma surgeons, emergency physicians, and critical care specialists on-site at all times. Average wait time under 15 minutes.' },
            { q: 'How can I access my medical records?', a: 'Through our secure patient portal, access records, lab results, imaging reports, and prescriptions anytime. Physical copies are available from our Medical Records department with 48-hour turnaround.' },
            { q: 'Do you offer international patient services?', a: 'Yes. Our International Patient Services team assists with visa support, airport pickup, accommodation, language interpretation, and full care coordination for patients traveling from abroad.' },
            { q: 'What are your visiting hours?', a: 'General visiting: 10 AM – 8 PM daily. ICU: 30-minute slots twice daily (11 AM & 5 PM). Maximum two visitors per patient. Children under 12 may be restricted in certain areas for safety.' },
        ];

        /* ─── Scroll Progress ─── */
        function ScrollProgress() {
            const y = useScrollY();
            const h = typeof document !== 'undefined' ? document.documentElement.scrollHeight - window.innerHeight : 1;
            const pct = h > 0 ? (y / h) * 100 : 0;
            return <div className="fixed top-0 left-0 right-0 z-[60] h-[3px]"><div className="h-full bg-gradient-to-r from-primary via-secondary to-primary-light" style={{ width: `${pct}%`, transition: 'width .1s linear' }}></div></div>;
        }

        /* ─── Navbar ─── */
        function Navbar() {
            const scrollY = useScrollY(), [mob, setMob] = useState(false);
            const sc = scrollY > 50;
            const links = ['Home', 'About', 'Departments', 'Doctors', 'Services', 'Testimonials', 'Contact'];
            useEffect(() => { document.body.style.overflow = mob ? 'hidden' : ''; return () => { document.body.style.overflow = '' } }, [mob]);
            const go = (id) => { setMob(false); document.getElementById(id.toLowerCase())?.scrollIntoView({ behavior: 'smooth' }) };
            return (
                <>
                    <nav className={`fixed top-0.5 left-0 right-0 z-50 transition-all duration-500 ${sc ? 'top-0' : 'top-3'}`} role="navigation" aria-label="Main navigation">
                        <div className={`max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-500 ${sc ? 'py-2' : 'py-3'}`}>
                            <div className={`flex items-center justify-between transition-all duration-500 rounded-2xl px-5 py-2.5 ${sc ? 'bg-white/80 backdrop-blur-2xl shadow-xl shadow-slate-900/5 border border-white/60' : 'bg-white/40 backdrop-blur-xl border border-white/30'}`}>
                                <a href="#" className="flex items-center gap-2.5 group" aria-label="Home">
                                    <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/40 transition-shadow">
                                        <i className="fa-solid fa-heart-pulse text-white text-lg"></i>
                                    </div>
                                    <div className="hidden sm:block">
                                        <span className="font-extrabold text-[17px] leading-tight block text-dark">MediCare<span className="text-primary">Plus</span></span>
                                        <span className="text-[9px] font-semibold text-slate-400 tracking-[.2em] uppercase leading-none">Healthcare</span>
                                    </div>
                                </a>
                                <div className="hidden lg:flex items-center gap-0.5">
                                    {links.map(l => <button key={l} onClick={() => go(l)} className="px-3.5 py-2 rounded-xl text-[13px] font-medium text-slate-500 hover:text-primary hover:bg-primary/5 transition-all">{l}</button>)}
                                </div>
                                <div className="flex items-center gap-3">
                                    <button onClick={() => go('Contact')} className="hidden lg:flex items-center gap-2 bg-gradient-to-r from-primary to-primary-dark text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:scale-[1.03] active:scale-[0.97] transition-all">
                                        <i className="fa-regular fa-calendar-check text-xs"></i> Book Appointment
                                    </button>
                                    <button className="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center hover:bg-slate-100/80 transition-colors" onClick={() => setMob(true)} aria-label="Open menu">
                                        <i className="fa-solid fa-bars text-slate-600"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </nav>
                    {mob && (
                        <div className="fixed inset-0 z-[60]">
                            <div className="absolute inset-0 bg-dark/40 backdrop-blur-sm" onClick={() => setMob(false)} style={{ animation: 'fadeUp .2s ease' }}></div>
                            <div className="absolute right-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl slide-r flex flex-col">
                                <div className="flex items-center justify-between p-5 border-b border-slate-100">
                                    <span className="font-bold text-dark text-sm">Menu</span>
                                    <button onClick={() => setMob(false)} className="w-9 h-9 rounded-lg flex items-center justify-center hover:bg-slate-50 transition-colors" aria-label="Close menu"><i className="fa-solid fa-xmark text-slate-400"></i></button>
                                </div>
                                <div className="flex-1 overflow-y-auto py-2 px-3">
                                    {links.map(l => <button key={l} onClick={() => go(l)} className="w-full text-left px-4 py-3 rounded-xl text-slate-600 font-medium text-sm hover:bg-primary/5 hover:text-primary transition-colors">{l}</button>)}
                                </div>
                                <div className="p-4 border-t border-slate-100">
                                    <button onClick={() => go('Contact')} className="w-full bg-gradient-to-r from-primary to-primary-dark text-white py-3 rounded-xl font-semibold text-sm">Book Appointment</button>
                                </div>
                            </div>
                        </div>
                    )}
                </>
            );
        }

        /* ─── Hero ─── */
        function Hero() {
            return (
                <section id="home" className="relative min-h-screen flex items-center overflow-hidden">
                    <div className="absolute top-0 right-0 w-[700px] h-[700px] bg-primary/[.04] rounded-full blur-[100px] -z-10"></div>
                    <div className="absolute bottom-0 left-0 w-[500px] h-[500px] bg-secondary/[.04] rounded-full blur-[100px] -z-10"></div>
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] border border-slate-100/60 rounded-full -z-10"></div>
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] border border-slate-100/40 rounded-full -z-10"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-24 pb-16">
                        <div className="grid lg:grid-cols-2 gap-14 lg:gap-8 items-center">
                            <div className="max-w-xl">
                                <FadeIn>
                                    <div className="inline-flex items-center gap-2.5 bg-primary/[.06] border border-primary/10 rounded-full px-4 py-2 mb-7">
                                        <span className="relative flex h-2.5 w-2.5"><span className="pulse-ring absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span><span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary"></span></span>
                                        <span className="text-xs font-bold text-primary tracking-wide uppercase">Trusted by 1M+ Patients</span>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={120}>
                                    <h1 className="text-[2.6rem] sm:text-[3.2rem] lg:text-[3.7rem] font-extrabold text-dark leading-[1.08] tracking-tight mb-6">
                                        Advanced Healthcare For a{' '}
                                        <span className="relative inline-block">
                                            <span className="bg-gradient-to-r from-primary via-primary-light to-secondary bg-clip-text text-transparent">Better Tomorrow</span>
                                            <svg className="absolute -bottom-2 left-0 w-full" viewBox="0 0 300 14" fill="none"><path d="M2 10C60 2 140 2 160 8C180 14 260 4 298 10" stroke="#14B8A6" strokeWidth="3" strokeLinecap="round" opacity=".35" /></svg>
                                        </span>
                                    </h1>
                                </FadeIn>
                                <FadeIn delay={240}>
                                    <p className="text-slate-500 text-lg leading-relaxed mb-9 max-w-md">World-class medical care with specialized doctors, cutting-edge technology, and a compassionate approach that puts your well-being above all else.</p>
                                </FadeIn>
                                <FadeIn delay={360}>
                                    <div className="flex flex-wrap gap-4 mb-11">
                                        <button onClick={() => document.getElementById('Contact').scrollIntoView({ behavior: 'smooth' })} className="group bg-gradient-to-r from-primary to-primary-dark text-white px-7 py-4 rounded-2xl font-semibold shadow-xl shadow-primary/20 hover:shadow-primary/35 hover:scale-[1.03] active:scale-[0.97] transition-all flex items-center gap-2.5">
                                            <i className="fa-regular fa-calendar-check text-sm"></i> Book Appointment
                                            <i className="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                                        </button>
                                        <a href="tel:+15552345678" className="group bg-white border-2 border-slate-200 text-dark px-7 py-4 rounded-2xl font-semibold hover:border-red-300 hover:text-red-600 hover:bg-red-50/50 transition-all flex items-center gap-2.5">
                                            <span className="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center group-hover:bg-red-100 transition-colors"><i className="fa-solid fa-phone-volume text-red-500 text-sm"></i></span>
                                            Emergency
                                        </a>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={480}>
                                    <div className="flex flex-wrap items-center gap-x-6 gap-y-3">
                                        {[['JCI Accredited', 'fa-shield-halved'], ['NABH Certified', 'fa-certificate'], ['ISO 9001:2015', 'fa-award']].map(([t, ic], i) => (
                                            <div key={t} className="flex items-center gap-2">
                                                <i className={`fa-solid ${ic} text-secondary text-sm`}></i>
                                                <span className="text-xs font-semibold text-slate-400">{t}</span>
                                                {i < 2 && <span className="w-px h-4 bg-slate-200 hidden sm:block"></span>}
                                            </div>
                                        ))}
                                    </div>
                                </FadeIn>
                            </div>

                            <div className="relative hidden lg:block">
                                <FadeIn delay={200} dir="right">
                                    <div className="relative max-w-md mx-auto">
                                        <div className="absolute -inset-6 bg-gradient-to-br from-primary/8 to-secondary/8 rounded-[2rem] blur-2xl"></div>
                                        <div className="relative rounded-3xl overflow-hidden shadow-2xl shadow-slate-900/10">
                                            <img src={IMG.hero} alt="Doctor examining patient at MediCare Plus" className="w-full aspect-[4/5] object-cover" loading="eager" />
                                            <div className="absolute inset-0 bg-gradient-to-t from-dark/30 via-transparent to-transparent"></div>
                                        </div>
                                    </div>
                                </FadeIn>
                                <div className="hero-float absolute -left-8 top-20 glass rounded-2xl p-4 float-1 shadow-xl shadow-slate-900/5">
                                    <div className="flex items-center gap-3">
                                        <div className="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center"><i className="fa-solid fa-phone-volume text-red-500"></i></div>
                                        <div><p className="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">24/7 Emergency</p><p className="text-sm font-extrabold text-dark">+1 (555) 911</p></div>
                                    </div>
                                </div>
                                <div className="hero-float absolute -right-6 top-[45%] glass rounded-2xl p-4 float-2 shadow-xl shadow-slate-900/5">
                                    <div className="flex items-center gap-3">
                                        <div className="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center"><i className="fa-solid fa-user-doctor text-primary"></i></div>
                                        <div><p className="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Specialists</p><p className="text-sm font-extrabold text-dark">500+ Doctors</p></div>
                                    </div>
                                </div>
                                <div className="hero-float absolute -left-4 bottom-20 glass rounded-2xl p-4 float-3 shadow-xl shadow-slate-900/5">
                                    <div className="flex items-center gap-3">
                                        <div className="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center"><i className="fa-solid fa-chart-line text-emerald-500"></i></div>
                                        <div><p className="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Success Rate</p><p className="text-sm font-extrabold text-dark">98.5%</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Stats ─── */
        function Stats() {
            const items = [
                { num: 25, suf: '+', label: 'Years Experience', icon: 'fa-award' },
                { num: 500, suf: '+', label: 'Expert Doctors', icon: 'fa-user-doctor' },
                { num: 1, suf: 'M+', label: 'Happy Patients', icon: 'fa-face-smile' },
                { num: 50, suf: 'K+', label: 'Surgeries Done', icon: 'fa-hand-holding-medical' },
                { num: 24, suf: '/7', label: 'Emergency Ready', icon: 'fa-clock' },
            ];
            return (
                <section className="relative z-10 -mt-6">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="bg-gradient-to-r from-primary-dark via-primary to-secondary rounded-2xl lg:rounded-3xl p-2 shadow-2xl shadow-primary/20">
                            <div className="bg-gradient-to-r from-primary-dark via-primary to-secondary rounded-xl lg:rounded-2xl p-6 sm:p-8 lg:p-10">
                                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 lg:gap-4">
                                    {items.map((s, i) => <Stat key={i} {...s} delay={i * 80} />)}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }
        function Stat({ num, suf, label, icon, delay }) {
            const [ref, c] = useCounter(num);
            return (
                <FadeIn ref={ref} delay={delay} className="text-center">
                    <div className="w-11 h-11 mx-auto mb-3 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm"><i className={`fa-solid ${icon} text-white`}></i></div>
                    <p className="text-2xl sm:text-3xl font-extrabold text-white tabular-nums">{c}{suf}</p>
                    <p className="text-white/60 text-xs font-medium mt-0.5">{label}</p>
                </FadeIn>
            );
        }

        /* ─── Wave Divider ─── */
        function Wave({ color = '#f8fafc', flip = false }) {
            return (
                <div className={`w-full overflow-hidden leading-[0] ${flip ? 'rotate-180' : ''}`} style={{ marginTop: '-1px' }}>
                    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" className="w-full h-[40px] sm:h-[60px] lg:h-[80px]">
                        <path d="M0,50 C240,10 480,80 720,40 C960,0 1200,70 1440,30 L1440,80 L0,80 Z" fill={color} />
                    </svg>
                </div>
            );
        }

        /* ─── About ─── */
        function About() {
            return (
                <section id="about" className="pt-8 pb-20 lg:pb-28 bg-slate-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                            <FadeIn dir="left">
                                <div className="relative">
                                    <div className="absolute -top-5 -left-5 w-28 h-28 rounded-2xl border-2 border-secondary/20 -z-10"></div>
                                    <div className="absolute -bottom-5 -right-5 w-20 h-20 rounded-full bg-secondary/10 -z-10"></div>
                                    <img src={IMG.about} alt="Modern hospital corridor at MediCare Plus" className="rounded-2xl shadow-xl w-full object-cover aspect-[4/3] relative z-[1]" loading="lazy" />
                                    <div className="absolute -bottom-6 -right-4 sm:-right-6 glass rounded-2xl shadow-xl p-5 z-[2]">
                                        <div className="flex items-center gap-3">
                                            <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/20"><i className="fa-solid fa-trophy text-white text-xl"></i></div>
                                            <div><p className="text-2xl font-extrabold text-dark">25+</p><p className="text-[11px] text-slate-400 font-semibold">Years of Excellence</p></div>
                                        </div>
                                    </div>
                                </div>
                            </FadeIn>
                            <div>
                                <FadeIn>
                                    <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">About Our Hospital</span>
                                    <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-5 leading-tight">A Legacy of Excellence in Healthcare</h2>
                                    <p className="text-slate-500 leading-relaxed mb-7">Founded over two decades ago, MediCare Plus has grown from a single clinic into one of the region's most comprehensive multi-specialty hospitals, earning the trust of over a million patients through unwavering commitment to clinical excellence.</p>
                                </FadeIn>
                                <div className="grid sm:grid-cols-2 gap-4 mb-8">
                                    {[{ t: 'Our Mission', d: 'Provide accessible, high-quality healthcare that improves lives through innovation, expertise, and genuine compassion for every patient.', ic: 'fa-bullseye', g: 'from-primary to-primary-light' }, { t: 'Our Vision', d: 'Be the most trusted healthcare institution globally, recognized for clinical outcomes, research, and exceptional patient experience.', ic: 'fa-eye', g: 'from-secondary to-secondary-light' }].map((m, i) => (
                                        <FadeIn key={i} delay={i * 120}>
                                            <div className="bg-white rounded-xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow h-full gborder">
                                                <div className={`w-10 h-10 rounded-lg bg-gradient-to-br ${m.g} flex items-center justify-center mb-3 shadow-sm`}><i className={`fa-solid ${m.ic} text-white text-sm`}></i></div>
                                                <h3 className="font-bold text-dark text-sm mb-1.5">{m.t}</h3>
                                                <p className="text-slate-500 text-sm leading-relaxed">{m.d}</p>
                                            </div>
                                        </FadeIn>
                                    ))}
                                </div>
                                <FadeIn delay={240}>
                                    <div className="flex flex-wrap gap-2.5">
                                        {['Accredited Facility', 'Modern Equipment', 'Expert Team', 'Affordable Care', '24/7 Support'].map(t => (
                                            <span key={t} className="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 rounded-lg px-3.5 py-2 text-xs font-semibold text-slate-500 hover:border-primary/20 hover:text-primary transition-colors cursor-default">
                                                <i className="fa-solid fa-check text-secondary text-[9px]"></i>{t}
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

        /* ─── Departments ─── */
        function Departments() {
            const spans = ['sm:col-span-2', 'sm:col-span-1', 'sm:col-span-1', 'sm:col-span-2', 'sm:col-span-1', 'sm:col-span-1', 'sm:col-span-1', 'sm:col-span-3'];
            return (
                <section id="departments" className="py-20 lg:py-28">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">Our Specialties</span>
                            <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-4 leading-tight">Medical Departments</h2>
                            <p className="text-slate-500">Comprehensive specialties staffed by experienced professionals dedicated to exceptional patient outcomes.</p>
                        </FadeIn>
                        <div className="grid sm:grid-cols-3 gap-4 lg:gap-5">
                            {depts.map((d, i) => (
                                <FadeIn key={d.name} delay={i * 60} className={spans[i]}>
                                    <div className={`dept-card group rounded-2xl p-6 h-full gborder ${i === 7 ? 'bg-gradient-to-r from-primary to-secondary text-white border-0' : 'bg-white border border-slate-100'}`}>
                                        <div className={`dept-icon w-[52px] h-[52px] rounded-xl ${i === 7 ? 'bg-white/15' : d.c} flex items-center justify-center mb-4`}><i className={`fa-solid ${d.icon} text-xl ${i === 7 ? 'text-white' : ''}`}></i></div>
                                        <h3 className={`font-bold text-lg mb-2 ${i === 7 ? 'text-white' : 'text-dark'}`}>{d.name}</h3>
                                        <p className={`text-sm leading-relaxed mb-4 ${i === 7 ? 'text-white/80' : 'text-slate-500'}`}>{d.desc}</p>
                                        <button className={`inline-flex items-center gap-1.5 text-sm font-semibold transition-colors ${i === 7 ? 'text-white/90 hover:text-white' : 'text-primary hover:text-primary-dark'}`}>
                                            Learn More <i className="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                        </button>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Doctors (Dark Section) ─── */
        function Doctors() {
            return (
                <section id="doctors" className="py-20 lg:py-28 bg-dark relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px]"></div>
                    <div className="absolute bottom-0 left-0 w-[400px] h-[400px] bg-secondary/5 rounded-full blur-[120px]"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="text-secondary font-bold text-xs tracking-[.2em] uppercase">Expert Team</span>
                            <h2 className="text-3xl sm:text-4xl font-extrabold text-white mt-2.5 mb-4 leading-tight">Meet Our Doctors</h2>
                            <p className="text-slate-400">Leaders in their fields with decades of combined experience and a shared commitment to your health.</p>
                        </FadeIn>
                        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            {docs.map((d, i) => (
                                <FadeIn key={d.name} delay={i * 100}>
                                    <div className="doc-card group bg-dark-light rounded-2xl overflow-hidden border border-white/5 hover:border-primary/30 transition-all duration-400">
                                        <div className="relative aspect-[4/5] overflow-hidden">
                                            <img src={d.img} alt={d.name} className="doc-img w-full h-full object-cover" loading="lazy" />
                                            <div className="absolute inset-0 bg-gradient-to-t from-dark via-dark/30 to-transparent"></div>
                                            <div className="doc-ov absolute inset-0 bg-primary/70 flex items-center justify-center opacity-0 backdrop-blur-sm">
                                                <div className="text-center">
                                                    <button onClick={() => document.getElementById('Contact').scrollIntoView({ behavior: 'smooth' })} className="bg-white text-primary px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-colors shadow-lg mb-3 block mx-auto">Book Appointment</button>
                                                    <div className="flex items-center justify-center gap-3">
                                                        {['fa-brands fa-linkedin-in', 'fa-solid fa-envelope', 'fa-solid fa-phone'].map((ic, j) => <button key={j} className="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-white hover:bg-white/25 transition-colors text-xs"><i className={ic}></i></button>)}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="absolute bottom-3.5 left-4 right-4">
                                                <div className="flex items-center gap-0.5">
                                                    {Array.from({ length: 5 }).map((_, j) => <i key={j} className={`fa-solid fa-star text-[10px] ${j < Math.floor(d.rating) ? 'text-amber-400' : 'text-white/15'}`}></i>)}
                                                    <span className="text-white/50 text-[11px] font-medium ml-1.5">{d.rating}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="p-4">
                                            <h3 className="font-bold text-white text-[15px]">{d.name}</h3>
                                            <p className="text-primary-light text-sm font-medium">{d.spec}</p>
                                            <p className="text-slate-500 text-xs font-medium mt-0.5">{d.exp} Experience</p>
                                        </div>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Services (Bento Grid) ─── */
        function Services() {
            const getSpan = (s) => s.full ? 'sm:col-span-3' : s.big ? 'sm:col-span-2' : 'sm:col-span-1';
            return (
                <section id="services" className="py-20 lg:py-28 bg-slate-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">What We Offer</span>
                            <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-4 leading-tight">Our Services</h2>
                            <p className="text-slate-500">Comprehensive medical services from routine checkups to complex surgical interventions, all under one roof.</p>
                        </FadeIn>
                        <div className="grid sm:grid-cols-3 gap-4 lg:gap-5">
                            {svcs.map((s, i) => {
                                const isImg = !!s.img;
                                return (
                                    <FadeIn key={s.title} delay={i * 60} className={getSpan(s)}>
                                        <div className={`svc-card group rounded-2xl overflow-hidden h-full relative ${isImg ? 'min-h-[240px]' : 'p-6 bg-white border border-slate-100'} ${s.full ? 'bg-gradient-to-r from-primary to-secondary p-8 lg:p-10' : 'hover:shadow-xl'}`}>
                                            {isImg ? (
                                                <>
                                                    <img src={s.img} alt={s.title} className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" />
                                                    <div className={`absolute inset-0 ${s.full ? 'bg-gradient-to-r from-primary/90 to-secondary/90' : 'bg-gradient-to-t from-dark/80 via-dark/50 to-dark/20'}`}></div>
                                                    <div className="relative z-[1] flex flex-col justify-end h-full p-6 lg:p-8">
                                                        <div className="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center mb-3 backdrop-blur-sm"><i className={`fa-solid ${s.icon} text-white text-lg`}></i></div>
                                                        <h3 className="text-white font-bold text-lg lg:text-xl mb-2">{s.title}</h3>
                                                        <p className="text-white/75 text-sm leading-relaxed max-w-md">{s.desc}</p>
                                                    </div>
                                                </>
                                            ) : s.full ? (
                                                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                                                    <div className="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center flex-shrink-0 backdrop-blur-sm"><i className={`fa-solid ${s.icon} text-white text-xl`}></i></div>
                                                    <div><h3 className="text-white font-bold text-xl mb-2">{s.title}</h3><p className="text-white/80 text-sm leading-relaxed max-w-lg">{s.desc}</p></div>
                                                </div>
                                            ) : (
                                                <>
                                                    <div className="w-12 h-12 rounded-xl bg-primary/[.07] flex items-center justify-center mb-4 group-hover:bg-primary group-hover:shadow-lg group-hover:shadow-primary/20 transition-all duration-300"><i className={`fa-solid ${s.icon} text-primary text-lg group-hover:text-white transition-colors duration-300`}></i></div>
                                                    <h3 className="font-bold text-dark text-base mb-2">{s.title}</h3>
                                                    <p className="text-slate-500 text-sm leading-relaxed">{s.desc}</p>
                                                </>
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

        /* ─── Why Choose Us ─── */
        function WhyChooseUs() {
            return (
                <section className="py-20 lg:py-28 relative overflow-hidden">
                    <div className="absolute top-0 left-0 w-[500px] h-[500px] bg-primary/[.03] rounded-full blur-[100px]"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">
                            <div className="lg:sticky lg:top-28">
                                <FadeIn>
                                    <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">Why MediCare Plus</span>
                                    <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-5 leading-tight">Reasons to Choose Our Hospital</h2>
                                    <p className="text-slate-500 leading-relaxed mb-8">We go beyond treating illness — we build lasting relationships based on trust, transparency, and relentless pursuit of better outcomes.</p>
                                </FadeIn>
                                <FadeIn delay={200}>
                                    <div className="relative rounded-2xl overflow-hidden shadow-xl">
                                        <img src={IMG.whyChoose} alt="State-of-the-art operating room" className="w-full object-cover aspect-[3/2]" loading="lazy" />
                                        <div className="absolute inset-0 bg-gradient-to-tr from-primary/40 via-transparent to-secondary/20"></div>
                                        <div className="absolute bottom-5 left-5 glass-dark rounded-xl px-5 py-3">
                                            <p className="text-white font-bold text-sm">State-of-the-Art Facilities</p>
                                            <p className="text-white/60 text-xs">Equipped with the latest medical technology</p>
                                        </div>
                                    </div>
                                </FadeIn>
                            </div>
                            <div className="relative">
                                <div className="absolute left-[19px] top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary via-secondary to-transparent hidden lg:block"></div>
                                <div className="space-y-6">
                                    {whyItems.map((it, i) => (
                                        <FadeIn key={i} delay={i * 100}>
                                            <div className="tl-item flex gap-5 lg:gap-6">
                                                <div className="hidden lg:flex flex-col items-center flex-shrink-0">
                                                    <div className="tl-dot w-10 h-10 rounded-full bg-primary/[.08] border-2 border-primary/20 flex items-center justify-center text-primary font-bold text-xs z-[1]">{i + 1}</div>
                                                </div>
                                                <div className="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-lg hover:shadow-slate-200/50 transition-all flex-1 gborder">
                                                    <h3 className="font-bold text-dark mb-2">{it.title}</h3>
                                                    <p className="text-slate-500 text-sm leading-relaxed">{it.desc}</p>
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

        /* ─── Testimonials ─── */
        function Testimonials() {
            const [a, setA] = useState(0), tRef = useRef(null);
            useEffect(() => { tRef.current = setInterval(() => setA(p => (p + 1) % testis.length), 5000); return () => clearInterval(tRef.current) }, []);
            const go = i => { setA(i); clearInterval(tRef.current); tRef.current = setInterval(() => setA(p => (p + 1) % testis.length), 5000) };
            const t = testis[a];
            return (
                <section id="testimonials" className="py-20 lg:py-28 bg-slate-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">Patient Stories</span>
                            <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-4 leading-tight">What Our Patients Say</h2>
                            <p className="text-slate-500">Real experiences from real patients who trusted us with their healthcare journey.</p>
                        </FadeIn>
                        <FadeIn>
                            <div className="max-w-3xl mx-auto">
                                <div className="relative bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 p-8 sm:p-12">
                                    <div className="absolute -top-5 left-1/2 -translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/25"><i className="fa-solid fa-quote-left text-white text-sm"></i></div>
                                    <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6 mb-6">
                                        <div className="w-20 h-20 rounded-2xl overflow-hidden shadow-lg flex-shrink-0 ring-2 ring-primary/10 ring-offset-2">
                                            <img src={t.img} alt={t.name} className="w-full h-full object-cover" />
                                        </div>
                                        <div className="text-center sm:text-left">
                                            <div className="flex items-center justify-center sm:justify-start gap-1 mb-2">
                                                {Array.from({ length: 5 }).map((_, j) => <i key={j} className={`fa-solid fa-star ${j < t.rating ? 'text-amber-400' : 'text-slate-200'}`}></i>)}
                                            </div>
                                            <p className="font-bold text-dark text-base">{t.name}</p>
                                            <p className="text-slate-400 text-sm">Verified Patient</p>
                                        </div>
                                    </div>
                                    <p className="text-slate-600 text-[17px] leading-relaxed italic text-center sm:text-left">"{t.text}"</p>
                                    <div className="flex items-center justify-center gap-2 mt-8">
                                        <button onClick={() => go((a - 1 + testis.length) % testis.length)} className="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all" aria-label="Previous"><i className="fa-solid fa-chevron-left text-sm"></i></button>
                                        <div className="flex items-center gap-2 mx-2">
                                            {testis.map((_, i) => <button key={i} onClick={() => go(i)} className={`rounded-full transition-all duration-300 ${i === a ? 'w-8 h-2.5 bg-primary' : 'w-2.5 h-2.5 bg-slate-200 hover:bg-slate-300'}`} aria-label={`Testimonial ${i + 1}`}></button>)}
                                        </div>
                                        <button onClick={() => go((a + 1) % testis.length)} className="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:border-primary hover:text-primary hover:bg-primary/5 transition-all" aria-label="Next"><i className="fa-solid fa-chevron-right text-sm"></i></button>
                                    </div>
                                </div>
                            </div>
                        </FadeIn>
                    </div>
                </section>
            );
        }

        /* ─── Appointment CTA ─── */
        function AppointmentCTA() {
            return (
                <section className="py-24 lg:py-32 relative overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-br from-primary-dark via-primary to-secondary grad-animate"></div>
                    <div className="absolute inset-0 opacity-[.07]" style={{ backgroundImage: 'radial-gradient(circle at 20% 50%,white 1px,transparent 1px),radial-gradient(circle at 80% 20%,white 1px,transparent 1px),radial-gradient(circle at 50% 80%,white 1px,transparent 1px)', backgroundSize: '60px 60px,80px 80px,70px 70px' }}></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                        <FadeIn className="text-center mb-14">
                            <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-2 mb-6 border border-white/10">
                                <i className="fa-solid fa-sparkles text-amber-300 text-xs"></i>
                                <span className="text-white/80 text-xs font-semibold uppercase tracking-wider">Start Your Journey</span>
                            </div>
                            <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5 leading-tight">Ready to Take the<br />Next Step?</h2>
                            <p className="text-white/60 text-lg max-w-xl mx-auto">Your health journey begins with a single conversation. Reach out today and let our experts guide you.</p>
                        </FadeIn>
                        <div className="grid sm:grid-cols-3 gap-5 max-w-3xl mx-auto">
                            {[
                                { icon: 'fa-calendar-check', label: 'Book Appointment', sub: 'Schedule your visit online', act: () => document.getElementById('Contact').scrollIntoView({ behavior: 'smooth' }) },
                                { icon: 'fa-phone', label: 'Call Now', sub: '+1 (555) 234-5678', act: () => window.location.href = 'tel:+15552345678' },
                                { icon: 'fa-envelope', label: 'Email Us', sub: 'care@medicareplus.com', act: () => window.location.href = 'mailto:care@medicareplus.com' },
                            ].map((c, i) => (
                                <FadeIn key={i} delay={i * 120}>
                                    <button onClick={c.act} className="group w-full bg-white/[.08] backdrop-blur-md border border-white/[.12] rounded-2xl p-6 text-left hover:bg-white/[.15] hover:border-white/[.25] hover:-translate-y-1 transition-all duration-300">
                                        <div className="w-12 h-12 rounded-xl bg-white/[.12] flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-white/[.18] transition-all"><i className={`fa-solid ${c.icon} text-white text-lg`}></i></div>
                                        <p className="text-white font-bold mb-1">{c.label}</p>
                                        <p className="text-white/50 text-sm">{c.sub}</p>
                                    </button>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── FAQ ─── */
        function FAQ() {
            const [o, setO] = useState(null);
            const tog = i => setO(o === i ? null : i);
            return (
                <section className="py-20 lg:py-28">
                    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center mb-14">
                            <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">Common Questions</span>
                            <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-4 leading-tight">Frequently Asked Questions</h2>
                            <p className="text-slate-500">Find answers to the most common questions about our services and policies.</p>
                        </FadeIn>
                        <div className="space-y-3">
                            {faqs.map((f, i) => (
                                <FadeIn key={i} delay={i * 40}>
                                    <div className={`rounded-2xl overflow-hidden transition-all duration-300 ${o === i ? 'bg-primary/[.03] border-primary/20 shadow-lg shadow-primary/5' : 'bg-white border-slate-100 hover:shadow-sm'} border`}>
                                        <button onClick={() => tog(i)} className="w-full flex items-center justify-between p-5 text-left gap-4" aria-expanded={o === i}>
                                            <span className={`font-semibold transition-colors ${o === i ? 'text-primary' : 'text-dark'}`}>{f.q}</span>
                                            <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors ${o === i ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400'}`}>
                                                <i className={`fa-solid fa-plus faq-chev text-xs ${o === i ? 'open' : ''}`}></i>
                                            </div>
                                        </button>
                                        <div className={`faq-body ${o === i ? 'open' : ''}`}>
                                            <p className="px-5 pb-5 text-slate-500 text-sm leading-relaxed">{f.a}</p>
                                        </div>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Contact ─── */
        function Contact() {
            const [f, setF] = useState({ name: '', email: '', phone: '', dept: '', msg: '' });
            const [ok, setOk] = useState(false);
            const u = (k, v) => setF(p => ({ ...p, [k]: v }));
            const sub = e => { e.preventDefault(); setOk(true); setTimeout(() => setOk(false), 4000) };
            return (
                <section id="contact" className="py-20 lg:py-28 bg-slate-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="text-primary font-bold text-xs tracking-[.2em] uppercase">Get In Touch</span>
                            <h2 className="text-3xl sm:text-4xl font-extrabold text-dark mt-2.5 mb-4 leading-tight">Contact Us</h2>
                            <p className="text-slate-500">Have questions or want to schedule a visit? We're here to help you every step of the way.</p>
                        </FadeIn>
                        <div className="grid lg:grid-cols-5 gap-8 lg:gap-10">
                            <div className="lg:col-span-3">
                                <FadeIn>
                                    <div className="bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/30 p-6 sm:p-8">
                                        {ok ? (
                                            <div className="text-center py-16">
                                                <div className="w-16 h-16 mx-auto mb-5 rounded-2xl bg-emerald-50 flex items-center justify-center"><i className="fa-solid fa-check text-emerald-500 text-2xl"></i></div>
                                                <h3 className="font-bold text-dark text-lg mb-2">Request Sent Successfully</h3>
                                                <p className="text-slate-500 text-sm max-w-sm mx-auto">Our team will contact you within 24 hours to confirm your appointment details.</p>
                                            </div>
                                        ) : (
                                            <form onSubmit={sub}>
                                                <h3 className="font-bold text-dark text-lg mb-6">Request an Appointment</h3>
                                                <div className="grid sm:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Full Name</label>
                                                        <input type="text" required value={f.name} onChange={e => u('name', e.target.value)} className="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/15 focus:border-primary transition-all bg-slate-50/50 focus:bg-white" placeholder="John Doe" />
                                                    </div>
                                                    <div>
                                                        <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Email Address</label>
                                                        <input type="email" required value={f.email} onChange={e => u('email', e.target.value)} className="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/15 focus:border-primary transition-all bg-slate-50/50 focus:bg-white" placeholder="john@email.com" />
                                                    </div>
                                                </div>
                                                <div className="grid sm:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Phone Number</label>
                                                        <input type="tel" required value={f.phone} onChange={e => u('phone', e.target.value)} className="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/15 focus:border-primary transition-all bg-slate-50/50 focus:bg-white" placeholder="+1 (555) 000-0000" />
                                                    </div>
                                                    <div>
                                                        <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Department</label>
                                                        <select required value={f.dept} onChange={e => u('dept', e.target.value)} className="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/15 focus:border-primary transition-all bg-slate-50/50 focus:bg-white appearance-none" style={{ backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2394a3b8\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M6 9l6 6 6-6\'/%3E%3C/svg%3E")', backgroundRepeat: 'no-repeat', backgroundPosition: 'right 14px center' }}>
                                                            <option value="">Select Department</option>
                                                            {depts.map(d => <option key={d.name} value={d.name}>{d.name}</option>)}
                                                        </select>
                                                    </div>
                                                </div>
                                                <div className="mb-6">
                                                    <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Message</label>
                                                    <textarea rows="3" value={f.msg} onChange={e => u('msg', e.target.value)} className="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/15 focus:border-primary transition-all resize-none bg-slate-50/50 focus:bg-white" placeholder="Describe your concern or preferred appointment time..."></textarea>
                                                </div>
                                                <button type="submit" className="w-full bg-gradient-to-r from-primary to-primary-dark text-white py-3.5 rounded-xl font-semibold shadow-xl shadow-primary/20 hover:shadow-primary/35 hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                                                    <i className="fa-regular fa-paper-plane text-sm"></i> Submit Appointment Request
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                </FadeIn>
                            </div>
                            <div className="lg:col-span-2 space-y-5">
                                <FadeIn delay={100}>
                                    <div className="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                                        <h3 className="font-bold text-dark mb-5">Contact Information</h3>
                                        <div className="space-y-5">
                                            {[
                                                { icon: 'fa-location-dot', cl: 'text-primary', lb: 'Address', val: '1234 Healthcare Boulevard\nMedical District, New York, NY 10001' },
                                                { icon: 'fa-phone', cl: 'text-secondary', lb: 'Phone', val: '+1 (555) 234-5678', link: 'tel:+15552345678' },
                                                { icon: 'fa-envelope', cl: 'text-primary', lb: 'Email', val: 'care@medicareplus.com', link: 'mailto:care@medicareplus.com' },
                                                { icon: 'fa-clock', cl: 'text-secondary', lb: 'Working Hours', val: 'Mon – Sat: 8:00 AM – 8:00 PM\nSun: 9:00 AM – 5:00 PM' },
                                            ].map((c, i) => (
                                                <div key={i} className="flex gap-3.5">
                                                    <div className="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center flex-shrink-0 border border-slate-100"><i className={`fa-solid ${c.icon} ${c.cl} text-sm`}></i></div>
                                                    <div>
                                                        <p className="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">{c.lb}</p>
                                                        {c.link ? <a href={c.link} className="text-sm font-medium text-dark hover:text-primary transition-colors">{c.val}</a> : <p className="text-sm font-medium text-dark whitespace-pre-line leading-relaxed">{c.val}</p>}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={200}>
                                    <div className="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-xl shadow-red-500/20 relative overflow-hidden">
                                        <div className="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
                                        <div className="absolute -right-2 -bottom-4 w-16 h-16 bg-white/5 rounded-full"></div>
                                        <div className="relative">
                                            <div className="flex items-center gap-3 mb-3">
                                                <div className="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm"><i className="fa-solid fa-truck-medical text-lg"></i></div>
                                                <div><p className="font-bold text-sm">Emergency Line</p><p className="text-white/60 text-xs">Available 24/7</p></div>
                                            </div>
                                            <a href="tel:+1555911" className="text-3xl font-extrabold tracking-wide hover:text-white/90 transition-colors block">+1 (555) 911</a>
                                        </div>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={300}>
                                    <div className="rounded-2xl overflow-hidden border border-slate-100 shadow-sm h-56 bg-slate-100">
                                        <iframe title="Hospital Location" src="https://www.openstreetmap.org/export/embed.html?bbox=-74.01,40.75,-73.97,40.77&layer=mapnik" className="w-full h-full border-0" loading="lazy"></iframe>
                                    </div>
                                </FadeIn>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        /* ─── Footer ─── */
        function Footer() {
            const go = id => { document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' }) };
            return (
                <footer className="bg-dark relative" role="contentinfo">
                    <div className="h-1 bg-gradient-to-r from-primary via-secondary to-primary-light"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                            <div>
                                <div className="flex items-center gap-2.5 mb-5">
                                    <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center"><i className="fa-solid fa-heart-pulse text-white text-lg"></i></div>
                                    <div><span className="font-extrabold text-[17px] block leading-tight text-white">MediCare<span className="text-primary-light">Plus</span></span><span className="text-[9px] font-semibold text-slate-500 tracking-[.2em] uppercase leading-none">Healthcare</span></div>
                                </div>
                                <p className="text-slate-400 text-sm leading-relaxed mb-6">World-class healthcare services with compassion, innovation, and unwavering commitment to every patient's well-being.</p>
                                <div className="flex gap-2">
                                    {[{ ic: 'fa-facebook-f', l: 'Facebook' }, { ic: 'fa-x-twitter', l: 'Twitter' }, { ic: 'fa-instagram', l: 'Instagram' }, { ic: 'fa-linkedin-in', l: 'LinkedIn' }].map(s => (
                                        <a key={s.l} href="#" aria-label={s.l} className="w-9 h-9 rounded-lg bg-white/5 border border-white/5 flex items-center justify-center text-slate-500 hover:bg-primary hover:border-primary hover:text-white hover:-translate-y-0.5 transition-all"><i className={`fa-brands ${s.ic} text-sm`}></i></a>
                                    ))}
                                </div>
                            </div>
                            <div>
                                <h4 className="font-bold text-xs uppercase tracking-[.15em] text-white mb-5">Quick Links</h4>
                                <ul className="space-y-3">
                                    {[['Home', 'home'], ['About Us', 'about'], ['Departments', 'departments'], ['Our Doctors', 'doctors'], ['Services', 'services'], ['Contact', 'contact']].map(([l, id]) => (
                                        <li key={id}><button onClick={() => go(id)} className="text-slate-400 text-sm hover:text-primary-light transition-colors flex items-center gap-2.5 group"><i className="fa-solid fa-chevron-right text-[7px] text-primary/30 group-hover:text-primary-light group-hover:translate-x-0.5 transition-all"></i>{l}</button></li>
                                    ))}
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-bold text-xs uppercase tracking-[.15em] text-white mb-5">Departments</h4>
                                <ul className="space-y-3">
                                    {depts.slice(0, 6).map(d => (
                                        <li key={d.name}><button onClick={() => go('departments')} className="text-slate-400 text-sm hover:text-primary-light transition-colors flex items-center gap-2.5 group"><i className="fa-solid fa-chevron-right text-[7px] text-primary/30 group-hover:text-primary-light group-hover:translate-x-0.5 transition-all"></i>{d.name}</button></li>
                                    ))}
                                </ul>
                            </div>
                            <div>
                                <h4 className="font-bold text-xs uppercase tracking-[.15em] text-white mb-5">Contact Info</h4>
                                <div className="space-y-4">
                                    <div className="flex gap-3"><i className="fa-solid fa-location-dot text-primary-light mt-0.5 text-sm"></i><p className="text-slate-400 text-sm leading-relaxed">1234 Healthcare Blvd, Medical District, New York, NY 10001</p></div>
                                    <div className="flex gap-3"><i className="fa-solid fa-phone text-primary-light mt-0.5 text-sm"></i><div><a href="tel:+15552345678" className="text-slate-400 text-sm hover:text-primary-light transition-colors block">+1 (555) 234-5678</a><a href="tel:+1555911" className="text-red-400 text-sm font-bold hover:text-red-300 transition-colors block">Emergency: +1 (555) 911</a></div></div>
                                    <div className="flex gap-3"><i className="fa-solid fa-envelope text-primary-light mt-0.5 text-sm"></i><a href="mailto:care@medicareplus.com" className="text-slate-400 text-sm hover:text-primary-light transition-colors">care@medicareplus.com</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="border-t border-white/5">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <p className="text-slate-500 text-xs">&copy; {new Date().getFullYear()} MediCare Plus. All rights reserved.</p>
                            <div className="flex gap-6">
                                {['Privacy Policy', 'Terms of Service', 'Sitemap'].map(l => <a key={l} href="#" className="text-slate-500 text-xs hover:text-slate-300 transition-colors">{l}</a>)}
                            </div>
                        </div>
                    </div>
                </footer>
            );
        }

        /* ─── Scroll to Top ─── */
        function ScrollTop() {
            const y = useScrollY();
            return (
                <button onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })} className={`scroll-btn fixed bottom-6 right-6 z-50 w-12 h-12 rounded-2xl bg-primary text-white shadow-xl shadow-primary/25 flex items-center justify-center hover:bg-primary-dark hover:scale-105 hover:shadow-primary/40 active:scale-95 transition-all ${y > 600 ? 'vis' : 'hid'}`} aria-label="Scroll to top">
                    <i className="fa-solid fa-arrow-up text-sm"></i>
                </button>
            );
        }

        /* ─── App ─── */
        function App() {
            return (
                <>
                    <ScrollProgress />
                    <Navbar />
                    <main>
                        <Hero />
                        <Stats />
                        <Wave color="#f8fafc" />
                        <About />
                        <Departments />
                        <Doctors />
                        <Wave color="#f8fafc" flip />
                        <Services />
                        <WhyChooseUs />
                        <Wave color="#f8fafc" />
                        <Testimonials />
                        <AppointmentCTA />
                        <FAQ />
                        <Wave color="#f8fafc" flip />
                        <Contact />
                    </main>
                    <Footer />
                    <ScrollTop />
                </>
            );
        }

        ReactDOM.createRoot(document.getElementById('root')).render(<App />);
    </script>
</body>

</html>