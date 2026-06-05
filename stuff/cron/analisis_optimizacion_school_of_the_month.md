# Análisis de Optimización: School of the Month

> **Contexto:** Cron job `update_ranks.py` → `update_school_of_the_month_candidates` → funciones en `database/school_of_the_month.py`  
> **Rama:** `opt-get-school-candidates`  
> **Índices añadidos en:** `frontend/database/00274_add_idx_school_of_the_month.sql`

---

## 1. Inventario de Queries Analizadas

| Función | Tabla principal | Costo estimado |
|---|---|---|
| `get_school_of_the_month_candidates` | `Submissions` | Alto |
| `get_current_problems_solved_per_month` | `Submissions` + `Runs` | Alto |
| `get_candidate_schools_list` | `Submissions` | Medio |
| `get_last_12_schools_of_the_month` | `School_Of_The_Month` | Bajo |

---

## 2. Evaluación de los Índices Añadidos en 00274

### 2.1 `idx_submissions_verdict_time_identity_problem_school`
```sql
(`verdict`, `time`, `identity_id`, `problem_id`, `school_id`, `submission_id`)
```

**Aciertos:**
- Cubre el patrón de acceso principal de `get_school_of_the_month_candidates`:
  `WHERE su.verdict = 'AC' AND su.time BETWEEN ? AND ?` → range scan eficiente.
- Incluye `identity_id`, `problem_id`, `school_id` como columnas de cobertura para los JOINs.
- `submission_id` al final sirve para el tiebreak del NOT EXISTS.

**Observación:** El orden `(verdict, time, ...)` es correcto: primero el valor de alta selectividad fijo (`AC`), luego el rango en `time`. Sin embargo, `school_id IS NOT NULL` no puede beneficiarse del índice directamente (IS NOT NULL no es sargable de forma óptima en InnoDB); el motor lo resuelve post-filtro. Impacto menor.

### 2.2 `idx_submissions_identity_problem_verdict_time_id`
```sql
(`identity_id`, `problem_id`, `verdict`, `time`, `submission_id`)
```

**Aciertos:**
- Diseñado exactamente para el subquery NOT EXISTS de first-AC:
  ```sql
  WHERE su_prev.identity_id = ? AND su_prev.problem_id = ?
        AND su_prev.verdict = 'AC'
        AND (su_prev.time < su.time OR ...)
  ```
- Covering index para ese subquery: el motor no toca la tabla base.

**Correctísimo.**

### 2.3 `idx_sotm_school_time`
```sql
(`school_id`, `time`)
```

**Aciertos:** Cubre el lookup `WHERE sotm.school_id = ? AND sotm.time >= ?`.

**Limitación:** El predicado `(sotm.selected_by IS NOT NULL OR sotm.ranking = 1)` aún requiere leer la fila completa. Con una extensión del índice esto se elimina (ver propuesta 4.2).

---

## 3. Análisis Específico: `get_school_of_the_month_candidates`

Esta función es el query más costoso del cron. Merece análisis propio.

### 3.A 🔴 [ALTO] Segundo `NOT EXISTS` ejecuta una vez por *submission*, no por *escuela*

**Código afectado:** `school_of_the_month.py:222–229`

```sql
AND NOT EXISTS (
    SELECT 1
    FROM School_Of_The_Month AS sotm
    WHERE
        sotm.school_id = su.school_id
        AND (sotm.selected_by IS NOT NULL OR sotm.ranking = 1)
        AND sotm.time >= DATE_SUB(%s, INTERVAL 1 YEAR)
)
```

Este subquery verifica si una **escuela** fue ganadora en el último año. El resultado es **idéntico para todas las submissions de la misma escuela** en el mes. Sin embargo, como es un NOT EXISTS correlacionado a `su.school_id`, MySQL lo re-ejecuta para cada fila que pasa el filtro principal.

Si la escuela X tiene 300 submissions AC con quality_seal este mes, el motor consulta `School_Of_The_Month` 300 veces para obtener siempre la misma respuesta.

**Fix:** Pre-computar el conjunto de escuelas excluidas como derived table en el FROM. MySQL lo materializa una sola vez como hash table y la anti-join es O(1) por submission:

