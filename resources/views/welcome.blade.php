<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BoxerOS — Your Corner, In Your Pocket</title>
    <meta name="description" content="The all-in-one training companion for professional boxers. Track weight, nutrition, hydration, injuries and fights — and talk to an AI coach that actually knows you.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --blood: #c0392b;
            --blood-dark: #96281b;
            --gold: #f39c12;
            --ink: #0a0a0f;
            --muted: rgba(255,255,255,0.5);
            --muted-2: rgba(255,255,255,0.38);
            --line: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--ink);
            color: #f0f0f0;
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, .display { font-family: 'Rajdhani', sans-serif; }
        a { text-decoration: none; }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 1.5rem; }

        /* Background atmosphere */
        .bg-blob { position: fixed; border-radius: 50%; filter: blur(130px); pointer-events: none; z-index: 0; }
        .blob-red  { width: 620px; height: 620px; background: rgba(192,57,43,0.16); top: -160px; right: -160px; }
        .blob-gold { width: 460px; height: 460px; background: rgba(243,156,18,0.07); bottom: 40px; left: -160px; }
        .grid-lines {
            position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.5;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 30%, transparent 75%);
        }

        /* Nav */
        .nav {
            position: sticky; top: 0; z-index: 50;
            backdrop-filter: blur(16px);
            background: rgba(10,10,15,0.7);
            border-bottom: 1px solid var(--line);
        }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; }
        .logo { font-family: 'Rajdhani', sans-serif; font-size: 1.5rem; font-weight: 700; letter-spacing: 0.5px; }
        .logo .r { color: var(--blood); }
        .nav-links { display: flex; gap: 0.6rem; align-items: center; }
        .btn-ghost {
            border: 1px solid rgba(255,255,255,0.15); color: #f0f0f0;
            padding: 0.55rem 1.2rem; border-radius: 10px; font-size: 0.875rem; font-weight: 500;
            transition: all 0.2s;
        }
        .btn-ghost:hover { border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.05); }
        .btn-red {
            background: linear-gradient(135deg, var(--blood), var(--blood-dark)); color: #fff;
            padding: 0.55rem 1.2rem; border-radius: 10px; font-size: 0.875rem; font-weight: 600;
            transition: all 0.2s;
        }
        .btn-red:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(192,57,43,0.45); }

        /* Hero */
        .hero { position: relative; z-index: 1; padding: 3.5rem 0 2rem; }
        .hero-grid { display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: center; }
        @media (min-width: 900px) {
            .hero { padding: 5rem 0 3.5rem; }
            .hero-grid { grid-template-columns: 1.05fr 0.95fr; gap: 3rem; }
        }
        .tag {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(192,57,43,0.13); border: 1px solid rgba(192,57,43,0.3); color: #e74c3c;
            font-size: 0.7rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
            padding: 0.4rem 0.9rem; border-radius: 999px; margin-bottom: 1.5rem;
        }
        .hero h1 {
            font-size: clamp(2.6rem, 7vw, 4.4rem); font-weight: 700; line-height: 1.04;
            letter-spacing: -1px; margin-bottom: 1.25rem;
        }
        .hero h1 .grad {
            background: linear-gradient(120deg, var(--blood) 0%, var(--gold) 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero p.lead {
            font-size: 1.08rem; line-height: 1.7; color: var(--muted);
            margin-bottom: 2rem; max-width: 30rem;
        }
        .cta-row { display: flex; flex-wrap: wrap; gap: 0.8rem; align-items: center; }
        .btn-hero {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, var(--blood), var(--blood-dark)); color: #fff;
            padding: 0.95rem 1.8rem; border-radius: 13px; font-size: 1rem; font-weight: 700;
            font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; transition: all 0.2s;
        }
        .btn-hero:hover { transform: translateY(-2px); box-shadow: 0 12px 34px rgba(192,57,43,0.5); }
        .btn-text { color: var(--muted); font-weight: 500; padding: 0.95rem 0.5rem; transition: color 0.2s; }
        .btn-text:hover { color: #fff; }

        /* Hero stat row */
        .hero-stats { display: flex; gap: 2rem; margin-top: 2.5rem; flex-wrap: wrap; }
        .hstat .num { font-family: 'Rajdhani', sans-serif; font-size: 1.9rem; font-weight: 700; line-height: 1; }
        .hstat .num.gold { color: var(--gold); }
        .hstat .num.red { color: var(--blood); }
        .hstat .lbl { font-size: 0.72rem; color: var(--muted-2); text-transform: uppercase; letter-spacing: 0.6px; margin-top: 0.35rem; }

        /* Phone mockup */
        .phone-col { display: flex; justify-content: center; }
        .phone {
            width: min(340px, 90vw); background: #111118; border-radius: 38px;
            border: 1.5px solid rgba(255,255,255,0.09); padding: 1.25rem;
            box-shadow: 0 50px 120px rgba(0,0,0,0.65), 0 0 70px rgba(192,57,43,0.12);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        .phone-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .phone-logo { font-family: 'Rajdhani', sans-serif; font-size: 1rem; font-weight: 700; }
        .phone-logo .r { color: var(--blood); }
        .phone-date { font-size: 0.65rem; color: rgba(255,255,255,0.3); }
        .pcard { background: #1a1a26; border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 1rem; margin-bottom: 0.75rem; }
        .plabel { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.3); margin-bottom: 0.5rem; }
        .pfighter { display: flex; align-items: center; gap: 0.75rem; }
        .pavatar { width: 44px; height: 44px; background: linear-gradient(135deg, var(--blood-dark), var(--blood)); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .pname { font-family: 'Rajdhani', sans-serif; font-size: 1.1rem; font-weight: 700; }
        .psub { font-size: 0.65rem; color: rgba(255,255,255,0.4); margin-top: 2px; }
        .precord { display: flex; gap: 1rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.05); }
        .pstat { text-align: center; }
        .pstat .n { font-family: 'Rajdhani', sans-serif; font-size: 1.3rem; font-weight: 700; }
        .pstat .l { font-size: 0.55rem; color: rgba(255,255,255,0.3); text-transform: uppercase; }
        .pfight { background: rgba(243,156,18,0.06); border: 1px solid rgba(243,156,18,0.15); border-radius: 14px; padding: 0.875rem; margin-bottom: 0.75rem; }
        .pfight .lab { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--gold); margin-bottom: 0.35rem; }
        .pfight .nm { font-family: 'Rajdhani', sans-serif; font-size: 1rem; font-weight: 700; }
        .pfight .dy { font-size: 0.65rem; color: rgba(255,255,255,0.4); }
        .prow { display: flex; justify-content: space-between; font-size: 0.65rem; margin-bottom: 0.35rem; }
        .prow span:first-child { color: rgba(255,255,255,0.4); }
        .ptrack { background: rgba(255,255,255,0.06); border-radius: 999px; height: 6px; overflow: hidden; margin-bottom: 0.5rem; }
        .pfill { height: 100%; border-radius: 999px; }
        .pgrid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .pmini { background: #1a1a26; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 0.75rem; }
        .pmini .l { font-size: 0.55rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem; }
        .pmini .v { font-family: 'Rajdhani', sans-serif; font-size: 1.1rem; font-weight: 700; }

        /* Section scaffolding */
        section.block { position: relative; z-index: 1; padding: 4rem 0; }
        .eyebrow { text-align: center; color: var(--blood); font-size: 0.72rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 0.75rem; }
        .section-title { text-align: center; font-family: 'Rajdhani', sans-serif; font-size: clamp(1.8rem, 4vw, 2.4rem); font-weight: 700; margin-bottom: 0.6rem; }
        .section-sub { text-align: center; font-size: 0.95rem; color: var(--muted-2); margin: 0 auto 2.5rem; max-width: 34rem; }

        /* Value strip */
        .value-strip { border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); background: rgba(255,255,255,0.015); }
        .value-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem 1rem; padding: 2rem 0; }
        @media (min-width: 760px) { .value-grid { grid-template-columns: repeat(4, 1fr); } }
        .value-item { text-align: center; }
        .value-item .n { font-family: 'Rajdhani', sans-serif; font-size: 2rem; font-weight: 700; color: var(--gold); }
        .value-item .t { font-size: 0.78rem; color: var(--muted-2); margin-top: 0.25rem; }

        /* Features */
        .feature-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        @media (min-width: 620px) { .feature-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 960px) { .feature-grid { grid-template-columns: repeat(3, 1fr); } }
        .fcard {
            background: #111118; border: 1px solid rgba(255,255,255,0.07); border-radius: 18px;
            padding: 1.5rem; transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }
        .fcard:hover { transform: translateY(-4px); border-color: rgba(192,57,43,0.4); box-shadow: 0 18px 40px rgba(0,0,0,0.4); }
        .ficon { font-size: 1.75rem; margin-bottom: 0.75rem; display: inline-block; }
        .ftitle { font-family: 'Rajdhani', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.4rem; }
        .fdesc { font-size: 0.85rem; line-height: 1.6; color: var(--muted-2); }

        /* AI spotlight */
        .spotlight { display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: center; }
        @media (min-width: 900px) { .spotlight { grid-template-columns: 1fr 1fr; gap: 3.5rem; } }
        .spotlight h2 { font-family: 'Rajdhani', sans-serif; font-size: clamp(1.8rem, 4vw, 2.4rem); font-weight: 700; margin-bottom: 1rem; line-height: 1.1; }
        .spotlight p { color: var(--muted); line-height: 1.7; margin-bottom: 1rem; }
        .check { display: flex; align-items: flex-start; gap: 0.65rem; margin-bottom: 0.7rem; font-size: 0.92rem; color: rgba(255,255,255,0.7); }
        .check .ic { color: var(--gold); font-weight: 700; flex-shrink: 0; }
        .chat-mock {
            background: #111118; border: 1px solid var(--line); border-radius: 20px; padding: 1.25rem;
            box-shadow: 0 30px 70px rgba(0,0,0,0.5);
        }
        .bubble { padding: 0.75rem 1rem; font-size: 0.875rem; line-height: 1.55; margin-bottom: 0.75rem; max-width: 85%; }
        .b-user { background: linear-gradient(135deg, var(--blood-dark), var(--blood)); border-radius: 16px 16px 4px 16px; margin-left: auto; }
        .b-ai { background: #1a1a2e; border: 1px solid var(--line); border-radius: 16px 16px 16px 4px; color: rgba(255,255,255,0.85); }
        .b-ai .who { font-size: 0.62rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--gold); margin-bottom: 0.3rem; }

        /* Bottom CTA */
        .final-cta { position: relative; z-index: 1; text-align: center; padding: 4.5rem 1.5rem; }
        .final-card {
            max-width: 720px; margin: 0 auto; border-radius: 24px; padding: 3rem 1.5rem;
            background: radial-gradient(ellipse at top, rgba(192,57,43,0.18), transparent 70%), #111118;
            border: 1px solid rgba(192,57,43,0.25);
        }
        .final-card h2 { font-family: 'Rajdhani', sans-serif; font-size: clamp(2rem, 5vw, 2.8rem); font-weight: 700; margin-bottom: 0.75rem; }
        .final-card p { color: var(--muted); margin-bottom: 1.75rem; }

        footer { position: relative; z-index: 1; border-top: 1px solid var(--line); padding: 1.75rem 0; }
        .foot-inner { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; justify-content: space-between; }
        .foot-inner span { font-size: 0.78rem; color: var(--muted-2); }

        /* Entrance animation (CSS-only, ends visible) */
        .rise { opacity: 0; animation: rise 0.7s ease forwards; }
        @keyframes rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        .d1 { animation-delay: 0.05s; } .d2 { animation-delay: 0.15s; } .d3 { animation-delay: 0.25s; }
        .d4 { animation-delay: 0.35s; } .d5 { animation-delay: 0.45s; }
        @media (prefers-reduced-motion: reduce) {
            .rise { opacity: 1; animation: none; } .phone { animation: none; }
        }
    </style>
</head>
<body>

    <div class="bg-blob blob-red"></div>
    <div class="bg-blob blob-gold"></div>
    <div class="grid-lines"></div>

    {{-- Nav --}}
    <header class="nav">
        <div class="wrap nav-inner">
            <div class="logo"><span class="r">BOXER</span>OS</div>
            <div class="nav-links">
                <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                <a href="{{ route('register') }}" class="btn-red">Get Started</a>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="hero">
        <div class="wrap hero-grid">
            <div>
                <div class="tag rise d1">🥊 Built for professional fighters</div>
                <h1 class="rise d2">Your corner.<br><span class="grad">In your pocket.</span></h1>
                <p class="lead rise d3">
                    The all-in-one command center for serious boxers. Track weight, nutrition,
                    hydration, injuries and fight camps — then talk to an AI coach that actually
                    knows your numbers.
                </p>
                <div class="cta-row rise d4">
                    <a href="{{ route('register') }}" class="btn-hero">Create Free Account →</a>
                    <a href="{{ route('login') }}" class="btn-text">I already have an account</a>
                </div>
                <div class="hero-stats rise d5">
                    <div class="hstat"><div class="num gold">7</div><div class="lbl">Tracking tools</div></div>
                    <div class="hstat"><div class="num red">24/7</div><div class="lbl">AI coach</div></div>
                    <div class="hstat"><div class="num">$0</div><div class="lbl">To start</div></div>
                </div>
            </div>

            {{-- Phone mockup --}}
            <div class="phone-col rise d3">
                <div class="phone">
                    <div class="phone-bar">
                        <div class="phone-logo"><span class="r">BOXER</span>OS</div>
                        <div class="phone-date">Mon, Jun 22</div>
                    </div>

                    <div class="pcard">
                        <div class="plabel">Fighter</div>
                        <div class="pfighter">
                            <div class="pavatar">🥊</div>
                            <div>
                                <div class="pname">"Iron" Mike</div>
                                <div class="psub">Welterweight · Orthodox</div>
                            </div>
                        </div>
                        <div class="precord">
                            <div class="pstat"><div class="n" style="color:var(--gold);">12</div><div class="l">Wins</div></div>
                            <div class="pstat"><div class="n" style="color:var(--blood);">2</div><div class="l">Losses</div></div>
                            <div class="pstat"><div class="n">1</div><div class="l">Draw</div></div>
                        </div>
                    </div>

                    <div class="pfight">
                        <div class="lab">Next Fight</div>
                        <div class="nm">vs Carlos "Thunder" Reyes</div>
                        <div class="dy">18 days · MGM Grand, Las Vegas</div>
                    </div>

                    <div class="pcard">
                        <div class="plabel">Today</div>
                        <div class="prow"><span>💧 Water</span><span style="color:#3498db;">2.5 / 3L</span></div>
                        <div class="ptrack"><div class="pfill" style="width:83%; background: linear-gradient(90deg,#2980b9,#3498db);"></div></div>
                        <div class="prow"><span>🔥 Calories</span><span style="color:var(--gold);">2100 / 2500</span></div>
                        <div class="ptrack"><div class="pfill" style="width:84%; background: linear-gradient(90deg,#e67e22,#f39c12);"></div></div>
                    </div>

                    <div class="pgrid">
                        <div class="pmini">
                            <div class="l" style="color:#2ecc71;">Weight</div>
                            <div class="v">69.8<span style="font-size:0.65rem;color:rgba(255,255,255,0.3)"> kg</span></div>
                        </div>
                        <div class="pmini">
                            <div class="l" style="color:#e74c3c;">Injury</div>
                            <div class="v" style="font-size:0.85rem;color:var(--gold);">Recovering</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Value strip --}}
    <div class="value-strip">
        <div class="wrap value-grid">
            <div class="value-item"><div class="n">Daily</div><div class="t">Weight & body logs</div></div>
            <div class="value-item"><div class="n">Auto</div><div class="t">AI calorie estimates</div></div>
            <div class="value-item"><div class="n">Live</div><div class="t">Fight countdowns</div></div>
            <div class="value-item"><div class="n">Smart</div><div class="t">Injury recovery advice</div></div>
        </div>
    </div>

    {{-- Features --}}
    <section class="block">
        <div class="wrap">
            <div class="eyebrow">Everything in one place</div>
            <h2 class="section-title">Built for the whole camp</h2>
            <p class="section-sub">From the weigh-in to the final bell — every part of your preparation, tracked and connected.</p>

            <div class="feature-grid">
                <div class="fcard">
                    <span class="ficon">⚖️</span>
                    <div class="ftitle">Weight & Body</div>
                    <div class="fdesc">Daily weigh-ins, before/after sweat loss, and visual trend charts so your cut never surprises you.</div>
                </div>
                <div class="fcard">
                    <span class="ficon">🍽️</span>
                    <div class="ftitle">Nutrition</div>
                    <div class="fdesc">Log meals by name or photo. CORNER estimates calories automatically and tracks your daily intake.</div>
                </div>
                <div class="fcard">
                    <span class="ficon">💧</span>
                    <div class="ftitle">Hydration</div>
                    <div class="fdesc">One-tap water logging with quick-add buttons. Hit your daily goal every session.</div>
                </div>
                <div class="fcard">
                    <span class="ficon">🩹</span>
                    <div class="ftitle">Injury Log</div>
                    <div class="fdesc">Report injuries and get instant, practical AI recovery guidance — with return-to-training cues.</div>
                </div>
                <div class="fcard">
                    <span class="ficon">📅</span>
                    <div class="ftitle">Fight Calendar</div>
                    <div class="fdesc">Countdown to your next bout, opponent and venue details, and your full win/loss record.</div>
                </div>
                <div class="fcard" style="border-color: rgba(192,57,43,0.35);">
                    <span class="ficon">🤖</span>
                    <div class="ftitle">CORNER — AI Coach</div>
                    <div class="fdesc">A coach that knows your weight, meals, injuries and fight date. Ask anything, anytime.</div>
                </div>
            </div>
        </div>
    </section>

    {{-- AI coach spotlight --}}
    <section class="block" style="background: rgba(255,255,255,0.015); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line);">
        <div class="wrap spotlight">
            <div>
                <div class="eyebrow" style="text-align:left;">Meet CORNER</div>
                <h2>An AI coach that<br>actually knows you.</h2>
                <p>CORNER isn't a generic chatbot. It reads your real logs — this week's training,
                   your current weight versus your goal, active injuries, and how many days until
                   your next fight — then coaches you on what to do next.</p>
                <div class="check"><span class="ic">✓</span><span>Personalized weekly training & cut plans</span></div>
                <div class="check"><span class="ic">✓</span><span>Calorie & macro estimates from a photo</span></div>
                <div class="check"><span class="ic">✓</span><span>Injury recovery and return-to-training advice</span></div>
                <div class="check"><span class="ic">✓</span><span>Honest, direct answers — like a real corner</span></div>
            </div>
            <div class="chat-mock">
                <div class="bubble b-user">I'm 1.2kg over with 18 days to the fight. What do I do this week?</div>
                <div class="bubble b-ai">
                    <div class="who">CORNER</div>
                    You're on track, Mike — 1.2kg in 18 days is a controlled cut, not a crash. Keep
                    water at 3L through Thursday, drop the evening carbs, and we'll add one extra
                    roadwork session. Your energy's been 7/10 this week, so you've got room. I'll
                    map the full week if you want it.
                </div>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <div class="final-cta">
        <div class="final-card">
            <h2>Ready to take control?</h2>
            <p>Free to use. No credit card. Set up your fighter profile in under a minute.</p>
            <a href="{{ route('register') }}" class="btn-hero">Start Training Smarter →</a>
        </div>
    </div>

    {{-- Footer --}}
    <footer>
        <div class="wrap foot-inner">
            <div class="logo" style="font-size:1.2rem;"><span class="r">BOXER</span>OS</div>
            <span>© {{ date('Y') }} BoxerOS · Your corner, in your pocket.</span>
        </div>
    </footer>

</body>
</html>
