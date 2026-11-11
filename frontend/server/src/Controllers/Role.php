<?php

namespace OmegaUp\Controllers;

/**
 * @psalm-type RoleSummary=array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}
 * @psalm-type RolePermission=array{permission_id: int, name: string, description: string}
 * @psalm-type RoleDetails=array{role: RoleSummary, permissions: list<RolePermission>}
 * @psalm-type RoleAssignment=array{name: string, acl_id: int, resource_type: string, resource_alias: null|string}
 * @psalm-type RoleAssignmentsResponse=array{assignments: list<RoleAssignment>, total_rows: int}
 */
class Role extends \OmegaUp\Controllers\Controller {
    const PAGE_SIZE = 20;
    const MAX_PAGE_SIZE = 100;

    private static function ensureCanConsultRoles(
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        if (
            \OmegaUp\Authorization::isSystemAdmin($identity) ||
            \OmegaUp\Authorization::isSupportTeamMember($identity)
        ) {
            return;
        }
        throw new \OmegaUp\Exceptions\ForbiddenAccessException();
    }

    /**
     * @return array{roles: list<RoleSummary>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::ensureCanConsultRoles($r->identity);

        return ['roles' => \OmegaUp\DAO\Roles::getAllWithCounts()];
    }

    /**
     * @return RoleDetails
     *
     * @omegaup-request-param string $role
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::ensureCanConsultRoles($r->identity);

        $roleName = $r->ensureString('role');
        $roleVO = \OmegaUp\DAO\Roles::getByName($roleName);

        foreach (\OmegaUp\DAO\Roles::getAllWithCounts() as $summary) {
            if ($summary['role_id'] === $roleVO->role_id) {
                return [
                    'role' => $summary,
                    'permissions' => \OmegaUp\DAO\Roles::getPermissions(
                        $summary['role_id']
                    ),
                ];
            }
        }
        throw new \OmegaUp\Exceptions\InvalidParameterException(
            'parameterNotFound',
            'role'
        );
    }

    /**
     * @return RoleAssignmentsResponse
     *
     * @omegaup-request-param null|int $acl_id
     * @omegaup-request-param string $assignment_type
     * @omegaup-request-param int $page
     * @omegaup-request-param int $page_size
     * @omegaup-request-param null|string $query
     * @omegaup-request-param string $role
     */
    public static function apiAssignments(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::ensureCanConsultRoles($r->identity);

        $roleVO = \OmegaUp\DAO\Roles::getByName($r->ensureString('role'));
        $roleId = intval($roleVO->role_id);
        $type = $r->ensureString('assignment_type');
        if (!in_array($type, ['user', 'group'], true)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'assignment_type'
            );
        }
        $aclId = $r->ensureOptionalInt('acl_id');
        $query = substr($r->ensureOptionalString('query') ?? '', 0, 256);
        $page = max(1, $r->ensureOptionalInt('page') ?? 1);
        $pageSize = min(
            self::MAX_PAGE_SIZE,
            max(1, $r->ensureOptionalInt('page_size') ?? self::PAGE_SIZE)
        );

        if ($type === 'user') {
            return \OmegaUp\DAO\UserRoles::getAssignmentsByRole(
                $roleId,
                $aclId,
                $query,
                $page,
                $pageSize
            );
        }
        return \OmegaUp\DAO\GroupRoles::getAssignmentsByRole(
            $roleId,
            $aclId,
            $query,
            $page,
            $pageSize
        );
    }
}
