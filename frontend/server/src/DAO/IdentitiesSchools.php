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
        if ($identity->current_identity_school_id !== null) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identity->current_identity_school_id
            );
            if ($identitySchool !== null) {
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

        if ($graduationDate !== null) {
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
        if ($identity->identity_id === null) {
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

        if ($rs === null) {
            return null;
        }

        return new \OmegaUp\DAO\VO\IdentitiesSchools($rs);
    }
}