```sql
LEFT JOIN (
    SELECT DISTINCT school_id
    FROM School_Of_The_Month
    WHERE (selected_by IS NOT NULL OR ranking = 1)
        AND time >= DATE_SUB(%s, INTERVAL 1 YEAR)
) AS recent_winners ON recent_winners.school_id = su.school_id
WHERE recent_winners.school_id IS NULL
```

---

### 3.B 🟡 [MEDIO] `i.user_id IS NOT NULL` es redundante

**Código afectado:** `school_of_the_month.py:208`

```sql
JOIN Users AS u ON u.user_id = i.user_id
WHERE ...
    AND i.user_id IS NOT NULL   -- ← redundante
```

El `INNER JOIN ... ON u.user_id = i.user_id` ya excluye implícitamente los casos donde `i.user_id IS NULL`: en SQL `NULL = value` evalúa a UNKNOWN, y el INNER JOIN solo retiene TRUE. La condición explícita no agrega nada al resultado y obliga al optimizer a cargarla como predicado extra.

---

### 3.C 🟡 [MEDIO] Condiciones de filtro de `Problems` en `WHERE` en lugar de `JOIN ON`

**Código afectado:** `school_of_the_month.py:201–205`

```sql
JOIN Problems AS p ON p.problem_id = su.problem_id
...
WHERE
    ...
    AND p.visibility >= 1
    AND p.quality_seal = 1
```

Para INNER JOINs, MySQL trata WHERE y ON de forma equivalente semánticamente. Sin embargo, al mover los filtros selectivos al ON, se le da al optimizer la señal explícita de que puede **invertir el orden del join**: empezar desde `Problems` (pocos registros con `quality_seal=1`) y buscar las submissions correspondientes, en lugar de escanear todas las submissions del mes y luego verificar el problema.

Con el índice cubriente propuesto en §4.2, este cambio de orden de join puede ser significativo.

Lo mismo aplica a `u.main_email_id IS NOT NULL`.

---

### 3.D ⚠️ [CORRECTNESS] La propuesta CTE con `ROW_NUMBER()` es incorrecta

La propuesta 4.5 original del análisis contiene un error lógico. El `ROW_NUMBER()` sobre submissions del mes actual no puede determinar si es la primera AC histórica:

```sql
-- INCORRECTO: si el usuario resolvió el problema el mes pasado,
-- su submission de este mes sería rn=1 dentro del CTE (al filtrar solo el mes)
-- pero el NOT EXISTS correcto la excluiría
WITH first_ac_submissions AS (
    SELECT ..., ROW_NUMBER() OVER (...) AS rn
    FROM Submissions
    WHERE su.time BETWEEN %s AND %s  -- ← no incluye historial previo
)
```

El `NOT EXISTS` actual es la implementación **correcta y adecuada** para este patrón de "primera AC histórica que cae en el mes actual". El índice `idx_submissions_identity_problem_verdict_time_id` lo cubre eficientemente. No reemplazar.

---

### 3.E Query Optimizado para `get_school_of_the_month_candidates`

Aplicando 3.A + 3.B + 3.C:

```sql
SELECT
    s.school_id,
    s.name,
    IFNULL(SUM(ROUND(100 / LOG(2, p.accepted + 1), 0)), 0.0) AS score
FROM Submissions AS su
JOIN Problems AS p
    ON p.problem_id = su.problem_id
    AND p.visibility >= 1
    AND p.quality_seal = 1
JOIN Schools AS s
    ON s.school_id = su.school_id
JOIN Identities AS i
    ON i.identity_id = su.identity_id
JOIN Users AS u
    ON u.user_id = i.user_id
    AND u.main_email_id IS NOT NULL
LEFT JOIN (
    SELECT DISTINCT school_id
    FROM School_Of_The_Month
    WHERE (selected_by IS NOT NULL OR ranking = 1)
        AND time >= DATE_SUB(%s, INTERVAL 1 YEAR)
) AS recent_winners ON recent_winners.school_id = su.school_id
WHERE
    su.verdict = 'AC'
    AND su.time BETWEEN %s AND %s
    AND su.school_id IS NOT NULL
    AND recent_winners.school_id IS NULL
    AND NOT EXISTS (
        SELECT 1
        FROM Submissions AS su_prev
        WHERE
            su_prev.identity_id = su.identity_id
            AND su_prev.problem_id = su.problem_id
            AND su_prev.verdict = 'AC'
            AND (
                su_prev.time < su.time
                OR (su_prev.time = su.time
                    AND su_prev.submission_id < su.submission_id)
            )
    )
GROUP BY s.school_id
ORDER BY score DESC
LIMIT 100;
```

