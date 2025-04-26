# General

* Many of the things expressed in the coding guidelines are validated on GitHub by linters and integration tests.
  - Whenever possible, this process should be automated to avoid regressions and facilitate the code review work.
* All code must declare data types in the interfaces (in function parameters and the type of data they return).
  - It is also preferred that arrays/maps inside functions have type annotations to make them easier to understand.
  - In the frontend we use [TypeScript](https://www.typescriptlang.org/), in PHP we use [Psalm](https://psalm.dev/) and in Python we use [mypy](http://mypy -lang.org/).
* All code (and comments) are written in English.
* Changes in functionalities or new functionalities must be accompanied by their respective new/modified tests.
* Avoid using `null` and `undefined` wherever possible. Especially in function parameters.
  - `null` should only be used when there is something a user did not provide.
  - `undefined` should only be used in the declaration of optional parameters in TypeScript functions.
  - Avoid declaring types that can be `null` and `undefined` at the same time.
  - If there are several parameters or fields that can be `null`/`undefined`, they should all be able to be null independently: if there are subsets of parameters/fields that must be passed together, it is better to create an intermediate type that groups them together. For example,
    ```php
    function MyFunc(
        \OmegaUp\DAO\Problems $problem,
        bool $customValidator,
        string|null $validatorLanguage = null,
        int|null $validatorTimeout = null,
    ): void {
    ```
   In this function, `$validatorLanguage` and `$validatorTimeout` must _be_ specified if `$customValidator` is `true`, and must _not_ be specified otherwise. So it's better to create another type that groups them:
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
   - Each possible `undefined`/`null` parameter doubles the number of combinations a function can receive, and this grows exponentially. Try to limit this to < 10.
* Functions that change their behavior significantly depending on flags/conditionals should be avoided. It is preferred that multiple functions be declared and the appropriate function be called.
* Whenever possible, use the [Guard Clause Pattern](https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html).
* There must not be any unused code. Removing unused code is preferred to commenting it out. At the end of the day, it can always be retrieved using the git history.
* Is preferred the use of [camelCase](https://en.wikipedia.org/wiki/Camel_case)  for function/variable/class names. Exceptions where [snake_case](https://en.wikipedia.org/wiki/Snake_case) is used are:
   - Columns in MySQL
   - Variables and parameters in Python
   - API parameters  
* It is preferred to avoid the use of abbreviations in the code (both in variable names and in comments). Abbreviations are not completely obvious to everyone.
* It is preferred to minimize the distance between where variables are declared and the first time they are used. Because, we want to minimize the amount of non-relevant code that has to be read to know what a variable contains.
* Comments should be used to explain complex or non-intuitive parts of the code. It is unnecessary to add comments to describe what the code does and it is preferred to explain _why things are done the way they are done_.

# Format

* We delegate the job of deciding how to style the code to the automated tools. We use:
  - [yapf](https://github.com/google/yapf) for Python.
  - [prettier.io](https://prettier.io/) for TypeScript/Vue.
  - [phpcbf](https://github.com/squizlabs/PHP_CodeSniffer) for PHP.
  
  You can validate the style by calling `./stuff/lint.sh validate`.
* More styling guidelines:
  - 2/4 spaces are used (depends on the type of file), not tabs.
  - The end-of-line must be Unix-style (`\n`), not Windows-style (`\r\n`).
  - Opening brackets goes in the same line as the last statement.
    ```php
        if (condition) {
            stuff;
        }
    ```
  - Include a space between the keywords and parentheses for: `if`, `else`, `while`, `switch`, `catch`, `function`.
  - Function calls do not have a space before the parentheses.
  - No spaces are left inside the parentheses.
  - A space after each comma, but no space before it.
  - All binary operators must have a space before and one after.
  - There should not be more than one blank line in a row.
* There should not be empty comments.
* No `/* ... */` block comments should be used, only `// ...` line comments.
 
# PHP

* Tests must be run before committing and all must pass 100%, no exceptions.
* Changes in functionality must be accompanied by their respective new / modified tests.
* Functions that require O(n) queries to the database (this includes operations with DAOs) should be avoided. Instead, queries should be created manually so that they do all the functionality in a single round trip.
* With the exception of functions that implement the API, none function can receive a parameter of type `\OmegaUp\Request`. All API functions must validate parameters, extract them to variables with the correct types, and call functions with these variables instead of the original `\OmegaUp\Request` .
* All functions must be commented with the style:
   ```php
   /**
    * set
    *
    * If cache is on, save value in key with given timeout
    *
    * @param string $value
    * @param int $timeout
    * @return boolean
    */
   public function set($value, $timeout) { ...
   ```
* Exceptions must be used to report erroneous conditions. The use of functions that return true/false is allowed when they are expected values.
* All APIs must report their results in the form of associative arrays.
* Use [RAII](http://en.wikipedia.org/wiki/Resource_Acquisition_Is_Initialization) when convenient, mainly when managing resources (files, etc...)

# Vue

* Avoid creating components that change their behavior significantly depending on flags/conditionals. It is preferred that the distinct behavior be abstracted using [`slot`s](https://vuejs.org/v2/guide/components-slots.html) so that it can be customized by the calling component. If there are multiple components that are going to use this different behavior, you can create _another_ component that has the appropriate `slot`.
* As the entire interface must be able to be displayed in several languages, texts should not be written directly and translation strings should be used instead.
* Avoid concatenating translation strings, because different languages may use different word orders. It is preferred to create a translation string with replaceable parameters and use the `ui.formatString` function:
  ```typescript
  <!-- Bad example:
  contestRanking = "Contest ranking: "
  -->
  <div>{{ T.contestRanking }} {{ user.rank }} {{ user.username }} {{ user.name }} {{ user.score }}</div>

  <!-- Better example:
  contestRanking = "Contest ranking: %(rank) %(username) %(name) %(score)"
  -->
  <div>{{ ui.formatString(T.contestRanking, { rank: user.rank, user: user.username, name: user.name, score: user.score }) }}</div>
  ```
* Avoid assigning colors in hexadecimal or `rgb(...)` format. Instead, the colors should be declared as variables so that dark mode doesn't break.
* Avoid using [lifecycle hooks](https://v3.vuejs.org/api/options-lifecycle-hooks.html) _unless_ there is something in the component that interacts directly with the DOM.
   - Direct interaction with the DOM should also be avoided.
* Using [computed properties and watchers](https://vuejs.org/v2/guide/computed.html) is preferred over manipulating variables programmatically.
* It's recommended to add **Storybook** stories for each new component, and if modifying an existing component, add or update the related stories. [See more](https://github.com/omegaup/omegaup/wiki/Coding-Guidelines-%E2%80%90-Storybook)
* **Development Tip**: If you encounter TypeScript errors related to HTML elements in Vue templates, you might be tempted to add JSX IntrinsicElements declarations. While this can be done by adding the following to a `.d.ts` file:
  ```typescript
  declare namespace JSX {
    interface IntrinsicElements {
      [elem: string]: any;
    }
  }
  ```
  This is not recommended as it could mask real errors and make debugging harder. Instead, try to properly type your components and templates to avoid these errors in the first place.


# TypeScript

* When a function has more than 2-3 parameters and _especially_ if those parameters are of the same type and definitely if it has several optional parameters, it is preferred to change the parameters to an object. Example:
  ```typescript
  // Bad example:
  function updateProblem(problem: Problem, previousVersion: string, currentVersion: string, points?: int): void {
    // ...
  }

  // Better example:
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
* Use of camelCase for function/variable/class names is preferred
* The use of [Type Assertions](https://www.typescriptlang.org/docs/handbook/2/everyday-types.html#type-assertions) should be avoided. It is only allowed in the following cases:
  - When you are interacting with the DOM (`document.querySelector` and its friends).
  - To declare that an empty literal (`null`, `{}`, `[]`) is of a certain type example: `null as null | string`, `[] as []types.Foo`.
  - In testing, to declare `params` in the Vue constructor.
* `jQuery` has been deprecated and can no longer be used anywhere.

# Python

* When a function has more than 2-3 parameters and _especially_ if those parameters are of the same type and definitely if it has several optional parameters, it is preferred to change the parameters so that they should be received by name. Example:
  ```python
  # Bad example:
  def updateProblem(problem: Problem, previousVersion: str, currentVersion: str, points: Optional[int] = None) -> None:
    # ...

  # Better example:
  def updateProblem(
    *,
    problem: Problem,
    previous_version: str,
    current_version: str,
    points: Optional[int] = None,
  ) -> None:
    # ...
  ```
* It is preferred to use `snake_case` for function and variable names, and `CamelCase` for classes.
* Avoid using `from module import function`. It is always preferred to import modules and call their members using the module name. The `typing` module is the only exception to this rule.
```python
# Bad example:
from typing import Optional

from module import function
# ...
function()

# Better example:
from typing import Optional

import module
# ...
module.function()
```
