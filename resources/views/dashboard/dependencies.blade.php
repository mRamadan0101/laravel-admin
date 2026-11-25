<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dependencies</h3>

        <div class="card-tools float-end">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i class="fa-solid fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <!-- /.card-header -->
    <div class="card-body dependencies">
        <div class="table-responsive">
            <table class="table table-striped">
                @foreach($dependencies as $dependency => $version)
                <tr>
                    <td width="240px">{{ $dependency }}</td>
                    <td><span class="badge text-bg-primary">{{ $version }}</span></td>
                </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.card-body -->
</div>

<script>
    $('.dependencies').css({'max-height': '510px', 'overflow-y': 'auto'});
</script>
