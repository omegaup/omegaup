<?php

class ProblemSearchBooleanModeTest extends \OmegaUp\Test\ControllerTestCase {
    private function createPublicProblem(
        int $aclId,
        string $alias,
        string $title
    ): \OmegaUp\DAO\VO\Problems {
        $problem = new \OmegaUp\DAO\VO\Problems([
            'acl_id' => $aclId,
            'visibility' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
            'alias' => $alias,
            'title' => $title,
            'current_version' => sha1($alias),
            'creation_date' => new \OmegaUp\Timestamp(\OmegaUp\Time::get()),
        ]);
        \OmegaUp\DAO\Problems::create($problem);
        return $problem;
    }

    /**
     * @return array{aclId: int, aliases: list<string>}
     */
    private function seedProblems(): array {
        [
            'user' => $author,
        ] = \OmegaUp\Test\Factories\User::createUserWithoutVerify();
        $aclId = \OmegaUp\DAO\ACLs::create(new \OmegaUp\DAO\VO\ACLs([
            'owner_id' => $author->user_id,
        ]));
        $aliases = [];
        $problemDefinitions = [
            ['suma-dos', 'Suma de dos numeros'],
            ['test-problem', 'Test Problem'],
        ];
        foreach ($problemDefinitions as [$alias, $title]) {
            $this->createPublicProblem($aclId, $alias, $title);
            $aliases[] = $alias;
        }
        \OmegaUp\Test\Utils::commit();
        return ['aclId' => $aclId, 'aliases' => $aliases];
    }

    /**
     * @return array{problems: list<array{alias: string}>, count: int}
     */
    private function searchProblems(string $query): array {
        return \OmegaUp\DAO\Problems::byIdentityType(
            IDENTITY_ANONYMOUS,
            'all',
            'problem_id',
            'desc',
            0,
            100,
            $query,
            null,
            null,
            [],
            0,
            false,
            [],
            null,
            false,
            null,
            'all',
            []
        );
    }

    public function testDashInQueryIsNotTreatedAsExclusion(): void {
        $this->seedProblems();

        $result = $this->searchProblems('-problem');
        $aliases = array_map(
            fn ($problem) => $problem['alias'],
            $result['problems']
        );
        $this->assertContains('test-problem', $aliases);

        [
            'problems' => $problems,
            'count' => $count,
        ] = \OmegaUp\DAO\Problems::getAllWithCount(1, 100, '-problem');
        $this->assertGreaterThan(0, $count);
        $allAliases = array_map(
            fn ($problem) => $problem->alias,
            $problems
        );
        $this->assertContains('test-problem', $allAliases);
    }

    /**
     * A term preceded by a plus sign must not be treated as a required-term
     * operator: the plus sign is escaped and the term is matched literally,
     * so a problem that only contains the first term still shows up.
     */
    public function testPlusDoesNotActAsRequiredOperator(): void {
        ['aclId' => $aclId] = $this->seedProblems();

        $this->createPublicProblem($aclId, 'alpha-solo', 'Alpha solo');
        \OmegaUp\Test\Utils::commit();

        $result = $this->searchProblems('alpha +beta');
        $aliases = array_map(
            fn ($problem) => $problem['alias'],
            $result['problems']
        );
        $this->assertContains('alpha-solo', $aliases);
    }

    public function testMalformedBooleanQueriesDoNotError(): void {
        $this->seedProblems();

        foreach (['(suma', '"unclosed', '++suma', 'suma)', '*'] as $query) {
            $result = $this->searchProblems($query);
            $this->assertArrayHasKey('problems', $result);
            $this->assertArrayHasKey('count', $result);

            $paged = \OmegaUp\DAO\Problems::getAllWithCount(1, 100, $query);
            $this->assertArrayHasKey('problems', $paged);
            $this->assertArrayHasKey('count', $paged);
        }
    }

    public function testPlainSearchStillWorks(): void {
        $this->seedProblems();

        $result = $this->searchProblems('suma');
        $aliases = array_map(
            fn ($problem) => $problem['alias'],
            $result['problems']
        );
        $this->assertContains('suma-dos', $aliases);

        [
            'problems' => $problems,
            'count' => $count,
        ] = \OmegaUp\DAO\Problems::getAllWithCount(1, 100, 'suma');
        $this->assertGreaterThan(0, $count);
        $allAliases = array_map(
            fn ($problem) => $problem->alias,
            $problems
        );
        $this->assertContains('suma-dos', $allAliases);
    }
}
