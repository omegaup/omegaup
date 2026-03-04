La carpeta `server` se divide principalmente en 2 grandes secciones. Los
`controllers` la logica de negocio, relevante solamente para omegaUp. Y `libs`
que son las librerias auxiliares, escritas en su mayoria por terceros.

## Acceso a los datos

Existen 2 capaz entre la logica del negocio y la base de datos:

* ORM
* [Database.php](https://github.com/omegaup/omegaup/blob/master/frontend/server/libs/Database.php)

La carpeta `libs/dao` contiene las clases  Consiste en los Data Access Objects
(DAO) y los Value Objects (VO). Los  V son clases que se mapean directamente a
cada una de las tablas de la base de datos. Esta clase tiene sus setters y
getters para cada uno de los campos de la base de datos.

Los DAO que son clases estáticas para cada una de las tablas también, y ellas
sirven para hacer obtener y persistentes los objetos VO.

## Controllers

Los controllers es donde se concentra la logica de negocio. Hay un controller
por cada modulo del juez:

- Clarification Controller
- Contest Controller
- Contest Controller
- Grader Controller
- Group Controller
- GroupScoreboard Controller
- Problem Controller
- Run Controller
- School Controller
- Scoreboard Controller
- Session Controller
- Tag Controller
- Time Controller
- User Controller

Los controladores utiliza los DAOs y VOs para acceder a la BD, nunca llama a la
base de datos directamente. Tampoco imprimen cosas en la pagina, para eso se
encargan las vistas.

El usuario solicita `http://www.omegaup.com/registro.php`, `registro.php` es
parte de la vista. Cuando el usuario da click en 'Registrar' la vista hace una
llamada al controller para registrar ciertos datos, el controller busca esos
datos en la base de datos mediante DAOs y VOs y luego el controller registra a
ese usuario mediante DAOs y le dice a la vista que todo salio bien, y la vista
le dice al usuario.

![Diagrama de MVC](http://www.symfony-project.org/images/jobeet/1_4/04/mvc.png)

Mas informacion:
[MVC](http://www.ibm.com/developerworks/java/library/j-dao/)
