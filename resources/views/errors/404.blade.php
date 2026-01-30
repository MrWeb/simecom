<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagina non trovata</title>
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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #fbbf24;
            line-height: 1;
            margin-bottom: 10px;
        }

        h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        p {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .home-btn {
            display: inline-block;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1e3a5f;
            padding: 14px 32px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .home-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
        }

        .home-btn:active {
            transform: translateY(0);
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #64748b;
            font-size: 0.7rem;
            line-height: 1.5;
        }

        .footer p {
            color: #64748b;
            font-size: 0.7rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #f0f5fa;
            padding: 15px 20px;
            text-align: center;
            z-index: 100;
        }

        .header img {
            height: 50px;
            width: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="https://www.simecom.it/" target="_blank">
            <img src="/logo_simecom.png" alt="Simecom">
        </a>
    </div>

    <div class="container">
        <div class="error-code">404</div>
        <h1>Pagina non trovata</h1>
        <p>
            La pagina che stai cercando non esiste o potrebbe essere stata spostata.
        </p>
        <a href="https://www.simecom.it/" class="home-btn">
            Torna alla Home
        </a>

        <div class="footer">
            <p>Simecom S.R.L. – soggetta a direzione e coordinamento di Sime Partecipazioni S.p.A.<br>
            Sede legale: via Nelson Mandela, 1 – 26010 Vaiano Cremasco (CR) – Cap. € 1.000.000,00 i.v.<br>
            C.C.I.A.A. R.E.A. N. 157175 – C.F./P.Iva 01274520194 – Registro Imprese di Cremona n. 01274520194</p>
        </div>
    </div>
</body>
</html>
