<template>
  <div class="row p-4 text-center justify-content-around align-items-center">
    <h3 class="col-12 mb-3 display-4">{{ title }}</h3>
    <div
      v-for="logo in logos"
      :key="logo.href"
      class="p-3 mt-2 mt-md-0 sponsor-logo-container"
    >
      <a :href="logo.href" target="_blank">
        <img
          :class="logo.class"
          :src="logo.src"
          :alt="logo.alt"
          width="280"
          height="120"
        />
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';

export default defineComponent({
  name: 'HomepageSponsors',
  props: {
    title: {
      type: String,
      required: true,
    },
    logos: {
      type: Array as PropType<
        {
          src: string;
          alt: string;
          href: string;
          class: string;
        }[]
      >,
      required: true,
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

h3.display-4 {
  color: $omegaup-primary--darker;
  font-weight: normal;
  font-size: 1.88rem;
  margin-top: 1.88em;
}
img.img-fluid {
  max-width: 280px;
  width: 280px;
  margin: 0 auto;
  display: block;
}
.sponsor-logo-container {
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
