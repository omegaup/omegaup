<template>
  <div class="homepage">
    <OmegaupCarousel />
    
    <OmegaupRanksSection />

    <OmegaupQuote
      quoteText="Yo sí creo que omegaUp me ayudó a llegar aquí [Microsoft], porque fue la primera plataforma que usé, y por bastante tiempo, para aprender las bases; y eso realmente cambió el destino de mi vida y para dónde fue mi camino."
      authorName="Elsy Pinzón"
      authorPicture="https://www.w3schools.com/howto/img_avatar.png"
    />


    <OmegaupSections />
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import OmegaupCarousel from './Carousel.vue';
import OmegaupRanksSection from './RanksSection.vue';
import OmegaupQuote from './Quote.vue';
import OmegaupSections from './Sections.vue';

@Component({
  components: {
    OmegaupCarousel,
    OmegaupRanksSection,
    OmegaupQuote,
    OmegaupSections,
  },
})
export default class Homepage extends Vue {
}
</script>

<style lang="scss" scoped>
// $omegaup-bg: #e4e8ec linear-gradient(to top, #e4e8ec, #f3f6f9) fixed no-repeat;
.homepage {
  margin-bottom: 100px;
  // background: $omegaup-bg;
}
</style>