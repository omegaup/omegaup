import { shallowMount, createLocalVue } from '@vue/test-utils';
import store from '@/js/omegaup/problem/creator/store';
import Group from './Group.vue';
import { NIL, v4 } from 'uuid';
import BootstrapVue, { BootstrapVueIcons } from 'bootstrap-vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(BootstrapVueIcons);

describe('Group.vue', () => {
  it('Should display "sin_grupo" and should not display points badge', async () => {
    const wrapper = shallowMount(Group, {
      store,
      localVue,
      propsData: {
        name: 'sin_grupo',
        defined: true,
        groupId: NIL,
        points: 50,
      },
    });
    expect(wrapper.find('span').text()).toBe('sin_grupo');
    expect(wrapper.find('[data-testid="group-points"]').exists()).toBe(false);
    expect(wrapper.find('[variant="success"]').exists()).toBe(false);
  });

  it('Should display "test" and 50.00 PTS', async () => {
    const wrapper = shallowMount(Group, {
      store,
      localVue,
      propsData: {
        name: 'test',
        defined: false,
        groupId: v4(),
        points: 50,
      },
    });
    expect(wrapper.find('span').text()).toBe('test');
    expect(wrapper.find('[data-testid="group-points"]').text()).toBe(
      '50.00 PTS',
    );
    expect(wrapper.find('[variant="success"]').exists()).toBe(false);
  });

  it('Should display "test", 50.00 PTS, and points badge should be green', async () => {
    const wrapper = shallowMount(Group, {
      store,
      localVue,
      propsData: {
        name: 'test',
        defined: true,
        groupId: v4(),
        points: 50,
      },
    });
    expect(wrapper.find('span').text()).toBe('test');
    expect(wrapper.find('[data-testid="group-points"]').text()).toBe(
      '50.00 PTS',
    );
    expect(wrapper.find('[variant="success"]').exists()).toBe(true);
  });
});
