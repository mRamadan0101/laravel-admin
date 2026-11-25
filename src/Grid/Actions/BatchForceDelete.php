<?php

namespace Encore\Admin\Grid\Actions;

use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BatchForceDelete extends BatchAction
{
    /**
     * @return string
     */
    public function name()
    {
        return __('admin.force_delete');
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
                $collection->each->forceDelete();
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
