<section class="space-y-6">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-8">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Full Legal
                    Name</label>
                <x-text-input id="name" name="name" type="text"
                    class="block w-full bg-slate-50/50 dark:bg-slate-900/50 border-none rounded-2xl py-3.5 px-5 font-bold shadow-inner focus:ring-2 focus:ring-indigo-500/30 transition-all dark:text-white"
                    :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Institutional
                    Email</label>
                <x-text-input id="email" name="email" type="email"
                    class="block w-full bg-slate-50/50 dark:bg-slate-900/50 border-none rounded-2xl py-3.5 px-5 font-bold shadow-inner focus:ring-2 focus:ring-indigo-500/30 transition-all dark:text-white"
                    :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div class="mt-4">
                        <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-tighter italic">
                            {{ __('Verification Pending') }}
                            <button form="send-verification"
                                class="ml-2 underline text-[10px] font-black uppercase text-slate-400 hover:text-indigo-600 transition-colors">
                                {{ __('Request Transmission') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-[10px] font-black text-emerald-600 uppercase tracking-widest">
                                {{ __('Security Token Transmitted.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-6">
            <button type="submit"
                class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 hover:shadow-xl shadow-indigo-500/20 transition-all">
                {{ __('Commit Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ __('Database Synchronized') }}
                </div>
            @endif
        </div>
    </form>
</section>
