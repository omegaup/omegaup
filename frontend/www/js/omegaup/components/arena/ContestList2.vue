<template>
  <div>
    <span>Contest list: {{ contests }}</span>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
@Component({
  components: {},
})
export default class ArenaContestList extends Vue {
  @Prop() contests!: omegaup.ArenaContests;
  @Prop() isLogged!: boolean;
  T = T;
}
</script>
