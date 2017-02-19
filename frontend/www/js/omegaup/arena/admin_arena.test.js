'use strict';

var omegaup = require('../../dist/omegaup.js');

describe('omegaup.arena', function() {
  describe('ArenaAdmin', function() {
    beforeAll(function() {
      omegaup.OmegaUp.ready = true;
      omegaup.OmegaUp._deltaTime = 0;
    });

    it('can be instantiated', function() {
      var arena = new omegaup.arena.Arena({contestAlias: 'test'});
      var admin = new omegaup.arena.ArenaAdmin(arena);
      expect(arena.contestAdmin).toEqual(true);
    });
  });
});
