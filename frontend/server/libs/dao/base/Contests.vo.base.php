<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Contests.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Contests extends VO {
    /**
     * Constructor de Contests
     *
     * Para construir un objeto de tipo Contests debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = $data['contest_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = $data['acl_id'];
        }
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['start_time'])) {
            $this->start_time = $data['start_time'];
        }
        if (isset($data['finish_time'])) {
            $this->finish_time = $data['finish_time'];
        }
        if (isset($data['last_updated'])) {
            $this->last_updated = $data['last_updated'];
        }
        if (isset($data['window_length'])) {
            $this->window_length = $data['window_length'];
        }
        if (isset($data['rerun_id'])) {
            $this->rerun_id = $data['rerun_id'];
        }
        if (isset($data['admission_mode'])) {
            $this->admission_mode = $data['admission_mode'];
        }
        if (isset($data['alias'])) {
            $this->alias = $data['alias'];
        }
        if (isset($data['scoreboard'])) {
            $this->scoreboard = $data['scoreboard'];
        }
        if (isset($data['points_decay_factor'])) {
            $this->points_decay_factor = $data['points_decay_factor'];
        }
        if (isset($data['partial_score'])) {
            $this->partial_score = $data['partial_score'];
        }
        if (isset($data['submissions_gap'])) {
            $this->submissions_gap = $data['submissions_gap'];
        }
        if (isset($data['feedback'])) {
            $this->feedback = $data['feedback'];
        }
        if (isset($data['penalty'])) {
            $this->penalty = $data['penalty'];
        }
        if (isset($data['penalty_type'])) {
            $this->penalty_type = $data['penalty_type'];
        }
        if (isset($data['penalty_calc_policy'])) {
            $this->penalty_calc_policy = $data['penalty_calc_policy'];
        }
        if (isset($data['show_scoreboard_after'])) {
            $this->show_scoreboard_after = $data['show_scoreboard_after'];
        }
        if (isset($data['urgent'])) {
            $this->urgent = $data['urgent'];
        }
        if (isset($data['languages'])) {
            $this->languages = $data['languages'];
        }
        if (isset($data['recommended'])) {
            $this->recommended = $data['recommended'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['start_time', 'finish_time', 'last_updated']);
        }
    }

    /**
      * El identificador unico para cada concurso
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $contest_id;

    /**
      * La lista de problemas de este concurso
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $acl_id;

    /**
      * El titulo que aparecera en cada concurso
      * @access public
      * @var varchar(256)
      */
    public $title;

    /**
      * Una breve descripcion de cada concurso.
      * @access public
      * @var tinytext
      */
    public $description;

    /**
      * Hora de inicio de este concurso
      * @access public
      * @var timestamp
      */
    public $start_time;

    /**
      * Hora de finalizacion de este concurso
      * @access public
      * @var timestamp
      */
    public $finish_time;

    /**
      * Indica la hora en que se actualizó de privado a público un concurso o viceversa
      * @access public
      * @var timestamp
      */
    public $last_updated;

    /**
      * Indica el tiempo que tiene el usuario para envíar solución, si es NULL entonces será durante todo el tiempo del concurso
      * @access public
      * @var int(11)
      */
    public $window_length;

    /**
      * Este campo es para las repeticiones de algún concurso, Contiene el id del concurso original.
      * @access public
      * @var int(11)
      */
    public $rerun_id;

    /**
      * Modalidad en la que se registra un concurso.
      * @access public
      * @var enum('private','registration','public')
      */
    public $admission_mode;

    /**
      * Almacenará el token necesario para acceder al concurso
      * @access public
      * @var varchar(32)
      */
    public $alias;

    /**
      * Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible
      * @access public
      * @var int(11)
      */
    public $scoreboard;

    /**
      * El factor de decaimiento de los puntos de este concurso. El default es 0 (no decae). TopCoder es 0.7
      * @access public
      * @var double
      */
    public $points_decay_factor;

    /**
      * Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos los casos
      * @access public
      * @var tinyint(1)
      */
    public $partial_score;

    /**
      * Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro
      * @access public
      * @var int(11)
      */
    public $submissions_gap;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('no','yes','partial')
      */
    public $feedback;

    /**
      * Entero indicando el número de minutos con que se penaliza por recibir un no-accepted
      * @access public
      * @var int(11)
      */
    public $penalty;

    /**
      * Indica la política de cálculo de penalty: minutos desde que inició el concurso, minutos desde que se abrió el problema, o tiempo de ejecución (en milisegundos).
      * @access public
      * @var enum('contest_start','problem_open','runtime','none')
      */
    public $penalty_type;

    /**
      * Indica como afecta el penalty al score.
      * @access public
      * @var enum('sum','max')
      */
    public $penalty_calc_policy;

    /**
      * Mostrar el scoreboard automáticamente después del concurso
      * @access public
      * @var tinyint(1)
      */
    public $show_scoreboard_after;

    /**
      * Indica si el concurso es de alta prioridad y requiere mejor QoS.
      * @access public
      * @var tinyint(1)
      */
    public $urgent;

    /**
      * Un filtro (opcional) de qué lenguajes se pueden usar en un concurso
      * @access public
      * @var set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua')
      */
    public $languages;

    /**
      * Mostrar el concurso en la lista de recomendados.
      * @access public
      * @var tinyint(1)
      */
    public $recommended;
}
