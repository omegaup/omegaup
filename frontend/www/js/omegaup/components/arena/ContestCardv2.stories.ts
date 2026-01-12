import { Meta, Story } from '@storybook/vue';
import ContestCardv2 from './ContestCardv2.vue';
import { types } from '../../api_types';

export default {
  title: 'Arena/ContestCard',
  component: ContestCardv2,
} as Meta;

const Template: Story = (args, { argTypes }) => ({
  components: { ContestCardv2 },
  props: Object.keys(argTypes),
  template: '<contest-cardv2 v-bind="$props" />',
});

export const Default = Template.bind({});
Default.args = {
  contest: {
    alias: 'contest-alias',
    title: 'Contest Title',
    description: 'Contest Description',
    start_time: new Date(),
    finish_time: new Date(Date.now() + 3600000),
    public: true,
    active: true,
    recommended: false,
    contestants: 10,
    organizer: 'omegaUp',
    participating: false,
    contest_id: 1,
    admission_mode: 'public',
    problemset_id: 1,
    last_updated: new Date(),
    original_finish_time: new Date(Date.now() + 3600000),
  } as types.ContestListItem,
};

export const Recommended = Template.bind({});
Recommended.args = {
  contest: {
    ...Default.args.contest,
    recommended: true,
    title: 'Recommended Contest',
  } as types.ContestListItem,
};

export const Current = Template.bind({});
Current.args = {
  contest: {
    ...Default.args.contest,
    title: 'Current Contest',
    start_time: new Date(Date.now() - 3600000), // 1 hour ago
    finish_time: new Date(Date.now() + 3600000), // 1 hour from now
    active: true,
  } as types.ContestListItem,
};

export const Future = Template.bind({});
Future.args = {
  contest: {
    ...Default.args.contest,
    title: 'Future Contest',
    start_time: new Date(Date.now() + 86400000), // 1 day from now
    finish_time: new Date(Date.now() + 172800000), // 2 days from now
    active: false,
  } as types.ContestListItem,
};

export const Past = Template.bind({});
Past.args = {
  contest: {
    ...Default.args.contest,
    title: 'Past Contest',
    start_time: new Date(Date.now() - 172800000), // 2 days ago
    finish_time: new Date(Date.now() - 86400000), // 1 day ago
    active: false,
  } as types.ContestListItem,
};
