{{--
@param name Input name.
@param text Field text name.
@param helperText Helper text for input. It is displayed only if there are no errors.
@param errorsNumber How many errors can be displayed.
If '0' is supplied, no errors would be displayed.
If '-1' is supplied, all errors would be displayed.
Another number is the maximum number of errors that will be shown.
--}}

@props(['name', 'text', 'helperText', 'errorsNumber' => -1])

@php
    $hasErrors = $errorsNumber != 0
        && $errors->default->has($name);
@endphp

<label class="flex flex-col gap-y-1">
    {{ $text }}
    <label class="mdc-text-field mdc-text-field--filled mdc-text-field--no-label @if($hasErrors) mdc-text-field--invalid @endif">
        <span class="mdc-text-field__ripple"></span>
        <input name="{{ $name }}" class="mdc-text-field__input" aria-label="Label" {{ $attributes }}>
        <span class="mdc-line-ripple"></span>
    </label>

    @if(isset($helperText) && !$hasErrors)
        <div class="mdc-text-field-helper-line">
            <div class="mdc-text-field-helper-text opacity-100">{{ $helperText }}</div>
        </div>
    @endif

    @if($hasErrors)
        @foreach ($errors->default->get($name) as $error)
            @if($loop->iteration <= $errorsNumber || $errorsNumber == -1)
                <div class="mdc-text-field-helper-text
                 mdc-text-field-helper-text--persistent
                 mdc-text-field-helper-text--validation-msg
                text-red-500">{{ $error }}</div>
            @endif
        @endforeach
    @endif
</label>
