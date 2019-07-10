<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Users.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Users extends VO {
    /**
     * Constructor de Users
     *
     * Para construir un objeto de tipo Users debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
        if (isset($data['facebook_user_id'])) {
            $this->facebook_user_id = $data['facebook_user_id'];
        }
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }
        if (isset($data['git_token'])) {
            $this->git_token = $data['git_token'];
        }
        if (isset($data['main_email_id'])) {
            $this->main_email_id = (int)$data['main_email_id'];
        }
        if (isset($data['main_identity_id'])) {
            $this->main_identity_id = (int)$data['main_identity_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['school_id'])) {
            $this->school_id = (int)$data['school_id'];
        }
        if (isset($data['scholar_degree'])) {
            $this->scholar_degree = $data['scholar_degree'];
        }
        if (isset($data['graduation_date'])) {
            $this->graduation_date = $data['graduation_date'];
        }
        if (isset($data['birth_date'])) {
            $this->birth_date = $data['birth_date'];
        }
        if (isset($data['gender'])) {
            $this->gender = $data['gender'];
        }
        if (isset($data['verified'])) {
            $this->verified = $data['verified'] == '1';
        }
        if (isset($data['verification_id'])) {
            $this->verification_id = $data['verification_id'];
        }
        if (isset($data['reset_digest'])) {
            $this->reset_digest = $data['reset_digest'];
        }
        if (isset($data['reset_sent_at'])) {
            $this->reset_sent_at = $data['reset_sent_at'];
        }
        if (isset($data['hide_problem_tags'])) {
            $this->hide_problem_tags = $data['hide_problem_tags'] == '1';
        }
        if (isset($data['in_mailing_list'])) {
            $this->in_mailing_list = $data['in_mailing_list'] == '1';
        }
        if (isset($data['is_private'])) {
            $this->is_private = $data['is_private'] == '1';
        }
        if (isset($data['preferred_language'])) {
            $this->preferred_language = $data['preferred_language'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime([]);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $username;

    /**
      * Facebook ID for this user.
      * @access public
      * @var varchar(20)
      */
    public $facebook_user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(100)
      */
    public $password;

    /**
      * Token de acceso para git
      * @access public
      * @var char(40)
      */
    public $git_token;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $main_email_id;

    /**
      * Identidad principal del usuario
      * @access public
      * @var int(11)
      */
    public $main_identity_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(256)
      */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $school_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('none','early_childhood','pre_primary','primary','lower_secondary','upper_secondary','post_secondary','tertiary','bachelors','master','doctorate')
      */
    public $scholar_degree;

    /**
      *  [Campo no documentado]
      * @access public
      * @var date
      */
    public $graduation_date;

    /**
      *  [Campo no documentado]
      * @access public
      * @var date
      */
    public $birth_date;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('female','male','other','decline')
      */
    public $gender;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $verified;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(50)
      */
    public $verification_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(45)
      */
    public $reset_digest;

    /**
      *  [Campo no documentado]
      * @access public
      * @var datetime
      */
    public $reset_sent_at;

    /**
      * Determina si el usuario quiere ocultar las etiquetas de los problemas
      * @access public
      * @var tinyint(1)
      */
    public $hide_problem_tags;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $in_mailing_list;

    /**
      * Determina si el usuario eligió no compartir su información de manera pública
      * @access public
      * @var tinyint(1)
      */
    public $is_private;

    /**
      * El lenguaje de programación de preferencia de este usuario
      * @access public
      * @var enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua')
      */
    public $preferred_language;
}
