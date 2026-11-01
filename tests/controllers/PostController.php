<?php

namespace Tests\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Tests\Models\Post;

class PostController extends AdminController
{
    protected $title = 'Posts';

    protected function grid()
    {
        $grid = new Grid(new Post());

        $grid->column('id', 'ID');
        $grid->column('title');

        $grid->enableTrash();

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new Post());

        $form->text('title');

        return $form;
    }
}
