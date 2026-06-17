<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050B14] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'VXI FloorSight - Login') }}</title>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        vxi: {
                            red: '#E31B23',
                            'red-light': '#FF2E37',
                            'red-dark': '#B61219',
                            navy: '#001C3D',
                            'navy-light': '#002D62',
                            'navy-dark': '#001024',
                            midnight: '#050B14',
                            slate: '#0F1E33',
                            cyan: '#22d3ee',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Cyberpunk Grid Overlay */
        .cyber-grid {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(227, 27, 35, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(227, 27, 35, 0.03) 1px, transparent 1px);
        }
        
        /* Subtle scanlines to match command-center aesthetic */
        .scanlines {
            background: linear-gradient(
                rgba(18, 30, 49, 0) 50%, 
                rgba(0, 0, 0, 0.25) 50%
            );
            background-size: 100% 4px;
        }
    </style>
</head>
<body class="antialiased h-full flex flex-col justify-between bg-[#050B14] relative overflow-hidden select-none">

    <!-- Ambient glowing nodes in background -->
    <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-vxi-red/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-96 h-96 bg-vxi-cyan/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Background Grid and Scanline Layers -->
    <div class="absolute inset-0 cyber-grid pointer-events-none z-0"></div>
    <div class="absolute inset-0 scanlines pointer-events-none z-0 opacity-40"></div>

    <!-- MAIN VIEWPORT CONTAINER -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 relative z-10">
        
        <div class="w-full max-w-md">
            
            <!-- VXI CENTRAL BRAND BADGE -->
            <div class="flex flex-col items-center mb-8">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-vxi-red text-white font-black text-3xl tracking-tighter shadow-xl shadow-vxi-red/30 mb-4 transform hover:scale-105 transition duration-300">
                    VXI
                </div>
                <div class="text-center">
                    <h2 class="text-xs font-black tracking-[0.25em] text-vxi-red uppercase">DAVAO CENTRALE SITE</h2>
                    <h1 class="text-xl font-bold text-white tracking-wide mt-1">FloorSight Security Access</h1>
                    <p class="text-[10px] text-slate-500 font-mono mt-1 uppercase">FELCRIS CENTRALE SITE GATEWAY</p>
                </div>
            </div>

            <!-- LOGIN CARD -->
            <div class="border border-vxi-navy/30 bg-vxi-midnight/80 backdrop-blur-xl rounded-2xl p-8 shadow-2xl shadow-black/80 relative overflow-hidden">
                
                <!-- Red edge-glow accent -->
                <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-vxi-red to-transparent"></div>

                <!-- Session Status Alerts -->
                @if (session('status'))
                    <div class="mb-5 p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- AUTHENTICATION FORM -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address Input -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Enterprise Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i data-lucide="mail" class="h-4 w-4"></i>
                            </span>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                autocomplete="username"
                                placeholder="name@vxi.com"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 py-2.5 pl-10 pr-4 text-sm text-slate-200 placeholder-slate-600 focus:border-vxi-red focus:outline-none focus:ring-1 focus:ring-vxi-red transition"
                            >
                        </div>
                        @if ($errors->has('email'))
                            <p class="text-[11px] text-vxi-red font-semibold font-mono mt-1">
                                {{ $errors->first('email') }}
                            </p>
                        @endif
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Secret Key / Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-slate-500 hover:text-vxi-red transition">
                                    Forgot Password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i data-lucide="lock" class="h-4 w-4"></i>
                            </span>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="••••••••••••"
                                class="block w-full rounded-lg border border-slate-800 bg-slate-950/80 py-2.5 pl-10 pr-4 text-sm text-slate-200 placeholder-slate-600 focus:border-vxi-red focus:outline-none focus:ring-1 focus:ring-vxi-red transition"
                            >
                        </div>
                        @if ($errors->has('password'))
                            <p class="text-[11px] text-vxi-red font-semibold font-mono mt-1">
                                {{ $errors->first('password') }}
                            </p>
                        @endif
                    </div>

                    <!-- Remember Me Toggle -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input 
                                id="remember_me" 
                                type="checkbox" 
                                name="remember"
                                class="rounded border-slate-800 bg-slate-950 text-vxi-red focus:ring-vxi-red/50 focus:ring-offset-slate-950 h-4 w-4"
                            >
                            <span class="ms-2 text-xs font-semibold text-slate-400 select-none">Keep console active</span>
                        </label>
                    </div>

                    <!-- Submit Operations Authentication -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full flex items-center justify-center space-x-2 py-3 px-4 bg-vxi-red hover:bg-vxi-red-dark text-white rounded-lg text-xs font-black tracking-widest uppercase transition-all duration-200 shadow-lg shadow-vxi-red/20 active:scale-[0.98]"
                        >
                            <span>Authenticate credentials</span>
                            <i data-lucide="shield-check" class="h-4 w-4"></i>
                        </button>
                    </div>

                </form>

            </div>

            <!-- Bottom Registration Hook -->
            @if (Route::has('register'))
                <p class="text-center text-xs text-slate-500 mt-6">
                    Need console access? 
                    <a href="{{ route('register') }}" class="font-bold text-slate-300 hover:text-vxi-red transition ml-1">
                        Submit IT Provision Request
                    </a>
                </p>
            @endif

        </div>

    </main>

    <!-- FOOTER COPYRIGHT -->
    <footer class="py-6 px-6 text-center text-[10px] text-slate-600 relative z-10 shrink-0">
        <p>© {{ date('Y') }} VXI Global Solutions • Felcris Centrale IT Ops • Davao City</p>
    </footer>

    <!-- SCRIPT INITS -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>