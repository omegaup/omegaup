import { mount, shallowMount } from '@vue/test-utils';
import T from '../../lang';
import course_Mine from './Mine.vue';
import type { types } from '../../api_types';
import common_EmptyState from '../common/EmptyState.vue';
import course_FilteredList from './FilteredList.vue';

describe('Mine.vue', () => {
  const emptyCourses: types.AdminCourses = {
    admin: {
      accessMode: 'admin',
      activeTab: 'current',
      filteredCourses: {
        archived: { courses: [], timeType: 'archived' },
        current: { courses: [], timeType: 'current' },
        past: { courses: [], timeType: 'past' },
        teachingAssistant: { courses: [], timeType: 'teachingAssistant' },
      },
    },
  };

  const coursesWithContent: types.AdminCourses = {
    admin: {
      accessMode: 'admin',
      activeTab: 'current',
      filteredCourses: {
        archived: { courses: [], timeType: 'archived' },
        current: {
          courses: [
            {
              alias: 'test-course',
              counts: {},
              name: 'Test Course',
              description: 'Test description',
              start_time: new Date(),
              admission_mode: 'public',
              assignments: [],
              is_open: true,
            },
          ],
          timeType: 'current',
        },
        past: { courses: [], timeType: 'past' },
        teachingAssistant: { courses: [], timeType: 'teachingAssistant' },
      },
    },
  };

  it('Should display course admin list', () => {
    const wrapper = mount(course_Mine, {
      propsData: {
        courses: emptyCourses,
        isMainUserIdentity: true,
      },
    });
    expect(wrapper.find('a.btn-primary').text()).toBe(T.courseNew);
    expect(wrapper.findComponent(common_EmptyState).exists()).toBe(true);
  });

  it('Should display EmptyState when user has no courses', () => {
    const wrapper = shallowMount(course_Mine, {
      propsData: {
        courses: emptyCourses,
        isMainUserIdentity: true,
      },
    });

    const emptyState = wrapper.findComponent(common_EmptyState);
    expect(emptyState.exists()).toBe(true);
    expect(emptyState.props('title')).toBe(T.courseListEmptyTitle);
    expect(emptyState.props('description')).toBe(T.courseListEmptyDescription);
    expect(emptyState.props('icon')).toBe('graduation-cap');
    expect(emptyState.props('buttonText')).toBe(T.courseNew);
    expect(emptyState.props('buttonLink')).toBe('/course/new/');
    expect(wrapper.findComponent(course_FilteredList).exists()).toBe(false);
  });

  it('Should not display primary action button in EmptyState when user is not main identity', () => {
    const wrapper = shallowMount(course_Mine, {
      propsData: {
        courses: emptyCourses,
        isMainUserIdentity: false,
      },
    });

    const emptyState = wrapper.findComponent(common_EmptyState);
    expect(emptyState.exists()).toBe(true);
    expect(emptyState.props('buttonText')).toBe('');
  });

  it('Should display course filtered list when user has courses', () => {
    const wrapper = shallowMount(course_Mine, {
      propsData: {
        courses: coursesWithContent,
        isMainUserIdentity: true,
      },
    });

    expect(wrapper.findComponent(common_EmptyState).exists()).toBe(false);
    expect(wrapper.findComponent(course_FilteredList).exists()).toBe(true);
    expect(wrapper.find('a.btn-primary').text()).toBe(T.courseNew);
  });
});
