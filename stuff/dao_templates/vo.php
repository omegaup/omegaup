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
 * Value Object class for table `{{ table.name }}`.
 *
 * @access public
 */
class {{ table.class_name }} extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
{%- for column in table.columns %}
        '{{ column.name }}' => true,
{%- endfor %}
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
{%- for column in table.columns %}
        if (isset($data['{{ column.name }}'])) {
{%- if 'timestamp' in column.type or 'datetime' in column.type %}
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['{{ column.name }}']
             * @var \OmegaUp\Timestamp $this->{{ column.name }}
             */
            $this->{{ column.name }} = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['{{ column.name }}']
                )
            );
{%- elif column.php_primitive_type == 'bool' %}
            $this->{{ column.name }} = boolval(
                $data['{{ column.name }}']
            );
{%- elif column.php_primitive_type == 'int' %}
            $this->{{ column.name }} = intval(
                $data['{{ column.name }}']
            );
{%- elif column.php_primitive_type == 'float' %}
            $this->{{ column.name }} = floatval(
                $data['{{ column.name }}']
            );
{%- else %}
            $this->{{ column.name }} = is_scalar(
                $data['{{ column.name }}']
            ) ? strval($data['{{ column.name }}']) : '';
{%- endif %}
    {%- if column.default == 'CURRENT_TIMESTAMP' %}
        } else {
            $this->{{ column.name }} = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
    {%- elif column.default and ('timestamp' in column.type or 'datetime' in column.type) %}
        } else {
            $this->{{ column.name }} = new \OmegaUp\Timestamp(
                {{ column.default|strtotime }}
            ); // {{ column.default }}
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
    public ${{ column.name }};  // {{ column.default }}
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

