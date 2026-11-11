## Recommended Workflow

This document provides a step-by-step guide for frontend development in omegaUp. It is designed to help developers create new views (entry points), configure the necessary backend and frontend components, and ensure that the code adheres to the project's standards.

By following this workflow, you will learn how to:

1. Configure `nginx.rewrites` to map user-friendly URLs to the appropriate PHP files.
2. Set up PHP files to handle requests and pass data to the frontend.
3. Create and link TypeScript entry points for rendering views.
4. Use Vue.js and Bootstrap 4 to build responsive and maintainable user interfaces.
5. Write and run tests to ensure the quality and stability of your changes.

Whether you are adding a new feature or modifying an existing one, this guide will help you navigate the development process efficiently and effectively.

## Creating a New View (Entry Point)
To begin, we will address the creation of a new view, also known as an entry point. This involves configuring the backend and frontend components to work together seamlessly. Follow the steps below to get started.

### Configuring `nginx.rewrites`

1. Identify the section of the code that corresponds to the change you are going to work on.
Currently, we have the following structure:
```md
omegaup/
├── frontend/
│   ├── www/
│   │   ├── admin/
│   │   │   ├── index.php
│   │   │   ├── support.php
│   │   │   └── user.php
│   │   ├── arena/
│   │   │   ├── admin.php
│   │   │   ├── contest_practice.php
│   │   │   ├── contest.php
│   │   │   ├── contestprint.php
│   │   │   ├── indexv2.php
│   │   │   ├── problem.php
│   │   │   ├── problemprint.php
│   │   │   ├── scoreboard.php
│   │   │   └── virtual.php
│   │   ├── badge/
│   │   │   └──  list.php
│   │   ├── certificate/
│   │   │   ├── details.php
│   │   │   ├── download.php
│   │   │   ├── mine.php
│   │   │   └── validation.php
│   │   ├── contests/
│   │   │   ├── activity.php
│   │   │   ├── edit.php
│   │   │   ├── mine.php
│   │   │   ├── new.php
│   │   │   ├── report.php
│   │   │   ├── scoreboardmerge.php
│   │   │   ├── stats.php
│   │   │   └── virtual.php
│   │   ├── course/
│   │   │   ├── activity.php
│   │   │   ├── arena.php
│   │   │   ├── assignment.php
│   │   │   ├── clarification.php
│   │   │   ├── clone.php
│   │   │   ├── edit.php
│   │   │   ├── home.php
│   │   │   ├── mine.php
│   │   │   ├── new.php
│   │   │   ├── scoreboard.php
│   │   │   ├── statistics.php
│   │   │   ├── student.php
│   │   │   ├── students.php
│   │   │   ├── studentWithAssignment.php
│   │   │   ├── submissionslist.php
│   │   │   └── tabs.php
│   │   ├── grader/
│   │   │   └──  grader.php
│   │   ├── group/
│   │   │   ├── edit.php
│   │   │   ├── list.php
│   │   │   ├── new.php
│   │   │   └── scoreboardedit.php
│   │   ├── problems/
│   │   │   ├── random/
│   │   │   │   ├── karel.php
│   │   │   │   └── language.php
│   │   │   ├── collection_details_by_author.php
│   │   │   ├── collection_details_by_level.php
│   │   │   ├── collection.php
│   │   │   ├── creator.php
│   │   │   ├── edit.php
│   │   │   ├── image.php
│   │   │   ├── input.php
│   │   │   ├── list.php
│   │   │   ├── mine.php
│   │   │   ├── new.php
│   │   │   ├── stats.php
│   │   │   └── template.php
│   │   ├── profile/
│   │   │   ├── dependents.php
│   │   │   └── index.php
│   │   ├── qualitynomination/
│   │   │   ├── details.php
│   │   │   ├── list.php
│   │   │   └── my_list.php
│   │   ├── rank/
│   │   │   ├── authors.php
│   │   │   ├── schools.php
│   │   │   └── users.php
│   │   ├── schools/
│   │   │   ├── profile.php
│   │   │   └── schoolofthemonth.php
│   │   ├── submissions/
│   │   │   ├── list.php
│   │   │   └── user_list.php
│   │   ├── teamsgroup/
│   │   │   ├── edit.php
│   │   │   ├── list.php
│   │   │   └── new.php
│   │   ├── users/
│   │   │   ├── emailedit.php
│   │   │   └── verification_parental_token.php
└── README.md
```
Each `.php` file represents an entry point.

2. Once you have identified the section for your new change, open the `nginx.rewrites` file and add a new mapping entry. For example, if you are creating a view for the school ranking, you would add the following line:
```
rewrite ^/rank/schools/?$ /rank/schools.php last;
```

The first part corresponds to the user-friendly URL that will appear in the browser's address bar, and the second part corresponds to the file that will handle the request, specifying the directory and the file.



### PHP configuration
1. Create the file you referenced in `nginx.rewrites`, ensuring it is placed in the correct directory.
2. Inside the newly created file, include the following code:
```php
<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => \OmegaUp\Controllers\School::getRankForTypeScript(
        $r
    )
);
```

