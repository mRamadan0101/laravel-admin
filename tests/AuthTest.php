<?php

class AuthTest extends TestCase
{
    public function testLoginPage()
    {
        $this->visit('admin/auth/login')
            ->see('login');
    }

    public function testVisitWithoutLogin()
    {
        $this->visit('admin')
            ->dontSeeIsAuthenticated('admin')
            ->seePageIs('admin/auth/login');
    }

    public function testLogin()
    {
        $credentials = ['username' => 'admin', 'password' => 'admin'];

        $this->visit('admin/auth/login')
            ->see('login')
            ->submitForm('Login', $credentials)
            ->see('dashboard')
            ->seeCredentials($credentials, 'admin')
            ->seeIsAuthenticated('admin')
            ->seePageIs('admin')
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

        $this
            ->seeInElement('.sidebar-menu .nav-link p', 'Admin')
            ->seeInElement('.sidebar-menu .nav-link p', 'Users')
            ->seeInElement('.sidebar-menu .nav-link p', 'Roles')
            ->seeInElement('.sidebar-menu .nav-link p', 'Permission')
            ->seeInElement('.sidebar-menu .nav-link p', 'Operation log')
            ->seeInElement('.sidebar-menu .nav-link p', 'Menu');
    }

    public function testLogout()
    {
        $this->visit('admin/auth/logout')
            ->seePageIs('admin/auth/login')
            ->dontSeeIsAuthenticated('admin');
    }
}
