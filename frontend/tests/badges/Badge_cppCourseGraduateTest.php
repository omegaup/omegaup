<?php

/**
 * Simple test for cppCourseGraduate Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_cppCourseGraduateTest extends \OmegaUp\Test\BadgesTestCase {
    public function testCppCourseGraduateCourseGraduate() {
        parent::courseGraduateTest(
            'introduccion_a_cpp',
            'c11-gcc',
            'cppCourseGraduate'
        );
    }
}
