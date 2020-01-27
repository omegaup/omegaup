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
}
