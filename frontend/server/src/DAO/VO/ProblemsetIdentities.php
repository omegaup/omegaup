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
 * Value Object class for table `Problemset_Identities`.
 *
 * @access public
 */
class ProblemsetIdentities extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
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
        if (isset($data['identity_id'])) {
            $this->identity_id = intval(
                $data['identity_id']
            );
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['access_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['access_time']
             * @var \OmegaUp\Timestamp $this->access_time
             */
            $this->access_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['access_time']
                )
            );
        }
        if (isset($data['end_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['end_time']
             * @var \OmegaUp\Timestamp $this->end_time
             */
            $this->end_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['end_time']
                )
            );
        }
        if (isset($data['score'])) {
            $this->score = intval(
                $data['score']
            );
        }
        if (isset($data['time'])) {
            $this->time = intval(
                $data['time']
            );
        }
        if (isset($data['share_user_information'])) {
            $this->share_user_information = boolval(
                $data['share_user_information']
            );
        }
        if (isset($data['privacystatement_consent_id'])) {
            $this->privacystatement_consent_id = intval(
                $data['privacystatement_consent_id']
            );
        }
        if (isset($data['is_invited'])) {
            $this->is_invited = boolval(
                $data['is_invited']
            );
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
     * @var \OmegaUp\Timestamp|null
     */
    public $access_time = null;

    /**
     * Hora en la que finaliza un concurso para el usuario cuando se habilita la opción de inicios diferentes
     *
     * @var \OmegaUp\Timestamp|null
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
