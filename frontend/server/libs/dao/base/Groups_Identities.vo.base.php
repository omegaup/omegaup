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
            $this->accept_teacher = $data['accept_teacher'];
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
    public $group_id;

    /**
      * Identidad del usuario
      * Llave Primaria
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      * Almacena la respuesta del participante de un curso si está de acuerdo en divulgar su información.
      * @access public
      * @var ?bool
     */
    public $share_user_information;

    /**
      * Id del documento con el consentimiento de privacidad
      * @access public
      * @var ?int
     */
    public $privacystatement_consent_id;

    /**
      * Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.
      * @access public
      * @var ?string
     */
    public $accept_teacher;
}
