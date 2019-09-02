<?php

/**
 * Description of ProblemsFactory
 *
 * @author heduenas
 */
class QualityNominationFactory {
    public static $reviewers = [];

    public static function initQualityReviewers() {
        $qualityReviewerGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::QUALITY_REVIEWER_GROUP_ALIAS
        );
        for ($i = 0; $i < 5; $i++) {
            $reviewer = UserFactory::createUser();
            $identity = \OmegaUp\DAO\Identities::getByPK($reviewer->main_identity_id);
            \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $qualityReviewerGroup->group_id,
                'identity_id' => $identity->identity_id,
            ]));
            self::$reviewers[] = $reviewer;
        }
    }

    public static function initTags() {
        \OmegaUp\DAO\Tags::create(new \OmegaUp\DAO\VO\Tags(['name' => 'dp']));
        \OmegaUp\DAO\Tags::create(new \OmegaUp\DAO\VO\Tags(['name' => 'math']));
        \OmegaUp\DAO\Tags::create(new \OmegaUp\DAO\VO\Tags(['name' => 'matrices']));
        \OmegaUp\DAO\Tags::create(new \OmegaUp\DAO\VO\Tags(['name' => 'greedy']));
        \OmegaUp\DAO\Tags::create(new \OmegaUp\DAO\VO\Tags(['name' => 'geometry']));
        \OmegaUp\DAO\Tags::create(new \OmegaUp\DAO\VO\Tags(['name' => 'search']));
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
        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'nomination' => $type,
            'contents' => $contents,
        ]);
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate($request);
        return $qualitynomination;
    }
}
