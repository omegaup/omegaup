<template>
  <omegaup-teams-group-form
    :teams-group-alias="teamsGroupAlias"
    :teams-group-description.sync="description"
    :teams-group-name.sync="name"
    @submit="(request) => $emit('update-teams-group', request)"
  >
    <template #teams-group-submit-button>
      {{ T.teamsGroupFormUpdate }}
    </template>
  </omegaup-teams-group-form>
</template>

<script lang="ts">
import teamsgroup_FormBase from './FormBase.vue';
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component({
  components: {
    'omegaup-teams-group-form': teamsgroup_FormBase,
  },
})
export default class TeamsGroupForm extends Vue {
  @Prop({ default: null }) teamsGroupAlias!: null | string;
  @Prop({ default: null }) teamsGroupDescription!: null | string;
  @Prop({ default: null }) teamsGroupName!: null | string;

  T = T;
  description: null | string = this.teamsGroupDescription;
  name: null | string = this.teamsGroupName;
}
</script>