This code is a standard used in omegaUp for most entry points. The render function takes care of processing the view, and the only part you need to customize is the callback passed as a parameter. Note that the function name includes the suffix `ForTypeScript`, which helps identify functions corresponding to an entry point.
3. Create the function inside the corresponding controller. 

Define the function in the appropriate controller. For example:

```php
public static function getRankForTypeScript(\OmegaUp\Request $r): array {
```

4. Create the code that will be sent to the client.
Write the logic to process the information that will be displayed in the view. The return value should be an array with the following structure:
```php
        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'showHeader' => false,
                    'rank' => $schoolRank['rank'],
                    'totalRows' => $schoolRank['totalRows'],
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleSchoolsRank'
                )
            ],
            'entrypoint' => 'schools_rank',
        ];
```
Each element in the array has a specific purpose:

- **`templateProperties`**: Contains important data required to render the view. Some of these fields are mandatory:
  - `payload`:  Includes all the relevant data that TypeScript will use to render the view
  - `title`: Specifies the text that will appear in the browser tab. This uses the `TranslationString` class to display the text in the user's preferred language.
  - Optional fields include `fullWidth`, `hideFooterAndHeader`, and `scripts`.

- **`entrypoint`**: Alongside templateProperties, this field specifies the TypeScript entry point that will render the data into visual elements.

5. Add a docblock for the function
Create a docblock to document the function. This is essential for generating `psalm` types, which will later be used in TypeScript. The docblock should include:
- **Function description**: Provide relevant details about the function's purpose, if necessary.
- **Return type**: Specify the correct type for the return value. The `payload` field should use a unique type (e.g., `SchoolRankPayload`) to ensure consistency when accessed in TypeScript.
- **Parameters**: List all the parameters the function accepts. For example, `$length` and `$page` are used for pagination in this case.

At the end docblock should look like this:
```php
 /**
     * Gets the details for historical rank of schools with pagination
     *
     * @return array{templateProperties: array{payload: SchoolRankPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
````

Additionally, define the psalm type at the top of the file:
```php
* @psalm-type SchoolRankPayload=array{page: int, length: int, rank: list<School>, totalRows: int, showHeader: bool, pagerItems: list<PageItem>}
```

6. Run validation and linting tools
Before proceeding with frontend changes, ensure the code is error-free and adheres to the project's style guidelines:
- **Run type validators**: Use psalm to check for type consistency and ensure there are no type-related errors. Execute the following command:
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
If errors are found, fix them. If you encounter difficulties, reach out to the omegaUp development team for assistance.

- **Run the linter**: Execute the following command to propagate the newly created types to TypeScript, fix code style issues, and validate syntax:
```bash
./stuff/lint.sh
```

### Run PHP Unit Tests
If you have experience creating tests, it is necessary to add the required tests to validate that the data sent from the payload is correct and to prevent regressions in the future. If you are not familiar with creating tests, reach out to any member of the omegaUp team—they can help you define and implement the test.

Once the tests have been created (either in a new file or an existing one), there are two ways to execute them:

1. **Run a single test**: This method is useful while you are in the process of creating the test. You can execute it as follows:
```bash
$ ./stuff/run-php-tests.sh frontend/tests/controllers/SchoolRankTest.php
```
Common results include:
```bash
PHPUnit 9.6.13 by Sebastian Bergmann and contributors.

Warning:       No code coverage driver available


                                    SchoolRankTest .EF

Time: 00:04.634, Memory: 16.00 MB

```
- **.**:  Indicates that the test passed successfully.
- **F**: Indicates that the test failed an assertion (e.g., expected value 5 but got 4).
- **E**: Indicates that the test encountered errors, such as a missing function or incorrect parameters.

2. **Run the entire test suite**:
Once the single test has been verified and is working correctly, you can run the full test suite to ensure that your changes do not negatively affect other parts of the codebase:
```bash
$ ./stuff/runtests.sh
```
This process is time-consuming because it runs a large number of tests, but it is necessary to guarantee that no issues will arise in GitHub workflows.

### Typescript configuration
Thanks to our [unified template](https://github.com/omegaup/omegaup/pull/3857), you no longer need to make changes inside the `.tpl` file. If you correctly configured the `PHP` function, you are ready to work with the TypeScript file.


1. Link the TypeScript file in `webpack.config-frontend.js`
Ensure the entry point in webpack.`config-frontend.js` is correctly linked to the TypeScript file you are working on. If the entry does not exist, you must create it. For example, for the school ranking view, you would add:
```js
    schools_rank: './frontend/www/js/omegaup/schools/rank.ts',
```

2. Create the TypeScript file
Now it's time to create the TypeScript file. Make sure you use the right `payloadParser` to retrieve the information passed from PHP. For example:
```ts
  const payload = types.payloadParsers.SchoolRankPayload();
