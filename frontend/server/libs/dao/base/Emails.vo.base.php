<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Emails.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Emails extends VO {
    const FIELD_NAMES = [
        'email_id' => true,
        'email' => true,
        'user_id' => true,
    ];

    /**
     * Constructor de Emails
     *
     * Para construir un objeto de tipo Emails debera llamarse a el constructor
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
        if (isset($data['email_id'])) {
            $this->email_id = (int)$data['email_id'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $email_id = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $email;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $user_id;
}
