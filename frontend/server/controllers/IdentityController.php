<?php

/**
 *  IdentityController
 *
 * @author juan.pablo
 */
class IdentityController extends Controller {
    public static function createInstanceOfIdentity(Users $user) {
        $identity = IdentitiesDAO::FindByUserId($user->user_id);
        return new Identities([
            'identity_id' => $identity->identity_id,
            'username' => $user->username,
            'password' => $user->password,
            'name' => $user->name,
            'main_user_id' => $user->user_id,
            'language_id' => $user->language_id,
            'country_id' => $user->country_id,
            'state_id' => $user->state_id,
            'school_id' => $user->school_id
        ]);
    }
}
