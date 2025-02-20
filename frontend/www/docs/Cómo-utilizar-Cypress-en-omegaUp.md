## Pasos previos
Hay que tener instalado `node` para poder utilizar `cypress`:
```bash
sudo apt-get install nodejs
sudo apt-get install npm
```

Si aún así se obtiene:
```bash
No version of Cypress is installed in: ~/.cache/Cypress/9.1.1/Cypress

Please reinstall Cypress by running: cypress install

----------

Cypress executable not found at: ~/.cache/Cypress/9.1.1/Cypress/Cypress

----------

Platform: linux-x64 (Ubuntu - 20.04)
Cypress Version: 9.1.1
```

Es necesario instalar `cypress` de la siguiente forma:
```bash
./node_modules/.bin/cypress install
```

Otros errores que pueden aparecer son los siguientes:
```bash
It looks like this is your first time using Cypress: 13.3.0


Cypress failed to start.

This may be due to a missing library or dependency. https://on.cypress.io/required-dependencies

Please refer to the error below for more details.

----------

~/.cache/Cypress/13.3.0/Cypress/Cypress: error while loading shared libraries: libnss3.so: cannot open shared object file: No such file or directory

Platform: linux-x64 (Ubuntu - 22.04)
Cypress Version: 13.3.0
```
Hay que instalar las siguientes dependencias:
```bash
sudo apt install libgconf-2-4 libatk1.0-0 libatk-bridge2.0-0 libgdk-pixbuf2.0-0 libgtk-3-0 libgbm-dev libnss3-dev libxss-dev
```

O el siguiente error:
```bash
It looks like this is your first time using Cypress: 13.3.0


Cypress failed to start.

This may be due to a missing library or dependency. https://on.cypress.io/required-dependencies

Please refer to the error below for more details.

----------

~/.cache/Cypress/13.3.0/Cypress/Cypress: error while loading shared libraries: libasound.so.2: cannot open shared object file: No such file or directory

----------

Platform: linux-x64 (Ubuntu - 22.04)
Cypress Version: 13.3.0
```

Hay que instalar la dependencia:
```bash
sudo apt-get install libasound2
```

## Introducción
**Nota:** Actualmente las pruebas se ejecutan desde fuera del contenedor de Docker

Todo lo relacionado con Cypress se encuentra dentro de la carpeta `./cypress` que se encuentra en el directorio raíz.

## GUI
Para correr el GUI de Cypress (que es genial), utiliza `./node_modules/.bin/cypress open`. Esto abrirá una pantalla con todas las pruebas disponibles. 

