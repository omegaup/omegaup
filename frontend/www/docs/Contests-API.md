**NOTA**: Esta documentación está desactualizada.

Para una versión más reciente, visita el sguiente enlace: [apiContest](https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/README.md#contest)

## GET `contests/`

### Descripción
Regresa los 10 concursos más recientes que el usuario loggeado puede ver. Usuarios no loggeados pueden consumir esta API

### Privilegios
Ninguno requerido.


### Parámetros
Ninguno

### Regresa
Regresa un arreglo con la siguiente información para cada concurso:

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`alias`|string|Alias del concurso|
|`contest_id`|int|Id del concurso|
|`title`|string|Título del concurso|
|`description`|string|Descripción del concurso|
|`start_time`|int|Hora de inicio del concurso en formato UNIX timestamp|
|`finish_time`|int|Hora del final del concurso en formato UNIX timestamp|
|`public`|int|Si `0`, el concurso es privado. Si `1`, el concurso es público|
|`director_id`|int|Id del usuario que es el director del concurso|
|`window_length`|int| Si no es nulo, la duración del concurso será `window_length` en minutos y el cronómetro del concurso será particular para cada usuario en vez de general para todos. El cronómetro iniciará cuando el concursante entra por primera vez al concurso. `start_time` determinará entonces la hora a partir de la cual los usuarios pueden empezar a abrir el concurso. (estilo USACO). El default es `null`.|
|`duration`|int|La duración del concurso, tomando en cuenta el valor de `window_length`|

##  GET `contests/:contest_alias/`

### Descripción
Regresa los detalles del concurso `:contest_alias`.

### Privilegios
Si el concurso es privado, el usuario debe estar en la lista de concursantes privados del concurso. Si el concurso es público, cualquier usuario puede acceder a esta API.

### Parámetros
Ninguno

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`title`|string|Título del concurso|
|`description`|string|Descripción del concurso|
|`alias`|string|Alias del concurso|
|`start_time`|int|Hora de inicio del concurso en formato UNIX timestamp|
|`finish_time`|int|Hora del final del concurso en formato UNIX timestamp|
|`window_length`|int| Si no es nulo, la duración del concurso será `window_length` en minutos y el cronómetro del concurso será particular para cada usuario en vez de general para todos. El cronómetro iniciará cuando el concursante entra por primera vez al concurso. `start_time` determinará entonces la hora a partir de la cual los usuarios pueden empezar a abrir el concurso. (estilo USACO). El default es `null`.|
|`scoreboard`|int|Entero entre `0` y `100` (inclusive) que determina el porcentaje del tiempo en que el scoreboard del concurso podrá ser visto por los concursantes. Cuando el porcentaje es excedido, el scoreboard que se regresa es la última versión que pudo ser pública. Los administradores siempre verán el scoreboard completo.|
|`points_decay_factor`|double|Double entre `0` y `1` inclusive. Si este número es distinto de cero, el puntaje que se obtiene al resolver correctamente un problema decae conforme pasa el tiempo. El valor del puntaje estará dado por `(1 - points_decay_factor)` `+ points_decay_factor * TT^2` `/ (10 * PT^2 + TT^2)`, donde `PT` es el penalty en minutos del envío y `TT` el tiempo total del concurso, en minutos.|
|`partial_score`|int|Entero entre `0` y `1` |
|`submissions_gap`|int| Número de segundos que el concursante necesita esperar para reenviar una solución.|
|`feedback`|string|Opciones: `yes`, `no`, `partial`|
|`penalty_time_start`|string|Determina cómo se calcula el penalty. Opciones: `contest`, `problem`, `none`. En caso de `contest`, el penalty para un envío se empieza a contar desde el inicio del concurso. `problem` indica que el penalty se toma en cuenta a partir de que se abre un problema. `none` indica que no habrá penalties en el concurso.|
|`penalty_calc_policy`|string|Opciones: `sum`, `max`.  Default:|
|`submission_deadline`|int|Tiempo restante en segundos para el final del concurso. Si `window_length` no es `NULL`, este valor puede ser diferente para cada concursante.|
|`problems`|array|Arreglo que contiene información sobre los problemas del concurso ordenados conforme lo deseó el contest director. Ver la tabla `problems` para más detalles.|

#### `problems`

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`title`|string|Tìtulo del problema|
|`alias`|string|Alias del problema|
|`validator`|string|Tipo del validador del problema. Opciones: `remote`, `literal`, `token`, `token-caseless`, `token-numeric`|
|`time_limit`|int|Tiempo límite en segundos para cada envío (TLE)|
|`memory_limit`|int|Límite de memora en KB. (MLE)|
|`submissions`|int|Número total de envíos a este problema en todo el sistema, no solamente en el concurso|
|`accepted`|int|Número de soluciones que han resuelto el problema por completo en todo el sistema, no solamente en el concurso dado|
|`difficulty`|string|Dificultad del problema calculada en base a las estadísticas del sistema|


##  POST `contests/create`

### Descripción
Crea un nuevo concurso. El director del concurso será el usuario que está actualmente loggeado.

### Privilegios
Cualquier usuario loggeado.

### Parámetros
| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
| `title` |string | Título del concurso |  |
|`description` | string | Descripción corta del concurso |  | 
| `alias` | string | Alias del concurso. Su uso principal es para construir las URLs del concurso (ver demás APIs). |   |
| `start_time`| int | Hora de inicio del concurso en formato UNIX timestamp si `window_length` es nulo. | |
| `finish_time` | int | Hora de final del concurso en formato UNIX timestamp si `window_length` es nulo. | |
| `window_length` | int | Si no es nulo, la duración del concurso será `window_length` en minutos y el cronómetro del concurso será particular para cada usuario en vez de general para todos. El cronómetro iniciará cuando el concursante entra por primera vez al concurso. `start_time` determinará entonces la hora a partir de la cual los usuarios pueden empezar a abrir el concurso. (estilo USACO). El default es `null`. | Opcional |
| `public` | int | Determina si el concurso es público o privado (`0` para privado, `1` para público) | |
| `scoreboard` | int | Entero entre `0` y `100` (inclusive) que determina el porcentaje del tiempo en que el scoreboard del concurso podrá ser visto por los concursantes. Cuando el porcentaje es excedido, el scoreboard que se regresa es la última versión que pudo ser pública. Los administradores siempre verán el scoreboard completo. | |
| `points_decay_factor` | double | Double entre `0` y `1` inclusive. Si este número es distinto de cero, el puntaje que se obtiene al resolver correctamente un problema decae conforme pasa el tiempo. El valor del puntaje estará dado por `(1 - points_decay_factor)` `+ points_decay_factor * TT^2` `/ (10 * PT^2 + TT^2)`, donde `PT` es el penalty en minutos del envío y `TT` el tiempo total del concurso, en minutos.  | |
| `partial_score` | int | Entero entre `0` y `1` | |
| `submissions_gap` | int | Número de segundos que el concursante necesita esperar para reenviar una solución. | |
| `feedback` | string | Opciones: `yes`, `no`, `partial` | |
| `penalty_time_start` | string | Determina cómo se calcula el penalty. Opciones: `contest`, `problem`, `none`. En caso de `contest`, el penalty para un envío se empieza a contar desde el inicio del concurso. `problem` indica que el penalty se toma en cuenta a partir de que se abre un problema. `none` indica que no habrá penalties en el concurso. | |
| `penalty_calc_policy` | string | Opciones: `sum`, `max`.  Default: | Opcional |
| `private_users` | json_array[int] | Arreglo de `user_id` de participantes que pueden entrar a un concurso privado. | Opcional |
| `problems` | array[string] | Arreglo de `problem_alias` con los alias de los problemas existentes que se usarán en el concurso. | Opcional |
| `show_scoreboard_after` | int | Si `1`, el scoreboard final será mostrado inmediatamente al final del concurso. Si `0`, el scoreboard se quedará congelado desde el momento en que el parámetro `scoreboard` lo haya indicado, aún después del final del concurso. | Opcional |

### Regresa

| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
| `status` | string | Si el request fue exitoso, regresa `ok`| 

##  POST `contests/:contest_alias/addProblem/`

### Descripción
Agrega un problema a un concurso. El problema debe haber sido previamente creado.

### Privilegios
Contest director o superior.

### Parámetros
| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`problem_alias`|string|Alias del problema a agregar||
|`points`|int|Valor de una solución completa a este problema. Lo usual es `100`, sin embargo, puede ser diferente para cada problema||
|`order_in_contest`|int|Ìndice que sirve para ordenar los problemas con respecto a otros del mismo concurso|Opcional|

### Regresa
| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
| `status` | string | Si el request fue exitoso, regresa `ok`| 

##  POST `contests/:contest_alias/addUser/`

### Descripción
Agrega un usuario a un concurso privado. Si el concurso es privado y el usuario no está en esta lista, no podrá entrar al concurso.

### Privilegios
Contest director o superior.

### Parámetros

| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`user_id`|int|Id del usuario a agregar||

### Regresa

| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
| `status` | string | Si el request fue exitoso, regresa `ok`| 

##  GET `contests/:contest_alias/clarifications/`

### Descripción
Regresa las clarificaciones de un concurso. Si el usuario es concursante, regresará sólo las clarificaciones marcadas como públicas más sus propias clarificaciones privadas. Si el usuario es contest director o admin, regresará las clarificaciones privadas también.

### Privilegios
Si el concurso es privado, el usuario debe estar en la lista de concursantes privados del concurso. Si el concurso es público, cualquier usuario puede acceder a esta API.

### Parámetros

| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`offset`|int|Determina a partir de cuál elemento se procesará el request con respecto al total de elementos. Usado comuúmente para paginar (determina el inicio de la página)|Opcional|
|`rowcount`|int|Determina cuántos elementos se regresan.|Opcional|


### Regresa
| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
|`clarification_id`|int|Id de la clarificación|
|`problem_alias`|string|Alias del problema al que corresponde la clarificación|
|`message`|string|Texto de la clarificación|
|`answer`|string|Respuesta a la clarificación|
|`time`|int|Timestamp del último update a la clarificación|
|`public`|int|`0` o `1` dependiendo si la clarificación es privada o pública|

##  GET `contests/:contest_alias/scoreboard/`

### Descripción
Regresa el scoreboard del concurso. Si el usuario es concursante, el scoreboard se congelará tal como lo dicte el parametro `scoreboard` en la creación del concurso (se puede modificar via Update). Si el usuario es administrador, siempre verá el scoreboard actualizado.

### Privilegios
Si el concurso es privado, el usuario debe estar en la lista de concursantes privados del concurso. Si el concurso es público, cualquier usuario puede acceder a esta API.

### Parámetros
Ninguno

### Regresa
| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
|`ranking`|array|Array de detalles del scoreboard para cada usuario, ver tabla `ranking`|

### `ranking`
La tabla ranking es un arreglo ordenado, con índices enteros, donde el índice 0 es el mejor concursante.

| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
|`username`|string|Username del concursante|
|`total[points]`|int|Puntos totales del concursante|
|`total[penalty]`|int|Penalty total del concursante|
|`problems`|array|Arreglo con detalle del scoreboard por problema. Ver tabla `problems`|

### `problems`
La tabla problems contiene información detallada de puntaje por problema por concursante. El índice del arreglo es un `string` que corresponde al `:problem_alias` del problema en cuestión. Para cada problema, la siguiente información es mostrada:

| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
|`points`|int|Puntos para el problema en particular|
|`penalty`|int|Penalty total para el problema en particular|
|`wrong_runs_count`|int|Total de envíos incorrectos para el problema en particular|

##  GET `contests/:contest_alias/users/`

### Descripción
Regresa una lista con los usuarios que han entrado al concurso.

### Privilegios
Contest director o superior.

### Parámetros
Ninguno

### Regresa

| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
|`user_id`|int|Id del usuario|
|`username`|string|Username del usuario|

##  POST `contests/:contest_alias/update`

### Descripción
Actualiza los contenidos de un concurso.

### Privilegios
Contest director o superior.

### Parámetros
| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
| `title` |string | Título del concurso |Opcional|
|`description` | string | Descripción corta del concurso | Opcional| 
| `alias` | string | Alias del concurso. Su uso principal es para construir las URLs del concurso (ver demás APIs). | Opcional  |
| `start_time`| int | Hora de inicio del concurso en formato UNIX timestamp si `window_length` es nulo. | Opcional|
| `finish_time` | int | Hora de final del concurso en formato UNIX timestamp si `window_length` es nulo. |Opcional |
| `window_length` | int | Si no es nulo, la duración del concurso será `window_length` en minutos y el cronómetro del concurso será particular para cada usuario en vez de general para todos. El cronómetro iniciará cuando el concursante entra por primera vez al concurso. `start_time` determinará entonces la hora a partir de la cual los usuarios pueden empezar a abrir el concurso. (estilo USACO). El default es `null`. | Opcional |
| `public` | int | Determina si el concurso es público o privado (`0` para privado, `1` para público) | |
| `scoreboard` | int | Entero entre `0` y `100` (inclusive) que determina el porcentaje del tiempo en que el scoreboard del concurso podrá ser visto por los concursantes. Cuando el porcentaje es excedido, el scoreboard que se regresa es la última versión que pudo ser pública. Los administradores siempre verán el scoreboard completo. |Opcional |
| `points_decay_factor` | double | Double entre `0` y `1` inclusive. Si este número es distinto de cero, el puntaje que se obtiene al resolver correctamente un problema decae conforme pasa el tiempo. El valor del puntaje estará dado por `(1 - points_decay_factor)` `+ points_decay_factor * TT^2` `/ (10 * PT^2 + TT^2)`, donde `PT` es el penalty en minutos del envío y `TT` el tiempo total del concurso, en minutos.  |Opcional |
| `partial_score` | int | Entero entre `0` y `1` | Opcional|
| `submissions_gap` | int | Número de segundos que el concursante necesita esperar para reenviar una solución. |Opcional |
| `feedback` | string | Opciones: `yes`, `no`, `partial` |Opcional |
| `penalty_time_start` | string | Determina cómo se calcula el penalty. Opciones: `contest`, `problem`, `none`. En caso de `contest`, el penalty para un envío se empieza a contar desde el inicio del concurso. `problem` indica que el penalty se toma en cuenta a partir de que se abre un problema. `none` indica que no habrá penalties en el concurso. |Opcional |
| `penalty_calc_policy` | string | Opciones: `sum`, `max`.  Default: | Opcional |
| `private_users` | json_array[int] | Arreglo de `user_id` de participantes que pueden entrar a un concurso privado. | Opcional |
| `problems` | array[string] | Arreglo de `problem_alias` con los alias de los problemas existentes que se usarán en el concurso. | Opcional |
| `show_scoreboard_after` | int | Si `1`, el scoreboard final será mostrado inmediatamente al final del concurso. Si `0`, el scoreboard se quedará congelado desde el momento en que el parámetro `scoreboard` lo haya indicado, aún después del final del concurso. | Opcional |

### Regresa

| Parámetro | Tipo | Descripción |
| -------- |:-------------:| :-----|
| `status` | string | Si el request fue exitoso, regresa `ok`| 