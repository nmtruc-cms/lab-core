<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;

class SetPasswordController extends BaseController
{
    public function show(): string
    {
        return view('Modules\Core\Views\Auth\set_password', [
            'validation' => session('errors') ?? [],
        ]);
    }

    public function save()
    {
        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors());
        }

        $user = auth()->user();

        if ($user === null) {
            return redirect()->to(site_url('login'));
        }

        $user->setPassword((string) $this->request->getPost('password'));
        $user->active = 1;

        model(UserModel::class)->save($user);

        return redirect()->to(lab_core_home_url())
            ->with('message', 'Password set successfully. Welcome!');
    }
}