**Cambios respecto al original:**
| Cambio | Qué hace |
|---|---|
| `recent_winners` derived table | SOTM se escanea 1 vez en vez de N veces |
| Filtros en JOIN ON | Permite al optimizer invertir orden de join |
| `i.user_id IS NOT NULL` eliminado | Predicado redundante removido |
| Primer `%s` = `first_day_of_next_month` | El parámetro de la derived table va primero |

**⚠️ Cambio en el orden de parámetros Python:**
```python
# Antes:
(first_day_of_current_month, first_day_of_next_month, first_day_of_next_month)
# 1° = BETWEEN start, 2° = BETWEEN end, 3° = DATE_SUB

# Después:
(first_day_of_next_month, first_day_of_current_month, first_day_of_next_month)
# 1° = DATE_SUB (derived table), 2° = BETWEEN start, 3° = BETWEEN end
```

---

## 4. Otros Problemas Identificados

### 4.1 🔴 [ALTO] Join a `Runs` redundante en `get_current_problems_solved_per_month`

**Código afectado:** `school_of_the_month.py:86–131`

La tabla `Submissions` tiene la columna `verdict` denormalizada directamente:
```sql
-- schema.sql línea 1207
`verdict` enum('AC','PA',...) NOT NULL
```

Sin embargo, `get_current_problems_solved_per_month` realiza dos joins a `Runs` innecesarios:

```sql
-- Join externo (línea 86–87)
INNER JOIN Runs AS r ON r.run_id = su.current_run_id
...
WHERE r.verdict = 'AC'   -- ← redundante

-- Subquery interno (líneas 99–106)
INNER JOIN Runs AS r2 ON r2.run_id = s2.current_run_id
WHERE r2.verdict = 'AC'  -- ← redundante
```

En contraste, `get_school_of_the_month_candidates` ya usa correctamente `su.verdict = 'AC'`.

**Impacto:** Cada join a `Runs` fuerza un lookup por `run_id` (FK). Dado que `Runs` es una tabla grande y `current_run_id` puede estar en páginas dispersas, esto multiplica las I/O del query. Eliminar ambos joins reduce la cardinalidad del plan de ejecución significativamente.

---

### 4.2 🔴 [ALTO] Llamada duplicada a `get_school_of_the_month_candidates`

**Código afectado:** `update_ranks.py:504–522`

```python
schools_sql = get_school_of_the_month_candidates(   # Primera llamada
    cur_readonly, first_day_of_next_month, first_day_of_current_month
)

if update_school_of_the_month:
    insert_school_of_the_month_candidates(cur, first_day_of_next_month, schools_sql)
else:
    schools_sql = get_school_of_the_month_candidates(  # ← Segunda llamada idéntica
        cur_readonly, first_day_of_next_month, first_day_of_current_month
    )
    debug_school_of_the_month_candidates(...)
```

Cuando `update_school_of_the_month=False` (modo debug/dry-run), el query pesado se ejecuta **dos veces** y el resultado de la primera se descarta. Dado que este es el query más costoso del cron, duplicarlo en modo verificación es innecesario.

---

### 4.3 🟡 [MEDIO] Índice en `Problems` no cubre la combinación `quality_seal + visibility + accepted`

**Contexto:** En `get_school_of_the_month_candidates`:
```sql
JOIN Problems AS p ON p.problem_id = su.problem_id
WHERE p.visibility >= 1
    AND p.quality_seal = 1
...
SUM(ROUND(100 / LOG(2, p.accepted + 1), 0)) AS score
```

Los índices actuales en `Problems`:
- `idx_quality_seal` (`quality_seal`) — simple
- `idx_problems_visibility` (`visibility`) — simple

El motor solo puede usar **uno** de estos por acceso. Además, la columna `accepted` (necesaria para calcular el score) requiere un table lookup adicional (no está en ningún índice).

