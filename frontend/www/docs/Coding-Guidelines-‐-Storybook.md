## What is Storybook?

Storybook is a web component development tool that provides an interactive environment to develop, test, and document user interface components in isolation. It allows developers to create and visualize the different states and variations of their components easily and quickly.

By using Storybook, developers can separate component development from the main application logic. This facilitates component re-use, improves collaboration between teams, and allows for greater efficiency in development.

Storybook helps improve code quality and user experience by providing a simple way to visualize and test components in different situations and states. Additionally, it facilitates component documentation by allowing the inclusion of interactive examples, descriptions, and additional notes.

In summary, Storybook is a fundamental tool for web component development, as it streamlines the development process, improves collaboration between teams, and guarantees the consistency and quality of the components.

## How Storybook Works

At OmegaUp, we have a script available to run Storybook (it is not necessary to spin up Docker):

```bash
$ yarn storybook
```
This will spin up the collection of available stories in our component library and launch a dashboard at ```localhost:6006``` or ```(http://localhost:6006)``` where we can consult each one of them.

## Adding Stories to the Library
- Inside the folder of the component you are working on, you must create a file COMPONENT.stories.ts, replacing COMPONENT with the name of the component. For example, if you have a Badge component, create a file called Badge.stories.ts.

- In each story file, import the Vue component and define a function that renders the component with different props and states to show the component's variations.

- Run the npm run storybook command in your project to start the Storybook server and see the stories for your existing Vue components.

And that's it! Now you can add stories to your existing Vue components in Storybook and visualize and test their different states and variations interactively.

## Example
```typescript
import { StoryObj, Meta } from '@storybook/vue';
import Badge from 'Badge.vue';

const meta: Meta<typeof Badge> = {
	component: Badge,
	
	// argTypes defines an object of properties that the component expects
	// and allows adding dynamic controls that will be presented on the dashboard
	// to add information dynamically
	argTypes: {},
};

export default meta;

type Story = StoryObj<typeof meta>;

export const MyStory: Story = {
	// args defines an object of props that are passed to the component
	args: {
		badge_alias: '100solvedProblems',
		unlocked: true,
	},

	// In case we need to make a custom template with our component
	// we can use the render function and overwrite the meta object.
	/*
	* render: (args, { argTypes }) => ({
	*  	components: { Badge },
	*  	template: '<Badge :badge="$props" />'
	*  }),
	*/
};

// Name with which the component will be presented on the dashboard
MyStory.storyName = 'My Awesome Story';
```

## Reference
[Storybook Documentation](https://storybook.js.org/docs/vue/writing-stories/introduction)