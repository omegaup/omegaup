<?php

require_once 'libs/ApiUtils.php';

/**
 * TagController
 */
class TagController extends Controller {
    public static function normalize($name) {
        $name = ApiUtils::RemoveAccents(trim($name));
        $name = preg_replace('/[^a-z0-9]/', '-', strtolower($name));
        $name = preg_replace('/--+/', '-', $name);

        return $name;
    }

    /**
     * Gets a list of tags
     *
     * @param Request $r
     */
    public function apiList(Request $r) {
        self::authenticateRequest($r);

        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new InvalidParameterException('parameterEmpty', 'query');
        }

        try {
            $tags = TagsDAO::FindByName($r[$param]);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = array();
        foreach ($tags as $tag) {
            $entry = array('name' => $tag->name);
            array_push($response, $entry);
        }

        return $response;
    }
}
