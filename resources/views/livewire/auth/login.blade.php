<div>
    <div
        class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-amber-50 via-white to-amber-50">
        <div class="max-w-6xl w-full flex flex-col lg:flex-row gap-12 items-center">

            {{-- Left Column: ERP Info (Hidden on mobile, visible on LG+) --}}
            <div class="hidden lg:flex flex-1 flex-col space-y-8 animate-fade-in">
                <div>
                    <h1 class="text-5xl font-extrabold text-gray-900 tracking-tight">
                        GDPS <span class="text-amber-600">ERP</span>
                    </h1>
                    <p class="mt-4 text-xl text-gray-600 leading-relaxed">
                        Integrated Enterprise Resource Planning for Garuda Daya Pratama Sejahtera.
                        Manage your workflow, finance, and operations in one unified platform.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">CRM & Sales</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Manage Lead Pipelines, Proposal
                            Generations & Contract Lifecycles (SPK). Integrated lead-to-deal tracking.</p>
                    </div>

                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Finance Core</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">SAP compatible General Ledger, automated
                            AP/AR, Treasury, and Real-time Project Budgeting.</p>
                    </div>

                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Project Management</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Master PI verification, automated Project
                            Code generation, and tiered Profitability Analysis.</p>
                    </div>

                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900">Logistics & Asset</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Procurement lifecycle (PR/PO), Multi-site
                            Inventory tracking, and Asset Management.</p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Login Form --}}
            <div class="max-w-md w-full space-y-8 flex-shrink-0">
                <div class="text-center lg:text-left lg:hidden">
                    <div
                        class="mx-auto h-16 w-16 lg:mx-0 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                        GDPS ERP System
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Sign in to your account
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <div class="mb-8 hidden lg:block">
                        <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
                        <p class="text-gray-500 mt-1 text-sm">Please enter your credentials to continue.</p>
                    </div>


                    <form wire:submit="login" class="space-y-6">
                        {{-- Email Field --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                    </svg>
                                </div>
                                <input wire:model="email" id="email" type="email" required autocomplete="email"
                                    class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200 @error('email') border-red-500 @enderror"
                                    placeholder="you@example.com">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password Field with Hide/Show Toggle --}}
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input wire:model="password" id="password" :type="show ? 'text' : 'password'" required
                                    autocomplete="current-password"
                                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200 @error('password') border-red-500 @enderror"
                                    placeholder="••••••••">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-amber-600 transition-colors">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="flex items-center">
                            <input wire:model="remember" id="remember" type="checkbox"
                                class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded cursor-pointer transition-colors">
                            <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer select-none">
                                Keep me signed in
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit"
                            class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <span>Sign in to Dashboard</span>
                            <svg class="h-4 w-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="pt-4 text-center">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">
                        &copy; {{ date('Y') }} PT Garuda Daya Pratama Sejahtera
                    </p>
                    <div class="mt-2 flex justify-center space-x-4">
                        <a href="#"
                            class="text-xs text-gray-400 hover:text-amber-600 transition-colors">Internal
                            Portal</a>
                        <span class="text-gray-300">•</span>
                        <a href="#"
                            class="text-xs text-gray-400 hover:text-amber-600 transition-colors">Support</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.8s ease-out forwards;
        }
    </style>
</div>
