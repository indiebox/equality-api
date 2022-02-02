@extends('layouts.html')

@push('scripts')
    <script src="https://www.recaptcha.net/recaptcha/api.js" async defer></script>
@endpush

@section('content')
    <div class="modal-window flex justify-center w-full py-14">
        <form class="h-full" method="POST" actions="{{ route('register.store') }}">
            @csrf

            <h1 class="text-3xl text-center mb-14">Регистрация</h1>
            <div class="flex flex-col justify-center sm:w-[360px]">
                <div class="flex flex-col space-y-7">
                    <x-input name="name" text="Имя" type="text" :value="old('name')" placeholder="Введите адрес эл. почты" required minlength="2" maxlength="50" />
                    <x-input name="email" text="Адрес эл. почты" type="email" :value="old('email')" placeholder="Введите имя" required maxlength="128" />
                    <x-input name="password" text="Новый пароль" type="password" placeholder="Введите новый пароль" required minlength="6" />
                    <x-input name="password_confirmation" text="Повторите пароль" type="password" placeholder="Введите пароль ещё раз" required minlength="6"/>
                    <div>
                        <div class="flex justify-center g-recaptcha" data-sitekey="{{ config('recaptcha.public_key') }}"></div>
                        @error(config('recaptcha.field_name'))
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-button class="h-12">Зарегистрироваться</x-button>
                </div>
            </div>
        </form>
    </div>
@endsection
