<div class="{{$viewClass['form-group']}} {!! !$errors->has($column) ?: 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} col-form-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}" id="{{$id}}">

        @if($canCheckAll)
            <label class="form-check form-check-inline icheck mt-2">
                <input type="checkbox" class="form-check-input {{ $checkAllClass }}"/>
                <span class="form-check-label">{{ __('admin.all') }}</span>
            </label>
            <hr class="mt-2 mb-0">
        @endif

        @include('admin::form.error')

        @if($groups)

        @foreach($groups as $group => $options)

            <p class="d-inline-block border-bottom pb-1 {{ $canCheckAll ? 'mt-3' : 'mt-2' }} mb-0">{{ $group }}</p>

            @foreach($options as $option => $label)

            <label class="form-check icheck">
                <input type="checkbox" name="{{$name}}[]" value="{{$option}}" class="form-check-input {{$class}}" {{ false !== array_search($option, array_filter(old($column, $value ?? []))) || ($value === null && in_array($option, $checked)) ?'checked':'' }} {!! $attributes !!} />
                <span class="form-check-label">{{$label}}</span>
            </label>

            @endforeach

        @endforeach

        @else

        @foreach($options as $option => $label)

            <label class="form-check icheck {{ $inline ? 'form-check-inline mt-2' : '' }}">
                <input type="checkbox" name="{{$name}}[]" value="{{$option}}" class="form-check-input {{$class}}" {{ false !== array_search($option, array_filter(old($column, $value ?? []))) || ($value === null && in_array($option, $checked)) ?'checked':'' }} {!! $attributes !!} />
                <span class="form-check-label">{{$label}}</span>
            </label>

        @endforeach

        @endif

        <input type="hidden" name="{{$name}}[]">

        @include('admin::form.help-block')

    </div>
</div>
