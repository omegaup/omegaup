import { ComponentOptions } from 'vue';
import { shallowMount } from '@vue/test-utils';
import arena_CodeView, { languageModeMap } from './CodeView.vue';

describe('CodeView.vue', () => {
  it('Should handle default options and emit initial events on mount', async () => {
    let dispatchedDetail: any = null;
    const eventListener = (e: Event) => {
      dispatchedDetail = (e as CustomEvent).detail;
    };
    window.addEventListener('code-and-language-set', eventListener);

    const wrapper = shallowMount(arena_CodeView as ComponentOptions<Vue>, {
      propsData: {
        value: '#include <iostream>',
        language: 'cpp17-gcc',
      },
    });

    expect(wrapper.find('[data-code-mirror]').exists()).toBe(true);
    expect(wrapper.emitted('input')).toEqual([['#include <iostream>']]);
    expect(wrapper.emitted('change')).toEqual([['#include <iostream>']]);
    expect(wrapper.emitted('change-language')).toEqual([['cpp17-gcc']]);
    expect(dispatchedDetail).toEqual({
      code: '#include <iostream>',
      language: 'cpp17-gcc',
    });

    window.removeEventListener('code-and-language-set', eventListener);
  });

  it('Should update mode when language prop changes', async () => {
    const wrapper = shallowMount(arena_CodeView as ComponentOptions<Vue>, {
      propsData: {
        value: 'print("hello")',
        language: 'py3',
      },
    });

    expect(wrapper.vm.$data.mode).toBe(languageModeMap['py3']);

    await wrapper.setProps({ language: 'java' });
    expect(wrapper.vm.$data.mode).toBe(languageModeMap['java']);
  });
});
