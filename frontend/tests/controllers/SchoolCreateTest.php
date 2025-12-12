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
     * A PHPUnit data provider for the schools list.
     *
     * @return array{0: string, 1: string, 2: list<string>}
     */
    public function schoolsListProvider(): array {
        return [
            ['query','empty',[]],
            // With prefix matching, 'school' won't match 'all school' since it doesn't start with 'school'
            ['query','school',[]],
            ['term','school',[]],
            // With prefix matching, 'college' won't match 'all college' since it doesn't start with 'college'
            ['query','college',[]],
            ['term','college',[]],
            // With prefix matching, 'university' won't match 'all university' since it doesn't start with 'university'
            ['query','university',[]],
            ['term','university',[]],
            // Query 'all' matches all schools starting with 'all'
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
            // Test prefix matching with 'all new'
            ['query','all new',['all new school']],
            ['term','all new',['all new school']],
            // Test prefix matching with 'all old'
            ['query','all old',['all old school']],
            ['term','all old',['all old school']],
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
