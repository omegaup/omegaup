Implementar una insignia es bastante simple, sólo debes seguir los siguientes pasos:

1. Crear un alias para la insignia, dicho alias debe ser único y debe tener 32 caracteres como máximo.

2. Crear una carpeta en [`/frontend/badges/`](https://github.com/omegaup/omegaup/tree/master/frontend/badges), cuyo nombre sea igual al alias de la insignia a implementar. A partir de ahora, esta carpeta será llamada `badgeFolder`.

3. Si la insignia tiene un ícono personalizado, el archivo `SVG` del mismo será agregado en `badgeFolder`, con nombre `icon.svg`.

4. Crear en `badgeFolder` un archivo llamado `query.sql`, dicho archivo debe contener la sentencia `SQL (MySQL)` que SELECCIONE los `user_id` de aquellos usuarios que deben recibir la insignia propuesta. Para poder seguir dicha lógica, es necesario conocer el [esquema de la base de datos de omegaUp](https://github.com/omegaup/omegaup/blob/master/frontend/database/schema.sql).

5. Crear el archivo [`localizations.json`](https://github.com/omegaup/omegaup/blob/master/frontend/badges/legacyUser/localizations.json) dentro de `badgeFolder`, éste debe contener las traducciones del nombre y descripción de la insignia, en los idiomas español (es), inglés (en) y portugués (pt). Recuerda que el tamaño máximo para el nombre de una insignia es de **50 caracteres**.

6. Para que las traducciones sean cargadas desde localizations.json hacia los archivos correspondientes, es necesario ejecutar el script: `./stuff/lint.sh`.

7. Se debe crear un archivo `test.json` dentro de `badgeFolder`, éste especificará a través del campo `testType`, la forma en que serán ejecutadas las pruebas unitarias de una insignia:

   -  `“testType”: “apicall”` Consiste en hacer uso de las apis de los controladores para crear los datos pertinentes (problemas, usuarios, concursos, ejecuciones, etc). Para hacer ésto, se debe crear el campo “actions” con un arreglo de todas las acciones que serán ejecutadas, las cuales pueden ser:

        - `changeTime`: permite modificar la fecha del sistema.
        - `apicalls`: permite llamar a una API determinada, estableciendo además el usuario y contraseña del usuario que hace la llamada y los parámetros que serán pasados. Las APIs son todas aquellas funciones públicas estáticas que tienen como prefijo api dentro de cada uno de los controladores ubicados en [esta carpeta](https://github.com/omegaup/omegaup/tree/master/frontend/server/controllers).
        - `scripts`: permite ejecutar algún cron script de omegaUp (`aggregateFeedback`, `assignBadges`, `updateUserRank`). Estos scripts se ubican en [esta carpeta](https://github.com/omegaup/omegaup/tree/master/stuff/cron).

        En este tipo de tests, se debe agregar al final el campo `expectedResults`, el cual deberá contener el nombre de usuario de aquellos usuarios que recibirán la insignia.

        Ejemplo:

      - https://github.com/omegaup/omegaup/blob/master/frontend/badges/coderOfTheMonth/test.json

   - `“testType”: “phpunit”` Consiste en crear una prueba unitaria con nombre de archivo igual a `alias de la insignia + “Test.php”`. Este archivo deberá ser guardado en la [carpeta de tests para las insignias](https://github.com/omegaup/omegaup/tree/master/frontend/tests/badges) y deberá seguir la estructura clásica de las pruebas unitarias ya implementadas en omegaUp, incluso puede hacer uso de los [factories](https://github.com/omegaup/omegaup/tree/master/frontend/tests/factories).
       
        Ejemplos:

     - https://github.com/omegaup/omegaup/blob/master/frontend/badges/100solvedProblems/test.json
     - https://github.com/omegaup/omegaup/blob/master/frontend/tests/badges/Badge_100solvedProblemsTest.php

    Cada opción tiene sus ventajas y desventajas, sugerimos usar phpunit para insignias con muchas llamadas APIs idénticas, en cualquier otro caso, es mejor usar apicalls.

8. Finalmente hay que ejecutar las pruebas para verificar que la insignia implementada cumpla con los criterios especificados y que la consulta y prueba unitaria propuesta sean ejecutadas satisfactoriamente. Para esto, se puede usar alguno de los siguientes scripts:
   - `./vendor/bin/phpunit --bootstrap frontend/tests/bootstrap.php --configuration frontend/tests/phpunit.xml frontend/tests/ --debug`
   - `./vendor/bin/phpunit --bootstrap frontend/tests/bootstrap.php --configuration frontend/tests/phpunit.xml 
 frontend/tests/badges/ --debug`
   - `./stuff/runtests.sh`

9. Si no es arrojado ningún error, ¡ya puedes enviar tu Pull Request para agregar la nueva insignia!


Acá hay algunos Pull Requests ya enviados para crear insignias, que puedes usar para guiarte:
- [Administrador de Concurso](https://github.com/omegaup/omegaup/pull/2602/files)
- [Administrador de Concurso Virtual](https://github.com/omegaup/omegaup/pull/2603/files)

Ante cualquier duda que te surja, no dudes en contactarnos :)