Un índice compuesto cubriente eliminaría el lookup:
```sql
(quality_seal, visibility, problem_id, accepted)
```

Con esto el JOIN a `Problems` sería index-only: filtra por `quality_seal=1 AND visibility>=1`, devuelve `problem_id` (para el JOIN) y `accepted` (para el SUM), sin tocar la tabla base.

---

### 4.4 🟡 [MEDIO] `School_Of_The_Month` NO EXISTS sin cobertura de `selected_by` y `ranking`

**Contexto:**
```sql
NOT EXISTS (
    SELECT 1 FROM School_Of_The_Month AS sotm
    WHERE sotm.school_id = su.school_id
        AND (sotm.selected_by IS NOT NULL OR sotm.ranking = 1)
        AND sotm.time >= DATE_SUB(%s, INTERVAL 1 YEAR)
)
```

El índice `idx_sotm_school_time` (`school_id`, `time`) filtra bien por `school_id` + `time`, pero el predicado `OR (selected_by IS NOT NULL OR ranking = 1)` obliga a leer la fila completa para evaluar ambas columnas.

El índice ya existente `school_id` (simple) + el nuevo `idx_sotm_school_time` solapan en propósito. Unificarlos en un índice cubriente reduciría I/O:

```sql
(school_id, time, selected_by, ranking)
```

**Nota:** La tabla `School_Of_The_Month` es pequeña (O(100) filas), así que el impacto real es bajo. Sigue siendo una mejora limpia.

---

### 4.5 🟢 [BAJO] `School_Of_The_Month` tiene índices solapados

La tabla tiene actualmente:
- `KEY school_id (school_id)` — del schema original
- `KEY idx_sotm_school_time (school_id, time)` — añadido en 00274

El índice simple `school_id` queda completamente redundante porque `idx_sotm_school_time` ya lo prefija. MySQL nunca elegirá el índice simple cuando el compuesto cubre el mismo acceso.

**Recomendación:** Eliminar `KEY school_id (school_id)` de `School_Of_The_Month` para reducir overhead de escritura.

---

### 4.6 🟢 [BAJO] `get_candidate_schools_list` repite filtros de `get_school_of_the_month_candidates`

La función `get_candidate_schools_list` (usada en la rama Python `compute_points_for_school`) aplica los mismos filtros que `get_school_of_the_month_candidates` pero en una query separada. Si la rama SQL ya cubre el caso de uso, `get_candidate_schools_list` se ejecuta en duplicado cuando ambas ramas corren (actualmente la rama Python está comentada, pero si se reactiva existirá el solapamiento).

---

## 5. Propuestas de Mejora

### Propuesta 5.1 — Aplicar query optimizado a `get_school_of_the_month_candidates`

**Archivo:** `stuff/cron/database/school_of_the_month.py:186–233`

Ver query completo en §3.E. Requiere actualizar el orden de parámetros en las llamadas `.execute()` (EXPLAIN y real) en líneas 235–237 y 247–249:

```python
# EXPLAIN call (línea 235–237) — antes:
cur_readonly.execute('EXPLAIN ' + sql, (first_day_of_current_month,
                                        first_day_of_next_month,
                                        first_day_of_next_month))
# Después:
cur_readonly.execute('EXPLAIN ' + sql, (first_day_of_next_month,
                                        first_day_of_current_month,
                                        first_day_of_next_month))

# Real call (línea 247–249) — antes:
cur_readonly.execute(sql, (first_day_of_current_month, first_day_of_next_month,
                           first_day_of_next_month))
# Después:
cur_readonly.execute(sql, (first_day_of_next_month, first_day_of_current_month,
                           first_day_of_next_month))
```

---

### Propuesta 5.2 — Eliminar joins a `Runs` en `get_current_problems_solved_per_month`

**Archivo:** `stuff/cron/database/school_of_the_month.py`

**Query actual:**
```sql
FROM Submissions AS su
INNER JOIN Runs AS r ON r.run_id = su.current_run_id
...
WHERE r.verdict = 'AC'
```

**Query optimizado:**
```sql
FROM Submissions AS su
WHERE su.verdict = 'AC'
```

