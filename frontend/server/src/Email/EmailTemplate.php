<?php

namespace OmegaUp\Email;

class EmailTemplate {
    const OMEGAUP_DARK_GREY = '#353a40';
    const OMEGAUP_GREEN = '#35b835';
    const OMEGAUP_BLUE = '#5588dd';
    const OMEGAUP_PRIMARY_ACCENT = '#0275d8';
    const OMEGAUP_GREY_LIGHTER = '#bbbbbb';
    const OMEGAUP_WHITE = '#ffffff';
    const OMEGAUP_LIGHT_GREY_BG = '#f5f5f5';
    const OMEGAUP_GREEN_DARK = '#2d9a2d';
    const OMEGAUP_ACCENT_LIGHT_BG = '#f0f8ff';
    const LOGO_URL = 'https://omegaup.com/media/omegaup_curves.png';

    public static function wrapWithBranding(
        string $title,
        string $content
    ): string {
        $darkGrey = self::OMEGAUP_DARK_GREY;
        $blue = self::OMEGAUP_BLUE;
        $accentBlue = self::OMEGAUP_PRIMARY_ACCENT;
        $white = self::OMEGAUP_WHITE;
        $lightGreyBg = self::OMEGAUP_LIGHT_GREY_BG;
        $lightBg = self::OMEGAUP_LIGHT_GREY_BG;
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
            background: linear-gradient(135deg, $blue 0%, $accentBlue 100%);
            padding: 30px;
            text-align: center;
        }
        .email-header img {
            max-width: 120px;
            height: auto;
        }
        .email-body {
            padding: 30px;
        }
        .email-body h1 {
            color: $darkGrey;
            font-size: 24px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        .email-body h2 {
            color: $blue;
            font-size: 16px;
            margin: 20px 0 12px 0;
            font-weight: 600;
        }
        .email-body p {
            color: $darkGrey;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 15px 0;
        }
        .btn-primary {
            display: inline-block;
            background-color: $blue;
            color: $white;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }
        .btn-primary:hover {
            background-color: $accentBlue;
            text-decoration: none;
        }
        .text-center {
            text-align: center;
        }
        .link-box {
            background-color: $white;
            border: 2px solid $blue;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
            color: $darkGrey;
        }
        .link-box a {
            color: $blue;
            text-decoration: none;
            font-weight: 600;
        }
        .security-notice {
            background-color: $white;
            border: 2px solid $blue;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            font-size: 12px;
            color: $darkGrey;
        }
        .email-footer {
            background-color: $lightBg;
            padding: 20px;
            text-align: center;
            border-top: 1px solid $border;
            font-size: 12px;
            color: $darkGrey;
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
            <p style="margin: 0 0 8px 0;">© 2026 omegaUp. All rights reserved.</p>
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

<h2>{$messages['verify_section_title']}</h2>
<p>{$messages['verify_instruction']}</p>

<div class="text-center">
    <a href="$verificationLink" class="btn-primary">{$messages['verify_button']}</a>
</div>

<p>{$messages['verify_subtext']}</p>
<div class="link-box">
    <div style="word-break: break-all;">
        <a href="$verificationLink">$verificationLink</a>
    </div>
</div>

<div class="security-notice">
    {$messages['security_notice']}
</div>

<p>{$messages['closing']}</p>
HTML;
    }
}
