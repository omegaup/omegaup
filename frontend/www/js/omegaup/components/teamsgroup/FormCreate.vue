<template>
  <omegaup-teams-group-form
    :alias.sync="alias"
    :description.sync="description"
    :name.sync="name"
    :number-of-contestants.sync="numberOfContestants"
    :max-number-of-contestants="maxNumberOfContestants"
    @submit="(request) => $emit('create-teams-group', { ...request, alias })"
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
import { Vue, Component, Watch, Emit, Prop } from 'vue-property-decorator';
import T from '../../lang';
import latinize from 'latinize';

@Component({
  components: {
    'omegaup-teams-group-form': teamsgroup_FormBase,
  },
})
export default class TeamsGroupFormCreate extends Vue {
  @Prop({ default: 10 }) maxNumberOfContestants!: number;

  T = T;
  alias: null | string = null;
  description: null | string = null;
  name: null | string = null;
  numberOfContestants: number = 3;

  generateAlias(name: string): string {
    // Remove accents
    return (
      latinize(name)
        // Replace whitespace
        .replace(/\s+/g, '-')
        // Remove invalid characters
        .replace(/[^a-zA-Z0-9_-]/g, '')
        .substring(0, 32)
    );
  }

  @Watch('alias')
  @Emit('validate-unused-alias')
  onAliasChanged(newValue: string): string {
    return newValue;
  }

  @Watch('name')
  onNameChanged(newValue: string): void {
    this.alias = this.generateAlias(newValue);
  }
}
</script>
