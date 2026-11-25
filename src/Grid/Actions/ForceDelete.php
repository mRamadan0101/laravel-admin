<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ForceDelete extends RowAction
{
    /**
     * @return string
     */
    public function name()
    {
        return '<i class="fa-solid fa-trash text-danger"></i> '.__('admin.force_delete');
    }

    /**
     * @param Model $model
     *
     * @return Response
     */
    public function handle(Model $model)
    {
        try {
            DB::transaction(function () use ($model) {
                $model->forceDelete();
            });
        } catch (\Exception $exception) {
            return $this->response()->error(trans('admin.delete_failed')." : {$exception->getMessage()}");
        }

        return $this->response()->success(trans('admin.delete_succeeded'))->refresh();
    }

    /**
     * @return void
     */
    public function dialog()
    {
        $this->question(trans('admin.force_delete_confirm'), '', ['confirmButtonColor' => '#d33']);
    }
}
