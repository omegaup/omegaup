import { StoryObj, Meta } from '@storybook/vue';
import CountryFlag from './CountryFlag.vue';

const AvailableCountries = [
  'ad',
  'af',
  'ag',
  'al',
  'am',
  'ar',
  'bo',
  'br',
  'ca',
  'cl',
  'de',
  'do',
  'eg',
  'fr',
  'ga',
  'la',
  'mo',
  'mx',
  'nl',
  'nz',
  'pe',
];

const meta: Meta<typeof CountryFlag> = {
  component: CountryFlag,
  title: 'Components/CountryFlag',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated, so we can't get prop types from the component
    country: {
      control: 'select',
      options: AvailableCountries,
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    country: 'mx',
  },
  render: (args, { argTypes }) => ({
    components: { CountryFlag },
    props: Object.keys(argTypes),
    template: '<country-flag :country="$props.country" />',
  }),
};

Default.storyName = 'CountryFlag';
