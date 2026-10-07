@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label d-block']) }}>
    {{ $value ?? $slot }}
</label>
