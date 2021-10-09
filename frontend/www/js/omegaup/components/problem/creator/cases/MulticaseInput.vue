<template>
  <div class="mt-3">
    <b-row>
      <b-col>
        <b-form-group label="Prefijo" label-for="prefix" class="mb-4">
          <b-form-input id="prefix" v-model="prefix" autocomplete="off" />
        </b-form-group>
      </b-col>
      <b-col>
        <b-form-group label="Sufijo" label-for="suffix" class="mb-4">
          <b-form-input id="suffix" v-model="suffix" autocomplete="off" />
        </b-form-group>
      </b-col>
    </b-row>
    <b-form-group
      label="Número de casos"
      :description="'Tus casos tendran el nombre: ' + caseNamePreview"
      label-for="case-points"
    >
      <b-form-input
        type="number"
        number
        min="0"
        id="case-num"
        v-model="caseNum"
      />
    </b-form-group>
    <b-form-group label="Nombre del grupo" label-for="case-group">
      <b-form-select id="case-group" v-model="casesGroup" :options="options" />
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import { types } from '../../../../problem/creator/types';
import { NIL } from 'uuid';
import { namespace } from 'vuex-class';

const caseStore = namespace('casesStore');

@Component({})
export default class MulticaseInput extends Vue {
  @caseStore.Getter('getGroupIdsAndNames') options!: types.Option[];

  prefix = '';
  suffix = '';
  caseNum = 1;
  casesGroup = NIL;

  get caseNamePreview() {
    return `${this.prefix + '1' + this.suffix}, ${
      this.prefix + '2' + this.suffix
    }, ...`;
  }
}
</script>

<style lang="scss"></style>
