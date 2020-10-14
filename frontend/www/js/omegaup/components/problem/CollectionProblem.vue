<template>
  <div class="card mb-3" omegaup-collection-problem>
    <div class="row no-gutters">
      <div class="col-md-4">
        <div class="card-body">
          <h1>
            <slot name="icon"></slot>
          </h1>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card-body">
          <h6 class="card-title">
            {{ title }}
          </h6>
          <slot name="problem-count"></slot>
          <slot name="collection-button"></slot>
        </div>
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
  width: 290px;
  margin-right: 0.5em;
}
</style>
