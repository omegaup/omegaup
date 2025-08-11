<?php

namespace OmegaUp\Test\Factories;

class QualityNomination {
    /** @var list<\OmegaUp\DAO\VO\Identities> */
    public static $reviewers = [];

    public static function initQualityReviewers(): void {
        $qualityReviewerGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::QUALITY_REVIEWER_GROUP_ALIAS
        );
        if (is_null($qualityReviewerGroup)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }
        for ($i = 0; $i < 5; $i++) {
            [
                'identity' => $identity,
            ] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => intval($qualityReviewerGroup->group_id),
                'identity_id' => $identity->identity_id,
            ]));
            self::$reviewers[] = $identity;
        }
    }

    public static function initTopicTags(): void {
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicDynamicProgramming']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicGraphTheory']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicGreedy']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicBinarySearch']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicMath']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicMatrices']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicGeometry']
            )
        );
        \OmegaUp\DAO\Tags::create(
            new \OmegaUp\DAO\VO\Tags(
                ['name' => 'problemTopicSorting']
            )
        );
    }

    /**
     * @param string $problemAlias
     * @param null|int $difficulty
     * @param null|int $quality
     * @param null|string[] $tags
     * @param bool $beforeAC
     *
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
     * @param array{difficulty?: int, quality?: int, tags?: string[], before_AC?: boolean} $contents
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
