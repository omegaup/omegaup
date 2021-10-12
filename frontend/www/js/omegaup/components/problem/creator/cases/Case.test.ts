import { shallowMount, createLocalVue } from '@vue/test-utils';
import store from '@/js/omegaup/problem/creator/store';
import Case from './Case.vue';
import { NIL } from 'uuid';
import BootstrapVue from 'bootstrap-vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);

describe('Case.vue', () => {
  it('Should display test and 50 points. Points badge should have blue color', async () => {
    const wrapper = shallowMount(Case, {
      store,
      localVue,
      propsData: {
        name: 'test',
        defined: false,
        noGroup: true,
        caseId: NIL,
        groupId: NIL,
        points: 50,
      },
    });
    expect(wrapper.find('span').text()).toBe('test');
    expect(wrapper.find('[data-testid="case-points"]').text()).toBe(
      '50.00 PTS',
    );
    expect(wrapper.find('[variant="success"]').exists()).toBe(false);
  });

  it('It should only display name', async () => {
    const wrapper = shallowMount(Case, {
      store,
      localVue,
      propsData: {
        name: 'test',
        defined: false,
        noGroup: false,
        caseId: NIL,
        groupId: NIL,
        points: 50,
      },
    });
    expect(wrapper.find('span').text()).toBe('test');
    expect(wrapper.find('[data-testid="case-points"]').exists()).toBe(false);
  });

  it('It should display name and points. Points badge should have green color', async () => {
    const wrapper = shallowMount(Case, {
      store,
      localVue,
      propsData: {
        name: 'test',
        defined: true,
        noGroup: true,
        caseId: NIL,
        groupId: NIL,
        points: 50,
      },
    });
    expect(wrapper.find('span').text()).toBe('test');
    expect(wrapper.find('[variant="success"]').exists()).toBe(true);
  });
});
