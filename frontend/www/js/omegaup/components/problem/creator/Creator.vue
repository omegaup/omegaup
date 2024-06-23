<template>
  <b-container fluid="lg">
    <creator-header />
    <creator-tabs
      @show-update-success-message="() => $emit('show-update-success-message')"
    />
  </b-container>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import creator_Header from './Header.vue';
import creator_Tabs from './Tabs.vue';
@Component({
  components: {
    'creator-header': creator_Header,
    'creator-tabs': creator_Tabs,
  },
})
export default class Creator extends Vue {}
</script>
