describe("Backend Local CSV", function() {
  it("parse", function() {
    var csv = '"Jones, Jay",10\n' +
    '"Xyz ""ABC"" O\'Brien",11:35\n' +
    '"Other, AN",12:35\n';

    var array = CSV.parse(csv);
    var exp = [
      ['Jones, Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other, AN', '12:35' ]
    ];
    expect(array).toEqual(exp);

    var csv = '"Jones, Jay", 10\n' +
    '"Xyz ""ABC"" O\'Brien", 11:35\n' +
    '"Other, AN", 12:35\n';
    var array = CSV.parse(csv, {trim : true});
    expect(array).toEqual(exp);

    var csv = 'Name, Value\n' +
    '"Jones, Jay", 10\n' +
    '"Xyz ""ABC"" O\'Brien", 11:35\n' +
    '"Other, AN", 12:35\n';
    var dataset = {
      data: csv
    };
    // strictly this is asynchronous
    CSV.fetch(dataset).done(function(dataset) {
      expect(dataset.records.length).toEqual(3);
      var row = dataset.records[0];
      expect(dataset.fields).toEqual(['Name', 'Value']);
      expect(row).toEqual(['Jones, Jay', 10]);
    });
  });

  it("parse - semicolon", function() {
    var csv = '"Jones; Jay";10\n' +
    '"Xyz ""ABC"" O\'Brien";11:35\n' +
    '"Other; AN";12:35\n';

    var array = CSV.parse(csv, {delimiter : ';'});
    var exp = [
      ['Jones; Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other; AN', '12:35' ]
    ];
    expect(array).toEqual(exp);
  });

  it("parse - quotechar", function() {
    var csv = "'Jones, Jay',10\n" +
    "'Xyz \"ABC\" O''Brien',11:35\n" +
    "'Other; AN',12:35\n";

    var array = CSV.parse(csv, {quotechar:"'"});
    var exp = [
      ["Jones, Jay", 10],
      ["Xyz \"ABC\" O'Brien", "11:35" ],
      ["Other; AN", "12:35" ]
    ];
    expect(array).toEqual(exp);
  });

  it("parse skipInitialRows", function() {
    var csv = '"Jones, Jay",10\n' +
    '"Xyz ""ABC"" O\'Brien",11:35\n' +
    '"Other, AN",12:35\n';

    var array = CSV.parse(csv, {skipInitialRows: 1});
    var exp = [
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other, AN', '12:35' ]
    ];
    expect(array).toEqual(exp);
  });

  it("serialize - Array", function() {
    var csv = [
      ['Jones, Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other, AN', '12:35' ]
    ];

    var out = CSV.serialize(csv);
    var exp = '"Jones, Jay",10\n' +
    '"Xyz ""ABC"" O\'Brien",11:35\n' +
    '"Other, AN",12:35\n';
    expect(out).toEqual(exp);
  });

  it("serialize - Object", function() {
    var indata = {
      fields: [ {id: 'name'}, {id: 'number'}],
      records: [
        {name: 'Jones, Jay', number: 10},
        {name: 'Xyz "ABC" O\'Brien', number: '11:35' },
        {name: 'Other, AN', number: '12:35' }
      ]
    };

    var array = CSV.serialize(indata);
    var exp = 'name,number\n' +
    '"Jones, Jay",10\n' +
    '"Xyz ""ABC"" O\'Brien",11:35\n' +
    '"Other, AN",12:35\n';
    expect(array).toEqual(exp);
  });

  it("serialize - dialect options", function() {
    var csv = [
      ['Jones, Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ]
    ];

    var out = CSV.serialize(csv, {doubleQuote: false});
    var exp = '"Jones, Jay",10\n' +
    '"Xyz "ABC" O\'Brien",11:35\n'
    expect(out).toEqual(exp);
  });

  it("request fail", function(done){
    var dataset = {
      url: "http://fauxurlexample.com",
    };

    CSV.fetch(dataset).always(function(response, status){
      if(response.error) {
        var r = response.error.request;
        expect(r.status).toEqual(0);
        expect(r.readyState).toEqual(0);
      } else {
        throw new Error("Response should fail but it didn't")
      }
      done();
    });
  });

  it("parse custom lineterminator", function(){
    var csv = '"Jones, Jay",10\r' +
    '"Xyz ""ABC"" O\'Brien",11:35\r' +
    '"Other, AN",12:35\r';

    var exp = [
      ['Jones, Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other, AN', '12:35' ]
    ];

    var settings = {
      delimiter: ',',
      lineterminator: '\r',
    };

    var array = CSV.parse(csv, settings);
    expect(array).toEqual(exp);
  });

  it('normalizeDialectOptions', function() {
    var indata = {
    };
    var exp = {
      delimiter: ',',
      doublequote: true,
      lineterminator: '\n',
      quotechar: '"',
      skipinitialspace: true,
      skipinitialrows: 0
    }
    var out = CSV.normalizeDialectOptions(indata);
    expect(out).toEqual(exp);

    var indata = {
      doubleQuote: false,
      trim: false
    };
    var exp = {
      delimiter: ',',
      doublequote: false,
      lineterminator: '\n',
      quotechar: '"',
      skipinitialspace: false,
      skipinitialrows: 0
    }
    var out = CSV.normalizeDialectOptions(indata);
    expect(out).toEqual(exp);
  });

  it('normalizeLineTerminator', function() {
    var exp = [
      ['Jones, Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other, AN', '12:35' ]
    ];
    var csv, array;

    // Multics, Unix and Unix-like systems (Linux, OS X, FreeBSD, AIX, Xenix, etc.), BeOS, Amiga, RISC OS, and other
    csv = '"Jones, Jay",10\n' +
    '"Xyz ""ABC"" O\'Brien",11:35\n' +
    '"Other, AN",12:35\n';
    array = CSV.parse(csv);
    expect(array).toEqual(exp);

    // Commodore 8-bit machines, Acorn BBC, ZX Spectrum, TRS-80, Apple II family, Oberon, Mac OS up to version 9, and OS-9
    csv = '"Jones, Jay",10\r' +
    '"Xyz ""ABC"" O\'Brien",11:35\r' +
    '"Other, AN",12:35\r';
    array = CSV.parse(csv);
    expect(array).toEqual(exp);

    // Microsoft Windows, DOS (MS-DOS, PC DOS, etc.),
    csv = '"Jones, Jay",10\r\n' +
    '"Xyz ""ABC"" O\'Brien",11:35\r\n' +
    '"Other, AN",12:35\r\n';
    array = CSV.parse(csv);
    expect(array).toEqual(exp);

    // Override line terminator
    var settings = {
      delimiter: ',',
      lineterminator: '\r',
    };
    csv = '"Jones, Jay",10\r' +
    '"Xyz ""ABC"" O\'Brien",11:35\r' +
    '"Other, AN",12:35\r';
    array = CSV.parse(csv, settings);
    expect(array).toEqual(exp);

    // Nested mixed terminators
    var exp = [
      ['Jones,\n Jay', 10],
      ['Xyz "ABC" O\'Brien', '11:35' ],
      ['Other, AN', '12:35' ]
    ];
    csv = '"Jones,\n Jay",10\r' +
    '"Xyz ""ABC"" O\'Brien",11:35\r' +
    '"Other, AN",12:35\r';
    array = CSV.parse(csv, settings);
    expect(array).toEqual(exp);
  });

});