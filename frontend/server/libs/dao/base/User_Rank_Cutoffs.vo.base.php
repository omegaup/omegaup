<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table User_Rank_Cutoffs.
 *
 * VO does not have any behaviour.
 * @access public
 */
class UserRankCutoffs extends VO {
    /**
     * Constructor de UserRankCutoffs
     *
     * Para construir un objeto de tipo UserRankCutoffs debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['score'])) {
            $this->score = $data['score'];
        }
        if (isset($data['percentile'])) {
            $this->percentile = $data['percentile'];
        }
        if (isset($data['classname'])) {
            $this->classname = $data['classname'];
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
      * @access public
      * @var double
      */
    public $score;

    /**
      *  [Campo no documentado]
      * @access public
      * @var double
      */
    public $percentile;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $classname;
}
