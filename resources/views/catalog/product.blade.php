@extends('layout.site')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1>{{ $product->name }}</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="https://via.placeholder.com/400x400"
                                 alt="" class="img-fluid">
                        </div>
                        <div class="col-md-6">
                            <p>Цена: {{ number_format($product->price, 2, '.', '') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p class="mt-4 mb-0">{{ $product->content }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            @isset($product->category)
                                Категория:
                                <a href="{{ route('catalog.category', ['slug' => $product->category->slug]) }}">
                                    {{ $product->category->name }}
                                </a>
                            @endisset
                        </div>
                        <div class="col-md-6 text-right">
                            @isset($product->brand)
                                Бренд:
                                <a href="{{ route('catalog.brand', ['slug' => $product->brand->slug]) }}">
                                    {{ $product->brand->name }}
                                </a>
                            @endisset
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p>Цена: {{ number_format($product->price, 2, '.', '') }}</p>
                        <!-- Форма для добавления товара в корзину -->
                        <form action="{{ route('basket.add', ['id' => $product->id]) }}"
                              method="post" class="form-inline">
                            @csrf
                            <label for="input-quantity">Количество</label>
                            <input type="text" name="quantity" id="input-quantity" value="1"
                                   class="form-control mx-2 w-25">
                            <button type="submit" class="btn btn-success">Добавить в корзину</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
