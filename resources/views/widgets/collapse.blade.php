<div {!! $attributes !!}>
    @foreach($items as $key => $item)
    <div class="card card card-primary card-outline" style="margin-bottom: 0px">
        <div class="card-header">
            <h4 class="card-title">
                <a data-bs-toggle="collapse" data-bs-parent="#{{$id}}" href="#collapse{{ $key }}">
                    {{ $item['title'] }}
                </a>
            </h4>
        </div>
        <div id="collapse{{ $key }}" class="panel-collapse collapse {{ $key == 0 ? 'in' : '' }}">
            <div class="card-body">
                {!! $item['content'] !!}
            </div>
        </div>
    </div>
    @endforeach

</div>
