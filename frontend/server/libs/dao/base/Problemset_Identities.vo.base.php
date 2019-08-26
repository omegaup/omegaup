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
class ProblemsetIdentities extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'identity_id' => true,
        'problemset_id' => true,
        'access_time' => true,
        'end_time' => true,
        'score' => true,
        'time' => true,
        'share_user_information' => true,
        'privacystatement_consent_id' => true,
        'is_invited' => true,
    ];

    /**
     * Constructor de ProblemsetIdentities
     *
     * Para construir un objeto de tipo ProblemsetIdentities debera llamarse a el constructor
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
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['access_time'])) {
            /**
             * @var string|int|float $data['access_time']
             * @var int $this->access_time
             */
            $this->access_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['access_time']);
        }
        if (isset($data['end_time'])) {
            /**
             * @var string|int|float $data['end_time']
             * @var int $this->end_time
             */
            $this->end_time = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['end_time']);
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
     * Identidad del usuario
     * Llave Primaria
     *
     * @var int|null
     */
    public $identity_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * Hora a la que entró el usuario al concurso
     *
     * @var int|null
     */
    public $access_time = null;

    /**
     * Hora en la que finaliza un concurso para el usuario cuando se habilita la opción de inicios diferentes
     *
     * @var int|null
     */
    public $end_time = null;

    /**
     * Indica el puntaje que obtuvo el usuario en el concurso
     *
     * @var int
     */
    public $score = 1;

    /**
     * Indica el tiempo que acumulo en usuario en el concurso
     *
     * @var int
     */
    public $time = 1;

    /**
     * Almacena la respuesta del participante de un concurso si está de acuerdo en divulgar su información.
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
     * Indica si la identidad ingresará al concurso por invitación o lo encontró en el listado de concursos públicos
     *
     * @var bool
     */
    public $is_invited = false;
}
