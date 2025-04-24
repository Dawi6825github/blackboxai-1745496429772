<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bingo Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #f7fafc;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            background: #4f46e5;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            text-decoration: none;
            margin-right: 0.5rem;
        }
        .btn:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Bingo Game</h1>
            <p>Play bingo online with friends and win exciting prizes!</p>
        </div>
        
        <div class="card">
            <h2>How to Play</h2>
            <p>1. Register or login to your account</p>
            <p>2. Purchase bingo cards</p>
            <p>3. Join active rounds</p>
            <p>4. Play and win!</p>
            
            @if (Route::has('login'))
                <div class="mt-4">
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.rounds.index') }}" class="btn">Admin Dashboard</a>
                        @else
                            <a href="{{ route('user.gameboard') }}" class="btn">Play Now</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn">Log in</a>
                        <a href="{{ route('register') }}" class="btn">Register</a>
                    @endauth
                </div>
            @endif
        </div>
    </div>
</body>
</html>
