<div {!! $attributes !!}>
    <ul class="nav nav-tabs">

        @foreach($tabs as $id => $tab)
            @if($tab['type'] == \Encore\Admin\Widgets\Tab::TYPE_CONTENT)
                <li class="nav-item"><a class="nav-link {{ $id == $active ? 'active' : '' }}" href="#tab_{{ $tab['id'] }}" data-bs-toggle="tab">{{ $tab['title'] }}</a></li>
            @elseif($tab['type'] == \Encore\Admin\Widgets\Tab::TYPE_LINK)
                <li class="nav-item"><a class="nav-link {{ $id == $active ? 'active' : '' }}" href="{{ $tab['href'] }}">{{ $tab['title'] }}</a></li>
            @endif
        @endforeach

        @if (!empty($dropDown))
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">
                Dropdown
            </a>
            <ul class="dropdown-menu">
                @foreach($dropDown as $link)
                <li><a class="dropdown-item" tabindex="-1" href="{{ $link['href'] }}">{{ $link['name'] }}</a></li>
                @endforeach
            </ul>
        </li>
        @endif
        <li class="nav-item ms-auto header">{{ $title }}</li>
    </ul>
    <div class="tab-content">
        @foreach($tabs as $id => $tab)
        <div class="tab-pane {{ $id == $active ? 'active show' : '' }}" id="tab_{{ $tab['id'] }}">
            @php($content = \Illuminate\Support\Arr::get($tab, 'content'))
                @if($content instanceof \Illuminate\Contracts\Support\Renderable)
                    {!! $content->render() !!}
                @else
                    {!! $content !!}
                @endif
        </div>
        @endforeach

    </div>
</div>
