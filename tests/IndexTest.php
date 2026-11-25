<?php

use Encore\Admin\Auth\Database\Administrator;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testIndex()
    {
        $this->visit('admin/')
            ->see('Dashboard')
            ->see('Description...')

            ->see('Environment')
            ->see('PHP version')
            ->see('Laravel version')

            ->see('Available extensions')
            ->seeLink('laravel-admin-ext/helpers', 'https://github.com/laravel-admin-extensions/helpers')
            ->seeLink('laravel-admin-ext/backup', 'https://github.com/laravel-admin-extensions/backup')
            ->seeLink('laravel-admin-ext/media-manager', 'https://github.com/laravel-admin-extensions/media-manager')

            ->see('Dependencies');
    }

    public function testClickMenu()
    {
        $this->visit('admin/')
            ->click('Users')
            ->seePageis('admin/auth/users')
            ->click('Roles')
            ->seePageis('admin/auth/roles')
            ->click('Permission')
            ->seePageis('admin/auth/permissions')
            ->click('Menu')
            ->seePageis('admin/auth/menu')
            ->click('Operation log')
            ->seePageis('admin/auth/logs');
    }

    public function testSkinClassFollowsConfig()
    {
        config(['admin.skin' => 'skin-red']);

        $this->visit('admin/');

        $this->assertStringContainsString('skin-red', $this->crawler->filter('body')->attr('class'));
        $this->assertEquals('dark', $this->crawler->filter('.app-sidebar')->attr('data-bs-theme'));

        config(['admin.skin' => 'green-light']);

        $this->visit('admin/');

        $this->assertStringContainsString('skin-green', $this->crawler->filter('body')->attr('class'));
        $this->assertStringContainsString('bg-body', $this->crawler->filter('.app-sidebar')->attr('class'));
        $this->assertNull($this->crawler->filter('.app-sidebar')->attr('data-bs-theme'));
    }

    public function testLocaleSwitcherAppearsWhenRtlLocaleIsSet()
    {
        config(['admin.rtl' => 'ar']);

        $this->visit('admin/')
            ->see('English')
            ->see('العربية')
            ->seeElement('.admin-locale-switcher');

        $this->visit('admin/locale/ar');

        $this->assertEquals('rtl', $this->crawler->filter('html')->attr('dir'));
        $this->assertEquals('ar', $this->crawler->filter('html')->attr('lang'));
    }

    public function testLocaleSwitcherHiddenWhenRtlIsAuto()
    {
        config(['admin.rtl' => null]);

        $this->visit('admin/');

        $this->assertCount(0, $this->crawler->filter('.admin-locale-switcher'));
    }
}
