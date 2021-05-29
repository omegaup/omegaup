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
}
