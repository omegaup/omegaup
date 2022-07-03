<template>
  <vue-cookie-accept-decline
    ref="myPanel1"
    :debug="false"
    :disable-decline="false"
    :show-postpone-button="false"
    element-id="myPanel1"
    position="bottom-left"
    transition-name="slideFromBottom"
    type="floating"
    @clicked-accept="$emit('cookie-clicked-accept')"
    @clicked-decline="$emit('cookie-clicked-decline')"
    @clicked-postpone="$emit('cookie-clicked-postpone')"
    @removed-cookie="cookieRemovedCookie"
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
