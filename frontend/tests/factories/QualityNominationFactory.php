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
            \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $qualityReviewerGroup->group_id,
                'identity_id' => $identity->identity_id,
            ]));
            self::$reviewers[] = $identity;
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
     * @param null|int $difficulty
     * @param null|int $quality
     * @param null|string[] $tags
     * @param bool $beforeAC
     * @return \OmegaUp\DAO\VO\QualityNominations
     */
    public static function createSuggestion(
        \OmegaUp\DAO\VO\Identities $user,
        string $problemAlias,
        ?int $difficulty,
        ?int $quality,
        ?array $tags,
        bool $beforeAC
    ): \OmegaUp\DAO\VO\QualityNominations {
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
            $user,
            $problemAlias,
            'suggestion',
            $contents
        );
    }

    /**
     * @param array{difficulty?: float, quality?: float, tags?: string[], before_AC?: boolean} $contents
     * @return \OmegaUp\DAO\VO\QualityNominations
     */
    public static function createQualityNomination(
        \OmegaUp\DAO\VO\Identities $user,
        string $problemAlias,
        string $type,
        $contents
    ): \OmegaUp\DAO\VO\QualityNominations {
        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        return \OmegaUp\Controllers\QualityNomination::createNomination(
            $problem,
            $user,
            $type,
            $contents
        );
    }
}
