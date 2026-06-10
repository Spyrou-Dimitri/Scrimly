<x-layouts.auth :title="__('forgot-password/forgot-password.page_title')">
    <main id="main-content" aria-labelledby="forgot-password-heading" class="lg:grid lg:grid-cols-12 bg-bg-main flex-1 p-6 gap-6 lg:p-0 flex flex-col items-center justify-center">
        <div class="hidden lg:block lg:col-span-7" aria-hidden="true">
            <picture>
                <source media="(min-width: 1800px)" srcset="{{ asset('/img/forgotPassword/KindredFullSIze.jpg') }}">
                <source media="(min-width: 1536px)" srcset="{{ asset('/img/forgotPassword/Kindred1000x1000.jpg') }}">
                <img src="{{ asset('/img/forgotPassword/Kindred800x800.jpg') }}" alt="{{ __('forgot-password/forgot-password.hero_image_alt') }}" class="min-h-screen w-full object-cover">
            </picture>
        </div>
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <x-auth.back-to-home />
                <h2 id="forgot-password-heading" class="text-[40px] text-white font-bold">{!! __('forgot-password/forgot-password.welcome') !!}</h2>
                <p class="text-white text-xl">{{ __('forgot-password/forgot-password.welcome_description') }}</p>
            </div>
            <div class="bg-bg-widget p-6 shadow-basic flex flex-col gap-8 lg:pr-6">
                <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6 align-start">
                    @csrf
                    <fieldset class="flex flex-col gap-6">
                        <legend class="sr-only">{{ __('forgot-password/forgot-password.form_title') }}</legend>
                        <p class="text-2xl text-gold border-b border-gold pb-4 font-bold" aria-hidden="true">{{ __('forgot-password/forgot-password.form_title') }}</p>

                        @if (session('status'))
                            <p role="status" class="text-sm font-medium text-gold">
                                {{ session('status') }}
                            </p>
                        @endif

                        <div class="flex flex-col gap-4">
                            <x-forms.input :name="'email'" :label="__('forgot-password/forgot-password.email')" :type="'email'" :required="true" :placeholder="'john.doe@example.com'" />
                        </div>

                        <x-forms.submit class="w-full lg:w-fit lg:self-start">{{ __('forgot-password/forgot-password.submit') }}</x-forms.submit>
                    </fieldset>
                </form>

            </div>
            <p class="text-white text-center lg:text-left">
                {{ __('forgot-password/forgot-password.remember_password') }} <x-cta href="{{ route('login') }}" :title="__('forgot-password/forgot-password.title_cta')" :class="'underline'">{{ __('forgot-password/forgot-password.login') }}</x-cta>
            </p>
        </div>

    </main>
</x-layouts.auth>
