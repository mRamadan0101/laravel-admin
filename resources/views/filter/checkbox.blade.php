<div class="pt-1">
    @foreach($options as $option => $label)

        <label class="form-check icheck {{ $inline ? 'form-check-inline' : '' }}">
            <input type="checkbox" class="form-check-input {{$id}}" name="{{$name}}[]" value="{{$option}}" {{ in_array((string)$option, request($name, is_null($value) ? [] : $value)) ? 'checked' : '' }} />
            <span class="form-check-label">{{$label}}</span>
        </label>

    @endforeach
</div>
