'use strict';

require('../../dist/commons.js');
var omegaup = require('../../dist/omegaup.js');
var arena = require('../../dist/arena.js');
var Markdown = require('../../../third_party/js/pagedown/Markdown.Sanitizer.js');

describe('arena', function() {
  describe('ArenaAdmin', function() {
    beforeAll(function() {
      omegaup.OmegaUp.ready = true;
      omegaup.OmegaUp._deltaTime = 0;
    });

    it('can be instantiated', function() {
      if (typeof global !== 'undefined' && !global.Markdown) {
        global.Markdown = Markdown;
      }
      var arenaInstance = new arena.Arena({ contestAlias: 'test' });
      var adminInstance = new arena.ArenaAdmin(arenaInstance);
      expect(arenaInstance.problemsetAdmin).toEqual(true);
    });
  });
});
