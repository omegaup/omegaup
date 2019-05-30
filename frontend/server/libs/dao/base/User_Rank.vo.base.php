<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table User_Rank.
 *
 * VO does not have any behaviour.
 * @access public
 */
class UserRank extends VO {
    /**
     * Constructor de UserRank
     *
     * Para construir un objeto de tipo UserRank debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['rank'])) {
            $this->rank = (int)$data['rank'];
        }
        if (isset($data['problems_solved_count'])) {
            $this->problems_solved_count = (int)$data['problems_solved_count'];
        }
        if (isset($data['score'])) {
            $this->score = (float)$data['score'];
        }
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['country_id'])) {
            $this->country_id = $data['country_id'];
        }
        if (isset($data['state_id'])) {
            $this->state_id = $data['state_id'];
        }
        if (isset($data['school_id'])) {
            $this->school_id = (int)$data['school_id'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime([]);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $rank;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problems_solved_count;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $score;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $username;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(256)
      */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(3)
      */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var char(3)
      */
    public $state_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $school_id;
}
