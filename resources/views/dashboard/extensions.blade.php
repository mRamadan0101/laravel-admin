<div class="card">
    <div class="card-header">
        <h3 class="card-title">Available extensions</h3>

        <div class="card-tools float-end">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="fa-solid fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">

            @foreach($extensions as $extension)
            <li class="list-group-item d-flex align-items-center">
                <i class="fa fa-{{$extension['icon']}} fa-2x fa-fw text-body-secondary me-3"></i>
                <a href="{{ $extension['link'] }}" target="_blank" class="fw-semibold text-decoration-none">
                    {{ $extension['name'] }}
                </a>
                @if($extension['installed'])
                    <span class="ms-auto text-success"><i class="fa-solid fa-check"></i></span>
                @endif
            </li>
            @endforeach

        </ul>
    </div>
    <!-- /.card-body -->
    <div class="card-footer text-center">
        <a href="https://github.com/laravel-admin-extensions" target="_blank" class="text-uppercase">View All Extensions</a>
    </div>
    <!-- /.card-footer -->
</div>
