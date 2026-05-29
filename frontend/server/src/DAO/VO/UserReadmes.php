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
 * Value Object class for table `User_Readmes`.
 *
 * @access public
 */
class UserReadmes extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'readme_id' => true,
        'user_id' => true,
        'content' => true,
        'is_visible' => true,
        'last_edit_time' => true,
        'report_count' => true,
        'is_disabled' => true,
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
        if (isset($data['readme_id'])) {
            $this->readme_id = intval(
                $data['readme_id']
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['content'])) {
            $this->content = is_scalar(
                $data['content']
            ) ? strval($data['content']) : '';
        }
        if (isset($data['is_visible'])) {
            $this->is_visible = boolval(
                $data['is_visible']
            );
        }
        if (isset($data['last_edit_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['last_edit_time']
             * @var \OmegaUp\Timestamp $this->last_edit_time
             */
            $this->last_edit_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['last_edit_time']
                )
            );
        } else {
            $this->last_edit_time = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['report_count'])) {
            $this->report_count = intval(
                $data['report_count']
            );
        }
        if (isset($data['is_disabled'])) {
            $this->is_disabled = boolval(
                $data['is_disabled']
            );
        }
    }

    /**
     * Identificador único del README
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $readme_id = 0;

    /**
     * Usuario dueño del README
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * Contenido del README en Markdown
     *
     * @var string|null
     */
    public $content = null;

    /**
     * Indica si el README es visible (1 = sí, 0 = no)
     *
     * @var bool
     */
    public $is_visible = true;

    /**
     * Última vez que se editó el README
     *
     * @var \OmegaUp\Timestamp
     */
    public $last_edit_time;  // CURRENT_TIMESTAMP

    /**
     * Número de reportes recibidos
     *
     * @var int
     */
    public $report_count = 0;

    /**
     * Indica si el README está deshabilitado por exceso de reportes (1 = sí, 0 = no)
     *
     * @var bool
     */
    public $is_disabled = false;
}
