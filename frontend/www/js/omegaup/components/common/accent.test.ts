import { mount } from '@vue/test-utils';
import common_Typeahead from './Typeahead.vue';

describe('Typeahead accent-insensitive results', () => {
  const tags = [
    { key: 'ordenando-dos-numeros', value: 'Ordenando Números' },
    { key: 'division-ab', value: 'División a/b' },
    {
      key: 'suma',
      value: 'Suma (<strong>suma</strong>)',
    },
  ];

  function mountTypeahead() {
    return mount(common_Typeahead, {
      propsData: {
        existingOptions: tags,
        activationThreshold: 2,
        debounceDelay: 0,
      },
    });
  }

  it('keeps accented titles when the query has no accent', async () => {
    jest.useFakeTimers();
    const wrapper = mountTypeahead();

    await wrapper.find('input.multiselect__input').setValue('division');
    jest.advanceTimersByTime(0);
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('División a/b');
    expect(wrapper.text()).toContain('Ordenando Números');
    expect(wrapper.emitted('update-existing-options')).toEqual([['division']]);

    jest.useRealTimers();
  });

  it('renders HTML labels instead of showing the tags as text', async () => {
    const wrapper = mountTypeahead();

    await wrapper.find('input.multiselect__input').setValue('suma');
    await wrapper.vm.$nextTick();

    expect(wrapper.html()).toContain('<strong>suma</strong>');
    expect(wrapper.html()).not.toContain('&lt;strong&gt;');
    expect(wrapper.text()).toContain('Suma (suma)');
  });

  it('decodes escaped text in the selected label', () => {
    const wrapper = mount(common_Typeahead, {
      propsData: {
        existingOptions: [],
        value: { key: 'ab', value: 'A &amp; B (<strong>ab</strong>)' },
      },
    });

    expect(wrapper.find('.omegaup-typeahead-label').text()).toBe('A & B (ab)');
    expect(wrapper.html()).toContain('<strong>ab</strong>');
  });
});
