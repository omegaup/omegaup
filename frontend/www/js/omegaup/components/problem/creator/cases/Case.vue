<template>
  <div :class="noGroup ? 'w-100' : undefined">
    <b-button
      :variant="selected.caseId === caseId ? 'light' : 'link'"
      :pressed="selected.caseId === caseId"
      :class="[
        { 'd-flex align-items-center justify-content-between': noGroup },
        'w-100 mx-1 text-decoration-none text-dark',
      ]"
      @click="setSelected({ caseId, groupId })"
    >
      <span>{{ name }}</span>
      <div v-if="noGroup">
        <b-badge
          size="sm"
          :variant="defined ? 'success' : 'primary'"
          class="ml-2"
          >{{ points.toFixed(2) }} PTS
        </b-badge>
      </div>
    </b-button>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import { types } from '../../../../problem/creator/types';

const caseStore = namespace('casesStore');

@Component({})
export default class Case extends Vue {
  @caseStore.State('selected') selected!: types.CaseGroupID;
  @caseStore.Mutation('setSelected') setSelected!: (
    caseGroupId: types.CaseGroupID,
  ) => void;

  @Prop() readonly name!: string;
  @Prop() readonly defined!: boolean;
  @Prop() readonly points!: number;
  @Prop() readonly noGroup!: boolean;
  @Prop() readonly caseId!: string;
  @Prop() readonly groupId!: string;
}
</script>
