import * as time from './time';

describe('time', () => {
  describe('formatDateLocal', () => {
    const expectedValue = '2010-01-01';

    it('Should format dates correctly', () => {
      expect(time.formatDateLocal(new Date('2010-01-01 11:22:33'))).toEqual(
        expectedValue,
      );
    });

    it('Should be able to roundtrip', () => {
      expect(time.formatDateLocal(time.parseDateLocal(expectedValue))).toEqual(
        expectedValue,
      );
    });

    it('Should be able to get current date', () => {
      expect(time.parseDateLocal('')).toEqual(expect.any(Date));
    });
  });

  describe('formatDateTimeLocal', () => {
    const expectedValue = '2010-01-01T11:22';

    it('Should format dates correctly', () => {
      expect(time.formatDateTimeLocal(new Date('2010-01-01 11:22:33'))).toEqual(
        expectedValue,
      );
    });

    it('Should be able to roundtrip', () => {
      expect(
        time.formatDateTimeLocal(time.parseDateTimeLocal(expectedValue)),
      ).toEqual(expectedValue);
    });

    it('Should be able to get current datetime', () => {
      expect(time.parseDateTimeLocal('')).toEqual(expect.any(Date));
    });
  });

  describe('formatTimestamp', () => {
    const expectedValue = '2010-01-01 11:22:33';

    it('Should format timestamps correctly', () => {
      expect(time.formatTimestamp(new Date('2010-01-01 11:22:33'))).toEqual(
        expectedValue,
      );
    });
  });

  describe('parseDuration', () => {
    it('Should handle valid inputs', () => {
      expect(time.parseDuration('0')).toEqual(0);
      expect(time.parseDuration('1')).toEqual(1000.0);
      expect(time.parseDuration('1s')).toEqual(1000.0);
      expect(time.parseDuration('1ms')).toEqual(1.0);
      expect(time.parseDuration('1.0ms')).toEqual(1.0);
      expect(time.parseDuration('0.001s')).toEqual(1.0);
      expect(time.parseDuration('1m30s')).toEqual(90000.0);
    });

    it('Should reject invalid inputs', () => {
      expect(time.parseDuration('-1s')).toBe(null);
      expect(time.parseDuration('.s')).toBe(null);
    });
  });

  describe('formatDelta', () => {
    // Setting an specific datetime to avoid flakiness in a leap-year
    const now = new Date(0).getDate();
    let dateNowSpy: jest.SpyInstance<number, []> | null = null;

    beforeEach(() => {
      dateNowSpy = jest.spyOn(Date, 'now').mockImplementation(() => now);
    });

    afterEach(() => {
      if (dateNowSpy) {
        dateNowSpy.mockRestore();
      }
    });

    it('Should handle valid dates with countdown time format', () => {
      expect(time.formatDelta(-2500000000)).toEqual('−28:22:26:40');
      expect(time.formatDelta(-1000000000)).toEqual('−11:13:46:40');
      expect(time.formatDelta(-100000000)).toEqual('−1:03:46:40');
      expect(time.formatDelta(-10000000)).toEqual('−02:46:40');
      expect(time.formatDelta(-1000000)).toEqual('−00:16:40');
      expect(time.formatDelta(-100000)).toEqual('−00:01:40');
      expect(time.formatDelta(-10000)).toEqual('−00:00:10');
      expect(time.formatDelta(-1000)).toEqual('−00:00:01');
      expect(time.formatDelta(0)).toEqual('00:00:00');
      expect(time.formatDelta(1000)).toEqual('00:00:01');
      expect(time.formatDelta(10000)).toEqual('00:00:10');
      expect(time.formatDelta(100000)).toEqual('00:01:40');
      expect(time.formatDelta(1000000)).toEqual('00:16:40');
      expect(time.formatDelta(10000000)).toEqual('02:46:40');
      expect(time.formatDelta(100000000)).toEqual('1:03:46:40');
      expect(time.formatDelta(1000000000)).toEqual('11:13:46:40');
      expect(time.formatDelta(2500000000)).toEqual('28:22:26:40');
    });

    it('Should handle valid human readable dates', () => {
      const millMapping = [
        [{ milliseconds: 3000000000, expected: 'en alrededor de 1 mes' }],
        [{ milliseconds: 5259492000, expected: 'en 2 meses' }],
        [{ milliseconds: 7889238000, expected: 'en 3 meses' }],
        [{ milliseconds: 10518984000, expected: 'en 4 meses' }],
        [{ milliseconds: 63113904000, expected: 'en alrededor de 2 años' }],
        [{ milliseconds: 94670856000, expected: 'en casi 3 años' }],
      ];
      for (const [{ milliseconds, expected }] of millMapping) {
        expect(time.formatDelta(milliseconds)).toEqual(expected);
      }
    });
  });

  describe('toDDHHMM', () => {
    it('Should handle deltas', () => {
      expect(time.toDDHHMM(1)).toEqual('00h 00m');
      expect(time.toDDHHMM(10)).toEqual('00h 00m');
      expect(time.toDDHHMM(100)).toEqual('00h 01m');
      expect(time.toDDHHMM(1000)).toEqual('00h 16m');
      expect(time.toDDHHMM(10000)).toEqual('02h 46m');
      expect(time.toDDHHMM(100000)).toEqual('1d 03h 46m');
      expect(time.toDDHHMM(1000000)).toEqual('11d 13h 46m');
      expect(time.toDDHHMM(2500000)).toEqual('28d 22h 26m');
    });
  });

  describe('remoteTimeAdapter', () => {
    beforeEach(() => {
      time._setRemoteDeltaTime(1);
    });

    it('Should handle Dates', () => {
      expect(time.remoteTimeAdapter(new Date(0))).toEqual(new Date(1));
    });

    it('Should handle arrays', () => {
      expect(time.remoteTimeAdapter([new Date(0)])).toEqual([new Date(1)]);
    });

    it('Should handle primitives', () => {
      expect(time.remoteTimeAdapter(1)).toEqual(1);
    });

    it('Should handle objects', () => {
      expect(
        time.remoteTimeAdapter({
          time: new Date(0),
          status: 'ok',
        }),
      ).toEqual({
        time: new Date(1),
        status: 'ok',
      });
    });

    it('Should handle complex objects', () => {
      expect(
        time.remoteTimeAdapter({
          contests: [
            {
              time: new Date(0),
            },
          ],
          status: 'ok',
        }),
      ).toEqual({
        contests: [
          {
            time: new Date(1),
          },
        ],
        status: 'ok',
      });
    });
  });

  describe('parseDateTimeLocal', () => {
    const expectedValue = '2021-02-01T08:55';

    it('Should parse dates correctly when given a month that does not have the current day', () => {
      expect(
        time.formatDateTimeLocal(time.parseDateTimeLocal(expectedValue)),
      ).toEqual(expectedValue);
    });

    it('Should parse dates accordingly when given parameters outside of the expected range', () => {
      expect(
        time.formatDateTimeLocal(time.parseDateTimeLocal('2021-02-29T08:55')),
      ).toEqual('2021-03-01T08:55');
    });
  });

  describe('convertLocalDateToGMTDate', () => {
    it('Should preserve UTC year/month/day as local date components', () => {
      const dateNow = new Date();
      const result = time.convertLocalDateToGMTDate(dateNow);

      expect(result.getFullYear()).toEqual(dateNow.getUTCFullYear());
      expect(result.getMonth()).toEqual(dateNow.getUTCMonth());
      expect(result.getDate()).toEqual(dateNow.getUTCDate());
    });

    it('Should handle midnight UTC dates that shift day in negative timezones', () => {
      // Simulate a birth_date timestamp from the server: 2000-01-15 midnight UTC
      const serverTimestamp = Date.UTC(2000, 0, 15);
      const dateFromServer = new Date(serverTimestamp);
      const result = time.convertLocalDateToGMTDate(dateFromServer);

      expect(result.getFullYear()).toEqual(2000);
      expect(result.getMonth()).toEqual(0);
      expect(result.getDate()).toEqual(15);
    });
  });

  describe('formatContestDuration', () => {
    it('Should show correct time format', () => {
      const daySeconds = 24 * 60 * 60 * 1000;
      const hoursSeconds = 60 * 60 * 1000;
      const minutesSeconds = 60 * 1000;
      const seconds = 1000;
      const today = new Date('2021-01-01 00:00:00+00:00');
      const tomorrow = new Date(today.getTime() + daySeconds);
      const millMapping = [
        [
          {
            startDate: today,
            finishDate: new Date(
              tomorrow.getTime() +
                daySeconds +
                hoursSeconds * 5 +
                minutesSeconds * 30 +
                seconds * 10,
            ),
            expected: '2:05:30:10',
          },
        ],
        [
          {
            startDate: today,
            finishDate: new Date(tomorrow.getTime() + daySeconds * 30),
            expected: '1 mes',
          },
        ],
        [
          {
            startDate: today,
            finishDate: new Date(
              tomorrow.getTime() +
                daySeconds * 31 +
                hoursSeconds * 5 +
                minutesSeconds * 30 +
                seconds * 10,
            ),
            expected: '1 mes 1 día 5 horas 30 minutos 10 segundos',
          },
        ],
        [
          {
            startDate: today,
            finishDate: new Date(tomorrow.getTime() - minutesSeconds),
            expected: '23:59:00',
          },
        ],
        [
          {
            startDate: today,
            finishDate: new Date(
              tomorrow.getTime() - minutesSeconds * 15 - seconds * 5,
            ),
            expected: '23:44:55',
          },
        ],
        [
          {
            startDate: today,
            finishDate: new Date(today.getTime() + minutesSeconds * 45),
            expected: '00:45:00',
          },
        ],
        [
          {
            startDate: today,
            finishDate: new Date(
              today.getTime() + hoursSeconds * 3 + minutesSeconds * 45,
            ),
            expected: '03:45:00',
          },
        ],
      ];
      for (const [{ startDate, finishDate, expected }] of millMapping) {
        expect(time.formatContestDuration(startDate, finishDate)).toEqual(
          expected,
        );
      }
    });
  });
});
