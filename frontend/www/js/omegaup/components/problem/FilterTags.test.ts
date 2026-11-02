import { mount } from '@vue/test-utils';
import VueTypeaheadBootstrap from 'vue-typeahead-bootstrap';

import T from '../../lang';
import type { types } from '../../api_types';
import problem_FilterTags from './FilterTags.vue';

const tagName = 'problemTagArrays';
const otherTagName = 'problemTagLoops';
const tag = { name: tagName, problemCount: 3 };
const otherTag = { name: otherTagName, problemCount: 2 };

describe('FilterTags.vue', () => {
  it('Should handle list of tags', () => {
    const wrapper = mount(problem_FilterTags, {
      propsData: {
        tags: [
          { name: 'problemTagMatrices', problemCount: 5 },
          { name: 'problemTagDiophantineEquations', problemCount: 4 },
          { name: 'problemTagInputAndOutput', problemCount: 3 },
          { name: 'problemTagArrays', problemCount: 2 },
        ] as types.TagWithProblemCount[],
        publicQualityTags: [
          { name: 'problemTagConditionals', problemCount: 1 },
          { name: 'problemTagLoops', problemCount: 1 },
          { name: 'problemTagFunctions', problemCount: 1 },
          { name: 'problemTagCharsAndStrings', problemCount: 1 },
          { name: 'problemTagSimulation', problemCount: 1 },
          { name: 'problemTagAnalyticGeometry', problemCount: 1 },
        ] as types.TagWithProblemCount[],
      },
    });

    expect(wrapper.text()).toContain(T.problemTagMatrices);
    expect(wrapper.text()).not.toContain(T.problemTagConditionals);
    expect(wrapper.find('input[value="problemTagArrays"]').exists()).toBe(true);
    expect(wrapper.find('input[value="problemTagConditionals"]').exists()).toBe(
      false,
    );
  });

  it.each([
    {
      description: 'include a tag and clear its exclusion',
      initialSelected: [],
      initialExcluded: [tagName],
      changedCheckbox: 0,
      checked: true,
      expectedSelected: [tagName],
      expectedExcluded: [],
    },
    {
      description: 'exclude a tag and clear its inclusion',
      initialSelected: [tagName],
      initialExcluded: [],
      changedCheckbox: 1,
      checked: true,
      expectedSelected: [],
      expectedExcluded: [tagName],
    },
    {
      description: 'clear an included tag',
      initialSelected: [tagName],
      initialExcluded: [],
      changedCheckbox: 0,
      checked: false,
      expectedSelected: [],
      expectedExcluded: [],
    },
    {
      description: 'clear an excluded tag',
      initialSelected: [],
      initialExcluded: [tagName],
      changedCheckbox: 1,
      checked: false,
      expectedSelected: [],
      expectedExcluded: [],
    },
  ])(
    'Should $description',
    async ({
      initialSelected,
      initialExcluded,
      changedCheckbox,
      checked,
      expectedSelected,
      expectedExcluded,
    }) => {
      const wrapper = mount(problem_FilterTags, {
        propsData: {
          publicQualityTags: [] as types.TagWithProblemCount[],
          tags: [tag],
          selectedTags: initialSelected,
          selectedExcludedTags: initialExcluded,
        },
      });

      const checkboxes = wrapper.findAll(
        '.tag-filter-row input[type="checkbox"]',
      );
      const include = checkboxes.at(0);
      const exclude = checkboxes.at(1);
      expect((include.element as HTMLInputElement).checked).toBe(
        initialSelected.includes(tagName),
      );
      expect((exclude.element as HTMLInputElement).checked).toBe(
        initialExcluded.includes(tagName),
      );

      await checkboxes.at(changedCheckbox).setChecked(checked);
      const emittedFilters = wrapper.emitted('filters-changed');
      expect((include.element as HTMLInputElement).checked).toBe(
        expectedSelected.includes(tagName),
      );
      expect((exclude.element as HTMLInputElement).checked).toBe(
        expectedExcluded.includes(tagName),
      );
      expect(emittedFilters?.[emittedFilters.length - 1][0]).toEqual({
        selected: expectedSelected,
        excluded: expectedExcluded,
      });
    },
  );

  it.each([
    {
      description: 'keep other selected tags when excluding one',
      initialSelected: [tagName, otherTagName],
      initialExcluded: [],
      change: { kind: 'exclude' as const, rowIndex: 1 },
      expectedSelected: [tagName],
      expectedExcluded: [otherTagName],
    },
    {
      description: 'keep other excluded tags when including one',
      initialSelected: [],
      initialExcluded: [tagName, otherTagName],
      change: { kind: 'include' as const, rowIndex: 1 },
      expectedSelected: [otherTagName],
      expectedExcluded: [tagName],
    },
    {
      description: 'clear the exclusion when a tag is added from the typeahead',
      initialSelected: [],
      initialExcluded: [tagName],
      change: { kind: 'typeahead' as const, name: tagName },
      expectedSelected: [tagName],
      expectedExcluded: [],
    },
  ])(
    'Should $description',
    async ({
      initialSelected,
      initialExcluded,
      change,
      expectedSelected,
      expectedExcluded,
    }) => {
      const wrapper = mount(problem_FilterTags, {
        propsData: {
          publicQualityTags: [tag, otherTag],
          tags: [tag, otherTag],
          selectedTags: initialSelected,
          selectedExcludedTags: initialExcluded,
        },
      });

      if (change.kind === 'typeahead') {
        await wrapper
          .findComponent(VueTypeaheadBootstrap)
          .vm.$emit('hit', change.name);
        await wrapper.vm.$nextTick();
      } else {
        await wrapper
          .findAll('.tag-filter-row')
          .at(change.rowIndex)
          .findAll('input[type="checkbox"]')
          .at(change.kind === 'include' ? 0 : 1)
          .setChecked(true);
      }

      const emittedFilters = wrapper.emitted('filters-changed');
      expect(emittedFilters?.[emittedFilters.length - 1][0]).toEqual({
        selected: expectedSelected,
        excluded: expectedExcluded,
      });
    },
  );

  it.each([
    { selected: [tagName], excluded: [], checkedIndex: 0 },
    { selected: [], excluded: [tagName], checkedIndex: 1 },
  ])(
    'Should restore selected tags from the collection URL state: $selected / $excluded',
    ({ selected, excluded, checkedIndex }) => {
      const wrapper = mount(problem_FilterTags, {
        propsData: {
          publicQualityTags: [] as types.TagWithProblemCount[],
          tags: [tag],
          selectedTags: selected,
          selectedExcludedTags: excluded,
        },
      });

      const checkboxes = wrapper.findAll(
        '.tag-filter-row input[type="checkbox"]',
      );
      expect(
        (checkboxes.at(checkedIndex).element as HTMLInputElement).checked,
      ).toBe(true);
      expect(
        (checkboxes.at(1 - checkedIndex).element as HTMLInputElement).checked,
      ).toBe(false);
    },
  );

  it('Should add a public quality tag only once', async () => {
    const wrapper = mount(problem_FilterTags, {
      propsData: {
        publicQualityTags: [tag],
        tags: [],
      },
    });

    const typeahead = wrapper.findComponent(VueTypeaheadBootstrap);
    await typeahead.vm.$emit('hit', tagName);
    await typeahead.vm.$emit('hit', tagName);

    expect(wrapper.emitted('filters-changed')).toHaveLength(1);
    expect(wrapper.emitted('filters-changed')?.[0][0]).toEqual({
      selected: [tagName],
      excluded: [],
    });
  });
});
