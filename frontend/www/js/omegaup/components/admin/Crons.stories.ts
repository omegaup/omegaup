import { StoryObj, Meta } from '@storybook/vue';
import Crons from './Crons.vue';
import { types } from '../../api_types';

const startedAt = new Date(2026, 7, 29, 8, 19);

const jobs: types.CronJob[] = [
  {
    name: 'update_ranks.py',
    description: 'Recomputes user, author and school rankings',
    schedule: '19 8 * * *',
    enabled: true,
  },
  {
    name: 'build_problem_rec_model.py',
    description: 'Trains the problem recommendation model',
    schedule: '0 4 * * 0',
    enabled: true,
  },
  {
    name: 'plagiarism_detector.py',
    description: 'Never scheduled, so it has no expression to describe',
    enabled: false,
  },
];

const runs: types.CronRun[] = [
  {
    run_id: 2,
    name: 'update_ranks.py',
    hostname: 'cron-01',
    status: 'success',
    started_at: startedAt,
    finished_at: startedAt,
    duration_seconds: 12.5,
    rows_affected: 4821,
    phases: [
      { phase: 'update_user_rank', status: 'success', duration: 8.1 },
      { phase: 'update_school_rank', status: 'success', duration: 4.4 },
    ],
  },
  {
    run_id: 1,
    name: 'build_problem_rec_model.py',
    hostname: 'cron-01',
    status: 'failure',
    started_at: startedAt,
    duration_seconds: 0.4,
    phases: [],
    error_text: 'MAP score 0.1200 below minimum 0.3000',
  },
];

const problemHealthFindings: types.ProblemHealthFinding[] = [
  {
    problem_id: 1,
    alias: 'sumas',
    title: 'Sumas',
    check_type: 'no_languages',
    severity: 'error',
    detail: 'the problem is public but has no enabled language',
    first_detected_at: startedAt,
  },
  {
    problem_id: 2,
    alias: 'triangulos',
    title: 'Triángulos',
    check_type: 'never_solved',
    severity: 'warning',
    detail: '30 submissions and no accepted solution yet',
    first_detected_at: startedAt,
  },
];

const meta: Meta<typeof Crons> = {
  component: Crons,
  title: 'Components/Admin/Crons',
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: { jobs, runs, problemHealthFindings },
  render: (args, { argTypes }) => ({
    components: { Crons },
    props: Object.keys(argTypes),
    template: `<crons
      :jobs="$props.jobs"
      :runs="$props.runs"
      :problem-health-findings="$props.problemHealthFindings" />`,
  }),
};

Default.storyName = 'Crons';

export const Healthy: Story = {
  args: { jobs, runs, problemHealthFindings: [] },
  render: Default.render,
};

Healthy.storyName = 'Crons with nothing to report';
