<?php
/**
 * Tests para el README del perfil de usuario.
 */
class UserReadmeTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Prueba crear un README nuevo para un usuario.
     */
    public function testCreateReadme() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $content = '# Hola\n\nSoy un usuario de omegaUp.';
        $response = \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => $content,
            ])
        );

        $this->assertSame('ok', $response['status']);

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertSame($content, $readme->content);
        $this->assertTrue($readme->is_visible);
        $this->assertFalse($readme->is_disabled);
        $this->assertSame(0, $readme->report_count);
    }

    /**
     * Prueba actualizar un README existente.
     */
    public function testUpdateExistingReadme() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Contenido inicial',
            ])
        );

        $updatedContent = '# Contenido actualizado\n\nNueva información.';
        $response = \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => $updatedContent,
            ])
        );

        $this->assertSame('ok', $response['status']);

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertSame($updatedContent, $readme->content);
    }

    /**
     * Prueba que el perfil incluye el README cuando está visible y habilitado.
     */
    public function testProfileIncludesReadme() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $content = '## Mi perfil\n\nMe gusta la programación competitiva.';
        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => $content,
            ])
        );

        $viewerLogin = self::login($viewer);
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $viewerLogin->auth_token,
                'username' => $identity->username,
            ])
        );

        $this->assertArrayHasKey('readme', $response);
        $this->assertSame($content, $response['readme']);
    }

    /**
     * Prueba que el perfil retorna null en readme cuando está deshabilitado.
     */
    public function testProfileReadmeNullWhenDisabled() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Contenido que será deshabilitado',
            ])
        );

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        \OmegaUp\DAO\UserReadmes::setDisabled(
            intval($readme->readme_id),
            true
        );

        $viewerLogin = self::login($viewer);
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $viewerLogin->auth_token,
                'username' => $identity->username,
            ])
        );

        $this->assertArrayHasKey('readme', $response);
        $this->assertNull($response['readme']);
    }

    /**
     * Prueba que el perfil retorna null en readme cuando no existe.
     */
    public function testProfileReadmeNullWhenNotExists() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();

        $viewerLogin = self::login($viewer);
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $viewerLogin->auth_token,
                'username' => $identity->username,
            ])
        );

        $this->assertArrayHasKey('readme', $response);
        $this->assertNull($response['readme']);
    }

    /**
     * Prueba que apiUpdateReadme falla si el contenido excede 10,000 caracteres.
     */
    public function testUpdateReadmeTooLong() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiUpdateReadme(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'readme' => str_repeat('a', 10001),
                ])
            );
            $this->fail('Debió lanzar InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterStringTooLong', $e->getMessage());
        }
    }

    /**
     * Prueba reportar un README y verificar la actualización del contador.
     */
    public function testReportReadme() {
        ['user' => $targetUser, 'identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();

        $ownerLogin = self::login($targetIdentity);
        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $ownerLogin->auth_token,
                'readme' => 'Contenido inapropiado',
            ])
        );

        $reporterLogin = self::login($reporter);
        $response = \OmegaUp\Controllers\User::apiReportReadme(
            new \OmegaUp\Request([
                'auth_token' => $reporterLogin->auth_token,
                'username' => $targetIdentity->username,
            ])
        );

        $this->assertSame('ok', $response['status']);

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($targetUser->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertSame(1, $readme->report_count);
        $this->assertFalse($readme->is_disabled);
    }

    /**
     * Prueba que el auto-deshabilitar ocurre al alcanzar el umbral de reportes.
     */
    public function testAutoDisableAtReportThreshold() {
        ['user' => $targetUser, 'identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $ownerLogin = self::login($targetIdentity);
        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $ownerLogin->auth_token,
                'readme' => 'Contenido que recibirá muchos reportes',
            ])
        );

        $threshold = \OmegaUp\Controllers\User::README_REPORT_THRESHOLD;
        for ($i = 0; $i < $threshold; $i++) {
            ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();
            $reporterLogin = self::login($reporter);
            \OmegaUp\Controllers\User::apiReportReadme(
                new \OmegaUp\Request([
                    'auth_token' => $reporterLogin->auth_token,
                    'username' => $targetIdentity->username,
                ])
            );
        }

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($targetUser->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertTrue($readme->is_disabled);
        $this->assertGreaterThanOrEqual($threshold, $readme->report_count);
    }

    /**
     * Prueba que un usuario no puede reportar el mismo README dos veces.
     */
    public function testDuplicateReportPrevented() {
        ['identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();

        $ownerLogin = self::login($targetIdentity);
        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $ownerLogin->auth_token,
                'readme' => 'README a reportar',
            ])
        );

        $reporterLogin = self::login($reporter);
        \OmegaUp\Controllers\User::apiReportReadme(
            new \OmegaUp\Request([
                'auth_token' => $reporterLogin->auth_token,
                'username' => $targetIdentity->username,
            ])
        );

        try {
            \OmegaUp\Controllers\User::apiReportReadme(
                new \OmegaUp\Request([
                    'auth_token' => $reporterLogin->auth_token,
                    'username' => $targetIdentity->username,
                ])
            );
            $this->fail('Debió lanzar DuplicatedEntryInDatabaseException');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('readmeAlreadyReported', $e->getMessage());
        }
    }

    /**
     * Prueba que editar un README deshabilitado lo vuelve a habilitar.
     */
    public function testUpdateReadmeRestoresAfterDisable() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Contenido original',
            ])
        );

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        \OmegaUp\DAO\UserReadmes::setDisabled(
            intval($readme->readme_id),
            true
        );

        \OmegaUp\Controllers\User::apiUpdateReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Contenido actualizado después de deshabilitar',
            ])
        );

        $updatedReadme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($updatedReadme);
        $this->assertFalse($updatedReadme->is_disabled);
        $this->assertSame(
            'Contenido actualizado después de deshabilitar',
            $updatedReadme->content
        );
    }

    /**
     * Prueba que reportar un README inexistente lanza NotFoundException.
     */
    public function testReportNonExistentReadme() {
        ['identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();

        $reporterLogin = self::login($reporter);
        try {
            \OmegaUp\Controllers\User::apiReportReadme(
                new \OmegaUp\Request([
                    'auth_token' => $reporterLogin->auth_token,
                    'username' => $targetIdentity->username,
                ])
            );
            $this->fail('Debió lanzar NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('resourceNotFound', $e->getMessage());
        }
    }
}
