<?php

/**
 * Description of ContestArchiveContest
 */
class CertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Generate certificates in a contest
     *
     */
    public function testGenerateContestCertificates() {
        // Create a valid contest Request object
        $contestData = \OmegaUp\Test\Factories\Contest::getRequest(new \OmegaUp\Test\Factories\ContestParams(
            ['admissionMode' => 'private']
        ));

        $r = $contestData['request'];
        $contestDirector = $contestData['director'];

        // Log in the user and set the auth token in the new request
        $login = self::login($contestDirector);
        $r['auth_token'] = $login->auth_token;
        $r['certificates_status'] = 'uninitiated';

        // Call the API
        $response = \OmegaUp\Controllers\Contest::apiCreate(
            $r
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        //error_log(print_r($contest,true));

        $certificates_cutoff = 3;

        //preguntar si certificates status es uninitiated (contest_id, certificate_cutoff)
        \OmegaUp\Controllers\Certificate::apiGenerateContestCertificates(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_id' => $contest->contest_id,
                'certificates_cutoff' => $certificates_cutoff
            ])
        );

        // Assert status of new contest
        $this->assertSame('ok', $response['status']);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        error_log(print_r($contest, true));

        // Assert that the contest requested exists in the DB
        //$this->assertContest($r);
    }
}
