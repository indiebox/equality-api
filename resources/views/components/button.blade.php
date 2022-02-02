{{--
@slot default Button text.
--}}

<button {{ $attributes->merge(['class' => 'mdc-button mdc-button--raised']) }}>
    <span class="mdc-button__label">{{ $slot }}</span>
</button>
