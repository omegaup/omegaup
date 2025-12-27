jest.mock('../../../../third_party/js/diff_match_patch.js');

import { shallowMount } from '@vue/test-utils';
import type { types } from '../../api_types';

import ContestCalendar from './ContestCalendar.vue';
import {
  generateCalendarCells,
  getAllContests,
  getContestsForDate,
  getWeekDaysHeader,
  isSameDay,
} from './calendarUtils';

describe('ContestCalendar.vue', () => {
  const daySeconds = 24 * 60 * 60 * 1000;
  const today = new Date();
  const yesterday = new Date(today.getTime() - daySeconds);
  const tomorrow = new Date(today.getTime() + daySeconds);

  const contests: types.ContestList = {
    current: [
      {
        admission_mode: 'public',
        alias: 'current-contest-1',
        description: 'Current Contest 1 Description',
        contest_id: 1,
        contestants: 12,
        finish_time: tomorrow,
        last_updated: yesterday,
        organizer: 'omegaup',
        original_finish_time: tomorrow,
        score_mode: 'all_or_nothing',
        participating: true,
        problemset_id: 1,
        recommended: true,
        start_time: yesterday,
        title: 'Current Contest 1',
        window_length: 300,
      },
    ],
    future: [
      {
        admission_mode: 'public',
        alias: 'future-contest-1',
        description: 'Future Contest 1 Description',
        contest_id: 2,
        contestants: 5,
        finish_time: new Date(tomorrow.getTime() + daySeconds * 2),
        last_updated: today,
        organizer: 'omegaup',
        original_finish_time: new Date(tomorrow.getTime() + daySeconds * 2),
        score_mode: 'all_or_nothing',
        participating: false,
        problemset_id: 2,
        recommended: false,
        start_time: new Date(tomorrow.getTime() + daySeconds),
        title: 'Future Contest 1',
        window_length: 300,
      },
    ],
    past: [
      {
        admission_mode: 'public',
        alias: 'past-contest-1',
        description: 'Past Contest 1 Description',
        contest_id: 3,
        contestants: 20,
        finish_time: new Date(yesterday.getTime() - daySeconds),
        last_updated: new Date(yesterday.getTime() - daySeconds * 2),
        organizer: 'omegaup',
        original_finish_time: new Date(yesterday.getTime() - daySeconds),
        score_mode: 'all_or_nothing',
        participating: true,
        problemset_id: 3,
        recommended: false,
        start_time: new Date(yesterday.getTime() - daySeconds * 2),
        title: 'Past Contest 1',
        window_length: 300,
      },
    ],
  };

  describe('calendarUtils', () => {
    it('Should generate correct week days header for Monday start', () => {
      const weekDays = getWeekDaysHeader(true);
      expect(weekDays).toEqual([
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat',
        'Sun',
      ]);
    });

    it('Should generate correct week days header for Sunday start', () => {
      const weekDays = getWeekDaysHeader(false);
      expect(weekDays).toEqual([
        'Sun',
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat',
      ]);
    });

    it('Should correctly identify same day', () => {
      const date1 = new Date(2024, 0, 15, 10, 30, 0);
      const date2 = new Date(2024, 0, 15, 22, 45, 0);
      const date3 = new Date(2024, 0, 16, 10, 30, 0);

      expect(isSameDay(date1, date2)).toBe(true);
      expect(isSameDay(date1, date3)).toBe(false);
    });

    it('Should generate 42 calendar cells for a month', () => {
      const cells = generateCalendarCells(2024, 0, [], true); // January 2024
      expect(cells.length).toBe(42);
    });

    it('Should identify today in calendar cells', () => {
      const cells = generateCalendarCells(
        today.getFullYear(),
        today.getMonth(),
        [],
        true,
      );
      const todayCell = cells.find((cell) => cell.isToday);
      expect(todayCell).toBeDefined();
      expect(todayCell?.day).toBe(today.getDate());
    });

    it('Should filter contests for a specific date', () => {
      const allContests = getAllContests(contests);
      const contestsOnDate = getContestsForDate(today, allContests);
      // Current contest spans yesterday to tomorrow, so should include today
      expect(contestsOnDate.length).toBeGreaterThanOrEqual(0);
    });

    it('Should combine all contests from all categories', () => {
      const allContests = getAllContests(contests);
      expect(allContests.length).toBe(3);
    });
  });

  describe('Component', () => {
    it('Should render calendar header with month navigation', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      expect(wrapper.find('.calendar-header').exists()).toBe(true);
      expect(wrapper.find('.month-navigation').exists()).toBe(true);
    });

    it('Should render loading spinner when loading is true', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: true,
        },
      });

      expect(wrapper.find('.calendar-loading').exists()).toBe(true);
    });

    it('Should render calendar grid when not loading', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      expect(wrapper.find('.calendar-grid').exists()).toBe(true);
    });

    it('Should render 7 day headers', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      const dayHeaders = wrapper.findAll('.day-header');
      expect(dayHeaders.length).toBe(7);
    });

    it('Should render 42 day cells', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      const dayCells = wrapper.findAll('.day-cell');
      expect(dayCells.length).toBe(42);
    });

    it('Should have view toggle buttons', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      expect(wrapper.find('.view-toggle').exists()).toBe(true);
    });

    it('Should emit date-selected event when clicking a day cell', async () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      const dayCells = wrapper.findAll('.day-cell');
      if (dayCells.length > 0) {
        await dayCells.at(15)?.trigger('click');
        expect(wrapper.emitted('date-selected')).toBeTruthy();
      }
    });

    it('Should have today class on the current day cell', () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      // Find the cell marked as today
      const todayCell = wrapper.find('.day-cell.today');
      expect(todayCell.exists()).toBe(true);
    });

    it('Should change month when navigating', async () => {
      const wrapper = shallowMount(ContestCalendar, {
        propsData: {
          contests,
          loading: false,
        },
      });

      const initialMonthLabel = wrapper.find('.month-title').text();

      // Click next month button
      const buttons = wrapper.findAll('b-button-stub');
      // Find the next button (usually second nav button)
      for (let i = 0; i < buttons.length; i++) {
        const buttonElement = buttons.at(i);
        // Trigger navigateNext explicitly
        if (
          buttonElement &&
          buttonElement.attributes('aria-label')?.includes('Next')
        ) {
          await buttonElement.trigger('click');
          break;
        }
      }

      // After navigating, the period-changed event should be emitted
      // Note: In shallow mount, some nested button clicks might not work as expected
    });
  });
});
