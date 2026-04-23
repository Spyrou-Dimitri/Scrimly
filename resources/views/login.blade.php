<x-layouts.auth title="Connexion • Scrimly">
    <main class="lg:grid lg:grid-cols-12 bg-bg-main min-h-screen p-6 gap-6 lg:p-0 flex flex-col items-center justify-center">
        <div class="hidden lg:block lg:col-span-7">
            <img src="{{ asset('/img/SejuHextech.jpg') }}" alt="Seju Hextech" class="min-h-screen w-full object-cover">
        </div>
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-[40px] text-white font-bold">{!! __('login/login.welcome') !!}</h1>
                <p class="text-white text-xl">{{ __('login/login.welcome_description') }}</p>
            </div>
            <div class="bg-bg-widget p-6 shadow-basic flex flex-col gap-8 lg:pr-6">
                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6 align-start">
                    @csrf
                    <legend class="text-2xl text-gold border-b border-gold pb-4 font-bold">{{ __('login/login.form_title') }}</legend>
                    <div class="flex flex-col gap-4">
                        <x-forms.input :name="'email'" :label="__('login/login.email')" :type="'email'" :required="true" :placeholder="'john.doe@example.com'">
                            @error('email')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <x-forms.input :name="'password'" :label="__('login/login.password')" :type="'password'" :required="true" :placeholder="'**************'">
                            @error('password')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                            <div class="flex flex-row gap-2">
                                <input type="checkbox" name="remember" class="accent-gold">
                                <label class="text-white">
                                    {{ __('login/login.remember_me') }}
                                </label>
                            </div>
                        </x-forms.input>
                    </div>

                    <x-forms.submit class="w-full lg:w-fit lg:self-start">{{ __('login/login.login') }}</x-forms.submit>
                </form>

            </div>
            <p class="text-white text-center lg:text-left">
                    {{ __('login/login.not_registered') }} <x-cta href="{{ route('register') }}" :title="__('login/login.title_cta')" :class="'underline'">{{ __('login/login.register') }}</x-cta>
                </p>
        </div>

    </main>
    <footer>
    </footer>
</x-layouts.auth>