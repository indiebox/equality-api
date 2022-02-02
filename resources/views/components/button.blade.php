{{--
@slot default Button text.
--}}

<button {{ $attributes->merge(['class' => 'mdc-button mdc-button--raised mdc-button--touch']) }}>
    <span class="mdc-button__ripple"></span>
    <span class="mdc-button__touch"></span>
    <span class="mdc-button__label">{{ $slot }}</span>
</button>
