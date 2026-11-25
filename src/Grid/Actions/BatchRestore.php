<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BatchRestore extends BatchAction
{
    /**
     * @return string
     */
    public function name()
    {
        return __('admin.restore');
    }

    /**
     * @param Collection $collection
     *
     * @return Response
     */
    public function handle(Collection $collection)
    {
        try {
            DB::transaction(function () use ($collection) {
                $collection->each->restore();
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
