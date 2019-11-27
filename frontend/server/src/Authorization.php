<?php

namespace OmegaUp;

/**
 * Contains static function calls that return true if an identity is authorized
 * to perform certain action.
 */
class Authorization {
    /**
     * Cache for the system admin privilege. This is used sort of frequently.
     * @var null|bool
     */
    private static $_isSystemAdmin = null;

    /**
     * Cache for system group for quality reviewers.
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_qualityReviewerGroup = null;

    /**
     * Cache for system group for course curators
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_courseCuratorGroup = null;

    /**
     * Cache for system group for mentors
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_mentorGroup = null;

    /**
     * Cache for system group for support team members
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_supportGroup = null;

    /**
     * Cache for system group identity creators
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_groupIdentityCreator = null;

    // Administrator for an ACL.
    const ADMIN_ROLE = 1;

    // Allowed to submit to a problemset.
    const CONTESTANT_ROLE = 2;

    // Problem reviewer.
    const REVIEWER_ROLE = 3;

    // Interviewer.
    const INTERVIEWER_ROLE = 4;

    // Mentor.
    const MENTOR_ROLE = 5;

    // Identity creator.
    const IDENTITY_CREATOR_ROLE = 7;

    // System-level ACL.
    const SYSTEM_ACL = 1;

    // Group for quality reviewers.
    const QUALITY_REVIEWER_GROUP_ALIAS = 'omegaup:quality-reviewer';

    // Group for course curators.
    const COURSE_CURATOR_GROUP_ALIAS = 'omegaup:course-curator';

    // Group for mentors.
    const MENTOR_GROUP_ALIAS = 'omegaup:mentor';

    // Group for support team members.
    const SUPPORT_GROUP_ALIAS = 'omegaup:support';

    // Group identities creators.
    const IDENTITY_CREATOR_GROUP_ALIAS = 'omegaup:group-identity-creator';

    public static function canViewSubmission(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Submissions $submission
    ): bool {
        return (
            $submission->identity_id === $identity->identity_id  ||
            self::canEditSubmission($identity, $submission)
        );
    }

    public static function canEditSubmission(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Submissions $submission
    ): bool {
        if (is_null($submission->problem_id)) {
            return false;
        }
        $problem = \OmegaUp\DAO\Problems::getByPK($submission->problem_id);
        if (is_null($problem)) {
            return false;
        }
        if ($problem->deprecated) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'problemDeprecated'
            );
        }

        if (!is_null($submission->problemset_id)) {
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                $submission->problemset_id
            );
            if (
                !is_null($problemset) &&
                self::isAdmin(
                    $identity,
                    $problemset
                )
            ) {
                return true;
            }
        }

        return self::isProblemAdmin($identity, $problem);
    }

    public static function canViewClarification(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Clarifications $clarification
    ): bool {
        if (is_null($clarification->problemset_id)) {
            return false;
        }

        // TODO Temporary until isAdmin function is fixed
        $identity_id = $identity->identity_id;
        if ($clarification->author_id === $identity_id) {
            return true;
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $clarification->problemset_id
        );
        if (is_null($problemset)) {
            return false;
        }

        return self::isAdmin($identity, $problemset);
    }

    public static function canEditClarification(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Clarifications $clarification
    ): bool {
        if (
            is_null($clarification->problemset_id)
            || is_null($clarification->problem_id)
        ) {
            return false;
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $clarification->problemset_id
        );
        if (is_null($problemset)) {
            return false;
        }

        $problem = \OmegaUp\DAO\Problems::getByPK($clarification->problem_id);
        if (is_null($problem) || is_null($problem->acl_id)) {
            return false;
        }

        return (self::isOwner($identity, $problem->acl_id)
                || self::isAdmin($identity, $problemset));
    }

    /**
     * Returns whether the identity can edit the problem. Only problem admins and
     * reviewers can do so.
     */
    public static function canEditProblem(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        if (is_null($problem->acl_id)) {
            return false;
        }
        return self::isProblemAdmin($identity, $problem) ||
            self::isQualityReviewer($identity) ||
            self::hasRole(
                $identity,
                $problem->acl_id,
                self::REVIEWER_ROLE
            );
    }

    /**
     * Returns whether the identity can view the problem solution. Only problem
     * admins and identities that have solved the problem can do so.
     */
    public static function canViewProblemSolution(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        if (is_null($identity->identity_id)) {
            return false;
        }
        return self::canEditProblem($identity, $problem) ||
            \OmegaUp\DAO\Problems::isProblemSolved(
                $problem,
                $identity->identity_id
            ) ||
            \OmegaUp\DAO\ProblemsForfeited::isProblemForfeited(
                $problem,
                $identity
            );
    }

    public static function canViewEmail(\OmegaUp\DAO\VO\Identities $identity): bool {
        return self::isMentor($identity);
    }

    public static function canCreateGroupIdentities(\OmegaUp\DAO\VO\Identities $identity): bool {
        return self::isGroupIdentityCreator($identity);
    }

    public static function canViewCourse(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ): bool {
        if (
            !self::isCourseAdmin($identity, $course) &&
            !self::isGroupMember($identity, $group)
        ) {
            return false;
        }

        return true;
    }

