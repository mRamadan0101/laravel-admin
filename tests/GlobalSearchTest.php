<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\GlobalSearch\GlobalSearch;
use Tests\Models\Post;

class GlobalSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    protected function tearDown(): void
    {
        GlobalSearch::flush();

        parent::tearDown();
    }

    public function testNavbarHasSearchBox()
    {
        $this->visit('admin')
            ->seeElement('.admin-global-search input[role="combobox"]');
    }

    public function testSearchesMenuPages()
    {
        $this->get('admin/_global_search_?q=oper')
            ->seeJson(['title' => 'Operation log', 'subtitle' => 'Admin']);
    }

    public function testSearchesRegisteredResources()
    {
        Post::create(['title' => 'Hello world']);
        Post::create(['title' => 'Another post']);
        Post::create(['title' => '100% match_test']);

        Admin::globalSearch(Post::class)
            ->label('Posts')
            ->columns(['title'])
            ->subtitle(function ($post) {
                return "#{$post->id}";
            })
            ->url('posts/{id}/edit');

        $response = $this->get('admin/_global_search_?q=hello')->response->getData(true);

        $group = collect($response['groups'])->firstWhere('label', 'Posts');

        $this->assertCount(1, $group['items']);
        $this->assertSame('Hello world', $group['items'][0]['title']);
        $this->assertSame('#1', $group['items'][0]['subtitle']);
        $this->assertStringEndsWith('/admin/posts/1/edit', $group['items'][0]['url']);

        $response = $this->get('admin/_global_search_?q=%25')->response->getData(true);
        $group = collect($response['groups'])->firstWhere('label', 'Posts');

        $this->assertSame(['100% match_test'], array_column($group['items'], 'title'));
    }

    public function testResourcesFromConfig()
    {
        Post::create(['title' => 'Configured']);

        config(['admin.global_search.resources' => [
            Post::class => ['columns' => ['title'], 'label' => 'Articles'],
        ]]);

        $this->get('admin/_global_search_?q=config')
            ->seeJson(['label' => 'Articles', 'title' => 'Configured']);
    }

    public function testPermissionHidesResource()
    {
        Post::create(['title' => 'Secret post']);

        Admin::globalSearch(Post::class)->columns(['title'])->permission('posts.view');

        $user = Administrator::create(['username' => 'viewer', 'password' => bcrypt('viewer'), 'name' => 'Viewer']);
        $this->be($user, 'admin');

        $this->get('admin/_global_search_?q=secret')
            ->dontSeeJson(['title' => 'Secret post']);
    }

    public function testRequestsAreNotLogged()
    {
        $this->get('admin/_global_search_?q=anything');

        $this->dontSeeInDatabase(config('admin.database.operation_log_table'), ['path' => 'admin/_global_search_']);
    }
}
