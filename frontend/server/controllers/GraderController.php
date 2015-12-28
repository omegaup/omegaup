<?php

/**
 * Description of GraderController
 *
 * @author joemmanuel
 */
class GraderController extends Controller {
    /**
     * Validate requests for grader apis
     *
     * @param Request $r
     * @throws ForbiddenAccessException
     */
    private static function validateRequest(Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::IsSystemAdmin($r['current_user_id'])) {
            throw new ForbiddenAccessException();
        }
    }

    /**
     * Sets embedded runners to $enabled and triggers a config reload in grader
     *
     * @param boolean $enabled
     * @throws ApiException
     * @throws InvalidFilesystemOperationException
     * @throws InvalidParameterException
     */
    private static function setEmbeddedRunners($value) {
        self::$log->info('Calling grader/reload-config');
        $grader = new Grader();
        $response = $grader->reloadConfig(array(
            'overrides' => array(
                'grader.embedded_runner.enable' => $value
            )
        ));

        self::$log->info('Reload config response: ' . $response);

        return $response;
    }

    /**
     * Entry point to configure omegaup in sardina (local) mode
     *
     * @param Request $r
     * @return type
     */
    public static function apiScaleIn(Request $r) {
        self::validateRequest($r);

        $response['grader'] = self::setEmbeddedRunners('true');

        // Get ec2 machines to terminate
        $instances_array = self::getEc2Status();
        $instances_string = '';
        foreach ($instances_array as $instance => $value) {
            self::$log->info('Detecting instance: '. $value);
            $instances_string = $instances_string. ' ' . $instance . ' ';
        }

        self::$log->info('Terminating EC2 instances');
        $ec2_cmd_output = array();
        $return_var = 0;
        $cmd = 'ec2-terminate-instances '. $instances_string .' --region us-west-1';

        self::$log->info('Executing: '. $cmd);
        exec($cmd, $ec2_cmd_output, $return_var);
        if ($return_var !== 0) {
            // D:
            self::$log->error($cmd . ' returned: ' .$return_var);
            throw new InvalidFilesystemOperationException('Error executing ec2-terminate-instances. Please check log for details');
        }

        $response['ec2-terminate-instances'] = $ec2_cmd_output;

        return $response;
    }

    /**
     * Entry point to configure omegaup in ec2 mode
     *
     * @param Request $r
     * @return type
     */
    public static function apiScaleOut(Request $r) {
        self::validateRequest($r);

        Validators::isNumber($r['count'], 'count');

        $response['grader'] = self::setEmbeddedRunners('false');

        self::$log->info('Bootstrapping more instances: ');
        $ec2_cmd_output = array();
        $return_var = 0;

        $cmd = 'ec2-run-instances ami-3e123e7b -k omegaup_backend_test_key -i m1.medium -n '. $r['count']. ' --region us-west-1';

        self::$log->info('Executing: '. $cmd);
        exec($cmd, $ec2_cmd_output, $return_var);
        if ($return_var !== 0) {
            // D:
            self::$log->error($cmd . ' returned: ' .$return_var);
            throw new InvalidFilesystemOperationException('Error executing ec2-run-instances. Please check log for details');
        }

        $response['ec2-run-instances'] = $ec2_cmd_output;

        return $response;
    }

    /**
     * Calls to /status grader
     *
     * @param Request $r
     * @return array
     */
    public static function apiStatus(Request $r) {
        self::validateRequest($r);

        $response = array();

        self::$log->debug('Getting grader /status');
        $grader = new Grader();
        $response['grader'] = $grader->status();

        // TODO(lhchavez): Re-enable when we use EC2 again.
        //self::$log->info("Getting EC2 status");
        //$response["cloud"] = self::getEc2Status();

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Use ec2-describe-instances cmd tool to check the status of the images in
     * ec2
     *
     * @return array
     * @throws InvalidFilesystemOperationException
     */
    private static function getEc2Status() {
        $ec2_describe_output = array();
        $return_var = 0;
        exec('ec2-describe-instances --region us-west-1 --simple', $ec2_describe_output, $return_var);
        if ($return_var !== 0) {
            // D:
            self::$log->error('ec2-describe-instances --region us-west-1 --simple ' . $return_var);
            return array('error'=>'error calling ec2-describe-instances');
        }

        return self::parseEc2DescribeCmdOutput($ec2_describe_output);
    }

    /**
     * Organizes nicely the tab separated string from ec2 cmd tool
     *
     * @param array $string
     * @return array
     */
    private static function parseEc2DescribeCmdOutput($ec2_describe_output) {
        $instances = array();
        foreach ($ec2_describe_output as $instance_data) {
            $contents_array = explode("\t", $instance_data);

            $values = array();
            $values['instance'] = $contents_array[0];
            $values['status'] = $contents_array[1];
            $values['endpoint'] = $contents_array[2];
            $values['sg'] = $contents_array[3];

            $instances[$values['instance']] = $values;
        }

        return $instances;
    }
}
