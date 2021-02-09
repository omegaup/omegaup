<?php

/**
 * Simple test for introToAlgorithmsCourseGraduate Badge
 *
 * @author RodCross
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_introToAlgorithmsCourseGraduateTest extends \OmegaUp\Test\BadgesTestCase {
    public function testIntroToAlgorithmsCourseGraduate() {
        parent::courseGraduateTest(
            'introduccion_a_algoritmos',
            'c11-gcc',
            'introToAlgorithmsCourseGraduate'
        );
    }
}
