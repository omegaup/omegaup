<template>
  <div class="col-sm-4 mt-2">
    <div class="border border-dark">
      <p>
        <strong>{{ this.name }}</strong> <br />
        {{ problemCount }} Problemas
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

@Component({
  directives: {
    tooltip: VTooltip,
  },
})
export default class CollectionProblem extends Vue {
  @Prop() levelTagAlias!: string;
  @Prop() problemCount!: number;

  get name(): string {
    return T[`${this.levelTagAlias}`];
  }
}
</script>
