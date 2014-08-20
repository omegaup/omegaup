<?php

class GroupsFactory {
	
	/**
	 * Create group
	 * 
	 * @param type $owner
	 * @param type $name
	 * @param type $description
	 */
	public static function createGroup($owner = null, $name = null, $description = null) {
		if (is_null($owner)) {
			$owner = UserFactory::createUser();
		}
		
		if (is_null($name)) {
			$name = Utils::CreateRandomString();
		}
		
		if (is_null($description)) {
			$description = Utils::CreateRandomString();
		}
		
		$r = new Request(array(
			"auth_token" => OmegaupTestCase::login($owner),
			"name" => $name,
			"description" => $description
		));
		
		$response = GroupController::apiCreate($r);
		$groups = GroupsDAO::search(new Groups(array(
			"name" => $name
		)));
		
		return array(
			"request" => $r,
			"response" => $response,
			"owner" => $owner,
			"group" => $groups[0]
		);
	}
	
	/**
	 * Add user to group helper
	 * 
	 * @param array $groupData
	 * @param Users $user
	 */
	public static function addUserToGroup(array $groupData, Users $user) {
		
		GroupController::apiAddUser(new Request(array(
			"auth_token" => OmegaupTestCase::login($groupData["owner"]),
			"username" => $user->username,
			"group_id" => $groupData["group"]->group_id
		)));
	}
}
