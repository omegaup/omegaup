<template>
  <div class="col-sm-4 mt-2">
    <div class="border border-dark">
      <div class="row">
        <div class="col-sm-4 d-flex align-items-center justify-content-end">
          <h1>
            <slot name="icon"></slot>
          </h1>
        </div>
        <div class="col-sm-8 d-flex align-items-center" style="height: 112px">
          <p>
            <strong>{{ title }}</strong> <br />
            {{ problemCount }} <slot name="problems"></slot>
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col mt-1 mb-1 text-center">
          <a class="btn btn-primary" :href="href">{{
            T.problemcollectionViewProblems
          }}</a>
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
  @Prop() href!: string;
  @Prop() problemCount!: number;
  T = T;
}
</script>
