_Arena_ (o _Frontend_ para v1) no va a tener nada de lógica para validar los problemas: eso es un trabajo para _Grader_. Además, es capaz de comunicarse con jueces externos!

_Grader_ es el encargado de manejar la cola de espera para el jueceo de los programas. Una vez que _Arena_/_Frontend_ le notifique el hecho de que tiene que juecear un problema, examina el registro en la base de datos, lo redirige a la cola del evaluador apropiado (local, uva, pku, tju, livearchive, spoj), y cambia su estado a 'espera'. Una vez en ese punto, grader se 'lava las manos' y vuelve a estar a la espera de otra notificación. Una vez que _Arena_ esté terminado, _Grader_ tiene que enviar un callback en cuanto se haya resuelto el resultado de su validación para que notifique vía Comet al usuario final (_Frontend_ tendrá que hacer polling por lo pronto).

Los evaluadores remotos tienen una lista de espera pequeña (UVa tiene una lista de espera de ~10 slots concurrentes y todos los demás de uno. La razón de esto es porque ninguno de ellos tenían contemplado que existieran consumidores automáticos de su información). Una vez que el servidor ha respondido con un veredicto, el evaluador tiene la responsabilidad de actualizar el registro de la ejecución y cambiar los campos correspondientes.

Para el caso de la evaluación local, _Grader_ debe tener una lista de _Runner_ registrados, para que _Grader_ pueda enviarles el código fuente y el input (cacheable) de los casos. _Runner_ regresará, por cada caso, una notificación de error si ocurrió algún problema, o la salida del programa, con metainformación sobre el tiempo de ejecución y la memoria. Una vez con esa información, _Grader_ ejecutará el validador para cada output, comparándolo con el output esperado, y emitirá una calificación final numérica para la ejecución (que generalmente es la suma de las calificaciones de cada caso). _Grader_ entonces debe actualizar el registro de la ejecución de la base de datos.

## Modo de Uso

Para invocar a Grader desde _Frontend_, lo único que tienes que hacer es enviar un JSON similar a `{'id':1234}` a `https://localhost:21680/grade/`. Es todo.

Como es necesario hacer la llamada usando certificados, es necesario usar la librería de cURL de PHP para hacer las llamadas. Pude hacer esto desde la consola usando:

`curl --url https://localhost:21680/grade/ -d '{"id": 12345}' -E frontend/omegaup.pem --cacert ssl/omegaup-ca.crt --insecure`

El --insecure es porque el certificado del grader no incluye su hostname. Si le pongo localhost de CN al certificado del grader, podemos eliminar esa bandera y ser felices todos :)