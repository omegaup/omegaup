<template>
  <vue-cookie-accept-decline
    :debug="false"
    :disableDecline="false"
    :showPostponeButton="false"
    @clicked-accept="$emit('cookie-clicked-accept')"
    @clicked-decline="$emit('cookie-clicked-decline')"
    @clicked-postpone="$emit('cookie-clicked-postpone')"
    @removed-cookie="cookieRemovedCookie"
    elementId="myPanel1"
    position="bottom-left"
    ref="myPanel1"
    transitionName="slideFromBottom"
    type="floating"
  >
    <!-- Optional -->
    <template #postponeContent>&times;</template>
    <!-- Optional -->
    <template #message>
      We use cookies to ensure you get the best experience on our website.
    </template>
    <!-- Optional -->
    <template #declineContent>Opt Out</template>
    <!-- Optional -->
    <template #acceptContent>Got It!</template>
  </vue-cookie-accept-decline>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import VueCookieAcceptDecline from 'vue-cookie-accept-decline';
import 'vue-cookie-accept-decline/dist/vue-cookie-accept-decline.css';
@Component({
  components: {
    'vue-cookie-accept-decline': VueCookieAcceptDecline,
  },
})
export default class Cookie extends Vue {
  cookieRemovedCookie = false;
}
</script>
