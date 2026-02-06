<?php

namespace OmegaUp\Email;

class EmailTemplate {
    const OMEGAUP_DARK_GREY = '#353a40';
    const OMEGAUP_GREEN = '#35b835';
    const OMEGAUP_GREEN_DARK = '#2d9a2d';
    const OMEGAUP_BLUE = '#5588dd';
    const OMEGAUP_GREY_LIGHTER = '#bbbbbb';
    const OMEGAUP_WHITE = '#ffffff';
    const OMEGAUP_LIGHT_GREY_BG = '#f5f5f5';
    const OMEGAUP_INFO_BG = 'rgba(255, 255, 255, 0.12)';
    const LOGO_URL = 'https://omegaup.com/media/omegaup_curves.png';

    public static function wrapWithBranding(
        string $title,
        string $content
    ): string {
        $darkGrey = self::OMEGAUP_DARK_GREY;
        $blue = self::OMEGAUP_BLUE;
        $white = self::OMEGAUP_WHITE;
        $green = self::OMEGAUP_GREEN;
        $greenDark = self::OMEGAUP_GREEN_DARK;
        $lightGreyBg = self::OMEGAUP_LIGHT_GREY_BG;
        $infoBg = self::OMEGAUP_INFO_BG;
        $border = self::OMEGAUP_GREY_LIGHTER;
        $logoUrl = self::LOGO_URL;

        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
HTML . htmlspecialchars($title) . <<<HTML
</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: $darkGrey;
            background-color: $lightGreyBg;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: $white;
            border-radius: 4px;
        }
        .email-header {
            background: $white;
            padding: 20px 30px;
            text-align: center;
            border-bottom: 1px solid $border;
        }
        .email-header img {
            max-width: 120px;
            height: auto;
        }
        .email-body {
            padding: 30px;
            background-color: $blue;
            color: $white;
        }
        .email-body h1 {
            color: $white;
            font-size: 24px;
            margin: 0 0 15px 0;
            font-weight: 700;
        }
        .email-body p {
            color: $white;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        .btn-primary {
            display: inline-block;
            background-color: $green;
            color: $white;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin: 10px 0;
        }
        .btn-primary:hover {
            background-color: $greenDark;
            text-decoration: none;
        }
        .text-center {
            text-align: center;
        }
        .link-box {
            word-break: break-all;
        }
        .info-box {
            background-color: $infoBg;
            border: 1px solid $white;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            font-size: 12px;
            color: $white;
        }
        .info-box a {
            color: $white;
            text-decoration: none;
            font-weight: 600;
        }
        .link-box a {
            color: $white;
        }
        .security-notice {
            color: $white;
            font-size: 14px;
            font-weight: 600;
        }
        .email-footer {
            background-color: $lightGreyBg;
            padding: 20px;
            text-align: center;
            border-top: 1px solid $border;
            font-size: 12px;
            color: $darkGrey;
        }
        .email-footer strong {
            font-weight: 700;
        }
        .email-social {
            margin: 0 0 10px 0;
        }
        .email-social a {
            display: inline-block;
            margin: 0 6px;
        }
        .email-social img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid $border;
            background: $white;
        }
        .email-footer a {
            color: $blue;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .email-body {
                padding: 20px;
            }
            .email-body h1 {
                font-size: 20px;
            }
            .btn-primary {
                display: block;
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoUrl" alt="omegaUp">
        </div>
        <div class="email-body">
HTML . $content . <<<'HTML'
        </div>
        <div class="email-footer">
            <div class="email-social">
                <a href="https://discord.com/invite/K3JFd9d3wk" target="_blank" rel="noreferrer">
                    <img src="https://omegaup.com/media/homepage/discord_logo.svg" alt="Discord">
                </a>
                <a href="https://www.facebook.com/omegaup/" target="_blank" rel="noreferrer">
                    <img src="https://www.facebook.com/images/fb_icon_325x325.png" alt="Facebook">
                </a>
            </div>
            <p style="margin: 0 0 8px 0;"><strong>© 2026 omegaUp. All rights reserved.</strong></p>
            <p style="margin: 0 0 8px 0;">
                <a href="https://omegaup.com">Visit omegaUp</a> |
                <a href="https://blog.omegaup.com">Blog</a>
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate email verification (welcome + email confirmation) content
     *
     * @param array<string, string> $messages
     */
    public static function getVerificationEmailContent(
        string $verificationLink,
        array $messages
    ): string {
        $verificationLink = htmlspecialchars($verificationLink);

        return <<<HTML
<h1>{$messages['welcome_title']}</h1>
<p>{$messages['welcome_intro']}</p>

<p>{$messages['verify_instruction']}</p>

<div class="text-center">
    <a href="$verificationLink" class="btn-primary">{$messages['verify_button']}</a>
</div>

<p>{$messages['verify_subtext']}</p>
<div class="info-box link-box">
    <a href="$verificationLink">$verificationLink</a>
</div>

<div class="info-box security-notice">
    {$messages['security_notice']}
</div>

<p>{$messages['closing']}</p>
HTML;
    }
}
