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
 * Value Object class for table `Interviews`.
 *
 * @access public
 */
class Interviews extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'interview_id' => true,
        'problemset_id' => true,
        'acl_id' => true,
        'alias' => true,
        'title' => true,
        'description' => true,
        'window_length' => true,
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
        if (isset($data['interview_id'])) {
            $this->interview_id = intval(
                $data['interview_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval(
                $data['acl_id']
            );
        }
        if (isset($data['alias'])) {
            $this->alias = is_scalar(
                $data['alias']
            ) ? strval($data['alias']) : '';
        }
        if (isset($data['title'])) {
            $this->title = is_scalar(
                $data['title']
            ) ? strval($data['title']) : '';
        }
        if (isset($data['description'])) {
            $this->description = is_scalar(
                $data['description']
            ) ? strval($data['description']) : '';
        }
        if (isset($data['window_length'])) {
            $this->window_length = intval(
                $data['window_length']
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
    public $interview_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * La lista de control de acceso del problema
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * El alias de la entrevista
     *
     * @var string|null
     */
    public $alias = null;

    /**
     * El titulo de la entrevista.
     *
     * @var string|null
     */
    public $title = null;

    /**
     * Una breve descripcion de la entrevista.
     *
     * @var string|null
     */
    public $description = null;

    /**
     * Indica el tiempo que tiene el usuario para envíar soluciones.
     *
     * @var int|null
     */
    public $window_length = null;
}
