<template>
  <div class="quote">
    <div class="content">
      <p class="quote-text">"{{ this.quoteText }}"</p>
      <div class="quote-author">
        <Avatar :imageSrc="this.authorPicture" />
        <span class="author">~ {{ this.authorName }}</span>
      </div>
    </div>

  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import Avatar from './Avatar.vue';

@Component({
  components: {
    Avatar,
  },
})
export default class Quote extends Vue {
  @Prop() private quoteText!: string;
  @Prop() private authorName!: string;
  @Prop() private authorPicture!: string;
}
</script>

<style lang="scss" scoped>
$omegaup-white: white;
$omegaup-primary--darker: #466AAF;
$omegaup-primary--accent: #0275D8;
$omegaup-unit-size: 8px;
$omegaup-unit-1: 8px;
$omegaup-unit-2: 16px;
$omegaup-unit-3: 24px;
$omegaup-unit-4: 32px;
$omegaup-unit-5: 40px;
$omegaup-unit-6: 48px;
$omegaup-unit-7: 56px;
$omegaup-unit-8: 64px;
$omegaup-unit-9: 72px;
$omegaup-unit-10: 80px;

.quote {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;
  min-height: 60px;
  padding: 16px 0;

  .content {
    display: flex;
    width: 80%;
    margin: 0 auto;

    .quote-text {
      font-style: italic;
      margin-right: 40px;
      width: 80%;
    }
  }
}

</style>