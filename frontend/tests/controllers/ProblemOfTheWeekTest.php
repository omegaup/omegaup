<?php

/**
 * Tests for ProblemOfTheWeekController.php.
 *
 */
class ProblemOfTheWeekTest extends OmegaupTestCase {
    public function testProblemOfTheWeekApis() {
        // Setup synthetic data.
        $numberOfProblems = 8;
        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem();
        }

        $this->createProblemOfTheWeek('2018-01-12', $problemData[0]['problem']->problem_id);
        $this->createProblemOfTheWeek('2018-01-19', $problemData[1]['problem']->problem_id);
        $this->createProblemOfTheWeek('2018-01-26', $problemData[2]['problem']->problem_id);
        $this->createProblemOfTheWeek('2018-02-02', $problemData[3]['problem']->problem_id);
        $this->createProblemOfTheWeek('2018-02-09', $problemData[4]['problem']->problem_id);
        $this->createProblemOfTheWeek('2018-02-16', $problemData[5]['problem']->problem_id);
        $this->createProblemOfTheWeek('2018-02-23', $problemData[6]['problem']->problem_id);

        $response = ProblemOfTheWeekController::apiGetLastProblemOfTheWeek(new Request([]));

        $this->assertEquals($response['results']['alias'], $problemData[6]['problem']->alias);

        $this->createProblemOfTheWeek('2018-01-05', $problemData[7]['problem']->problem_id);

        $response = ProblemOfTheWeekController::apiGetLastProblemOfTheWeek(new Request([]));

        $this->assertEquals($response['results']['alias'], $problemData[6]['problem']->alias);

        $response = ProblemOfTheWeekController::apiGetListOfProblemsOfTheWeek(
            new Request([
                'offset' => 1,
                'rowcount' => 3
            ])
        );

        $this->assertEquals(3, count($response['results']));
        $this->assertEquals($response['results'][0]['alias'], $problemData[5]['problem']->alias);
        $this->assertEquals($response['results'][1]['alias'], $problemData[4]['problem']->alias);
        $this->assertEquals($response['results'][2]['alias'], $problemData[3]['problem']->alias);
    }
    private function createProblemOfTheWeek($date, $problemId) {
        ProblemOfTheWeekDAO::save(new ProblemOfTheWeek([
            'problem_id' => $problemId,
            'time' => $date,
            'difficulty' => 'easy',
        ]));
    }
}
