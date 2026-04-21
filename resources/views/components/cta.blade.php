@props(['href' => '',
 'title' => '',
  'cta_title' => '',
   'class' => ''])

@php
    $classes_variants = [
            'primary' => 'cta-primary',
            'secondary' => 'cta-secondary',
            'nav' => 'nav-link',
            'underline' => 'cta-underline',
        ];

    $class_variant = $classes_variants[$class] ?? $classes_variants['primary']
@endphp

<a href="{{ $href }}" title=" {{$title}}" class="{{$class_variant}}">
    {{$slot}}
</a>