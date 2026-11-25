<div class="btn-group btn-group-sm grid-trash-switcher" role="group">
    <a href="{{ $list }}" class="btn btn-outline-secondary {{ $inTrash ? '' : 'active' }}" @if(!$inTrash) aria-current="page" @endif>
        <i class="fa-solid fa-list"></i><span class="d-none d-sm-inline ms-1">{{ trans('admin.all') }}</span>
    </a>
    <a href="{{ $trash }}" class="btn btn-outline-secondary {{ $inTrash ? 'active' : '' }}" @if($inTrash) aria-current="page" @endif>
        <i class="fa-solid fa-trash-can"></i><span class="d-none d-sm-inline ms-1">{{ trans('admin.trash') }}</span>
        @if($count)
            <span class="badge rounded-pill text-bg-danger ms-1">{{ $count }}</span>
        @endif
    </a>
</div>
