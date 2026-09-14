var iso3166 = require("../iso3166.min");

suite("iso3166-2", function () {
  bench("#country(alpha 2)", function () {
    iso3166.country("SE");
  });

  bench("#country(alpha 3)", function () {
    iso3166.country("USA");
  });

  bench("#subdivision(alpha 2, code)", function () {
    iso3166.country("SE", "O");
  });

  bench("#subdivision(alpha 3, code)", function () {
    iso3166.country("USA", "IN");
  });

  bench("#subdivision(alpha 2, name)", function () {
    iso3166.country("US", "Indiana");
  });

  bench("#subdivision(alpha 3, name)", function () {
    iso3166.country("USA", "Indiana");
  });

  bench("#subdivision(alpha 2-code)", function () {
    iso3166.country("SE-O");
  });

  bench("#subdivision(alpha 3-code)", function () {
    iso3166.country("SWE-O");
  });

  bench("#subdivision(alpha 2-name)", function () {
    iso3166.country("US-Indiana");
  });

  bench("#subdivision(alpha 3-name)", function () {
    iso3166.country("USA-Indiana");
  });
});
