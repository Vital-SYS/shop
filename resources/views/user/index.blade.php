@extends('layout.site')

@section('content')
    <h1>Личный кабинет</h1>
    <p>Добро пожаловать, {{ auth()->user()->name }}</p>
    <p>Это личный кабинет постоянного покупателя нашего интернет-магазина.</p>
    <form action="{{ route('user.logout') }}" method="post">
        @csrf
        <button type="submit" class="btn btn-primary">Выйти</button>
    </form>
@endsection
