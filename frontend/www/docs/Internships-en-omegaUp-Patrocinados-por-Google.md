- [Proceso de Aplicación](#proceso-de-aplicaci-n)
      - [Nuestro proceso de aplicación consiste en 4 fases.](#nuestro-proceso-de-aplicaci-n-consiste-en-4-fases)
    + [Fase 1: Completa el test de programación](#fase-1--completa-el-test-de-programaci-n)
    + [Fase 2: Familiarizate con nuestro código.](#fase-2--familiarizate-con-nuestro-c-digo)
    + [Fase 3: Escribe tu Aplicación](#fase-3--escribe-tu-aplicaci-n)
    + [Fase 4: Entrevista](#fase-4--entrevista)
- [Ideas List](#ideas-list)
  * [Cuentas para Menores de Edad](#cuentas-para-menores-de-edad)
  * [Migración a Cypress](#migraci-n-a-cypress)
  * [Optimizar omegaUp.com para móviles](#optimizar-omegaupcom-para-m-viles)
  * [Detector de Plagio](#detector-de-plagio)
  * [Generación automática de diplomas](#generaci-n-autom-tica-de-diplomas)
- [Como Comenzar a Desarrollar](#como-comenzar-a-desarrollar)
- [Comunicación](#comunicaci-n)
- [Preguntas frecuentes](#preguntas-frecuentes)

# Proceso de Aplicación

#### Nuestro proceso de aplicación consiste en 4 fases.

### Fase 1: Completa el test de programación 

 - Primero, si no tienes una ya, crea una cuenta en [omegaUp.com](https://omegaUp.com).
 - Únete a [GSoC 2023 omegaUp Test](https://omegaup.com/arena/gsoc2023).  La prueba consiste de 4 problemas, tienes que resolver por lo menos 3 para pasar. **En caso de plagios se descalificara a todos los estudiantes involucrados**, así que por favor no compartas tus soluciones con otros aplicantes.

### Fase 2: Familiarizate con nuestro código.

Te pedimos que completes la fase 1 antes de que comiences a trabajar con nuestro código.

 - Sigue estas [intrucciones](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) para instalar tu entorno de desarrollo.
 - Busca alguna tarea pequeña que resolver en nuestro [issue tracker](https://github.com/omegaup/omegaup/issues), especialmente de nuestra lista de ["Good first issues"](https://github.com/omegaup/omegaup/labels/Good%20first%20issue), o pide que se te asigne uno en nuestro [canal de Discord](https://discord.gg/gMEMX7Mrwe). Alternativamente, puedes encontrar bugs o mejoras en omegaup.com tu mismo, reportalas en nuestro issue tracker.
 - Antes de comenzar a resolverlo, comenta el issue que te interesa resolverlo y pide que se te asigne. Esto es para asegurarnos de que nadie más está trabajando en el issue que tu encontraste.
 - Implementa tu solución y mandala a revisión. Una vez que sea aceptado tu cambio puedes moverte a la tercera fase.

### Fase 3: Escribe tu Aplicación

En este paso esperamos que ya estés familiarizado con nuestro entorno de desarrollo y con nuestro código ya que eso hace más fácil entender nuestra lista de ideas de proyectos mostrada al inicio de esta página. **Te pedimos que no comiences a trabajar en esta fase hasta que tengas por lo menos una solución aceptada como se describe en la fase 2.**

 - Crea un documento de diseño para tu proyecto usando [este template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit). **El documento tiene que estar escrito en inglés**, no tiene que ser un inglés perfecto solo tiene que ser entendible.
 - También te recomendamos mandarnos un borrador de tu aplicación para darte retroalimentación. Envía el link a tu borrador a través de este formulario `https://forms.gle/XRCU4MS9oAJCXuk3A`. Asegurate de que cualquier persona con la liga pueda ver el documento y pueda comentar.
 - Vamos a tratar de darte la mayor cantidad posible de retroalimentación. Sin embargo, no vamos a dar retroalimentación a ningún1 candidato que no haya completado las fases 1 y 2.
 - Cuando consideres que tu aplicación está lista, no olvides **enviarla a [Google](https://summerofcode.withgoogle.com/age-verification/student/?next=%2Fstudent-signup%2F)**. Si no la envías, no podrás ser seleccionado para Summer of Code 2023.

### Fase 4: Entrevista
Una vez que tu documento ha sido enviado, vamos a seleccionar a una lista corta de candidatos basado en las primeras 3 fases y vamos a agendar entrevistas con ellos. La entrevista consistirá en preguntas conductuales así como preguntas técnicas.

# Ideas List

> Te invitamos a visitar omegaup.com para que conozcas las funcionalidades de nuestra plataforma. Esta es una **lista de ideas** de proyectos, igualmente puedes proponer tus propias ideas. Únete a nuestro [Discord channel](https://discord.gg/gMEMX7Mrwe) y siéntete libre de hacer preguntas ahí!


## Cuentas para Menores de Edad

**Descripción**:

En la actualidad, estudiantes menores a 13 años de edad no pueden usar omegaUp ya que para ello omegaUp necesita cumplir con una serie de regulaciones. La idea es agregar un nuevo tipo de cuenta restringida para menores que les permitirá acceder y consumar contiendo que ha sido verificado a mano content así como ser invitado a concursos y cursos por parte de sus profesores.

**Resultados Esperados**:

Usuarios menores a 13 años pueden registrarse en omegaup.com y pueden aprender programación de forma segura, ya sea por su propia cuenta o con la ayuda de sus profesores.

**Habilidades Preferentes**:
* PHP
* MySQL
* Vue.js
* Typescript

**Posibles mentores**:

[heduenas](https://github.com/heduenas), [pabo99](https://github.com/pabo99)

**Tamaño estimado del proyecto:**

350 horas

**Nivel de habilidad**:

Medio

## Migración a Cypress

**Descripción**:

Tenemos varias pruebas end-to-end que usan el framework Selenium. Al paso del tiempo se han hecho lentas, caras de mantener y flaky (dan falsos negativos) Hemos comenzado a escribir los nuevos tests en el framework Cypress, el cual nos da mucho mejores resultados y es más fácil de usar. Este proyecto consiste en migrar todos los tests existentes al framework de Cypress y añadir nuevos tests usando Cypress.

**Resultado esperado**:

Todas las pruebas end-to-end corren en Cypress y tienen buen performance. Permitiéndole a los desarrolladores de omegaUp ser más productivos y tener mayor satisfacción con el proceso de desarrollo, produciendo un mejor producto final para nuestros usuarios.

**Habilidades preferidas**:
* Typescript
* Frameworks de pruebas end-to-end
* Vue.js
* PHP

**Posibles mentores**:

[pabo99](https://github.com/pabo99), [carlosabcs](https://github.com/heduenas)

**Tamaño estimado:**

350 horas

**Nivel de habilidad**:

Entre baja y media

## Optimizar omegaUp.com para móviles

**Descripción**:

Hay muchos estudiantes que no tienen acceso a una computadora pero sí tienen acceso a un teléfono móvil. La mayoría de estos teléfonos son de rango medio a bajo. Acutalmente, nuestro sitio web consume demasiados recursos (ancho de banda, RAM, CPU). Este proyecto consiste en hacer una versión móvil de omegaUp.com que funcione muy bien en móviles. 

**Resultado esperado**:

Cuando un estudiante visite omegaUp.com desde un teléfono móvil, la página se carga rápido y los estudiantes pueden navegar y aprender en omegaUp.com sin mucho problema.

**Habilidades preferidas**:
* Vue.js
* Typescript
* PHP

**Posibles mentores**:

[carlosabcs](https://github.com/carlosabcs), [tvanessa](https://github.com/tvanessa)

**Tamaño estimado:**

175 horas

**Nivel de habilidad**:

Medio

## Detector de Plagio

**Descripción**:

Siempre que hay un concurso/curso de programacion, hay riesgo de que los concursantes hagan trampa compartiendo sus soluciones entre ellos. Actualmente la detección de plagio se hace de una manera muy ad-hoc y que no es sostenible para la escala de omegaUp. En este proyecto se va a generar un reporte de las similaridades entre códigos una vez que termine un concurso/curso. Esto se puede llevar a cabo integrando algún servicio externo, por ejemplo [MOSS](https://theory.stanford.edu/~aiken/moss/).

**Resultado esperado**:

omegaUp realiza un análisis de similaridades entre códigos cada que termina un concurso/curso para que el administrador pueda rápidamente identificar y descalificar a los estudiantes que cometieron plagio.

**Habilidades preferidas**:
* PHP
* SQL
* Golang
* Vue.js

**Posibles mentores**:

[carlosabcs](https://github.com/carlosabcs), [pabo99](https://github.com/pabo99)

**Tamaño estimado:**

350 horas

**Skill level**:

Alto

## Generación automática de diplomas

**Breve descripción**:

omegaUp alberga cientos de cursos y de concursos cada año, muchos de los cuales otorgan diplomas a los estudiantes. Actualmente los organizadores tienen que generar sus diplomas de forma manual fuera de omegaUp. Este proyecto consiste en añadir la funcionalidad para que los organizadores puedan generar diplomas de forma masiva para sus concursos/cursos con mínimo esfuerzo. Estos certificados deben de incluir un código QR que pueda ser usado para verificar la autenticidad del documento.

**Resultados esperados**:

Los organizadores de concursos/cursos pueden generar diplomas de forma masiva para sus concursos/cursos con mínimo esfuerzo. Los estudiantes son notificados cada que les otorgan un nuevo diploma, pueden descargarlo en cualquier momento como PDF y su diploma contiene un código QR para verificar la autenticidad.

**Habilidades preferidas**:
* Python
* PHP
* SQL
* Python
* RabbitMQ
* Vue.js

**Posibles mentores**:

[pabo99](https://github.com/pabo99), [heduenas](https://github.com/heduenas)

**Tamaño estimado:**

350 horas

**Nivel de habilidad**:

Alto

# Como Comenzar a Desarrollar
Si te interesa trabajar con nosotros este verano, primero que nada, nos sentimos muy honrados de que estés interesado en nuestra organización y queremos que el proceso de aplicación sea lo mas sencillo y agradable posible.

Para familiarizarte con omegaUp.com y comenzar a desarrollar te recomendamos seguir estos pasos:

 - Visita [omegaup.org](omegaup.org) para conocer más acerca de nuestro trabajo, nuestra visión, y la gente que está siendo beneficiada por nuestra labor.
 - Lee [este articulo](http://www.ioinformatics.org/oi/pdf/v8_2014_169_178.pdf) publicado por nuestros oc-fundadores para aprender acerca de la arquitectura y el diseño de nuestra plataforma.

# Comunicación
## Si tienes preguntas acerca del [entorno de desarrollo](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Quiero-desarrollar-en-omegaUp.md) or the [codebase](https://github.com/omegaup/omegaup) o sobre el proceso de aplicación para GSoC, sigue nuestra guia [Obteniendo ayuda](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) para obtener respuestas a tus preguntas.

**Nuestro principal medio de comunicación con candidatos es nuestro [canal de Discor](https://discord.gg/gMEMX7Mrwe). Te invitamos a unirte!**

# Preguntas frecuentes #
   * **La instalación del entorno de desarrollo me esta fallando.** Sigue nuestra guía [Obteniendo ayuda](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) para resolver tu problema.
   * **Necesito saber hablar inglés?** Muy poco. Es necesario escribir tu aplicación en inglés, leer algunas documentaciones en inglés y leer/escribir código en inglés. Fuera de eso, usarás solamente español.
  * **Cuántos becarios se van a contratar?** Vamos a intentar conseguir 3 estudiantes, pero no hay ninguna garantía de que obtengamos los 3. Sabremos con certeza a mitades de mayo de 2023.
  * **Como escogemos a nuestros becarios?** Vamos a revisar todas las aplicaciones que recibamos y que estén completas y vamos a escoger a nuestros candidatos basados en 3 cosas:
    * Nivel de habilidad. Esto se busca medir en las 4 fases de la aplicación.
    * Plan de trabajo presentado en tu aplicación. Debe de estar bien estructurado y ser realista en cuanto a tiempos.
    * Cultural fit. Nos gusta trabajar con gente que promueve la inclusión y que pro activamente buscan ayudar a sus compañeros. Una buena manera de mostrar eso es ayudando a otros candidatos que hacen preguntas en el [canal de Discord](https://discord.gg/gMEMX7Mrwe).
* **Hay aplicaciones de muestra?** Estos son 2 buenos ejemplos:
 * Carlos Cordova - [2018](https://docs.google.com/document/d/1ZEnC33hW4WjZ1WcsDjEtuIeNPuvW62q_hBFjhFosLOI/edit#heading=h.30j0zll)
 * 
    Vincent Fango - [2018](https://docs.google.com/document/d/1ei3AV1ByLpONbTgO3Grnl8aVOIL2hwz48IxLmDyuOWA/edit#heading=h.gjdgxs). También puedes ver la presentación final del proyecto de Vincent Fango: <br>
[![Vincent](https://img.youtube.com/vi/cOnJ_5M1DFs/0.jpg)](https://www.youtube.com/watch?v=cOnJ_5M1DFs)
* **Puedo postular a multiples proyectos?** Este año te pedimos que incluyas solo 1 diseño en tu aplicación. Cuando estés programando tu proyecto y si lo terminas muy pronto puedes comenzar a trabajar en un proyecto adicional.
* **Existen otras oportunidades en omegaUp si no soy seleccionado para GSoC?** Claro que sí. Puedes unirte como voluntario o puedes aplicar a un programa de becario de practicas profesionales si eres estudiante y tu escuela cuenta con algún programa de practicas.
