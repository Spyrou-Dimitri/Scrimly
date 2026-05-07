<button {{ $attributes->merge(['type' => 'submit', 'class' => 'cta-primary']) }}>
    {{ $slot }}
</button>