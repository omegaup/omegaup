<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Groups_Identities.
 *
 * VO does not have any behaviour.
 * @access public
 */
class GroupsIdentities extends VO {
    const FIELD_NAMES = [
        'group_id' => true,
        'identity_id' => true,
        'share_user_information' => true,
        'privacystatement_consent_id' => true,
        'accept_teacher' => true,
    ];

    /**
     * Constructor de GroupsIdentities
     *
     * Para construir un objeto de tipo GroupsIdentities debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['group_id'])) {
            $this->group_id = (int)$data['group_id'];
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['share_user_information'])) {
            $this->share_user_information = boolval($data['share_user_information']);
        }
        if (isset($data['privacystatement_consent_id'])) {
            $this->privacystatement_consent_id = (int)$data['privacystatement_consent_id'];
        }
        if (isset($data['accept_teacher'])) {
            $this->accept_teacher = strval($data['accept_teacher']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $group_id = null;

    /**
     * Identidad del usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.
     *
     * @var bool|null
     */
    public $share_user_information = null;

    /**
     * Id del documento con el consentimiento de privacidad
     *
     * @var int|null
     */
    public $privacystatement_consent_id = null;

    /**
     * Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.
     *
     * @var string|null
     */
    public $accept_teacher = null;
}
