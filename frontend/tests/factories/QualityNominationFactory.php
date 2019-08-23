<?php

/**
 * Description of ProblemsFactory
 *
 * @author heduenas
 */
class QualityNominationFactory {
    public static $reviewers = [];

    public static function initQualityReviewers() {
        $qualityReviewerGroup = GroupsDAO::findByAlias(
            Authorization::QUALITY_REVIEWER_GROUP_ALIAS
        );
        for ($i = 0; $i < 5; $i++) {
            $reviewer = UserFactory::createUser();
            $identity = IdentitiesDAO::getByPK($reviewer->main_identity_id);
            GroupsIdentitiesDAO::create(new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $qualityReviewerGroup->group_id,
                'identity_id' => $identity->identity_id,
            ]));
            self::$reviewers[] = $reviewer;
        }
    }

    public static function initTags() {
        TagsDAO::create(new \OmegaUp\DAO\VO\Tags(['name' => 'dp']));
        TagsDAO::create(new \OmegaUp\DAO\VO\Tags(['name' => 'math']));
        TagsDAO::create(new \OmegaUp\DAO\VO\Tags(['name' => 'matrices']));
        TagsDAO::create(new \OmegaUp\DAO\VO\Tags(['name' => 'greedy']));
        TagsDAO::create(new \OmegaUp\DAO\VO\Tags(['name' => 'geometry']));
        TagsDAO::create(new \OmegaUp\DAO\VO\Tags(['name' => 'search']));
    }

    public static function createSuggestion($login, $problemAlias, $difficulty, $quality, $tags) {
        $contents = [];
        if (!is_null($difficulty)) {
            $contents['difficulty'] = $difficulty;
        }
        if (!is_null($quality)) {
            $contents['quality'] = $quality;
        }
        if (!is_null($tags)) {
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
