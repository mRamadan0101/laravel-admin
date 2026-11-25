<?php

namespace Encore\Admin\Grid\Concerns;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Actions\BatchForceDelete;
use Encore\Admin\Grid\Actions\BatchRestore;
use Encore\Admin\Grid\Actions\ForceDelete;
use Encore\Admin\Grid\Actions\Restore;
use Encore\Admin\Grid\Displayers\DropdownActions;
use Encore\Admin\Grid\Tools\TrashSwitcher;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasTrash
{
    /**
     * @var bool
     */
    protected $trashEnabled = false;

    /**
     * @var string
     */
    protected $trashQueryName = '_trashed';

    /**
     * Add a trash view for soft-deleted rows with restore and force-delete actions.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function enableTrash(bool $enable = true)
    {
        $this->trashEnabled = $enable;

        return $this;
    }

    /**
     * @return bool
     */
    public function trashEnabled()
    {
        return $this->trashEnabled
            && in_array(SoftDeletes::class, class_uses_deep($this->model()->getOriginalModel()));
    }

    /**
     * Whether the grid currently lists soft-deleted rows.
     *
     * @return bool
     */
    public function inTrash()
    {
        return $this->trashEnabled() && (bool) request($this->trashQueryName);
    }

    /**
     * @return string
     */
    public function getTrashQueryName()
    {
        return $this->trashQueryName;
    }

    /**
     * @return void
     */
    protected function setupTrash()
    {
        if (!$this->trashEnabled()) {
            return;
        }

        $this->tools->append(new TrashSwitcher());

        if (!$this->inTrash()) {
            return;
        }

        $this->model()->onlyTrashed();

        $this->disableCreateButton();

        $this->tools->batch(function (Grid\Tools\BatchActions $batch) {
            $batch->disableDelete();
            $batch->add(new BatchRestore());
            $batch->add(new BatchForceDelete());
        });

        $callback = $this->actionsCallback;

        $this->actionsCallback = function ($actions) use ($callback) {
            if ($callback) {
                $callback->call($actions, $actions);
            }

            $actions->disableView()->disableEdit()->disableDelete();

            foreach ([new Restore(), new ForceDelete()] as $action) {
                if ($actions instanceof DropdownActions) {
                    $actions->add($action);
                } else {
                    $actions->append(
                        $action->setGrid($this->grid)->setColumn($this->column)->setRow($this->row)->render()
                    );
                }
            }
        };
    }
}
