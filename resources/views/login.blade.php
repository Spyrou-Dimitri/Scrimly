<x-layouts.auth :title="__('login/login.page_title')">
    <main id="main-content" aria-labelledby="login-heading" class="lg:grid lg:grid-cols-12 bg-bg-main flex-1 p-6 gap-6 lg:p-0 flex flex-col items-center justify-center">
        <div class="hidden lg:block lg:col-span-7" aria-hidden="true">
            <picture>
                <source media="(min-width: 1800px)" srcset="{{ asset('/img/SejuHextech.jpg') }}">
                <source media="(min-width: 1536px)" srcset="{{ asset('/img/SejuHextech1000x1000.jpg') }}">
                <img src="{{ asset('/img/SejuHextech800x800.jpg') }}" alt="{{ __('login/login.hero_image_alt') }}" class="min-h-screen w-full object-cover">
            </picture>
        </div>
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <x-auth.back-to-home />
                <h2 id="login-heading" class="text-[40px] text-white font-bold">{!! __('login/login.welcome') !!}</h2>
                <p class="text-white text-xl">{{ __('login/login.welcome_description') }}</p>
            </div>
            <div class="bg-bg-widget p-6 shadow-basic flex flex-col gap-8 lg:pr-6">
                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6 align-start">
                    @csrf
                    <fieldset class="flex flex-col gap-6">
                        <legend class="sr-only">{{ __('login/login.form_title') }}</legend>
                        <p class="text-2xl text-gold border-b border-gold pb-4 font-bold" aria-hidden="true">{{ __('login/login.form_title') }}</p>
                        <div class="flex flex-col gap-4">
                            <x-forms.input :name="'email'" :label="__('login/login.email')" :type="'email'" :required="true" :placeholder="'john.doe@example.com'" />
                            <x-forms.input :name="'password'" :label="__('login/login.password')" :type="'password'" :required="true" :placeholder="'**************'">
                                <div class="flex flex-row gap-2">
                                    <input type="checkbox" name="remember" id="remember" class="accent-gold">
                                    <label for="remember" class="text-white">
                                        {{ __('login/login.remember_me') }}
                                    </label>
                                </div>
                            </x-forms.input>
                        </div>

                        <x-forms.submit class="w-full lg:w-fit lg:self-start">{{ __('login/login.login') }}</x-forms.submit>
                    </fieldset>
                </form>

            </div>
            <p class="text-white text-center lg:text-left">
                    {{ __('login/login.not_registered') }} <x-cta href="{{ route('register') }}" :title="__('login/login.title_cta')" :class="'underline'">{{ __('login/login.register') }}</x-cta>
                </p>
        </div>

    </main>
</x-layouts.auth>
