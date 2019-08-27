<?php
/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Announcement`.
 *
 * @access public
 */
class Announcement extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'announcement_id' => true,
        'user_id' => true,
        'time' => true,
        'description' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['announcement_id'])) {
            $this->announcement_id = (int)$data['announcement_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['time'])) {
            /**
             * @var string|int|float $data['time']
             * @var int $this->time
             */
            $this->time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['time']);
        } else {
            $this->time = \OmegaUp\Time::get();
        }
        if (isset($data['description'])) {
            $this->description = strval($data['description']);
        }
    }

    /**
     * Identificador del aviso
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $announcement_id = 0;

    /**
     * UserID del autor de este aviso
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Fecha de creacion de este aviso
     *
     * @var int
     */
    public $time;  // CURRENT_TIMESTAMP

    /**
     * Mensaje de texto del aviso
     *
     * @var string|null
     */
    public $description = null;
}
