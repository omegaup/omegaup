[![omegaUp](frontend/www/media/omegaup.png)](https://omegaup.com)

[![Build Status](https://travis-ci.com/omegaup/omegaup.svg)](https://travis-ci.com/omegaup/omegaup)

[![Contributors](https://img.shields.io/github/contributors/omegaup/omegaup)](https://github.com/omegaup/omegaup/graphs/contributors)

[![Issues open](https://img.shields.io/github/issues/omegaup/omegaup)](https://github.com/omegaup/omegaup/issues?q=is%3Aissue+is%3Aopen)

[![Issues closed](https://img.shields.io/github/issues-closed/omegaup/omegaup)](https://github.com/omegaup/omegaup/issues?q=is%3Aissue+is%3Aclosed)

[![Forks](https://img.shields.io/github/forks/omegaup/omegaup?style=social)](https://github.com/omegaup/omegaup/network/members)

[![Stars](https://img.shields.io/github/stars/omegaup/omegaup?style=social)](https://github.com/omegaup/omegaup/stargazers)

[![Twitter](https://img.shields.io/twitter/follow/omegaup.svg?style=social&label=Follow)](https://twitter.com/omegaup)

[omegaUp](https://omegaup.com) es una plataforma educativa gratuita que ayuda a mejorar las habilidades en programación, usada por miles de estudiantes y profesores en Latinoamérica.

## Directorios

Directorios que se utilizan activamente en el desarrollo.

| Directorio | Descripción |
|------------|-------------|
| [frontend/server/controllers](https://github.com/omegaup/omegaup/tree/master/frontend/server/controllers) | Lógica de negocio que implementa la API de omegaUp. |
| [frontend/server/libs](https://github.com/omegaup/omegaup/tree/master/frontend/server/libs) | Bibliotecas y utilerías. |
| [frontend/server/libs/dao](https://github.com/omegaup/omegaup/tree/master/frontend/server/libs/dao) | Los Data Access Objects [DAO] y Value Objects [VO]. Clases utilizadas para representar los esquemas de la base de datos y facilitar su consumo por los controladores. |
| [frontend/templates](https://github.com/omegaup/omegaup/tree/master/frontend/templates) | Plantillas de Smarty utilizadas para generar el HTML que se despliega a los usuarios. También aquí están los archivos de internacionalización para inglés, español y portugués. |
| [frontend/www](https://github.com/omegaup/omegaup/tree/master/frontend/www) |  Los contenidos completos de la página de internet. |

El resto del código está en otros repositorios

| Repositorio| Descripción |
|------------|-------------|
| [quark](https://github.com/lhchavez/quark) | Incluye el código del grader para la calificación de problemas y ejecutar los códigos bajo minijail, así como el servicio utilizado en los servidores de la nube para servir la cola de envíos. |
| [karel.js](https://github.com/omegaup/karel.js) | La versión oficial de Karel utilizada por la Olimpiada Mexicana de Informática. |
| [omegajail](https://github.com/omegaup/omegajail) | Un mecanismo de ejecución segura que basado en contenedores de Linux y seccomp-bpf. Utiliza [minijail](https://android.googlesource.com/platform/external/minijail/+/master), escrito por el proyecto [Chromium](https://www.chromium.org). |
| [libinteractive](https://github.com/omegaup/libinteractive) | Una librería para hacer problemas interactivos fácilmente.

## Navegadores Soportados

Los navegadores oficialmente soportados son aquellos que soportan [ECMAScript 2015 (ES6)](https://caniuse.com/#feat=es6), e incluyen los siguientes:

| Navegador | Versión |
|-----------|---------|
| [Chrome](https://www.google.com/chrome/) | 51 |
|[Firefox](http://mozilla.org/firefox/releases/) | 68 |
| [Edge](https://www.microsoft.com/edge) | 12 |
| [Safari](https://www.apple.com/safari/) | 12 |

Esto también incluye todos los navegadores basados en Blink / WebKit cuyas versiones sean compatibles con las de Chrome / Safari.

## Licencia

BSD
