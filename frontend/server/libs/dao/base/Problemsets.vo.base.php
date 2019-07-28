<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemsets.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Problemsets extends VO {
    /**
     * Constructor de Problemsets
     *
     * Para construir un objeto de tipo Problemsets debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = (int)$data['acl_id'];
        }
        if (isset($data['access_mode'])) {
            $this->access_mode = $data['access_mode'];
        }
        if (isset($data['languages'])) {
            $this->languages = $data['languages'];
        }
        if (isset($data['needs_basic_information'])) {
            $this->needs_basic_information = boolval($data['needs_basic_information']);
        }
        if (isset($data['requests_user_information'])) {
            $this->requests_user_information = $data['requests_user_information'];
        }
        if (isset($data['scoreboard_url'])) {
            $this->scoreboard_url = $data['scoreboard_url'];
        }
        if (isset($data['scoreboard_url_admin'])) {
            $this->scoreboard_url_admin = $data['scoreboard_url_admin'];
        }
        if (isset($data['type'])) {
            $this->type = $data['type'];
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = (int)$data['contest_id'];
        }
        if (isset($data['assignment_id'])) {
            $this->assignment_id = (int)$data['assignment_id'];
        }
        if (isset($data['interview_id'])) {
            $this->interview_id = (int)$data['interview_id'];
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
      * El identificador único para cada conjunto de problemas
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      * La lista de control de acceso compartida con su container
      * @access public
      * @var int
     */
    public $acl_id;

    /**
      * La modalidad de acceso a este conjunto de problemas
      * @access public
      * @var string
     */
    public $access_mode = 'public';

    /**
      * Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas
      * @access public
      * @var ?string
     */
    public $languages;

    /**
      * Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un concurso sólo si ya llenó su información de perfil
      * @access public
      * @var bool
     */
    public $needs_basic_information = false;

    /**
      * Se solicita información de los participantes para contactarlos posteriormente.
      * @access public
      * @var string
     */
    public $requests_user_information = 'no';

    /**
      * Token para la url del scoreboard en problemsets
      * @access public
      * @var string
     */
    public $scoreboard_url;

    /**
      * Token para la url del scoreboard de admin en problemsets
      * @access public
      * @var string
     */
    public $scoreboard_url_admin;

    /**
      * Almacena el tipo de problemset que se ha creado
      * @access public
      * @var string
     */
    public $type = 'Contest';

    /**
      * Id del concurso
      * @access public
      * @var ?int
     */
    public $contest_id;

    /**
      * Id del curso
      * @access public
      * @var ?int
     */
    public $assignment_id;

    /**
      * Id de la entrevista
      * @access public
      * @var ?int
     */
    public $interview_id;
}
