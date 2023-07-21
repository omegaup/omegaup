<?php

/**
 * Simple test for introToAlgorithms2CourseGraduate Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_introToAlgorithms2CourseGraduateTest extends \OmegaUp\Test\BadgesTestCase {
    public function testIntroToAlgorithms2CourseGraduate() {
        parent::courseGraduateTest(
            'introduccion_a_algoritmos_ii',
            'c11-gcc',
            'introToAlgorithms2CourseGraduate'
        );
    }
}
