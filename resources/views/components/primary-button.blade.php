<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-auth']) }}>
    {{ $slot }}
</button>
