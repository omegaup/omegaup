# Bienvenido a omegaUp!

## Código

Estos son los directorios que estamos usando activamente en el desarrollo:

* **bin/** Incluye binarios pre-compilados y scripts que se requieren en ciertas partes de omegaup. Por ejemplo: los compiladores de Karel.
* **frontend/**  Incluye el código del frontend: la lógica de la página principal y la arena para concursar y enviar problemas.
* **grader/**  Incluye el código del grader para la calificación de problemas.
* **runner/** Incluye el código que se encarga de ejecutar los códigos bajo el sandbox.

Y estos directorios también están en desarrollo, pero tienen su propio repositorio:
* **minijail/** La nueva versión del sandbox! Este proyecto también tiene licencia BSD, pero diferente copyright owner (los contribuidores del proyecto Chromium)
* **karel/** El intérprete oficial de Karel para la OMI.
* **sandbox/** Incluye el código del sandbox, herramienta que evita que los códigos ejecuten rutinas maliciosas.
