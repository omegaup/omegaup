## POST `clarification/create`

### Descripción
Crea una nueva clarificación para un problema **en un concurso**. Las clarificaciones son creadas como privadas por default.

### Privilegios
Usuario loggeado.

### Parámetros

| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`contest_alias`|string|Alias del concurso||
|`problem_alias`|string|Alias del problema||
|`message`|string|Contenido de la clarificación||

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 
|`clarification_id`|int|Id de la clarificación recién enviada|

## GET clarifications/:clarification_id

#### Descripción
Regresa los detalles de una clarificación de un problema **en un concurso**.

#### Privilegios
Usuario loggeado y con acceso al concurso.

### Parámetros
Ninguno

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`message`|string|El mensaje de la clarificación| 
|`answer`|string|La respuesta a la clarificación|
|`time`|datetime|Fecha de la última modificación a la clarificación|
|`problem_id`|int|Id del problema|
|`contest_id`|int|Id del concurso|


## POST clarifications/:clarification_id/update

### Descripción
Actualizar contenidos de una clarificación de un problema **en un concurso**.

### Privilegios
Administrador de concurso o superior

### Parámetros
| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`contest_alias`|string|Alias del concurso|Opcional|
|`problem_alias`|string|Alias del problema|Opcional|
|`message`|string|Contenido de la clarificación|Opcional|

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 

