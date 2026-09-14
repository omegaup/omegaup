var assert = require('assert');
var fs = require('fs');
var karel = require('../js/karel.js');
var DOMParser = require('xmldom').DOMParser;
var util = require('../js/util.js');

describe('problems', function () {
  var problems = fs.readdirSync('test/problems');

  problems.forEach(function (problem) {
    it(problem, function () {
      // 10s timeout per problem.
      this.timeout(10000);

      var problemDir = 'test/problems/' + problem + '/';
      var file = fs.readFileSync(problemDir + 'sol.txt', { encoding: 'utf-8' });
      var compiled = karel.compile(file);
      fs.readdirSync(problemDir + 'cases').forEach(function (casename) {
        if (!casename.endsWith('.in')) return;
        var inPath = problemDir + 'cases/' + casename;
        var outPath = inPath.slice(0, -3) + '.out';

        var worldXml = new DOMParser().parseFromString(
          fs.readFileSync(inPath, { encoding: 'utf-8' }),
          'text/xml',
        );
        var world = new karel.World(100, 100);
        world.load(worldXml);
        world.runtime.load(compiled);
        while (world.runtime.step()) {
          // Keep going...
        }
        var output = world.output().replace(/\s+/g, '');

        var expectedOutput = fs
          .readFileSync(outPath, { encoding: 'utf-8' })
          .replace(/\s+/g, '');
        assert.equal(output, expectedOutput);
      });
    });
  });
});

describe('draw worlds', function () {
  var problems = fs.readdirSync('test/problems');

  problems.forEach(function (problem) {
    it(problem, function () {
      // 10s timeout per problem.
      this.timeout(10000);

      var problemDir = 'test/problems/' + problem + '/';
      var solutionPath = problemDir + 'sol.txt';

      var allDoneWriting = fs
        .readdirSync(problemDir + 'cases')
        .slice(0, 1) // only test one case
        .map(function (casename) {
          if (!casename.endsWith('.in')) return;
          var inPath = problemDir + 'cases/' + casename;
          var pngPath = inPath.slice(0, -3) + '.png';

          var world = fs.readFileSync(inPath, { encoding: 'utf-8' });

          return util
            .Draw(world, pngPath, { run: solutionPath })
            .finally(() => assert(fs.existsSync(pngPath)));
        });

      return Promise.all(allDoneWriting);
    });
  });
});

describe('import old mdo+kec', function () {
  var oldCases = fs.readdirSync('test/mdokec');

  oldCases.forEach(function (casename) {
    if (!casename.endsWith('.in')) return;

    it(casename.slice(0, -3), function () {
      var inPath = 'test/mdokec/' + casename;
      var mdoPath = inPath.slice(0, -3) + '.mdo';
      var kecPath = inPath.slice(0, -3) + '.kec';

      var world = util.ImportMdoKec(mdoPath, kecPath);

      var output = world.save().replace(/\s+/g, '');

      var expectedOutput = fs
        .readFileSync(inPath, { encoding: 'utf-8' })
        .replace(/\s+/g, '');

      assert.equal(output, expectedOutput);
    });
  });
});
