<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ArticleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', only: ['create']),
        ];
    }

    public function create()
    {
        return view('article.create');
    }

    public function index()
    {
        $articles = Article::with(['category', 'user', 'images'])
            ->where('is_accepted', true)
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('article.index', compact('articles'));
    }

    public function show(Article $article)
    {
        $article->loadMissing(['category', 'user', 'images']);

        if ($article->is_accepted !== true && (! auth()->check() || ! auth()->user()->is_revisor)) {
            abort(404);
        }

        return view('article.show', compact('article'));
    }

    public function byCategory(Category $category)
    {
        $articles = $category->articles()
            ->with(['category', 'user', 'images'])
            ->where('is_accepted', true)
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('article.byCategory', compact('articles', 'category'));
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->input('query'));

        if ($query === '') {
            return redirect()->route('article.index');
        }

        $articles = Article::search($query)
            ->query(function ($builder) {
                $builder->with(['category', 'user', 'images'])
                    ->where('is_accepted', true);
            })
            ->paginate(9)
            ->withQueryString();

        return view('article.searched', compact('articles', 'query'));
    }
}
