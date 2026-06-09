@php
use App\Enums\DefaultAvatar;
@endphp

<x-layouts.auth :title="__('register/register.page_title')">
    <main id="main-content" aria-labelledby="register-heading" class="flex flex-1 flex-col items-center justify-center gap-6 bg-bg-main p-6">
    <x-auth.back-to-home />

        <section class="mx-auto flex w-full max-w-4xl flex-col gap-6">
            <div class="bg-bg-widget shadow-basic flex flex-col p-6 sm:p-10">
                <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-start lg:gap-x-0 lg:gap-y-0">
                    @csrf
                    <div class="flex flex-col gap-2 lg:col-start-1 lg:row-start-1 lg:pr-8">
                        <h2 id="register-heading" class="text-center text-[32px] font-bold text-white lg:text-left">{!! __('register/register.welcome') !!}</h2>
                        <p class="text-center text-text-secondary lg:text-left">
                            {{ __('register/register.welcome_description') }}
                        </p>
                    </div>
                    <fieldset class="avatar-fieldset m-0 flex min-w-0 flex-col gap-4 border-0 p-0 lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:pl-8 lg:border-l lg:border-gold">
                        <legend class="sr-only">{{ __('register/register.avatar_section_title') }}</legend>
                        <div class="flex flex-col gap-2">
                            <p class="w-full text-center text-xl font-bold text-gold lg:text-[32px]">{{ __('register/register.avatar_section_title') }}</p>
                            <p class="text text-center text-text-secondary">{{ __('register/register.avatar_section_description') }}</p>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="relative mx-auto flex w-full max-w-44 flex-col gap-3">
                                <x-destructive type="button" id="deleteAvatar" class="absolute -top-2 -right-2 z-[1] hidden" :only-icon="true" :title="__('register/register.delete_avatar')">
                                    <flux:icon name="trash" class="size-5 shrink-0 opacity-70" />
                                </x-destructive>
                                <div class="relative aspect-square w-full overflow-hidden rounded-lg bg-input-bg ring-2 ring-input-border">
                                    <div id="avatar-preview-placeholder" class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-4 text-center text-text-secondary">
                                        <flux:icon name="user-circle" class="size-12 shrink-0 opacity-70" />
                                        <p class="text-xs leading-snug font-medium">{{ __('register/register.preview_placeholder') }}</p>
                                    </div>
                                    <img
                                        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                        alt="{{ __('register/register.avatar_section_image') }}"
                                        id="imagePreview"
                                        class="absolute inset-0 hidden size-full object-cover"
                                        width="320"
                                        height="320">
                                </div>
                            </div>
                            <div class="mx-auto w-fit">
                                <label
                                    for="avatarUpload"
                                    class="cta-secondary focus-within:ring-2 focus-within:ring-gold-light flex gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    {{ __('register/register.upload_photo') }}
                                    <input type="file" name="avatar" accept="image/*" id="avatarUpload" class="absolute inset-0 opacity-0 pointer-events-none">

                                </label>
                                @error('avatar')
                                <span class="font-spaceGrotesk font-semibold text-input-error">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <p class="text-center text-sm font-medium text-white">{{ __('register/register.choose_avatar') }}</p>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach (DefaultAvatar::cases() as $avatar)
                                <div class="relative">
                                    <input
                                        type="radio"
                                        name="default_avatar"
                                        id="default-avatar-{{ $avatar->value }}"
                                        value="{{ $avatar->value }}"
                                        class="peer absolute inset-0 opacity-0 pointer-events-none"
                                        @checked(old('default_avatar', DefaultAvatar::cases()[0]->value) === $avatar->value)
                                    >
                                    <label
                                        for="default-avatar-{{ $avatar->value }}"
                                        class="block cursor-pointer overflow-hidden rounded-lg ring-2 ring-transparent transition-all 
               peer-focus:ring-gold-light peer-checked:ring-gold">
                                        <img
                                            src="{{ $avatar->url() }}"
                                            alt="{{ $avatar->label() }}"
                                            class="aspect-square w-full object-cover">
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="m-0 flex min-w-0 flex-col gap-4 border-0 p-0 lg:col-start-1 lg:mt-4 lg:row-start-2 lg:pr-8">
                        <legend class="sr-only">{{ __('register/register.form_title') }}</legend>
                        <div class="flex flex-col gap-4">
                            <x-forms.input :name="'username'" :label="__('register/register.username')" :type="'text'" :required="true" :placeholder="'Faker'"/>
                            <x-forms.input :name="'riot_tag'" :label="__('register/register.riot_id')" :type="'text'" :placeholder="'HideOnBush#KR'"/>
                            <x-forms.input :name="'email'" :label="__('register/register.email')" :type="'email'" :required="true" :placeholder="'email@example.com'" />
                            <div class="flex flex-col gap-2">
                                <label class="block font-medium text-white" for="password">
                                    {{ __('register/register.password') }}<span class="font-semibold text-gold">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        data-password="input"
                                        type="password"
                                        name="password"
                                        id="password"
                                        required
                                        class="w-full border border-input-border bg-input-bg py-2 pl-4 pr-12 text-white outline-none transition-all duration-200 focus:ring-2 focus:ring-gold"
                                        placeholder="{{ __('register/register.password_placeholder') }}">
                                    <button
                                        type="button"
                                        data-password-toggle="button"
                                        aria-label="{{ __('register/register.toggle_password') }}"
                                        aria-pressed="false"
                                        class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3 text-gray-400 transition-colors hover:text-gold">
                                        <svg data-icon="show" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <svg data-icon="hide" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden" aria-hidden="true">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                <span class="font-spaceGrotesk font-semibold text-input-error">
                                    {{ $message }}
                                </span>
                                @enderror
                                <ul class="flex flex-col gap-2 text-sm ">
                                    <li data-password-rule="length" class="flex items-center gap-2 text-input-error">
                                        <flux:icon data-password-rule="length-icon-error" name="x-circle" class="size-4 shrink-0 opacity-70" />
                                        <flux:icon data-password-rule="length-icon-success" name="check-circle" class="hidden size-4 shrink-0 opacity-70" />
                                        <span>
                                            {{ __('register/register.password_rules.length') }}
                                        </span>
                                    </li>
                                    <li data-password-rule="uppercase" class="flex items-center gap-2 text-input-error">
                                        <flux:icon data-password-rule="uppercase-icon-error" name="x-circle" class="size-4 shrink-0 opacity-70" />
                                        <flux:icon data-password-rule="uppercase-icon-success" name="check-circle" class="hidden size-4 shrink-0 opacity-70" />
                                        <span>
                                            {{ __('register/register.password_rules.uppercase') }}
                                        </span>
                                    </li>
                                    <li data-password-rule="lowercase" class="flex items-center gap-2 text-input-error">
                                        <flux:icon data-password-rule="lowercase-icon-error" name="x-circle" class="size-4 shrink-0 opacity-70" />
                                        <flux:icon data-password-rule="lowercase-icon-success" name="check-circle" class="hidden size-4 shrink-0 opacity-70" />
                                        <span>
                                            {{ __('register/register.password_rules.lowercase') }}
                                        </span>
                                    </li>
                                    <li data-password-rule="number" class="flex items-center gap-2 text-input-error">
                                        <flux:icon data-password-rule="number-icon-error" name="x-circle" class="size-4 shrink-0 opacity-70" />
                                        <flux:icon data-password-rule="number-icon-success" name="check-circle" class="hidden size-4 shrink-0 opacity-70" />
                                        <span>
                                            {{ __('register/register.password_rules.contains_number') }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <x-forms.submit class="w-full lg:w-fit lg:self-start">{{ __('register/register.register') }}</x-forms.submit>
                        <p class="text-center text-white lg:text-left">
                            {{ __('register/register.already_registered') }} <x-cta href="{{ route('login') }}" :title="__('register/register.title_cta')" :class="'underline'">{{ __('register/register.login') }}</x-cta>
                        </p>
                    </fieldset>
                </form>
            </div>
        </section>
    </main>
</x-layouts.auth>