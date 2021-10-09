<template>
  <b-row class="mb-3">
    <b-col class="d-flex align-items-center">
      <span class="mr-2">Nombre: </span>
      <b-form-input v-model="name" size="sm" placeholder="Nuevo Problema" />
    </b-col>
    <b-col class="d-flex justify-content-end">
      <b-button class="mr-2" variant="light" size="sm">
        <BIconUpload class="mr-1" />
        <span class="d-none d-md-inline"> Cargar Problema </span>
      </b-button>
      <b-button class="mr-2" variant="primary" size="sm">
        <BIconDownload class="mr-1" />
        <span class="d-none d-md-inline"> Generar Problema </span>
      </b-button>
      <b-button variant="warning" size="sm">
        <BIconPlus class="mr-1" />
        <span class="d-none d-md-inline"> Nuevo Problema </span>
      </b-button>
    </b-col>
  </b-row>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';

@Component({})
export default class Header extends Vue {}
</script>
