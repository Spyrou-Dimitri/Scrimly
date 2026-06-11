<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #13131B; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    <table style="background-color: #13131B; margin: 0; padding: 24px 0;">
        <tr>
            <td align="center">
                <table  style="max-width: 600px; width: 100%;">
                    <tr>
                        <td align="center" style="padding: 0 24px 24px;">
                            <table>
                                <tr>
                                    
                                    <td style="vertical-align: middle;">
                                        <span style="font-size: 28px; font-weight: 700; color: #C99C3D;">ScrimlyLol</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 24px;">
                            <table style="background-color: #1B1B23; border: 1px solid #333237; border-radius: 0;">
                                <tr>
                                    <td style="padding: 32px 28px;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 24px 24px 0; color: #959AA4; font-size: 13px; line-height: 1.6;">
                            <p style="margin: 0 0 8px;">&copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
                            @hasSection('footer')
                                @yield('footer')
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
