<template>
  <div
    class="skeleton"
    :class="{ 'skeleton-circle': circle }"
    :style="{ width: width, height: height, borderRadius: borderRadius }"
  ></div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Skeleton extends Vue {
  @Prop({ default: '100%' }) width!: string;
  @Prop({ default: '1rem' }) height!: string;
  @Prop({ default: false }) circle!: boolean;
  @Prop({ default: null }) borderRadius!: string;
}
</script>

<style lang="scss" scoped>
.skeleton {
  background-color: #e2e8f0; // muted-gray (matches shadcn/ui generic feel)
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  border-radius: 0.25rem;
}

.skeleton-circle {
  border-radius: 50%;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

// Dark mode support if applicable in the future
@media (prefers-color-scheme: dark) {
  .skeleton {
    background-color: #1e293b;
  }
}
</style>
