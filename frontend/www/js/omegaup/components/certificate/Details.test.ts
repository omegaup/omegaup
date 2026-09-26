import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';

import T from '../../lang';
import * as ui from '../../ui';

import certificate_Details from './Details.vue';

// defineComponent() is typed for Vue 2.7/3 interop; @vue/test-utils@1 expects
// ComponentOptions<Vue>. Runtime is correct — assertion removed with test-utils@2.
const Details = (certificate_Details as unknown) as ComponentOptions<Vue>;

describe('Details.vue', () => {
  it('Should render the certificate details title and markdown body', () => {
    const uuid = 'abc-123-uuid';
    const wrapper = shallowMount(Details, {
      propsData: {
        uuid,
      },
    });

    expect(wrapper.text()).toContain(T.certificateDetailsTitle);
    const markdown = wrapper.find('omegaup-markdown-stub');
    expect(markdown.exists()).toBeTruthy();
    expect(markdown.props().markdown).toBe(
      ui.formatString(T.certificateDetailsBody, {
        uuid: encodeURIComponent(uuid),
      }),
    );
  });
});
