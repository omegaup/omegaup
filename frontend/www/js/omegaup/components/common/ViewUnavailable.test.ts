import { shallowMount } from '@vue/test-utils';

import T from '../../lang';
import common_ViewUnavailable from './ViewUnavailable.vue';

describe('ViewUnavailable.vue', () => {
  it('Should show the default title when no message is given', () => {
    const wrapper = shallowMount(common_ViewUnavailable);

    expect(wrapper.text()).toContain(T.viewUnavailableTitle);
  });

  it('Should show a custom title and description', () => {
    const wrapper = shallowMount(common_ViewUnavailable, {
      propsData: {
        title: 'Section turned off',
        description: 'The EphemeralGrader IDE is currently disabled.',
      },
    });

    expect(wrapper.text()).toContain('Section turned off');
    expect(wrapper.text()).toContain(
      'The EphemeralGrader IDE is currently disabled.',
    );
  });
});
