<template>
  <div class="mt-3">
    <b-form-group
      description="En minúsculas y sin espacios"
      label="Nombre del caso"
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
    <b-form-group label="Nombre del grupo" label-for="case-group">
      <b-form-select id="case-group" v-model="caseGroup" :options="options" />
    </b-form-group>

    <b-form-group
      label="Puntaje"
      :description="
        autoPoints ? 'El programa calculará automáticamente el puntaje' : ''
      "
      label-for="case-points"
    >
      <b-form-input
        :disabled="autoPoints"
        type="number"
        number
        min="0"
        max="100"
        id="case-points"
        v-model="casePoints"
      />
    </b-form-group>
    <b-form-checkbox v-model="autoPoints" name="auto-points">
      Puntaje Automático</b-form-checkbox
    >
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';
import { NIL } from 'uuid';
import { namespace } from 'vuex-class';
import { types } from '../../../../problem/creator/types';

const caseStore = namespace('casesStore');

@Component({})
export default class CaseInput extends Vue {
  @caseStore.Getter('getGroupIdsAndNames') options!: types.Option[];

  @Prop({ default: '' }) readonly name!: string;
  @Prop({ default: NIL }) readonly group!: string;
  @Prop({ default: '' }) readonly points!: number | '';
  @Prop({ default: false }) readonly defined!: boolean;

  caseName = '';
  caseGroup = NIL;
  casePoints: number | '' = ''; // Inde
  autoPoints = true;

  mounted() {
    this.caseGroup = this.group;
    this.caseName = this.name;
    if (typeof this.points === 'number') {
      this.casePoints = parseFloat(this.points.toFixed(2));
    } else {
      this.casePoints = '';
    }
    this.autoPoints = !this.defined;
  }
  // options = [{ value: NIL, text: "sin_grupo" }];
}
</script>

<style lang="scss"></style>
