'use strict';

require('../../dist/commons.js');
var omegaup = require('../../dist/omegaup.js');
var Markdown =
    require('../../../third_party/js/pagedown/Markdown.Sanitizer.js');

describe('omegaup.arena', function() {
  describe('ArenaAdmin', function() {
    beforeAll(function() {
      omegaup.OmegaUp.ready = true;
      omegaup.OmegaUp._deltaTime = 0;
    });

    it('can be instantiated', function() {
      if (typeof(global) !== 'undefined' && !global.Markdown) {
        global.Markdown = Markdown;
      }
      var arena = new omegaup.arena.Arena({contestAlias: 'test'});
      var admin = new omegaup.arena.ArenaAdmin(arena);
      expect(arena.contestAdmin).toEqual(true);
    });
  });
});
