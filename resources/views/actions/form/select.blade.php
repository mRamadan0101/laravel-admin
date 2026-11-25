<div class="form-group">
    <label>{{ $label }}</label>

    <select class="form-select {{$class}}" style="width: 100%;" name="{{$name}}" {!! $attributes !!} >

        <option value=""></option>
        @foreach($options as $select => $option)
            <option value="{{$select}}" {{ $select == old($column, $value) ?'selected':'' }}>{{$option}}</option>
        @endforeach
    </select>
    @include('admin::actions.form.help-block')
</div>

