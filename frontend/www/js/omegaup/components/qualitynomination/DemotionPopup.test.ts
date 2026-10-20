import { shallowMount } from '@vue/test-utils';
import qualitynomination_DemotionPopup, {
  AvailableViews,
} from './DemotionPopup.vue';

describe('DemotionPopup.vue', () => {
  it('Should render with initial Question view', () => {
    const wrapper = shallowMount(qualitynomination_DemotionPopup);

    expect(wrapper.find('[data-demotion-popup]').exists()).toBe(true);
    expect(wrapper.vm.currentView).toBe(AvailableViews.Question);
    expect(wrapper.find('select[name="selectedReason"]').exists()).toBe(true);
    expect(
      wrapper.find('[data-submit-report-button]').attributes('disabled'),
    ).toBe('disabled');
  });

  it('Should handle selection of duplicate reason and show original input', async () => {
    const wrapper = shallowMount(qualitynomination_DemotionPopup);

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
    const wrapper = shallowMount(qualitynomination_DemotionPopup);

    await wrapper.setData({
      selectedReason: 'poorly-described',
      rationale: 'Needs more examples',
    });

    const submitBtn = wrapper.find('[data-submit-report-button]');
    expect(submitBtn.attributes('disabled')).toBeUndefined();

    await submitBtn.trigger('click');

    expect(wrapper.emitted('submit')).toBeTruthy();
    expect(wrapper.vm.currentView).toBe(AvailableViews.Thanks);
  });

  it('Should emit dismiss when onHide is invoked', async () => {
    const wrapper = shallowMount(qualitynomination_DemotionPopup);

    wrapper.vm.onHide();
    expect(wrapper.emitted('dismiss')).toBeTruthy();
  });
});
