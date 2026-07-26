<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="CarePulse — Advanced healthcare ecosystem with board-certified specialists, AI-assisted diagnostics, and seamless patient journey." />
    <title>CarePulse · Advanced Healthcare Ecosystem</title>
    <link rel="icon" type="image/png" href="favicon.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
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
                        primary: { DEFAULT: '#0b2b3c', light: '#1a4b5f', dark: '#061a24' },
                        secondary: { DEFAULT: '#1c7e6f', light: '#2ea392', dark: '#115a4f' },
                        accent: { DEFAULT: '#c97d4b', light: '#da9a6e', dark: '#a05f33' },
                        warm: '#f8f6f2',
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fafcfb; color: #1f2a34; }
        ::selection { background: #1c7e6f; color: #fff; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #eef3f1; }
        ::-webkit-scrollbar-thumb { background: #b6cfc8; border-radius: 12px; }

        .glass { background: rgba(255,255,255,0.65); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.35); }
        .glass-dark { background: rgba(11,43,60,0.7); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }

        .section-pad { padding: 6rem 0; }
        @media (max-width: 768px) { .section-pad { padding: 3.5rem 0; } }

        /* ---- animations ---- */
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

        .animate-float-slow { animation: float 8s ease-in-out infinite; }
        .animate-float-delay { animation: float 6s ease-in-out 2s infinite; }

        @keyframes pulse-ring { 0%{box-shadow:0 0 0 0 rgba(28,126,111,0.4)} 70%{box-shadow:0 0 0 14px rgba(28,126,111,0)} 100%{box-shadow:0 0 0 0 rgba(28,126,111,0)} }
        .animate-pulse-ring { animation: pulse-ring 2.5s ease-out infinite; }

        @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
        .shimmer-bg { background: linear-gradient(90deg, transparent 30%, rgba(255,255,255,0.08) 50%, transparent 70%); background-size: 200% 100%; animation: shimmer 3s infinite; }

        /* ---- hero mesh ---- */
        .hero-mesh {
            background:
                radial-gradient(ellipse 70% 50% at 15% 45%, rgba(28,126,111,0.07) 0%, transparent 65%),
                radial-gradient(ellipse 50% 70% at 85% 25%, rgba(201,125,75,0.05) 0%, transparent 65%),
                radial-gradient(ellipse 40% 40% at 50% 80%, rgba(28,126,111,0.04) 0%, transparent 60%),
                linear-gradient(160deg, #f8f6f2 0%, #ffffff 45%, #eef5f2 100%);
        }

        /* ---- gradient text ---- */
        .gradient-text {
            background: linear-gradient(135deg, #1c7e6f 0%, #2ea392 50%, #0b2b3c 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ---- card hover ---- */
        .card-hover { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
        .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 44px -12px rgba(0,20,16,0.14); }

        .stat-number { font-variant-numeric: tabular-nums; }

        /* ---- dept card accent ---- */
        .dept-card { position: relative; }
        .dept-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg,#1c7e6f,#2ea392,#c97d4b); border-radius:999px 999px 0 0; opacity:0; transition:opacity 0.35s; z-index:2; }
        .dept-card:hover::before { opacity:1; }

        /* ---- testimonial quote ---- */
        .quote-mark { font-family: Georgia, serif; font-size: 6rem; line-height: 1; color: rgba(28,126,111,0.1); position: absolute; top: -10px; left: 16px; }

        /* ---- wave ---- */
        .footer-wave { position: relative; }
        .footer-wave::before {
            content: ''; position: absolute; top: -50px; left: 0; width: 100%; height: 50px;
            background: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 50' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 25C360 50 720 0 1080 25C1260 37.5 1350 50 1440 50V50H0Z' fill='%23061a24'/%3E%3C/svg%3E") no-repeat bottom center;
            background-size: 100% 50px;
        }

        /* ---- progress dots ---- */
        .progress-dot { width: 28px; height: 4px; border-radius: 999px; transition: all 0.4s; }
        .progress-dot.active { width: 40px; background: #1c7e6f; }
        .progress-dot.inactive { background: #d1d5db; }

        /* ---- image overlay gradient ---- */
        .img-overlay { position: relative; overflow: hidden; }
        .img-overlay::after { content:''; position:absolute; inset:0; background:linear-gradient(to top, rgba(11,43,60,0.5) 0%, transparent 50%); transition:opacity 0.4s; }
        .img-overlay:hover::after { opacity: 0.7; }

        /* ---- dot pattern ---- */
        .dot-pattern {
            background-image: radial-gradient(circle, rgba(28,126,111,0.12) 1px, transparent 1px);
            background-size: 18px 18px;
        }

        /* Hero slide transitions */
        .hero-slide-enter { opacity: 0; transform: translateY(18px); }
        .hero-slide-active { opacity: 1; transform: translateY(0); transition: all 0.7s cubic-bezier(0.16,1,0.3,1); }
        .hero-slide-exit { opacity: 0; transform: translateY(-12px); transition: all 0.4s ease-in; position: absolute; top: 0; left: 0; right: 0; }
        .hero-img-enter { opacity: 0; transform: scale(1.08); }
        .hero-img-active { opacity: 1; transform: scale(1); transition: all 0.8s cubic-bezier(0.16,1,0.3,1); }
        .hero-progress { height: 3px; border-radius: 999px; background: #1c7e6f; transition: width 4s linear; }
    </style>
</head>

<body>
    <div id="root"></div>
    <script type="text/babel">
        const { useState, useEffect, useRef, useCallback } = React;

        // ---------- ALL UNIQUE premium real images ----------
        const IMG = {
            hero: 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=900&h=600&fit=crop&q=80',
            about1: 'https://images.unsplash.com/photo-1581595220892-b0739db3ba8c?w=700&h=500&fit=crop&q=80',
            about2: 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=600&h=450&fit=crop&q=80',
            dept1: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&h=400&fit=crop&q=80',
            dept2: 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=600&h=400&fit=crop&q=80',
            dept3: 'https://images.unsplash.com/photo-1530497610245-94d3c16cda28?w=600&h=400&fit=crop&q=80',
            dept4: 'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=600&h=400&fit=crop&q=80',
            dept5: 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=600&h=400&fit=crop&q=80',
            dept6: 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?w=600&h=400&fit=crop&q=80',
            doc1: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=800&h=1000&fit=crop&q=80',
            doc2: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=800&h=1000&fit=crop&q=80',
            doc3: 'https://images.unsplash.com/photo-1651008376811-b90baee60c1f?w=800&h=1000&fit=crop&q=80',
            doc4: 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=800&h=1000&fit=crop&q=80',
            doc5: 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=1000&fit=crop&q=80',
            doc6: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800&h=1000&fit=crop&q=80',
            emerg: 'https://images.unsplash.com/photo-1585842378054-ee2e52f94ba2?w=600&h=400&fit=crop&q=80',
            surgery: 'https://images.unsplash.com/photo-1579154204601-01588f351e67?w=700&h=500&fit=crop&q=80',
            scan: 'https://images.unsplash.com/photo-1516069677018-378515003435?w=700&h=500&fit=crop&q=80',
            pat1: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=160&h=160&fit=crop&q=80',
            pat2: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=160&h=160&fit=crop&q=80',
            pat3: 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=160&h=160&fit=crop&q=80',
            pat4: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=160&h=160&fit=crop&q=80',
            cta: 'https://images.unsplash.com/photo-1504439468489-c8920d796a29?w=1400&h=500&fit=crop&q=80',
            lobby: 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=600&h=400&fit=crop&q=80',
            // Facilities gallery
            fac1: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&h=600&fit=crop&q=80',
            fac2: 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=800&h=600&fit=crop&q=80',
            fac3: 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=800&h=600&fit=crop&q=80',
            fac4: 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop&q=80',
            fac5: 'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?w=800&h=600&fit=crop&q=80',
            fac6: 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800&h=600&fit=crop&q=80',
            // Hero slides
            heroSlide1: 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=900&h=600&fit=crop&q=80',
            heroSlide2: 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=900&h=600&fit=crop&q=80',
            heroSlide3: 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=900&h=600&fit=crop&q=80',
        };

        // ---------- hooks ----------
        function useInView(opts = {}) {
            const ref = useRef(null);
            const [inView, setInView] = useState(false);
            useEffect(() => {
                const el = ref.current;
                if (!el) return;
                const obs = new IntersectionObserver(([e]) => {
                    if (e.isIntersecting) { setInView(true); obs.unobserve(el); }
                }, { threshold: 0.1, ...opts });
                obs.observe(el);
                return () => obs.disconnect();
            }, []);
            return [ref, inView];
        }

        function useCounter(target, duration = 2000) {
            const [count, setCount] = useState(0);
            const [ref, inView] = useInView();
            const ran = useRef(false);
            useEffect(() => {
                if (inView && !ran.current) {
                    ran.current = true;
                    const start = performance.now();
                    const step = (now) => {
                        const p = Math.min((now - start) / duration, 1);
                        const eased = 1 - Math.pow(1 - p, 3);
                        setCount(Math.floor(eased * target));
                        if (p < 1) requestAnimationFrame(step);
                    };
                    requestAnimationFrame(step);
                }
            }, [inView, target, duration]);
            return [ref, count];
        }

        function useScrollY() {
            const [y, setY] = useState(0);
            useEffect(() => {
                const handler = () => setY(window.scrollY);
                window.addEventListener('scroll', handler, { passive: true });
                return () => window.removeEventListener('scroll', handler);
            }, []);
            return y;
        }

        // ---------- fade-in component ----------
        function FadeIn({ children, className = '', delay = 0, dir = 'up' }) {
            const [ref, inView] = useInView();
            const dirs = { up: 'translate-y-8', down: '-translate-y-8', left: '-translate-x-8', right: 'translate-x-8' };
            return (
                <div ref={ref} className={`transition-all duration-700 ease-out ${inView ? 'opacity-100 translate-x-0 translate-y-0' : `opacity-0 ${dirs[dir]}`} ${className}`} style={{ transitionDelay: `${delay}ms` }}>
                    {children}
                </div>
            );
        }

        // ======================================================
        //  NAVBAR
        // ======================================================
        function Navbar() {
            const [mobile, setMobile] = useState(false);
            const [activeLink, setActiveLink] = useState('Home');
            const y = useScrollY();
            const links = [
                { name: 'Home', icon: 'fa-house' },
                { name: 'About', icon: 'fa-building-columns' },
                { name: 'Departments', icon: 'fa-hospital' },
                { name: 'Doctors', icon: 'fa-user-doctor' },
                { name: 'Services', icon: 'fa-hand-holding-medical' },
                { name: 'Testimonials', icon: 'fa-quote-left' },
                { name: 'FAQ', icon: 'fa-circle-question' },
                { name: 'Contact', icon: 'fa-envelope' },
            ];
            const scrollTo = (name) => { setMobile(false); setActiveLink(name); document.getElementById(name.toLowerCase())?.scrollIntoView({ behavior: 'smooth' }); };
            useEffect(() => { document.body.style.overflow = mobile ? 'hidden' : ''; return () => { document.body.style.overflow = ''; }; }, [mobile]);

            // Track active section on scroll
            useEffect(() => {
                const sections = links.map(l => document.getElementById(l.name.toLowerCase())).filter(Boolean);
                const handler = () => {
                    let current = 'Home';
                    sections.forEach(sec => { if (sec.getBoundingClientRect().top <= 120) current = sec.id.charAt(0).toUpperCase() + sec.id.slice(1); });
                    setActiveLink(current);
                };
                window.addEventListener('scroll', handler, { passive: true });
                return () => window.removeEventListener('scroll', handler);
            }, []);

            return (
                <>
                {/* Top gradient accent line */}
                <div className="fixed top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-secondary via-accent to-secondary z-[60]"></div>
                <header className={`sticky top-0 z-50 transition-all duration-500 ${y > 50 ? 'bg-white/95 backdrop-blur-xl shadow-lg shadow-slate-900/[0.04] border-b border-slate-200/50' : 'bg-white/70 backdrop-blur-md border-b border-transparent'}`} style={{marginTop:'3px'}}>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-[72px]">
                        {/* Logo */}
                        <div className="flex items-center gap-3 group cursor-pointer" onClick={() => scrollTo('Home')}>
                            <div className="relative">
                                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-secondary to-primary flex items-center justify-center shadow-lg shadow-secondary/25 group-hover:shadow-secondary/40 transition-all duration-300 group-hover:scale-105">
                                    <i className="fa-solid fa-heart-pulse text-white text-sm"></i>
                                </div>
                                <div className="absolute -top-0.5 -right-0.5 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white animate-pulse"></div>
                            </div>
                            <div className="flex flex-col">
                                <span className="font-outfit font-black text-xl tracking-tight text-primary leading-none">Care<span className="gradient-text">Pulse</span></span>
                                <span className="text-[9px] font-semibold text-slate-400 tracking-[0.15em] uppercase leading-none mt-0.5">Healthcare</span>
                            </div>
                        </div>

                        {/* Desktop nav */}
                        <nav className="hidden lg:flex items-center bg-slate-50/80 rounded-full px-1.5 py-1.5 border border-slate-200/60">
                            {links.map(l => (
                                <button key={l.name} onClick={() => scrollTo(l.name)}
                                    className={`px-4 py-2 text-[13px] font-semibold rounded-full transition-all duration-300 ${activeLink === l.name ? 'bg-white text-primary shadow-sm shadow-slate-200/80' : 'text-slate-500 hover:text-primary hover:bg-white/60'}`}>
                                    {l.name}
                                </button>
                            ))}
                        </nav>

                        {/* Right actions */}
                        <div className="flex items-center gap-2.5">
                            <a href="login.php" className="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary px-4 py-2.5 rounded-full border border-slate-200/80 hover:border-primary/20 hover:bg-primary/[0.03] transition-all duration-300">
                                <i className="fa-solid fa-arrow-right-to-bracket text-xs"></i> Login
                            </a>
                            <button onClick={() => scrollTo('Contact')} className="bg-gradient-to-r from-primary to-primary-light hover:from-primary-dark hover:to-primary text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 hover:scale-[1.02] transition-all duration-300 flex items-center gap-2">
                                <i className="fa-solid fa-calendar-check text-xs"></i> Book Slot
                            </button>
                            <button onClick={() => setMobile(true)} className="lg:hidden w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-xl transition"><i className="fa-solid fa-bars-staggered"></i></button>
                        </div>
                    </div>
                </header>

                {/* Mobile drawer (rendered outside header to avoid stacking context clipping) */}
                {mobile && (
                    <div className="fixed inset-0 z-[100] flex justify-end">
                        <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={() => setMobile(false)}></div>
                        <div className="relative w-80 bg-white shadow-2xl flex flex-col h-full border-l border-slate-200 overflow-y-auto">
                            {/* Drawer header */}
                            <div className="p-6 pb-4 border-b border-slate-100 bg-gradient-to-br from-primary to-primary-light">
                                <div className="flex justify-between items-center">
                                    <div className="flex items-center gap-2">
                                        <div className="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center"><i className="fa-solid fa-heart-pulse text-white text-xs"></i></div>
                                        <span className="font-outfit font-bold text-white">CarePulse</span>
                                    </div>
                                    <button onClick={() => setMobile(false)} className="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 transition"><i className="fa-solid fa-xmark"></i></button>
                                </div>
                                <p className="text-white/60 text-xs mt-3">Advanced Healthcare Ecosystem</p>
                            </div>
                            {/* Drawer links */}
                            <div className="flex-1 p-4 space-y-1">
                                {links.map(l => (
                                    <button key={l.name} onClick={() => scrollTo(l.name)}
                                        className={`w-full text-left px-4 py-3.5 font-medium rounded-xl transition-all duration-200 flex items-center gap-3 ${activeLink === l.name ? 'bg-secondary/10 text-secondary font-semibold' : 'text-slate-600 hover:bg-slate-50'}`}>
                                        <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${activeLink === l.name ? 'bg-secondary/20' : 'bg-slate-100'}`}>
                                            <i className={`fa-solid ${l.icon} text-xs ${activeLink === l.name ? 'text-secondary' : 'text-slate-400'}`}></i>
                                        </div>
                                        {l.name}
                                        {activeLink === l.name && <div className="ml-auto w-1.5 h-1.5 bg-secondary rounded-full"></div>}
                                    </button>
                                ))}
                            </div>
                            {/* Drawer footer */}
                            <div className="p-4 border-t border-slate-100 space-y-3 bg-slate-50/50">
                                <a href="login.php" className="flex items-center justify-center gap-2 border border-slate-200 py-3.5 rounded-xl font-semibold text-slate-600 hover:bg-white hover:border-primary/30 transition-all">
                                    <i className="fa-solid fa-arrow-right-to-bracket text-xs"></i> Portal Login
                                </a>
                                <button onClick={() => scrollTo('Contact')} className="w-full bg-gradient-to-r from-primary to-primary-light text-white py-3.5 rounded-xl font-bold shadow-lg shadow-primary/15 flex items-center justify-center gap-2">
                                    <i className="fa-solid fa-calendar-check text-xs"></i> Book Appointment
                                </button>
                            </div>
                        </div>
                    </div>
                )}
                </>
            );
        }

        // ======================================================
        //  HERO — gradient mesh, decorative orbs, glass cards
        // ======================================================
        function Hero() {
            const slides = [
                {
                    badge: 'Next-Gen Clinical Intelligence',
                    heading: <>Advanced <span className="gradient-text">health intelligence</span> for tomorrow</>,
                    desc: 'Board-certified specialists, AI-assisted diagnostics, and seamless patient journey — all in one ecosystem.',
                    img: IMG.heroSlide1,
                    imgAlt: 'Modern healthcare facility corridor',
                },
                {
                    badge: 'Precision Medicine & Care',
                    heading: <>Where <span className="gradient-text">compassion meets</span> cutting-edge science</>,
                    desc: 'Our multidisciplinary teams deliver personalized treatments using genomics, robotics, and AI-driven protocols.',
                    img: IMG.heroSlide2,
                    imgAlt: 'Advanced ICU monitoring systems',
                },
                {
                    badge: '24/7 Emergency Response',
                    heading: <>Saving lives with <span className="gradient-text">rapid critical</span> intervention</>,
                    desc: 'Level-1 trauma center with air ambulance, real-time telemetry, and the fastest door-to-treatment times in the region.',
                    img: IMG.heroSlide3,
                    imgAlt: 'Cardiac monitoring and diagnostics',
                },
            ];

            const [current, setCurrent] = useState(0);
            const [animating, setAnimating] = useState(false);
            const timerRef = useRef(null);

            const goTo = useCallback((idx) => {
                if (animating) return;
                setAnimating(true);
                setCurrent(idx);
                setTimeout(() => setAnimating(false), 700);
            }, [animating]);

            useEffect(() => {
                timerRef.current = setInterval(() => {
                    setCurrent(prev => (prev + 1) % slides.length);
                }, 4000);
                return () => clearInterval(timerRef.current);
            }, [slides.length]);

            const resetTimer = (idx) => {
                clearInterval(timerRef.current);
                goTo(idx);
                timerRef.current = setInterval(() => {
                    setCurrent(prev => (prev + 1) % slides.length);
                }, 4000);
            };

            const slide = slides[current];

            return (
                <section id="home" className="hero-mesh overflow-hidden relative">
                    {/* Decorative orbs */}
                    <div className="absolute top-16 left-8 w-80 h-80 bg-secondary/[0.05] rounded-full blur-3xl pointer-events-none"></div>
                    <div className="absolute bottom-8 right-12 w-96 h-96 bg-accent/[0.04] rounded-full blur-3xl pointer-events-none"></div>
                    <div className="absolute top-32 right-1/4 w-3 h-3 bg-secondary/25 rounded-full animate-float pointer-events-none"></div>
                    <div className="absolute bottom-40 left-1/3 w-2 h-2 bg-accent/20 rounded-full animate-float-delay pointer-events-none"></div>
                    <div className="absolute top-1/2 left-16 w-4 h-4 bg-secondary/10 rounded-full animate-float-slow pointer-events-none"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-6 lg:gap-10 items-center py-10 lg:py-28 relative">
                        <div className="lg:col-span-6 space-y-5 lg:space-y-7">
                            {/* Animated badge */}
                            <FadeIn>
                                <span key={'badge-'+current} className="inline-flex items-center gap-2 bg-secondary/10 text-secondary text-xs font-bold px-4 py-2 rounded-full tracking-wide hero-slide-active">
                                    <span className="w-2 h-2 bg-secondary rounded-full animate-pulse"></span>
                                    {slide.badge}
                                </span>
                            </FadeIn>

                            {/* Auto-rotating heading */}
                            <div className="relative min-h-[96px] sm:min-h-[140px] lg:min-h-[180px]">
                                {slides.map((s, i) => (
                                    <h1 key={i}
                                        className={`font-outfit font-black text-3xl sm:text-5xl lg:text-[3.5rem] text-slate-900 leading-[1.1] tracking-tight transition-all duration-700 ease-out ${i === current ? 'opacity-100 translate-y-0 relative' : 'opacity-0 translate-y-4 absolute top-0 left-0 right-0 pointer-events-none'}`}>
                                        {s.heading}
                                    </h1>
                                ))}
                            </div>

                            {/* Auto-rotating description */}
                            <div className="relative min-h-[44px] sm:min-h-[52px]">
                                {slides.map((s, i) => (
                                    <p key={i}
                                        className={`text-slate-500 text-sm sm:text-base lg:text-lg max-w-md leading-relaxed transition-all duration-600 delay-100 ease-out ${i === current ? 'opacity-100 translate-y-0 relative' : 'opacity-0 translate-y-3 absolute top-0 left-0 right-0 pointer-events-none'}`}>
                                        {s.desc}
                                    </p>
                                ))}
                            </div>

                            {/* Slide indicators */}
                            <div className="flex items-center gap-3">
                                {slides.map((_, i) => (
                                    <button key={i} onClick={() => resetTimer(i)} className="group flex flex-col items-start gap-1.5">
                                        <span className={`text-[11px] font-bold transition-all duration-300 ${i === current ? 'text-secondary' : 'text-slate-400'}`}>
                                            0{i + 1}
                                        </span>
                                        <div className="w-12 h-[3px] bg-slate-200 rounded-full overflow-hidden">
                                            <div className={`h-full rounded-full transition-all ${i === current ? 'bg-secondary' : 'bg-transparent'}`}
                                                style={{ width: i === current ? '100%' : '0%', transition: i === current ? 'width 4s linear' : 'width 0.3s' }}>
                                            </div>
                                        </div>
                                    </button>
                                ))}
                            </div>

                            <FadeIn delay={300}>
                                <div className="flex flex-wrap gap-3.5 items-center">
                                    <button onClick={() => document.getElementById('contact').scrollIntoView({behavior:'smooth'})} className="group bg-primary hover:bg-primary-dark text-white px-6 sm:px-8 py-3.5 sm:py-4 rounded-full text-sm font-bold shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 flex items-center gap-2">
                                        Book Diagnostic Slot <i className="fa-solid fa-arrow-right text-xs sm:text-sm group-hover:translate-x-1 transition-transform"></i>
                                    </button>
                                    <a href="login.php" className="group text-slate-500 font-semibold flex items-center gap-2 hover:text-primary transition text-sm">Staff Access <i className="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i></a>
                                </div>
                            </FadeIn>
                            <FadeIn delay={400}>
                                <div className="hidden sm:flex flex-wrap items-center gap-5 pt-2">
                                    {[
                                        { icon: 'fa-circle-check', label: '98% satisfaction' },
                                        { icon: 'fa-clock', label: '24/7 emergency' },
                                        { icon: 'fa-shield-halved', label: 'HIPAA certified' },
                                    ].map((b, i) => (
                                        <div key={i} className="flex items-center gap-2 text-sm text-slate-400">
                                            <div className="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center"><i className={`fa-solid ${b.icon} text-secondary text-xs`}></i></div>
                                            <span>{b.label}</span>
                                        </div>
                                    ))}
                                </div>
                            </FadeIn>
                        </div>

                        {/* Auto-rotating hero image */}
                        <div className="lg:col-span-6 relative mt-6 lg:mt-0">
                            <div className="relative">
                                <div className="absolute -inset-4 bg-gradient-to-br from-secondary/15 to-accent/10 rounded-[2.5rem] blur-2xl"></div>
                                <div className="relative rounded-[2rem] overflow-hidden shadow-2xl border border-white/40 h-[260px] sm:h-[420px] lg:h-[480px]">
                                    {slides.map((s, i) => (
                                        <img key={i} src={s.img} alt={s.imgAlt}
                                            className={`w-full h-full object-cover transition-all duration-800 ease-out ${i === current ? 'opacity-100 scale-100 relative' : 'opacity-0 scale-105 absolute inset-0 pointer-events-none'}`} />
                                    ))}
                                    <div className="absolute inset-0 bg-gradient-to-t from-primary/20 via-transparent to-transparent"></div>
                                </div>
                            </div>
                            {/* Floating glass card — left */}
                            <div className="absolute -bottom-5 -left-3 sm:-left-6 glass rounded-2xl shadow-xl p-3.5 flex items-center gap-3 animate-float z-10">
                                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-secondary to-secondary-dark flex items-center justify-center shadow-md shadow-secondary/20">
                                    <i className="fa-solid fa-user-doctor text-white text-xs"></i>
                                </div>
                                <div>
                                    <span className="text-primary font-black text-lg leading-none">540+</span>
                                    <p className="text-[10px] text-slate-500 font-medium mt-0.5">Specialists</p>
                                </div>
                            </div>
                            {/* Floating glass card — right */}
                            <div className="absolute -top-3 -right-2 sm:-right-4 glass rounded-2xl shadow-xl p-3 animate-float-delay z-10">
                                <div className="flex items-center gap-2 mb-1.5">
                                    <div className="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                                    <span className="text-[10px] font-bold text-slate-700">Live Monitoring</span>
                                </div>
                                <div className="flex items-end gap-[3px]">
                                    {[38,62,48,78,52,68,88,58,72,44,82].map((h,i) => (
                                        <div key={i} className="w-[4px] rounded-full bg-gradient-to-t from-secondary to-secondary-light" style={{height: h/6+'px', opacity: 0.5 + (h/176)}}></div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  STATS — icons, gradient bg, animated counters
        // ======================================================
        function Stats() {
            const items = [
                { num: 28, suffix: '+', label: 'Years of Excellence', icon: 'fa-award', color: 'from-secondary to-secondary-dark' },
                { num: 540, suffix: '+', label: 'Specialists', icon: 'fa-user-doctor', color: 'from-accent to-accent-dark' },
                { num: 72, suffix: '+', label: 'Specialties', icon: 'fa-stethoscope', color: 'from-primary-light to-primary' },
                { num: 100, suffix: '%', label: 'Digital Records', icon: 'fa-database', color: 'from-secondary-light to-secondary' },
            ];
            return (
                <section className="bg-gradient-to-r from-slate-50 via-white to-slate-50 border-y border-slate-100">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 gap-8 py-16">
                        {items.map((it, i) => {
                            const [ref, count] = useCounter(it.num);
                            return (
                                <FadeIn key={i} delay={i * 80}>
                                    <div ref={ref} className="text-center group">
                                        <div className={`w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br ${it.color} flex items-center justify-center shadow-lg shadow-secondary/10 group-hover:scale-110 transition-transform duration-300`}>
                                            <i className={`fa-solid ${it.icon} text-white text-lg`}></i>
                                        </div>
                                        <div className="font-outfit font-black text-4xl text-primary stat-number">{count}{it.suffix}</div>
                                        <p className="text-slate-500 text-sm font-medium mt-1">{it.label}</p>
                                    </div>
                                </FadeIn>
                            );
                        })}
                    </div>
                </section>
            );
        }

        // ======================================================
        //  INSURANCES — trusted partners & accepted plans
        // ======================================================
        function Insurances() {
            const partners = [
                { name: 'MetLife', icon: 'fa-shield-heart', color: 'text-blue-500 bg-blue-500/10' },
                { name: 'Allianz', icon: 'fa-building-shield', color: 'text-indigo-500 bg-indigo-500/10' },
                { name: 'Aetna', icon: 'fa-heart-pulse', color: 'text-red-500 bg-red-500/10' },
                { name: 'Cigna', icon: 'fa-user-shield', color: 'text-cyan-500 bg-cyan-500/10' },
                { name: 'Blue Cross', icon: 'fa-house-medical-shield', color: 'text-sky-600 bg-sky-600/10' },
                { name: 'UnitedHealth', icon: 'fa-kit-medical', color: 'text-emerald-500 bg-emerald-500/10' }
            ];
            return (
                <section className="bg-slate-50/50 py-10 border-b border-slate-100 overflow-hidden">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <p className="text-center text-xs font-bold uppercase tracking-[0.2em] text-slate-400 mb-6">Accepted Insurance & Healthcare Partners</p>
                        <div className="flex flex-wrap justify-center items-center gap-6 md:gap-12 opacity-75">
                            {partners.map((p, i) => (
                                <FadeIn key={i} delay={i * 50} className="flex items-center gap-2.5 px-5 py-2.5 bg-white border border-slate-200/80 rounded-2xl shadow-sm hover:shadow-md hover:border-secondary/20 hover:scale-[1.03] transition-all duration-300 group cursor-default">
                                    <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${p.color} group-hover:scale-110 transition-transform duration-300`}>
                                        <i className={`fa-solid ${p.icon} text-sm`}></i>
                                    </div>
                                    <span className="font-outfit font-bold text-slate-700 text-sm tracking-tight group-hover:text-primary transition-colors">{p.name}</span>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  ABOUT — overlapping images, floating badge
        // ======================================================
        function About() {
            return (
                <section id="about" className="section-pad bg-warm relative overflow-hidden">
                    <div className="absolute top-20 right-0 w-64 h-64 dot-pattern rounded-full pointer-events-none"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-14 items-center">
                        <div className="lg:col-span-6 relative">
                            <FadeIn dir="left">
                                <div className="relative">
                                    {/* Main image */}
                                    <div className="rounded-3xl overflow-hidden shadow-xl border border-slate-200 relative z-10">
                                        <img src={IMG.about1} alt="Doctor consultation" className="w-full h-[380px] object-cover" />
                                    </div>
                                    {/* Overlapping image */}
                                    <div className="absolute -bottom-8 -right-6 w-[55%] rounded-2xl overflow-hidden shadow-2xl border-4 border-white z-20 hidden sm:block">
                                        <img src={IMG.about2} alt="Medical technology" className="w-full h-[200px] object-cover" />
                                    </div>
                                    {/* Floating badge */}
                                    <div className="absolute -top-4 -left-4 glass rounded-2xl shadow-lg p-4 z-20 animate-float">
                                        <div className="text-center">
                                            <span className="font-outfit font-black text-3xl gradient-text">28+</span>
                                            <p className="text-[11px] text-slate-500 font-semibold">Years of<br/>Excellence</p>
                                        </div>
                                    </div>
                                    {/* Decorative dot pattern */}
                                    <div className="absolute -bottom-4 -left-4 w-20 h-20 dot-pattern rounded-xl -z-10"></div>
                                </div>
                            </FadeIn>
                        </div>
                        <div className="lg:col-span-6 space-y-6">
                            <FadeIn>
                                <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                    <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Our Legacy
                                </span>
                                <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Built on clinical integrity & innovation</h2>
                                <p className="text-slate-500 text-[15px] leading-relaxed mt-4">For 28 years, CarePulse has combined certified expertise with advanced diagnostics. We treat every patient with precision and compassion, pushing the boundaries of modern healthcare.</p>
                            </FadeIn>
                            <FadeIn delay={100}>
                                <div className="space-y-3 pt-2">
                                    {['Board-certified specialists in 72+ specialties','AI-powered diagnostic accuracy up to 99.2%','Fully integrated digital health records','Internationally accredited facilities'].map((item, i) => (
                                        <div key={i} className="flex items-start gap-3">
                                            <div className="w-6 h-6 rounded-full bg-secondary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <i className="fa-solid fa-check text-secondary text-[10px]"></i>
                                            </div>
                                            <span className="text-slate-600 text-sm">{item}</span>
                                        </div>
                                    ))}
                                </div>
                            </FadeIn>
                            <FadeIn delay={200}>
                                <div className="grid sm:grid-cols-2 gap-4 pt-4">
                                    <div className="bg-white p-5 rounded-2xl border border-slate-200 card-hover shadow-sm">
                                        <div className="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center"><i className="fa-solid fa-bullseye text-secondary"></i></div>
                                        <h4 className="font-bold text-slate-800 mt-3">Mission</h4>
                                        <p className="text-xs text-slate-500 mt-1">Accessible, innovative care for every individual regardless of circumstance.</p>
                                    </div>
                                    <div className="bg-white p-5 rounded-2xl border border-slate-200 card-hover shadow-sm">
                                        <div className="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center"><i className="fa-solid fa-eye text-accent"></i></div>
                                        <h4 className="font-bold text-slate-800 mt-3">Vision</h4>
                                        <p className="text-xs text-slate-500 mt-1">Global leader in telehealth, AI diagnostics, and patient-centered care.</p>
                                    </div>
                                </div>
                            </FadeIn>
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  FACILITIES — hospital gallery showcase
        // ======================================================
        function Facilities() {
            const gallery = [
                { img: IMG.fac1, title: 'Main Reception & Lobby', tag: 'Reception', span: 'col-span-2 row-span-2' },
                { img: IMG.fac2, title: 'Advanced Operation Theater', tag: 'Surgery', span: 'col-span-1 row-span-1' },
                { img: IMG.fac3, title: 'Intensive Care Unit', tag: 'ICU', span: 'col-span-1 row-span-1' },
                { img: IMG.fac4, title: 'Diagnostic Imaging Center', tag: 'Radiology', span: 'col-span-1 row-span-2' },
                { img: IMG.fac5, title: 'Emergency Department', tag: 'Emergency', span: 'col-span-1 row-span-1' },
                { img: IMG.fac6, title: 'Research & Innovation Lab', tag: 'Research', span: 'col-span-1 row-span-1' },
            ];
            const highlights = [
                { icon: 'fa-hospital', num: 12, suffix: '+', label: 'Operation Theaters' },
                { icon: 'fa-bed', num: 500, suffix: '+', label: 'Bed Capacity' },
                { icon: 'fa-square-parking', num: 800, suffix: '+', label: 'Parking Spaces' },
                { icon: 'fa-elevator', num: 15, suffix: '', label: 'Floors' },
            ];
            return (
                <section className="section-pad bg-white relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-96 h-96 dot-pattern rounded-full pointer-events-none opacity-30"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Infrastructure <span className="w-8 h-[2px] bg-secondary rounded-full"></span>
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">World-class facilities</h2>
                            <p className="text-slate-500 text-sm mt-3">State-of-the-art infrastructure designed for optimal patient outcomes and comfort.</p>
                        </FadeIn>

                        {/* Gallery masonry grid */}
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 auto-rows-[180px] md:auto-rows-[200px]">
                            {gallery.map((item, i) => (
                                <FadeIn key={i} delay={i * 80} className={`${item.span} rounded-2xl overflow-hidden relative group cursor-pointer`}>
                                    <img src={item.img} alt={item.title} className="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-60 group-hover:opacity-90 transition-all duration-500"></div>
                                    <div className="absolute top-4 left-4 z-10">
                                        <span className="inline-block bg-white/20 backdrop-blur-sm text-white text-[10px] font-bold px-3 py-1 rounded-full tracking-wider uppercase">{item.tag}</span>
                                    </div>
                                    <div className="absolute bottom-0 left-0 right-0 p-5 z-10 translate-y-2 group-hover:translate-y-0 transition-transform duration-500">
                                        <h4 className="font-outfit font-bold text-white text-lg leading-snug">{item.title}</h4>
                                        <div className="flex items-center gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                                            <span className="text-secondary-light text-sm font-medium">Explore</span>
                                            <i className="fa-solid fa-arrow-right text-secondary-light text-xs group-hover:translate-x-1 transition-transform"></i>
                                        </div>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>

                        {/* Facility highlights */}
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
                            {highlights.map((h, i) => {
                                const [ref, count] = useCounter(h.num);
                                return (
                                    <FadeIn key={i} delay={i * 80}>
                                        <div ref={ref} className="bg-gradient-to-br from-slate-50 to-white rounded-2xl p-6 border border-slate-200 text-center card-hover group">
                                            <div className="w-12 h-12 mx-auto rounded-xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary/20 group-hover:scale-110 transition-all duration-300">
                                                <i className={`fa-solid ${h.icon} text-secondary text-lg`}></i>
                                            </div>
                                            <div className="font-outfit font-black text-3xl text-primary mt-3 stat-number">{count}{h.suffix}</div>
                                            <p className="text-slate-500 text-sm font-medium mt-1">{h.label}</p>
                                        </div>
                                    </FadeIn>
                                );
                            })}
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  DEPARTMENTS — glass overlay, gradient accent
        // ======================================================
        function Departments() {
            const depts = [
                { name: 'Cardiology', icon: 'fa-heart-pulse', img: IMG.dept1, desc: 'Advanced cardiac diagnostics, catheterization and interventional procedures.', color: 'bg-rose-500/10 text-rose-500' },
                { name: 'Neurology', icon: 'fa-brain', img: IMG.dept2, desc: 'Comprehensive brain, spine & nervous system care with cutting-edge tech.', color: 'bg-purple-500/10 text-purple-500' },
                { name: 'Orthopedics', icon: 'fa-bone', img: IMG.dept3, desc: 'Robotic joint replacements, sports medicine & fracture management.', color: 'bg-blue-500/10 text-blue-500' },
                { name: 'Pediatrics', icon: 'fa-baby', img: IMG.dept4, desc: 'Holistic child health from neonate to adolescent with family care.', color: 'bg-emerald-500/10 text-emerald-500' },
                { name: 'Gynecology', icon: 'fa-venus', img: IMG.dept5, desc: "Women's health, maternity services & reproductive medicine.", color: 'bg-pink-500/10 text-pink-500' },
                { name: 'Emergency', icon: 'fa-truck-medical', img: IMG.dept6, desc: '24/7 level-1 trauma center with rapid-response critical care teams.', color: 'bg-amber-500/10 text-amber-500' },
            ];
            return (
                <section id="departments" className="section-pad bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Specialties <span className="w-8 h-[2px] bg-secondary rounded-full"></span>
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Medical departments</h2>
                            <p className="text-slate-500 text-sm mt-3">Comprehensive care across every major medical discipline with state-of-the-art facilities.</p>
                        </FadeIn>
                        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {depts.map((d, i) => (
                                <FadeIn key={i} delay={i * 70}>
                                    <div className="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm dept-card card-hover group">
                                        <div className="h-52 overflow-hidden relative">
                                            <img src={d.img} alt={d.name} className="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" />
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                        </div>
                                        <div className="p-6">
                                            <div className="flex items-center gap-3">
                                                <div className={`w-10 h-10 rounded-xl ${d.color} flex items-center justify-center`}>
                                                    <i className={`fa-solid ${d.icon} text-sm`}></i>
                                                </div>
                                                <h4 className="font-outfit font-bold text-slate-800 text-lg">{d.name}</h4>
                                            </div>
                                            <p className="text-slate-500 text-sm mt-3 leading-relaxed">{d.desc}</p>
                                            <button className="mt-4 text-secondary text-sm font-semibold flex items-center gap-1.5 group/link">
                                                Learn more <i className="fa-solid fa-arrow-right text-[10px] group-hover/link:translate-x-1 transition-transform"></i>
                                            </button>
                                        </div>
                                    </div>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  DOCTORS — full card with hover overlay
        // ======================================================
        function Doctors() {
            const docs = [
                { name: 'Dr. Sarah Mitchell', spec: 'Cardiologist', exp: '18 yrs', img: IMG.doc1, bio: 'Specializes in interventional cardiology and heart failure management with over 3000 successful procedures.' },
                { name: 'Dr. James Rodriguez', spec: 'Neurologist', exp: '15 yrs', img: IMG.doc2, bio: 'Expert in stroke rehabilitation, neurodegenerative disorders and advanced brain mapping.' },
                { name: 'Dr. Emily Chen', spec: 'Orthopedic Surgeon', exp: '12 yrs', img: IMG.doc3, bio: 'Minimally invasive joint reconstruction and sports injury specialist with robotic surgery expertise.' },
                { name: 'Dr. Michael Patel', spec: 'Pediatrician', exp: '20 yrs', img: IMG.doc4, bio: 'Dedicated to child development, adolescent health and neonatal intensive care.' },
                { name: 'Dr. Robert Thorne', spec: 'General Surgeon', exp: '14 yrs', img: IMG.doc5, bio: 'Specializes in minimally invasive laparoscopic procedures and trauma surgery.' },
                { name: 'Dr. Lisa Wong', spec: 'Gynecologist', exp: '10 yrs', img: IMG.doc6, bio: 'Focused on maternal-fetal medicine, high-risk pregnancies, and reproductive health.' },
            ];
            const [active, setActive] = useState(0);

            return (
                <section id="doctors" className="section-pad bg-warm relative overflow-hidden">
                    <div className="absolute bottom-0 left-0 w-72 h-72 dot-pattern rounded-full pointer-events-none opacity-50"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Experts <span className="w-8 h-[2px] bg-secondary rounded-full"></span>
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Meet our specialists</h2>
                            <p className="text-slate-500 text-sm mt-3">Our physicians bring decades of combined expertise across all major medical disciplines.</p>
                        </FadeIn>

                        <div className="grid lg:grid-cols-12 gap-8 items-start">
                            {/* Active doctor detail card */}
                            <FadeIn className="lg:col-span-5">
                                <div className="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-lg card-hover">
                                    <div className="h-96 md:h-[400px] overflow-hidden relative">
                                        <img src={docs[active].img} alt={docs[active].name} className="w-full h-full object-cover object-[center_15%]" />
                                        <div className="absolute inset-0 bg-gradient-to-t from-primary/60 via-transparent to-transparent"></div>
                                        <div className="absolute bottom-4 left-6 text-white">
                                            <p className="text-secondary-light font-semibold text-sm">{docs[active].spec}</p>
                                            <h3 className="font-outfit font-bold text-2xl">{docs[active].name}</h3>
                                        </div>
                                    </div>
                                    <div className="p-6">
                                        <div className="flex items-center gap-4 mb-4">
                                            <div className="flex items-center gap-2 text-sm bg-secondary/10 text-secondary px-3 py-1.5 rounded-full font-semibold">
                                                <i className="fa-solid fa-clock text-xs"></i> {docs[active].exp}
                                            </div>
                                            <div className="flex text-amber-400 text-sm gap-0.5">
                                                {[1,2,3,4,5].map(s => <i key={s} className="fa-solid fa-star"></i>)}
                                            </div>
                                        </div>
                                        <p className="text-slate-600 text-sm italic leading-relaxed">"{docs[active].bio}"</p>
                                        <button onClick={() => document.getElementById('contact').scrollIntoView({behavior:'smooth'})} className="mt-6 w-full bg-primary text-white py-3.5 rounded-xl font-bold hover:bg-primary-dark transition-all shadow-md shadow-primary/10 flex items-center justify-center gap-2">
                                            <i className="fa-solid fa-calendar-check text-sm"></i> Book Consultation
                                        </button>
                                    </div>
                                </div>
                            </FadeIn>

                            {/* Doctor selector grid */}
                            <div className="lg:col-span-7 grid sm:grid-cols-2 gap-5">
                                {docs.map((d, i) => (
                                    <FadeIn key={i} delay={i * 60}>
                                        <button onClick={() => setActive(i)} className={`w-full flex items-center gap-5 p-5 md:p-6 rounded-3xl border transition-all duration-300 card-hover text-left ${active === i ? 'bg-primary/[0.04] border-secondary/40 shadow-lg ring-1 ring-secondary/20' : 'bg-white border-slate-200 hover:border-slate-300'}`}>
                                            <img src={d.img} alt={d.name} className={`w-20 h-20 rounded-2xl object-cover transition-all duration-300 ${active === i ? 'ring-2 ring-secondary ring-offset-2' : ''}`} />
                                            <div className="space-y-1">
                                                <h4 className="font-outfit font-bold text-slate-800 text-base leading-none">{d.name}</h4>
                                                <p className="text-slate-500 text-xs font-medium">{d.spec}</p>
                                                <span className="inline-flex text-[11px] font-bold text-secondary bg-secondary/10 px-2.5 py-1 rounded-full">{d.exp} experience</span>
                                            </div>
                                            {active === i && <div className="ml-auto w-2 h-2 bg-secondary rounded-full animate-pulse"></div>}
                                        </button>
                                    </FadeIn>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  SERVICES — enhanced bento grid
        // ======================================================
        function Services() {
            const services = [
                { title: 'Emergency Care', icon: 'fa-kit-medical', img: IMG.emerg, big: true, desc: '24/7 Level-1 trauma with GPS-enabled fleet' },
                { title: 'ICU Monitoring', icon: 'fa-bed-pulse', desc: 'Real-time vital tracking with 1:1 critical care nursing', color: 'from-rose-500/10 to-rose-500/5' },
                { title: 'Diagnostic Lab', icon: 'fa-flask-vial', desc: '2000+ tests with AI-automated reporting & alerts', color: 'from-violet-500/10 to-violet-500/5' },
                { title: 'Radiology (MRI/CT)', icon: 'fa-x-ray', img: IMG.scan, big: true, desc: '3T MRI & 256-slice CT with AI-assisted imaging' },
                { title: 'Robotic Surgery', icon: 'fa-syringe', img: IMG.surgery, big: true, desc: 'Da Vinci robotic-assisted precision procedures' },
                { title: 'Telehealth', icon: 'fa-laptop-medical', desc: 'Secure HD video consults from anywhere', color: 'from-emerald-500/10 to-emerald-500/5' },
                { title: 'Pharmacy', icon: 'fa-prescription-bottle-medical', desc: 'Digital prescription & same-day delivery', color: 'from-amber-500/10 to-amber-500/5' },
            ];
            return (
                <section id="services" className="section-pad bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Capabilities <span className="w-8 h-[2px] bg-secondary rounded-full"></span>
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Integrated care services</h2>
                            <p className="text-slate-500 text-sm mt-3">End-to-end healthcare capabilities powered by advanced technology and human expertise.</p>
                        </FadeIn>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 auto-rows-[200px]">
                            {services.map((s, i) => {
                                const span = s.big ? 'col-span-2 row-span-2' : 'col-span-1 row-span-1';
                                return (
                                    <FadeIn key={i} delay={i * 50} className={`${span} rounded-2xl overflow-hidden border border-slate-200 relative group card-hover ${s.img ? 'bg-slate-900' : 'bg-gradient-to-br ' + (s.color || 'from-slate-50 to-white') + ' shadow-sm'}`}>
                                        {s.img ? (
                                            <>
                                                <img src={s.img} alt={s.title} className="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-80 group-hover:scale-105 transition-all duration-700" />
                                                <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                                <div className="absolute bottom-5 left-5 right-5 text-white z-10">
                                                    <div className="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center mb-3">
                                                        <i className={`fa-solid ${s.icon} text-lg`}></i>
                                                    </div>
                                                    <h4 className="font-outfit font-bold text-xl">{s.title}</h4>
                                                    <p className="text-white/70 text-sm mt-1">{s.desc}</p>
                                                </div>
                                            </>
                                        ) : (
                                            <div className="p-5 flex flex-col justify-center h-full relative">
                                                <div className="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm border border-slate-200 mb-3 group-hover:scale-110 transition-transform duration-300">
                                                    <i className={`fa-solid ${s.icon} text-secondary text-sm`}></i>
                                                </div>
                                                <h4 className="font-bold text-slate-800 text-sm">{s.title}</h4>
                                                <p className="text-slate-500 text-xs mt-1 leading-relaxed">{s.desc}</p>
                                            </div>
                                        )}
                                    </FadeIn>
                                );
                            })}
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  TESTIMONIALS — auto-rotate, decorative quotes
        // ======================================================
        function Testimonials() {
            const list = [
                { name: 'Rebecca Thompson', role: 'Heart Surgery Patient', text: 'The cardiology team saved my life. Every moment was handled with professionalism and compassion. I will never forget their exceptional kindness and expertise.', img: IMG.pat1 },
                { name: 'David Kim', role: 'Orthopedic Patient', text: 'After years of chronic back pain, Dr. Chen performed a minimally invasive surgery. I was walking the next day and fully recovered within just three weeks.', img: IMG.pat2 },
                { name: 'Susan Clarke', role: 'Maternity Patient', text: 'Maternity care exceeded every expectation. They made everything feel safe and joyful. I wholeheartedly recommend them to every expecting mother.', img: IMG.pat3 },
                { name: 'Robert Hayes', role: 'Telehealth Patient', text: 'Online consultation was a true game-changer. Speaking with my neurologist from home while receiving thorough, personalized advice is the future of healthcare.', img: IMG.pat4 },
            ];
            const [idx, setIdx] = useState(0);
            const timerRef = useRef(null);

            const startTimer = useCallback(() => {
                if (timerRef.current) clearInterval(timerRef.current);
                timerRef.current = setInterval(() => setIdx(prev => (prev + 1) % list.length), 5000);
            }, [list.length]);

            useEffect(() => { startTimer(); return () => clearInterval(timerRef.current); }, [startTimer]);

            const goTo = (i) => { setIdx(i); startTimer(); };

            return (
                <section id="testimonials" className="section-pad bg-warm relative overflow-hidden">
                    <div className="absolute top-10 right-10 w-80 h-80 dot-pattern rounded-full pointer-events-none opacity-40"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-center">
                        <div className="lg:col-span-6">
                            <FadeIn>
                                <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                    <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Testimonials
                                </span>
                                <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Patient stories</h2>
                                <p className="text-slate-500 text-sm mt-2">Real experiences from people whose lives we've touched.</p>
                            </FadeIn>
                            <div className="mt-8 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-lg relative overflow-hidden">
                                <span className="quote-mark">"</span>
                                <div className="relative z-10">
                                    <p className="text-lg text-slate-700 leading-relaxed italic mt-6">"{list[idx].text}"</p>
                                    <div className="flex items-center gap-4 mt-8 pt-6 border-t border-slate-100">
                                        <img src={list[idx].img} className="w-14 h-14 rounded-full object-cover ring-2 ring-secondary ring-offset-2" alt={list[idx].name} />
                                        <div>
                                            <p className="font-bold text-slate-800">{list[idx].name}</p>
                                            <p className="text-secondary text-sm font-medium">{list[idx].role}</p>
                                        </div>
                                        <div className="ml-auto flex text-amber-400 text-sm gap-0.5">
                                            {[1,2,3,4,5].map(s => <i key={s} className="fa-solid fa-star"></i>)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="flex gap-2 mt-5">
                                {list.map((_, i) => (
                                    <button key={i} onClick={() => goTo(i)} className={`progress-dot ${i === idx ? 'active' : 'inactive'}`}></button>
                                ))}
                            </div>
                        </div>
                        <div className="lg:col-span-6 grid gap-3">
                            {list.map((t, i) => (
                                <FadeIn key={i} delay={i * 60}>
                                    <button onClick={() => goTo(i)} className={`w-full flex items-center gap-4 p-4 rounded-2xl border transition-all duration-300 card-hover text-left ${i === idx ? 'bg-primary/[0.04] border-secondary/40 shadow-lg ring-1 ring-secondary/20' : 'bg-white border-slate-200'}`}>
                                        <img src={t.img} className={`w-12 h-12 rounded-full object-cover transition-all ${i === idx ? 'ring-2 ring-secondary ring-offset-2' : ''}`} alt={t.name} />
                                        <div className="flex-1 min-w-0">
                                            <p className="font-bold text-slate-800 text-sm">{t.name}</p>
                                            <p className="text-slate-500 text-xs truncate">{t.text.slice(0, 55)}…</p>
                                        </div>
                                        {i === idx && <div className="w-2 h-2 bg-secondary rounded-full animate-pulse flex-shrink-0"></div>}
                                    </button>
                                </FadeIn>
                            ))}
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  FAQ — accordion layout with active state & support card
        // ======================================================
        function FAQ() {
            const faqs = [
                {
                    q: "How do I book an appointment with a specialist?",
                    a: "You can book an appointment easily by using our online consultation form below, or by clicking the 'Book Slot' button in the navigation bar. Alternatively, you can call our 24/7 registration desk at +1 (555) 234-5678."
                },
                {
                    q: "Do you accept international health insurance plans?",
                    a: "Yes, CarePulse accepts most major international and national insurance plans, including Allianz, Blue Cross, Cigna, Aetna, and UnitedHealthcare. Please contact our billing office prior to your visit to confirm coverage details."
                },
                {
                    q: "What should I bring for my first diagnostic checkup?",
                    a: "For your first visit, please bring a valid government-issued ID, your insurance card, any previous medical records or test results, and a list of current medications you are taking."
                },
                {
                    q: "Is telemedicine available for all specialties?",
                    a: "Most of our medical specialties offer secure online video consultations. This includes Neurology, Cardiology follow-ups, Pediatrics, and General Medicine. You can request a telehealth slot directly via our online form."
                },
                {
                    q: "How do I access my medical records and lab reports?",
                    a: "All patient records and diagnostic reports are fully digitized. You can access them securely through our Patient Portal. Login credentials will be provided to you via email after your first registration."
                }
            ];

            const [openIdx, setOpenIdx] = useState(0);

            return (
                <section id="faq" className="section-pad bg-warm relative overflow-hidden">
                    <div className="absolute top-1/4 left-0 w-48 h-48 dot-pattern rounded-full pointer-events-none opacity-40"></div>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                <span className="w-8 h-[2px] bg-secondary rounded-full"></span> FAQ <span className="w-8 h-[2px] bg-secondary rounded-full"></span>
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Frequently asked questions</h2>
                            <p className="text-slate-500 text-sm mt-3">Find answers to common queries about our healthcare services, billing, and patient care.</p>
                        </FadeIn>

                        <div className="grid lg:grid-cols-12 gap-8 items-start">
                            <div className="lg:col-span-4 space-y-6">
                                <FadeIn>
                                    <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-md">
                                        <div className="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center mb-4">
                                            <i className="fa-solid fa-circle-question text-secondary text-xl"></i>
                                        </div>
                                        <h4 className="font-outfit font-bold text-slate-800 text-lg">Still have questions?</h4>
                                        <p className="text-slate-500 text-xs mt-2 leading-relaxed">If you cannot find the answer to your query in our FAQs, feel free to contact our support team directly. We are here to help you 24/7.</p>
                                        
                                        <div className="space-y-3 mt-6">
                                            <a href="tel:+15552345678" className="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-secondary/20 hover:bg-slate-100/55 transition-all group">
                                                <div className="w-8 h-8 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center group-hover:scale-105 transition-transform"><i className="fa-solid fa-phone text-xs"></i></div>
                                                <div>
                                                    <p className="text-[10px] text-slate-400 font-semibold uppercase leading-none">Call Support</p>
                                                    <p className="text-xs font-bold text-slate-700 mt-1">+1 (555) 234-5678</p>
                                                </div>
                                            </a>
                                            <a href="mailto:support@carepulse.com" className="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-secondary/20 hover:bg-slate-100/55 transition-all group">
                                                <div className="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center group-hover:scale-105 transition-transform"><i className="fa-solid fa-envelope text-xs"></i></div>
                                                <div>
                                                    <p className="text-[10px] text-slate-400 font-semibold uppercase leading-none">Email Us</p>
                                                    <p className="text-xs font-bold text-slate-700 mt-1">support@carepulse.com</p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </FadeIn>
                                
                                <FadeIn delay={100}>
                                    <div className="bg-gradient-to-br from-secondary to-secondary-dark text-white p-6 rounded-3xl shadow-lg">
                                        <h4 className="font-outfit font-bold text-base">Need Urgent Medical Help?</h4>
                                        <p className="text-white/75 text-xs mt-1.5 leading-relaxed">Our emergency response team is active 24/7. Call our priority line for immediate dispatch.</p>
                                        <a href="tel:+1555911" className="mt-4 inline-flex items-center gap-2 bg-white text-secondary px-5 py-2.5 rounded-xl font-bold text-xs shadow-md hover:bg-slate-50 transition-colors">
                                            <i className="fa-solid fa-phone-volume"></i> Call +1 (555) 911
                                        </a>
                                    </div>
                                </FadeIn>
                            </div>

                            <div className="lg:col-span-8 space-y-4">
                                {faqs.map((faq, idx) => {
                                    const isOpen = openIdx === idx;
                                    return (
                                        <FadeIn key={idx} delay={idx * 50}>
                                            <div className={`bg-white rounded-2xl border transition-all duration-300 overflow-hidden ${isOpen ? 'border-secondary/40 shadow-md ring-1 ring-secondary/5' : 'border-slate-200 hover:border-slate-300'}`}>
                                                <button onClick={() => setOpenIdx(isOpen ? -1 : idx)} className="w-full flex items-center justify-between p-5 md:p-6 text-left outline-none">
                                                    <span className={`font-outfit font-bold text-sm md:text-base transition-colors duration-300 ${isOpen ? 'text-secondary' : 'text-slate-800'}`}>{faq.q}</span>
                                                    <div className={`w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 ${isOpen ? 'bg-secondary/10 text-secondary rotate-180' : 'bg-slate-100 text-slate-500'}`}>
                                                        <i className="fa-solid fa-chevron-down text-xs"></i>
                                                    </div>
                                                </button>
                                                <div className={`transition-all duration-300 ease-in-out ${isOpen ? 'max-h-[200px] border-t border-slate-100 opacity-100' : 'max-h-0 opacity-0 pointer-events-none'}`}>
                                                    <div className="p-5 md:p-6 text-slate-500 text-xs md:text-sm leading-relaxed bg-slate-50/50">
                                                        {faq.a}
                                                    </div>
                                                </div>
                                            </div>
                                        </FadeIn>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  CTA — background image, glass overlay
        // ======================================================
        function CTA() {
            return (
                <section className="relative overflow-hidden">
                    <img src={IMG.cta} alt="" className="absolute inset-0 w-full h-full object-cover" />
                    <div className="absolute inset-0 bg-primary/85"></div>
                    <div className="absolute inset-0 shimmer-bg"></div>
                    <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 flex flex-col sm:flex-row items-center justify-between gap-8 text-white">
                        <FadeIn>
                            <div className="flex items-center gap-3 mb-4">
                                <div className="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                                <span className="text-emerald-300 text-sm font-semibold">Next available slot: Today</span>
                            </div>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl">Ready to schedule?</h2>
                            <p className="text-white/70 mt-2 max-w-md">Book your diagnostic slot or consult with a specialist. Our team responds within 30 minutes.</p>
                        </FadeIn>
                        <FadeIn delay={150}>
                            <div className="flex flex-wrap gap-4">
                                <button onClick={() => document.getElementById('contact').scrollIntoView({behavior:'smooth'})} className="bg-white text-primary px-8 py-4 rounded-full font-bold shadow-xl hover:bg-slate-100 hover:shadow-2xl transition-all duration-300 flex items-center gap-2">
                                    <i className="fa-solid fa-calendar-check"></i> Book Now
                                </button>
                                <a href="tel:+15552345678" className="border border-white/30 backdrop-blur-sm px-8 py-4 rounded-full font-bold hover:bg-white/10 transition-all duration-300 flex items-center gap-2">
                                    <i className="fa-solid fa-phone"></i> Call Desk
                                </a>
                            </div>
                        </FadeIn>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  CONTACT — enhanced form, working hours
        // ======================================================
        function Contact() {
            const [form, setForm] = useState({ name: '', email: '', phone: '', dept: '', msg: '' });
            const [sent, setSent] = useState(false);
            const handleSubmit = (e) => { e.preventDefault(); setSent(true); setTimeout(() => setSent(false), 4000); };

            return (
                <section id="contact" className="section-pad bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <FadeIn className="text-center max-w-2xl mx-auto mb-14">
                            <span className="inline-flex items-center gap-2 text-secondary font-bold text-xs tracking-[0.2em] uppercase">
                                <span className="w-8 h-[2px] bg-secondary rounded-full"></span> Get in Touch <span className="w-8 h-[2px] bg-secondary rounded-full"></span>
                            </span>
                            <h2 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 mt-3">Request consultation</h2>
                        </FadeIn>
                        <div className="grid lg:grid-cols-12 gap-10">
                            <div className="lg:col-span-7">
                                <FadeIn>
                                    <div className="bg-warm p-8 sm:p-10 rounded-3xl shadow-md border border-slate-200">
                                        {sent ? (
                                            <div className="py-16 text-center">
                                                <div className="w-16 h-16 mx-auto rounded-full bg-secondary/10 flex items-center justify-center mb-4">
                                                    <i className="fa-solid fa-check text-secondary text-2xl"></i>
                                                </div>
                                                <h3 className="font-outfit font-bold text-xl text-slate-800">Request Submitted!</h3>
                                                <p className="text-slate-500 mt-2">We'll connect within 2 hours during business hours.</p>
                                            </div>
                                        ) : (
                                            <form onSubmit={handleSubmit} className="space-y-4">
                                                <div className="grid sm:grid-cols-2 gap-4">
                                                    <input type="text" placeholder="Full name" className="w-full p-4 rounded-xl border border-slate-200 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" value={form.name} onChange={e => setForm({...form, name: e.target.value})} required />
                                                    <input type="email" placeholder="Email address" className="w-full p-4 rounded-xl border border-slate-200 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" value={form.email} onChange={e => setForm({...form, email: e.target.value})} required />
                                                </div>
                                                <div className="grid sm:grid-cols-2 gap-4">
                                                    <input type="tel" placeholder="Phone number" className="w-full p-4 rounded-xl border border-slate-200 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all" value={form.phone} onChange={e => setForm({...form, phone: e.target.value})} />
                                                    <select className="w-full p-4 rounded-xl border border-slate-200 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all text-slate-500" value={form.dept} onChange={e => setForm({...form, dept: e.target.value})}>
                                                        <option value="">Select department</option>
                                                        <option>Cardiology</option>
                                                        <option>Neurology</option>
                                                        <option>Orthopedics</option>
                                                        <option>Pediatrics</option>
                                                        <option>Gynecology</option>
                                                        <option>Emergency</option>
                                                        <option>General Medicine</option>
                                                    </select>
                                                </div>
                                                <textarea rows="4" placeholder="Describe your concern or clinical notes..." className="w-full p-4 rounded-xl border border-slate-200 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/10 outline-none transition-all resize-none" value={form.msg} onChange={e => setForm({...form, msg: e.target.value})}></textarea>
                                                <button type="submit" className="w-full bg-primary text-white py-4 rounded-xl font-bold shadow-md shadow-primary/10 hover:bg-primary-dark hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                                                    <i className="fa-solid fa-paper-plane text-sm"></i> Submit Request
                                                </button>
                                            </form>
                                        )}
                                    </div>
                                </FadeIn>
                            </div>
                            <div className="lg:col-span-5 space-y-5">
                                <FadeIn delay={100}>
                                    <div className="bg-warm p-6 rounded-2xl border border-slate-200">
                                        <h4 className="font-bold text-slate-800 flex items-center gap-2"><i className="fa-solid fa-location-dot text-secondary"></i> Direct Contact</h4>
                                        <div className="space-y-3 mt-4 text-sm text-slate-600">
                                            <p className="flex items-center gap-3"><i className="fa-solid fa-location-dot text-secondary w-5 text-center"></i> 1234 Healthcare Blvd, New York, NY</p>
                                            <p className="flex items-center gap-3"><i className="fa-solid fa-phone text-secondary w-5 text-center"></i> +1 (555) 234-5678</p>
                                            <p className="flex items-center gap-3"><i className="fa-solid fa-envelope text-secondary w-5 text-center"></i> care@carepulse.com</p>
                                        </div>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={150}>
                                    <div className="bg-warm p-6 rounded-2xl border border-slate-200">
                                        <h4 className="font-bold text-slate-800 flex items-center gap-2"><i className="fa-solid fa-clock text-secondary"></i> Working Hours</h4>
                                        <div className="space-y-2 mt-4 text-sm">
                                            <div className="flex justify-between"><span className="text-slate-500">Mon — Fri</span><span className="font-semibold text-slate-700">8:00 AM — 9:00 PM</span></div>
                                            <div className="flex justify-between"><span className="text-slate-500">Saturday</span><span className="font-semibold text-slate-700">9:00 AM — 6:00 PM</span></div>
                                            <div className="flex justify-between"><span className="text-slate-500">Sunday</span><span className="font-semibold text-slate-700">10:00 AM — 4:00 PM</span></div>
                                            <div className="flex justify-between pt-1 border-t border-slate-200"><span className="text-slate-500">Emergency</span><span className="font-semibold text-emerald-600">24/7 Open</span></div>
                                        </div>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={200}>
                                    <div className="bg-gradient-to-br from-primary to-primary-dark text-white p-6 rounded-2xl shadow-lg shadow-primary/15">
                                        <div className="flex items-center gap-4">
                                            <div className="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center animate-pulse-ring">
                                                <i className="fa-solid fa-phone-volume text-xl"></i>
                                            </div>
                                            <div>
                                                <p className="font-bold text-sm">Emergency Hotline 24/7</p>
                                                <p className="text-2xl font-black font-outfit">+1 (555) 911</p>
                                            </div>
                                        </div>
                                    </div>
                                </FadeIn>
                                <FadeIn delay={250}>
                                    <img src={IMG.lobby} alt="Hospital reception" className="rounded-2xl border border-slate-200 w-full h-36 object-cover shadow-sm" />
                                </FadeIn>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }

        // ======================================================
        //  FOOTER — wave, newsletter, enhanced layout
        // ======================================================
        function Footer() {
            const [email, setEmail] = useState('');
            return (
                <footer className="bg-primary-dark text-white/80 pt-16 pb-8 footer-wave">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-white/10">
                            {/* Brand */}
                            <div>
                                <div className="flex items-center gap-2 mb-4">
                                    <div className="w-9 h-9 bg-gradient-to-br from-secondary to-secondary-dark rounded-xl flex items-center justify-center shadow-md">
                                        <i className="fa-solid fa-heart-pulse text-white text-xs"></i>
                                    </div>
                                    <span className="font-outfit font-black text-lg text-white">Care<span className="text-secondary-light">Pulse</span></span>
                                </div>
                                <p className="text-sm text-white/50 leading-relaxed">Advanced healthcare ecosystem delivering precision medicine and compassionate care since 1997.</p>
                                <div className="flex gap-3 mt-5">
                                    {[
                                        { icon: 'fa-facebook-f', hover: 'hover:bg-blue-600' },
                                        { icon: 'fa-x-twitter', hover: 'hover:bg-slate-700' },
                                        { icon: 'fa-linkedin-in', hover: 'hover:bg-blue-700' },
                                        { icon: 'fa-instagram', hover: 'hover:bg-pink-600' },
                                    ].map((s, i) => (
                                        <a key={i} href="#" className={`w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center ${s.hover} transition-all duration-300`}>
                                            <i className={`fa-brands ${s.icon} text-sm`}></i>
                                        </a>
                                    ))}
                                </div>
                            </div>

                            {/* Quick links */}
                            <div>
                                <h5 className="font-bold text-white mb-5 text-sm tracking-wide">Quick Links</h5>
                                <ul className="space-y-2.5">
                                    {['Home', 'About', 'Departments', 'Doctors', 'Services', 'FAQ', 'Contact'].map(l => (
                                        <li key={l}><button onClick={() => document.getElementById(l.toLowerCase()).scrollIntoView({behavior:'smooth'})} className="text-sm text-white/50 hover:text-secondary-light transition-colors duration-200 flex items-center gap-2"><i className="fa-solid fa-chevron-right text-[8px] text-secondary/50"></i> {l}</button></li>
                                    ))}
                                </ul>
                            </div>

                            {/* Specialties */}
                            <div>
                                <h5 className="font-bold text-white mb-5 text-sm tracking-wide">Specialties</h5>
                                <ul className="space-y-2.5 text-sm text-white/50">
                                    {['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'Gynecology', 'Emergency Medicine'].map(s => (
                                        <li key={s} className="flex items-center gap-2"><i className="fa-solid fa-chevron-right text-[8px] text-secondary/50"></i> {s}</li>
                                    ))}
                                </ul>
                            </div>

                            {/* Newsletter */}
                            <div>
                                <h5 className="font-bold text-white mb-5 text-sm tracking-wide">Stay Updated</h5>
                                <p className="text-sm text-white/50 mb-4">Subscribe for health tips and hospital updates.</p>
                                <form onSubmit={e => { e.preventDefault(); setEmail(''); }} className="flex gap-2">
                                    <input type="email" placeholder="Your email" className="flex-1 px-4 py-2.5 rounded-xl bg-white/10 border border-white/10 text-white placeholder:text-white/30 text-sm outline-none focus:border-secondary/50 transition" value={email} onChange={e => setEmail(e.target.value)} />
                                    <button type="submit" className="px-4 py-2.5 bg-secondary rounded-xl text-white font-bold text-sm hover:bg-secondary-light transition-colors">
                                        <i className="fa-solid fa-paper-plane"></i>
                                    </button>
                                </form>
                                <div className="mt-5 p-3 rounded-xl bg-white/5 border border-white/10">
                                    <div className="flex items-center gap-2 text-xs">
                                        <i className="fa-solid fa-shield-halved text-secondary text-sm"></i>
                                        <span className="text-white/40">Your data is protected under HIPAA compliance.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-white/30">
                            <p>&copy; 2025 CarePulse Health Systems. All rights reserved.</p>
                            <div className="flex gap-6">
                                <a href="#" className="hover:text-white/60 transition">Privacy Policy</a>
                                <a href="#" className="hover:text-white/60 transition">Terms of Service</a>
                                <a href="#" className="hover:text-white/60 transition">HIPAA Notice</a>
                            </div>
                        </div>
                    </div>
                </footer>
            );
        }

        // ======================================================
        //  SCROLL TO TOP
        // ======================================================
        function ScrollTop() {
            const y = useScrollY();
            return (
                <button onClick={() => window.scrollTo({top:0,behavior:'smooth'})} className={`fixed bottom-6 right-6 z-50 w-12 h-12 rounded-full bg-primary text-white shadow-lg shadow-primary/20 flex items-center justify-center hover:bg-primary-dark hover:scale-110 transition-all duration-300 ${y > 400 ? 'opacity-100 scale-100' : 'opacity-0 scale-75 pointer-events-none'}`}>
                    <i className="fa-solid fa-arrow-up"></i>
                </button>
            );
        }

        // ======================================================
        //  APP
        // ======================================================
        function App() {
            return (
                <div className="min-h-screen flex flex-col">
                    <Navbar />
                    <main className="flex-1">
                        <Hero />
                        <Stats />
                        <Insurances />
                        <About />
                        <Facilities />
                        <Departments />
                        <Doctors />
                        <Services />
                        <Testimonials />
                        <FAQ />
                        <CTA />
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