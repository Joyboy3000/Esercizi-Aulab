<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicController extends Controller
{
    public function homepage()
    {
        $articles = Schema::hasTable('articles')
            ? Article::with(['category', 'images'])
                ->where('is_accepted', true)
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get()
            : new Collection();

        return view('welcome', compact('articles'));
    }

    public function setLanguage(string $lang, Request $request): RedirectResponse
    {
        $supportedLocales = ['it', 'en', 'es'];

        if (in_array($lang, $supportedLocales, true)) {
            session()->put('locale', $lang);
        }

        return redirect()->back();
    }
}
