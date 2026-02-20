<?php

namespace App\Http\Controllers;

use App\Mail\BecomeRevisor;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class RevisorController extends Controller
{
    public function index()
    {
        $article_to_check = Article::whereNull('is_accepted')
            ->with(['category', 'user', 'images'])
            ->orderBy('created_at', 'asc')
            ->first();

        return view('revisor.index', compact('article_to_check'));
    }

    public function accept(Article $article)
    {
        $this->rememberLastReview($article);
        $article->setAccepted(true);
        $article->save();

        return redirect()->back()->with('success', 'Annuncio accettato.');
    }

    public function reject(Article $article)
    {
        $this->rememberLastReview($article);
        $article->setAccepted(false);
        $article->save();

        return redirect()->back()->with('success', 'Annuncio rifiutato.');
    }

    public function undoLastReview()
    {
        $lastReview = session('last_review');

        if (! $lastReview) {
            return redirect()->back()->with('error', 'Nessuna operazione da annullare.');
        }

        $article = Article::find($lastReview['article_id']);

        if (! $article) {
            session()->forget('last_review');

            return redirect()->back()->with('error', 'Annuncio non trovato.');
        }

        $article->setAccepted($lastReview['previous_state']);
        $article->save();

        session()->forget('last_review');

        return redirect()->back()->with('success', 'Ultima operazione annullata.');
    }

    public function showBecomeRevisorForm()
    {
        return view('revisor.become');
    }

    public function becomeRevisor()
    {
        if (auth()->user()->is_revisor) {
            return redirect()->route('homepage')->with('error', 'Sei gia revisore.');
        }

        Mail::to(env('ADMIN_EMAIL', config('mail.from.address')))->send(new BecomeRevisor(auth()->user()));

        return redirect()->route('homepage')->with('success', 'Richiesta inviata con successo.');
    }

    public function makeRevisor(User $user)
    {
        Artisan::call('app:make-user-revisor', ['email' => $user->email]);

        return redirect()->route('homepage')->with('success', "{$user->name} ora e' revisore.");
    }

    private function rememberLastReview(Article $article): void
    {
        session([
            'last_review' => [
                'article_id' => $article->id,
                'previous_state' => $article->is_accepted,
            ],
        ]);
    }
}
