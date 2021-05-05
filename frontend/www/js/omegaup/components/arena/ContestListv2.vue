<template>
  <div>
    <span>Contest list: {{ contests }}</span>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
@Component({
  components: {},
})
export default class ArenaContestList extends Vue {
  @Prop() contests!: types.ContestListItem[];
  T = T;
}
</script>
