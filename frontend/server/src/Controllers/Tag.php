<?php

 namespace OmegaUp\Controllers;

/**
 * TagController
 */
class Tag extends \OmegaUp\Controllers\Controller {
    public static function normalize(string $name): string {
        $name = \OmegaUp\ApiUtils::removeAccents(trim($name));
        $name = preg_replace('/[^a-z0-9]/', '-', strtolower($name));
        $name = preg_replace('/--+/', '-', $name);

        return $name;
    }

    /**
     * Gets a list of tags
     *
     * @omegaup-request-param mixed $query
     * @omegaup-request-param mixed $term
     *
     * @return list<array{name: string}>
     */
    public static function apiList(\OmegaUp\Request $r) {
        $param = '';
        if (is_string($r['term'])) {
            $param = $r['term'];
        } elseif (is_string($r['query'])) {
            $param = $r['query'];
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'query'
            );
        }

        $response = [];
        foreach (\OmegaUp\DAO\Tags::findByName($param) as $tag) {
            $response[] = [
                'name' => strval($tag->name),
            ];
        }
        return $response;
    }

    /**
     * Returns the list of all tags beginning with 'problemLevel'
     *
     * @return list<string>
     */
    public static function getLevelTags() {
        return \OmegaUp\DAO\Tags::findPublicTagsByPrefix(
            'problemLevel'
        );
    }

    /**
     * Returns the list of all tags beginning with 'problemTag'
     *
     * @return list<string>
     */
    public static function getPublicTags() {
        return \OmegaUp\DAO\Tags::findPublicTagsByPrefix(
            'problemTag'
        );
    }

    /**
     * Return most frequent public tags of a certain level
     *
     * @return list<array{alias: string, total: int}>
     */
    public static function getPublicQualityTagsByLevel(
        string $problemLevel
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::TAGS_LIST,
            "level-{$problemLevel}",
            fn () => \OmegaUp\DAO\Tags::getPublicQualityTagsByLevel(
                $problemLevel
            ),
            APC_USER_CACHE_SESSION_TIMEOUT
        );
    }

    /**
     * Return most frequent public tags of a certain level
     *
     * @return list<array{alias: string, total: int}>
     */
    public static function getFrequentQualityTagsByLevel(
        string $problemLevel,
        int $rows
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::TAGS_LIST,
            "level-{$problemLevel}-{$rows}",
            fn () => \OmegaUp\DAO\Tags::getFrequentQualityTagsByLevel(
                $problemLevel,
                $rows
            ),
            APC_USER_CACHE_SESSION_TIMEOUT
        );
    }

    /**
     * Return most frequent public tags of a certain level
     *
     * @return array{frequent_tags: list<array{alias: string, total: int}>}
     *
     * @omegaup-request-param string $problemLevel
     * @omegaup-request-param int $rows
     */
    public static function apiFrequentTags(\OmegaUp\Request $r): array {
        $param = $r->ensureString(
            'problemLevel',
            fn (string $problemAlias) => \OmegaUp\Validators::alias(
                $problemAlias
            )
        );

        return [
            'frequent_tags' => self::getFrequentQualityTagsByLevel(
                $param
            ),
        ];
    }
}
