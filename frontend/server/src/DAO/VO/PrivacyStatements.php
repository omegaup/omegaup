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
 * Value Object class for table `PrivacyStatements`.
 *
 * @access public
 */
class PrivacyStatements extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'privacystatement_id' => true,
        'git_object_id' => true,
        'type' => true,
    ];

    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['privacystatement_id'])) {
            $this->privacystatement_id = (int)$data['privacystatement_id'];
        }
        if (isset($data['git_object_id'])) {
            $this->git_object_id = strval($data['git_object_id']);
        }
        if (isset($data['type'])) {
            $this->type = strval($data['type']);
        }
    }

    /**
     * Id del documento de privacidad
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $privacystatement_id = 0;

    /**
     * Id de la versión del documento en el que se almacena la nueva política
     *
     * @var string|null
     */
    public $git_object_id = null;

    /**
     * Tipo de documento de privacidad
     *
     * @var string
     */
    public $type = 'privacy_policy';
}
