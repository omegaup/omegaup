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
{%- if 'tinyint' in column.type %}
            $this->{{ column.name }} = $data['{{ column.name }}'] == '1';
{%- elif 'int' in column.type %}
            $this->{{ column.name }} = (int)$data['{{ column.name }}'];
{%- elif 'double' in column.type %}
            $this->{{ column.name }} = (float)$data['{{ column.name }}'];
{%- else %}
            $this->{{ column.name }} = $data['{{ column.name }}'];
{%- endif %}
        }
{%- endfor %}
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime([{{ table.columns|selectattr('type', 'equalto', ('timestamp',))|map(attribute='name')|listformat("'{}'")|join(', ') }}]);
            return;
        }
        parent::toUnixTime($fields);
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

