# Bienvenido a omegaUp!

[![Build Status](https://travis-ci.org/omegaup/omegaup.svg?branch=master)](https://travis-ci.org/omegaup/omegaup)

## Código

Estos son los directorios que estamos usando activamente en el desarrollo:

* [frontend/server/controllers](https://github.com/omegaup/omegaup/tree/master/frontend/server/controllers):
  La lógica de negocio que implementa el API de omegaUp.
* [frontend/server/libs](https://github.com/omegaup/omegaup/tree/master/frontend/server/libs):
  Bibliotecas y utilerías.
* [frontend/server/libs/dao](https://github.com/omegaup/omegaup/tree/master/frontend/server/libs/dao):
  Los Data Access Objects [DAO].  Clases utilizadas para representar los
  esquemas de la base de datos y facilitar su consumo por los controladores.
* [frontend/templates](https://github.com/omegaup/omegaup/tree/master/frontend/templates):
  plantillas de Smarty utilizadas para generar el HTML que se despliega a los
  usuarios.  También aquí están los archivos de internacionalización para
  inglés, español y portugués.
* [frontend/www](https://github.com/omegaup/omegaup/tree/master/frontend/www):
  Los contenidos completos de la página de internet.

El resto del código está en otros repositorios:

* [backend](https://github.com/omegaup/backend): Incluye el código del grader
  para la calificación de problemas y ejecutar los códigos bajo minijail, así
  como el servicio utilizado en los servidores de la nube para servir la cola
  de envíos.
* [omegaUp Karel](https://github.com/omegaup/karel): Es la versión que utiliza
  omegaUp para evaluar programas de Karel.  Es un port de OMI Karel a consola
  de comandos Linux.
* [minijail](https://github.com/omegaup/minijail): Un fork de
  [minijail](https://chromium.googlesource.com/chromiumos/platform2/+/master/minijail/),
  escrito por el proyecto (Chromium)[https://www.chromium.org] y adaptado para
  ser usado en concursos de programación.
* [libinteractive](https://github.com/omegaup/libinteractive): Una librería
  para hacer problemas interactivos fácilmente.
