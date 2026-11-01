<?php

use Encore\Admin\Auth\Database\Administrator;
use Tests\Models\Post;

class TrashTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');

        Post::create(['title' => 'Kept post']);
        Post::create(['title' => 'Trashed post'])->delete();
    }

    public function testListHidesTrashedRowsAndShowsSwitcher()
    {
        $this->visit('admin/posts')
            ->see('Kept post')
            ->dontSee('Trashed post')
            ->seeElement('.grid-trash-switcher')
            ->seeInElement('.grid-trash-switcher .badge', '1');
    }

    public function testTrashListsOnlyTrashedRows()
    {
        $this->visit('admin/posts?_trashed=1')
            ->see('Trashed post')
            ->dontSee('Kept post')
            ->see('Restore')
            ->see('Delete permanently')
            ->dontSeeElement('.grid-create-btn');
    }

    public function testRestoreAction()
    {
        $post = Post::onlyTrashed()->first();

        $this->post('admin/_handle_action_', [
            '_action' => 'Encore_Admin_Grid_Actions_Restore',
            '_model'  => 'Tests_Models_Post',
            '_key'    => $post->id,
        ])->seeJson(['status' => true]);

        $this->assertFalse($post->fresh()->trashed());
    }

    public function testForceDeleteAction()
    {
        $post = Post::onlyTrashed()->first();

        $this->post('admin/_handle_action_', [
            '_action' => 'Encore_Admin_Grid_Actions_ForceDelete',
            '_model'  => 'Tests_Models_Post',
            '_key'    => $post->id,
        ])->seeJson(['status' => true]);

        $this->assertNull(Post::withTrashed()->find($post->id));
    }

    public function testBatchRestore()
    {
        $second = Post::create(['title' => 'Second']);
        $second->delete();

        $this->post('admin/_handle_action_', [
            '_action' => 'Encore_Admin_Grid_Actions_BatchRestore',
            '_model'  => 'Tests_Models_Post',
            '_key'    => Post::onlyTrashed()->pluck('id')->implode(','),
        ])->seeJson(['status' => true]);

        $this->assertSame(0, Post::onlyTrashed()->count());
    }

    public function testDeleteStillSoftDeletes()
    {
        $post = Post::where('title', 'Kept post')->first();

        $this->delete('admin/posts/'.$post->id);

        $this->assertTrue(Post::withTrashed()->find($post->id)->trashed());
    }
}
