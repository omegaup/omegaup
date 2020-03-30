<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <!-- Facebook like button -->
      <iframe
        src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup&width=137&layout=button_count&action=like&size=small&show_faces=false&share=true&height=46&appId"
        height="46"
        style="border:none;overflow:hidden"
        scrolling="no"
        frameborder="0"
        allowTransparency="true"
        allow="encrypted-media"
      ></iframe>
      <br />
      <!-- Twitter follow -->
      <a
        href="https://twitter.com/omegaup?ref_src=twsrc%5Etfw"
        class="twitter-follow-button"
        data-width="300px"
        data-height="20"
        data-show-screen-name="false"
        data-dnt="true"
        data-show-count="true"
        >{{ T.followOmegaUp }}</a
      >
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class SocialMedia extends Vue {
  T = T;
}
</script>
