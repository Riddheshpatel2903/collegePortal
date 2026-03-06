<section class="space-y-6">
    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-8 py-3 bg-rose-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 hover:shadow-xl shadow-rose-500/20 transition-all">{{ __('Initialize Deletion sequence') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}"
            class="p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800">
            @csrf
            @method('delete')

            <h2 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-tighter mb-4">
                {{ __('Final Confirmation Required') }}
            </h2>

            <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed">
                {{ __('Once your account is purged, all associated institutional data will be permanently erased. Please provide your secure access token to authorize this termination.') }}
            </p>

            <div class="mb-8">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Access Token
                    Verification</label>
                <x-text-input id="password" name="password" type="password"
                    class="block w-full bg-slate-50/50 dark:bg-slate-900/50 border-none rounded-2xl py-3.5 px-5 font-bold shadow-inner focus:ring-2 focus:ring-rose-500/30 transition-all dark:text-white"
                    placeholder="{{ __('Enter Security Key') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-4">
                <button type="button" x-on:click="$dispatch('close')"
                    class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    {{ __('Abort Mission') }}
                </button>

                <button type="submit"
                    class="px-8 py-3 bg-rose-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 hover:shadow-xl shadow-rose-500/20 transition-all">
                    {{ __('Confirm Purge') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
