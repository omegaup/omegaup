<?php

 namespace OmegaUp\Controllers;

/**
 * AuthorizationController
 */
class Authorization extends \OmegaUp\Controllers\Controller {
    /**
     * @return array{has_solved: bool, is_admin: bool, can_view: bool, can_edit: bool}
     */
    public static function apiProblem(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateValidUsername(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['token'],
            'token'
        );

        // This is not supposed to be called by end-users, but by the
        // gitserver. Regular sessions cannot be used since they
        // expire, so use a pre-shared secret to authenticate that
        // grants admin-level privileges just for this call.
        if ($r['token'] !== OMEGAUP_GITSERVER_SECRET_TOKEN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $resolvedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['username']
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $isAdmin = \OmegaUp\Authorization::isProblemAdmin(
            $resolvedIdentity,
            $problem
        );
        $canEdit = $isAdmin || \OmegaUp\Authorization::canEditProblem(
            $resolvedIdentity,
            $problem
        );
        return [
            'has_solved' => \OmegaUp\DAO\Problems::isProblemSolved(
                $problem,
                intval($resolvedIdentity->identity_id)
            ),
            'is_admin' => $isAdmin,
            'can_view' => $canEdit || \OmegaUp\DAO\Problems::isVisible(
                $problem
            ),
            'can_edit' => $canEdit,
        ];
    }
}
