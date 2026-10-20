import { shallowMount } from '@vue/test-utils';
import qualitynomination_Details from './Details.vue';

describe('Details.vue', () => {
  const baseProps = {
    author: {
      name: 'Author Name',
      username: 'author_user',
    },
    contents: {
      original: '',
      rationale: 'Problem is duplicate',
      reason: 'duplicate',
    },
    initialRationale: 'Initial rationale text',
    nomination: 'demotion',
    nominator: {
      name: 'Nominator Name',
      username: 'nominator_user',
    },
    problem: {
      alias: 'problem-test',
      title: 'Problem Test',
    },
    qualitynomination_id: 42,
    reviewer: true,
    votes: [],
  };

  it('Should render nomination details properly', () => {
    const wrapper = shallowMount(qualitynomination_Details, {
      propsData: baseProps,
    });

    expect(wrapper.text()).toContain('demotion');
    expect(wrapper.text()).toContain('Author Name');
    expect(wrapper.text()).toContain('author_user');
    expect(wrapper.text()).toContain('Problem Test');
    expect(wrapper.text()).toContain('problem-test');
    expect(wrapper.find('textarea[name="rationale"]').exists()).toBe(true);
  });

  it('Should generate correct profile and problem URLs', () => {
    const wrapper = shallowMount(qualitynomination_Details, {
      propsData: baseProps,
    });

    expect(wrapper.vm.userUrl('test_user')).toBe('/profile/test_user/');
    expect(wrapper.vm.problemUrl('test_problem')).toBe(
      '/arena/problem/test_problem/',
    );
  });

  it('Should show reviewer actions and handle resolution workflow', async () => {
    const wrapper = shallowMount(qualitynomination_Details, {
      propsData: baseProps,
    });

    const banButton = wrapper.find('[data-ban-problem-button]');
    expect(banButton.exists()).toBe(true);

    await banButton.trigger('click');

    expect(wrapper.vm.showConfirmation).toBe(true);
    expect(wrapper.vm.status).toBe('banned');

    wrapper.vm.markResolution(true);

    expect(wrapper.vm.showConfirmation).toBe(false);
    expect(wrapper.emitted('mark-resolution')).toEqual([
      [wrapper.vm, 'banned', true],
    ]);
  });

  it('Should hide reviewer action buttons when reviewer is false', () => {
    const wrapper = shallowMount(qualitynomination_Details, {
      propsData: {
        ...baseProps,
        reviewer: false,
      },
    });

    expect(wrapper.find('[data-ban-problem-button]').exists()).toBe(false);
  });
});
