<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Identities.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetIdentities extends VO {
    /**
     * Constructor de ProblemsetIdentities
     *
     * Para construir un objeto de tipo ProblemsetIdentities debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['access_time'])) {
            $this->access_time = $data['access_time'];
        }
        if (isset($data['score'])) {
            $this->score = (int)$data['score'];
        }
        if (isset($data['time'])) {
            $this->time = (int)$data['time'];
        }
        if (isset($data['share_user_information'])) {
            $this->share_user_information = boolval($data['share_user_information']);
        }
        if (isset($data['privacystatement_consent_id'])) {
            $this->privacystatement_consent_id = (int)$data['privacystatement_consent_id'];
        }
        if (isset($data['is_invited'])) {
            $this->is_invited = boolval($data['is_invited']);
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
      * Identidad del usuario
      * Llave Primaria
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      * Hora a la que entró el usuario al concurso
      * @access public
      * @var ?string
     */
    public $access_time;

    /**
      * Indica el puntaje que obtuvo el usuario en el concurso
      * @access public
      * @var int
     */
    public $score = 1;

    /**
      * Indica el tiempo que acumulo en usuario en el concurso
      * @access public
      * @var int
     */
    public $time = 1;

    /**
      * Almacena la respuesta del participante de un concurso si está de acuerdo en divulgar su información.
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
      * Indica si la identidad ingresará al concurso por invitación o lo encontró en el listado de concursos públicos
      * @access public
      * @var bool
     */
    public $is_invited = false;
}
