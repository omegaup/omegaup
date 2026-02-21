<?php

namespace OmegaUp\DAO;

/**
 * IdentitiesSchools Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Identities_Schools}.
 * @access public
 */
class IdentitiesSchools extends \OmegaUp\DAO\Base\IdentitiesSchools {
    public static function createNewSchoolForIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        int $schoolId,
        ?string $graduationDate
    ): \OmegaUp\DAO\VO\IdentitiesSchools {
        // First get the current IdentitySchool and update its end_time
        if (!is_null($identity->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identity->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $identitySchool->end_time = new \OmegaUp\Timestamp(
                    \OmegaUp\Time::get()
                );
                \OmegaUp\DAO\IdentitiesSchools::update($identitySchool);
            }
        }

        $newIdentitySchool = new \OmegaUp\DAO\VO\IdentitiesSchools([
            'identity_id' => $identity->identity_id,
            'school_id' => $schoolId,
        ]);

        if (!is_null($graduationDate)) {
            $newIdentitySchool->graduation_date = $graduationDate;
        }

        // Create new IdentitySchool and save it
        \OmegaUp\DAO\IdentitiesSchools::create($newIdentitySchool);
        return $newIdentitySchool;
    }

    public static function getByIdentityAndSchoolId(
        \OmegaUp\DAO\VO\Identities $identity,
        int $schoolId
    ): ?\OmegaUp\DAO\VO\IdentitiesSchools {
        if (is_null($identity->identity_id)) {
            return null;
        }
        $sql = 'SELECT
                    iss.identity_school_id,
                    iss.identity_id,
                    iss.school_id,
                    iss.graduation_date,
                    iss.creation_time,
                    iss.end_time
                FROM
                    Identities_Schools iss
                WHERE
                    iss.identity_id = ?
                    AND iss.school_id = ?
                LIMIT 1';
        $args = [$identity->identity_id, $schoolId];

        /** @var array{creation_time: \OmegaUp\Timestamp, end_time: \OmegaUp\Timestamp|null, graduation_date: null|string, identity_id: int, identity_school_id: int, school_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $args);

        if (is_null($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\IdentitiesSchools($rs);
    }
    public static function getByIdentity(
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        if (is_null($identity->identity_id)) {
            return [];
        }

        $sql = 'SELECT
                iss.identity_school_id,
                iss.graduation_date,
                s.name AS school_name
            FROM
                Identities_Schools iss
            INNER JOIN
                Schools s ON s.school_id = iss.school_id
            WHERE
                iss.identity_id = ?
            ORDER BY
                iss.creation_time DESC';

        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$identity->identity_id]
        );
    }
}
