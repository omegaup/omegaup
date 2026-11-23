import { mount, shallowMount } from '@vue/test-utils';

import { types } from '../../api_types';

import user_FollowList from './FollowList.vue';

const users: types.FollowUser[] = [
  {
    username: 'ana',
    gravatar_32: 'https://secure.gravatar.com/avatar/abc?s=32',
    name: 'Ana P.',
    classname: 'user-rank-unranked',
  },
  {
    username: 'bruno',
    gravatar_32: 'https://secure.gravatar.com/avatar/def?s=32',
    name: undefined,
    classname: 'user-rank-expert',
  },
];

describe('FollowList.vue', () => {
  it('Should show the empty message when there is nobody to list', () => {
    const wrapper = shallowMount(user_FollowList, {
      propsData: {
        users: [],
        totalRows: 0,
        page: 1,
        pageSize: 20,
        emptyMessage: 'No followers yet',
      },
    });

    expect(wrapper.find('[data-follow-list-empty]').text()).toBe(
      'No followers yet',
    );
    expect(wrapper.find('[data-follow-list-item]').exists()).toBe(false);
  });

  it('Should list every user on the page', () => {
    const wrapper = mount(user_FollowList, {
      propsData: { users, totalRows: 2, page: 1, pageSize: 20 },
    });

    expect(wrapper.findAll('[data-follow-list-item]').length).toBe(2);
    expect(wrapper.text()).toContain('ana');
    expect(wrapper.text()).toContain('bruno');
    expect(wrapper.find('[data-follow-list-empty]').exists()).toBe(false);
  });

  it("Should show each user's gravatar as their avatar", () => {
    const wrapper = mount(user_FollowList, {
      propsData: { users, totalRows: 2, page: 1, pageSize: 20 },
    });

    const sources = wrapper
      .findAll('[data-follow-list-avatar]')
      .wrappers.map((avatar) => avatar.attributes('src'));
    expect(sources).toEqual([
      'https://secure.gravatar.com/avatar/abc?s=32',
      'https://secure.gravatar.com/avatar/def?s=32',
    ]);
  });

  it('Should hide the pager when everything fits on one page', () => {
    const wrapper = mount(user_FollowList, {
      propsData: { users, totalRows: 2, page: 1, pageSize: 20 },
    });

    expect(wrapper.find('ul.pagination').exists()).toBe(false);
  });

  it('Should build a pager when there is more than one page', () => {
    const wrapper = mount(user_FollowList, {
      propsData: { users, totalRows: 45, page: 1, pageSize: 20 },
    });

    // 45 rows at 20 per page is 3 pages, plus the « » controls.
    const labels = wrapper
      .findAll('ul.pagination li')
      .wrappers.map((item) => item.text());
    expect(labels).toEqual(['«', '1', '2', '3', '»']);
    expect(wrapper.find('li.active').text()).toBe('1');
  });

  it('Should mark the current page as active', () => {
    const wrapper = mount(user_FollowList, {
      propsData: { users, totalRows: 45, page: 2, pageSize: 20 },
    });

    expect(wrapper.find('li.active').text()).toBe('2');
  });

  it('Should emit page-changed when a page is clicked', async () => {
    const wrapper = mount(user_FollowList, {
      propsData: { users, totalRows: 45, page: 1, pageSize: 20 },
    });

    const pageLinks = wrapper.findAll('ul.pagination li a');
    // Index 2 is page "2": « is 0, page 1 is 1.
    await pageLinks.at(2).trigger('click');

    expect(wrapper.emitted('page-changed')?.[0]?.[0]).toBe(2);
  });
});
