<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Il tuo video personalizzato</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #2563eb;">Ciao {{ $customerName }}!</h1>

    <p>Abbiamo preparato un video personalizzato per te.</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $videoUrl }}"
           style="display: inline-block; background-color: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Guarda il tuo video
        </a>
    </p>

    <p>Clicca sul pulsante qui sopra per visualizzare il contenuto che abbiamo creato apposta per te.</p>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

    <p style="color: #6b7280; font-size: 12px;">
        Se non riesci a cliccare il pulsante, copia e incolla questo link nel tuo browser:<br>
        <a href="{{ $videoUrl }}" style="color: #2563eb;">{{ $videoUrl }}</a>
    </p>
</body>
</html>
