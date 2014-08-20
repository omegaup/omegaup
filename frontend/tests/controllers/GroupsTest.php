<?php

/**
 * CreateContestTest
 *
 * @author joemmanuel
 */

class GroupsTest extends OmegaupTestCase {
	
	/**
	 * Basic create group test
	 */
	public function testCreateGroup() {
		$owner = UserFactory::createUser();
		$name = Utils::CreateRandomString();
		$description = Utils::CreateRandomString();
		
		$response = GroupController::apiCreate(new Request(array(
			"auth_token" => self::login($owner),
			"name" => $name,
			"description" => $description
		)));
		
		$this->assertEquals("ok", $response["status"]);
		
		$groups = GroupsDAO::search(new Groups(array(
			"name" => $name
		)));
		$group = $groups[0];
		$this->assertNotNull($group);
		$this->assertEquals($description, $group->getDescription());
		$this->assertEquals($owner->getUserId(), $group->getOwnerId());
	}
	
	/**
	 * Add user to group
	 */
	public function testAddUserToGroup() {		
		$group = GroupsFactory::createGroup();
		$user = UserFactory::createUser();
						
		$response = GroupController::apiAddUser(new Request(array(
			"auth_token" => self::login($group["owner"]),
			"username" => $user->username,
			"group_id" => $group["group"]->group_id
		)));
		$this->assertEquals("ok", $response["status"]);
		
		$group_users = GroupsUsersDAO::getByPK($group["group"]->group_id, $user->user_id);
		$this->assertNotNull($group_users);
	}
	
	/**
	 * Add user to group
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testAddUserToGroupNotOwned() {		
		$group = GroupsFactory::createGroup();
		$user = UserFactory::createUser();
		$userCalling = UserFactory::createUser();
						
		$response = GroupController::apiAddUser(new Request(array(
			"auth_token" => self::login($userCalling),
			"username" => $user->username,
			"group_id" => $group["group"]->group_id
		)));		
	}
	
	/**
	 * Remove user from group test
	 */
	public function testRemoveUserFromGroup() {
		
		$groupData = GroupsFactory::createGroup();
		$user = UserFactory::createUser();
		GroupsFactory::addUserToGroup($groupData, $user);
		
		$response = GroupController::apiRemoveUser(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"username" => $user->username,
			"group_id" => $groupData["group"]->group_id
		)));
		
		$this->assertEquals("ok", $response["status"]);
		
		$group_users = GroupsUsersDAO::getByPK($groupData["group"]->group_id, $user->user_id);
		$this->assertNull($group_users);
	}
	
	/**
	 * Remove user from group test
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testRemoveUserFromGroupUserNotInGroup() {
		
		$groupData = GroupsFactory::createGroup();
		$user = UserFactory::createUser();		
		
		GroupController::apiRemoveUser(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"username" => $user->username,
			"group_id" => $groupData["group"]->group_id
		)));			
	}
	
	/**
	 * Remove user from group test
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testRemoveUserFromGroupUserNotOwner() {
		
		$groupData = GroupsFactory::createGroup();
		$user = UserFactory::createUser();		
		
		GroupController::apiRemoveUser(new Request(array(
			"auth_token" => self::login($user),
			"username" => $user->username,
			"group_id" => $groupData["group"]->group_id
		)));			
	}
	
	/**
	 * List of groups
	 */
	public function testGroupsList() {
		
		// Create 5 groups for the same owner
		$owner = UserFactory::createUser();
		$groups = array();
		$n = 5;
		for ($i = 0; $i < $n; $i++) {
			$groups[] = GroupsFactory::createGroup($owner);
		}
		
		// Create a group for another user
		GroupsFactory::createGroup();
		
		// Call API
		$response = GroupController::apiList(new Request(array(
			"auth_token" => self::login($owner),						
		)));
				
		$this->assertEquals($n, count($response["groups"]));		
	}
	
	/**
	 * Details of a group
	 */
	public function testGroupDetails() {
		// Create a group with 5 users
		$groupData = GroupsFactory::createGroup();
		$users = array();
		$nUsers = 5;
		for ($i = 0; $i < $nUsers; $i++) {
			$users[] = UserFactory::createUser();
			GroupsFactory::addUserToGroup($groupData, $users[$i]);
		}
		
		// Call API
		$response = GroupController::apiDetails(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_id" => $groupData["group"]->group_id
		)));
				
		$this->assertEquals($nUsers, count($response["users"]));
		$this->assertEquals($groupData["group"]->group_id, $response["group"]["group_id"]);
	}
}
