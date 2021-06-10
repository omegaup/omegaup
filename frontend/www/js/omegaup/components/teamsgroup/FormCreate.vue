<template>
  <omegaup-teams-group-form
    :teams-group-alias.sync="alias"
    :teams-group-description.sync="description"
    :teams-group-name.sync="name"
    @submit="
      (request) => $emit('create-teams-group', { ...request, ...{ alias } })
    "
  >
    <template #teams-group-title>
      <div class="card-header">
        <h3 class="card-title">
          {{ T.omegaupTitleTeamsGroupNew }}
        </h3>
      </div>
    </template>
  </omegaup-teams-group-form>
</template>

<script lang="ts">
import teamsgroup_FormBase from './FormBase.vue';
import { Vue, Component, Watch } from 'vue-property-decorator';
import T from '../../lang';
import latinize from 'latinize';

@Component({
  components: {
    'omegaup-teams-group-form': teamsgroup_FormBase,
  },
})
export default class TeamsGroupFormCreate extends Vue {
  T = T;
  alias: null | string = null;
  description: null | string = null;
  name: null | string = null;

  generateAlias(name: string): string {
    // Remove accents
    let generatedAlias = latinize(name);
    // Replace whitespace
    generatedAlias = generatedAlias.replace(/\s+/g, '-');
    // Remove invalid characters
    generatedAlias = generatedAlias.replace(/[^a-zA-Z0-9_-]/g, '');
    generatedAlias = generatedAlias.substring(0, 32);
    return generatedAlias;
  }

  @Watch('alias')
  onAliasChanged(newValue: string): void {
    this.$emit('validate-unused-alias', newValue);
  }

  @Watch('name')
  onNameChanged(newValue: string): void {
    this.alias = this.generateAlias(newValue);
  }
}
</script>
