# Quiero desarrollar en omegaUp

Gracias por tu interés en contribuir con omegaUp. Aquí encontrarás información sobre el entorno de desarrollo y una descripción breve de nuestro proyecto.

Si no estás tan familiarizado con omegaUp, por favor considera primero visitar [omegaUp.com](https://omegaup.com/), crear una cuenta y resolver 1 o 2 problemas en la plataforma. Después puedes visitar [omegaup.org](https://omegaup.org/) para aprender más acerca de la organización y las diferentes vertientes de nuestro trabajo.

## Contenido

- [Entorno de desarrollo](#entorno-de-desarrollo)
- [Arquitectura (resumen)](#arquitectura-resumen)
- [Estructura del código](#estructura-del-código)
- [Decisiones de diseño](#decisiones-de-diseño)

## Entorno de desarrollo
El primer paso es la instalación del entorno de desarrollo. Usamos docker para montarlo y los sistemas operativos donde se ha usado principalmente son Windows y Ubuntu. Puede ejecutarse en macOS, pero necesitarás configuraciones adicionales.
 - [Instalación del entorno de desarrollo](https://github.com/omegaup/omegaup/wiki/Instalaci%C3%B3n-de-m%C3%A1quina-virtual).


## Arquitectura (resumen)
Estos son los componentes de omegaUp y cómo se conectan entre sí _(los nombres son codenames temporales)_:

* [UX](https://github.com/omegaup/omegaup/wiki/UX): Hay dos componentes de la interfaz de usuario: _UX_, que es la interfaz de _Arena_, y el resto de la página. El resto de la página, lo genera directamente _Frontend_. JS.
* [Frontend](https://github.com/omegaup/omegaup/wiki/Frontend): _Frontend_ es una colección de controladores (del modelo MVC) que manejan toda la interacción con el sitio: administración de problemas y concursos, usuarios, rankings, problemas resueltos y faltantes, el scoreboard, etc. _Frontend_ se comunica con _Backend_ para compilar y ejecutar programas. PHP+MySQL.
* Backend: El subsistema de evaluación. Escrito en Go.
  * [Grader](https://github.com/omegaup/omegaup/wiki/Grader): El encargado de mantener la cola de envíos y enviarlos a el/los _Runner_, recibir la respuesta y establecer un veredicto.
  * [Runner](https://github.com/omegaup/omegaup/wiki/Runner): _Runner_ es un sistema des-centralizado y asíncrono de compilación y ejecución de programas. Los demás sistemas se pueden comunicar con _Runner_ mediante un API RESTful. Runner sabe cómo compilar, ejecutar y pasarle el _input_ a los programas que envíe el usuario, así como verificar si están bien o no. Es básicamente un frontend bonito y distribuido para _Minijail_.
  * Minijail: Es un fork del sandbox de Linux de Chrome OS. Puede ejecutar código en C, C++, Perl, Python, Ruby, Java, Karel. Escrito en C.

Para más detalles, puedes consultar los dos papers que han sido publicados en el journal del IOI:

* Luis Héctor CHÁVEZ, Alan GONZÁLEZ, Joemmanuel PONCE.
omegaUp: [Cloud-Based Contest Management System and Training Platform in the Mexican Olympiad in Informatics](http://ioinformatics.org/oi/pdf/v8_2014_169_178.pdf)
* Luis Héctor CHÁVEZ. [libinteractive: A Better Way to Write Interactive Tasks](https://ioinformatics.org/journal/v9_2015_3_14.pdf)


## Estructura del código

El código se encuentra en `/opt/omegaup`. La instalación de desarrollo trae dos cuentas preconfiguradas: `omegaup` (administrador) y `user` (usuario normal). Su contraseñas son `omegaup` y `user`, respectivamente.

Estos son los directorios que estamos usando activamente en el desarrollo:

 - [frontend/database](https://github.com/omegaup/omegaup/tree/main/frontend/database): contiene el archivo SQL principal para construir el esquema de la base de datos de la plataforma. También se incluyen todos los archivos SQL que se agregan cada vez que se realiza una modificación a la base de datos.
 - [frontend/server/src](https://github.com/omegaup/omegaup/tree/main/frontend/server/src): contiene todas las clases PHP y carpetas referentes al servidor, entre ellas:
   - [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO) Clases de Data Access Object (DAO) para manipular la base de datos: almacenar de forma permanente y recuperar instancias de objetos a través de las consultas de MySQL. Existen dos carpetas para los DAO. En una se encuentran todos los métodos que manipulan y consultan la base de datos con condiciones específicas, aquellas necesarias para presentarle la información al usuario dependiendo de acciones particulares. La otra carpeta se llama Base y contiene los métodos básicos de manipulación y consulta, estos son: crear, eliminar, actualizar, obtener un registro por su identificador, y obtener todos los registros.
      - [frontend/server/src/DAO/VO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO/VO) Clases de Value Object (VO) que se utilizan para construir los diferentes objetos que se necesitan en omegaUp.com. No hay necesidad de modificarlos, ya que estos se crean automáticamente
      - [frontend/server/src/DAO/Base](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO/Base) Estos objetos contienen métodos principales para interactuar con la base de datos, como lo son: crear, actualizar, eliminar y recuperar registros de cada uno de los objetos DAO. No hay necesidad de modificarlos, ya que estos se crean automáticamente
   - [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers): Clases de los controladores donde se encuentran los diferentes métodos para las API de la plataforma y que utilizan los DAO para obtener y manipular la información solicitada.
 - [frontend/tests](https://github.com/omegaup/omegaup/tree/main/frontend/tests): contiene las clases PHP donde se crean pruebas unitarias para probar el correcto funcionamiento de todos los controladores y sus métodos. Aquí también se encuentran clases Python donde se crean pruebas para probar el correcto flujo de las interacciones con la interfaz de usuario (UI).
 - [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www): contiene todos los archivos TS y Vue.js referentes al frontend. En los archivos TS se hacen todas las llamadas necesarias a los controladores del servidor a través de las API y se reciben los datos solicitados. En estos archivos también se construyen los componentes de los archivos Vue.js que muestran la información al usuario a través del HMTL y CSS. Por cada componente de Vue se incluye un archivo de pruebas unitarias para probar que se muestren y oculten los elementos del componente necesarios cuando es requerido, y que emita los eventos con la información esperada, entre otras cosas.
 - [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates): Aquí están los archivos de internacionalización para inglés, español y portugués. Además de la plantilla principal donde se cargan las librerías que se utilizarán en todas las vistas (`Bootstrap` por ejemplo).

#### Te puede interesar:
 - [Coding guidelines](https://github.com/omegaup/omegaup/wiki/Coding-guidelines).
 - [Comandos utiles para el desarrollo](https://github.com/omegaup/omegaup/wiki/Comandos-%C3%BAtiles-para-el-desarrollo).

## Decisiones de diseño

* Encriptación para todo: TODA la comunicación con OmegaUp y sus subsistemas debe ir encriptada: cliente a servidor y comunicación entre componentes. Nada de peros. La razón para esto es minimizar la probabilidad de trampas en concursos (en algún concurso de programación, alguien se puso a sniffear el tráfico). Además, con eso del firesheep y ataques similares, hacer esto es trivial.
* Facebook Connect / OAuth / OpenID: ¡minimicemos la cantidad de passwords en el sistema! Usando _cualquier_ sistema de federación de identidad haremos del internet un lugar más feliz para todos. Pero también hay que tomar en cuenta que debemos de soportar varias identidades para la misma persona (caso de uso básico: _user_ se registra en OmegaUp con user@email.com usando OpenID. Después, un profe lo agrega a OmegaUp por motivos de una clase como a0001@escuela.mx. Debe haber un mecanismo mediante el cual confirme que es el dueño de esa otra cuenta y ambas direcciones y _user_ son aliases para la misma persona).
* Desacoplamiento de componentes: La razón de eso es que hay un plan de migración de parte de la funcionalidad de _Frontend_ hacia _Arena_ en un futuro y necesitamos que sea lo más fácil posible.
* Lenguajes de programación: Todo el backend está escrito en Go/C, _Frontend_ está en PHP+MySQL, _UX_ está en VueJS+TypeScript.

### Continúa...

| Tema                                                  | Descripción                                                  |
| -----------------------------------------------------  | ------------------------------------------------------------ |
| [Cómo empezar a desarrollar](https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-Hacer-un-Pull-Request) | Configuraciones en git, cómo enviar un PR                    |
| [Arquitectura](https://github.com/omegaup/omegaup/wiki/Arquitectura)  | Arquitectura de software de omegaUp.com                      |
| [Release and Deployment](https://github.com/omegaup/omegaup/wiki/Release-&-deployment)  | Cómo y cuándo es el deployment                               |
