<div class="modal" tabindex="-1" role="dialog" id="{{ $modal_id }}">
    <div class="modal-dialog {{ $modal_size }}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title fs-5">{{ $title }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form>
            <div class="modal-body">
                @foreach($fields as $field)
                    {!! $field->render() !!}
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('admin.submit') }}</button>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->