<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/** {{ table.class_name }} Data Access Object (DAO) Base.
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link {{ table.class_name }}}.
 * @access public
 * @abstract
 *
 */
abstract class {{ table.class_name }}DAOBase {
{%- if table.columns|selectattr('primary_key')|list %}
{%- if table.columns|rejectattr('primary_key')|list %}
    /**
     * Guardar registros.
     *
     * Este metodo guarda el estado actual del objeto {@link {{ table.class_name }}}
     * pasado en la base de datos. La llave primaria indicará qué instancia va
     * a ser actualizada en base de datos. Si la llave primara o combinación de
     * llaves primarias que describen una fila que no se encuentra en la base de
     * datos, entonces save() creará una nueva fila, insertando en ese objeto
     * el ID recién creado.
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }}
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     */
    final public static function save({{ table.class_name }} ${{ table.name }}) {
        if (is_null(self::getByPK({{ table.columns|selectattr('primary_key')|listformat('${table.name}->{.name}', table=table)|join(', ') }}))) {
            return {{ table.class_name }}DAOBase::create(${{ table.name }});
        }
        return {{ table.class_name }}DAOBase::update(${{ table.name }});
    }

    /**
     * Actualizar registros.
     *
     * @static
     * @return Filas afectadas
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }} a actualizar.
     */
    final public static function update({{ table.class_name }} ${{ table.name }}) {
        $sql = 'UPDATE `{{ table.name }}` SET {{ table.columns|rejectattr('primary_key')|listformat('`{.name}` = ?', table=table)|join(', ') }} WHERE {{ table.columns|selectattr('primary_key')|listformat('`{.name}` = ?', table=table)|join(' AND ') }};';
        $params = [
{%- for column in table.columns|rejectattr('primary_key') %}
{%- if 'tinyint' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (int)${{ table.name }}->{{ column.name }},
{%- elif 'int' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (int)${{ table.name }}->{{ column.name }},
{%- elif 'double' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (float)${{ table.name }}->{{ column.name }},
{%- else %}
            ${{ table.name }}->{{ column.name }},
{%- endif %}
{%- endfor %}
{%- for column in table.columns|selectattr('primary_key') %}
{%- if 'tinyint' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (int)${{ table.name }}->{{ column.name }},
{%- elif 'int' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (int)${{ table.name }}->{{ column.name }},
{%- elif 'double' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (float)${{ table.name }}->{{ column.name }},
{%- else %}
            ${{ table.name }}->{{ column.name }},
{%- endif %}
{%- endfor %}
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
{% endif %}
    /**
     * Obtener {@link {{ table.class_name }}} por llave primaria.
     *
     * Este metodo cargará un objeto {@link {{ table.class_name }}} de la base
     * de datos usando sus llaves primarias.
     *
     * @static
     * @return @link {{ table.class_name }} Un objeto del tipo {@link {{ table.class_name }}}. NULL si no hay tal registro.
     */
    final public static function getByPK({{ table.columns|selectattr('primary_key')|listformat('${.name}')|join(', ') }}) {
        if ({{ table.columns|selectattr('primary_key')|listformat('is_null(${.name})')|join(' || ') }}) {
            return null;
        }
        $sql = 'SELECT {{ table.fieldnames }} FROM {{ table.name }} WHERE ({{ table.columns|selectattr('primary_key')|listformat('{.name} = ?')|join(' AND ') }}) LIMIT 1;';
        $params = [{{ table.columns|selectattr('primary_key')|listformat('${.name}')|join(', ') }}];
        global $conn;
        $row = $conn->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new {{ table.class_name }}($row);
    }

    /**
     * Eliminar registros.
     *
     * Este metodo eliminará el registro identificado por la llave primaria en
     * el objeto {{ table.class_name }} suministrado. Una vez que se ha
     * eliminado un objeto, este no puede ser restaurado llamando a
     * {@link save()}, ya que este último creará un nuevo registro con una
     * llave primaria distinta a la que estaba en el objeto eliminado.
     *
     * Si no puede encontrar el registro a eliminar, {@link Exception} será
     * arrojada.
     *
     * @static
     * @throws Exception Se arroja cuando no se encuentra el objeto a eliminar en la base de datos.
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }} a eliminar
     */
    final public static function delete({{ table.class_name }} ${{ table.name }}) {
        $sql = 'DELETE FROM `{{ table.name }}` WHERE {{ table.columns|selectattr('primary_key')|listformat('{.name} = ?')|join(' AND ') }};';
        $params = [{{ table.columns|selectattr('primary_key')|listformat('${table.name}->{.name}', table=table)|join(', ') }}];
        global $conn;

        $conn->Execute($sql, $params);
        if ($conn->Affected_Rows() == 0) {
            throw new NotFoundException('recordNotFound');
        }
    }
{% endif %}
    /**
     * Obtener todas las filas.
     *
     * Esta funcion leerá todos los contenidos de la tabla en la base de datos
     * y construirá un arreglo que contiene objetos de tipo {@link {{ table.class_name }}}.
     * Este método consume una cantidad de memoria proporcional al número de
     * registros regresados, así que sólo debe usarse cuando la tabla en
     * cuestión es pequeña o se proporcionan parámetros para obtener un menor
     * número de filas.
     *
     * @static
     * @param $pagina Página a ver.
     * @param $filasPorPagina Filas por página.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipoDeOrden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link {{ table.class_name }}}.
     */
    final public static function getAll($pagina = null, $filasPorPagina = null, $orden = null, $tipoDeOrden = 'ASC') {
        $sql = 'SELECT {{ table.fieldnames }} from {{ table.name }}';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . $conn->escape($orden) . '` ' . ($tipoDeOrden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $filasPorPagina) . ', ' . (int)$filasPorPagina;
        }
        $allData = [];
        foreach ($conn->GetAll($sql) as $row) {
            $allData[] = new {{ table.class_name }}($row);
        }
        return $allData;
    }

    /**
     * Crear registros.
     *
     * Este metodo creará una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {{ table.class_name }} suministrado.
     *
{%- if column in table.columns|selectattr('auto_increment')|list %}
     * Este método asignará el valor de la columna autogenerada en el objeto
     * {{ table.class_name }} dentro de la misma transacción.
     *
{%- endif %}
     * @static
     * @return Un entero mayor o igual a cero identificando el número de filas afectadas.
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }} a crear.
     */
    final public static function create({{ table.class_name }} ${{ table.name }}) {
{%- for column in table.columns|selectattr('default') %}
        if (is_null(${{ table.name }}->{{ column.name }})) {
{%- if column.default == 'CURRENT_TIMESTAMP' %}
            ${{ table.name }}->{{ column.name }} = gmdate('Y-m-d H:i:s', Time::get());
{%- elif 'tinyint' in column.type %}
            ${{ table.name }}->{{ column.name }} = {{ 'true' if column.default == '1' else 'false' }};
{%- elif 'int' in column.type %}
            ${{ table.name }}->{{ column.name }} = {{ column.default }};
{%- elif 'double' in column.type %}
            ${{ table.name }}->{{ column.name }} = (float){{ column.default }};
{%- else %}
            ${{ table.name }}->{{ column.name }} = '{{ column.default }}';
{%- endif %}
        }
{%- endfor %}
        $sql = 'INSERT INTO {{ table.name }} ({{ table.columns|rejectattr('auto_increment')|listformat('`{.name}`', table=table)|join(', ') }}) VALUES ({{ table.columns|rejectattr('auto_increment')|listformat('?', table=table)|join(', ') }});';
        $params = [
{%- for column in table.columns|rejectattr('auto_increment') %}
{%- if 'tinyint' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (int)${{ table.name }}->{{ column.name }},
{%- elif 'int' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (int)${{ table.name }}->{{ column.name }},
{%- elif 'double' in column.type %}
            is_null(${{ table.name }}->{{ column.name }}) ? null : (float)${{ table.name }}->{{ column.name }},
{%- else %}
            ${{ table.name }}->{{ column.name }},
{%- endif %}
{%- endfor %}
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
{%- for column in table.columns|selectattr('auto_increment') %}
        ${{ table.name }}->{{ column.name }} = $conn->Insert_ID();
{%- endfor %}

        return $ar;
    }
}

