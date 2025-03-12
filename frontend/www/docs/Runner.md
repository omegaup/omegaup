_Runner_ es _otro_ servicio web que tiene la responsabilidad de compilar y ejecutar los códigos que lleguen a _Grader_. Toda la información se transmitirá mediante JSON gzippado (con content-type: application/json+gzip), a través de HTTPS, con autenticación mutua por medio de certificados y tiene los siguientes URLs:

# /compile/

Compila una ejecución. Esta llamada es síncrona.

### Entrada

>     {
>        'lang': 'lang-name', // uno de 'c', 'cpp', 'java', 'py', 'rb', 'pl', 'p'
>        'code': ['code_1', 'code_2', ..., 'code_n']
>     }

el JSON debe contener un campo `lang` y un `code`, que tiene una lista de cadenas, que son los archivos que se van a compilar. Se creará una carpeta temporal con nombre pseudoaleatorio (usando `mkdtemp`) y ahí se extraerán los archivos. El primer archivo de la lista se guardará en el archivo main.`lang` y los demás en f01.`lang`, f02.`lang`, f03.`lang`, etc.  Ningún archivo debe depender de su nombre para ejecutarse (i.e. en Java los archivos no pueden tener clases públicas) por cuestiones de facilitarnos la vida. Una vez extraídos todos los archivos al sistema de archivos, se ejecutará el sandbox para compilar el programa, usando el profile que corresponda al lenguaje.

### Salida

>     {
>         'error': 'El compilador ha regresado .....'
>     }

En caso de haber un error, el servicio regresará un JSON con un campo llamado `error` con la salida del proceso de compilación.

>     {
>         'token': 'ABJdfoeKFPer9183409dsfDFPOfkaR834JFDJF='
>     }

En caso de que la compilación sea exitosa, se regresará un JSON con un campo llamado 'token', con un token opaco que se deberá enviar en las llamadas subsecuentes para identificar el envío.

# /run/

Ejecuta un programa que previamente fue compilado con un _input-set_ de entrada. Esta llamada es síncrona, y puede tomar mucho tiempo (hasta el tiempo límite).

### Entrada

>     {
>         'token': 'ABJdfoeKFPer9183409dsfDFPOfkaR834JFDJF=',
>         'input': 'd41d8cd98f00b204e9800998ecf8427e'
>     }

_Grader_ inicialmente asume que el _Runner_ siempre tiene el input set. Una vez localizado el input set, _Runner_ extraerá el lenguaje del token que anteriormente mencioné que era opaco (jejeje :P), y con esa información, el _Sandbox_ ejecutará el programa con cada uno de los inputs, guardando la salida estándar y metainformación como si tuvo algún error la ejecucióne, el tiempo y memoria que tomó. Entonces, construye un JSON con esa información y lo regresa. Una vez terminada de enviar la respuesta, la carpeta temporal, junto con todos los archivos generados es eliminada automáticamente.

En caso de que _Runner_ no tenga el input-set, regresa un JSON con el error apropiado. 

### Salida

>     {
>         'results': [
>             {
>                 'name': '05', 'status': 'OK', 'time': 103, 'memory': 1235,
>                 'output': 'BlaBlaBla'
>              },
>             {
>                 'name': '06', 'status': 'TLE', 'time': 3000, 'memory': 1235,
>              }
>         ]
>     }

Esta es la información que regresa en caso de éxito: una lista con los resultados de cada caso, con información de tiempo, memoria y la salida (en caso de no haber errores).

>     {
>         'error': 'missing input'
>     }

Este es el mensaje (textual) que se regresa cuando _Runner_ no tiene el input set solicitado.

# /input/
Sube un input-set a _Runner_ para su uso posterior.

### Entrada

>     {
>         'input': 'd41d8cd98f00b204e9800998ecf8427e',
>         'cases': [
>             {
>                 'name': '05', 'data': 'blablablablabla'
>             },
>             {
>                 'name': '06', 'data': 'blebleblebleble'
>             },
>         ]
>     }

Esto simplemente sube los archivos de entrada al sistema donde está el _Runner_.

### Salida

>     {
>         'status': 'ok'
>     }