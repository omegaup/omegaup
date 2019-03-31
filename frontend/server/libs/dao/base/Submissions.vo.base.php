<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Submissions.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Submissions extends VO {
    const FIELD_NAMES = [
        'submission_id' => true,
        'current_run_id' => true,
        'identity_id' => true,
        'problem_id' => true,
        'problemset_id' => true,
        'guid' => true,
        'language' => true,
        'time' => true,
        'submit_delay' => true,
        'type' => true,
    ];

    /**
     * Constructor de Submissions
     *
     * Para construir un objeto de tipo Submissions debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['submission_id'])) {
            $this->submission_id = (int)$data['submission_id'];
        }
        if (isset($data['current_run_id'])) {
            $this->current_run_id = (int)$data['current_run_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['guid'])) {
            $this->guid = $data['guid'];
        }
        if (isset($data['language'])) {
            $this->language = $data['language'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['submit_delay'])) {
            $this->submit_delay = (int)$data['submit_delay'];
        }
        if (isset($data['type'])) {
            $this->type = $data['type'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $submission_id;

    /**
      * La evaluación actual del envío
      * @access public
      * @var ?int
     */
    public $current_run_id;

    /**
      * Identidad del usuario
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $guid;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $language;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $time = null;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $submit_delay = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $type = 'normal';
}
