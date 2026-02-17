<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        if (in_array($locale, ['en', 'fr', 'ar'])) {
            session(['locale' => $locale]);
        }
        return back();
    }
}
