'use strict';

require('../dist/commons.js');
var omegaup = require('../dist/omegaup.js');

describe('omegaup.Time', function() {
  describe('formatDateLocal', function() {
    const expectedValue = '2010-01-01';

    it('Should format dates correctly', function() {
      expect(
        omegaup.Time.formatDateLocal(new Date('2010-01-01 11:22:33')),
      ).toEqual(expectedValue);
    });

    it('Should be able to roundtrip', function() {
      expect(
        omegaup.Time.formatDateLocal(
          omegaup.Time.parseDateLocal(expectedValue),
        ),
      ).toEqual(expectedValue);
    });
  });

  describe('formatDateTimeLocal', function() {
    const expectedValue = '2010-01-01T11:22';

    it('Should format dates correctly', function() {
      expect(
        omegaup.Time.formatDateTimeLocal(new Date('2010-01-01 11:22:33')),
      ).toEqual(expectedValue);
    });

    it('Should be able to roundtrip', function() {
      expect(
        omegaup.Time.formatDateTimeLocal(
          omegaup.Time.parseDateTimeLocal(expectedValue),
        ),
      ).toEqual(expectedValue);
    });
  });

  describe('parseDuration', function() {
    it('Should handle valid inputs', function() {
      expect(omegaup.Time.parseDuration('0')).toEqual(0);
      expect(omegaup.Time.parseDuration('1')).toEqual(1000.0);
      expect(omegaup.Time.parseDuration('1s')).toEqual(1000.0);
      expect(omegaup.Time.parseDuration('1ms')).toEqual(1.0);
      expect(omegaup.Time.parseDuration('1.0ms')).toEqual(1.0);
      expect(omegaup.Time.parseDuration('0.001s')).toEqual(1.0);
      expect(omegaup.Time.parseDuration('1m30s')).toEqual(90000.0);
    });

    it('Should reject invalid inputs', function() {
      expect(omegaup.Time.parseDuration('-1s')).toBe(null);
      expect(omegaup.Time.parseDuration('.s')).toBe(null);
    });
  });

  describe('formatDelta', function() {
    it('Should handle valid dates with countdown time format', function() {
      expect(omegaup.Time.formatDelta(1000)).toEqual('00:00:01');
      expect(omegaup.Time.formatDelta(10000)).toEqual('00:00:10');
      expect(omegaup.Time.formatDelta(100000)).toEqual('00:01:40');
      expect(omegaup.Time.formatDelta(1000000)).toEqual('00:16:40');
      expect(omegaup.Time.formatDelta(10000000)).toEqual('02:46:40');
      expect(omegaup.Time.formatDelta(100000000)).toEqual('1:03:46:40');
      expect(omegaup.Time.formatDelta(1000000000)).toEqual('11:13:46:40');
      expect(omegaup.Time.formatDelta(2500000000)).toEqual('28:22:26:40');
    });

    it('Should handle valid human readable dates', function() {
      expect(omegaup.Time.formatDelta(3000000000)).toEqual('en un mes');
      expect(omegaup.Time.formatDelta(5000000000)).toEqual('en 2 meses');
      expect(omegaup.Time.formatDelta(7500000000)).toEqual('en 3 meses');
      expect(omegaup.Time.formatDelta(10000000000)).toEqual('en 4 meses');
      expect(omegaup.Time.formatDelta(50000000000)).toEqual('en 2 años');
      expect(omegaup.Time.formatDelta(100000000000)).toEqual('en 3 años');
    });
  });

  describe('toDDHHMM', function() {
    it('Should handle deltas', function() {
      expect(omegaup.Time.toDDHHMM(1)).toEqual('00h 00m');
      expect(omegaup.Time.toDDHHMM(10)).toEqual('00h 00m');
      expect(omegaup.Time.toDDHHMM(100)).toEqual('00h 01m');
      expect(omegaup.Time.toDDHHMM(1000)).toEqual('00h 16m');
      expect(omegaup.Time.toDDHHMM(10000)).toEqual('02h 46m');
      expect(omegaup.Time.toDDHHMM(100000)).toEqual('1d 03h 46m');
      expect(omegaup.Time.toDDHHMM(1000000)).toEqual('11d 13h 46m');
      expect(omegaup.Time.toDDHHMM(2500000)).toEqual('28d 22h 26m');
    });
  });
});
