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
    it('Should handle valid dates with countdown time format', () => {
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
      expect(time.formatDelta(3000000000)).toEqual('en un mes');
      expect(time.formatDelta(5000000000)).toEqual('en 2 meses');
      expect(time.formatDelta(7500000000)).toEqual('en 3 meses');
      expect(time.formatDelta(10000000000)).toEqual('en 4 meses');
      expect(time.formatDelta(50000000000)).toEqual('en 2 años');
      expect(time.formatDelta(100000000000)).toEqual('en 3 años');
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
});
