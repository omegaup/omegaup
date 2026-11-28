import { shallowMount } from '@vue/test-utils';

import T from '../../lang';

import user_FollowButton from './FollowButton.vue';

describe('FollowButton.vue', () => {
  it('Should show the follow label when not following', () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: false,
      },
    });

    expect(wrapper.find('button[data-follow-button]').text()).toBe(
      T.profileFollow,
    );
  });

  it('Should show the following label when already following', () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: true,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: false,
      },
    });

    expect(wrapper.find('button[data-follow-button]').text()).toBe(
      T.profileFollowing,
    );
  });

  it('Should display both counts', () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 42,
        followingCount: 7,
        isLoggedIn: true,
        isOwnProfile: false,
      },
    });

    expect(wrapper.find('[data-followers-count]').text()).toContain('42');
    expect(wrapper.find('[data-followers-count]').text()).toContain(
      T.profileFollowers,
    );
    expect(wrapper.find('[data-following-count]').text()).toContain('7');
    expect(wrapper.find('[data-following-count]').text()).toContain(
      T.profileFollowing,
    );
  });

  it('Should hide the button on your own profile but keep the counts', () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: true,
      },
    });

    expect(wrapper.find('button[data-follow-button]').exists()).toBe(false);
    expect(wrapper.find('[data-followers-count]').text()).toContain('3');
  });

  it('Should hide the button when not logged in', () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: false,
        isOwnProfile: false,
      },
    });

    expect(wrapper.find('button[data-follow-button]').exists()).toBe(false);
  });

  it('Should emit show-list when a count is clicked', async () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: false,
      },
    });

    await wrapper.find('[data-followers-count]').trigger('click');
    expect(wrapper.emitted('show-list')?.[0]?.[0]).toBe('followers');

    await wrapper.find('[data-following-count]').trigger('click');
    expect(wrapper.emitted('show-list')?.[1]?.[0]).toBe('following');
  });

  it('Should allow opening the lists even on your own profile', async () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: true,
      },
    });

    await wrapper.find('[data-followers-count]').trigger('click');
    expect(wrapper.emitted('show-list')?.[0]?.[0]).toBe('followers');
  });

  it('Should emit follow with the username and bump the count on success', async () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: false,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: false,
      },
    });

    await wrapper.find('button[data-follow-button]').trigger('click');

    const payload = wrapper.emitted('follow')?.[0]?.[0] as {
      username: string;
      onSuccess: () => void;
    };
    expect(payload.username).toBe('ana');
    expect(wrapper.emitted('unfollow')).toBeUndefined();

    // The button only flips once the API call reports success.
    expect(wrapper.find('button[data-follow-button]').text()).toBe(
      T.profileFollow,
    );
    payload.onSuccess();
    await wrapper.vm.$nextTick();

    expect(wrapper.find('button[data-follow-button]').text()).toBe(
      T.profileFollowing,
    );
    expect(wrapper.find('[data-followers-count]').text()).toContain('4');
  });

  it('Should emit unfollow and drop the count on success', async () => {
    const wrapper = shallowMount(user_FollowButton, {
      propsData: {
        username: 'ana',
        isFollowing: true,
        followersCount: 3,
        followingCount: 5,
        isLoggedIn: true,
        isOwnProfile: false,
      },
    });

    await wrapper.find('button[data-follow-button]').trigger('click');

    const payload = wrapper.emitted('unfollow')?.[0]?.[0] as {
      username: string;
      onSuccess: () => void;
    };
    expect(payload.username).toBe('ana');
    expect(wrapper.emitted('follow')).toBeUndefined();

    payload.onSuccess();
    await wrapper.vm.$nextTick();

    expect(wrapper.find('button[data-follow-button]').text()).toBe(
      T.profileFollow,
    );
    expect(wrapper.find('[data-followers-count]').text()).toContain('2');
  });
});
