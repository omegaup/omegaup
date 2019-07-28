<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Groups_Scoreboards_Problemsets.
 *
 * VO does not have any behaviour.
 * @access public
 */
class GroupsScoreboardsProblemsets extends VO {
    /**
     * Constructor de GroupsScoreboardsProblemsets
     *
     * Para construir un objeto de tipo GroupsScoreboardsProblemsets debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['group_scoreboard_id'])) {
            $this->group_scoreboard_id = (int)$data['group_scoreboard_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['only_ac'])) {
            $this->only_ac = boolval($data['only_ac']);
        }
        if (isset($data['weight'])) {
            $this->weight = (int)$data['weight'];
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
    public $group_scoreboard_id;

    /**
      * Conjunto de problemas del scoreboard
      * Llave Primaria
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var bool
     */
    public $only_ac = false;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $weight = 1;
}