En el subquery interno, mismo cambio:
```sql
-- Antes:
FROM Submissions AS s2
INNER JOIN Runs AS r2 ON r2.run_id = s2.current_run_id
WHERE s2.time >= ... AND r2.verdict = 'AC'

-- Después:
FROM Submissions AS s2
WHERE s2.time >= ... AND s2.verdict = 'AC'
```

**Resultado:** Se eliminan 2 joins con random I/O. El índice `idx_submissions_verdict_time_identity_problem_school` puede usarse directamente desde el inicio del plan.

---

### Propuesta 5.3 — Índice cubriente en `Problems`

**Archivo:** `frontend/database/schema.sql` + nueva migración

```sql
ALTER TABLE `Problems`
    ADD INDEX `idx_problems_quality_seal_visibility_id_accepted`
        (`quality_seal`, `visibility`, `problem_id`, `accepted`);
```

El optimizer puede resolver el JOIN a `Problems` completamente desde el índice:
- Filtra: `quality_seal = 1` (eq), `visibility >= 1` (range)
- Devuelve: `problem_id` (lookup key), `accepted` (para el score)

**Antes de aplicar:** Verificar con `EXPLAIN FORMAT=JSON` que el plan usa `index_only=true` para la tabla `Problems`.

---

### Propuesta 5.4 — Corregir llamada duplicada

**Archivo:** `stuff/cron/update_ranks.py:504–522`

```python
# Actual (ejecuta la query dos veces en modo debug):
schools_sql = get_school_of_the_month_candidates(...)
if update_school_of_the_month:
    insert_school_of_the_month_candidates(cur, first_day_of_next_month, schools_sql)
else:
    schools_sql = get_school_of_the_month_candidates(...)  # duplicado
    debug_school_of_the_month_candidates(...)

# Corregido (ejecuta una sola vez):
schools_sql = get_school_of_the_month_candidates(...)
if update_school_of_the_month:
    insert_school_of_the_month_candidates(cur, first_day_of_next_month, schools_sql)
else:
    debug_school_of_the_month_candidates(
        first_day_of_next_month, schools_sql, schools_sql, use_json_format=True
    )
```

---

### Propuesta 5.5 — Índice cubriente en `School_Of_The_Month` y eliminar redundante

```sql
-- Reemplazar los dos índices actuales (school_id simple + idx_sotm_school_time)
-- por un único índice cubriente:
ALTER TABLE `School_Of_The_Month`
    DROP INDEX `school_id`,
    DROP INDEX `idx_sotm_school_time`,
    ADD INDEX `idx_sotm_school_time_cover`
        (`school_id`, `time`, `selected_by`, `ranking`);
```

El NOT EXISTS puede resolverse completamente desde el índice sin row lookup.

---

---

## 6. Resumen de Prioridades

| # | Propuesta | Afecta | Impacto | Esfuerzo | Tipo |
|---|---|---|---|---|---|
| 5.1 | Anti-join + filtros en ON en `get_school_of_the_month_candidates` | SOTM candidates | 🔴 Alto | Bajo | Code change |
| 5.2 | Eliminar joins a `Runs` redundantes | problems/month | 🔴 Alto | Bajo | Code change |
| 5.4 | Corregir llamada duplicada en modo debug | ambas | 🔴 Alto | Mínimo | Bug fix |
| 5.3 | Índice cubriente en `Problems` | SOTM candidates | 🟡 Medio | Bajo | DDL migration |
| 5.5 | Índice cubriente en `School_Of_The_Month` | SOTM candidates | 🟡 Bajo-Medio | Bajo | DDL migration |

---

## 7. Validación Recomendada

Antes de desplegar cualquier cambio en producción:

```sql
-- Verificar que Submissions.verdict es consistente con Runs.verdict
SELECT COUNT(*) 
FROM Submissions su
JOIN Runs r ON r.run_id = su.current_run_id
WHERE su.verdict != r.verdict;
-- Debe retornar 0 filas para confirmar que la columna está sincronizada

-- EXPLAIN ANALYZE para medir el impacto real de los nuevos índices
EXPLAIN ANALYZE SELECT ...;  -- Query actual vs. propuesto
```

Los logs de `EXPLAIN` ya están implementados en el código (líneas 122–129 y 235–245 de `school_of_the_month.py`), lo que facilita comparar los planes antes/después en producción.
