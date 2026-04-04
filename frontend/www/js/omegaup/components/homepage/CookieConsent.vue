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
    <template #message>
      {{ T.modalConsentCookiesDescription }}
    </template>
    <template #declineContent>{{ T.modalConsentCookiesDecline }}</template>
    <template #acceptContent>{{ T.modalConsentCookiesAccept }}</template>
  </vue-cookie-accept-decline>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import VueCookieAcceptDecline from 'vue-cookie-accept-decline';
import 'vue-cookie-accept-decline/dist/vue-cookie-accept-decline.css';
import T from '../../lang';
@Component({
  components: {
    'vue-cookie-accept-decline': VueCookieAcceptDecline,
  },
})
export default class CookieConsent extends Vue {
  T = T;
  cookieRemovedCookie = false;
}
</script>

<style lang="scss">
.cookie {
  // Our translation strings need a lot more horizontal space.
  &__floating {
    @media (min-width: 768px) {
      max-width: 600px;
    }
    &__buttons {
      &__button {
        white-space: break-spaces;
        width: 50% !important;
      }
    }
  }
}
</style>
