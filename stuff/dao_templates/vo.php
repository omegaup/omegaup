<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table {{ table.name }}.
 *
 * VO does not have any behaviour.
 * @access public
 */
class {{ table.class_name }} extends VO {
    /**
     * Constructor de {{ table.class_name }}
     *
     * Para construir un objeto de tipo {{ table.class_name }} debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
{%- for column in table.columns %}
        if (isset($data['{{ column.name }}'])) {
            $this->{{ column.name }} = $data['{{ column.name }}'];
        }
{%- endfor %}
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([{{ table.columns|selectattr('type', 'equalto', ('timestamp',))|map(attribute='name')|listformat("'{}'")|join(', ') }}]);
        }
    }
{%- for column in table.columns %}

    /**
      * {{ column.comment or ' [Campo no documentado]' }}
{%- if column.primary_key %}
      * Llave Primaria
{%- endif %}
{%- if column.auto_increment %}
      * Auto Incremento
{%- endif %}
      * @access public
      * @var {{ column.type|join('')|lower }}
      */
    public ${{ column.name }};
{%- endfor %}
}

