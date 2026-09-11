<?php
/**
 * Unittest for Schools' APIs
 */
class SchoolCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Create school happy path
     */
    public function testCreateSchool() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $name = \OmegaUp\Test\Utils::createRandomString();

        // Call api
        \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ])
        );

        $this->assertCount(1, \OmegaUp\DAO\Schools::findByName($name));
    }

    /**
     * Does not matter how many times apiCreate is called, if the school name
     * already exists, only one school will be created. This API does not throw
     * an exception.
     */
    public function testCreateSchoolDuplicatedName() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $name = \OmegaUp\Test\Utils::createRandomString();

        // Call api
        \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ])
        );
        $this->assertCount(1, \OmegaUp\DAO\Schools::findByName($name));

        // Call api again
        \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ])
        );
        $this->assertCount(1, \OmegaUp\DAO\Schools::findByName($name));
    }

    /**
     * A school whose name merely contains another school's name as a
     * substring is a different school and must get its own profile.
     */
    public function testCreateSchoolWithSubstringNameCreatesNewSchool() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $shortName = \OmegaUp\Test\Utils::createRandomString();
        $longName = "Universidad {$shortName} Campus Sur";

        $longSchoolId = \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $longName,
            ])
        )['school_id'];

        // Creating a school whose name is a substring of an existing one
        // should not match it.
        $shortSchoolId = \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $shortName,
            ])
        )['school_id'];

        $this->assertNotSame($longSchoolId, $shortSchoolId);
    }

    /**
     * Creating a school with the same name reuses the existing profile, and
     * an exact country/state match is preferred when one exists.
     */
    public function testCreateSchoolReusesExactNameAcrossStates() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $name = \OmegaUp\Test\Utils::createRandomString();

        $schoolId = \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
                'country_id' => 'MX',
                'state_id' => 'QUE',
            ])
        )['school_id'];

        // The same name without a state still reuses the school.
        $this->assertSame(
            $schoolId,
            \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => $name,
                ])
            )['school_id']
        );

        // And the same name with the matching state reuses it too.
        $this->assertSame(
            $schoolId,
            \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => $name,
                    'country_id' => 'MX',
                    'state_id' => 'QUE',
                ])
            )['school_id']
        );
    }

    /**
     * A PHPUnit data provider for the schools list.
     *
     * @return array{0: string, 1: string, 2: list<string>}
     */
    public function schoolsListProvider(): array {
        return [
            ['query','empty',[]],
            ['query','school',['all school','all new school','all old school']],
            ['term','school',['all school','all new school','all old school']],
            ['query','college',['all college','all colleges']],
            ['term','college',['all college','all colleges']],
            ['query','university',['all university','all big university']],
            ['term','university',['all university','all big university']],
            [
                'query',
                'all',
                [
                    'all school',
                    'all college',
                    'all university',
                    'all new school',
                    'all old school',
                    'all big university',
                    'all colleges',
                ],
            ],
        ];
    }

    /**
     * Create an specific number of schools and get them all when
     * School::apiList is called
     *
     * @param list<string> $expectedSchools
     *
     * @dataProvider schoolsListProvider
     */
    public function testSchoolsList(
        string $searchParam,
        string $keyword,
        array $expectedSchools
    ) {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $schoolsMapping = [
            'all school',
            'all college',
            'all university',
            'all new school',
            'all old school',
            'all big university',
            'all colleges',
        ];

        $login = self::login($identity);

        // Call api
        foreach ($schoolsMapping as $school) {
            \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => $school,
                ])
            );
        }

        $schools = \OmegaUp\Controllers\School::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                $searchParam => $keyword,
            ])
        )['results'];

        $actualSchools = array_map(fn ($school) => $school['value'], $schools);
        $this->assertCount(count($expectedSchools), $actualSchools);
        $this->assertSame($actualSchools, $expectedSchools);
    }

    public function testSchoolsListWithNoSearchParam() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        // Call api
        \OmegaUp\Controllers\School::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
            ])
        );

        try {
            \OmegaUp\Controllers\School::apiList(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }
    }
}
