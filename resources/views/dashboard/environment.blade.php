<div class="card">
    <div class="card-header">
        <h3 class="card-title">Environment</h3>

        <div class="card-tools float-end">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="fa-solid fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <!-- /.card-header -->
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">

                @foreach($envs as $env)
                <tr>
                    <td width="120px">{{ $env['name'] }}</td>
                    <td>{{ $env['value'] }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.card-body -->
</div>