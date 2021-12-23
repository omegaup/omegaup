<template>
  <div class="mt-3">
    <b-form-group
      :description="T.problemCreatorCaseGroupNameHelper"
      :label="T.problemCreatorCaseName"
      label-for="case-name"
      class="mb-4"
    >
      <b-form-input
        id="case-name"
        v-model="caseName"
        required
        autocomplete="off"
      />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select id="case-group" v-model="caseGroup" :options="options" />
    </b-form-group>

    <b-form-group
      :label="T.problemCreatorPoints"
      :description="casePointsDefined ? 'T.problemCreatorAutoPointsHelper' : ''"
      label-for="case-points"
    >
      <b-form-input
        :disabled="casePointsDefined"
        type="number"
        number
        min="0"
        max="100"
        v-model="casePoints"
      />
    </b-form-group>
    <b-form-checkbox v-model="casePointsDefined" name="auto-points">{{
      T.problemCreatorAutoPoints
    }}</b-form-checkbox>
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
  @Prop({ default: '' }) points!: number | '';
  @Prop({ default: false }) pointsDefined!: boolean;

  caseName = '';
  caseGroup = '';
  casePoints: '' | number = '';
  casePointsDefined = false;

  T = T;

  mounted() {
    this.caseName = this.name;
    this.caseGroup = this.group;
    this.casePoints = this.points;
    this.casePointsDefined = this.pointsDefined;
  }
}
</script>