```
If the parser you need does not exist, ensure you defined the correct type in the `PHP` file and ran lint.sh to autogenerate the parser from the `psalm` type you created.
3. Create the Vue instance
Once the TypeScript file is set up, create the Vue instance where the component will be rendered. For example:
```ts

  new Vue({
    el: '#main-container',
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
    render: function (createElement) {
      return createElement('omegaup-schools-rank', {
        props: {
          page: payload.page,
          length: payload.length,
          showHeader: payload.showHeader,
          rank: payload.rank,
          totalRows: payload.totalRows,
          pagerItems: payload.pagerItems,
        },
      });
    },
  });
```
4. Purpose of `.ts` files
These `.ts` files are primarily used to:

- Create a Vue instance for rendering the component in the view.
- Pass props to the component.
- Manage events and interactions.

Additionally, you can use these files to call `API` functions and pass the results to the component being rendered.


### Vue.js + Bootstrap 4 configuration
1. Ensure TypeScript Types Are Defined
All the data you receive from a TypeScript file must have the types you already defined in the corresponding `PHP` file. Import these types into your `Vue` component. For example:
```ts
  @Prop() page!: number;
  @Prop() length!: number;
  @Prop() showHeader!: boolean;
  @Prop() totalRows!: number;
  @Prop() rank!: omegaup.SchoolsRank[];
  @Prop() pagerItems!: types.PageItem[];
```
2. Use Bootstrap 4 Classes
Ensure that all the classes you use in the `.vue` file are compatible with [Bootstrap 4](https://getbootstrap.com/docs/4.4/getting-started/introduction/). If the `.vue` file was written using Bootstrap 3, migrate it to Bootstrap 4. The unified template expects Bootstrap 4 classes, and using classes from a different version may cause layout or styling issues.
3. Avoid Using id Attributes
Avoid using id attributes in `.vue` files to prevent conflicts. If you must use them, add a flag to disable linting for that specific case. For example:
```ts
        <!-- id-lint off -->
			<input
			class="form-control"
			id="inputTimeLimit"
			max="5.0"
			min="0.1"
			step="0.1"
			type="number"
			v-model="timeLimit"
			/>
        <!-- id-lint on -->
```

### Testing vue components in Jest
Once you have modified a Vue component or created a new one, it is important to add the corresponding tests. These tests ensure that the component behaves as expected and help prevent regressions in the future. Additionally, tools like [Codecov](https://about.codecov.io/) can help you identify which lines of code are missing test coverage.


You can find an example of a simple test for a Vue component [here](https://github.com/omegaup/omegaup/blob/main/frontend/www/js/omegaup/components/arena/Arena.test.ts) You can copy and paste it, or if you prefer, you can use the following snippet to create a new test from scratch:
```json
{
	"Jest test": {
		"prefix": "test",
		"body": [
			"import { shallowMount } from '@vue/test-utils';",
			"import ${1:namespace_ComponentName } from './${2:ComponentFileName}.vue';",
			"",
			"describe('${2:ComponentFileName}.vue', () => {",
			"\tit('${3:test description}', () => {",
			"\t\tconst wrapper = shallowMount(${1:namespace_ComponentName}, {",
			"\t\t\tpropsData: {",
			"\t\t\t\t${4:propName}: ${5:propValue},",
			"\t\t\t},",
			"\t\t});",
			"\t\texpect(wrapper.find(${6:selector}).text()).toBe(${7:value});",
			"\t});",
			"});",
		],
		"description": "Create a new test for Jest"
	}
}
```
 You can use it in VS Code, [here](https://code.visualstudio.com/docs/editor/userdefinedsnippets#_create-your-own-snippets) is the documentation to create your own snippets.

### Adding End-to-End Tests in Cypress
If your change involves a component where users can interact, you should also create an end-to-end (e2e) test in Cypress. These tests help prevent client-side regressions and ensure that the user experience remains consistent.

There is a dedicated document for creating and running Cypress tests, which you can find [here]([How to use Cypress in omegaUp](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-use-Cypress-in-omegaUp.md))

***

Here are some additional guidelines to follow during development:

* **Invoke API calls from .ts files**: Always make API calls from TypeScript files instead of directly in .vue files. This ensures better separation of concerns and maintainability.
* **Avoid using jQuery**: Do not use jQuery in your code. Modern JavaScript and Vue.js provide all the tools you need for DOM manipulation and event handling.
* **Use the guard clause pattern**: Simplify your code by replacing deeply nested conditionals with [guard clauses](https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html). This improves readability and reduces complexity.
* **Remove unnecessary logging:** Before committing your changes, ensure that all debugging or unnecessary logging statements have been removed.
* **Use kebab-case for HTML element names**: Consistently use kebab-case for naming HTML elements to maintain uniformity and avoid conflicts.
* **Use camelCase for method names**: Always use camelCase for naming methods to follow JavaScript conventions.
* **Leverage ES6 interpolation**: Use ES6 template literals for string interpolation, as they are more concise and easier to read.
* **Avoid var**: Use let and const instead of var for variable declarations to ensure block scoping and avoid hoisting issues.