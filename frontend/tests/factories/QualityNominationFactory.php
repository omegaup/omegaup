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
            ['user' => $reviewer, 'identity' => $identity] = UserFactory::createUser();
            if (is_null($reviewer->main_identity_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
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
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'matrices']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'greedy']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'geometry']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'search']
            )
        );
    }

    /**
     * @param ScopedLoginToken $login
     * @param string $problemAlias
     * @param null|float $difficulty
     * @param null|float $quality
     * @param null|string[] $tags
     * @param bool $beforeAC
     * @return array{status: string, qualitynomination_id: int}
     */
    public static function createSuggestion(
        ScopedLoginToken $login,
        string $problemAlias,
        ?float $difficulty,
        ?float $quality,
        ?array $tags,
        bool $beforeAC
    ): array {
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
        if ($beforeAC) {
            $contents['before_ac'] = true;
        }
        return self::createQualityNomination(
            $login,
            $problemAlias,
            'suggestion',
            $contents
        );
    }

    /**
     * @param array{difficulty?: float, quality?: float, tags?: string[], before_AC?: boolean} $contents
     * @return array{status: string, qualitynomination_id: int}
     */
    public static function createQualityNomination(
        ScopedLoginToken $login,
        string $problemAlias,
        string $type,
        $contents
    ) {
        $contentsJson = json_encode($contents);
        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'nomination' => $type,
            'contents' => $contentsJson,
        ]);
        /** @var array{status: string, qualitynomination_id: int} */
        $qualitynomination = \OmegaUp\Controllers\QualityNomination::apiCreate(
            $request
        );
        return $qualitynomination;
    }
}
