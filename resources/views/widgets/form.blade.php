<form {!! $attributes !!}>
    <div class="card-body fields-group">

        @foreach($fields as $field)
            {!! $field->render() !!}
        @endforeach

    </div>

    @if ($method != 'GET')
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @endif
    
    <!-- /.card-body -->
    @if(count($buttons) > 0)
    <div class="card-footer">
        <div class="col-md-{{$width['label']}}"></div>

        <div class="col-md-{{$width['field']}}">
            @if(in_array('reset', $buttons))
            <div class="btn-group float-start">
                <button type="reset" class="btn btn-warning float-end">{{ trans('admin.reset') }}</button>
            </div>
            @endif

            @if(in_array('submit', $buttons))
            <div class="btn-group float-end">
                <button type="submit" class="btn btn-info float-end">{{ trans('admin.submit') }}</button>
            </div>
            @endif
        </div>
    </div>
    @endif
</form>
