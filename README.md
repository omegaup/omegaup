# Bienvenido a omegaUp!

[![Build Status](https://travis-ci.com/omegaup/omegaup.svg?branch=master)](https://travis-ci.com/omegaup/omegaup)

## Código

Estos son los directorios que estamos usando activamente en el desarrollo:

* [frontend/server/controllers](https://github.com/omegaup/omegaup/tree/master/frontend/server/controllers):
  La lógica de negocio que implementa el API de omegaUp.
* [frontend/server/libs](https://github.com/omegaup/omegaup/tree/master/frontend/server/libs):
  Bibliotecas y utilerías.
* [frontend/server/libs/dao](https://github.com/omegaup/omegaup/tree/master/frontend/server/libs/dao):
  Los Data Access Objects [DAO] y Value Objects [VO].  Clases utilizadas para representar los
  esquemas de la base de datos y facilitar su consumo por los controladores.
* [frontend/templates](https://github.com/omegaup/omegaup/tree/master/frontend/templates):
  plantillas de Smarty utilizadas para generar el HTML que se despliega a los
  usuarios.  También aquí están los archivos de internacionalización para
  inglés, español y portugués.
* [frontend/www](https://github.com/omegaup/omegaup/tree/master/frontend/www):
  Los contenidos completos de la página de internet.

El resto del código está en otros repositorios:

* [quark](https://github.com/lhchavez/quark): Incluye el código del grader
  para la calificación de problemas y ejecutar los códigos bajo minijail, así
  como el servicio utilizado en los servidores de la nube para servir la cola
  de envíos.
* [karel.js](https://github.com/omegaup/karel.js): La versión oficial de Karel
  utilizada por la Olimpiada Mexicana de Informática.
* [omegajail](https://github.com/omegaup/omegajail): Un mecanismo de ejecución
  segura que basado en contenedores de Linux y seccomp-bpf. Utiliza
  [minijail](https://android.googlesource.com/platform/external/minijail/+/master),
  escrito por el proyecto [Chromium](https://www.chromium.org).
* [libinteractive](https://github.com/omegaup/libinteractive): Una librería
  para hacer problemas interactivos fácilmente.

Todo el código de omegaUp está distribuido bajo la licencia BSD.

## Navegadores Soportados

Los navegadores oficialmente soportados son aquellos que soportan [ECMAScript
2015 (ES6)](https://caniuse.com/#feat=es6), e incluyen los siguientes:

* [Chrome](https://www.google.com/chrome/): 51
* [Firefox](http://mozilla.org/firefox/releases/): 68
* [Edge](https://www.microsoft.com/edge): 12
* [Safari](https://www.apple.com/safari/): 12

Esto también incluye todos los navegadores basados en Blink / WebKit cuyas
versiones sean compatibles con las de Chrome / Safari.
