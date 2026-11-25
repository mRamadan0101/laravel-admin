<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} col-form-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        <div class="card m-0">
            <!-- /.card-header -->
            <div class="card-body">
                {!! $value !!}&nbsp;
            </div><!-- /.card-body -->
        </div>

        @include('admin::form.help-block')

    </div>
</div>