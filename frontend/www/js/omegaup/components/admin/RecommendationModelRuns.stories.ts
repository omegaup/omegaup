import { StoryObj, Meta } from '@storybook/vue';
import RecommendationModelRuns from './RecommendationModelRuns.vue';
import { types } from '../../api_types';

const createdAt = new Date(2026, 8, 20, 4, 0);

const modelRuns: types.RecommendationModelRun[] = [
  {
    model_run_id: 3,
    created_at: createdAt,
    map_score: 0.1934,
    dataset_size: 48210,
    rng_seed: 42,
    published: true,
  },
  {
    model_run_id: 2,
    created_at: createdAt,
    map_score: 0.1211,
    dataset_size: 47992,
    published: false,
    skip_reason:
      'MAP score 0.1211 regressed more than 0.0500 below the last published 0.1902',
  },
  {
    model_run_id: 1,
    created_at: createdAt,
    map_score: 0.0312,
    dataset_size: 512,
    rng_seed: 7,
    published: false,
    skip_reason: 'MAP score 0.0312 below minimum 0.0500',
  },
];

const meta: Meta<typeof RecommendationModelRuns> = {
  component: RecommendationModelRuns,
  title: 'Components/Admin/RecommendationModelRuns',
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: { modelRuns },
  render: (args, { argTypes }) => ({
    components: { RecommendationModelRuns },
    props: Object.keys(argTypes),
    template: '<recommendation-model-runs :model-runs="$props.modelRuns" />',
  }),
};

Default.storyName = 'Recommendation model runs';

export const Empty: Story = {
  args: { modelRuns: [] },
  render: Default.render,
};

Empty.storyName = 'Recommendation model runs before any training';
