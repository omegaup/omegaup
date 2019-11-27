<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Contests`.
 *
 * @access public
 */
class Contests extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'contest_id' => true,
        'problemset_id' => true,
        'acl_id' => true,
        'title' => true,
        'description' => true,
        'start_time' => true,
        'finish_time' => true,
        'last_updated' => true,
        'window_length' => true,
        'rerun_id' => true,
        'admission_mode' => true,
        'alias' => true,
        'scoreboard' => true,
        'points_decay_factor' => true,
        'partial_score' => true,
        'submissions_gap' => true,
        'feedback' => true,
        'penalty' => true,
        'penalty_type' => true,
        'penalty_calc_policy' => true,
        'show_scoreboard_after' => true,
        'urgent' => true,
        'languages' => true,
        'recommended' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = intval(
                $data['contest_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval(
                $data['acl_id']
            );
        }
        if (isset($data['title'])) {
            $this->title = strval(
                $data['title']
            );
        }
        if (isset($data['description'])) {
            $this->description = strval(
                $data['description']
            );
        }
        if (isset($data['start_time'])) {
            /**
             * @var string|int|float $data['start_time']
             * @var int $this->start_time
             */
            $this->start_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['start_time']
                )
            );
        }
        if (isset($data['finish_time'])) {
            /**
             * @var string|int|float $data['finish_time']
             * @var int $this->finish_time
             */
            $this->finish_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['finish_time']
                )
            );
        }
        if (isset($data['last_updated'])) {
            /**
             * @var string|int|float $data['last_updated']
             * @var int $this->last_updated
             */
            $this->last_updated = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['last_updated']
                )
            );
        } else {
            $this->last_updated = \OmegaUp\Time::get();
        }
        if (isset($data['window_length'])) {
            $this->window_length = intval(
                $data['window_length']
            );
        }
        if (isset($data['rerun_id'])) {
            $this->rerun_id = intval(
                $data['rerun_id']
            );
        }
        if (isset($data['admission_mode'])) {
            $this->admission_mode = strval(
                $data['admission_mode']
            );
        }
        if (isset($data['alias'])) {
            $this->alias = strval(
                $data['alias']
            );
        }
        if (isset($data['scoreboard'])) {
            $this->scoreboard = intval(
                $data['scoreboard']
            );
        }
        if (isset($data['points_decay_factor'])) {
            $this->points_decay_factor = floatval(
                $data['points_decay_factor']
            );
        }
        if (isset($data['partial_score'])) {
            $this->partial_score = boolval(
                $data['partial_score']
            );
        }
        if (isset($data['submissions_gap'])) {
            $this->submissions_gap = intval(
                $data['submissions_gap']
            );
        }
        if (isset($data['feedback'])) {
            $this->feedback = strval(
                $data['feedback']
            );
        }
        if (isset($data['penalty'])) {
            $this->penalty = intval(
                $data['penalty']
            );
        }
        if (isset($data['penalty_type'])) {
            $this->penalty_type = strval(
                $data['penalty_type']
            );
        }
        if (isset($data['penalty_calc_policy'])) {
            $this->penalty_calc_policy = strval(
                $data['penalty_calc_policy']
            );
        }
        if (isset($data['show_scoreboard_after'])) {
            $this->show_scoreboard_after = boolval(
                $data['show_scoreboard_after']
            );
        }
        if (isset($data['urgent'])) {
            $this->urgent = boolval(
                $data['urgent']
            );
        }
        if (isset($data['languages'])) {
            $this->languages = strval(
                $data['languages']
            );
        }
        if (isset($data['recommended'])) {
            $this->recommended = boolval(
                $data['recommended']
            );
        }
    }

    /**
     * El identificador unico para cada concurso
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $contest_id = 0;

    /**
     * La lista de problemas de este concurso
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * El titulo que aparecera en cada concurso
     *
     * @var string|null
     */
    public $title = null;

    /**
     * Una breve descripcion de cada concurso.
     *
     * @var string|null
     */
    public $description = null;

    /**
     * Hora de inicio de este concurso
     *
     * @var int
     */
    public $start_time = 946706400; // 2000-01-01 06:00:00

    /**
     * Hora de finalizacion de este concurso
     *
     * @var int
     */
    public $finish_time = 946706400; // 2000-01-01 06:00:00

    /**
     * Indica la hora en que se actualizó de privado a público un concurso o viceversa
     *
     * @var int
     */
    public $last_updated;  // CURRENT_TIMESTAMP

    /**
     * Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso
     *
     * @var int|null
     */
    public $window_length = null;

    /**
     * Este campo es para las repeticiones de algún concurso, Contiene el id del concurso original.
     *
     * @var int|null
     */
    public $rerun_id = null;

    /**
     * Modalidad en la que se registra un concurso.
     *
     * @var string
     */
    public $admission_mode = 'private';

    /**
     * Almacenará el token necesario para acceder al concurso
     *
     * @var string|null
     */
    public $alias = null;

    /**
     * Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible
     *
     * @var int
     */
    public $scoreboard = 1;

    /**
     * El factor de decaimiento de los puntos de este concurso. El default es 0 (no decae). TopCoder es 0.7
     *
     * @var float
     */
    public $points_decay_factor = 0.00;

    /**
     * Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos
     *
     * @var bool
     */
    public $partial_score = true;

    /**
     * Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro
     *
     * @var int
     */
    public $submissions_gap = 60;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $feedback = null;

    /**
     * Entero indicando el número de minutos con que se penaliza por recibir un no-accepted
     *
     * @var int
     */
    public $penalty = 1;

    /**
     * Indica la política de cálculo de penalty: minutos desde que inició el concurso, minutos desde que se abrió el problema, o tiempo de ejecución (en milisegundos).
     *
     * @var string|null
     */
    public $penalty_type = null;

    /**
     * Indica como afecta el penalty al score.
     *
     * @var string|null
     */
    public $penalty_calc_policy = null;

    /**
     * Mostrar el scoreboard automáticamente después del concurso
     *
     * @var bool
     */
    public $show_scoreboard_after = true;

    /**
     * Indica si el concurso es de alta prioridad y requiere mejor QoS.
     *
     * @var bool
     */
    public $urgent = false;

    /**
     * Un filtro (opcional) de qué lenguajes se pueden usar en un concurso
     *
     * @var string|null
     */
    public $languages = null;

    /**
     * Mostrar el concurso en la lista de recomendados.
     *
     * @var bool
     */
    public $recommended = false;
}
