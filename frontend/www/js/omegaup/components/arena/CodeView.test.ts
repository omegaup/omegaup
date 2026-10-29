import { mount } from '@vue/test-utils';

import arena_CodeView from './CodeView.vue';

describe('CodeView.vue', () => {
  function dispatchWheel(
    wrapper: ReturnType<typeof mount>,
    deltaY: number,
    scrollInfo: { top: number; clientHeight: number; height: number },
  ): { propagated: boolean } {
    const editor = (wrapper.vm as any).cmWrapper.editor;
    jest.spyOn(editor, 'getScrollInfo').mockReturnValue(scrollInfo);
    const event = new WheelEvent('wheel', {
      deltaY,
      bubbles: true,
      cancelable: true,
    });
    let propagated = false;
    const editorElement = wrapper.find('.CodeMirror').element;
    editorElement.addEventListener('wheel', () => {
      propagated = true;
    });
    editorElement.dispatchEvent(event);
    return { propagated };
  }

  it('Should let the page scroll when the editor is at the bottom', () => {
    const wrapper = mount(arena_CodeView, {
      propsData: { value: 'int main() {}', language: 'cpp17-gcc' },
    });
    const { propagated } = dispatchWheel(wrapper, 100, {
      top: 100,
      clientHeight: 200,
      height: 300,
    });
    expect(propagated).toBe(false);
  });

  it('Should let the page scroll when the editor is at the top', () => {
    const wrapper = mount(arena_CodeView, {
      propsData: { value: 'int main() {}', language: 'cpp17-gcc' },
    });
    const { propagated } = dispatchWheel(wrapper, -100, {
      top: 0,
      clientHeight: 200,
      height: 300,
    });
    expect(propagated).toBe(false);
  });

  it('Should keep scrolling the editor while it has content left', () => {
    const wrapper = mount(arena_CodeView, {
      propsData: { value: 'int main() {}', language: 'cpp17-gcc' },
    });
    const { propagated } = dispatchWheel(wrapper, 100, {
      top: 10,
      clientHeight: 200,
      height: 300,
    });
    expect(propagated).toBe(true);
  });
});
