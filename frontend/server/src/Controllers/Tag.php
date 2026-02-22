<?php

 namespace OmegaUp\Controllers;

/**
 * TagController
 *
 * @psalm-type TagWithProblemCount=array { name: string, problemCount: int }
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
     * @omegaup-request-param null|string $query
     * @omegaup-request-param null|string $term
     *
     * @return list<array{name: string}>
     */
    public static function apiList(\OmegaUp\Request $r) {
        $param = '';
        $term = $r->ensureOptionalString('term');
        $query = $r->ensureOptionalString('query');

        if ($term !== null) {
            $param = $term;
        } elseif ($query !== null) {
            $param = $query;
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
     * @return list<TagWithProblemCount>
     */
    public static function getPublicQualityTagsByLevel(
        string $problemLevel
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::TAGS_LIST,
            "publicquality-level-{$problemLevel}",
            fn () => \OmegaUp\DAO\Tags::getPublicQualityTagsByLevel(
                $problemLevel
            ),
            APC_USER_CACHE_SESSION_TIMEOUT
        );
    }

    /**
     * Return most frequent public tags of a certain level
     *
     * @return list<TagWithProblemCount>
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
     * @return array{frequent_tags: list<TagWithProblemCount>}
     *
     * @omegaup-request-param string $problemLevel
     * @omegaup-request-param int $rows
     */
    public static function apiFrequentTags(\OmegaUp\Request $r): array {
        $param = $r->ensureString(
            'problemLevel',
            fn (string $problemAlias) => \OmegaUp\Validators::alias(
                $problemAlias,
                maxLength: 75,
            )
        );

        $rows = $r->ensureInt(
            'rows'
        );

        return [
            'frequent_tags' => self::getFrequentQualityTagsByLevel(
                $param,
                $rows
            ),
        ];
    }
}
