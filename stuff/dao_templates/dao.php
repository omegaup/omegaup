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
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link {{ table.class_name }} }.
 * @access public
 * @abstract
 *
 */
abstract class {{ table.class_name }}DAOBase extends DAO {
    /**
     * Campos de la tabla.
     */
    const FIELDS = '{{ table.fieldnames }}';

    /**
     * Guardar registros.
     *
{%- if table.primary_key %}
     * Este metodo guarda el estado actual del objeto {@link {{ table.class_name }}} pasado en la base de datos. La llave
     * primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
     * primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
     * en ese objeto el ID recien creado.
{%- else %}
     * Este metodo guarda el estado actual del objeto {@link {{ table.class_name }}} pasado en la base de datos.
     * save() siempre creara una nueva fila.
{%- endif %}
     *
     * @static
     * @throws Exception si la operacion fallo.
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }}
     * @return Un entero mayor o igual a cero denotando las filas afectadas.
     */
    final public static function save({{ table.class_name }} ${{ table.name }}) {
{%- if table.primary_key %}
        if (!is_null(self::getByPK({{ table.primary_key|listformat('${table.name}->{.name}', table=table)|join(', ') }}))) {
            return {{ table.class_name }}DAOBase::update(${{ table.name }});
        } else {
            return {{ table.class_name }}DAOBase::create(${{ table.name }});
        }
{%- else %}
        return {{ table.class_name }}DAOBase::create(${{ table.name }});
{%- endif %}
    }
{%- if table.primary_key %}

