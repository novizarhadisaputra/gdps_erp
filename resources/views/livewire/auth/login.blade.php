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
                            <x-heroicon-o-presentation-chart-bar class="h-6 w-6" />
                        </div>
                        <h3 class="font-bold text-gray-900">CRM & Sales</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Manage Lead Pipelines, Proposal
                            Generations & Contract Lifecycles (SPK). Integrated lead-to-deal tracking.</p>
                    </div>

                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <x-heroicon-o-banknotes class="h-6 w-6" />
                        </div>
                        <h3 class="font-bold text-gray-900">Finance Core</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">SAP compatible General Ledger, automated
                            AP/AR, Treasury, and Real-time Project Budgeting.</p>
                    </div>

                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <x-heroicon-o-clipboard-document-check class="h-6 w-6" />
                        </div>
                        <h3 class="font-bold text-gray-900">Project Management</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Master PI verification, automated Project
                            Code generation, and tiered Profitability Analysis.</p>
                    </div>

                    <div
                        class="p-6 bg-white rounded-2xl shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div
                            class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-4">
                            <x-heroicon-o-truck class="h-6 w-6" />
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
                        <x-heroicon-o-computer-desktop class="h-10 w-10 text-white" />
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
                                    <x-heroicon-o-envelope class="h-5 w-5" />
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
                                    <x-heroicon-o-lock-closed class="h-5 w-5" />
                                </div>
                                <input wire:model="password" id="password" :type="show ? 'text' : 'password'" required
                                    autocomplete="current-password"
                                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200 @error('password') border-red-500 @enderror"
                                    placeholder="••••••••">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-amber-600 transition-colors cursor-pointer focus:outline-none">
                                    {{-- Eye Icon (Show Password) --}}
                                    <x-heroicon-o-eye x-show="!show" class="h-5 w-5" />

                                    {{-- Eye Slash Icon (Hide Password) --}}
                                    <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-5 w-5" />
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
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove>Sign in to Dashboard</span>
                            <x-heroicon-o-arrow-right wire:loading.remove class="h-4 w-4 ml-2" />

                            <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span wire:loading class="ml-2">Signing in...</span>
                        </button>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="pt-4 text-center">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">
                        &copy; {{ date('Y') }} PT Garuda Daya Pratama Sejahtera
                    </p>
                    <div class="mt-2 flex justify-center space-x-4">
                        <a href="#" class="text-xs text-gray-400 hover:text-amber-600 transition-colors">Internal
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
