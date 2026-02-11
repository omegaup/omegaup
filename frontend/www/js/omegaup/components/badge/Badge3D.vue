<template>
  <div
    class="badge-3d-wrapper"
    @mouseenter="onMouseEnter"
    @mousemove="onMouseMove"
    @mouseleave="onMouseLeave"
  >
    <div ref="badge3d" class="badge-3d">
      <div class="badge-layer rim">
        <slot></slot>
      </div>
      <div class="badge-layer edge-2">
        <slot></slot>
      </div>
      <div class="badge-layer edge-1">
        <slot></slot>
      </div>
      <div class="badge-layer front">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
export default class Badge3D extends Vue {
  private bounds: DOMRect | null = null;
  private tiltStrength = 30;

  onMouseEnter(event: MouseEvent): void {
    const target = event.currentTarget as HTMLElement;
    this.bounds = target.getBoundingClientRect();
  }

  onMouseMove(event: MouseEvent): void {
    if (!this.bounds) return;
    const x = event.clientX - this.bounds.left;
    const y = event.clientY - this.bounds.top;
    const cx = x - this.bounds.width / 2;
    const cy = y - this.bounds.height / 2;
    const badgeEl = this.$refs.badge3d as HTMLElement;
    if (badgeEl) {
      badgeEl.style.transform = `rotateX(${
        (-cy / this.bounds.height) * this.tiltStrength
      }deg) rotateY(${
        (cx / this.bounds.width) * this.tiltStrength
      }deg) scale3d(1.06, 1.06, 1.06)`;
    }
  }

  onMouseLeave(): void {
    const badgeEl = this.$refs.badge3d as HTMLElement;
    if (badgeEl) {
      badgeEl.style.transform = '';
    }
    this.bounds = null;
  }

  beforeDestroy(): void {
    this.onMouseLeave();
  }
}
</script>

<style lang="scss" scoped>
.badge-3d-wrapper {
  perspective: 1600px;
  display: inline-block;
  width: 100%;
  height: 100%;
}

.badge-3d {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transition: transform 0.12s ease-out;
}

.badge-layer {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  transform-style: preserve-3d;
  pointer-events: none;

  >>> * {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
}

.rim {
  transform: translateZ(6px);
  filter: drop-shadow(1px 0 0 var(--status-secondary-color))
    drop-shadow(-1px 0 0 var(--status-secondary-color))
    drop-shadow(0 1px 0 var(--status-secondary-color))
    drop-shadow(0 -1px 0 var(--status-secondary-color));
  opacity: 0.9;
}

.edge-2 {
  transform: translateZ(18px);
}

.edge-1 {
  transform: translateZ(30px);
}

.front {
  transform: translateZ(42px);
}

@media (prefers-reduced-motion: reduce) {
  .badge-3d {
    transform: none !important;
  }
}

@media (hover: none) and (pointer: coarse) {
  .badge-3d {
    transform: none;
  }

  .badge-layer.rim,
  .badge-layer.edge-1,
  .badge-layer.edge-2 {
    display: none;
  }
}
</style>
