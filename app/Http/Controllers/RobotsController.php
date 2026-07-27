<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /account-settings',
            'Disallow: /notifications',
            'Disallow: /profile',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /dashboard',
            'Disallow: /api/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
