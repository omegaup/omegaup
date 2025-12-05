import { Meta, Story } from '@storybook/vue';
import ContestCard from './ContestCard.vue';
import { types } from '../../api_types';

export default {
  title: 'Arena/ContestCard',
  component: ContestCard,
} as Meta;

const Template: Story = (args, { argTypes }) => ({
  components: { ContestCard },
  props: Object.keys(argTypes),
  template: '<contest-card v-bind="$props" />',
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

export const Participating = Template.bind({});
Participating.args = {
  contest: {
    ...Default.args.contest,
    participating: true,
    title: 'Participating Contest',
  } as types.ContestListItem,
};
