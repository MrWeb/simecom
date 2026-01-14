<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Video in preparazione</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            text-align: center;
            color: white;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        p {
            color: #94a3b8;
            line-height: 1.6;
        }

        .spinner {
            margin: 30px auto;
            width: 50px;
            height: 50px;
            border: 4px solid #334155;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    <meta http-equiv="refresh" content="30">
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Il tuo video e' in preparazione</h1>
        <p>
            Stiamo preparando il tuo video personalizzato.<br>
            La pagina si aggiornera' automaticamente.
        </p>
    </div>
</body>
</html>
