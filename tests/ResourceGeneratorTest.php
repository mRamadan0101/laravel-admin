<?php

use Encore\Admin\Console\ResourceGenerator;
use Tests\Models\Profile;
use Tests\Models\User;

class ResourceGeneratorTest extends TestCase
{
    public function testGenerateForm()
    {
        $form = (new ResourceGenerator(User::class))->generateForm();

        $this->assertStringContainsString("\$form->text('username', __('Username'));", $form);
        $this->assertStringContainsString("\$form->email('email', __('Email'));", $form);
        $this->assertStringContainsString("\$form->mobile('mobile', __('Mobile'));", $form);
        $this->assertStringContainsString("\$form->image('avatar', __('Avatar'));", $form);
        $this->assertStringContainsString("\$form->password('password', __('Password'));", $form);
        $this->assertStringContainsString("\$form->text('data', __('Data'));", $form);
        $this->assertStringNotContainsString("'id'", $form);
        $this->assertStringNotContainsString("'created_at'", $form);
    }

    public function testGenerateFormDatetimeColumns()
    {
        $form = (new ResourceGenerator(Profile::class))->generateForm();

        $this->assertStringContainsString("\$form->color('color', __('Color'));", $form);
        $this->assertStringContainsString("\$form->datetime('start_at', __('Start at'))", $form);
    }

    public function testGenerateGridAndShow()
    {
        $generator = new ResourceGenerator(User::class);

        $this->assertStringContainsString("\$grid->column('id', __('Id'));", $generator->generateGrid());
        $this->assertStringContainsString("\$show->field('email', __('Email'));", $generator->generateShow());
    }
}
