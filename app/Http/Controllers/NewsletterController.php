<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'El correo no es válido.',
        ]);

        NewsletterSubscriber::firstOrCreate([
            'email' => $validated['email'],
        ]);

        return back()->with('newsletter_status', 'Gracias. Te hemos suscrito a la newsletter.');
    }
}
