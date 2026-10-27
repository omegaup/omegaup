jest.mock('vue-infinite-scroll', () => ({}));

import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';

import submissions_List from './List.vue';

const List = submissions_List as ComponentOptions<Vue>;

describe('List.vue', () => {
  const submissions: types.Submission[] = [
    {
      alias: 'sumas',
      language: 'cpp17-gcc',
      memory: 2 * 1024 * 1024,
      runtime: 1500,
      school_id: 1,
      school_name: 'Escuela Alfa',
      time: new Date(0),
      title: 'Sumas',
      username: 'alice',
      verdict: 'AC',
    },
    {
      alias: 'restas',
      language: 'py3',
      memory: 0,
      runtime: 0,
      time: new Date(0),
      title: 'Restas',
      username: 'alice',
      verdict: 'WA',
    },
  ];

  it('Should render a row for each submission', () => {
    const wrapper = shallowMount(List, {
      propsData: { submissions },
    });

    const rows = wrapper.findAll('tbody > tr');
    expect(rows.length).toBe(2);
    expect(wrapper.find('a[href="/arena/problem/sumas/"]').text()).toBe(
      'Sumas',
    );
    expect(rows.at(0).find('td.verdict').classes()).toContain('verdict-AC');
    expect(rows.at(0).text()).toContain(T.verdictAC);
    expect(rows.at(0).text()).toContain('1.50 s');
    expect(rows.at(0).text()).toContain('2.00 MiB');
  });

  it('Should show a dash when runtime or memory is zero', () => {
    const wrapper = shallowMount(List, {
      propsData: { submissions },
    });

    const cells = wrapper.findAll('tbody > tr').at(1).findAll('td');
    expect(cells.at(cells.length - 2).text()).toBe('—');
    expect(cells.at(cells.length - 1).text()).toBe('—');
  });

  it('Should show the author header when user is not included', () => {
    const wrapper = shallowMount(List, {
      propsData: { submissions, includeUser: false },
    });

    expect(wrapper.find('h4').text()).toContain(T.wordsBy);
    expect(wrapper.find('omegaup-common-typeahead-stub').exists()).toBe(false);
    expect(wrapper.find('a.school-text').exists()).toBe(false);
  });

  it('Should show the user search and coder column when user is included', () => {
    const wrapper = shallowMount(List, {
      propsData: { submissions, includeUser: true },
    });

    expect(wrapper.find('h4').exists()).toBe(false);
    expect(wrapper.find('omegaup-common-typeahead-stub').exists()).toBe(true);
    expect(wrapper.find('thead').text()).toContain(T.submissionsListCoder);
    expect(wrapper.find('a[href="/schools/profile/1/"]').text()).toBe(
      'Escuela Alfa',
    );
  });

  it('Should build the search link from the selected user', async () => {
    const wrapper = shallowMount(List, {
      propsData: { submissions, includeUser: true },
    });

    expect(wrapper.find('a[href="/submissions/"] > button').exists()).toBe(
      true,
    );

    await wrapper.setData({
      searchedUsername: { key: 'user name', value: 'user name' },
    });

    expect(
      wrapper.find('a[href="/submissions/user%20name/"] > button').exists(),
    ).toBe(true);
  });

  it('Should show placeholder rows while loading', () => {
    const wrapper = shallowMount(List, {
      propsData: { submissions: [], loading: true },
    });

    expect(wrapper.findAll('tbody .line').length).toBe(3);
  });
});
