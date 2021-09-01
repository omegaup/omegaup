<template>
<div>
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link" :class="{ active: showTab === 'studying' }" @click="showTab = 'studying'" data-toggle="tab" role="tab">Home</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" :class="{ active: showTab === 'studying' }" @click="showTab = 'studying'" data-toggle="tab" role="tab">Home</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" :class="{ active: showTab === 'studying' }" @click="showTab = 'studying'" data-toggle="tab" role="tab">Home</a>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane" role="tabpanel" :class="{ active: showTab === 'studying' }">Jxjxjx</div>
  </div>
</div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class CourseTabs extends Vue {

  tabs = {
    enrolled: {
      id: 'enrolled',
      name: T.courseTabEnrolled,
    },
    general: {
      id: 'general',
      name: T.courseTabGeneral,
    },
    past: {
      id: 'past',
      name: T.courseTabPast,
    }
  };
  selectedTab = this.tabs.enrolled.id;
}
</script>