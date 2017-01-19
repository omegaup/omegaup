<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Groups_Scoreboards_Contests.
 *
 * VO does not have any behaviour.
 * @access public
 */
class GroupsScoreboardsContests extends VO {
    /**
     * Constructor de GroupsScoreboardsContests
     *
     * Para construir un objeto de tipo GroupsScoreboardsContests debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['group_scoreboard_id'])) {
            $this->group_scoreboard_id = $data['group_scoreboard_id'];
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = $data['contest_id'];
        }
        if (isset($data['only_ac'])) {
            $this->only_ac = $data['only_ac'];
        }
        if (isset($data['weight'])) {
            $this->weight = $data['weight'];
        }
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto GroupsScoreboardsContests en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode([
            'group_scoreboard_id' => $this->group_scoreboard_id,
            'contest_id' => $this->contest_id,
            'only_ac' => $this->only_ac,
            'weight' => $this->weight,
        ]);
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
    public $group_scoreboard_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $contest_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $only_ac;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $weight;
}
