<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problems.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Problems extends VO {
    /**
     * Constructor de Problems
     *
     * Para construir un objeto de tipo Problems debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = (int)$data['acl_id'];
        }
        if (isset($data['visibility'])) {
            $this->visibility = (int)$data['visibility'];
        }
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['alias'])) {
            $this->alias = $data['alias'];
        }
        if (isset($data['commit'])) {
            $this->commit = $data['commit'];
        }
        if (isset($data['current_version'])) {
            $this->current_version = $data['current_version'];
        }
        if (isset($data['languages'])) {
            $this->languages = $data['languages'];
        }
        if (isset($data['input_limit'])) {
            $this->input_limit = (int)$data['input_limit'];
        }
        if (isset($data['visits'])) {
            $this->visits = (int)$data['visits'];
        }
        if (isset($data['submissions'])) {
            $this->submissions = (int)$data['submissions'];
        }
        if (isset($data['accepted'])) {
            $this->accepted = (int)$data['accepted'];
        }
        if (isset($data['difficulty'])) {
            $this->difficulty = (float)$data['difficulty'];
        }
        if (isset($data['creation_date'])) {
            $this->creation_date = $data['creation_date'];
        }
        if (isset($data['source'])) {
            $this->source = $data['source'];
        }
        if (isset($data['order'])) {
            $this->order = $data['order'];
        }
        if (isset($data['deprecated'])) {
            $this->deprecated = $data['deprecated'] == '1';
        }
        if (isset($data['email_clarifications'])) {
            $this->email_clarifications = $data['email_clarifications'] == '1';
        }
        if (isset($data['quality'])) {
            $this->quality = (float)$data['quality'];
        }
        if (isset($data['quality_histogram'])) {
            $this->quality_histogram = $data['quality_histogram'];
        }
        if (isset($data['difficulty_histogram'])) {
            $this->difficulty_histogram = $data['difficulty_histogram'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime(['creation_date']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $acl_id;

    /**
      * -1 banned, 0 private, 1 public, 2 recommended
      * @access public
      * @var int(1)
      */
    public $visibility;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(256)
      */
    public $title;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(32)
      */
    public $alias;

    /**
      * El hash SHA1 del commit en la rama master del problema.
      * @access public
      * @var char(40)
      */
    public $commit;

    /**
      * El hash SHA1 del árbol de la rama private.
      * @access public
      * @var char(40)
      */
    public $current_version;

    /**
      *  [Campo no documentado]
      * @access public
      * @var set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua')
      */
    public $languages;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $input_limit;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $visits;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $submissions;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $accepted;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $difficulty;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $creation_date;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(256)
      */
    public $source;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('normal','inverse')
      */
    public $order;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $deprecated;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $email_clarifications;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $quality;

    /**
      * Valores del histograma de calidad del problema.
      * @access public
      * @var text
      */
    public $quality_histogram;

    /**
      * Valores del histograma de dificultad del problema.
      * @access public
      * @var text
      */
    public $difficulty_histogram;
}
