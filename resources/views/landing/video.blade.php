<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Il tuo video personalizzato</title>
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
            max-width: 900px;
            width: 100%;
        }

        .greeting {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }

        .greeting h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .greeting h1 span {
            color: #fbbf24;
        }

        .greeting p {
            color: #94a3b8;
            font-size: 1rem;
        }

        .video-wrapper {
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        video {
            width: 100%;
            display: block;
        }

        .video-wrapper.clickable {
            cursor: pointer;
        }

        .video-wrapper.clickable::after {
            content: 'Clicca per scoprire di più';
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9);
            color: #1e3a5f;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: translateX(-50%) scale(1); }
            50% { transform: translateX(-50%) scale(1.05); }
        }

        .play-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
        }

        .play-button {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        .play-button:hover {
            transform: scale(1.1);
        }

        .play-button svg {
            width: 40px;
            height: 40px;
            margin-left: 2px;
            fill: #1e3a5f;
        }

        .hidden {
            display: none !important;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #64748b;
            font-size: 0.7rem;
            line-height: 1.5;
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
        <div class="greeting">
            <h1>Ciao <span>{{ ucwords(strtolower($campaign->customer_name)) }}</span>!</h1>
            <p>Abbiamo preparato questo video per te</p>
        </div>

        <div class="video-wrapper" id="videoWrapper">
            <div class="play-overlay hidden" id="playOverlay">
                <div class="play-button">
                    <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
            </div>
            <video id="video" controls playsinline>
                <source src="{{ $videoUrl }}" type="video/mp4">
                Il tuo browser non supporta il tag video.
            </video>
        </div>

        <div class="footer">
            <p>Simecom S.R.L. – soggetta a direzione e coordinamento di Sime Partecipazioni S.p.A.<br>
            Sede legale: via Nelson Mandela, 1 – 26010 Vaiano Cremasco (CR) – Cap. € 1.000.000,00 i.v.<br>
            C.C.I.A.A. R.E.A. N. 157175 – C.F./P.Iva 01274520194 – Registro Imprese di Cremona n. 01274520194</p>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const wrapper = document.getElementById('videoWrapper');
        const playOverlay = document.getElementById('playOverlay');
        const redirectLink = @json($redirectLink);
        let isClickable = false;

        // Tenta autoplay con audio
        const playPromise = video.play();

        if (playPromise !== undefined) {
            playPromise.then(() => {
                // Autoplay riuscito
            }).catch(() => {
                // Autoplay fallito, mostra play button
                playOverlay.classList.remove('hidden');
            });
        }

        // Click su overlay per avviare video
        playOverlay.addEventListener('click', function() {
            playOverlay.classList.add('hidden');
            video.play();
        });

        video.addEventListener('timeupdate', function() {
            const timeRemaining = video.duration - video.currentTime;

            if (timeRemaining <= 5 && !isClickable) {
                isClickable = true;
                video.removeAttribute('controls');
                wrapper.classList.add('clickable');
            }
        });

        wrapper.addEventListener('click', function(e) {
            if (isClickable && e.target !== video) {
                window.location.href = redirectLink;
            } else if (isClickable) {
                window.location.href = redirectLink;
            }
        });

    </script>
</body>
</html>
