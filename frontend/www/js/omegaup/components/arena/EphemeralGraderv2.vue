<template>
  <h1>This is a test</h1>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import vgl from 'vue-golden-layout';
import 'golden-layout/src/css/goldenlayout-light-theme.css';
Vue.use(vgl);

@Component
export default class EphemeralGrader extends Vue {
}
</script>
