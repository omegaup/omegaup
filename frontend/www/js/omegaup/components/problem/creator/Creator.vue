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

<style lang="scss">
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}
</style>
