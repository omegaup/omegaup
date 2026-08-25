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
        'archived' => true,
        'certificate_cutoff' => true,
        'certificates_status' => true,
        'contest_for_teams' => true,
        'default_show_all_contestants_in_scoreboard' => true,
        'score_mode' => true,
        'plagiarism_threshold' => true,
        'check_plagiarism' => true,
        'orden' => true,
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
            $this->title = is_scalar(
                $data['title']
            ) ? strval($data['title']) : '';
        }
        if (isset($data['description'])) {
            $this->description = is_scalar(
                $data['description']
            ) ? strval($data['description']) : '';
        }
        if (isset($data['start_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['start_time']
             * @var \OmegaUp\Timestamp $this->start_time
             */
            $this->start_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['start_time']
                )
            );
        } else {
            $this->start_time = new \OmegaUp\Timestamp(
                946706400
            ); // 2000-01-01 06:00:00
        }
        if (isset($data['finish_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['finish_time']
             * @var \OmegaUp\Timestamp $this->finish_time
             */
            $this->finish_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['finish_time']
                )
            );
        } else {
            $this->finish_time = new \OmegaUp\Timestamp(
                946706400
            ); // 2000-01-01 06:00:00
        }
        if (isset($data['last_updated'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['last_updated']
             * @var \OmegaUp\Timestamp $this->last_updated
             */
            $this->last_updated = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['last_updated']
                )
            );
        } else {
            $this->last_updated = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
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
            $this->admission_mode = is_scalar(
                $data['admission_mode']
            ) ? strval($data['admission_mode']) : '';
        }
        if (isset($data['alias'])) {
            $this->alias = is_scalar(
                $data['alias']
            ) ? strval($data['alias']) : '';
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
            $this->feedback = is_scalar(
                $data['feedback']
            ) ? strval($data['feedback']) : '';
        }
        if (isset($data['penalty'])) {
            $this->penalty = intval(
                $data['penalty']
            );
        }
        if (isset($data['penalty_type'])) {
            $this->penalty_type = is_scalar(
                $data['penalty_type']
            ) ? strval($data['penalty_type']) : '';
        }
        if (isset($data['penalty_calc_policy'])) {
            $this->penalty_calc_policy = is_scalar(
                $data['penalty_calc_policy']
            ) ? strval($data['penalty_calc_policy']) : '';
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
            $this->languages = is_scalar(
                $data['languages']
            ) ? strval($data['languages']) : '';
        }
        if (isset($data['recommended'])) {
            $this->recommended = boolval(
                $data['recommended']
            );
        }
        if (isset($data['archived'])) {
            $this->archived = boolval(
                $data['archived']
            );
        }
        if (isset($data['certificate_cutoff'])) {
            $this->certificate_cutoff = intval(
                $data['certificate_cutoff']
            );
        }
        if (isset($data['certificates_status'])) {
            $this->certificates_status = is_scalar(
                $data['certificates_status']
            ) ? strval($data['certificates_status']) : '';
        }
        if (isset($data['contest_for_teams'])) {
            $this->contest_for_teams = boolval(
                $data['contest_for_teams']
            );
        }
        if (isset($data['default_show_all_contestants_in_scoreboard'])) {
            $this->default_show_all_contestants_in_scoreboard = boolval(
                $data['default_show_all_contestants_in_scoreboard']
            );
        }
        if (isset($data['score_mode'])) {
            $this->score_mode = is_scalar(
                $data['score_mode']
            ) ? strval($data['score_mode']) : '';
        }
        if (isset($data['plagiarism_threshold'])) {
            $this->plagiarism_threshold = boolval(
                $data['plagiarism_threshold']
            );
        }
        if (isset($data['check_plagiarism'])) {
            $this->check_plagiarism = boolval(
                $data['check_plagiarism']
            );
        }
        if (isset($data['orden'])) {
            $this->orden = intval(
                $data['orden']
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
     * @var \OmegaUp\Timestamp
     */
    public $start_time;  // 2000-01-01 06:00:00

    /**
     * Hora de finalizacion de este concurso
     *
     * @var \OmegaUp\Timestamp
     */
    public $finish_time;  // 2000-01-01 06:00:00

    /**
     * Indica la hora en que se actualizó de privado a público un concurso o viceversa
     *
     * @var \OmegaUp\Timestamp
     */
    public $last_updated;  // CURRENT_TIMESTAMP

    /**
     * Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso
     *
     * @var int|null
     */
    public $window_length = null;

    /**
     * Este campo es para las repeticiones de algún concurso, Contiene el id del concurso original o null en caso de ser un concurso original.
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
     * Indica la cantidad de información que se mostrará en los detalles de un envío. "detailed" muestra el veredicto de la solución caso por caso. "summary" muestra porcentaje de casos que tuvo bien, así como el veredicto del caso con peor calificación. "none" oculta toda la información de los veredictos.
     *
     * @var string
     */
    public $feedback = 'none';

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

    /**
     * Indica si el concurso ha sido archivado por el administrador.
     *
     * @var bool
     */
    public $archived = false;

    /**
     * Número de concursantes a premiar con diplomas que mencionan su lugar en el ranking
     *
     * @var int|null
     */
    public $certificate_cutoff = null;

    /**
     * Estado de la petición de generar diplomas
     *
     * @var string
     */
    public $certificates_status = 'uninitiated';

    /**
     * Bandera que indica si el concurso es para equipos.
     *
     * @var bool
     */
    public $contest_for_teams = false;

    /**
     * Bandera que indica si en el scoreboard se mostrarán todos los concursantes por defecto.
     *
     * @var bool
     */
    public $default_show_all_contestants_in_scoreboard = false;

    /**
     * Indica el tipo de evaluación para el concurso
     *
     * @var string
     */
    public $score_mode = 'partial';

    /**
     * El porcentaje mínimo permitido de similitud entre un par de envíos. Cuando plagio Seleccionado, será 90.
     *
     * @var bool
     */
    public $plagiarism_threshold = false;

    /**
     * Indica si se debe correr el detector de plagios.
     *
     * @var bool
     */
    public $check_plagiarism = false;
    
    /** @var int|null */
    public $orden;

    public function toArray(): array {
        $array = [
            'orden' => $this->orden,
            // ... rest of the array
        ];
        return $array;
    }
}
