<?php
/**
 * Allows for runtime inclusion/exclusion of certain experiments.
 *
 * In order to use this, an Experiments instance must be created and then the
 * API endpoints that are related to the experiment must call ensureEnabled(). For
 * instance, if an API that is using the 'foo' experiment, it must contain the
 * following code at the beginning of the API function.
 *
 *     public static function apiFoo(Request $r) {
 *         $experiments->ensureEnabled(Experiments::FOO);
 *         //...
 *     }
 *
 * Experiment names must be restricted to lowercase ASCII letters and
 * underscores. Once an experiment is deemed stable, all conditionals and calls
 * to ensureEnabled() related to the experiment should be removed to avoid
 * permanent clutter.
 *
 * There are several ways to enable an experiment:
 *
 * * By defining a global constant with the name 'EXPERIMENT_${NAME}', where
 *   ${NAME} is the name of your experiment in all-caps. This is mostly for use
 *   during development and for initial launch.
 * * By passing in a $_REQUEST parameter called 'experiments', that has a
 *   comma-separated list of key-value pairs. The keys must match an experiment
 *   name, and the value is the result of
 *
 *       hash_hmac('sha1', ${NAME}, OMEGAUP_EXPERIMENT_SECRET).
 *
 *   This is done to avoid users from unintentionally enabling experiments that
 *   are not quite ready. frontend/server/libs/ExperimentHashCmd.php is a
 *   command-line script that can calculate these hashes for you.
 * * By adding a row to the Users_Experiments table.
 * * TODO(lhchavez): Add support (and guidelines) for randomized trials.
 */
class Experiments {
    /**
     * Constant for the omegaUp for Schools experiment.
     */
    const SCHOOLS = 'schools';

    /**
     * An array with all the known experiments.
     */
    private static $kKnownExperiments = array(
        self::SCHOOLS,
    );

    /**
     * The prefix for config-based define()s.
     */
    const EXPERIMENT_PREFIX = 'EXPERIMENT_';

    const EXPERIMENT_REQUEST_NAME = 'experiments';

    private $enabledExperiments = array();

    /**
     * Creates an instance of Experiments.
     * @param array $request Typically $_REQUEST, except in tests.
     * @param User $user The currently logged in user.
     * @param array $defines Typically get_defined_constants(true)['user'],
     *                       except in tests.
     * @param array $knownExperiments Typically
     *                                Experiments::$kKnownExperiments, except
     *                                in tests.
     */
    public function __construct(
        array $request,
        Users $user = null,
        array $defines = null,
        array $knownExperiments = null
    ) {
        if (is_null($knownExperiments)) {
            $knownExperiments = self::$kKnownExperiments;
        }
        if (is_null($defines)) {
            $defines = get_defined_constants(true)['user'];
        }

        $this->loadExperimentsFromConfig($defines, $knownExperiments);

        if (!is_null($user)) {
            $this->loadExperimentsForUser($user, $knownExperiments);
        }

        if (isset($request[self::EXPERIMENT_REQUEST_NAME]) &&
            !empty($request[self::EXPERIMENT_REQUEST_NAME])
        ) {
            $this->loadExperimentsFromRequest($request, $knownExperiments);
        }
    }

    /**
     * Loads experiments from config (defines).
     * @param array $defines Typically get_defined_constants(true)['user'],
     *                       except in tests.
     * @param array $knownExperiments Typically
     *                                Experiments::$kKnownExperiments, except
     *                                in tests.
     */
    private function loadExperimentsFromConfig(
        array $defines,
        array $knownExperiments
    ) {
        foreach ($knownExperiments as $name) {
            if ($this->isEnabledByConfig($name, $defines)) {
                $this->enabledExperiments[] = $name;
            }
        }
    }

    /**
     * Loads experiments for a particular user.
     * @param Users $user The user.
     * @param array $knownExperiments Typically
     *                                Experiments::$kKnownExperiments, except
     *                                in tests.
     */
    private function loadExperimentsForUser(
        Users $user,
        array $knownExperiments
    ) {
        $search = new UsersExperiments(['user_id' => $user->user_id]);
        foreach (UsersExperimentsDAO::search($search) as $ue) {
            if (in_array($ue->experiment, $knownExperiments) &&
                !$this->isEnabled($ue->experiment)
            ) {
                $this->enabledExperiments[] = $ue->experiment;
            }
        }
    }

    /**
     * Loads experiments from request parameters. To avoid users enabling
     * experiments without permission, the request must provide both the name
     * of the experiment as well as an HMAC-hashed version.
     * @param array $request Typically $_REQUEST, except in tests.
     * @param array $knownExperiments Typically
     *                                Experiments::$kKnownExperiments, except
     *                                in tests.
     */
    private function loadExperimentsFromRequest(
        array $request,
        array $knownExperiments
    ) {
        $tokens = explode(',', $request[self::EXPERIMENT_REQUEST_NAME]);
        foreach ($tokens as $token) {
            $kvp = explode('=', $token);
            if (count($kvp) != 2) {
                continue;
            }
            $name = $kvp[0];
            $hash = $kvp[1];
            if (in_array($name, $knownExperiments) &&
                !$this->isEnabled($name) &&
                $hash == self::getExperimentHash($name)
            ) {
                $this->enabledExperiments[] = $name;
            }
        }
    }

    /**
     * Ensures an experiment is enabled.
     * @param string name The name of the experiment.
     * @throws NotFoundException if the experiment is not enabled.
     */
    public function ensureEnabled($name) {
        if (!$this->isEnabled($name)) {
            throw new NotFoundException('apiNotFound');
        }
    }

    /**
     * Returns whether an experiment is enabled.
     * @param string $name The experiment name.
     * @return boolean True iff the experiment is enabled.
     */
    public function isEnabled($name) {
        return in_array($name, $this->enabledExperiments);
    }

    /**
     * Returns the hash of the experiment for the given name.
     * @param string $name The experiment name.
     * @return string The hashed experiment name.
     */
    public static function getExperimentHash($name) {
        return hash_hmac('sha1', $name, OMEGAUP_EXPERIMENT_SECRET);
    }

    /**
     * Returns an array with all the enabled experiments.
     * @return array The array with all experiment names.
     */
    public function getEnabledExperiments() {
        return $this->enabledExperiments;
    }

    /**
     * Returns whether an experiment is enabled by a config definition.
     * @param string $name The experiment name.
     * @param array $defines The array with all the user-defined constants.
     * @return boolean True iff the experiment is enabled by config.
     */
    private function isEnabledByConfig($name, $defines) {
        return array_key_exists(
            self::EXPERIMENT_PREFIX . strtoupper($name),
            $defines
        );
    }
}
