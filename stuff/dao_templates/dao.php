<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\Base;

/** {{ table.class_name }} Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\{{ table.class_name }}}.
 * @access public
 * @abstract
 */
abstract class {{ table.class_name }} {
{%- if table.columns|selectattr('primary_key')|list and table.columns|rejectattr('primary_key')|list and not table.columns|selectattr('auto_increment')|list %}
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link \OmegaUp\DAO\VO\{{ table.class_name }}}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces replace() creará una nueva fila.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException si las columnas de la
     * llave primaria están vacías.
     *
     * @param \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }} El
     * objeto de tipo {@link \OmegaUp\DAO\VO\{{ table.class_name }}}.
     *
     * @return int Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function replace(
        \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }}
    ): int {
        if (
            {{ table.columns|selectattr('primary_key')|listformat('empty(${table.name}->{.name})', table=table)|join(' ||\n            ') }}
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
        $sql = '
            REPLACE INTO
                {{ table.name }} (
                    {{ table.columns|listformat('`{.name}`', table=table)|join(',\n                    ') }}
                ) VALUES (
                    {{ table.columns|listformat('?', table=table)|join(',\n                    ') }}
                );';
        $params = [
  {%- for column in table.columns %}
    {%- if 'timestamp' in column.type or 'datetime' in column.type %}
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                ${{ table.name }}->{{ column.name }}
            ),
    {%- elif column.php_type in ('?bool', '?int') and not column.primary_key %}
            (
                !is_null(${{ table.name }}->{{ column.name }}) ?
                intval(${{ table.name }}->{{ column.name }}) :
                null
            ),
    {%- elif column.php_type == '?float' %}
            (
                !is_null(${{ table.name }}->{{ column.name }}) ?
                floatval(${{ table.name }}->{{ column.name }}) :
                null
            ),
    {%- elif column.php_type in ('bool', 'int') %}
            intval(${{ table.name }}->{{ column.name }}),
    {%- elif column.php_type == 'float' %}
            floatval(${{ table.name }}->{{ column.name }}),
    {%- else %}
            ${{ table.name }}->{{ column.name }},
    {%- endif %}
  {%- endfor %}
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
{% endif %}
{%- if table.columns|selectattr('primary_key')|list and table.columns|rejectattr('primary_key')|list %}
    /**
     * Actualizar registros.
     *
     * @param \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }} El objeto de tipo {{ table.class_name }} a actualizar.
     *
     * @return int Número de filas afectadas
     */
    final public static function update(
        \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }}
    ): int {
        $sql = '
            UPDATE
                `{{ table.name }}`
            SET
                {{ table.columns|rejectattr('primary_key')|listformat('`{.name}` = ?', table=table)|join(',\n                ') }}
            WHERE
                (
                    {{ table.columns|selectattr('primary_key')|listformat('`{.name}` = ?', table=table)|join(' AND\n                    ') }}
                );';
        $params = [
  {%- for column in table.columns|rejectattr('primary_key') %}
    {%- if 'timestamp' in column.type or 'datetime' in column.type %}
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                ${{ table.name }}->{{ column.name }}
            ),
    {%- elif column.php_type in ('?bool', '?int') %}
            (
                is_null(${{ table.name }}->{{ column.name }}) ?
                null :
                intval(${{ table.name }}->{{ column.name }})
            ),
    {%- elif column.php_type == '?float' %}
            (
                is_null(${{ table.name }}->{{ column.name }}) ?
                null :
                floatval(${{ table.name }}->{{ column.name }})
            ),
    {%- elif column.php_type in ('bool', 'int') %}
            intval(${{ table.name }}->{{ column.name }}),
    {%- elif column.php_type == 'float' %}
            floatval(${{ table.name }}->{{ column.name }}),
    {%- else %}
            ${{ table.name }}->{{ column.name }},
    {%- endif %}
  {%- endfor %}
  {%- for column in table.columns|selectattr('primary_key') %}
    {%- if 'timestamp' in column.type or 'datetime' in column.type %}
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                ${{ table.name }}->{{ column.name }}
            ),
    {%- elif column.php_type in ('?bool', '?int') %}
            (
                is_null(${{ table.name }}->{{ column.name }}) ?
                null :
                intval(${{ table.name }}->{{ column.name }})
            ),
    {%- elif column.php_type == '?float' %}
            (
                is_null(${{ table.name }}->{{ column.name }}) ?
                null :
                floatval(${{ table.name }}->{{ column.name }})
            ),
    {%- elif column.php_type in ('bool', 'int') %}
            intval(${{ table.name }}->{{ column.name }}),
    {%- elif column.php_type == 'float' %}
            floatval(${{ table.name }}->{{ column.name }}),
    {%- else %}
            ${{ table.name }}->{{ column.name }},
    {%- endif %}
  {%- endfor %}
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }
{% endif %}
{%- if table.columns|selectattr('primary_key')|list %}
    /**
     * Obtener {@link \OmegaUp\DAO\VO\{{ table.class_name }}} por llave primaria.
     *
     * Este método cargará un objeto {@link \OmegaUp\DAO\VO\{{ table.class_name }}}
     * de la base de datos usando sus llaves primarias.
     *
     * @return ?\OmegaUp\DAO\VO\{{ table.class_name }} Un objeto del tipo
     * {@link \OmegaUp\DAO\VO\{{ table.class_name }}} o NULL si no hay tal
     * registro.
     */
    final public static function getByPK(
        {{ table.columns|selectattr('primary_key')|listformat('{0.php_type} ${0.name}')|join(',\n        ') }}
    ): ?\OmegaUp\DAO\VO\{{ table.class_name }} {
  {%- if table.columns|selectattr('primary_key')|rejectattr('not_null')|list|length %}
        if ({{ table.columns|selectattr('primary_key')|rejectattr('not_null')|listformat('is_null(${.name})')|join(' || ') }}) {
            return null;
        }
  {%- endif %}
        $sql = '
            SELECT
                {{ table.fieldnames|join(',\n                ') }}
            FROM
                `{{ table.name }}`
            WHERE
                (
                    {{ table.columns|selectattr('primary_key')|listformat('`{.name}` = ?')|join(' AND\n                    ') }}
                )
            LIMIT 1;';
        $params = [{{ table.columns|selectattr('primary_key')|listformat('${.name}')|join(', ') }}];
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\{{ table.class_name }}($row);
    }

    /**
     * Verificar si existe un {@link \OmegaUp\DAO\VO\{{ table.class_name }}} por llave primaria.
     *
     * Este método verifica la existencia de un objeto {@link \OmegaUp\DAO\VO\{{ table.class_name }}}
     * de la base de datos usando sus llaves primarias **sin necesidad de cargar sus campos**.
     *
     * Este método es más eficiente que una llamada a getByPK cuando no se van a utilizar
     * los campos.
     *
     * @return bool Si existe o no tal registro.
     */
    final public static function existsByPK(
        {{ table.columns|selectattr('primary_key')|listformat('{0.php_type} ${0.name}')|join(',\n        ') }}
    ): bool {
  {%- if table.columns|selectattr('primary_key')|rejectattr('not_null')|list|length %}
        if ({{ table.columns|selectattr('primary_key')|rejectattr('not_null')|listformat('is_null(${.name})')|join(' || ') }}) {
            return false;
        }
  {%- endif %}
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `{{ table.name }}`
            WHERE
                (
                    {{ table.columns|selectattr('primary_key')|listformat('`{.name}` = ?')|join(' AND\n                    ') }}
                );';
        $params = [{{ table.columns|selectattr('primary_key')|listformat('${.name}')|join(', ') }}];
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }

    /**
     * Contar todos los registros en `{{ table.name }}`.
     *
     * Este método obtiene el número total de filas de la tabla **sin cargar campos**,
     * útil para pruebas donde sólo se valida el conteo.
     *
     * @return int Número total de registros.
     */
    final public static function countAll(): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                `{{ table.name }}`;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, []);
        return intval($count);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {@link \OmegaUp\DAO\VO\{{ table.class_name }}} suministrado.
     * Una vez que se ha eliminado un objeto, este no puede ser restaurado
     * llamando a {@link replace()}, ya que este último creará un nuevo
     * registro con una llave primaria distinta a la que estaba en el objeto
     * eliminado.
     *
     * Si no puede encontrar el registro a eliminar,
     * {@link \OmegaUp\Exceptions\NotFoundException} será arrojada.
     *
     * @param \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }} El
     * objeto de tipo \OmegaUp\DAO\VO\{{ table.class_name }} a eliminar
     *
     * @throws \OmegaUp\Exceptions\NotFoundException Se arroja cuando no se
     * encuentra el objeto a eliminar en la base de datos.
     */
    final public static function delete(
        \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }}
    ): void {
        $sql = '
            DELETE FROM
                `{{ table.name }}`
            WHERE
                (
                    {{ table.columns|selectattr('primary_key')|listformat('`{.name}` = ?')|join(' AND\n                    ') }}
                );';
        $params = [
            {{ table.columns|selectattr('primary_key')|listformat('${table.name}->{.name}', table=table)|join(',\n            ') }}
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        if (\OmegaUp\MySQLConnection::getInstance()->Affected_Rows() == 0) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }
    }
{% endif %}
    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo
     * {@link \OmegaUp\DAO\VO\{{ table.class_name }}}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @param ?int $pagina Página a ver.
     * @param int $filasPorPagina Filas por página.
     * @param string $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param string $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     *
     * @return list<\OmegaUp\DAO\VO\{{ table.class_name }}> Un arreglo que contiene objetos del tipo
     * {@link \OmegaUp\DAO\VO\{{ table.class_name }}}.
     */
    final public static function getAll(
        ?int $pagina = null,
        int $filasPorPagina = 100,
        string $orden = '{{ table.fieldnames[0].split(".")[-1] | replace("`", "") }}',
        string $tipoDeOrden = 'ASC'
    ): array {
        $sanitizedOrder = \OmegaUp\MySQLConnection::getInstance()->escape(
            $orden
        );
        \OmegaUp\Validators::validateInEnum(
            $tipoDeOrden,
            'order_type',
            [
                'ASC',
                'DESC',
            ]
        );
        $sql = "
            SELECT
                {{ table.fieldnames|join(',\n                ') }}
            FROM
                `{{ table.name }}`
            ORDER BY
                `{$sanitizedOrder}` {$tipoDeOrden}
        ";
        if (!is_null($pagina)) {
            $sql .= (
                ' LIMIT ' .
                (($pagina - 1) * $filasPorPagina) .
                ', ' .
                intval($filasPorPagina)
            );
        }
        $allData = [];
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll($sql) as $row
        ) {
            $allData[] = new \OmegaUp\DAO\VO\{{ table.class_name }}(
                $row
            );
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {@link \OmegaUp\DAO\VO\{{ table.class_name }}}
     * suministrado.
     *
{%- if column in table.columns|selectattr('auto_increment')|list %}
     * Este método asignará el valor de la columna autogenerada en el objeto
     * {@link \OmegaUp\DAO\VO\{{ table.class_name }}} dentro de la misma
     * transacción.
     *
{%- endif %}
     * @param \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }} El
     * objeto de tipo {@link \OmegaUp\DAO\VO\{{ table.class_name }}}
     * a crear.
     *
     * @return int Un entero mayor o igual a cero identificando el número de
     *             filas afectadas.
     */
    final public static function create(
        \OmegaUp\DAO\VO\{{ table.class_name }} ${{ table.name }}
    ): int {
        $sql = '
            INSERT INTO
                `{{ table.name }}` (
                    {{ table.columns|rejectattr('auto_increment')|listformat('`{.name}`', table=table)|join(',\n                    ') }}
                ) VALUES (
                    {{ table.columns|rejectattr('auto_increment')|listformat('?', table=table)|join(',\n                    ') }}
                );';
        $params = [
{%- for column in table.columns|rejectattr('auto_increment') %}
  {%- if 'timestamp' in column.type or 'datetime' in column.type %}
            \OmegaUp\DAO\DAO::toMySQLTimestamp(
                ${{ table.name }}->{{ column.name }}
            ),
  {%- elif column.php_type in ('?bool', '?int') %}
            (
                is_null(${{ table.name }}->{{ column.name }}) ?
                null :
                intval(${{ table.name }}->{{ column.name }})
            ),
  {%- elif column.php_type == '?float' %}
            (
                is_null(${{ table.name }}->{{ column.name }}) ?
                null :
                floatval(${{ table.name }}->{{ column.name }})
            ),
  {%- elif column.php_type in ('bool', 'int') %}
            intval(${{ table.name }}->{{ column.name }}),
  {%- elif column.php_type == 'float' %}
            floatval(${{ table.name }}->{{ column.name }}),
  {%- else %}
            ${{ table.name }}->{{ column.name }},
  {%- endif %}
{%- endfor %}
        ];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        $affectedRows = \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
        if ($affectedRows == 0) {
            return 0;
        }
{%- for column in table.columns|selectattr('auto_increment') %}
        ${{ table.name }}->{{ column.name }} = (
            \OmegaUp\MySQLConnection::getInstance()->Insert_ID()
        );
{%- endfor %}

        return $affectedRows;
    }
}

