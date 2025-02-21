# General

* Muchas de las cosas expresadas en las guías de estilo se validan en GitHub al momento de correr las pruebas.
  - Siempre que se pueda, se debería automatizar este proceso para evitar regresiones y facilitar la labor de la revisión de código.
* Todo el código debe declarar los tipos de datos en las interfaces (parámetros de funciones y el tipo de datos que regresan).
  - También se prefiere que los arreglos / mapas adentro de las funciones tengan anotaciones de tipos para facilitar su comprensión.
  - En el frontend usamos [TypeScript](https://www.typescriptlang.org/), en PHP usamos [Psalm](https://psalm.dev/) y en Python usamos [mypy](http://mypy-lang.org/).
* Todo el código (y los comentarios) se escriben en inglés.
* Cambios en funcionalidad o funcionalidad nueva deben ser acompañados por sus respectivos tests nuevos/modificados.
* Se evita el uso de `null` y `undefined` siempre que sea posible. Especialmente en los parámetros de funciones.
  - `null` se debe usar únicamente cuando hay algo que un usuario no proveyó.
  - `undefined` se debe usar únicamente en la declaración de parámetros opcionales en funciones de TypeScript.
  - Se debe evitar declarar tipos que puedan ser simultáneamente `null` y `undefined`.
  - Si hay varios parámetros o campos que pueden ser `null`/`undefined`, todos deberían poder serlo independientemente: si hay subconjuntos de parámetros/campos que se deben pasar juntos, es mejor crear un tipo intermedio que los agrupe. Por ejemplo,
    ```php
    function MyFunc(
        \OmegaUp\DAO\Problems $problem,
        bool $customValidator,
        string|null $validatorLanguage = null,
        int|null $validatorTimeout = null,
    ): void {
    ```
    En esta función, `$validatorLanguage` y `$validatorTimeout` se _deben_ especificar si `$customValidator` es `true`, y se deben _no_ especificar en caso contrario. Entonces es mejor crear otro tipo que los agrupe:
    ```php
    /**
     * @psalm-type ValidatorOptions=array{language: string, timeout: int}
     */
    // ...
    /**
     * @param null|ValidatorOptions $customValidatorOptions
     */
    function MyFunc(
        \OmegaUp\DAO\Problems $problem,
        array? $customValidatorOptions = null,
    ): void {
    ```
    - Cada parámetro `undefined`/`null` duplica el número de posibles combinaciones recibibles para una función, y esto crece exponencialmente. Hay que intentar limitar esto a < 10.
* Se deben evitar funciones que cambien su comportamiento significativamente dependiendo de banderas / condicionales. Se prefiere que se declaren múltiples funciones y que se llame la función apropiada.
* Siempre que sea posible, hay que usar el [Guard Clause Pattern](https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html).
* No debe haber código no-usado. Se prefiere eliminar código que no se usa a comentarlo. Al final de cuentas, siempre se puede recuperar usando la historia de git.
* Se prefiere el uso de [camelCase](https://en.wikipedia.org/wiki/Camel_case) para nombres de funciones/variables/clases. Excepciones donde se usa [snake_case](https://en.wikipedia.org/wiki/Snake_case):
  - columnas en MySQL
  - variables y parámetros en Python
  - parámetros del API
* Se prefiere evitar el uso de abreviaturas en el código (tanto en nombres de variables como en comentarios). No siempre las abreviaturas son completamente obvias para todas las personas.
* Se prefiere minimizar la distancia entre donde se declaran las variables y la primera vez que se usan, para minimizar la cantidad de código no-relevante que hay que leer para saber qué contiene una variable.
* Los comentarios deberían usarse para explicar partes complicadas o no-intuitivas del código. No es necesario agregar comentarios para describir lo que hace el código y se prefiere que mejor expliquen _el por qué_.

# Formato

* Le delegamos el trabajo de decidir cómo darle el formato al código a herramientas automatizadas. Usamos [yapf](https://github.com/google/yapf) para Python, [prettier.io](https://prettier.io/) para TypeScript/Vue y [phpcbf](https://github.com/squizlabs/PHP_CodeSniffer) para PHP. Puedes validar que se está cumpliendo con el estilo llamando `./stuff/lint.sh validate`.
  - Se usan 2/4 espacios (depende del tipo de archivo), no tabs.
  - El fin-de-linea debe ser estilo Unix (`\n`), no estilo Windows (`\r\n`).
  - La llave va en la misma línea que el statement anterior.
    ```php
    if (condicion) {
        bloque;
    }
    ```
  - Un espacio entre los keywords y el paréntesis para: `if`, `else`, `while`, `switch`, `catch`, `function`.
  - Las llamadas a funciones no tienen espacio antes del paréntesis.
  - No se dejan espacios adentro del paréntesis.
  - Un espacio después de cada coma, pero sin espacio antes.
  - Todos los operadores binarios deben tener un espacio antes y uno después.
  - No debe haber más de una línea en blanco seguida.
* No deben haber comentarios vacíos.
* No se deben usar comentarios de bloque `/* ... */`, solo de línea `// ...`.

# PHP

* Los tests se deben correr antes de hacer commit y todos deben pasar 100%, sin excepción.
* Se debe evitar funciones que requieran O(n) consultas a la base de datos (esto incluye operaciones con los DAOs). En vez, se deberían crear consultas manualmente para que hagan toda la funcionalidad en un sólo viaje redondo.
* Con excepción de las funciones que implementan el API, ninguna función puede recibir un parámetro de tipo `\OmegaUp\Request`. Todas las funciones de API deben validar los parámetros, extraerlos a variables con los tipos correctos y llamar a las funciones con estas variables en vez del `\OmegaUp\Request` original.
* Todas las funciones deben estar comentadas con el estilo:
  ```php
  /**
   * set
   *  
   * Si el cache está prendido, guarda value en key con el timeout dado
   *      
   * @param string $value
   * @param int $timeout   
   * @return boolean
   */
  public function set($value, $timeout) { ...
  ```
* Se deben usar excepciones para reportar condiciones erróneas. El uso de funciones que regresen true/false es permitido cuando son valores esperados.
* Todas las APIs deben reportar sus resultados en forma de arreglos asociativos.
* Usar [RAII](http://en.wikipedia.org/wiki/Resource_Acquisition_Is_Initialization) cuando sea conveniente, principalmente en la administración de recursos (archivos, etc...)

# Vue

* Se debe evitar crear componentes que cambien su comportamiento significativamente dependiendo de banderas / condicionales. Se prefiere que el comportamiento distinto se abstraiga usando [`slot`s](https://vuejs.org/v2/guide/components-slots.html) para que el componente que lo llame pueda personalizarlo. Si hay varios componentes que van a usar este comportamiento distinto, se puede crear _otro_ componente que tenga el `slot` apropiado.
* Como toda la interfaz se debe poder mostrar en varios idiomas, no se deben escribir textos directamente y en vez se deben usar cadenas de traducción.
* Se debe evitar concatenar cadenas de traducción, porque distintos idiomas pueden usar distintos órdenes para las palabras. Se prefiere crear una cadena de traducción con parámetros reemplazables y usar la función `ui.formatString`:
  ```typescript
  <!-- Ejemplo malo:
  contestRanking = "Contest ranking: "
  -->
  <div>{{ T.contestRanking }} {{ user.rank }} {{ user.username }} {{ user.name }} {{ user.score }}</div>

  <!-- Ejemplo mejor:
  contestRanking = "Contest ranking: %(rank) %(username) %(name) %(score)"
  -->
  <div>{{ ui.formatString(T.contestRanking, { rank: user.rank, user: user.username, name: user.name, score: user.score }) }}</div>
  ```
* Se debe evitar asignar colores en formato hexadecimal o `rgb(...)`. En vez de esto, los colores deberían declararse como variables para que no se rompa el modo oscuro.
* Se debe evitar el uso de [lifecycle hooks](https://v3.vuejs.org/api/options-lifecycle-hooks.html) _a menos_ que haya algo en el componente que interactúe directamente con el DOM.
  - También se debe evitar la interacción directa con el DOM.
* Se prefiere el uso de [propiedades computadas y watchers](https://vuejs.org/v2/guide/computed.html) en vez de manipular variables programáticamente.
* Se recomienda agregar **storybook** stories para cada componente nuevo, y en caso de modificar un componente existente agregar o actualizar las stories relacionadas al mismo. [Ver más](/docs/Coding-Guidelines-%E2%80%90-Storybook.md)

# TypeScript

* Cuando una función tenga más de 2-3 parámetros y _sobre todo_ si esos parámetros son del mismo tipo y definitivamente si tiene varios parámetros opcionales, se prefiere cambiar los parámetros por un objeto. Ejemplo:
  ```typescript
  // Ejemplo malo:
  function updateProblem(problem: Problem, previousVersion: string, currentVersion: string, points?: int): void {
    // ...
  }

  // Ejemplo mejor:
  function updateProblem({
    problem,
    previousVersion,
    currentVersion,
    points,
  }: {
    problem: Problem;
    previousVersion: string;
    currentVersion: string;
    points?: int;
  }): void {
    // ...
  }
  ```
* Se prefiere el uso de camelCase para nombres de funciones/variables/clases.
* Se debe evitar el uso de [Type Assertions](https://www.typescriptlang.org/docs/handbook/2/everyday-types.html#type-assertions). Únicamente se permite en los siguientes casos:
  - Cuando se está interactuando con el DOM (`document.querySelector` y sus amigos).
  - Para declarar que una literal vacía (`null`, `{}`, `[]`) es de cierto tipo ejemplo: `null as null | string`, `[] as []types.Foo`.
  - En las pruebas, para declarar `params` en el constructor de Vue.
* jQuery ha sido deprecado y ya no se puede usar en ningún lugar.

# Python

* Cuando una función tenga más de 2-3 parámetros y _sobre todo_ si esos parámetros son del mismo tipo y definitivamente si tiene varios parámetros opcionales, se prefiere cambiar los parámetros para que se deban recibir por nombre. Ejemplo:
  ```python
  # Ejemplo malo:
  def updateProblem(problem: Problem, previousVersion: str, currentVersion: str, points: Optional[int] = None) -> None:
    # ...

  # Ejemplo mejor:
  def updateProblem(
    *,
    problem: Problem,
    previous_version: str,
    current_version: str,
    points: Optional[int] = None,
  ) -> None:
    # ...
  ```
* Se prefiere el uso de snake_case para nombres de funciones y variables, y CamelCase para clases.
* Hay que evitar el uso de `from module import function`. Se prefiere siempre importar módulos y llamar los miembros de los módulos con el nombre del módulo. El módulo `typing` es la única excepción a esta regla.
  ```python
  # Ejemplo malo:
  from typing import Optional

  from module import function
  # ...
  function()

  # Ejemplo mejor:
  from typing import Optional

  import module
  # ...
  module.function()
  ```