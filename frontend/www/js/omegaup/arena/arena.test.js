'use strict';

require('../../dist/commons.js');
var omegaup = require('../../dist/omegaup.js');
var Markdown =
    require('../../../third_party/js/pagedown/Markdown.Sanitizer.js');

describe('omegaup.arena', function() {
  describe('FormatDelta', function() {
    it('Should handle zeroes',
       function() { expect(omegaup.UI.formatDelta(0)).toEqual('00:00:00'); });

    it('Should handle large deltas', function() {
      expect(omegaup.UI.formatDelta(31 * 24 * 3600 * 1000))
          .toEqual('31:00:00:00');
    });
  });

  describe('GetOptionsFromLocation', function() {
    it('Should detect normal contests', function() {
      var options = omegaup.arena.GetOptionsFromLocation(
          new window.URL('http://localhost/arena/test/'));
      expect(options.contestAlias).toEqual('test');
      expect(options.isPractice).toEqual(false);
      expect(options.isOnlyProblem).toEqual(false);
      expect(options.disableClarifications).toEqual(false);
      expect(options.disableSockets).toEqual(false);
      expect(options.scoreboardToken).toEqual(null);
    });

    it('Should detect practice mode', function() {
      var options = omegaup.arena.GetOptionsFromLocation(
          new window.URL('http://localhost/arena/test/practice'));
      expect(options.contestAlias).toEqual('test');
      expect(options.isPractice).toEqual(true);
    });

    it('Should detect only problems', function() {
      var options = omegaup.arena.GetOptionsFromLocation(
          new window.URL('http://localhost/arena/problem/test/'));
      expect(options.contestAlias).toEqual(null);
      expect(options.onlyProblemAlias).toEqual('test');
      expect(options.isOnlyProblem).toEqual(true);
    });

    it('Should detect ws=off', function() {
      var options = omegaup.arena.GetOptionsFromLocation(
          new window.URL('http://localhost/arena/test/?ws=off'));
      expect(options.disableSockets).toEqual(true);
    });
  });

  describe('Arena', function() {
    beforeAll(function() {
      omegaup.OmegaUp.ready = true;
      omegaup.OmegaUp._deltaTime = 0;
    });

    it('can be instantiated', function() {
      if (typeof(global) !== 'undefined' && !global.Markdown) {
        global.Markdown = Markdown;
      }
      var arena = new omegaup.arena.Arena({contestAlias: 'test'});
      expect(arena.options.contestAlias).toEqual('test');
      expect(arena.problemsetAdmin).toEqual(false);
    });
  });
});