![](https://i.imgur.com/M6lhhVC.png)

Si le damos a `Run n Integration Specs` se correrán todas las pruebas de una. Si solo queremos correr una, le damos click a la que estamos interesados.

### Vista de pruebas
![](https://i.imgur.com/sJ7WAj8.png)

Ahora podrás ver en tiempo real la ejecución de la prueba y si haces hover en algún comando, puedes ver una `snapshot` de la pagina en ese momento de la prueba. Tambien, puedes abrir la consola (click derecho -> inspeccionar elemento) y ver el output de las acciones de la pagina.

Podemos seleccionar un elemento con la herramienta selector y así conseguir el comando necesario para poder seleccionar ese objeto dentro del código de la prueba.

![](https://i.imgur.com/rllMilf.png)

## Headless
Si no quieres correr la prueba con GUI, puedes utilizar `./node_modules/.bin cypress run` para ejecutar las pruebas sin interfaz. De igual manera, se va a crear un video dentro de `./cypress/videos` para que puedas ver, si gustas, como se realizo la prueba. 

## Escribir pruebas para cypress
Las pruebas se encuentran contenidas dentro de `./cypress/integration` y llevan de nombre de archivo `nombre.spec.ts`. (Se pueden crear subfolders si es necesario)

Para los comandos basicos. https://docs.cypress.io/guides/getting-started/writing-your-first-test

### Comandos
Una feature interesante de Cypress son los **comandos personalizados**. Los comandos personalizados son simplemente funciones que tienen dentro comandos de cypress para hacer pruebas. Por ejemplo, un comando personalizado puede ser `login(username, password)`. En este caso, este comando iniciara sesión y así ya no se tiene que reescribir en cada una de las pruebas. 

Estos comandos se declaran dentro de `./cypress/support/commands.js`
```typescript
Cypress.Commands.add('login', (username, password) => {
  cy.visit('/');
  cy.get('.navbar-right > .nav-item > .nav-link').click();
  cy.get('.form-horizontal > :nth-child(1) > .form-control').clear();
  cy.get('.form-horizontal > :nth-child(1) > .form-control').type(username);
  cy.get(':nth-child(2) > .form-control').clear();
  cy.get(':nth-child(2) > .form-control').type(password);
  cy.get(':nth-child(3) > .btn').click();
});
```
Como estamos utilizando typescript es necesario agregar el tipo del comando dentro de `./cypress/support/cypress.d.ts`
```typescript
// <reference types="cypress"/>

declare namespace Cypress {

  interface Chainable {
    login(username: string, password: string): void;
    // Aquí van los demás comandos
  }
}
```
https://docs.cypress.io/api/cypress-api/custom-commands

### Eventos
En algunas ocasiones, queremos seguir corriendo una prueba a pesar de haber tenido una excepción de x cosa. Para eso existen los eventos de Cypress. Estos se pueden declarar tanto de manera global como local. Por ejemplo, al correr Cypress, la API del Sign-In de Google no reconocía `127.0.0.1` (ip de docker) como host permitido para iniciar sesión, y esto estaba rompiendo las pruebas, entonces se puede agregar un evento global donde, al momento de que aparezca esta excepción, no termine la prueba y siga corriendo.

Los eventos globales se agregan en `./cypress/support/index.js`. Los locales dentro del archivo de la prueba.

```typescript
// index.js
import './commands';

Cypress.on('uncaught:exception', (err, runnable) => {
  if (err.error.includes('idpiframe_initialization_failed')) {
    // Google API sign in error
    return false;
  }
});

```
https://docs.cypress.io/api/events/catalog-of-events

### Cypress Studio
La manera mas genial para escribir pruebas! Si abrimos la GUI de Cypress y nos vamos a cualquier prueba, al momento de finalizar, hay un botón que te deja grabar tu mismo la prueba, y todas las interacciones que hagas con la pagina, las agregara a la prueba que estés modificando!

![](https://docs.cypress.io/_nuxt/img/extend-activate-studio.91d9bd8.png)

O si movemos el mouse un poco mas arriba, podemos crear un test totalmente nuevo.

Después de grabarlo, estos se guardan en el respectivo archivo como comandos. 

**Nota:** Como Studio guarda tus interacciones con comandos, no es necesario apurarte a hacer x acción, ya que el tiempo que te tardes entre cada interacción no se toma en cuenta.

### Plugins importantes
Actualmente se tiene dos plugins instalados dentro de Cypress: `WaitUntil` y `File-Upload`.

El primero, como su nombre lo dice, espera a que cierto elemento(s) estén presente dentro del DOM. Si bien, cypress ya cuenta con esto por defecto, hay ocasiones donde, por ejemplo, le das click al botón de `iniciar sesión` y cypress rápidamente le da click a un botón despegable del navbar. Antes de que pueda darle a la opción correspondiente, se termina de iniciar sesión y se refresca la pagina y Cypress no puede darle al botón del navbar, rompiendo la prueba. Para evitar esto utilizamos `cy.waitUntil()`. 
```typescript
    cy.login('user', 'user'); // Inicio sesión
    cy.waitUntil(() =>
      cy.get('.active > .container-lg > .slide > :nth-child(1) > h2'),
    ); // Me espero hasta encontrar un elemento de la Landing Page
    cy.get('.nav-problems > .nav-link').click(); // Ahora si, hago la navegación
    cy.get('.dropdown-menu > [href="/problem/new/"]').click();
```

El segundo, simplemente te permite subir un archivo:
```typescript
   cy.get('input[type="file"]').attachFile(
      '../../frontend/tests/resources/testproblem.zip',
    );
```
## Github Actions
Muchas veces, puede que tus pruebas funcionen en local, pero cuando intentas hacer un PR, falla la prueba dentro de las Github Actions. Para poder ver exactamente en que fallo la prueba, podemos ir, dentro del mismo PR, hacia `Checks` y luego a `CI`
![](https://i.imgur.com/6iu4w1L.png)

Hacemos scroll hacia abajo en esta pantalla y descargamos los `test-logs`
![](https://i.imgur.com/4Huvtpy.png)

Ahi, podrás ver todos los videos generados por el contenedor de Cypress corriendo en Github Actions, y podrás ver perfectamente que esta pasando con tu prueba y así debuggear de una mejor manera!