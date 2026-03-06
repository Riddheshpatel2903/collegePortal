<section class="space-y-6">
    <form method="post" action="{{ route('password.update') }}" class="space-y-8">
        @csrf
        @method('put')

        <div class="space-y-6">
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Current Access
                    Token</label>
                <x-text-input id="update_password_current_password" name="current_password" type="password"
                    class="block w-full bg-gray-50/50 dark:bg-gray-900/50 border-none rounded-2xl py-3.5 px-5 font-bold shadow-inner focus:ring-2 focus:ring-indigo-500/30 transition-all dark:text-white"
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">New Security
                        Key</label>
                    <x-text-input id="update_password_password" name="password" type="password"
                        class="block w-full bg-gray-50/50 dark:bg-gray-900/50 border-none rounded-2xl py-3.5 px-5 font-bold shadow-inner focus:ring-2 focus:ring-indigo-500/30 transition-all dark:text-white"
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Verify Security
                        Key</label>
                    <x-text-input id="update_password_password_confirmation" name="password_confirmation"
                        type="password"
                        class="block w-full bg-gray-50/50 dark:bg-gray-900/50 border-none rounded-2xl py-3.5 px-5 font-bold shadow-inner focus:ring-2 focus:ring-indigo-500/30 transition-all dark:text-white"
                        autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <button type="submit"
                class="px-8 py-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 hover:shadow-xl shadow-indigo-500/20 transition-all">
                {{ __('Update Credentials') }}
            </button>

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest">
                    <i class="bi bi-shield-check"></i>
                    {{ __('Encryption Synchronized') }}
                </div>
            @endif
        </div>
    </form>
</section>