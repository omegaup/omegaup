<?php

/**
 * TagController
 */
class TagController extends Controller {
    public static function normalize($name) {
        $name = \OmegaUp\ApiUtils::removeAccents(trim($name));
        $name = preg_replace('/[^a-z0-9]/', '-', strtolower($name));
        $name = preg_replace('/--+/', '-', $name);

        return $name;
    }

    /**
     * Gets a list of tags
     *
     * @param Request $r
     */
    public static function apiList(Request $r) {
        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', 'query');
        }

        $tags = TagsDAO::FindByName($r[$param]);

        $response = [];
        if (empty($tags)) {
            return $response;
        }
        foreach ($tags as $tag) {
            $entry = ['name' => $tag->name];
            array_push($response, $entry);
        }

        return $response;
    }
}
