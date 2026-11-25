<div class="{{$viewClass['form-group']}} {!! ($errors->has($errorKey['start'].'start') || $errors->has($errorKey['end'].'end')) ? 'has-error' : '' !!}">

    <label for="{{$id['start']}}" class="{{$viewClass['label']}} col-form-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div class="d-flex flex-wrap gap-2">
            <div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                    <input type="text" name="{{$name['start']}}" value="{{ old($column['start'], $value['start'] ?? null) }}" class="form-control {{$class['start']}}" autocomplete="off" style="width: 150px" {!! $attributes !!} />
                </div>
            </div>

            <div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                    <input type="text" name="{{$name['end']}}" value="{{ old($column['end'], $value['end'] ?? null) }}" class="form-control {{$class['end']}}" autocomplete="off" style="width: 150px" {!! $attributes !!} />
                </div>
            </div>
        </div>

        @include('admin::form.help-block')

    </div>
</div>
