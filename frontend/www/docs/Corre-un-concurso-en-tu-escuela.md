Si quieres correr un concurso en tu escuela, asegúrate que los siguientes dominios sean accesibles desde las máquinas que usarán los concursantes:

* Una de las direcciones de omegaUp:
  * https://arena.omegaup.com si quieres usar el modo lockdown (asegúrate de no permitir la otra o el modo lockdown no va a servir).
  * https://omegaup.com si quieres el modo normal
* https://ssl.google-analytics.com

Los siguientes son opcionales:

* https://secure.gravatar.com (Opcional, muestra el avatar en la esquina superior derecha)
* https://accounts.google.com (Opcional, para hacer login via Google)
* https://connect.facebook.net y https://s-static.*.facebook.com (Opcional, para hacer login via Facebook)

Todas son conexiones por https, así que únicamente es necesario el puerto 443. Las conexiones por el puerto 80 no funcionarán ya que se realiza una redirección automática a https. También hay que configurar el firewall para hacer DENY en vez de DROP. De otra manera, el browser va a intentar conectarse con los dominios mencionados arriba y al no recibir respuesta, esperará por 20-30s antes de rendirse, haciendo que la página cargue lentísimo.

## Modo lockdown

Si en vez de usar el dominio normal, te conectas por https://arena.omegaup.com/, entrarás a modo lockdown. Este modo se hizo por si quieres garantías más fuertes que los alumnos no puedan intercambiar información a través de la plataforma. Mucha de la funcionalidad de la página está restringida, y para mantener la integridad del bloqueo no se pueden hacer excepciones del bloqueo por ningún motivo. Las cosas más comunes que estarían restringidas en modo lockdown:

* Modo admin
* Modo de práctica
* Ver código de envíos pasados (se muestra en vez un mensaje de error).

Nuevamente, si tu situación requiere que alguna de las cosas que están bloqueadas en modo lockdown funcionen, no uses el modo lockdown y conéctate por https://omegaup.com.

## Sistema operativo

Usamos Ubuntu 14.04 para calificar los envíos, así que cualquier distribución relativamente reciente de Linux debe ser 100% compatible con nuestro ambiente de evaluación. Windows tiene algunos problemas, como que utilizan `%I64d` para imprimir long longs en vez de `%lld`, y que muchos editores de Windows incluyen el archivo `conio.h` (que no existe en Linux).

## Grupos grandes y otras consideraciones

Si planeas hacer un concurso grande con mucha gente (100 o más concursantes) conectándose, por favor avísanos de antemano para garantizar que vamos a tener la capacidad de servir tu concurso ese día.