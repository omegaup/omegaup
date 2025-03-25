import { mount } from '@vue/test-utils';
import T from '../../lang';
import course_Mine from './Mine.vue';
import type { types } from '../../api_types';

describe('Mine.vue', () => {
  it('Should display course admin list', () => {
    const wrapper = mount(course_Mine, {
      propsData: {
        courses: {
          admin: {
            accessMode: 'admin',
            activeTab: 'current',
            filteredCourses: {
              current: {},
              past: {},
            },
          },
        } as types.AdminCourses,
        isMainUserIdentity: true,
      },
    });
    expect(wrapper.find('a.btn-primary').text()).toBe(T.courseNew);
  });
});
