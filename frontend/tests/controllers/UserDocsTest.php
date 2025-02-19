<?php
/**
 * Description of UserDocs
 */
class UserDocsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testRenderMarkdownFile() {
        [
            'content' => $content,
            'filename' => $filename,
        ] = \OmegaUp\Controllers\User::getMarkdownViewerForTypeScript(
            new \OmegaUp\Request([
                'file' => '_Footer',
            ])
        )['templateProperties']['payload'];

        $this->assertSame('omegaUp 2025', $content);
        $this->assertSame('_Footer', $filename);
    }

    public function testFileDoesNotExist() {
        try {
            \OmegaUp\Controllers\User::getMarkdownViewerForTypeScript(
                new \OmegaUp\Request([
                    'file' => 'frontend/www/docs/DOES_NOT_EXIST.md',
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('fileNotFound', $e->getMessage());
        }
    }
}
