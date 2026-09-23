<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for auth token format validation in Session controller.
 */
class SessionAuthTokenTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A valid auth token should produce a valid session.
     */
    public function testValidAuthTokenProducesSession(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertTrue($session['valid']);
        $this->assertNotNull($session['identity']);
    }

    /**
     * Tokens with wrong number of parts should be rejected.
     */
    public function testTokenWithWrongPartCountIsRejected(): void {
        $malformedTokens = [
            '',                           // empty
            'abcdef1234567890abcdef1234567890',  // no dashes
            'aabbcc-1234',                // only two parts
            'aa-bb-cc-dd',                // four parts
        ];

        foreach ($malformedTokens as $token) {
            \OmegaUp\Controllers\Session::invalidateLocalCache();
            $session = \OmegaUp\Controllers\Session::getCurrentSession(
                new \OmegaUp\Request([
                    'auth_token' => $token,
                ])
            );
            $this->assertFalse(
                $session['valid'],
                "Token '{$token}' should have been rejected (wrong part count)"
            );
        }
    }

    /**
     * Token with non-hex entropy should be rejected.
     */
    public function testTokenWithNonHexEntropyIsRejected(): void {
        // 30 chars but contains 'g' which is not hex
        $badEntropy = 'gggggggggggggggggggggggggggggg';
        $token = "{$badEntropy}-1-" . str_repeat('a', 64);

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $token,
            ])
        );
        $this->assertFalse(
            $session['valid'],
            'Token with non-hex entropy should be rejected'
        );
    }

    /**
     * Token with wrong-length entropy should be rejected.
     */
    public function testTokenWithWrongLengthEntropyIsRejected(): void {
        // Too short (20 hex chars instead of 30)
        $shortEntropy = str_repeat('a', 20);
        $token = "{$shortEntropy}-1-" . str_repeat('b', 64);

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $token,
            ])
        );
        $this->assertFalse(
            $session['valid'],
            'Token with short entropy should be rejected'
        );

        // Too long (40 hex chars instead of 30)
        $longEntropy = str_repeat('a', 40);
        $token = "{$longEntropy}-1-" . str_repeat('b', 64);

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $token,
            ])
        );
        $this->assertFalse(
            $session['valid'],
            'Token with long entropy should be rejected'
        );
    }

    /**
     * Token with non-numeric identity_id should be rejected.
     */
    public function testTokenWithNonNumericIdentityIdIsRejected(): void {
        $entropy = str_repeat('a', 30);
        $hash = str_repeat('b', 64);

        $badIds = ['abc', '', '-1', '12.5', '1a2b'];
        foreach ($badIds as $id) {
            $token = "{$entropy}-{$id}-{$hash}";

            \OmegaUp\Controllers\Session::invalidateLocalCache();
            $session = \OmegaUp\Controllers\Session::getCurrentSession(
                new \OmegaUp\Request([
                    'auth_token' => $token,
                ])
            );
            $this->assertFalse(
                $session['valid'],
                "Token with identity_id '{$id}' should be rejected"
            );
        }
    }

    /**
     * Token with wrong-length hash should be rejected.
     */
    public function testTokenWithWrongLengthHashIsRejected(): void {
        $entropy = str_repeat('a', 30);

        // Too short hash (32 chars instead of 64)
        $token = "{$entropy}-1-" . str_repeat('b', 32);

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $token,
            ])
        );
        $this->assertFalse(
            $session['valid'],
            'Token with short hash should be rejected'
        );
    }

    /**
     * Token with non-hex hash should be rejected.
     */
    public function testTokenWithNonHexHashIsRejected(): void {
        $entropy = str_repeat('a', 30);
        // 64 chars but contains 'z'
        $badHash = str_repeat('z', 64);
        $token = "{$entropy}-1-{$badHash}";

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $token,
            ])
        );
        $this->assertFalse(
            $session['valid'],
            'Token with non-hex hash should be rejected'
        );
    }

    /**
     * Token with many dashes (attack vector) should be capped by explode
     * limit and subsequently rejected by format checks.
     */
    public function testTokenWithManyDashesIsRejected(): void {
        // Construct a token with 1000 dashes to verify it doesn't cause
        // excessive memory allocation.
        $token = implode('-', array_fill(0, 1000, 'a'));

        \OmegaUp\Controllers\Session::invalidateLocalCache();
        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            new \OmegaUp\Request([
                'auth_token' => $token,
            ])
        );
        $this->assertFalse(
            $session['valid'],
            'Token with many dashes should be rejected'
        );
    }
}
