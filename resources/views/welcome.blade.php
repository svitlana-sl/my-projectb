@auth
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Best Paws Pet Care</title>

        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="icon" href="/favicon.ico">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen" style="background: linear-gradient(135deg, #7e5bef 0%, #a78bfa 100%);">
            @livewire('navigation-menu')

            <!-- Page Content -->
            <main>
                <div class="relative flex flex-col items-center justify-center text-center w-full"
                     style="padding: 80px 20px; min-height: 80vh;">

                    <!-- Назва проєкту -->
                    <h1 style="color: white; font-size: 5rem; font-weight: 800; margin-bottom: 1rem; line-height: 1;">
                        Best Paws Pet Care
                    </h1>

                    <!-- Підзаголовок -->
                    <p style="color: #e9d5ff; font-size: 1.5rem; font-weight: 600; margin-bottom: 3rem; line-height: 1.2;">
                        Your trusted pet sitting service
                    </p>

                    <!-- Кнопки -->
                    <div class="flex flex-row gap-6 justify-center">
                        <a href="/api/documentation" target="_blank"
                           style="display: inline-flex; align-items: center; justify-content: center; padding: 1rem 2.5rem; border-radius: 9999px; color: white; background: linear-gradient(to right, #9333ea, #7c3aed); font-size: 1rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); min-width: 220px; text-decoration: none;"
                           onmouseover="this.style.background='linear-gradient(to right, #7c3aed, #6d28d9)'"
                           onmouseout="this.style.background='linear-gradient(to right, #9333ea, #7c3aed)'">
                            API Documentation
                        </a>

                        <a href="/admin"
                           style="display: inline-flex; align-items: center; justify-content: center; padding: 1rem 2.5rem; border-radius: 9999px; color: white; background: linear-gradient(to right, #8b5cf6, #7c3aed); font-size: 1rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); min-width: 220px; text-decoration: none;"
                           onmouseover="this.style.background='linear-gradient(to right, #7c3aed, #6d28d9)'"
                           onmouseout="this.style.background='linear-gradient(to right, #8b5cf6, #7c3aed)'">
                            Admin Panel
                        </a>
                    </div>
                </div>
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
@else
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Best Paws Pet Care</title>

        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="icon" href="/favicon.ico">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen" style="background: linear-gradient(135deg, #7e5bef 0%, #a78bfa 100%);">
            <!-- Navigation for unauthenticated users -->
            @if (Route::has('login'))
                <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
                    <!-- Primary Navigation Menu -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center">
                                    <a href="{{ url('/') }}">
                                        <img src="/android-chrome-192x192.png" alt="Best Paws Pet Care" class="block h-9 w-auto">
                                    </a>
                                </div>

                                <!-- Navigation Links -->
                                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                    <a href="{{ url('/') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                        Home
                                    </a>
                                    <a href="/api/documentation" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        API Documentation
                                    </a>
                                </div>
                            </div>

                            <!-- Authentication Links -->
                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <div class="flex items-center space-x-4">
                                    <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                        Log in
                                    </a>

                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Register
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Hamburger -->
                            <div class="-me-2 flex items-center sm:hidden">
                                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Navigation Menu -->
                    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                        <div class="pt-2 pb-3 space-y-1">
                            <a href="{{ url('/') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out">
                                Home
                            </a>
                            <a href="/api/documentation" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                                API Documentation
                            </a>
                        </div>

                        <!-- Responsive Authentication Options -->
                        <div class="pt-4 pb-1 border-t border-gray-200">
                            <div class="mt-3 space-y-1">
                                <a href="{{ route('login') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Register
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </nav>
            @endif

            <!-- Page Content -->
            <main>
                <div class="relative flex flex-col items-center justify-center text-center w-full"
                     style="padding: 80px 20px; min-height: 80vh;">

                    <!-- Назва проєкту -->
                    <h1 style="color: white; font-size: 5rem; font-weight: 800; margin-bottom: 1rem; line-height: 1;">
                        Best Paws Pet Care
                    </h1>

                    <!-- Підзаголовок -->
                    <p style="color: #e9d5ff; font-size: 1.5rem; font-weight: 600; margin-bottom: 3rem; line-height: 1.2;">
                        Your trusted pet sitting service
                    </p>

                    <!-- Кнопки -->
                    <div class="flex flex-row gap-6 justify-center">
                        <a href="/api/documentation" target="_blank"
                           style="display: inline-flex; align-items: center; justify-content: center; padding: 1rem 2.5rem; border-radius: 9999px; color: white; background: linear-gradient(to right, #9333ea, #7c3aed); font-size: 1rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); min-width: 220px; text-decoration: none;"
                           onmouseover="this.style.background='linear-gradient(to right, #7c3aed, #6d28d9)'"
                           onmouseout="this.style.background='linear-gradient(to right, #9333ea, #7c3aed)'">
                            API Documentation
                        </a>

                        <a href="/admin"
                           style="display: inline-flex; align-items: center; justify-content: center; padding: 1rem 2.5rem; border-radius: 9999px; color: white; background: linear-gradient(to right, #8b5cf6, #7c3aed); font-size: 1rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); min-width: 220px; text-decoration: none;"
                           onmouseover="this.style.background='linear-gradient(to right, #7c3aed, #6d28d9)'"
                           onmouseout="this.style.background='linear-gradient(to right, #8b5cf6, #7c3aed)'">
                            Admin Panel
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
@endauth
