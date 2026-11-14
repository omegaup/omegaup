import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import qualitynomination_ReviewerPopup from './ReviewerPopup.vue';
import { AvailableViews } from './DemotionPopup.vue';

const ReviewerPopup = qualitynomination_ReviewerPopup as ComponentOptions<Vue>;

interface ProblemTag {
  text: string;
  value: string;
}

type ReviewerPopupVm = Vue & {
  currentView: AvailableViews;
  publicTagsList: string[];
  addOtherTag: (tag: string) => void;
  removeTag: (tag: string) => void;
  sortedProblemTags: ProblemTag[];
  onHide: () => void;
};

describe('ReviewerPopup.vue', () => {
  const baseProps = {
    allowUserAddTags: true,
    levelTags: ['problemLevelBasicKarel'],
    problemLevel: 'problemLevelBasicKarel',
    selectedPublicTags: ['math', 'algorithms'],
    problemAlias: 'test-problem',
    problemTitle: 'Test Problem',
  };

  it('Should render with initial Content view and public tags', () => {
    const wrapper = shallowMount(ReviewerPopup, {
      propsData: baseProps,
    });

    expect(wrapper.find('[data-reviewewr-popup]').exists()).toBe(true);
    expect((wrapper.vm as ReviewerPopupVm).currentView).toBe(
      AvailableViews.Content,
    );
    expect((wrapper.vm as ReviewerPopupVm).publicTagsList).toEqual([
      'math',
      'algorithms',
    ]);
  });

  it('Should add and remove tags properly', async () => {
    const wrapper = shallowMount(ReviewerPopup, {
      propsData: baseProps,
    });

    (wrapper.vm as ReviewerPopupVm).addOtherTag('geometry');
    expect((wrapper.vm as ReviewerPopupVm).publicTagsList).toContain(
      'geometry',
    );

    // Duplicate add should be ignored
    (wrapper.vm as ReviewerPopupVm).addOtherTag('geometry');
    expect(
      (wrapper.vm as ReviewerPopupVm).publicTagsList.filter(
        (t: string) => t === 'geometry',
      ).length,
    ).toBe(1);

    (wrapper.vm as ReviewerPopupVm).removeTag('geometry');
    expect((wrapper.vm as ReviewerPopupVm).publicTagsList).not.toContain(
      'geometry',
    );
  });

  it('Should emit rate-problem-as-reviewer and switch to Thanks view on submit', async () => {
    const wrapper = shallowMount(ReviewerPopup, {
      propsData: baseProps,
    });

    const submitBtn = wrapper.find('[data-review-submit-button]');
    expect(submitBtn.exists()).toBe(true);
    expect(submitBtn.attributes('disabled')).toBeUndefined();

    await submitBtn.trigger('click');

    expect(wrapper.emitted('rate-problem-as-reviewer')).toEqual([
      [
        {
          tags: ['math', 'algorithms'],
          qualitySeal: true,
        },
      ],
    ]);
    expect((wrapper.vm as ReviewerPopupVm).currentView).toBe(
      AvailableViews.Thanks,
    );
  });

  it('Should update publicTagsList when selectedPublicTags prop changes', async () => {
    const wrapper = shallowMount(ReviewerPopup, {
      propsData: baseProps,
    });

    await wrapper.setProps({
      selectedPublicTags: ['graphs', 'trees'],
    });

    expect((wrapper.vm as ReviewerPopupVm).publicTagsList).toEqual([
      'graphs',
      'trees',
    ]);
  });

  it('Should compute sortedProblemTags', () => {
    const wrapper = shallowMount(ReviewerPopup, {
      propsData: {
        ...baseProps,
        possibleTags: [
          'problemLevelBasicKarel',
          'problemLevelAdvancedCompetitiveProgramming',
        ],
      },
    });

    expect((wrapper.vm as ReviewerPopupVm).sortedProblemTags.length).toBe(2);
    expect((wrapper.vm as ReviewerPopupVm).sortedProblemTags[0]).toHaveProperty(
      'value',
    );
    expect((wrapper.vm as ReviewerPopupVm).sortedProblemTags[0]).toHaveProperty(
      'text',
    );
  });

  it('Should emit dismiss when onHide is invoked', () => {
    const wrapper = shallowMount(ReviewerPopup, {
      propsData: baseProps,
    });

    (wrapper.vm as ReviewerPopupVm).onHide();
    expect(wrapper.emitted('dismiss')).toBeTruthy();
  });
});
