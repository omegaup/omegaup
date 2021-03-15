import { mount } from '@vue/test-utils';
import common_Typeahead from './Typeahead.vue';
import VoerroTagsInput from '@voerro/vue-tagsinput';
import Vue from 'vue';

describe('Typeahead.vue', () => {
  it('Should not call update-existing-options with a short query', async () => {
    const wrapper = mount(common_Typeahead, {
      propsData: {
        existingOptions: [],
      },
    });

    const tagsInput = wrapper.findComponent(VoerroTagsInput);
    tagsInput.vm.$emit('change', 'qu');
    expect(wrapper.emitted()).toEqual({});
  });

  it('Should call update-existing-options with a longer query', async () => {
    const wrapper = mount(common_Typeahead, {
      propsData: {
        existingOptions: [],
      },
    });

    const tagsInput = wrapper.findComponent(VoerroTagsInput);
    tagsInput.vm.$emit('change', 'query');
    expect(wrapper.emitted()).toEqual({
      'update-existing-options': [['query']],
    });
  });

  it('Should call update:value with a non-empty tag', async () => {
    const wrapper = mount(common_Typeahead, {
      propsData: {
        existingOptions: [],
      },
    });

    const tagsInput = wrapper.findComponent(VoerroTagsInput);
    tagsInput.vm.$emit('input', [{ key: 'key', value: 'value' }]);
    tagsInput.vm.$emit('tag-added');
    await Vue.nextTick();
    expect(wrapper.emitted()).toEqual({
      'update:value': [['key'], ['key']],
    });
  });

  it('Should call update:value with an empty tag', async () => {
    const wrapper = mount(common_Typeahead, {
      propsData: {
        existingOptions: [{ key: 'key', value: 'value' }],
      },
    });

    const tagsInput = wrapper.findComponent(VoerroTagsInput);
    tagsInput.vm.$emit('input', []);
    tagsInput.vm.$emit('tag-removed');
    await Vue.nextTick();
    expect(wrapper.emitted()).toEqual({
      'update:value': [[null]],
    });
  });
});
