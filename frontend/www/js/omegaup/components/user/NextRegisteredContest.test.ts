import { shallowMount } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';
import user_NextRegisteredContest from './NextRegisteredContest.vue';
import { types } from '../../api_types';
import T from '../../lang';

const NextRegisteredContest = user_NextRegisteredContest as ComponentOptions<Vue>;

describe('NextRegisteredContest.vue', () => {
  const futureDate = new Date(Date.now() + 86400000);
  const finishDate = new Date(Date.now() + 172800000);

  const defaultContest: types.ContestListItem = {
    alias: 'test-contest',
    title: 'Test Contest Title',
    organizer: 'omegaUp Organizer',
    contestants: 128,
    recommended: true,
    start_time: futureDate,
    finish_time: finishDate,
  } as types.ContestListItem;

  it('Should render future contest with details button', () => {
    const wrapper = shallowMount(NextRegisteredContest, {
      propsData: {
        nextRegisteredContest: defaultContest,
      },
    });

    expect(wrapper.text()).toContain('Test Contest Title');
    expect(wrapper.text()).toContain('omegaUp Organizer');
    expect(wrapper.text()).toContain('128');
    expect(wrapper.text()).toContain(
      T.userNextRegisteredContestButtonSeeDetails,
    );
  });

  it('Should emit redirect event with alias when button is clicked', async () => {
    const wrapper = shallowMount(NextRegisteredContest, {
      propsData: {
        nextRegisteredContest: defaultContest,
      },
    });

    const button = wrapper.find('button.btn-primary');
    await button.trigger('click');

    expect(wrapper.emitted('redirect')).toBeTruthy();
    expect(wrapper.emitted('redirect')?.[0]).toEqual(['test-contest']);
  });

  it('Should render enter button when contest is already started', () => {
    const pastDate = new Date(Date.now() - 3600000);
    const wrapper = shallowMount(NextRegisteredContest, {
      propsData: {
        nextRegisteredContest: {
          ...defaultContest,
          start_time: pastDate,
        },
      },
    });

    expect(wrapper.text()).toContain(T.userNextRegisteredContestButtonEnter);
  });
});
