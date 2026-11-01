<?php

namespace Encore\Admin\Grid\Tools;

class TrashSwitcher extends AbstractTool
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $name = $this->grid->getTrashQueryName();
        $pageName = 'page';
        $inTrash = $this->grid->inTrash();

        $count = $this->grid->model()->getOriginalModel()->newQuery()->onlyTrashed()->count();

        $list = request()->fullUrlWithoutQuery([$name, $pageName]);
        $trash = request()->fullUrlWithQuery([$name => 1, $pageName => null]);

        return view('admin::grid.trash-switcher', compact('inTrash', 'count', 'list', 'trash'))->render();
    }
}
