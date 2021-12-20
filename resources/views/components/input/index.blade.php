{{--
@param text Field text name.
--}}

@props(['text'])

<label class="flex flex-col gap-y-1">
    {{ $text }}
    <input class="p-2 border border-cyan-500 transition-shadow focus:outline-none focus:ring-4 ring-cyan-500" {{ $attributes }}>
    @error($attributes->get('name'))
        <div class="text-red-500">{{ $message }}</div>
    @enderror
</label>
