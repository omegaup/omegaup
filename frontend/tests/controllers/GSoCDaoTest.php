<?php

class GSoCDaoTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetEditionsAndGetEditionByYear(): void {
        \OmegaUp\DAO\GSoCEdition::create(
            new \OmegaUp\DAO\VO\GSoCEdition([
                'year' => 2024,
                'is_active' => false,
            ])
        );
        \OmegaUp\DAO\GSoCEdition::create(
            new \OmegaUp\DAO\VO\GSoCEdition([
                'year' => 2025,
                'is_active' => true,
            ])
        );

        $editions = \OmegaUp\DAO\GSoCEdition::getEditions();
        $edition2024 = $this->findByPredicate(
            $editions,
            /**
             * @param array{year: int} $edition
             */
            fn (array $edition): bool => $edition['year'] === 2024
        );
        $edition2025 = $this->findByPredicate(
            $editions,
            /**
             * @param array{year: int} $edition
             */
            fn (array $edition): bool => $edition['year'] === 2025
        );
        $this->assertNotNull($edition2024);
        $this->assertNotNull($edition2025);

        $index2024 = array_search(2024, array_column($editions, 'year'));
        $index2025 = array_search(2025, array_column($editions, 'year'));
        $this->assertNotFalse($index2024);
        $this->assertNotFalse($index2025);
        $this->assertLessThan($index2024, $index2025);

        $editionByYear = \OmegaUp\DAO\GSoCEdition::getEditionByYear(2024);
        $this->assertNotNull($editionByYear);
        $this->assertSame(2024, $editionByYear['year']);

        $this->assertNull(\OmegaUp\DAO\GSoCEdition::getEditionByYear(2030));
    }

    public function testCreateGetAndFilterIdeas(): void {
        $edition = new \OmegaUp\DAO\VO\GSoCEdition([
            'year' => 2026,
            'is_active' => true,
        ]);
        \OmegaUp\DAO\GSoCEdition::create($edition);

        $firstIdeaId = \OmegaUp\DAO\GSoCIdea::createIdea(
            intval($edition->edition_id),
            'First GSoC idea',
            briefDescription: 'Idea one',
            expectedResults: 'Result one',
            preferredSkills: 'PHP',
            possibleMentors: 'mentor-1',
            estimatedHours: 175,
            skillLevel: 'Medium',
            status: 'Proposed',
            blogLink: 'https://example.com/idea-1',
            contributorUsername: 'alice'
        );
        $secondIdeaId = \OmegaUp\DAO\GSoCIdea::createIdea(
            intval($edition->edition_id),
            'Second GSoC idea',
            status: 'Accepted'
        );

        $idea = \OmegaUp\DAO\GSoCIdea::getIdeaById($firstIdeaId);
        $this->assertNotNull($idea);
        $this->assertSame($firstIdeaId, $idea['idea_id']);
        $this->assertSame('First GSoC idea', $idea['title']);
        $this->assertSame('Proposed', $idea['status']);
        $this->assertSame(intval($edition->edition_id), $idea['edition_id']);

        $allIdeas = \OmegaUp\DAO\GSoCIdea::getIdeas();
        $this->assertArrayContainsWithPredicate(
            $allIdeas,
            /**
             * @param array{idea_id: int} $ideaSummary
             */
            fn (array $ideaSummary): bool => $ideaSummary['idea_id'] === $firstIdeaId
        );
        $this->assertArrayContainsWithPredicate(
            $allIdeas,
            /**
             * @param array{idea_id: int} $ideaSummary
             */
            fn (array $ideaSummary): bool => $ideaSummary['idea_id'] === $secondIdeaId
        );

        $acceptedIdeas = \OmegaUp\DAO\GSoCIdea::getIdeas(
            intval($edition->edition_id),
            'Accepted'
        );
        $this->assertCount(1, $acceptedIdeas);
        $this->assertSame($secondIdeaId, $acceptedIdeas[0]['idea_id']);
        $this->assertSame('Accepted', $acceptedIdeas[0]['status']);
    }

    public function testUpdateIdeaAndGetByIdeaAndEdition(): void {
        $edition = new \OmegaUp\DAO\VO\GSoCEdition([
            'year' => 2027,
            'is_active' => true,
        ]);
        \OmegaUp\DAO\GSoCEdition::create($edition);

        $ideaId = \OmegaUp\DAO\GSoCIdea::createIdea(
            intval($edition->edition_id),
            'Original title',
            status: 'Proposed'
        );

        $affectedRows = \OmegaUp\DAO\GSoCIdea::updateIdea(
            $ideaId,
            title: 'Updated title',
            status: 'Accepted'
        );
        $this->assertGreaterThan(0, $affectedRows);

        $idea = \OmegaUp\DAO\GSoCIdea::getIdeaById($ideaId);
        $this->assertNotNull($idea);
        $this->assertSame('Updated title', $idea['title']);
        $this->assertSame('Accepted', $idea['status']);

        $ideaEdition = \OmegaUp\DAO\GSoCIdeaEdition::getByIdeaAndEdition(
            $ideaId,
            intval($edition->edition_id)
        );
        $this->assertNotNull($ideaEdition);
        $this->assertSame($ideaId, intval($ideaEdition->idea_id));
        $this->assertSame(
            intval($edition->edition_id),
            intval($ideaEdition->edition_id)
        );

        $this->assertSame(
            0,
            \OmegaUp\DAO\GSoCIdea::updateIdea(987654321, title: 'missing')
        );
        $this->assertNull(
            \OmegaUp\DAO\GSoCIdeaEdition::getByIdeaAndEdition(987654321, 1)
        );
    }
}
