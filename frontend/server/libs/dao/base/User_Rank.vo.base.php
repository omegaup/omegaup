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
    function __construct(?array $data = null) {
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
    public function toUnixTime(iterable $fields = []) : void {
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
      * @var int
     */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $rank;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $problems_solved_count = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var float
     */
    public $score = 0.00;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $username;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $country_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $state_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $school_id;
}
