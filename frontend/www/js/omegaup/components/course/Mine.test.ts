import { mount } from '@vue/test-utils';
import expect from 'expect';
import T from '../../lang';
import course_Mine from './Mine.vue';
import { types } from '../../api_types';

describe('Mine.vue', () => {
  it('Should display course admin list', () => {
    const wrapper = mount(course_Mine, {
      propsData: {
        courses: <types.AdminCourses>{
          admin: {
            accessMode: 'admin',
            activeTab: 'current',
            filteredCourses: {
              current: {},
              past: {},
            },
          },
        },
        isMainUserIdentity: true,
      },
    });
    expect(wrapper.find('a.btn-primary').text()).toBe(T.courseNew);
  });
});
