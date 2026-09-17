var _ = require("underscore");
var test = require("tape");
var iso3166 = require("../iso3166.min");

test("country()", function (t) {
  t.plan(8);

  var country = iso3166.country("SE");

  t.equal(country.code, "SE");
  t.equal(country.name, "Sweden");

  country = iso3166.country("SWE");
  t.equal(country.code, "SE");
  t.equal(country.name, "Sweden");

  country = iso3166.country("Sweden");
  t.equal(country.code, "SE");
  t.equal(country.name, "Sweden");

  country = iso3166.country("UN");
  t.equal(country, null);

  country = iso3166.country("United Nations");
  t.equal(country, null);
});

test("subdivison()", function (t) {
  t.plan(53);

  var sub = iso3166.subdivision("SE-O");
  t.equal(sub.countryCode, "SE");
  t.equal(sub.countryName, "Sweden");
  t.equal(sub.code, "SE-O");
  t.equal(sub.regionCode, "O");
  t.equal(sub.name, "Västra Götalands län");

  sub = iso3166.subdivision("SE", "O");
  t.equal(sub.countryCode, "SE");
  t.equal(sub.countryName, "Sweden");
  t.equal(sub.code, "SE-O");
  t.equal(sub.regionCode, "O");
  t.equal(sub.name, "Västra Götalands län");

  sub = iso3166.subdivision("US-IN");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("US", "IN");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("US-Indiana");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("US", "Indiana");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("USA", "Indiana");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("SWE-O");
  t.equal(sub.countryCode, "SE");
  t.equal(sub.countryName, "Sweden");
  t.equal(sub.code, "SE-O");
  t.equal(sub.regionCode, "O");
  t.equal(sub.name, "Västra Götalands län");

  sub = iso3166.subdivision("USA-Indiana");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("US-Indiana");
  t.equal(sub.countryCode, "US");
  t.equal(sub.countryName, "United States");
  t.equal(sub.code, "US-IN");
  t.equal(sub.regionCode, "IN");
  t.equal(sub.name, "Indiana");

  sub = iso3166.subdivision("UN-1");
  t.equal(sub, null);

  sub = iso3166.subdivision("UN", "1");
  t.equal(sub, null);

  sub = iso3166.subdivision("UNN-1");
  t.equal(sub, null);
});

test("general", function (t) {
  var codes = iso3166.codes;
  var data = iso3166.data;
  var tests = _.keys(codes).length * 3;
  t.plan(tests);

  _.each(codes, function (code2, code3) {
    t.equal(code2.length, 2, code2 + " should be of length 2");
    t.equal(code3.length, 3, code3 + " code3 should be of length 3");
    t.ok(data[code2], "there should be data for " + code2);
  });
});
