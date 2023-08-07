<template>
  <div class="card align-items-center mx-2 my-2" omegaup-collection-problem>
    <div class="row no-gutters align-items-center justify-content-center">
      <div class="col-md-3">
        <div class="card-body text-center">
          <h1>
            <slot name="icon"></slot>
          </h1>
        </div>
      </div>
      <div class="col-md-9">
        <div class="card-body">
          <h6 class="card-title">
            {{ title }}
          </h6>
          <slot name="problem-count"></slot>
          <slot name="button"></slot>
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
  width: 310px;
  margin-right: 0.5em;
}
</style>
