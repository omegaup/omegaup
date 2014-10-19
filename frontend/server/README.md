La carpeta `server` se divide principalmente en 2 grandes secciones. Los `controllers` la logica de negocio, relevante solamente para OmegaUp. Y `libs` que son las librerias auxiliares, escritas en su mayoria por terceros.


## Acceso a los datos
Existen 2 capaz entre la logica del negocio y la base de datos:
  * [ORM][1] 
  * [AdoDB][2]

La carpeta `libs/dao` contiene las clases  Consiste en los Data Access Objects (DAO) y los Value Objects (VO). Los  V son clases que se mapean directamente a cada una de las tablas de la base de datos. Esta clase tiene sus setters y getters para cada uno de los campos de la base de datos.

Los DAO que son clases estaticas para cada una de las tablas tambien, y ellas sirven para hacer obtener y persistentes los objetos VO.

Ejemplo: buscar todas la ejecuciones del usuario con el mail de 'alanboy@acm.org'
`php	// Crear el objeto VO
	$u = new Usuarios();
	$u->setEmail( 'alanboy@acm.org' );

	// Busca los usuarios
	$resultados = UsuariosDAO::search( $u );

	if(sizeof($resultados) != 1){
		die('Este email no existe en nuestros registros');
	}

	// De si existir, ahora $resultados en un arreglo 
	// de objetos 'Usuarios' que tienen ese correo
	// solo debe haber uno, asi que agarramos el 
	// que esta en el index 0
	$usuario = $resultados[0];

	// ahora en $usuario hay un objeto Usuarios,
	// ahora hay que buscar en los runs
	$e = new Ejecuciones();
	$e->setUsuarioID( $usuario->getUsuarioID() );
	$resultados = EjecucionesDAO::search( $e );

	// ahora $resultados es un arreglo con objetos 
	// 'Ejecuciones' de ese usuario
	foreach($resultados as $run){
		echo "ID: " . $run->getEjecucionID() . "<br>";
	}
	

## Controllers
Los controllers es donde se concentra la logica de negocio. Hay un controller por cada modulo del juez:
  * Clarification Controller
  * Contest Controller
  * Contest Controller
  * Grader Controller
  * Group Controller
  * GroupScoreboard Controller
  * Problem Controller
  * Run Controller
  * School Controller
  * Scoreboard Controller
  * Session Controller
  * Tag Controller
  * Time Controller
  * User Controller


Los controladores utiliza los DAOs y VOs para acceder a la BD, nunca llama a la base de datos directamente. Tampoco imprimen cosas en la pagina, para eso se encargan las vistas.

El usuario solicita `http://www.omegaup.com/registro.php`, `registro.php` es parte de la vista. Cuando el usuario da click en 'Registrar' la vista hace una llamada al controller para registrar ciertos datos, el controller busca esos datos en la base de datos mediante DAOs y VOs y luego el controller registra a ese usuario mediante DAOs y le dice a la vista que todo salio bien, y la vista le dice al usuario.

![Diagrama de MVC](http://www.symfony-project.org/images/jobeet/1_4/04/mvc.png)


Mas informacion:
[MVC](http://www.ibm.com/developerworks/java/library/j-dao/)

[1]: https://github.com/CaffeinaSoftware/web-framework
[2]: https://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=2&cad=rja&uact=8&ved=0CCYQFjAB&url=http%3A%2F%2Fadodb.sourceforge.net%2F&ei=5EFEVOfvLO76iAKh3YCABQ&usg=AFQjCNEa80MA9f1_35F7l57GJuaAHW9ENA&sig2=xdukzgxFOcbI7cVhGnObNA