    public static function isAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        object $entity
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        if (is_null($entity->acl_id)) {
            return false;
        }
        /** @var int $entity->acl_id */
        return self::isOwner($identity, $entity->acl_id) ||
            self::hasRole(
                $identity,
                $entity->acl_id,
                self::ADMIN_ROLE
            );
    }

    public static function isContestAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Contests $contest
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $contest);
    }

    public static function isInterviewAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Interviews $interview
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $interview);
    }

    public static function isProblemAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $problem);
    }

    public static function hasRole(
        \OmegaUp\DAO\VO\Identities $identity,
        int $acl_id,
        int $role_id
    ): bool {
        return (
            \OmegaUp\DAO\GroupRoles::hasRole(
                $identity->identity_id,
                $acl_id,
                $role_id
            ) ||
            \OmegaUp\DAO\UserRoles::hasRole(
                $identity->identity_id,
                $acl_id,
                $role_id
            )
        );
    }

    public static function isSystemAdmin(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_isSystemAdmin)) {
            self::$_isSystemAdmin = self::hasRole(
                $identity,
                self::SYSTEM_ACL,
                self::ADMIN_ROLE
            );
        }
        return self::$_isSystemAdmin;
    }

    public static function isQualityReviewer(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_qualityReviewerGroup)) {
            self::$_qualityReviewerGroup = \OmegaUp\DAO\Groups::findByAlias(
                self::QUALITY_REVIEWER_GROUP_ALIAS
            );
            if (is_null(self::$_qualityReviewerGroup)) {
                return false;
            }
        }
        return self::isGroupMember(
            $identity,
            self::$_qualityReviewerGroup
        );
    }

    public static function isMentor(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_mentorGroup)) {
            self::$_mentorGroup = \OmegaUp\DAO\Groups::findByAlias(
                self::MENTOR_GROUP_ALIAS
            );
            if (is_null(self::$_mentorGroup)) {
                return false;
            }
        }
        return self::isGroupMember(
            $identity,
            self::$_mentorGroup
        );
    }

    /**
     * Only last two days of the month mentor is available to choose
     * the coder of the month
     */
    public static function canChooseCoder(int $currentTimestamp): bool {
        $today = date('Y-m-d', $currentTimestamp);
        $lastDayOfMonth = intval(date('t', $currentTimestamp));
        $availableDateToChooseCoder = [];
        $availableDateToChooseCoder[] = date(
            'Y-m-',
            $currentTimestamp
        ) . $lastDayOfMonth;
        $availableDateToChooseCoder[] = date(
            'Y-m-',
            $currentTimestamp
        ) . ($lastDayOfMonth - 1);
        return in_array($today, $availableDateToChooseCoder);
    }

    public static function isGroupIdentityCreator(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_groupIdentityCreator)) {
            self::$_groupIdentityCreator = \OmegaUp\DAO\Groups::findByAlias(
                self::IDENTITY_CREATOR_GROUP_ALIAS
            );
            if (is_null(self::$_groupIdentityCreator)) {
                return false;
            }
        }
        return self::isGroupMember(
            $identity,
            self::$_groupIdentityCreator
        );
    }

    public static function isSupportTeamMember(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_supportGroup)) {
            self::$_supportGroup = \OmegaUp\DAO\Groups::findByAlias(
                self::SUPPORT_GROUP_ALIAS
            );
            if (is_null(self::$_supportGroup)) {
                return false;
            }
        }
        return self::isGroupMember(
            $identity,
            self::$_supportGroup
        );
    }

    public static function isGroupAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Groups $group
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $group);
    }

    private static function isOwner(
        \OmegaUp\DAO\VO\Identities $identity,
        int $aclId
    ): bool {
        $acl = \OmegaUp\DAO\ACLs::getByPK($aclId);
        if (is_null($acl)) {
            return false;
        }
        return $acl->owner_id == $identity->user_id;
    }

    /**
     * An admin is either the group owner or a member of the admin group.
     */
    public static function isCourseAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $course);
    }

    private static function isGroupMember(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Groups $group
    ): bool {
        if (self::isSystemAdmin($identity)) {
            return true;
        }
        $groupUsers = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $group->group_id,
            $identity->identity_id
        );
        return !empty($groupUsers);
    }

    public static function isCourseCurator(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_courseCuratorGroup)) {
            self::$_courseCuratorGroup = \OmegaUp\DAO\Groups::findByAlias(
                self::COURSE_CURATOR_GROUP_ALIAS
            );
            if (is_null(self::$_courseCuratorGroup)) {
                return false;
            }
        }
        return self::isGroupMember(
            $identity,
            self::$_courseCuratorGroup
        );
    }

    public static function clearCacheForTesting(): void {
        self::$_isSystemAdmin = null;
        self::$_qualityReviewerGroup = null;
        self::$_mentorGroup = null;
        self::$_supportGroup = null;
        self::$_groupIdentityCreator = null;
    }

    public static function canSubmitToProblemset(
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Problemsets $problemset
    ): bool {
        if (is_null($problemset)) {
            return false;
        }
        return self::isAdmin($identity, $problemset) ||
            \OmegaUp\DAO\GroupRoles::isContestant(
                $identity->identity_id,
                $problemset->acl_id
            );
    }

    public static function canCreatePublicCourse(\OmegaUp\DAO\VO\Identities $identity): bool {
        return self::isCourseCurator($identity);
    }
}
