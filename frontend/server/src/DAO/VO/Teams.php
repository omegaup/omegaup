<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Teams`.
 *
 * @access public
 */
class Teams extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'team_id' => true,
        'team_group_id' => true,
        'identity_id' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['team_id'])) {
            $this->team_id = intval(
                $data['team_id']
            );
        }
        if (isset($data['team_group_id'])) {
            $this->team_group_id = intval(
                $data['team_group_id']
            );
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $team_id = 0;

    /**
     * Id del grupo de equipos
     *
     * @var int|null
     */
    public $team_group_id = null;

    /**
     * La identidad asociada al equipo
     *
     * @var int|null
     */
    public $identity_id = null;
}
