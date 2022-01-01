<template>
  <div class="mt-3">
    <b-form-group
      :description="T.problemCreatorCaseGroupNameHelper"
      :label="T.problemCreatorCaseName"
      label-for="case-name"
      class="mb-4"
    >
      <b-form-input
        v-model="caseName"
        :formatter="formatter"
        required
        autocomplete="off"
      />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select v-model="caseGroup" :options="options" />
    </b-form-group>

    <b-form-group
      v-show="casePoints !== null"
      :label="T.problemCreatorPoints"
      label-for="case-points"
    >
      <b-form-input
        v-model="casePoints"
        :formatter="pointsFormatter"
        type="number"
        number
        min="0"
        max="100"
      />
    </b-form-group>
    <b-form-group
      :label="T.problemCreatorAutomaticPoints"
      :description="T.problemCreatorAutomaticPointsHelper"
    >
      <b-form-checkbox
        :checked="casePoints === null"
        name="auto-points"
        @change="casePoints = casePoints === null ? 0 : null"
      >
      </b-form-checkbox>
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { NIL } from 'uuid';
import { Component, Vue, Prop } from 'vue-property-decorator';
import T from '../../../../lang';

@Component
export default class CaseInput extends Vue {
  @Prop({ default: '' }) name!: string;
  @Prop({ default: NIL }) group!: string;
  @Prop({ default: null }) points!: number | null;

  caseName = this.name;
  caseGroup = this.group;
  casePoints: number | null = this.points;

  T = T;

  formatter(text: string) {
    return text.toLowerCase().replace(/[^a-zA-Z0-9_-]/g, '');
  }

  pointsFormatter(points: number | null) {
    if (points === null) {
      return null;
    }
    return Math.max(points, 0);
  }
}
</script>
