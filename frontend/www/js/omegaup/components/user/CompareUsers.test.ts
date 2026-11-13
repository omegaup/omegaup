import { shallowMount } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';
import user_CompareUsers from './CompareUsers.vue';
import { types } from '../../api_types';
import T from '../../lang';

const CompareUsers = user_CompareUsers as ComponentOptions<Vue>;

describe('CompareUsers.vue', () => {
  const user1 = {
    profile: {
      username: 'user1',
      name: 'User One',
      classname: 'user-rank-master',
      is_private: false,
    } as types.UserProfileInfo,
    solvedProblemsCount: 100,
    contestsCount: 10,
  };

  const user2 = {
    profile: {
      username: 'user2',
      name: 'User Two',
      classname: 'user-rank-expert',
      is_private: false,
    } as types.UserProfileInfo,
    solvedProblemsCount: 80,
    contestsCount: 8,
  };

  it('Should display empty state when no users are compared', () => {
    const wrapper = shallowMount(CompareUsers);
    expect(wrapper.text()).toContain(T.compareDescription);
    expect(wrapper.find('button.btn-primary').attributes('disabled')).toBe(
      'disabled',
    );
  });

  it('Should show loading state when isLoading is true', () => {
    const wrapper = shallowMount(CompareUsers, {
      propsData: {
        isLoading: true,
      },
    });

    expect(wrapper.find('.spinner-border').exists()).toBe(true);
  });

  it('Should emit compare event when compare button is clicked', async () => {
    const wrapper = shallowMount(CompareUsers, {
      propsData: {
        selectedUser1: { key: 'user1', value: 'User 1' },
        selectedUser2: { key: 'user2', value: 'User 2' },
      },
    });

    const compareBtn = wrapper.find('button.btn-primary');
    expect(compareBtn.attributes('disabled')).toBeUndefined();
    await compareBtn.trigger('click');

    expect(wrapper.emitted('compare')).toBeTruthy();
    expect(wrapper.emitted('compare')?.[0]).toEqual([
      { username1: 'user1', username2: 'user2' },
    ]);
  });

  it('Should correctly determine winner and loser classes', () => {
    const wrapper = shallowMount(CompareUsers, {
      propsData: {
        user1,
        user2,
      },
    });

    const vm = (wrapper.vm as unknown) as {
      getComparisonClass: (userNumber: number) => string;
    };
    expect(vm.getComparisonClass(1)).toBe('compare-winner');
    expect(vm.getComparisonClass(2)).toBe('compare-loser');
  });
});
