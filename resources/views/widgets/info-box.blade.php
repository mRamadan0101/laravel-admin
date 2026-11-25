<div {!! $attributes !!}>
    <div class="inner">
        <h3>{{ $info }}</h3>

        <p>{{ $name }}</p>
    </div>
    <i class="small-box-icon fa fa-{{ $icon }}"></i>
    <a href="{{ $link }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        {{ trans('admin.more') }}&nbsp;
        <i class="fa-solid fa-circle-arrow-right"></i>
    </a>
</div>
