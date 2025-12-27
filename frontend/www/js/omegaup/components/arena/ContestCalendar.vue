<template>
  <div class="contest-calendar">
    <!-- Header: Month navigation + View toggle -->
    <div class="calendar-header">
      <div class="month-navigation">
        <b-button
          variant="outline-secondary"
          size="sm"
          :aria-label="T.calendarPreviousMonth"
          @click="navigatePrevious"
        >
          <font-awesome-icon icon="chevron-left" />
        </b-button>
        <h3 class="month-title">{{ currentPeriodLabel }}</h3>
        <b-button
          variant="outline-secondary"
          size="sm"
          :aria-label="T.calendarNextMonth"
          @click="navigateNext"
        >
          <font-awesome-icon icon="chevron-right" />
        </b-button>
        <b-button variant="outline-primary" size="sm" @click="goToToday">
          {{ T.calendarToday }}
        </b-button>
      </div>
      <div class="view-toggle">
        <b-button-group size="sm">
          <b-button
            :variant="viewMode === 'month' ? 'primary' : 'outline-primary'"
            @click="viewMode = 'month'"
          >
            {{ T.calendarMonthView }}
          </b-button>
          <b-button
            :variant="viewMode === 'week' ? 'primary' : 'outline-primary'"
            @click="viewMode = 'week'"
          >
            {{ T.calendarWeekView }}
          </b-button>
        </b-button-group>
        <b-form-checkbox
          v-model="weekStartsOnMonday"
          class="ml-3 week-start-toggle"
          switch
        >
          {{ T.calendarMondayStart }}
        </b-form-checkbox>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="calendar-loading">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <!-- Calendar Grid -->
    <template v-else>
      <!-- Day headers -->
      <div
        class="calendar-grid"
        :class="{ 'week-view-grid': viewMode === 'week' }"
      >
        <div v-for="day in weekDays" :key="day" class="day-header">
          {{ day }}
        </div>

        <!-- Day cells -->
        <div
          v-for="(cell, index) in calendarCells"
          :key="`cell-${index}`"
          class="day-cell"
          :class="{
            'other-month': !cell.isCurrentMonth,
            today: cell.isToday,
            'has-contests': cell.contests.length > 0,
          }"
          @click="selectDay(cell)"
        >
          <span class="day-number">{{ cell.day }}</span>
          <div class="contest-indicators">
            <span
              v-for="(contest, cIndex) in cell.contests.slice(0, 3)"
              :key="`contest-${cIndex}`"
              class="contest-dot"
              :class="contestIndicatorClass(contest)"
              :title="contest.title"
            ></span>
            <span v-if="cell.contests.length > 3" class="more-indicator">
              +{{ cell.contests.length - 3 }}
            </span>
          </div>
        </div>
      </div>
    </template>

    <!-- Day Detail Modal -->
    <b-modal
      v-model="showDayDetail"
      :title="selectedDayTitle"
      size="lg"
      hide-footer
      scrollable
    >
      <template v-if="selectedDayContests.length > 0">
        <div
          v-for="contest in selectedDayContests"
          :key="contest.contest_id"
          class="day-contest-item mb-3"
        >
          <omegaup-contest-card :contest="contest">
            <template #contest-button-scoreboard>
              <div></div>
            </template>
            <template #text-contest-date>
              <b-card-text>
                <font-awesome-icon icon="calendar-alt" />
                {{ formatContestTime(contest) }}
              </b-card-text>
            </template>
            <template #contest-dropdown>
              <div></div>
            </template>
          </omegaup-contest-card>
        </div>
      </template>
      <div v-else class="text-center text-muted py-4">
        {{ T.calendarNoContests }}
      </div>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Component, Prop, Vue, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

import 'bootstrap-vue/dist/bootstrap-vue.css';
import 'bootstrap/dist/css/bootstrap.css';

import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  ButtonGroupPlugin,
  ButtonPlugin,
  CardPlugin,
  FormCheckboxPlugin,
  ModalPlugin,
} from 'bootstrap-vue';

