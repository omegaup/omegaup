<?php

namespace OmegaUp;

/**
 * Allows for runtime inclusion/exclusion of certain experiments.
 *
 * In order to use this, an Experiments instance must be created and then the
 * API endpoints that are related to the experiment must call ensureEnabled(). For
 * instance, if an API that is using the 'foo' experiment, it must contain the
 * following code at the beginning of the API function.
 *
 *     public static function apiFoo(\OmegaUp\Request $r) {
 *         \OmegaUp\Experiments::getInstance()->ensureEnabled(\OmegaUp\Experiments::FOO);
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
 *   are not quite ready. frontend/server/cmd/ExperimentHashCmd.php is a
 *   command-line script that can calculate these hashes for you.
 * * By adding a row to the Users_Experiments table.
 * * TODO(lhchavez): Add support (and guidelines) for randomized trials.
 */
class Experiments {
    /**
     * An array with all the known experiments.
     */
    private const KNOWN_EXPERIMENTS = [
    ];

    /**
     * The prefix for config-based define()s.
     */
    const EXPERIMENT_PREFIX = 'EXPERIMENT_';

    const EXPERIMENT_REQUEST_NAME = 'experiments';

    /** @var string[] */
    private $enabledExperiments = [];

    /** @var null|Experiments */
    private static $_instance = null;

    /**
     * Creates an instance of Experiments.
     * @param null|string $requestExperiments Typically $_REQUEST['experiments'], except in tests.
     * @param \OmegaUp\DAO\VO\Identities $identity The currently logged in identity.
     * @param array<string, mixed> $defines Typically get_defined_constants(true)['user'],
     *                       except in tests.
     * @param string[] $knownExperiments Typically
     *                                \OmegaUp\Experiments::KNOWN_EXPERIMENTS, except
     *                                in tests.
     */
    public function __construct(
        ?string $requestExperiments,
        \OmegaUp\DAO\VO\Identities $identity = null,
        array $defines = null,
        array $knownExperiments = null
    ) {
        if ($knownExperiments === null) {
            $knownExperiments = self::KNOWN_EXPERIMENTS;
        }
        if ($defines === null) {
            /** @var array<string, mixed> */
            $defines = get_defined_constants(true)['user'];
        }

        $this->loadExperimentsFromConfig($defines, $knownExperiments);

        if ($identity !== null) {
            $this->loadExperimentsForIdentity($identity, $knownExperiments);
        }

        if ($requestExperiments !== null) {
            $this->loadExperimentsFromRequest(
                $requestExperiments,
                $knownExperiments
            );
        }
    }

    /**
     * Loads experiments from config (defines).
     * @param array<string, mixed> $defines Typically
     * get_defined_constants(true)['user'], except in tests.
     * @param string[] $knownExperiments Typically
     * \OmegaUp\Experiments::KNOWN_EXPERIMENTS, except in tests.
     */
    private function loadExperimentsFromConfig(
        array $defines,
        array $knownExperiments
    ): void {
        foreach ($knownExperiments as $name) {
            if ($this->isEnabledByConfig($name, $defines)) {
                $this->enabledExperiments[] = $name;
            }
        }
    }

    /**
     * Loads experiments for a particular identity.
     * @param \OmegaUp\DAO\VO\Identities $identity The identity.
     * @param string[] $knownExperiments Typically
     * \OmegaUp\Experiments::KNOWN_EXPERIMENTS, except in tests.
     */
    private function loadExperimentsForIdentity(
        \OmegaUp\DAO\VO\Identities $identity,
        array $knownExperiments
    ): void {
        if ($identity->user_id === null) {
            // No experiments can be enabled for unassociated identities.
            return;
        }
        foreach (
            \OmegaUp\DAO\UsersExperiments::getByUserId(
                $identity->user_id
            ) as $ue
        ) {
            if (
                in_array($ue->experiment, $knownExperiments) &&
                $ue->experiment !== null &&
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
     * @param string $requestExperiments Typically $_REQUEST['experiments'], except in tests.
     * @param string[] $knownExperiments Typically
     *                                \OmegaUp\Experiments::KNOWN_EXPERIMENTS, except
     *                                in tests.
     */
    private function loadExperimentsFromRequest(
        string $requestExperiments,
        array $knownExperiments
    ): void {
        $tokens = explode(',', $requestExperiments);
        foreach ($tokens as $token) {
            $kvp = explode('=', $token);
            if (count($kvp) != 2) {
                continue;
            }
            $name = $kvp[0];
            $hash = $kvp[1];
            if (
                in_array($name, $knownExperiments) &&
                !$this->isEnabled($name) &&
                $hash == self::getExperimentHash($name)
            ) {
                $this->enabledExperiments[] = $name;
            }
        }
    }

    /**
     * Ensures an experiment is enabled.
     * @param string $name The name of the experiment.
     * @throws \OmegaUp\Exceptions\NotFoundException if the experiment is not enabled.
     */
    public function ensureEnabled(string $name): void {
        if (!$this->isEnabled($name)) {
            throw new \OmegaUp\Exceptions\NotFoundException('apiNotFound');
        }
    }

    /**
     * Returns whether an experiment is enabled.
     * @param string $name The experiment name.
     * @return bool True iff the experiment is enabled.
     */
    public function isEnabled(string $name): bool {
        return in_array($name, $this->enabledExperiments);
    }

    /**
     * Returns the hash of the experiment for the given name.
     * @param string $name The experiment name.
     * @return string The hashed experiment name.
     */
    public static function getExperimentHash(string $name): string {
        return hash_hmac('sha1', $name, OMEGAUP_EXPERIMENT_SECRET);
    }

    /**
     * Returns an array with all the enabled experiments.
     * @return string[] The array with all experiment names.
     */
    public function getEnabledExperiments(): array {
        return $this->enabledExperiments;
    }

    /**
     * Returns an array with all the known experiments.
     * @return string[] The array with all the known experiment names.
     */
    public function getAllKnownExperiments(): array {
        return self::KNOWN_EXPERIMENTS;
    }

    /**
     * Returns whether an experiment is enabled by a config definition.
     * @param string $name The experiment name.
     * @param array<string, mixed> $defines The array with all the user-defined constants.
     * @return bool True iff the experiment is enabled by config.
     */
    public function isEnabledByConfig(string $name, array $defines): bool {
        return array_key_exists(
            self::EXPERIMENT_PREFIX . strtoupper($name),
            $defines
        );
    }

    /**
     * Returns the global instance of Experiments.
     */
    public static function getInstance(): Experiments {
        if (self::$_instance === null) {
            /** @psalm-suppress RedundantCondition This is not set on tests. */
            if (isset($_REQUEST)) {
                $request = $_REQUEST;
            } else {
                $request = [];
            }

            $session = \OmegaUp\Controllers\Session::getCurrentSession(
                new \OmegaUp\Request($request)
            );
            if (
                isset($request[self::EXPERIMENT_REQUEST_NAME])
                && !empty($request[self::EXPERIMENT_REQUEST_NAME])
                && is_string($request[self::EXPERIMENT_REQUEST_NAME])
            ) {
                $requestExperiments = strval(
                    $request[self::EXPERIMENT_REQUEST_NAME]
                );
            } else {
                $requestExperiments = null;
            }
            self::$_instance = new Experiments(
                $requestExperiments,
                $session['identity']
            );
        }
        return self::$_instance;
    }
}
