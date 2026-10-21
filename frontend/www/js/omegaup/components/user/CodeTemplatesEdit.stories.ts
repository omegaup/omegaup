import { StoryObj, Meta } from '@storybook/vue';
import { types } from '../../api_types';
import CodeTemplatesEdit from './CodeTemplatesEdit.vue';

const profile: types.UserProfileInfo = {
  name: 'omegaUp admin',
  classname: 'user-rank-unranked',
  email: 'admin@omegaup.com',
  username: 'omegaup',
  verified: true,
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py3',
  programming_languages: {
    'c11-gcc': 'C11 (gcc)',
    'cpp17-gcc': 'C++17 (g++)',
    java: 'Java',
    py3: 'Python 3',
    rb: 'Ruby',
  },
  rankinfo: {
    name: 'omegaUp admin',
    problems_solved: 2,
    rank: 1,
  },
  is_own_profile: true,
  birth_date: new Date('1999-09-09'),
  locale: 'es',
  gender: 'decline',
  has_competitive_objective: true,
  has_learning_objective: true,
  has_scholar_objective: false,
  has_teaching_objective: false,
};

const meta: Meta<typeof CodeTemplatesEdit> = {
  component: CodeTemplatesEdit,
  title: 'Components/User/CodeTemplatesEdit',
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  render: () => ({
    components: { 'omegaup-user-edit-code-templates': CodeTemplatesEdit },
    data: () => ({ profile }),
    template:
      '<div class="card"><omegaup-user-edit-code-templates :profile="profile" /></div>',
  }),
};

Default.storyName = 'CodeTemplatesEdit';
