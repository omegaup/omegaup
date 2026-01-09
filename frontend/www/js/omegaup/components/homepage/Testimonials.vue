<template>
  <div class="testimonials-container py-5 my-5">
    <div class="container-lg">
      <div class="row align-items-center">
        <!-- Left Column: Author Cards (Tabs) -->
        <div class="col-lg-5 mb-5 mb-lg-0">
          <div class="author-tabs pr-lg-4">
            <div
              v-for="(testimonial, index) in testimonials"
              :key="index"
              class="author-card p-4 mb-3 d-flex flex-column transition-all cursor-pointer"
              :class="{ active: activeIndex === index }"
              @click="activeIndex = index"
            >
              <span class="author-name font-weight-bold mb-1">{{ testimonial.author.name }}</span>
              <span class="author-title small opacity-75">{{ testimonial.author.title[T.locale] }}</span>
            </div>
          </div>
        </div>

        <!-- Right Column: Testimonial Content -->
        <div class="col-lg-7">
          <div class="testimonial-content pl-lg-4">
            <h2 class="display-4 font-weight-bold mb-4 text-indigo">Testimonial</h2>
            <transition name="fade" mode="out-in">
              <div :key="activeIndex" class="testimonial-box">
                <p class="lead font-italic mb-0">
                  {{ testimonials[activeIndex].text[T.locale] }}
                </p>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import testimonialsConfig from '../../testimonials.config';

@Component
export default class Testimonials extends Vue {
  T = T;
  testimonials = testimonialsConfig;
  activeIndex = 0;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.testimonials-container {
  background: white;
}

.author-card {
  background: #f8f9fa;
  border-radius: 1rem;
  border: 2px solid transparent;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  
  &.active {
    background: #6366f1;
    color: white;
    box-shadow: 0 10px 15px rgba(99, 102, 241, 0.2);
    transform: translateX(10px);
    
    .author-title {
      opacity: 0.9 !important;
    }
  }
  
  &:not(.active):hover {
    background: #f0f0f0;
    transform: translateX(5px);
  }
}

.text-indigo {
  color: #6366f1;
}

.testimonial-box {
  min-height: 200px;
  background: rgba(99, 102, 241, 0.03);
  padding: 2.5rem;
  border-left: 5px solid #6366f1;
  border-radius: 0.5rem 1.5rem 1.5rem 0.5rem;
  
  p.lead {
    font-size: 1.4rem;
    line-height: 1.6;
    color: #334155;
  }
}

.transition-all {
  transition: all 0.3s ease;
}

.cursor-pointer {
  cursor: pointer;
}

// Transitions
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

@media (max-width: 991px) {
  .author-card.active {
    transform: translateY(-5px);
  }
}
</style>
