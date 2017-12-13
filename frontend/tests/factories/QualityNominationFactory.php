<?php

/**
 * Description of ProblemsFactory
 *
 * @author heduenas
 */
class QualityNominationFactory {
    public static $reviewers = [];

    public static function initQualityReviewers() {
        $qualityReviewerGroup = GroupsDAO::FindByAlias(
            Authorization::QUALITY_REVIEWER_GROUP_ALIAS
        );
        for ($i = 0; $i < 5; $i++) {
            $reviewer = UserFactory::createUser();
            GroupsUsersDAO::save(new GroupsUsers([
                'group_id' => $qualityReviewerGroup->group_id,
                'user_id' => $reviewer->user_id,
                'role_id' => Authorization::ADMIN_ROLE,
            ]));
            self::$reviewers[] = $reviewer;
        }
    }

    public static function createSuggestion($login, $problemAlias, $difficulty, $quality, $tags) {
        $contents = [];
        if ($difficulty != null) {
            $contents['difficulty'] = $difficulty;
        }
        if ($quality != null) {
            $contents['quality'] = $quality;
        }
        if ($tags != null) {
            $contents['tags'] = $tags;
        }
        $contentsJson = json_encode($contents);
        return self::createQualityNomination($login, $problemAlias, 'suggestion', $contentsJson);
    }

    public static function createQualityNomination($login, $problemAlias, $type, $contents) {
        $request = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'nomination' => $type,
            'contents' => $contents,
        ]);
        $qualitynomination = QualityNominationController::apiCreate($request);
        return $qualitynomination;
    }
}
