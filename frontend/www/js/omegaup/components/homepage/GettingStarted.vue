<template>
  <div class="container-lg py-5 mt-5">
    <h2 class="text-center mb-5 font-weight-bold">
      {{ T.homepageGettingStartedTitle }}
    </h2>
    <div class="row align-items-stretch">
      <!-- Compete Card -->
      <div class="col-xs-12 col-md-6 col-lg-3 mb-4">
        <div class="card h-100 shadow-sm border-0 border-radius-lg hover-up transition-all card-compete">
          <div class="card-body text-center d-flex flex-column p-4">
            <div class="icon-wrapper mb-3">
              <img src="/media/homepage/contests_section.svg" alt="Compete" class="img-fluid" />
            </div>
            <h4 class="card-title font-weight-bold mb-3">{{ T.homepageCompeteSectionTitle }}</h4>
            <p class="card-text text-muted mb-4 small flex-grow-1">
              {{ T.homepageCompeteSectionDescription }}
            </p>
            <a href="/arena/" class="btn btn-outline-primary btn-pill btn-block-md">
              {{ T.buttonGoToContests }}
            </a>
          </div>
        </div>
      </div>

      <!-- Train Card -->
      <div class="col-xs-12 col-md-6 col-lg-3 mb-4">
        <div class="card h-100 shadow-sm border-0 border-radius-lg hover-up transition-all card-train">
          <div class="card-body text-center d-flex flex-column p-4 text-white">
            <div class="icon-wrapper mb-3">
              <img src="/media/homepage/problems_section.svg" alt="Train" class="img-fluid" />
            </div>
            <h4 class="card-title font-weight-bold mb-3">{{ T.homepageTrainSectionTitle }}</h4>
            <p class="card-text mb-4 small flex-grow-1 opacity-90">
              {{ T.homepageTrainSectionDescription }}
            </p>
            <a href="/problem/" class="btn btn-light btn-pill btn-block-md font-weight-bold">
              {{ T.buttonGoToProblems }}
            </a>
          </div>
        </div>
      </div>

      <!-- Create Card -->
      <div v-if="!isUnder13User" class="col-xs-12 col-md-6 col-lg-3 mb-4">
        <div class="card h-100 shadow-sm border-0 border-radius-lg hover-up transition-all card-create">
          <div class="card-body text-center d-flex flex-column p-4">
            <div class="icon-wrapper mb-3">
              <img src="/media/homepage/create_section.svg" alt="Create" class="img-fluid" />
            </div>
            <h4 class="card-title font-weight-bold mb-3">{{ T.homepageCreateSectionTitle }}</h4>
            <p class="card-text text-muted mb-4 small flex-grow-1">
              {{ T.homepageCreateSectionDescription }}
            </p>
            <div class="d-flex flex-column gap-2">
              <a href="/problem/new/" class="btn btn-outline-success btn-pill btn-sm mb-2">
                {{ T.buttonCreateProblem }}
              </a>
              <a href="/contest/new/" class="btn btn-outline-success btn-pill btn-sm">
                {{ T.buttonCreateContest }}
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Teach Card -->
      <div class="col-xs-12 col-md-6 col-lg-3 mb-4">
        <div class="card h-100 shadow-sm border-0 border-radius-lg hover-up transition-all card-teach">
          <div class="card-body text-center d-flex flex-column p-4">
            <div class="icon-wrapper mb-3">
              <img src="/media/homepage/courses_section.svg" alt="Teach" class="img-fluid" />
            </div>
            <h4 class="card-title font-weight-bold mb-3">{{ T.homepageTeachSectionTitle }}</h4>
            <p class="card-text text-muted mb-4 small flex-grow-1">
              {{ T.homepageTeachSectionDescription }}
            </p>
            <a href="/course/" class="btn btn-outline-info btn-pill btn-block-md">
              {{ T.buttonGoToCourses }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class GettingStarted extends Vue {
  @Prop({ default: false }) isUnder13User!: boolean;
  T = T;
}
</script>

<style lang="scss" scoped>
.hover-up {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
  }
}

.border-radius-lg {
  border-radius: 1.25rem;
}

.btn-pill {
  border-radius: 50px;
  padding: 0.6rem 1.5rem;
  font-weight: 500;
}

.card-train {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  border: none;
  .btn-light {
    color: #0088cc;
  }
}

.icon-wrapper {
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  img {
    max-height: 100%;
  }
}

.gap-2 {
  gap: 0.5rem;
}
</style>
