<?php

namespace OmegaUp\DAO;

/**
 * IdentitiesSchools Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Identities_Schools}.
 *
 * @author carlosabcs
 * @access public
 */
class IdentitiesSchools extends \OmegaUp\DAO\Base\IdentitiesSchools {
    public static function createNewSchoolForIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        ?string $graduationDate
    ): int {
        // First get the current IdentitySchool and update its end_time
        $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getCurrentSchoolFromIdentity(
            $identity
        );
        if (!is_null($identitySchool)) {
            $identitySchool->end_time = \OmegaUp\Time::get();
            \OmegaUp\DAO\IdentitiesSchools::update($identitySchool);
        }

        $newIdentitySchool = new \OmegaUp\DAO\VO\IdentitiesSchools([
            'identity_id' => $identity->identity_id,
            'school_id' => $identity->school_id,
        ]);

        if (!is_null($graduationDate)) {
            $newIdentitySchool->graduation_date = $graduationDate;
        }

        // Create new IdentitySchool and save it
        return \OmegaUp\DAO\IdentitiesSchools::create($newIdentitySchool);
    }

    public static function getCurrentSchoolFromIdentity(
        \OmegaUp\DAO\VO\Identities $identity
    ): ?\OmegaUp\DAO\VO\IdentitiesSchools {
        // TODO: Remove this function and only use getByPK when
        // current_identity_school_id is added to Identities
        $sql = 'SELECT
                    *
                FROM
                    Identities_Schools
                WHERE
                    identity_id = ? AND end_time IS NULL
                ORDER BY
                    creation_time DESC;';
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$identity->identity_id]
        );

        if (is_null($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\IdentitiesSchools($row);
    }
}
