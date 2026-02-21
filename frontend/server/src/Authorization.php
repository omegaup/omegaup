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

    /**
     * Cache for system group for certificate generators
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_certificateGeneratorGroup = null;

    /**
     * Cache for system group for teaching assistants.
     * @var null|\OmegaUp\DAO\VO\Groups
     */
    private static $_teachingAssistantGroup = null;

    // Administrator for an ACL.
    const ADMIN_ROLE = 1;

    // Allowed to submit to a problemset.
    const CONTESTANT_ROLE = 2;

    // Problem reviewer.
    const REVIEWER_ROLE = 3;

    // Mentor.
    const MENTOR_ROLE = 5;

    // Support.
    const SUPPORT_ROLE = 6;

    // Identity creator.
    const IDENTITY_CREATOR_ROLE = 7;

    // Certificate generator.
    const CERTIFICATE_GENERATOR_ROLE = 8;

    // Teaching assistant.
    const TEACHING_ASSISTANT_ROLE = 9;

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

    // Group for certificate generators.
    const CERTIFICATE_GENERATOR_GROUP_ALIAS = 'omegaup:certificate-generator';

    // Group for teaching assistants.
    const TEACHING_ASSISTANT_GROUP_ALIAS = 'omegaup:teaching-assistant';

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
            if (!is_null($problemset)) {
                if (self::isAdmin($identity, $problemset)) {
                    return true;
                }
                if ($problemset->type === 'Assignment') {
                    $course = \OmegaUp\DAO\Courses::getByProblemsetId(
                        $problemset
                    );
                    if (
                        !is_null($course) && self::isTeachingAssistant(
                            $identity,
                            $course
                        )
                    ) {
                        return true;
                    }
                }
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

        if (self::isAdmin($identity, $problemset)) {
            return true;
        }

        $course = \OmegaUp\DAO\Courses::getByProblemsetId($problemset);
        return (!is_null($course) && self::isTeachingAssistant(
            $identity,
            $course
        ));
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

        if (self::isAdmin($identity, $problemset)) {
            return true;
        }

        $course = \OmegaUp\DAO\Courses::getByProblemsetId($problemset);
        return (self::isOwner($identity, $problem->acl_id)
                || (!is_null($course) && self::isTeachingAssistant(
                    $identity,
                    $course
                )));
    }

    public static function isUnderThirteenUser(\OmegaUp\DAO\VO\Users $user): bool {
        // This is mostly for users who hasn't give us their birth day
        if (is_null($user->birth_date)) {
            return false;
        }
        // User's age is U13? $user->birth_date - current date then return true, otherwise return false
        return strtotime($user->birth_date) >= strtotime(
            '-13 year',
            \OmegaUp\Time::get()
        );
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
     * Returns whether the identity can edit or remove the contest. Only contest
     * admins can do so.
     */
    public static function canEditContest(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Contests $contest
    ): bool {
        if (is_null($contest->acl_id)) {
            return false;
        }
        return self::isContestAdmin($identity, $contest);
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

    public static function canGenerateCertificates(\OmegaUp\DAO\VO\Identities $identity): bool {
        return self::isCertificateGenerator($identity);
    }

    public static function isAdminOrTeachingAssistant(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course
    ): bool {
        return(
            self::isTeachingAssistant($identity, $course) ||
            self::isCourseAdmin($identity, $course)
        );
    }

    public static function canViewCourse(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course,
        \OmegaUp\DAO\VO\Groups $group
    ): bool {
        if (
            !self::isCourseAdmin($identity, $course) &&
            !self::isGroupMember($identity, $group) &&
            !self::isTeachingAssistant($identity, $course)
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
                intval($identity->identity_id),
                $acl_id,
                $role_id
            ) ||
            \OmegaUp\DAO\UserRoles::hasRole(
                intval($identity->identity_id),
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
     * the coder and school of the month
     */
    public static function canChooseCoderOrSchool(int $currentTimestamp): bool {
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

    public static function isCertificateGenerator(\OmegaUp\DAO\VO\Identities $identity): bool {
        if (is_null(self::$_certificateGeneratorGroup)) {
            $certificateGeneratorGroup = \OmegaUp\DAO\Groups::findByAlias(
                self::CERTIFICATE_GENERATOR_GROUP_ALIAS
            );
            if (
                is_null($certificateGeneratorGroup)
                || is_null($certificateGeneratorGroup->acl_id)
            ) {
                return false;
            }
            self::$_certificateGeneratorGroup = $certificateGeneratorGroup;
        } else {
            $certificateGeneratorGroup = self::$_certificateGeneratorGroup;
        }
        /** @var int $certificateGeneratorGroup->acl_id */
        return self::isGroupMember(
            $identity,
            $certificateGeneratorGroup
        ) || self::hasRole(
            $identity,
            $certificateGeneratorGroup->acl_id,
            self::CERTIFICATE_GENERATOR_ROLE
        );
    }

    public static function isTeachingAssistant(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Courses $course
    ): bool {
        if (is_null($course->acl_id)) {
            return false;
        }
        return self::hasRole(
            $identity,
            $course->acl_id,
            self::TEACHING_ASSISTANT_ROLE
        );
    }

    /**
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param list<\OmegaUp\DAO\VO\Groups> $groups
     */
    public static function isMemberOfAnyGroup(
        $identity,
        $groups = []
    ): bool {
        if (self::isSystemAdmin($identity)) {
            return true;
        }
        if (empty($groups) || is_null($identity->user_id)) {
            return false;
        }
        return \OmegaUp\DAO\GroupsIdentities::existsByGroupId(
            $identity,
            $groups
        );
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
        if (is_null(self::$_groupIdentityCreator->acl_id)) {
            return false;
        }
        return self::isGroupMember(
            $identity,
            self::$_groupIdentityCreator
        ) || self::hasRole(
            $identity,
            self::$_groupIdentityCreator->acl_id,
            self::IDENTITY_CREATOR_ROLE
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
        if (is_null(self::$_supportGroup->acl_id)) {
            return false;
        }
        return self::isGroupMember(
            $identity,
            self::$_supportGroup
        ) || self::hasRole(
            $identity,
            self::$_supportGroup->acl_id,
            self::SUPPORT_ROLE
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

    public static function isTeamGroupAdmin(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\TeamGroups $teamGroup
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isAdmin($identity, $teamGroup);
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
        return \OmegaUp\DAO\GroupsIdentities::existsByPK(
            $group->group_id,
            $identity->identity_id
        );
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
        self::$_certificateGeneratorGroup = null;
        self::$_teachingAssistantGroup = null;
    }

    public static function canSubmitToProblemset(
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Problemsets $problemset
    ): bool {
        if (is_null($problemset) || is_null($problemset->acl_id)) {
            return false;
        }
        return self::isAdmin($identity, $problemset) ||
            \OmegaUp\DAO\GroupRoles::isContestant(
                intval($identity->identity_id),
                $problemset->acl_id
            ) ||
            \OmegaUp\DAO\TeamsGroupRoles::isContestant(
                intval($identity->identity_id),
                $problemset->acl_id
            );
    }

    public static function canEditProblemset(
        \OmegaUp\DAO\VO\Identities $identity,
        int $problemsetId
    ): bool {
        $problemset = \OmegaUp\DAO\Problemsets::getByPK($problemsetId);
        if (is_null($problemset) || is_null($problemset->acl_id)) {
            return false;
        }
        return self::isAdmin($identity, $problemset);
    }

    public static function canCreatePublicCourse(\OmegaUp\DAO\VO\Identities $identity): bool {
        return self::isCourseCurator($identity);
    }
}
