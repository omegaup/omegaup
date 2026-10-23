import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import qualitynomination_DemotionPopup, {
  AvailableViews,
} from './DemotionPopup.vue';

const DemotionPopup = qualitynomination_DemotionPopup as ComponentOptions<Vue>;

type DemotionPopupVm = Vue & {
  currentView: AvailableViews;
  selectedReason: string;
  rationale: string;
  original: string;
  onHide: () => void;
};

describe('DemotionPopup.vue', () => {
  it('Should render with initial Question view', () => {
    const wrapper = shallowMount(DemotionPopup);

    expect(wrapper.find('[data-demotion-popup]').exists()).toBe(true);
    expect((wrapper.vm as DemotionPopupVm).currentView).toBe(
      AvailableViews.Question,
    );
    expect(wrapper.find('select[name="selectedReason"]').exists()).toBe(true);
    expect(
      wrapper.find('[data-submit-report-button]').attributes('disabled'),
    ).toBe('disabled');
  });

  it('Should handle selection of duplicate reason and show original input', async () => {
    const wrapper = shallowMount(DemotionPopup);

    expect(wrapper.find('input[name="original"]').exists()).toBe(false);

    await wrapper.setData({ selectedReason: 'duplicate' });

    expect(wrapper.find('input[name="original"]').exists()).toBe(true);
    expect(
      wrapper.find('[data-submit-report-button]').attributes('disabled'),
    ).toBe('disabled');

    await wrapper.setData({
      original: 'https://omegaup.com/arena/problem/test/',
    });
    expect(
      wrapper.find('[data-submit-report-button]').attributes('disabled'),
    ).toBeUndefined();
  });

  it('Should emit submit and switch to Thanks view on submit', async () => {
    const wrapper = shallowMount(DemotionPopup);

    await wrapper.setData({
      selectedReason: 'poorly-described',
      rationale: 'Needs more examples',
    });

    const submitBtn = wrapper.find('[data-submit-report-button]');
    expect(submitBtn.attributes('disabled')).toBeUndefined();

    await submitBtn.trigger('click');

    expect(wrapper.emitted('submit')).toBeTruthy();
    expect((wrapper.vm as DemotionPopupVm).currentView).toBe(
      AvailableViews.Thanks,
    );
  });

  it('Should emit dismiss when onHide is invoked', async () => {
    const wrapper = shallowMount(DemotionPopup);

    (wrapper.vm as DemotionPopupVm).onHide();
    expect(wrapper.emitted('dismiss')).toBeTruthy();
  });

  it('Should emit dismiss after timeout following a successful submission', async () => {
    jest.useFakeTimers();
    try {
      const wrapper = shallowMount(DemotionPopup);

      await wrapper.setData({
        selectedReason: 'poorly-described',
        rationale: 'Needs more examples',
      });

      const submitBtn = wrapper.find('[data-submit-report-button]');
      await submitBtn.trigger('click');

      expect(wrapper.emitted('submit')).toBeTruthy();
      expect((wrapper.vm as DemotionPopupVm).currentView).toBe(
        AvailableViews.Thanks,
      );
      expect(wrapper.emitted('dismiss')).toBeUndefined();

      jest.advanceTimersByTime(2000);

      expect(wrapper.emitted('dismiss')).toBeTruthy();
    } finally {
      jest.useRealTimers();
    }
  });
});
