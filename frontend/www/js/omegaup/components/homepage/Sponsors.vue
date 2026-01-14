<template>
  <div class="sponsors-section py-5 px-4 text-center">
    <div class="background-decorations">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
    </div>
    <div class="container-lg position-relative">
      <h3 class="section-title mb-5">{{ title }}</h3>
      <div class="d-flex flex-wrap justify-content-center align-items-center gap-4">
        <div
          v-for="logo in logos"
          :key="logo.href"
          class="sponsor-card"
        >
          <a :href="logo.href" target="_blank" class="sponsor-link">
            <img :class="[logo.class, 'sponsor-logo']" :src="logo.src" :alt="logo.alt" />
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

@Component
export default class Sponsors extends Vue {
  @Prop() title!: string;
  @Prop() logos!: {
    src: string;
    alt: string;
    href: string;
    class: string;
  }[];
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.sponsors-section {
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
  border-radius: 3rem;
  margin: 5rem 0;
  position: relative;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.background-decorations {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  
  .blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.15;
  }
  
  .blob-1 {
    top: -10%;
    right: -5%;
    width: 300px;
    height: 300px;
    background: #4facfe;
  }
  
  .blob-2 {
    bottom: -15%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: #764ba2;
  }
}

.section-title {
  color: white;
  font-weight: 800;
  font-size: clamp(2rem, 4vw, 3rem);
  letter-spacing: -0.03em;
  text-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.sponsor-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  padding: 3rem;
  border-radius: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 300px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);

  &:hover {
    transform: translateY(-12px) scale(1.02);
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 
                0 0 20px rgba(79, 172, 254, 0.2);
    
    .sponsor-logo {
      opacity: 1;
      filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));
    }
  }
}

.sponsor-logo {
  max-width: 220px;
  width: 100%;
  height: auto;
  opacity: 0.9;
  filter: brightness(1.1);
  transition: all 0.5s ease;
}

.gap-4 {
  gap: 2.5rem;
}

@media (max-width: 768px) {
  .sponsors-section {
    border-radius: 1.5rem;
    padding: 3rem 1.5rem !important;
  }
  .sponsor-card {
    min-width: 100%;
    padding: 2.5rem;
  }
}
</style>
