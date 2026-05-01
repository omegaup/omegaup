This is the recommended workflow, divided in sections:

### PHP payload configuration
1. Look for the PHP function that specifies [the template](https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/User.php#L3632) you are going to migrate.
2. Make sure all data returned in the previous function, that is relevant for the template, is inside the `smartyProperties['payload']` field.
3. Once the data is ready in the `payload`, now is time to create a `psalm` type for the payload. These are some examples:
- https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/User.php#L3630
- https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/User.php#L10
- https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/User.php#L16
4. Don't forget to pass the previously created types, as annotations for the function you are using to pass the data to the frontend. (Ex.: https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/User.php#L2552)
5. You must also include a field `smartyProperties['title']` in order to pass the title of the view you are migrating. Remember that it should be a string containing the prefix: `omegaupTitle` and also that the string should be inside `{es,en,pt}.lang` files in order to be displayed.
6. Next to the `smartyProperties` field, you must specify the `entrypoint` field which should have the name of the typescript entry you are going to use for rendering the data into visual elements. If the entry doesn't exist, it will be created later. Here an example of [an entrypoint](https://github.com/omegaup/omegaup/blob/master/frontend/server/src/Controllers/User.php#L3425) which corresponds to [this entry](https://github.com/omegaup/omegaup/blob/master/webpack.config.js#L94) on the `webpack.config.js` file.

Once you finished doing all these changes, you should runt `stuff/lint.sh` in order to autogenerate the types that are going to be used later on the typescript files.

You could also run `stuff/runtests.sh` to verify that the tests are not being negatively affected by your changes.

### Typescript configuration
Thanks to our [new unified template](https://github.com/omegaup/omegaup/pull/3857), now you don't have to make any changes inside the `.tpl` file. If  you did the right configuration inside the `PHP` function, then you are ready to work with the typescript file.

> If you are actually migrating a `.js` file to a `.ts` file, you should follow the next steps also. Just take advantage on the fact that most of the logic is already written in your `.js` file, just make it work now on `typescript`!

1. Make sure the entrypoint inside `webpack.config.js` is correctly linked to the `typescript` file you are working with. If that file doesn't exist, you must create it. For example: [this entry](https://github.com/omegaup/omegaup/blob/master/webpack.config.js#L83), matches with [this file](https://github.com/omegaup/omegaup/blob/master/frontend/www/js/omegaup/schools/schoolofthemonth.ts). 
2. The structure of the typescript files is pretty similar. They import some libraries like: `omegaUp`, `types`, `UI`, etc. You should just take a look on the existing `.ts` files and copy that. Just make sure that you always import what you are going to use. 
3. Make sure that you are using the right `payloadParser` for getting the information passed from PHP. Similar to [this example](https://github.com/omegaup/omegaup/blob/master/frontend/www/js/omegaup/schools/schoolofthemonth.ts#L10), you should use the same type you created before. If the parser you are looking for, doesn't exist, make sure you wrote the right type on the `PHP` file and also that you run `stuff/lint.sh` in order to autogenerate the parser from the psalm type you created.
4. Basically, these `.ts` files are used to create `Vue` instance for rendering the component for the view, passing the props and also managing the events. Sometimes, you could use this file for calling to `API` functions and passing that to the component you are rendering, for example [here](https://github.com/omegaup/omegaup/blob/master/frontend/www/js/omegaup/common/navbar.ts#L73).

### Vue.js + Bootstrap 4 configuration
1. All the data you receive from a typescript file, must have the types you already defined before in the `PHP` file. So you must import them [like here](https://github.com/omegaup/omegaup/blob/bcb3f0d4c70d98098a24bc79aa2a39683102cb07/frontend/www/js/omegaup/components/badge/List.vue#L43).
2. Make sure all the classes you are using work in [Bootstrap 4](https://getbootstrap.com/docs/4.4/getting-started/introduction/). If the `.vue` file already existed and was written in BS3, make sure you migrate it to BS4. If  you don't do that, it won't work. The new unified template file, expects that you are using BS4.
3. Don't forget to avoid using `id` attributes inside the `.vue` file, but in case you necessarily must use them, add a flag [like this](https://github.com/omegaup/omegaup/blob/33cbfefe358627dd4815bababea944ee49461e6b/frontend/www/js/omegaup/grader/SettingsComponent.vue#L6)

### Testing vue components in Jest
Once you have modified a vue component or even when you have created another one, it is important to add its corresponding tests. Often, [Codecov](https://about.codecov.io/) will help you to identify which lines in the code are missing in the coverage.

[Here](https://github.com/omegaup/omegaup/blob/main/frontend/www/js/omegaup/components/arena/Arena.test.ts) you can find a simple test for a vue component. You can copy and paste it, or if you wish, you can use a basic snippet to create a new one from scratch:
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


***

Following are more guidelines to follow while doing this migration:

* Invoke the API calls from `.ts` files instead of the .vue files.
* Don't use jQuery!
* Prefer to use the [guard clause pattern](https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html)
* Remove unnecessary logging prior to committing.
* All HTML element names should use kebab-case.
* Use camelCase consistently for method names.
* Use ES6 interpolation which is more concise.
* Don't use `var`!, now we have `let` and `const`.