import ContestCard from './ContestCard.vue';
import {
  CalendarCell,
  formatMonthYear,
  generateCalendarCells,
  generateWeekCells,
  getAllContests,
  getWeekDaysHeader,
} from './calendarUtils';

Vue.use(ButtonPlugin);
Vue.use(ButtonGroupPlugin);
Vue.use(ModalPlugin);
Vue.use(FormCheckboxPlugin);
Vue.use(CardPlugin);
library.add(fas);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-contest-card': ContestCard,
  },
})
export default class ContestCalendar extends Vue {
  @Prop({ required: true }) contests!: types.ContestList;
  @Prop({ default: false }) loading!: boolean;

  T = T;
  ui = ui;

  viewMode: 'month' | 'week' = 'month';
  weekStartsOnMonday: boolean = true;
  currentYear: number = new Date().getFullYear();
  currentMonth: number = new Date().getMonth();
  currentWeekDate: Date = new Date();

  showDayDetail: boolean = false;
  selectedDay: CalendarCell | null = null;

  get weekDays(): string[] {
    return getWeekDaysHeader(this.weekStartsOnMonday);
  }

  get allContests(): types.ContestListItem[] {
    return getAllContests(this.contests);
  }

  get calendarCells(): CalendarCell[] {
    if (this.viewMode === 'month') {
      return generateCalendarCells(
        this.currentYear,
        this.currentMonth,
        this.allContests,
        this.weekStartsOnMonday,
      );
    } else {
      return generateWeekCells(
        this.currentWeekDate,
        this.allContests,
        this.weekStartsOnMonday,
      );
    }
  }

  get currentPeriodLabel(): string {
    if (this.viewMode === 'month') {
      return formatMonthYear(this.currentYear, this.currentMonth);
    } else {
      const startDate = this.calendarCells[0]?.date;
      const endDate = this.calendarCells[6]?.date;
      if (startDate && endDate) {
        const options: Intl.DateTimeFormatOptions = {
          month: 'short',
          day: 'numeric',
        };
        return `${startDate.toLocaleDateString(
          undefined,
          options,
        )} - ${endDate.toLocaleDateString(
          undefined,
          options,
        )}, ${endDate.getFullYear()}`;
      }
      return '';
    }
  }

