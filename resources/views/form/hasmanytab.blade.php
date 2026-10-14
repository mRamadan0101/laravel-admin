<style>
    .nav-tabs > li:hover > i{
        display: inline;
    }
    .close-tab {
        position: absolute;
        font-size: 10px;
        top: 2px;
        right: 5px;
        color: #94A6B0;
        cursor: pointer;
        display: none;
    }
</style>
<div id="has-many-{{$column}}" class="nav-tabs-custom has-many-{{$column}}">
    <div class="row header">
        <div class="col-md-2 {{$viewClass['label']}}"><h4 class="float-end">{{ $label }}</h4></div>
        <div class="col-md-8 {{$viewClass['field']}}">
            <button type="button" class="btn btn-outline-secondary btn-sm add"><i class="fa-solid fa-circle-plus" style="font-size: large;"></i></button>
        </div>
    </div>

    <hr style="margin-top: 0px;">

    <ul class="nav nav-tabs">
        @foreach($forms as $pk => $form)
            <li class="@if ($form == reset($forms)) active @endif ">
                <a href="#{{ $relationName . '_' . $pk }}" data-bs-toggle="tab">
                    {{ $pk }} <i class="fa-solid fa-circle-exclamation text-danger hide"></i>
                </a>
                <i class="close-tab fa-solid fa-xmark" ></i>
            </li>
        @endforeach

    </ul>
    
    <div class="tab-content has-many-{{$column}}-forms">

        @foreach($forms as $pk => $form)
            <div class="tab-pane fields-group has-many-{{$column}}-form @if ($form == reset($forms)) active @endif" id="{{ $relationName . '_' . $pk }}">
                @foreach($form->fields() as $field)
                    {!! $field->render() !!}
                @endforeach
            </div>
        @endforeach
    </div>

    <template class="nav-tab-tpl">
        <li class="new">
            <a href="#{{ $relationName . '_new_' . \Encore\Admin\Form\NestedForm::DEFAULT_KEY_NAME }}" data-bs-toggle="tab">
                &nbsp;New {{ \Encore\Admin\Form\NestedForm::DEFAULT_KEY_NAME }} <i class="fa-solid fa-circle-exclamation text-danger hide"></i>
            </a>
            <i class="close-tab fa-solid fa-xmark" ></i>
        </li>
    </template>
    <template class="pane-tpl">
        <div class="tab-pane fields-group new" id="{{ $relationName . '_new_' . \Encore\Admin\Form\NestedForm::DEFAULT_KEY_NAME }}">
            {!! $template !!}
        </div>
    </template>

</div>
