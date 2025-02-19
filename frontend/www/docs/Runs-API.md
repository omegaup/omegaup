## GET `runs/:run_alias`

### Descripción
Regresa los detalles de un run en particular.

### Privilegios
Usuario loggeado. 

### Parámetros
Ninguno

### Regresa

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
|`time`|int|Hora de envío del run en formato UNIX timestamp|
|`submit_delay`|int|Minutos que pasaron desde el inicio del concurso hasta que se envió el run.|
|`source`|string|Código fuente del run en cuestión|


## GET `runs/:run_alias/adminDetails`

### Descripción
Regresa los detalles completos de run de interés para el administrador del concurso, incluyendo un diff entre los casos oficiales y las salidas producidas por el run.

### Privilegios
Administrador de un concurso o superior.

### Parámetros
Ninguno

### Regresa
**Pending**

## POST `runs/create`

### Descripción
Crea un nuevo run para un problema **en un concurso**.

### Privilegios
Usuario loggeado. 

### Parámetros

| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`problem_alias`|string|Alias del problema||
|`contest_alias`|string|Alias del concurso||
|`language`|string|Lenguaje de programación usado para la solución. Posibles valores: 'kp', 'kj', 'c', 'cpp', 'java', 'py', 'rb', 'pl', 'cs', 'p'||
|source|string|Código fuente de la solución||

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 

## GET `runs/:run_alias/source`

### Descripción
Regresa el código fuente de un run. Si el código no compiló, regresa el error de compilación.

### Privilegios
Usuario loggeado. 

### Parámetros
Ninguno

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 
|`source`|string|Código fuente del problema|
|`compile_error`|string|Error de compilación, si existe.|
