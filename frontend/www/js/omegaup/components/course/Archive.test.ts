import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import course_Archive from './Archive.vue';

describe('Archive.vue', () => {
  it('Should show the correct message', () => {
    const wrapper = shallowMount(course_Archive, {
      propsData: {
        alreadyArchived: false,
      },
    });
    expect(wrapper.text()).toContain(T.courseArchiveHelpText);
    expect(wrapper.text()).toContain(T.wordsArchiveCourse);
  });
});
