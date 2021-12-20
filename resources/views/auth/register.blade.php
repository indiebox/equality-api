@extends('layouts.html')

@push('scripts')
    <script src="https://www.recaptcha.net/recaptcha/api.js" async defer></script>
@endpush

@section('content')
    <div class="flex items-center justify-center h-[100vh]">
        <form class="shadow-lg px-14 py-7" method="POST" actions="{{ route('register.store') }}">
            @csrf

            <h1 class="text-3xl text-center mb-4">Регистрация</h1>
            <div class="flex flex-col justify-center">
                <div class="space-y-5">
                    <x-input text="Имя:" type="text" name="name" :value="old('name')" required minlength="2" maxlength="50" />
                    <x-input text="Email:" type="email" name="email" :value="old('email')" required maxlength="128" />
                    <x-input text="Пароль:" type="password" name="password" required minlength="6" />
                    <x-input text="Подтвердите пароль:" type="password" name="password_confirmation" required minlength="6"/>
                    <div>
                        <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.public_key') }}"></div>
                        @error(config('recaptcha.field_name'))
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button class="button button-primary mt-5">Зарегистрироваться</button>
            </div>
        </form>
    </div>
@endsection
