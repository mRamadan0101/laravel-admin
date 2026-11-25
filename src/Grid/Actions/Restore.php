<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Restore extends RowAction
{
    /**
     * @return string
     */
    public function name()
    {
        return '<i class="fa-solid fa-trash-arrow-up"></i> '.__('admin.restore');
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
                $model->restore();
            });
        } catch (\Exception $exception) {
            return $this->response()->error($exception->getMessage());
        }

        return $this->response()->success(trans('admin.restore_succeeded'))->refresh();
    }

    /**
     * @return void
     */
    public function dialog()
    {
        $this->question(trans('admin.restore_confirm'));
    }
}
