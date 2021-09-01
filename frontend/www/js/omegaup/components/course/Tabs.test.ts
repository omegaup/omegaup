import { shallowMount } from '@vue/test-utils';
import T from '../../lang';
import course_Tabs from './Tabs.vue';

describe('Tabs.vue', () => {
  it('Should show tabs', () => {
    const wrapper = shallowMount(course_Tabs);

    expect(wrapper.text()).toContain(T.courseTabEnrolled);
    expect(wrapper.text()).toContain(T.courseTabFinished);
    expect(wrapper.text()).toContain(T.courseTabGeneral);
  });
});
