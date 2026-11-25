<div class="btn-group me-1">
    <button type="button" class="btn btn-sm btn-dropbox {{ $btn_class }} {{ $expand ? 'active' : '' }}" title="{{ trans('admin.filter') }}" aria-pressed="{{ $expand ? 'true' : 'false' }}">
        <i class="fa-solid fa-filter"></i><span class="d-none d-sm-inline-block">&nbsp;&nbsp;{{ trans('admin.filter') }}</span>
    </button>

    @if($scopes->isNotEmpty())
    <button type="button" class="btn btn-sm btn-dropbox dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <span>{{ $label }}</span>
        <span class="visually-hidden">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu">
        @foreach($scopes as $scope)
            {!! $scope->render() !!}
        @endforeach
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ $cancel }}">{{ trans('admin.cancel') }}</a></li>
    </ul>
    @endif
</div>

<script>
var $btn = $('.{{ $btn_class }}');
var $filter = $('#{{ $filter_id }}');

$btn.off('click').on('click', function (e) {
    var visible = $filter.is(':visible');
    $filter.toggleClass('hide', visible);
    $(this).toggleClass('active', !visible).attr('aria-pressed', !visible);
});
</script>