    /**
     * Obtener {@link {{ table.class_name }}} por llave primaria.
     *
     * Este metodo cargara un objeto {@link {{ table.class_name }}} de la base de datos
     * usando sus llaves primarias.
     *
     * @static
     * @return @link {{ table.class_name }} Un objeto del tipo {@link {{ table.class_name }}}. NULL si no hay tal registro.
     */
    final public static function getByPK({{ table.primary_key|listformat('${.name}')|join(', ') }}) {
        if ({{ table.primary_key|listformat('is_null(${.name})')|join(' || ') }}) {
            return null;
        }
        $sql = 'SELECT {{ table.fieldnames }} FROM {{ table.name }} WHERE ({{ table.primary_key|listformat('{.name} = ?')|join(' AND ') }}) LIMIT 1;';
        $params = [{{ table.primary_key|listformat('${.name}')|join(', ') }}];
        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }
        return new {{ table.class_name }}($rs);
    }
{%- endif %}

    /**
     * Obtener todas las filas.
     *
     * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
     * un vector que contiene objetos de tipo {@link {{ table.class_name }}}. Tenga en cuenta que este metodo
     * consumen enormes cantidades de recursos si la tabla tiene muchas filas.
     * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
     *
     * @static
     * @param $pagina Pagina a ver.
     * @param $columnas_por_pagina Columnas por pagina.
     * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
     * @return Array Un arreglo que contiene objetos del tipo {@link {{ table.class_name }}}.
     */
    final public static function getAll($pagina = null, $columnas_por_pagina = null, $orden = null, $tipo_de_orden = 'ASC') {
        $sql = 'SELECT {{ table.fieldnames }} from {{ table.name }}';
        global $conn;
        if (!is_null($orden)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orden) . '` ' . ($tipo_de_orden == 'DESC' ? 'DESC' : 'ASC');
        }
        if (!is_null($pagina)) {
            $sql .= ' LIMIT ' . (($pagina - 1) * $columnas_por_pagina) . ', ' . (int)$columnas_por_pagina;
        }
        $rs = $conn->Execute($sql);
        $allData = [];
        foreach ($rs as $row) {
            $allData[] = new {{ table.class_name }}($row);
        }
        return $allData;
    }

    /**
      * Buscar registros.
      *
      * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link {{ table.class_name }}} de la base de datos.
      * Consiste en buscar todos los objetos que coinciden con las variables permanentes instanciadas de objeto pasado como argumento.
      * Aquellas variables que tienen valores NULL seran excluidos en busca de criterios.
      *
      * <code>
      *   // Ejemplo de uso - buscar todos los clientes que tengan limite de credito igual a 20000
      *   $cliente = new Cliente();
      *   $cliente->setLimiteCredito('20000');
      *   $resultados = ClienteDAO::search($cliente);
      *
      *   foreach ($resultados as $c){
      *       echo $c->nombre . '<br>';
      *   }
      * </code>
      * @static
      * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }}
      * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
      * @param $orden 'ASC' o 'DESC' el default es 'ASC'
      */
    final public static function search(${{ table.name }}, $orderBy = null, $orden = 'ASC', $offset = 0, $rowcount = null, $likeColumns = null) {
        if (!(${{ table.name }} instanceof {{ table.class_name }})) {
            ${{ table.name }} = new {{ table.class_name }}(${{ table.name }});
        }

        $clauses = [];
        $params = [];
{%- for column in table.columns %}
        if (!is_null(${{ table.name }}->{{ column.name }})) {
            $clauses[] = '`{{ column.name }}` = ?';
            $params[] = ${{ table.name }}->{{ column.name }};
        }
{%- endfor %}
        global $conn;
        if (!is_null($likeColumns)) {
            foreach ($likeColumns as $column => $value) {
                $escapedValue = mysqli_real_escape_string($conn->_connectionID, $value);
                $clauses[] = "`{$column}` LIKE '%{$escapedValue}%'";
            }
        }
        if (sizeof($clauses) == 0) {
            return self::getAll();
        }
        $sql = 'SELECT {{ table.fieldnames }} FROM `{{ table.name }}`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . mysqli_real_escape_string($conn->_connectionID, $orderBy) . '` ' . ($orden == 'DESC' ? 'DESC' : 'ASC');
        }
        // Add LIMIT offset, rowcount if rowcount is set
        if (!is_null($rowcount)) {
            $sql .= ' LIMIT '. (int)$offset . ', ' . (int)$rowcount;
        }
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new {{ table.class_name }}($row);
        }
        return $ar;
    }
{%- if table.columns|selectattr('primary_key')|list %}

    /**
      * Actualizar registros.
      *
      * @return Filas afectadas
      * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }} a actualizar.
      */
    final private static function update({{ table.class_name }} ${{ table.name }}) {
{%- if table.columns|rejectattr('primary_key')|list %}
        $sql = 'UPDATE `{{ table.name }}` SET {{ table.columns|rejectattr('primary_key')|listformat('`{.name}` = ?', table=table)|join(', ') }} WHERE {{ table.columns|selectattr('primary_key')|listformat('`{.name}` = ?', table=table)|join(' AND ') }};';
        $params = [
{%- for column in table.columns %}
{%- if not column.primary_key %}
            ${{ table.name }}->{{ column.name }},
{%- endif %}
{%- endfor %}
{%- for column in table.columns %}
{%- if column.primary_key %}
            ${{ table.name }}->{{ column.name }},
{%- endif %}
{%- endfor %}
        ];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
{%- endif %}
    }
{%- endif %}

    /**
     * Crear registros.
     *
     * Este metodo creara una nueva fila en la base de datos de acuerdo con los
     * contenidos del objeto {{ table.class_name }} suministrado. Asegurese
     * de que los valores para todas las columnas NOT NULL se ha especificado
     * correctamente. Despues del comando INSERT, este metodo asignara la clave
     * primaria generada en el objeto {{ table.class_name }} dentro de la misma transaccion.
     *
     * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }} a crear.
     */
    final private static function create({{ table.class_name }} ${{ table.name }}) {
{%- for column in table.columns %}
{%- if column.default %}
        if (is_null(${{ table.name }}->{{ column.name }})) {
{%- if column.default == 'CURRENT_TIMESTAMP' %}
            ${{ table.name }}->{{ column.name }} = gmdate('Y-m-d H:i:s');
{%- else %}
            ${{ table.name }}->{{ column.name }} = '{{ column.default }}';
{%- endif %}
        }
{%- endif %}
{%- endfor %}
        $sql = 'INSERT INTO {{ table.name }} ({{ table.columns|listformat('`{.name}`', table=table)|join(', ') }}) VALUES ({{ table.columns|listformat('?', table=table)|join(', ') }});';
        $params = [
{%- for column in table.columns %}
            ${{ table.name }}->{{column.name}},
{%- endfor %}
        ];
        global $conn;
        $conn->Execute($sql, $params);
        $ar = $conn->Affected_Rows();
        if ($ar == 0) {
            return 0;
        }
{%- for column in table.columns %}
{%- if column.auto_increment %}
        ${{ table.name }}->{{ column.name }} = $conn->Insert_ID();
{%- endif %}
{%- endfor %}

        return $ar;
    }

    /**
     * Buscar por rango.
     *
     * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link {{ table.class_name }}} de la base de datos siempre y cuando
     * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link {{ table.class_name }}}.
     *
     * Aquellas variables que tienen valores NULL seran excluidos en la busqueda (los valores 0 y false no son tomados como NULL) .
     * No es necesario ordenar los objetos criterio, asi como tambien es posible mezclar atributos.
     * Si algun atributo solo esta especificado en solo uno de los objetos de criterio se buscara que los resultados conicidan exactamente en ese campo.
     *
     * <code>
     *   // Ejemplo de uso - buscar todos los clientes que tengan limite de credito
     *   // mayor a 2000 y menor a 5000. Y que tengan un descuento del 50%.
     *   $cr1 = new Cliente();
     *   $cr1->limite_credito = "2000";
     *   $cr1->descuento = "50";
     *
     *   $cr2 = new Cliente();
     *   $cr2->limite_credito = "5000";
     *   $resultados = ClienteDAO::byRange($cr1, $cr2);
     *
     *   foreach($resultados as $c ){
     *       echo $c->nombre . "<br>";
     *   }
     * </code>
     * @static
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }}
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }}
     * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
     * @param $orden 'ASC' o 'DESC' el default es 'ASC'
     */
    final public static function byRange({{ table.class_name }} ${{ table.name }}A, {{ table.class_name }} ${{ table.name }}B, $orderBy = null, $orden = 'ASC') {
        $clauses = [];
        $params = [];

{%- for column in table.columns %}

        $a = ${{ table.name }}A->{{ column.name }};
        $b = ${{ table.name }}B->{{ column.name }};
        if (!is_null($a) && !is_null($b)) {
            $clauses[] = '`{{ column.name }}` >= ? AND `{{ column.name }}` <= ?';
            $params[] = min($a, $b);
            $params[] = max($a, $b);
        } elseif (!is_null($a) || !is_null($b)) {
            $clauses[] = '`{{ column.name }}` = ?';
            $params[] = is_null($a) ? $b : $a;
        }
{%- endfor %}

        $sql = 'SELECT * FROM `{{ table.name }}`';
        $sql .= ' WHERE (' . implode(' AND ', $clauses) . ')';
        if (!is_null($orderBy)) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $orden;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $ar = [];
        foreach ($rs as $row) {
            $ar[] = new {{ table.class_name }}($row);
        }
        return $ar;
    }
{%- if table.columns|selectattr('primary_key')|list %}

    /**
     * Eliminar registros.
     *
     * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
     * en el objeto {{ table.class_name }} suministrado. Una vez que se ha suprimido un objeto, este no
     * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila
     * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado.
     * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
     *
     * @throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
     * @return int El numero de filas afectadas.
     * @param {{ table.class_name }} [${{ table.name }}] El objeto de tipo {{ table.class_name }} a eliminar
     */
    final public static function delete({{ table.class_name }} ${{ table.name }}) {
        if (is_null(self::getByPK({{ table.primary_key|listformat('${table.name}->{.name}', table=table)|join(', ') }}))) {
            throw new Exception('Registro no encontrado.');
        }
        $sql = 'DELETE FROM `{{ table.name }}` WHERE {{ table.primary_key|listformat('{.name} = ?')|join(' AND ') }};';
        $params = [{{ table.primary_key|listformat('${table.name}->{.name}', table=table)|join(', ') }}];
        global $conn;

        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
{%- endif %}
}