  get selectedDayTitle(): string {
    if (!this.selectedDay) return '';
    return this.selectedDay.date.toLocaleDateString(undefined, {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  }

  get selectedDayContests(): types.ContestListItem[] {
    return this.selectedDay?.contests || [];
  }

  navigatePrevious(): void {
    if (this.viewMode === 'month') {
      if (this.currentMonth === 0) {
        this.currentMonth = 11;
        this.currentYear--;
      } else {
        this.currentMonth--;
      }
    } else {
      const newDate = new Date(this.currentWeekDate);
      newDate.setDate(newDate.getDate() - 7);
      this.currentWeekDate = newDate;
    }
    this.$emit('period-changed', this.getPeriodInfo());
  }

  navigateNext(): void {
    if (this.viewMode === 'month') {
      if (this.currentMonth === 11) {
        this.currentMonth = 0;
        this.currentYear++;
      } else {
        this.currentMonth++;
      }
    } else {
      const newDate = new Date(this.currentWeekDate);
      newDate.setDate(newDate.getDate() + 7);
      this.currentWeekDate = newDate;
    }
    this.$emit('period-changed', this.getPeriodInfo());
  }

  goToToday(): void {
    const today = new Date();
    this.currentYear = today.getFullYear();
    this.currentMonth = today.getMonth();
    this.currentWeekDate = today;
    this.$emit('period-changed', this.getPeriodInfo());
  }

  selectDay(cell: CalendarCell): void {
    this.selectedDay = cell;
    this.showDayDetail = true;
    this.$emit('date-selected', cell.date);
  }

  contestIndicatorClass(contest: types.ContestListItem): string {
    if (contest.recommended) {
      return 'recommended';
    }
    if (contest.participating) {
      return 'participating';
    }
    return 'public';
  }

  formatContestTime(contest: types.ContestListItem): string {
    const start = new Date(contest.start_time);
    const end = new Date(contest.finish_time);
    const options: Intl.DateTimeFormatOptions = {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    };
    return `${start.toLocaleDateString(
      undefined,
      options,
    )} - ${end.toLocaleDateString(undefined, options)}`;
  }

  getPeriodInfo(): { year: number; month: number; viewMode: string } {
    return {
      year: this.currentYear,
      month: this.currentMonth,
      viewMode: this.viewMode,
    };
  }

  @Watch('weekStartsOnMonday')
  onWeekStartChanged(): void {
    // Force recalculation of calendar cells
    this.$forceUpdate();
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.contest-calendar {
  padding: 1rem;

  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    gap: 1rem;

    .month-navigation {
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .month-title {
        min-width: 200px;
        text-align: center;
        margin: 0;
        font-size: 1.25rem;
      }
    }

    .view-toggle {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
  }

  .calendar-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: var(--arena-scoreboard-hover-color, #dee2e6);
    border: 1px solid var(--arena-scoreboard-hover-color, #dee2e6);
    border-radius: 0.25rem;
    overflow: hidden;

    &.week-view-grid {
      .day-cell {
        min-height: 150px;
      }
    }

    .day-header {
      padding: 0.5rem;
      text-align: center;
      font-weight: bold;
      background-color: var(
        --arena-contest-list-sidebar-tab-list-background-color,
        #f8f9fa
      );
      color: var(--arena-contest-list-empty-category-font-color, #6c757d);
      font-size: 0.875rem;
    }

    .day-cell {
      min-height: 100px;
      padding: 0.5rem;
      background-color: var(
        --arena-runs-table-status-je-ve-font-color,
        #ffffff
      );
      cursor: pointer;
      transition: background-color 0.2s ease;

      &:hover {
        background-color: var(--arena-scoreboard-hover-color, #f1f3f4);
      }

      &.other-month {
        opacity: 0.5;
        background-color: #f8f9fa;
      }

      &.today {
        border: 2px solid var(--arena-button-border-color, #007bff);

        .day-number {
          background-color: var(--arena-button-border-color, #007bff);
          color: white;
          border-radius: 50%;
          padding: 0.125rem 0.5rem;
          display: inline-block;
        }
      }

      &.has-contests {
        background-color: rgba(0, 123, 255, 0.05);
      }

      .day-number {
        font-weight: bold;
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
      }

      .contest-indicators {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;

        .contest-dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          flex-shrink: 0;

          &.participating {
            background-color: $omegaup-green;
          }

          &.public {
            background-color: #007bff;
          }

          &.recommended {
            background-color: gold;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
          }
        }

        .more-indicator {
          font-size: 0.75rem;
          color: var(--arena-contest-list-empty-category-font-color, #6c757d);
          font-weight: 500;
        }
      }
    }
  }

  .day-contest-item {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 1rem;

    &:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
  }

  .week-start-toggle {
    font-size: 0.875rem;
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .contest-calendar {
    padding: 0.5rem;

    .calendar-header {
      flex-direction: column;
      align-items: stretch;

      .month-navigation {
        justify-content: center;

        .month-title {
          min-width: 150px;
          font-size: 1rem;
        }
      }

      .view-toggle {
        justify-content: center;
        flex-wrap: wrap;
      }
    }

    .calendar-grid {
      .day-cell {
        min-height: 60px;
        padding: 0.25rem;

        .day-number {
          font-size: 0.75rem;
        }

        .contest-indicators {
          .contest-dot {
            width: 6px;
            height: 6px;
          }
        }
      }

      .day-header {
        padding: 0.25rem;
        font-size: 0.75rem;
      }
    }
  }
}
</style>
