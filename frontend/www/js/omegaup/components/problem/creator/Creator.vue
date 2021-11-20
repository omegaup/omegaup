<template>
  <b-container fluid="lg">
    <Header />
    <Tabs />
  </b-container>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Header from './Header.vue';
import Tabs from './Tabs.vue';
@Component({
  components: {
    Header,
    Tabs,
  },
})
export default class Creator extends Vue {}
</script>
