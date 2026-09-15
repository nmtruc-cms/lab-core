<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        return auth()->loggedIn()
            ? redirect()->to(lab_core_home_url())
            : redirect()->to(site_url('login'));
    }
}
