<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} col-form-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        @foreach($options as $option => $label)

            <label class="form-check icheck {{ $inline ? 'form-check-inline mt-2' : '' }}">
                <input type="radio" name="{{$name}}" value="{{$option}}" class="form-check-input minimal {{$class}}" {{ ($option == old($column, $value)) || ($value === null && in_array($label, $checked)) ?'checked':'' }} {!! $attributes !!} />
                <span class="form-check-label">{{$label}}</span>
            </label>

        @endforeach

        @include('admin::form.help-block')

    </div>
</div>
