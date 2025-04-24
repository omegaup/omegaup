<?php
/**
 * Description of UserDocs
 */
class UserDocsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetAllDocs() {
        [
            'pdf' => $pdfFiles,
            'md' => $mdFiles,
            'dir' => $directories,
        ] = \OmegaUp\Controllers\User::getDocsForTypeScript(
            new \OmegaUp\Request()
        )['templateProperties']['payload']['docs'];

        $this->assertNotEmpty($pdfFiles);
        $this->assertNotEmpty($mdFiles);
        $this->assertNotEmpty($directories);
    }
}
