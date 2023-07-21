<?php

/**
 * Simple test for pythonCourseGraduate Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_pythonCourseGraduateTest extends \OmegaUp\Test\BadgesTestCase {
    public function testPythonCourseGraduate() {
        parent::courseGraduateTest(
            'Curso-de-Python-FutureLabs',
            'py3',
            'pythonCourseGraduate'
        );
    }
}
