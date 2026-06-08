@props(['fields'])

@if ($errors->hasAny($fields))
    <div class="flex flex-col gap-1" role="alert">
        @foreach ($fields as $field)
            @error($field)
                <span class="font-spaceGrotesk text-input-error font-semibold">
                    {{ $message }}
                </span>
            @enderror
        @endforeach
    </div>
@endif
