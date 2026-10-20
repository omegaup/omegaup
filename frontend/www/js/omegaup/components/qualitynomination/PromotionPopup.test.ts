import { shallowMount } from '@vue/test-utils';
import qualitynomination_PromotionPopup from './PromotionPopup.vue';
import { AvailableViews } from './DemotionPopup.vue';

describe('PromotionPopup.vue', () => {
  it('Should render with initial Content view and disabled submit button', () => {
    const wrapper = shallowMount(qualitynomination_PromotionPopup, {
      propsData: {
        solved: true,
        tried: true,
      },
    });

    expect(wrapper.find('[data-promotion-popup]').exists()).toBe(true);
    expect(wrapper.vm.currentView).toBe(AvailableViews.Content);
    expect(
      wrapper.find('[data-submit-feedback-button]').attributes('disabled'),
    ).toBe('disabled');
  });

  it('Should enable submit button when difficulty or quality is selected', async () => {
    const wrapper = shallowMount(qualitynomination_PromotionPopup, {
      propsData: {
        solved: false,
        tried: true,
      },
    });

    await wrapper.setData({ difficulty: '2' });
    expect(
      wrapper.find('[data-submit-feedback-button]').attributes('disabled'),
    ).toBeUndefined();

    await wrapper.setData({ quality: '3' });
    expect(
      wrapper.find('[data-submit-feedback-button]').attributes('disabled'),
    ).toBeUndefined();
  });

  it('Should emit submit and switch to Thanks view on submit', async () => {
    const wrapper = shallowMount(qualitynomination_PromotionPopup, {
      propsData: {
        solved: true,
        tried: false,
      },
    });

    await wrapper.setData({
      difficulty: '1',
      quality: '4',
    });

    const submitBtn = wrapper.find('[data-submit-feedback-button]');
    await submitBtn.trigger('click');

    expect(wrapper.emitted('submit')).toEqual([
      [
        {
          solved: true,
          tried: false,
          difficulty: '1',
          quality: '4',
        },
      ],
    ]);
    expect(wrapper.vm.currentView).toBe(AvailableViews.Thanks);
  });

  it('Should handle dismiss methods properly', () => {
    const wrapper = shallowMount(qualitynomination_PromotionPopup);

    wrapper.vm.onDismiss();
    expect(wrapper.emitted('dismiss')).toEqual([[wrapper.vm, true]]);

    wrapper.vm.onHide(false);
    expect(wrapper.emitted('dismiss')).toEqual([
      [wrapper.vm, true],
      [wrapper.vm, false],
    ]);
  });
});
