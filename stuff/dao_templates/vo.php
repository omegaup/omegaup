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
class {{ table.class_name }} extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
{%- for column in table.columns %}
        '{{ column.name }}' => true,
{%- endfor %}
    ];

    /**
     * Constructor de {{ table.class_name }}
     *
     * Para construir un objeto de tipo {{ table.class_name }} debera llamarse a el constructor
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
{%- for column in table.columns %}
        if (isset($data['{{ column.name }}'])) {
{%- if 'timestamp' in column.type or 'datetime' in column.type %}
            /**
             * @var string|int|float $data['{{ column.name }}']
             * @var int $this->{{ column.name }}
             */
            $this->{{ column.name }} = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['{{ column.name }}']);
{%- elif column.php_primitive_type == 'bool' %}
            $this->{{ column.name }} = boolval($data['{{ column.name }}']);
{%- elif column.php_primitive_type in ('int', 'float') %}
            $this->{{ column.name }} = ({{ column.php_primitive_type }})$data['{{ column.name }}'];
{%- else %}
            $this->{{ column.name }} = strval($data['{{ column.name }}']);
{%- endif %}
    {%- if column.default == 'CURRENT_TIMESTAMP' %}
        } else {
            $this->{{ column.name }} = \OmegaUp\Time::get();
    {%- endif %}
        }
{%- endfor %}
    }
{%- for column in table.columns %}

    /**
     * {{ column.comment or '[Campo no documentado]' }}
{%- if column.primary_key %}
     * Llave Primaria
{%- endif %}
{%- if column.auto_increment %}
     * Auto Incremento
{%- endif %}
     *
     * @var {{ column.php_primitive_type }}{% if not column.default %}|null{% endif %}
     */
{%- if column.default %}
{%- if column.default == 'CURRENT_TIMESTAMP' %}
    public ${{ column.name }};  // CURRENT_TIMESTAMP
{%- elif 'timestamp' in column.type %}
    public ${{ column.name }} = {{ column.default|strtotime }}; // {{ column.default }}
{%- elif column.php_primitive_type == 'bool' %}
    public ${{ column.name }} = {{ 'true' if column.default == '1' else 'false' }};
{%- elif column.php_primitive_type == 'int' %}
    public ${{ column.name }} = {{ '%d'|format(column.default|int) }};
{%- elif column.php_primitive_type == 'float' %}
    public ${{ column.name }} = {{ '%.2f'|format(column.default|float) }};
{%- else %}
    public ${{ column.name }} = '{{ column.default }}';
{%- endif %}
{%- elif column.auto_increment %}
    public ${{ column.name }} = 0;
{%- else %}
    public ${{ column.name }} = null;
{%- endif %}
{%- endfor %}
}

