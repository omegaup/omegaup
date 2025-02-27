A continuación se muestra una lista de comandos/scripts que son útiles para las personas involucradas en el desarrollo:


### Ejecutar todos los linters
**Comando:**
```bash
./stuff/lint.sh
```   
**Descripción:** Script necesario para ejecutar todas las validaciones del código con los que cuenta omegaUp. En caso de no ejecutarse directamente, este se ejecutará al momento de hacer `git push`

**Ubicación de Ejecución:** Desde fuera del contenedor de Docker en el directorio raíz del proyecto. e.g. `ubuntu@pc:~/dev/omegaup$`


### Generar archivos `.lang`
**Comando:**
```bash
./stuff/lint.sh --linters=i18n fix --all
```
**Descripción:** Genera los archivos `*.lang*` a partir de los archivos `es.lang`, `en.lang` y `pt.lang`

**Ubicación de Ejecución:** Desde fuera del contenedor de Docker en el directorio raíz del proyecto. e.g. `ubuntu@pc:~/dev/omegaup$`


### Ejecutar todas las pruebas y validaciones de omegaUp en PHP
**Comando:**
```bash
./stuff/runtests.sh
```
**Descripción:** Actualmente se encarga de ejecutar las pruebas en `PHPUnit`, además de ejecutar el validador de tipos en `MySQL` y en `PSALM` 

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz


### Ejecutar pruebas de Cypress
**Comando:**
```bash
npx cypress open
```
**Descripción:** Abre el Cypress Test Runner, una interfaz gráfica que te permite ejecutar y depurar interactivamente las pruebas de Cypress en un navegador. Te permite seleccionar y ejecutar pruebas individuales, ver los resultados detallados de las pruebas e inspeccionar cualquier fallo en tiempo real, proporcionando un entorno eficiente para escribir, ejecutar y depurar pruebas. Podría requerir configuraciones adicionales para funcionar correctamente en el entorno local.

**Ubicación de Ejecución:** Desde fuera del contenedor de Docker.  


### Restablecer la base de datos al estado inicial
**Comando:**
```bash
./stuff/bootstrap-environment.py --purge
```
**Descripción:** Script necesario para restaurar la base de datos a su estado inicial. Además, ejecuta una serie de peticiones API para poblar el entorno de desarrollo local. Con este script, se pueden crear concursos, cursos, problemas y todo lo necesario para realizar pruebas manuales. La información generada se almacena en el archivo `stuff/bootstrap.json`

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz



### Ejecutar validadores de tipos en PHP
**Comando:**
```bash
find frontend/ \
    -name *.php \
    -and -not -wholename 'frontend/server/libs/third_party/*' \
    -and -not -wholename 'frontend/tests/badges/*' \
    -and -not -wholename 'frontend/tests/controllers/*' \
    -and -not -wholename 'frontend/tests/runfiles/*' \
    -and -not -wholename 'frontend/www/preguntas/*' \
  | xargs ./vendor/bin/psalm \
    --long-progress \
    --show-info=false
```
**Descripción:** Comando para ejecutar los validadores de tipos en los archivos de php

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz



### Ejecutar pruebas unitarias de PHP para un archivo específico
**Comando:**
```bash
./stuff/run-php-tests.sh frontend/tests/controllers/$MY_FILE.php
```
**Descripción:** Comando para ejecutar pruebas unitarias en php de un archivo individual ,si se desean ejecutar todas las pruebas se quita el nombre del archivo.

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz



### Aplicar cambios a schema.sql
**Comando:**
```bash
./stuff/update-dao.sh
```
**Descripción:** Script necesario para aplicar los cambios en el archivo `schema.sql` cuando se agrega un nuevo archivo de migración en `.sql` (Este comando sólo funcionará hasta que se hace commit del archivo de migración)

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz


### Aplicar migraciones de base de datos localmente
**Comando:**
```bash
./stuff/db-migrate.py migrate --databases=omegaup,omegaup-test
```
**Descripción:** Script necesario para aplicar localmente los cambios al schema hechos a través de nuevo archivo de migración en `.sql`

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz



### Ejecutar pruebas unitarias de Vue
**Comando:**
```bash
yarn run test:watch
```
**Descripción:** Ejecuta las pruebas unitarias de Vue en modo de "observación". Esto significa que cuando haces cambios en los archivos de prueba o en los archivos de código fuente que están siendo testeados, las pruebas se vuelven a ejecutar automáticamente para reflejar los cambios. Esto facilita el desarrollo, ya que permite a los desarrolladores ver los resultados de las pruebas de manera continua, sin tener que ejecutarlas manualmente cada vez que se realiza un cambio en el código. El comando se ejecuta dentro del entorno de desarrollo local de la aplicación, proporcionando un flujo de trabajo eficiente y dinámico durante el desarrollo de las pruebas. 

**Ubicación de Ejecución:** Dentro del contenedor de Docker en el directorio raíz 


### Ejecutar un archivo de prueba unitaria de Vue específico
**Comando:**
```bash
./node_modules/.bin/jest frontend/www/js/omegaup/components/$MI_ARCHIVO
```
**Descripción:** Comando útil para ejecutar un archivo individual de las pruebas unitarias de vue
**Ubicación de Ejecución:** Funciona bien dentro y fuera del contenedor



### Reiniciar el servicio Docker
**Comando:**
```bash
systemctl restart docker.service
```
**Descripción:** Comando que sirve para reiniciar el proceso de Docker. Utilizarlo cuando al ejecutar Docker aparece el siguiente error: `OCI runtime exec failed: exec failed: unable to start container process: open /dev/pts/0: operation not permitted: unknown`

**Ubicación de Ejecución:** Fuera del contenedor de Docker