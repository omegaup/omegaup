<template>
  <li class="dropdown">
    <a href="#" class="notification-btn dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
      <span class="glyphicon glyphicon-bell"></span>
      <span class="label label-danger count-label">100</span>
    </a>
    <ul class="dropdown-menu">
      <li>Acá van las notificaciones</li>
    </ul>
  </li>
</template>

<style>
.nav>li>a.notification-btn {
  padding: 8px 4px 0 0;
}

.glyphicon-bell {
  font-size: 20px;
  display: block;
  text-align: center;
}

.count-label {
  display: block;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component
export default class NotificationList extends Vue {
  T = T;
}
</script>
