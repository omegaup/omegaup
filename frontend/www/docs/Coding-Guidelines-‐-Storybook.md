## Qué es storybook?

Storybook es una herramienta de desarrollo de componentes web que proporciona un entorno interactivo para desarrollar, probar y documentar componentes de interfaz de usuario de forma aislada. Permite a los desarrolladores crear y visualizar los diferentes estados y variaciones de sus componentes de manera fácil y rápida.

Al utilizar Storybook, los desarrolladores pueden separar el desarrollo de componentes de la lógica de la aplicación principal. Esto facilita la re-utilización de componentes, mejora la colaboración entre equipos y permite una mayor eficiencia en el desarrollo.

Storybook ayuda a mejorar la calidad del código y la experiencia de usuario al proporcionar una forma sencilla de visualizar y probar los componentes en diferentes situaciones y estados. Además, facilita la documentación de los componentes al permitir incluir ejemplos interactivos, descripciones y notas adicionales.

En resumen, Storybook es una herramienta fundamental para el desarrollo de componentes web, ya que agiliza el proceso de desarrollo, mejora la colaboración entre equipos y garantiza la consistencia y calidad de los componentes.

## Como funciona storybook

En OmegaUp, tenemos un script disponible para correr storybook (no es necesario levantar docker):

```jsx
$ yarn storybook
```

Esto levantará la colección de `stories` disponibles en nuestra biblioteca de componentes y lanzará un dashboard en `[localhost:6006](http://localhost:6006)` donde podremos consultar cada una de ellas.

## Agregando Stories a la biblioteca

1. Dentro de la carpeta del componente que estés trabajando, debes crear un archivo `COMPONENT.stories.ts` reemplazando `COMPONENT` por el nombre del componente. Por ejemplo, si tienes un componente `Badge`, crea un archivo llamado `Badge.stories.ts`.
2. En cada archivo de historia, importa el componente Vue y define una función que renderice el componente con diferentes props y estados para mostrar las variaciones del componente.
3. Ejecuta el comando `npm run storybook` en tu proyecto para iniciar el servidor de Storybook y ver las historias de tus componentes Vue existentes.

¡Y eso es todo! Ahora puedes agregar historias a tus componentes Vue existentes en Storybook y visualizar y probar sus diferentes estados y variaciones de manera interactiva.

Ejemplo:

```tsx
import { StoryObj, Meta } from '@storybook/vue';
import Badge from 'Badge.vue';

const meta: Meta<typeof Badge> = {
	component: Badge,
	
	// argTypes define un objeto de propiedades que el componente espera
	// y permite agregar controles dinámicos que serán presentados en el dashboard
	// para agregar información dinámicamente
	argTypes: {},
};

export default meta;

type Story = StoryObj<typeof meta>;

export const MyStory: Story = {
	// args define un objeto de props que son pasadas al componente
	args: {
		badge_alias: '100solvedProblems',
		unlocked: true,
	},

	// En caso de que necesitemos hacer algun custom template con nuestro componente
	// podemos utilizar la función render y sobre-escribir el meta object.
	/*
	* render: (args, { argTypes }) => ({
	*   components: { Badge },
	*   template: '<Badge :badge="$props" />'
	*  }),
	*/
};

// Nombre con el que será presentado el componente en el dashboard
MyStory.storyName = 'My Awesome Story';
```

### Referencia

[Documentación Storybook](https://storybook.js.org/docs/vue/writing-stories/introduction)