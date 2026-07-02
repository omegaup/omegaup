<?php
/**
 * Tests the /api/user/list API for substring and special-character search.
 */
class UserListTest extends \OmegaUp\Test\ControllerTestCase {
    public function testSearchUsersBySubstringAndSpecialCharacters() {
        ['identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );
        $password = \OmegaUp\Test\Utils::createRandomString();

        $identities = [
            [
                'username' => "{$group->alias}:substring_user",
                'name' => 'Substring User',
                'country_id' => '',
                'state_id' => '',
                'gender' => 'female',
                'password' => $password,
            ],
            [
                'username' => "{$group->alias}:underscore_name",
                'name' => 'Underscore_Name',
                'country_id' => '',
                'state_id' => '',
                'gender' => 'female',
                'password' => $password,
            ],
            [
                'username' => "{$group->alias}:dot.name",
                'name' => 'Dot.Name',
                'country_id' => '',
                'state_id' => '',
                'gender' => 'female',
                'password' => $password,
            ],
            [
                'username' => "{$group->alias}:dash-name",
                'name' => 'Dash-Name',
                'country_id' => '',
                'state_id' => '',
                'gender' => 'female',
                'password' => $password,
            ],
        ];

        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => json_encode($identities),
            'group_alias' => $group->alias,
        ]));
        // Regression tests for identity search via FULLTEXT ngram index:
        // - substring matches (partial word search still works)
        // - usernames containing special characters (_, ., -)
        // - search terms starting with a MySQL boolean-mode operator ("-name")
        //   should match literally instead of being treated as an exclusion
        $this->assertSearchResultContains('string', "{$group->alias}:substring_user");
        $this->assertSearchResultContains('underscore_', "{$group->alias}:underscore_name");
        $this->assertSearchResultContains('dot.', "{$group->alias}:dot.name");
        $this->assertSearchResultContains('dash-name', "{$group->alias}:dash-name");
        $this->assertSearchResultContains('-name', "{$group->alias}:dash-name");
    }

    private function assertSearchResultContains(string $query, string $expectedUsername): void {
        $results = \OmegaUp\Controllers\User::apiList(new \OmegaUp\Request([
            'query' => $query,
            'auth_token' => self::login(\OmegaUp\Test\Factories\User::createUser()['identity'])->auth_token,
        ]))['results'];

        $usernames = array_map(fn ($item) => $item['key'], $results);
        $this->assertContains(
            $expectedUsername,
            $usernames,
            "Expected identity {$expectedUsername} to be present for query {$query}"
        );
    }
}
