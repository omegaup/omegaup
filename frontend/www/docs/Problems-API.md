## POST `problem/create`

### Descripción
Crea un nuevo problema.

### Privilegios
Usuario loggeado

### Parámetros
| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`author_username`|string|Username del usuario que originalmente redactó el problema||
|`title`|string|Título del problema||
|`alias`|string|Alias corto del problema||
|`source`|string|Fuente del problema (UVA, OMI, etc..)||
|`public`|int|`0` si el problema es privado. `1` si el problema es público||
|`validator`|string|Define cómo se van a comparar las salidas del los concursantes con las salidas oficiales. Ver la tabla de **validadores**||
|`time_limit`|int|Límite de tiempo de ejecución para cada caso del problema en milisegundos. (TLE)||
|`memory_limit`|int|Límite de memoria en tiempo de ejecución para cada caso del problema en KB (MLE)||
|`order`|string|||
|`problem_contents`|FILE|Un archivo ZIP con los contenidos del problema: [Cómo escribir problemas para omegaup](Cómo-escribir-problemas-para-Omegaup)||

#### Validadores
| Tipo | Descripción |
| -------- |:-------------|
|`literal`||
|`token`||
|`token-caseless`||
|`token-numeric`||

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 
|`uploaded_files`|array[]string|Arreglo de archivos que fueron desempacados||

## GET `problems/:problem_alias`

### Descripción
Regresa los detalles de un problema **dentro de un concurso**.

### Privilegios
Usuario loggeado. Si el concurso es privado, el usuario requiere estar invitado.

### Parámetros
| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`contest_alias`|string|Alias del concurso||
|`lang`|string|Idioma del concurso. Default es `es`|Opcional|

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`title`|string|Título del problema|
|`author_id`|int|Autor del problema|
|`validator`|string|Validador del problema. Ver tabla de **validadores**|
|`time_limit`|int|Tiempo límite de ejecución en milisegundos|
|`memory_limit`|int|Memoria límite en KB|
|`visits`|int|Visitas totales a este problema|
|`submissions`|int|Total de envíos para este problema en todos los concursos|
|`accepted`|int|Total de envíos correctos (AC) para este problema en todos los concursos|
|`difficulty`|int|Dificultad del problema determinada por Omegaup|
|`creation_date`|datetime|Fecha de creación del problema|
|`source`|string|Fuente del problema.|
|`runs`|array|Regresa un arreglo con todos los runs del concursante para este problema. Ver tabla `runs`|

#### Runs

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`guid`|string|Identificación del run|
|`language`|string|Lenguaje del envío.|
|`status`|string|Status del problema en el proceso de calificación. Posibles valores: 'new','waiting','compiling','running','ready'|
|`veredict`|string|Veredicto del juez sobre el problema. Veredictos posibles: 'AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE'|
|`runtime`|int|Tiempo total de ejecución en milisegundos que tardó el envío en resolver los casos del problema.|
|`memory`|int|Memoria total que usó el run para resolver los casos de prueba.|
|`score`|double|Double entre `0` y `1` que indica el total de casos resueltos, donde `1` significa que se resolvieron todos los casos.|
|`contest_score`|int|Puntaje ponderado del run. Es el puntaje que se muestra en el scoreboard.|
|`time`|datetime|Hora de envío del run|
|`submit_delay`|int|Minutos que pasaron desde el inicio del concurso hasta que se envió el run.|
