<?php

require_once 'Utils.php';
require_once SERVER_PATH . '/libs/Authorization.php';


class AuthorizationTest extends PHPUnit_Framework_TestCase
{
    public function testIsSystemAdmin()
    {
        // Add admin role to DB
        $r = new Roles(array(
            "role_id" => '1',
            "name" => 'admin',
            "description" => 'lol'
        ));
        RolesDAO::save($r);
        
        // Add user to DB
        $ur = new UserRoles(array("user_id" => Utils::GetContestDirectorUserId(), "role_id" => '1'));
        UserRolesDAO::save($ur);
        
        // Check is system admin
        $this->assertTrue(Authorization::IsSystemAdmin(Utils::GetContestDirectorUserId()));
        
        // Clean DB
        UserRolesDAO::delete($ur);        
    }
}
?>
