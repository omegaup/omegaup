<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-center align-items-center">
      <div class="row w-100">
        <!-- Left Side: Testimonial List -->
        <div class="col-md-6">
          <div class="p-3">
            <ul class="testimonial-list">
              <li v-for="(testimonial, index) in testimonials" :key="index">
                <div
                  class="testimonial-card p-3"
                  :class="{ active: activeIndex === index }"
                  @click="toggleTestimonial(index)"
                >
                  <div class="d-flex flex-row align-items-center">
                    <div class="d-flex flex-column ml-2">
                      <span class="font-weight-normal">{{
                        testimonial.author.name
                      }}</span>
                      <span>{{ testimonial.author.title[T.locale] }}</span>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Right Side: Testimonial Details -->
        <div class="col-md-6">
          <div class="p-3 testimonials-margin">
            <div
              v-for="(testimonial, index) in testimonials"
              v-show="activeIndex === index"
              :key="'details-' + index"
              class="card-body"
            >
              <h4>Testimonial</h4>
              <p>{{ testimonial.text[T.locale] }}</p>
            </div>
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

  testimonials = testimonialsConfig.map((testimonial) => ({
    text: testimonial.text,
    author: {
      ...testimonial.author,
    },
  }));

  activeIndex = 0;

  toggleTestimonial(index: number) {
    if (this.activeIndex !== index) {
      this.activeIndex = index;
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.testimonial-card {
  cursor: pointer;
  border-radius: 5px;
  box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
  transition: background-color 0.2s ease;
}

.testimonial-card:hover,
.testimonial-card.active {
  background-color: #eee;
}

.testimonial-list {
  list-style: none;
  padding-left: 0;
  margin-bottom: 0;
}

.testimonial-list li {
  margin-bottom: 20px;
}

.testimonials-margin {
  margin-top: -19px;
}
</style>
