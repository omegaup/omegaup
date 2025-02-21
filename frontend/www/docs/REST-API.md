# API de omegaUp

omegaUp está construido a partir de un API [REST](https://es.wikipedia.org/wiki/Representational_State_Transfer) que es posible
invocar directamente. Todos los APIs están implementados mediante peticiones
HTTP (ya sea GET o POST) y todos regresan un status HTTP apropiado y una
respuesta en JSON. Como nos interesa mantener la privacidad de todos nuestros
usuarios y evitar trampa, solo soportamos las llamadas al API a través de HTTPS
y cualquier llamada hecha sobre HTTP inseguro va a fallar porque el servidor
regresará un error HTTP 301 de redirección permanente.

Todos los URL del API comienzan con `https://omegaup.com/api/` y el resto
del path depende de cada función. La convención en la documentación es
únicamente especificar lo que va _después_ de ese prefijo. Por ejemplo, el API
para obtener el tiempo se menciona en este documento como `time/get`, pero el
URL completo sería `https://omegaup.com/api/time/get/`.

Muchas de las llamadas no requieren ningún privilegio especial (e incluso se
pueden hacer simplemente visitando el URL en un navegador), pero otras
únicamente están disponibles a usuarios que hayan iniciado sesión. Para hacer
esto, debes llamar [user/login](https://github.com/omegaup/omegaup/wiki/User-API#post-userlogin).
Todas las llamadas subsecuentes que necesiten autenticación debes realizarlas
agregando un cookie llamado `ouat` (omegaUp Auth Token), con el contenido de
`auth_token` de `user/login`. Toma en cuenta que para evitar trampa, solo
puedes tener una sesión activa al mismo tiempo, así que si inicias sesión
programáticamente, perderás la sesión del navegador y viceversa.

# Categorías

* [Contests](Contests API)
* [Problems](Problems API)
* [Runs](Runs API)
* [Users](User API)
* [Clarifications](Clarifications API)

# Ejemplo

El API para obtener la hora del servidor se puede invocar haciendo una llamada
GET a `https://omegaup.com/api/time/get/`. Como no necesita ningún privilegio,
la respuesta tendrá un status de `HTTP 200 OK` y su contenido será algo similar
a esto, que debe ser interpretado como JSON: `{"time":1436577101,"status":"ok"}`.

## GET `time/get/`

### Descripción

Obtiene el UNIX timestamp según lo reporta el reloj interno del servidor. Útil
para sincronizar el reloj local que posiblemente esté incorrecto.

### Privilegios

Ninguno requerido.

### Parámetros

Ninguno

### Regresa

| Parámetro | Tipo | Descripción  |
| -------- |:-------------:| :-----|
|`status`|string|Si el request fue exitoso, regresa `ok`| 
|`time`|int|UNIX timestamp del servidor|