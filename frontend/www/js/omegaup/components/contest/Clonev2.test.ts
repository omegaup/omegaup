import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import contest_Clone from './Clonev2.vue';

describe('Linksv2.vue', () => {
  it('Should display the form', async () => {
    const wrapper = shallowMount(contest_Clone);

    expect(wrapper.text()).toContain(T.wordsTitle);
  });
});
