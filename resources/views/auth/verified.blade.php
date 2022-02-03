@extends('layouts.html')

@section('content')
    <div class="flex flex-col justify-center items-center h-[100vh]">
        @if($verified)
            <p>Ваша почта успешно подтверждена.</p>
            <p>Теперь вы можете начать использовать {{ config('app.name') }}.</p>
        @else
            <p>Ваша почта уже подтверждена.</p>
        @endif
    </div>
@endsection
