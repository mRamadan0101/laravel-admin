<div class="btn-group float-end me-2">
    <a href="" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $modalID }}"><i class="fa-solid fa-filter"></i>&nbsp;&nbsp;{{ trans('admin.filter') }}</a>
    <a href="{!! $action !!}" class="btn btn-sm btn-facebook"><i class="fa-solid fa-rotate-left"></i>&nbsp;&nbsp;{{ trans('admin.reset') }}</a>
</div>

<div class="modal fade" id="{{ $modalID }}" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fs-5" id="myModalLabel">{{ trans('admin.filter') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{!! $action !!}" method="get" pjax-container>
                <div class="modal-body">
                    <div class="form">
                        @foreach($filters as $filter)
                            <div class="form-group">
                                {!! $filter->render() !!}
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary submit">{{ trans('admin.submit') }}</button>
                    <button type="reset" class="btn btn-warning me-auto">{{ trans('admin.reset') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>