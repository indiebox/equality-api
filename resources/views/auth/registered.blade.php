@extends('layouts.html')

@section('content')
    <div class="flex flex-col justify-center items-center h-[100vh]">
        <p>Регистрация прошла успешна.</p>
        <p>На указанную вами почту было выслано письмо с ссылкой для подтверждения аккаунта.</p>
        <p>Подтвердите ваш аккаунт, чтобы начать использовать {{ config('app.name') }}.</p>
    </div>
@endsection
