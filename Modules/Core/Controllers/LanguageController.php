<?php

declare(strict_types=1);

namespace Modules\Core\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class LanguageController extends BaseController
{
    public function switch(string $locale): RedirectResponse
    {
        $supportedLocales = array_keys(lab_core_supported_locales());

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('App')->defaultLocale;
        }

        session()->set('locale', $locale);

        return redirect()->back();
    }
}
