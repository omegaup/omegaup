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
            $this->user_id = $data['user_id'];
        }
        if (isset($data['rank'])) {
            $this->rank = $data['rank'];
        }
        if (isset($data['problems_solved_count'])) {
            $this->problems_solved_count = $data['problems_solved_count'];
        }
        if (isset($data['score'])) {
            $this->score = $data['score'];
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
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([]);
        }
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
}
