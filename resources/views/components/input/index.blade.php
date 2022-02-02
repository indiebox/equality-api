{{--
@param text Field text name.
--}}

@props(['text'])

<label class="flex flex-col gap-y-1">
    {{ $text }}
    <label class="mdc-text-field mdc-text-field--filled mdc-text-field--no-label @error($attributes->get('name')) mdc-text-field--invalid @enderror">
        <span class="mdc-text-field__ripple"></span>
        <input class="mdc-text-field__input" aria-label="Label" {{ $attributes }}>
        <span class="mdc-line-ripple"></span>
    </label>
    @error($attributes->get('name'))
        <div class="mdc-text-field-helper-text
         mdc-text-field-helper-text--persistent
         mdc-text-field-helper-text--validation-msg
         text-red-500">{{ $message }}</div>
    @enderror
</label>
