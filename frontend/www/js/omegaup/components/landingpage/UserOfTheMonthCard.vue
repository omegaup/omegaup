<template>
  <div class="coder-of-the-month">
    <h3 class="rank-title">Coder del Mes</h3>
    <div class="card-content">
      <UIAvatar imageSrc="https://www.w3schools.com/howto/img_avatar.png" />
      <div class="profile-info">
        <h3 class="username">Jonathan Peraza Feliciano</h3>
        <span>@yonii_hdc</span>

        <hr class="separator" />
        <span>Universidad del Caribe</span>
        <span>Quintana Roo, México</span>
      </div>
      <UIButton label="Ver listado completo" />
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import OmegaupAvatar from './Avatar.vue';
import OmegaupButton from './Button.vue';

@Component({
  components: {
    UIAvatar: OmegaupAvatar,
    UIButton: OmegaupButton,
  },
})
export default class CoderOfTheMonth extends Vue {
}
</script>

<style lang="scss" scoped>
$omegaup-primary--darker: #466AAF;
$omegaup-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);

.coder-of-the-month {
  .card-content {
    border-radius: 5px;
    box-shadow: $omegaup-shadow;
    height: 100%;
    padding: 24px;
    background-color: white;

    width: 80%;
    margin: 0 auto;
    margin-top: 40px !important;

    .avatar {
      margin: 0 auto;
      margin-top: -60px;
    }

    .profile-info {
      display: flex;
      flex-direction: column;

      .username {
        color: $omegaup-primary--darker;
        margin-bottom: 0;
      }

      .separator {
        width: 80%;
        border-top: 1px solid gray;
      }
    }

    .btn {
      margin: 0 auto;
      margin-top: 20px;
      bottom: 0;
    }
  }
}
</style>