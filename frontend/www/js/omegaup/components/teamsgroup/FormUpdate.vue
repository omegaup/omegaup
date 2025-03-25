<template>
  <omegaup-teams-group-form
    :alias="alias"
    :description.sync="currentDescription"
    :name.sync="currentName"
    :number-of-contestants.sync="currentNumberOfContestants"
    :max-number-of-contestants="maxNumberOfContestants"
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
  @Prop({ default: null }) alias!: null | string;
  @Prop({ default: null }) description!: null | string;
  @Prop({ default: null }) name!: null | string;
  @Prop({ default: 3 }) numberOfContestants!: number;
  @Prop({ default: 10 }) maxNumberOfContestants!: number;

  T = T;
  currentDescription: null | string = this.description;
  currentName: null | string = this.name;
  currentNumberOfContestants: number = this.numberOfContestants;
}
</script>
