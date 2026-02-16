
##  frontend/www
Contenido:

* js/
* css/
* admin/

###  js
Como su nombre lo indica, aqui iran todos los fuentes de javascript y frameworks de javascript. Aqui iran los JS's enteros, con comentarios y legibles para nosotros. Cuando se quiera hacer un release de openjuan debemos hacer un script que minifique estos javascripts, quitando espacios, comentarios, etc, etc...

### css
Al igual que js, aqui iran los css normales sin minificar.

### admin
... pensando como estara la cosa...

##  frontend/server
Contenido:

* dao/
* controllers/

Ninguno de estos modulos deben de ser accessible al mundo exterior, el  unico que puede llamarlos es la interfaz de usuario. Es por eso que estan debajo de la carpeta www.

###  DAO/VO

La carpeta *dao* contiene las clases para la capa de accesso a datos. Tiene 2 cosas que hay que conocer: *data access objects* y *value objects*. Los *value objects* (VO) no son mas que clases que se mapean directamente a cada una de las tablas de la base de dato; asi entonces, ahi dentro hay una clase denominada Usuarios, ya que hay una tabla con el mismo nombre. Esta clase tiene sus setters y getters para cada uno de los campos de la base de datos. Los *data access objects* (dao) que son clases estaticas para cada una de las tablas tambien, y ellas sirven para obtener y hacer persistentes los objetos *vo*.

Ejemplo: Bbuscar todas la ejecuciones del usuario con el mail de 'alanboy@acm.org'
``` php
	// crear el objeto
	$u = new Usuarios();
	$u->setEmail( 'alanboy@acm.org' );


	// busca los usuarios con ese email
	$resultados = UsuariosDAO::search( $u );

	if(sizeof($resultados) != 1){
		die('Este email no existe en nuestros registros');
	}

	// de si existir, ahora $resultados en un arreglo 
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
```
A simple vista esta capa agrega mas lineas de codigo, pero lo he encotrado mucho mucho mas legible y debuggeable. Es por eso que la mayoria de los frameworks de php lo implementan. Esta es mi implementacion del modelo dao, es por eso que si surge cualquier cosa, lo podemos modificar a nuestro gusto. Tambien cabe mencionar que el framework se encarga de inyecciones, transacciones y esta totalmente docuementado en **frontend/docs/dao_docs.html**.

[[Aqui hay mas info sobre este modelo|http://www.ibm.com/developerworks/java/library/j-dao/]]

### Controllers
Los controllers es donde se hace la magina. el codigo anterior iria en un controller apropiado... habria un controller por cada modulo del juez... registro, login, envio de soluciones, mensajeria, etc  etc... el controller utiliza los dao's y vo's para hacer su magina, nunca llama a la base de datos directamente. los controllers tampoco imprimen cosas en la pagina, solo hacen la magia... 

a los controllers los llama, ahora si... lo que esta arriba de www que llamare 'vista'. por ejemplo, asi se registra un usuario:

el wey pide 'openjuan.com/registro.php' ese archivo es la vista. cuando el le da click en 'registrar' la vista le le pide al controller que registre a estos datos, el controller busca esos datos en la base de datos mediante dao's y vo's y luego el controller registra a ese wey mediante dao's y vo's y le dice a la vista que todo salio bien, y la vista le dice al usuario.

De esta manera hemos separado perfectamente: 
``` php
   dao/vo                         controller						vista
los datos <----- ----> la logica del negocio <-----  ----->la interfaz de usuario
```


[[ http://www.symfony-project.org/images/jobeet/1_4/04/mvc.png ]]


### Conclusion
De esta manera... a la par de hacer nuestro pedo, estamos haciendo el API !! El api son los controllers !! 
	
## frontend/docs

