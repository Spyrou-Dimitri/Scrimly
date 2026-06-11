@extends('emails.layout')

@section('content')
    <h1 style="margin: 0 0 8px; font-size: 28px; line-height: 1.25; font-weight: 700; color: #FFFFFF;">
        {{ __('mail/reset-password.greeting', ['username' => $user->username]) }}
    </h1>

    <p style="margin: 0 0 24px; padding-bottom: 16px; border-bottom: 1px solid #C99C3D; font-size: 14px; line-height: 1.6; color: #959AA4;">
        {{ __('mail/reset-password.intro') }}
    </p>

    <p style="margin: 0 0 28px; font-size: 16px; line-height: 1.7; color: #FFFFFF;">
        {{ __('mail/reset-password.expire', ['count' => $expireMinutes]) }}
    </p>

    <table style="margin: 0 0 28px;">
        <tr>
            <td align="center" style="border-radius: 0; background: #C99C3D;">
                <a href="{{ $resetUrl }}" target="_blank"  style="display: inline-block; padding: 14px 28px; font-size: 16px; font-weight: 700; color: #2A2618; text-decoration: none;">
                    {{ __('mail/reset-password.action') }}
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 20px; font-size: 14px; line-height: 1.7; color: #959AA4;">
        {{ __('mail/reset-password.outro') }}
    </p>

    <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #FFFFFF;">
        {{ __('mail/reset-password.salutation') }},
    </p>
@endsection

@section('footer')
    <p style="margin: 0; font-size: 12px; line-height: 1.6; color: #959AA4;">
        Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
        <a href="{{ $resetUrl }}" style="color: #C99C3D; word-break: break-all;">{{ $resetUrl }}</a>
    </p>
@endsection
