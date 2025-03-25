import { shallowMount } from '@vue/test-utils';
import T from '../../lang';

import course_Archive from './Archive.vue';

describe('Archive.vue', () => {
  it('Should show the correct message for course', () => {
    const wrapper = shallowMount(course_Archive, {
      propsData: {
        alreadyArchived: false,
        archiveHelpText: T.courseArchiveHelpText,
        archiveHeaderTitle: T.wordsArchiveCourse,
        archiveButtonDescription: T.wordsArchiveCourse,
        archiveConfirmText: T.courseArchiveConfirmText,
      },
    });
    expect(wrapper.text()).toContain(T.courseArchiveHelpText);
    expect(wrapper.text()).toContain(T.wordsArchiveCourse);
  });

  it('Should show the correct message for contest', () => {
    const wrapper = shallowMount(course_Archive, {
      propsData: {
        alreadyArchived: false,
        archiveHelpText: T.contestEditArchiveHelpText,
        archiveHeaderTitle: T.contestEditArchiveContest,
        archiveButtonDescription: T.contestEditArchiveContest,
        archiveConfirmText: T.contestEditArchiveConfirmText,
      },
    });
    expect(wrapper.text()).toContain(T.contestEditArchiveHelpText);
    expect(wrapper.text()).toContain(T.contestEditArchiveContest);
  });
});
