<ul>
    @foreach($items as $item)
        <li>
            <a href="{{ route('catalog.category', ['slug' => $item->slug]) }}">{{ $item->name }}</a>
            @if ($item->descendants->count())
                <span class="badge badge-dark">
                <i class="fa fa-plus"></i>
            </span>
                @include('layout.part.branch', ['items' => $item->descendants])
            @endif
        </li>
    @endforeach
</ul>
