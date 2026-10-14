<div class="card-header {{ $expand?'':'hide' }} filter-box" id="{{ $filterID }}">
    <form action="{!! $action !!}" class="form-horizontal" pjax-container method="get">

        <div class="row">
            @foreach($layout->columns() as $column)
            <div class="col-md-{{ $column->width() }}">
                <div class="card-body">
                    <div class="fields-group">
                        @foreach($column->filters() as $filter)
                            {!! $filter->render() !!}
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <!-- /.card-body -->

        <div class="card-footer">
            <div class="row">
                <div class="col-md-{{ $layout->columns()->first()->width() }}">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <div class="btn-group float-start">
                            <button class="btn btn-info submit btn-sm"><i
                                        class="fa-solid fa-magnifying-glass"></i>&nbsp;&nbsp;{{ trans('admin.search') }}</button>
                        </div>
                        <div class="btn-group float-start " style="margin-left: 10px;">
                            <a href="{!! $action !!}" class="btn btn-outline-secondary btn-sm"><i
                                        class="fa-solid fa-rotate-left"></i>&nbsp;&nbsp;{{ trans('admin.reset') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
