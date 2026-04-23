<x-layouts.auth title="Inscription • Scrimly">
    <main class="lg:grid lg:grid-cols-12 bg-bg-main min-h-screen p-6 gap-6 lg:p-0 flex flex-col items-center justify-center">
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-[40px] text-white font-bold">{!! __('register/register.welcome') !!}</h1>
                <p class="text-white text-xl">
                    {{ __('register/register.welcome_description') }}
                </p>
            </div>
            <div class="bg-bg-widget p-6 shadow-basic   flex flex-col gap-8 lg:pr-6">
                <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4 align-start">
                    @csrf
                    <legend class="text-2xl text-gold border-b border-gold pb-4 font-bold">{{ __('register/register.form_title') }}</legend>
                    <div class="flex flex-col gap-4">
                        <x-forms.input :name="'username'" :label="__('register/register.username')" :type="'text'" :required="true" :placeholder="'Faker'">
                            @error('username')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <x-forms.input :name="'riot_tag'" :label="__('register/register.riot_id')" :type="'text'" :placeholder="'HideOnBush#KR'">
                            @error('riot_tag')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <x-forms.input :name="'email'" :label="__('register/register.email')" :type="'email'" :required="true" :placeholder="'email@example.com'">
                            @error('email')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <div class="flex flex-col gap-2">
                            <label class="block text-white font-medium" for="password">
                                {{ __('register/register.password') }}<span class="text-gold font-semibold">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    required
                                    class="bg-input-bg border border-input-border py-2 pl-4 pr-12 text-white w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-200"
                                    placeholder="{{ __('register/register.password_placeholder') }}">
                                <button
                                    type="button"
                                    data-password-toggle="password"
                                    aria-label="{{ __('register/register.toggle_password') }}"
                                    aria-pressed="false"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-gold transition-colors">
                                    <svg data-icon="show" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg data-icon="hide" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="hidden">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                    </div>
                    <x-forms.submit class="w-full lg:w-fit lg:self-start">{{ __('register/register.register') }}</x-forms.submit>
                </form>
            </div>
            <p class="text-white text-center lg:text-left">
                {{ __('register/register.already_registered') }} <x-cta href="{{ route('login') }}" :title="__('register/register.title_cta')" :class="'underline'">{{ __('register/register.login') }}</x-cta>
            </p>
        </div>

        <div class="hidden lg:block lg:col-span-7">
            <img src="{{ asset('/img/SwainHextech.webp') }}" alt="Seju Hextech" class="min-h-screen w-full object-cover">
        </div>

    </main>
</x-layouts.auth>