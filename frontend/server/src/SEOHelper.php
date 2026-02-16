<?php

namespace OmegaUp;

/**
 * SEO Helper class for generating SEO metadata
 *
 * @psalm-type OpenGraph=array{type: string, title: string, description: string, url: string, image: string, imageWidth: string, imageHeight: string, siteName: string, locale: string}
 * @psalm-type TwitterCard=array{card: string, title: string, description: string, image: string, site: string, creator?: string}
 * @psalm-type SeoMeta=array{description: string, canonical: string, robots: string, og: OpenGraph, twitter: TwitterCard, keywords?: string, author?: string, structuredData?: list<OrganizationSchema|WebSiteSchema|ProblemSchema>, hreflang?: array<string, string>, appleTouchIcon?: string}
 * @psalm-type OrganizationSchema=array{'@context': string, '@type': string, name: string, url: string, logo: string, description: string, sameAs: list<string>}
 * @psalm-type WebSiteSchema=array{'@context': string, '@type': string, name: string, url: string, potentialAction: array{'@type': string, target: array{'@type': string, urlTemplate: string}, 'query-input': string}}
 * @psalm-type ProblemSchema=array{'@context': string, '@type': string, name: string, description: string, url: string, educationalLevel: string, learningResourceType: string}
 */
class SEOHelper {
    /**
     * Generates default SEO metadata for a page
     *
     * @param string $title Page title
     * @param string $description Page description
     * @param string $url Canonical URL (optional)
     * @param string $image Social share image URL (optional)
     * @param string $type Open Graph type (default: 'website')
     * @return SeoMeta SEO metadata array
     */
    public static function generateMetadata(
        string $title,
        string $description,
        ?string $url = null,
        ?string $image = null,
        string $type = 'website'
    ): array {
        $baseUrl = OMEGAUP_URL;
        if (empty($url)) {
            $url = self::getCurrentUrl();
        } else {
            $url = self::ensureAbsoluteUrl($url, $baseUrl);
        }

        if (empty($image)) {
            $image = self::getDefaultImageUrl($baseUrl);
        } else {
            $image = self::ensureAbsoluteUrl($image, $baseUrl);
        }

        $session = \OmegaUp\Controllers\Session::getCurrentSession();
        $locale = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $session['identity'] ?? null
        );

        return [
            'description' => $description,
            'canonical' => $url,
            'robots' => 'index, follow',
            'og' => [
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'image' => $image,
                'imageWidth' => '1200',
                'imageHeight' => '630',
                'siteName' => 'omegaUp',
                'locale' => self::mapLocaleToOGLocale($locale),
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $image,
                'site' => '@omegaup',
            ],
        ];
    }

    /**
     * Generates structured data (JSON-LD) for Organization
     *
     * @param string $url Base URL
     * @return OrganizationSchema Structured data array
     */
    public static function generateOrganizationSchema(string $url): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => 'omegaUp',
            'url' => $url,
            'logo' => self::ensureAbsoluteUrl('/media/omegaup.png', $url),
            'description' => 'Free educational platform that helps improve programming skills, used by tens of thousands of students and teachers in Latin America. Planning a better future. For everyone.',
            'sameAs' => [
                'https://x.com/omegaup',
                'https://github.com/omegaup/omegaup',
            ],
        ];
    }

    /**
     * Generates structured data (JSON-LD) for WebSite
     *
     * @param string $url Base URL
     * @return WebSiteSchema Structured data array
     */
    public static function generateWebSiteSchema(string $url): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'omegaUp',
            'url' => $url,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $url . '/problems/?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Generates structured data (JSON-LD) for a Problem
     *
     * @param string $title Problem title
     * @param string $description Problem description
     * @param string $url Problem URL
     * @param string $baseUrl Base URL
     * @return ProblemSchema Structured data array
     */
    public static function generateProblemSchema(
        string $title,
        string $description,
        string $url,
        string $baseUrl
    ): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LearningResource',
            'name' => $title,
            'description' => $description,
            'url' => self::ensureAbsoluteUrl($url, $baseUrl),
            'educationalLevel' => 'Intermediate',
            'learningResourceType' => 'Programming Problem',
        ];
    }

    /**
     * Gets the current URL from request
     *
     * @return string Current URL
     */
    private static function getCurrentUrl(): string {
        $protocol = (!empty(
            $_SERVER['HTTPS']
        ) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'omegaup.com';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $protocol . $host . $uri;
    }

    /**
     * Ensures URL is absolute
     *
     * @param string $url URL to check
     * @param string $baseUrl Base URL
     * @return string Absolute URL
     */
    private static function ensureAbsoluteUrl(
        string $url,
        string $baseUrl
    ): string {
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }
        $baseUrl = rtrim($baseUrl, '/');
        $url = ltrim($url, '/');
        return $baseUrl . '/' . $url;
    }

    /**
     * Gets default social share image URL
     *
     * @param string $baseUrl Base URL
     * @return string Image URL
     */
    private static function getDefaultImageUrl(string $baseUrl): string {
        return self::ensureAbsoluteUrl('/media/omegaup.png', $baseUrl);
    }

    /**
     * Maps locale to Open Graph locale format
     *
     * @param string $locale Locale code (e.g., 'en', 'es', 'pt')
     * @return string Open Graph locale (e.g., 'en_US', 'es_ES')
     */
    private static function mapLocaleToOGLocale(string $locale): string {
        $localeMap = [
            'en' => 'en_US',
            'es' => 'es_ES',
            'pt' => 'pt_BR',
        ];
        return $localeMap[$locale] ?? 'en_US';
    }

    /**
     * Generates hreflang tags for multi-language support
     *
     * @param string $baseUrl Base URL
     * @param string $path Current path
     * @return array<string, string> Array of locale => URL mappings
     */
    public static function generateHreflangTags(
        string $baseUrl,
        string $path = '/'
    ): array {
        $locales = ['en', 'es', 'pt'];
        $hreflang = [];
        foreach ($locales as $locale) {
            $url = rtrim($baseUrl, '/') . $path;
            $hreflang[$locale] = $url . (strpos(
                $url,
                '?'
            ) !== false ? '&' : '?') . 'lang=' . $locale;
        }
        return $hreflang;
    }
}
