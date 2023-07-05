@extends('layout.site', ['title' => $page->name])

@section('content')
    <div class="card">
        <div class="card-header">
            <h1>{{ $page->name }}</h1>
        </div>
        <div class="card-body">
            {!! $page->content  !!}
        </div>
        <div class="card-footer">
            @if($page->created_at)
                Добавлена: {{ $page->created_at->format('d.m.Y H:i') }}
            @endif
        </div>
    </div>
@endsection
