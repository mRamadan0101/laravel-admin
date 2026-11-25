<div class="card">

    <div class="card-header">

        <div class="btn-group">
            <a class="btn btn-primary btn-sm {{ $id }}-tree-tools" data-action="expand" title="{{ trans('admin.expand') }}">
                <i class="fa-regular fa-square-plus"></i>&nbsp;{{ trans('admin.expand') }}
            </a>
            <a class="btn btn-primary btn-sm {{ $id }}-tree-tools" data-action="collapse" title="{{ trans('admin.collapse') }}">
                <i class="fa-regular fa-square-minus"></i>&nbsp;{{ trans('admin.collapse') }}
            </a>
        </div>

        @if($useSave)
        <div class="btn-group">
            <a class="btn btn-info btn-sm {{ $id }}-save" title="{{ trans('admin.save') }}"><i class="fa-solid fa-floppy-disk"></i><span class="d-none d-sm-inline-block">&nbsp;{{ trans('admin.save') }}</span></a>
        </div>
        @endif

        @if($useRefresh)
        <div class="btn-group">
            <a class="btn btn-warning btn-sm {{ $id }}-refresh" title="{{ trans('admin.refresh') }}"><i class="fa-solid fa-rotate-right"></i><span class="d-none d-sm-inline-block">&nbsp;{{ trans('admin.refresh') }}</span></a>
        </div>
        @endif

        <div class="btn-group">
            {!! $tools !!}
        </div>

        @if($useCreate)
        <div class="btn-group float-end">
            <a class="btn btn-success btn-sm" href="{{ url($path) }}/create"><i class="fa-solid fa-floppy-disk"></i><span class="d-none d-sm-inline-block">&nbsp;{{ trans('admin.new') }}</span></a>
        </div>
        @endif

    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
        <div class="dd" id="{{ $id }}">
            <ol class="dd-list">
                @each($branchView, $items, 'branch')
            </ol>
        </div>
    </div>
    <!-- /.card-body -->
</div>
