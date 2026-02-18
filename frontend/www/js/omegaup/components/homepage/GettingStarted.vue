<template>
  <div class="getting-started-section py-5">
    <div class="container-lg">
      <div class="section-header text-center mb-4">
        <p class="section-subtitle mb-1">{{ T.homepageHowItWorks }}</p>
        <h2 class="section-title">{{ T.homepageGetStartedTitle }}</h2>
      </div>
      <div class="cards-grid">
        <div class="getting-started-card">
          <div class="card-image-wrapper">
            <img
              class="card-image"
              src="/media/homepage/contests_section.svg"
              :alt="T.homepageCompeteSectionTitle"
            />
          </div>
          <div class="card-body">
            <h3 class="card-title">{{ T.homepageCompeteSectionTitle }}</h3>
            <p class="card-description">
              {{ T.homepageCompeteSectionDescription }}
            </p>
            <div class="card-links">
              <a class="card-link" href="/arena/">{{
                T.buttonGoToContests
              }}</a>
            </div>
          </div>
        </div>
        <div class="getting-started-card">
          <div class="card-image-wrapper">
            <img
              class="card-image"
              src="/media/homepage/problems_section.svg"
              :alt="T.homepageTrainSectionTitle"
            />
          </div>
          <div class="card-body">
            <h3 class="card-title">{{ T.homepageTrainSectionTitle }}</h3>
            <p class="card-description">
              {{ T.homepageTrainSectionDescription }}
            </p>
            <div class="card-links">
              <a class="card-link" href="/problem/">{{
                T.buttonGoToProblems
              }}</a>
            </div>
          </div>
        </div>
        <div class="getting-started-card">
          <div class="card-image-wrapper">
            <img
              class="card-image"
              src="/media/homepage/create_section.svg"
              :alt="T.homepageCreateSectionTitle"
            />
          </div>
          <div class="card-body">
            <h3 class="card-title">{{ T.homepageCreateSectionTitle }}</h3>
            <p class="card-description">
              {{ T.homepageCreateSectionDescription }}
            </p>
            <div v-if="!isUnder13User" class="card-links">
              <a class="card-link" href="/problem/new/">{{
                T.buttonCreateProblem
              }}</a>
              <a class="card-link" href="/contest/new/">{{
                T.buttonCreateContest
              }}</a>
            </div>
          </div>
        </div>
        <div class="getting-started-card">
          <div class="card-image-wrapper">
            <img
              class="card-image"
              src="/media/homepage/courses_section.svg"
              :alt="T.homepageTeachSectionTitle"
            />
          </div>
          <div class="card-body">
            <h3 class="card-title">{{ T.homepageTeachSectionTitle }}</h3>
            <p class="card-description">
              {{ T.homepageTeachSectionDescription }}
            </p>
            <div class="card-links">
              <a class="card-link" href="/course/">{{
                T.buttonGoToCourses
              }}</a>
            </div>
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
@import '../../../../sass/main.scss';

.getting-started-section {
  background-color: $omegaup-white;
}

.section-header {
  .section-subtitle {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: $omegaup-primary--darker;
    margin-bottom: 0.25rem;
  }

  .section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--homepage-getting-started-title-color);
    margin: 0;
  }
}

.cards-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;

  @media (min-width: 576px) {
    grid-template-columns: repeat(2, 1fr);
  }

  @media (min-width: 992px) {
    grid-template-columns: repeat(4, 1fr);
  }
}

.getting-started-card {
  border: 1px solid var(--homepage-getting-started-card-border-color);
  border-radius: 12px;
  overflow: hidden;
  background-color: $omegaup-white;
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.2s ease;

  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  }

  .card-image-wrapper {
    padding: 1rem 1rem 0;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 140px;

    @media (min-width: 992px) {
      min-height: 160px;
    }
  }

  .card-image {
    max-width: 100%;
    max-height: 140px;
    object-fit: contain;

    @media (min-width: 992px) {
      max-height: 150px;
    }
  }

  .card-body {
    padding: 1rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--homepage-getting-started-title-color);
    margin-bottom: 0.5rem;
  }

  .card-description {
    font-size: 0.875rem;
    color: var(--homepage-getting-started-description-color);
    line-height: 1.5;
    flex-grow: 1;
    margin-bottom: 0.75rem;
  }

  .card-links {
    display: flex;
    flex-direction: row;
    align-items: center;
    margin-top: auto;
    flex-wrap: wrap;
    gap: 0.25rem 0.75rem;

    .card-link {
      font-size: 0.85rem;
      color: $omegaup-primary--accent;
      text-decoration: underline;
      font-weight: 500;
      white-space: nowrap;

      &:hover {
        color: $omegaup-primary--darker;
      }
    }
  }
}
</style>
