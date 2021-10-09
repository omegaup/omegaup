<template>
  <div class="mt-3">
    <b-form-group
      description="En minúsculas y sin espacios"
      label="Nombre del grupo"
      label-for="group-name"
      class="mb-4"
    >
      <b-form-input
        id="group-name"
        v-model="groupName"
        required
        autocomplete="off"
      />
    </b-form-group>
    <b-form-group
      label="Puntaje"
      :description="
        autoPoints ? 'El programa calculará automáticamente el puntaje' : ''
      "
      label-for="group-points"
    >
      <b-form-input
        :disabled="autoPoints"
        type="number"
        number
        min="0"
        max="100"
        id="case-points"
        v-model="groupPoints"
      />
    </b-form-group>
    <b-form-checkbox v-model="autoPoints" name="auto-points">
      Puntaje Automático</b-form-checkbox
    >
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue } from 'vue-property-decorator';

@Component({})
export default class GroupInput extends Vue {
  @Prop({ default: '' }) readonly name!: string;
  @Prop({ default: '' }) readonly points!: number | '';
  @Prop({ default: false }) readonly defined!: boolean;

  groupName = '';
  groupPoints: number | '' = '';
  autoPoints = true;

  mounted() {
    this.groupName = this.name;
    if (typeof this.points === 'number') {
      this.groupPoints = parseFloat(this.points.toFixed(2));
    } else {
      this.groupPoints = '';
    }
    this.autoPoints = !this.defined;
  }
}
</script>

<style lang="scss"></style>
