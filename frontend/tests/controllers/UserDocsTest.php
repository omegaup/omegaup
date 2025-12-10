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
        // This array is empty until PR #7989 is approved and merged
        $this->assertEmpty($mdFiles);
        $this->assertNotEmpty($directories);
    }
}
