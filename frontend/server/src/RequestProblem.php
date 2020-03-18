<?php

namespace OmegaUp;

class RequestProblem {
    /**
     * @readonly
     * @var string
     */
    public $problem;

    /**
     * @readonly
     * @var int
     */
    public $points;

    /**
     * @param array{problem: string, points: int} $params
     */
    public function __construct($params) {
        $this->problem = $params['problem'];
        $this->points = $params['points'];
    }
}
