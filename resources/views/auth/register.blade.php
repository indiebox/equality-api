@extends('layouts.html')

@section('content')
    <div class="flex items-center justify-center h-[100vh]">
        <form class="shadow-lg px-14 py-7" method="POST" actions="{{ route('register.store') }}">
            @csrf

            <h1 class="text-3xl text-center mb-4">Регистрация</h1>
            <div class="flex flex-col justify-center">
                <div class="space-y-5">
                    <x-input text="Имя:" type="text" name="name"  minlength="2" maxlength="50" />
                    <x-input text="Email:" type="email" name="email"  maxlength="128" />
                    <x-input text="Пароль:" type="password" name="password"  minlength="6" />
                    <x-input text="Подтвердите пароль:" type="password" name="password_confirmation"  minlength="6"/>
                </div>

                <button class="button button-primary mt-10">Зарегистрироваться</button>
            </div>
        </form>
    </div>
@endsection
