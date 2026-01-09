<template>
  <div class="open-source-banner px-5 py-4 text-white position-relative overflow-hidden">
    <div class="content-wrapper d-flex align-items-center justify-content-between h-80">
      <div class="banner-text py-3">
        <h2 class="banner-title mb-1">
          {{ T.homepageOpenSourceTitle }}
        </h2>
        <p class="banner-subtitle mb-4">
          {{ T.homepageOpenSourceSubtitle }}
        </p>
        <div class="cta-row d-flex align-items-center flex-wrap gap-4">
          <a href="https://github.com/omegaup/omegaup" 
             target="_blank" 
             class="btn btn-github px-4 py-2 font-weight-bold">
            {{ T.homepageGitHubRepository }}
          </a>
          <div class="contact-text ml-lg-4 mt-3 mt-lg-0">
            <span class="opacity-75">{{ T.homepageWriteToUs }}</span>
            <a href="mailto:hello@omegaup.com" class="text-white ml-1 font-weight-bold underline-hover">hello@omegaup.com</a>
          </div>
        </div>
      </div>
      
      <div class="octocat-container">
        <div class="tilted-card d-flex align-items-center justify-content-center">
          <img src="/media/homepage/github-octocat.png" 
               alt="GitHub Octocat" 
               class="octocat-img img-fluid" />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class OpenSourceBanner extends Vue {
  T = T;
}
</script>

<style lang="scss" scoped>
.open-source-banner {
  background: #5e5eff;
  border-radius: 1.5rem;
  min-height: 200px;
}

.banner-title {
  font-weight: 800;
  font-size: 1.75rem;
  letter-spacing: -0.02em;
  text-transform: uppercase;
}

.banner-subtitle {
  font-weight: 700;
  font-size: 1.25rem;
  text-transform: uppercase;
  opacity: 0.95;
}

.btn-github {
  background: white;
  color: black;
  border-radius: 0.5rem;
  text-transform: uppercase;
  font-size: 0.85rem;
  transition: all 0.2s ease;
  
  &:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
  }
}

.contact-text {
  font-size: 0.95rem;
  .underline-hover:hover {
    text-decoration: underline !important;
  }
}

.octocat-container {
  flex-shrink: 0;
  margin-right: -2rem;
  padding: 1rem;
}

.tilted-card {
  background: white;
  width: 200px;
  height: 240px;
  border-radius: 1.5rem;
  transform: rotate(5deg);
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  overflow: hidden;
  padding: 1.5rem;
  
  .octocat-img {
    height: 100%;
    object-fit: contain;
    transform: rotate(-5deg); // Straighten the cat slightly relative to card
  }
}

.gap-4 {
  gap: 1.5rem;
}

@media (max-width: 991px) {
  .open-source-banner {
    padding: 2rem !important;
  }
  .octocat-container {
    display: none; // Match typical compact banner behavior for mobile
  }
  .banner-title {
    font-size: 1.5rem;
  }
}
</style>
