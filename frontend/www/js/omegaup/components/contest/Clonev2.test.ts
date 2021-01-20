import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import contest_Clone from './Clonev2.vue';

describe('Clonev2.vue', () => {
  it('Should display the form', async () => {
    const wrapper = shallowMount(contest_Clone);

    expect(wrapper.text()).toContain(T.wordsTitle);
  });

  it('Should pass the right arguments to event', async () => {
    const wrapper = shallowMount(contest_Clone);

    const contest = {
      alias: 'contestAlias',
      title: 'Contest Title',
      description: 'Contest description.',
    };
    await wrapper.setData(contest);

    await wrapper.find('button[type="submit"]').trigger('click');
    expect(wrapper.emitted('clone')).toBeDefined();
  });
});
