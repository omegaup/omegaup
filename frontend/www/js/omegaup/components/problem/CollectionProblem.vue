<template>
  <div class="card text-center mx-2 my-2 bg-light" omegaup-collection-problem>
    <div class="card-body d-flex flex-column align-items-center p-2">
      <div class="icon-container mb-1">
        <h1 class="mb-0">
          <slot name="icon"></slot>
        </h1>
      </div>
      <h6 class="card-title mb-1 mx-auto small">
        {{ title }}
      </h6>
      <slot name="problem-count"></slot>
      <div class="mt-1">
        <slot name="button"></slot>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  directives: {
    tooltip: VTooltip,
  },
})
export default class CollectionProblem extends Vue {
  @Prop() title!: string;
  T = T;
}
</script>

<style>
[omegaup-collection-problem] {
  width: 12.5rem;
  margin-right: 0.25em;
  height: 100%;
}

[omegaup-collection-problem] .card-body {
  min-height: 9.375rem;
}

[omegaup-collection-problem] .icon-container {
  font-size: 0.9em;
}

[omegaup-collection-problem] .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
}

[omegaup-collection-problem] .card-title {
  font-size: 0.85rem;
  line-height: 1.2;
}

@media (max-width: 768px) {
  [omegaup-collection-problem] {
    width: 11.25rem;
  }

  [omegaup-collection-problem] .card-body {
    min-height: 8.75rem;
  }
}
</style>
