@extends('layout.site')

@section('content')
    <h1>{{ $brand->name }}</h1>
    <p>{{ $brand->content }}</p>
    <div class="row">
        @foreach ($brand->products as $product)
            @include('part.product')
        @endforeach
    </div>
@endsection
