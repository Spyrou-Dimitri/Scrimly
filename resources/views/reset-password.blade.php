<x-layouts.auth :title="__('reset-password/reset-password.page_title')">
    <main id="main-content" aria-labelledby="reset-password-heading" class="lg:grid lg:grid-cols-12 bg-bg-main flex-1 p-6 gap-6 lg:p-0 flex flex-col items-center justify-center">
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <x-auth.back-to-home />
                <h2 id="reset-password-heading" class="text-[40px] text-white font-bold">{!! __('reset-password/reset-password.welcome') !!}</h2>
                <p class="text-white text-xl">{{ __('reset-password/reset-password.welcome_description') }}</p>
            </div>
            <div class="bg-bg-widget p-6 shadow-basic flex flex-col gap-8 lg:pr-6">
                <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6 align-start">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                    <fieldset class="flex flex-col gap-6">
                        <legend class="sr-only">{{ __('reset-password/reset-password.form_title') }}</legend>
                        <p class="text-2xl text-gold border-b border-gold pb-4 font-bold" aria-hidden="true">{{ __('reset-password/reset-password.form_title') }}</p>

                        <div class="flex flex-col gap-4">
                            <x-forms.input :name="'email'" :label="__('reset-password/reset-password.email')" :type="'email'" :required="true" :placeholder="'john.doe@example.com'" :value="old('email', $request->email)" />
                            <x-forms.input :name="'password'" :label="__('reset-password/reset-password.password')" :type="'password'" :required="true" :placeholder="'**************'" />
                            <x-forms.input :name="'password_confirmation'" :label="__('reset-password/reset-password.password_confirmation')" :type="'password'" :required="true" :placeholder="'**************'" />
                        </div>

                        <x-forms.submit class="w-full lg:w-fit lg:self-start">{{ __('reset-password/reset-password.submit') }}</x-forms.submit>
                    </fieldset>
                </form>

            </div>
            <p class="text-white text-center lg:text-left">
                {{ __('reset-password/reset-password.remember_password') }} <x-cta href="{{ route('login') }}" :title="__('reset-password/reset-password.title_cta')" :class="'underline'">{{ __('reset-password/reset-password.login') }}</x-cta>
            </p>
        </div>
        <div class="hidden lg:block lg:col-span-7" aria-hidden="true">
            <picture>
                <source media="(min-width: 1800px)" srcset="{{ asset('/img/resetPassword/AurelionSol.jpg') }}">
                <source media="(min-width: 1536px)" srcset="{{ asset('/img/resetPassword/AurelionSol1000x1000.jpg') }}">
                <img src="{{ asset('/img/resetPassword/AurelionSol800x800.jpg') }}" alt="{{ __('reset-password/reset-password.hero_image_alt') }}" class="min-h-screen w-full object-cover">
            </picture>
        </div>

    </main>
</x-layouts.auth>
