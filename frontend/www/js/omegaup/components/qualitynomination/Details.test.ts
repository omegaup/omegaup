import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import qualitynomination_Details from './Details.vue';

const Details = qualitynomination_Details as ComponentOptions<Vue>;

type DetailsVm = Vue & {
  userUrl: (username: string) => string;
  problemUrl: (problemAlias: string) => string;
  showConfirmation: boolean;
  status: string;
  markResolution: (decision: boolean) => void;
};

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
    const wrapper = shallowMount(Details, {
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
    const wrapper = shallowMount(Details, {
      propsData: baseProps,
    });

    expect((wrapper.vm as DetailsVm).userUrl('test_user')).toBe(
      '/profile/test_user/',
    );
    expect((wrapper.vm as DetailsVm).problemUrl('test_problem')).toBe(
      '/arena/problem/test_problem/',
    );
  });

  it('Should show reviewer actions and handle resolution workflow', async () => {
    const wrapper = shallowMount(Details, {
      propsData: baseProps,
    });

    const banButton = wrapper.find('[data-ban-problem-button]');
    expect(banButton.exists()).toBe(true);

    await banButton.trigger('click');

    expect((wrapper.vm as DetailsVm).showConfirmation).toBe(true);
    expect((wrapper.vm as DetailsVm).status).toBe('banned');

    (wrapper.vm as DetailsVm).markResolution(true);

    expect((wrapper.vm as DetailsVm).showConfirmation).toBe(false);
    expect(wrapper.emitted('mark-resolution')).toEqual([
      [wrapper.vm, 'banned', true],
    ]);
  });

  it('Should hide reviewer action buttons when reviewer is false', () => {
    const wrapper = shallowMount(Details, {
      propsData: {
        ...baseProps,
        reviewer: false,
      },
    });

    expect(wrapper.find('[data-ban-problem-button]').exists()).toBe(false);
  });
});
