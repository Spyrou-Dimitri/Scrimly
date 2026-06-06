<a
    href="{{ route('home') }}"
    title="{{ __('layouts/auth.back_to_home_title') }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 text-text-secondary transition-colors duration-150 hover:text-gold group']) }}>
    <flux:icon name="arrow-left" class="size-4 transition-colors duration-150 group-hover:text-gold" />
    <span>{{ __('layouts/auth.back_to_home') }}</span>
</a>
