<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bingo Game Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4f46e5;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .card {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: white;
        }
        .win {
            color: green;
            font-weight: bold;
        }
        .lose {
            color: red;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bingo Game Result</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $bet->user->name }},</p>
            
            <p>We're writing to inform you about the result of your recent Bingo game.</p>
            
            <div class="card">
                <h2>Game Details</h2>
                <p><strong>Round:</strong> {{ $bet->round->name }}</p>
                <p><strong>Pattern:</strong> {{ $bet->round->patterns->first()->name }}</p>
                <p><strong>Bet Amount:</strong> ${{ number_format($bet->amount, 2) }}</p>
                
                @if($bet->is_winner)
                    <p class="win">Congratulations! You won ${{ number_format($bet->winning_amount, 2) }}!</p>
                @else
                    <p class="lose">Unfortunately, you didn't win this time. Better luck next round!</p>
                @endif
            </div>
            
            <p>Thank you for playing our Bingo game. Visit our website to join more rounds and increase your chances of winning!</p>
            
            <p>Best regards,<br>The Bingo Game Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Bingo Game. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
