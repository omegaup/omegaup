## POST `user/login`

### Descripción
Loggea a un usuario al sistema.

### Privilegios
Ningunos

### Parámetros

| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`usernameOrEmail`|string|Username o email del usuario a loginear||
|`password`|string|Password del usuario||

### Regresa 

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 
|`auth_token`|string|Token que se debe usar en las demás llamadas a la API para identificarse|

## POST `user/create`

### Descripción
Crea un nuevo usuario en el sistema

### Privilegios
Ninguno

### Parámetros

| Parámetro | Tipo | Descripción  | Opcional? |
| -------- |:-------------:| :-----|:-----|
|`username`|string|Nombre corto del usuario||
|`password`|string|Password|
|`email`|string|Email del usuario a crear|

### Regresa 

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 
