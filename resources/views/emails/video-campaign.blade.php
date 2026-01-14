<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Il tuo video personalizzato</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header con logo -->
                    <tr>
                        <td style="background-color: #f0f5fa; padding: 25px; text-align: center;">
                            <a href="https://www.simecom.it/" target="_blank">
                                <img src="{{ asset('logo_simecom.png') }}" alt="Simecom" style="height: 50px; width: auto;">
                            </a>
                        </td>
                    </tr>

                    <!-- Contenuto principale -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h1 style="margin: 0 0 20px 0; color: #1e3a5f; font-size: 28px; font-weight: 700;">
                                Ciao {{ ucwords(strtolower($customerName)) }}!
                            </h1>

                            <p style="margin: 0 0 25px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Abbiamo preparato un <strong>video personalizzato</strong> esclusivamente per te.
                            </p>

                            <!-- Pulsante CTA -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ $videoUrl }}"
                                           style="display: inline-block; background-color: #F39200; color: #ffffff; padding: 18px 40px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(243, 146, 0, 0.4);">
                                            Guarda il tuo video
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 25px 0 0 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Clicca sul pulsante qui sopra per visualizzare il contenuto che abbiamo creato apposta per te.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; color: #888888; font-size: 12px; line-height: 1.5;">
                                Se non riesci a cliccare il pulsante, copia e incolla questo link nel tuo browser:
                            </p>
                            <p style="margin: 0; word-break: break-all;">
                                <a href="{{ $videoUrl }}" style="color: #F39200; font-size: 12px;">{{ $videoUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Copyright -->
                    <tr>
                        <td style="background-color: #1e3a5f; padding: 20px 30px; text-align: center;">
                            <p style="margin: 0; color: #94a3b8; font-size: 11px; line-height: 1.6;">
                                Simecom S.R.L. – Via Nelson Mandela, 1 – 26010 Vaiano Cremasco (CR)<br>
                                C.F./P.Iva 01274520194
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
