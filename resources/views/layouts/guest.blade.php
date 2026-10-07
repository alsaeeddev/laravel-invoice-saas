<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Auth pages styling (keeps the original look on top of Bootstrap) -->
        <style>
            body { font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; color: #111827; background: #f3f4f6; -webkit-font-smoothing: antialiased; }
            .auth-wrap { min-height: 100vh; }
            .auth-logo { width: 80px; height: 80px; color: #6b7280; fill: currentColor; }
            .auth-card { background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1); overflow: hidden; }
            @media (min-width: 640px) {
                .auth-card { max-width: 448px; border-radius: .5rem; }
            }

            .auth-card .form-label { font-weight: 500; font-size: .875rem; color: #374151; margin-bottom: 0; }
            .auth-card .form-control { border-color: #d1d5db; border-radius: .375rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,.05); }
            .auth-card .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 1px #6366f1; }
            .auth-card .form-check-input { border-color: #d1d5db; box-shadow: 0 1px 2px 0 rgba(0,0,0,.05); }
            .auth-card .form-check-input:checked { background-color: #4f46e5; border-color: #4f46e5; }
            .auth-card .form-check-input:focus { border-color: #6366f1; box-shadow: 0 0 0 .2rem rgba(99,102,241,.25); }

            .auth-muted { color: #4b5563; font-size: .875rem; }
            .auth-error { color: #dc2626; font-size: .875rem; }
            .auth-error li + li { margin-top: .25rem; }
            .auth-success { color: #16a34a; font-size: .875rem; font-weight: 500; }
            .auth-link { color: #4b5563; font-size: .875rem; text-decoration: underline; }
            .auth-link:hover { color: #111827; }

            .btn-auth { display: inline-flex; align-items: center; padding: .5rem 1rem; background: #1f2937; border: 1px solid transparent; border-radius: .375rem; color: #fff; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .1em; transition: all .15s ease-in-out; }
            .btn-auth:hover, .btn-auth:focus { background: #374151; color: #fff; }
            .btn-auth:active { background: #111827; color: #fff; }
            .btn-auth:focus { outline: none; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #6366f1; }
        </style>
    </head>
    <body>
        <div class="auth-wrap d-flex flex-column align-items-center justify-content-sm-center pt-4 pt-sm-0">
            <div>
                <a href="/">
                    <x-application-logo class="auth-logo" />
                </a>
            </div>

            <div class="auth-card w-100 mt-4 px-4 py-3">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
