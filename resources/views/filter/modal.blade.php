<div class="btn-group float-end" style="margin-right: 10px">
    <a href="" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $modalID }}"><i class="fa-solid fa-filter"></i>&nbsp;&nbsp;{{ trans('admin.filter') }}</a>
    <a href="{!! $action !!}" class="btn btn-sm btn-facebook"><i class="fa-solid fa-rotate-left"></i>&nbsp;&nbsp;{{ trans('admin.reset') }}</a>
</div>

<div class="modal fade" id="{{ $modalID }}" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="visually-hidden">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">{{ trans('admin.filter') }}</h4>
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
                    <button type="reset" class="btn btn-warning float-start">{{ trans('admin.reset') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>