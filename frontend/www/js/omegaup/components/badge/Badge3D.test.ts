import { shallowMount } from '@vue/test-utils';
import badge_Badge3D from './Badge3D.vue';

// Rotation produced by a pointer at the bottom-right corner of the stubbed
// 100x100 bounds, with the component's default tilt strength of 30.
const TILTED_TRANSFORM =
  'rotateX(-15deg) rotateY(15deg) scale3d(1.06, 1.06, 1.06)';

// jsdom reports zero-sized rects, so the bounds are stubbed to keep the
// expected rotation deterministic.
function stubBounds(element: Element): void {
  element.getBoundingClientRect = () =>
    ({ left: 0, top: 0, width: 100, height: 100 } as DOMRect);
}

describe('Badge3D.vue', () => {
  it('Should render the slot content on every badge layer', () => {
    const wrapper = shallowMount(badge_Badge3D, {
      slots: {
        default: '<img class="badge-slot-content" />',
      },
    });

    expect(wrapper.findAll('.badge-layer').length).toBe(4);
    expect(wrapper.findAll('.badge-slot-content').length).toBe(4);
  });

  it('Should tilt the badge while the pointer moves over it', async () => {
    const wrapper = shallowMount(badge_Badge3D);
    const container = wrapper.find('.badge-3d-wrapper');
    const badgeElement = wrapper.vm.$refs.badge3d as HTMLElement;
    stubBounds(container.element);

    await container.trigger('mouseenter');
    await container.trigger('mousemove', { clientX: 100, clientY: 100 });

    expect(badgeElement).toHaveStyle({ transform: TILTED_TRANSFORM });
  });

  it('Should reset the tilt when the pointer leaves', async () => {
    const wrapper = shallowMount(badge_Badge3D);
    const container = wrapper.find('.badge-3d-wrapper');
    const badgeElement = wrapper.vm.$refs.badge3d as HTMLElement;
    stubBounds(container.element);

    await container.trigger('mouseenter');
    await container.trigger('mousemove', { clientX: 100, clientY: 100 });
    expect(badgeElement).toHaveStyle({ transform: TILTED_TRANSFORM });

    await container.trigger('mouseleave');
    expect(badgeElement).not.toHaveStyle({ transform: TILTED_TRANSFORM });
  });

  it('Should ignore pointer movement before the bounds are measured', async () => {
    const wrapper = shallowMount(badge_Badge3D);
    const container = wrapper.find('.badge-3d-wrapper');
    const badgeElement = wrapper.vm.$refs.badge3d as HTMLElement;
    stubBounds(container.element);

    await container.trigger('mousemove', { clientX: 100, clientY: 100 });

    expect(badgeElement).not.toHaveStyle({ transform: TILTED_TRANSFORM });
  });
});
