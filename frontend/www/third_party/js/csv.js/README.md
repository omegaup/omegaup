## CSV.js

Simple, ultra light (10kb uncompressed) javascript CSV library for browser and node with **zero dependencies**.

Originally developed as part of [ReclineJS][] but now fully standalone.

[ReclineJS]: http://okfnlabs.org/recline/

## Usage

Grab the `csv.js` file and include it in your application.

Depends on jQuery or underscore.deferred (for deferred) in fetch (and jQuery if
you need ajax). `parse` and `serialize` have zero dependencies.

### fetch

A convenient way to load a CSV file from various different sources. fetch
supports 3 options depending on the attribute provided on the info argument:

    CSV.fetch({
        data: 'raw csv string'
        // or ...
        url: 'url to a csv file'
        // or ...
        file: an HTML 5 file object

        // optional options about structure of the CSV file
        // following the CSV Dialect Description Format 
        // http://dataprotocols.org/csv-dialect/
        dialect: {
          ...
        }
      }
    ).done(function(dataset) {
      // dataset object doc'd below
      console.log(dataset);
    });

Some more detail on the argument object:

* `data` is a string in CSV format. This is passed directly to
  the CSV parser
* `url`: a url to an online CSV file that is ajax accessible (note this
  usually requires either local or on a server that is CORS enabled). The file
  is then loaded using jQuery.ajax and parsed using the CSV parser (NB: this
  requires jQuery) All options generates similar data and use the memory store
  outcome, that is they return something like:
* `file`: is an HTML5 file object. This is opened and parsed with the CSV
  parser.
* `dialect`: hash / dictionary following the same structure as for `parse`
  method below.

[csvddf]: http://dataprotocols.org/csv-dialect/

Returned `dataset` object looks like:

<pre>
{
  // an array of arrays - one array each row in the CSV
  // (excluding header row - i.e. first row)
  records: [ [...], [...], ... ],
  // list of fields
  fields: [ 'field-name-1', 'field-name-2', ... ],
  metadata: { may be some metadata e.g. file name }
}
</pre>

### Raw parsing

    var out = CSV.parse(csvString, dialect);

Converts a Comma Separated Values string into an array of arrays.  Each line in
the CSV becomes an array.

Empty fields are converted to nulls and non-quoted numbers are converted to
integers or floats.

* `csvString`: the csv string to parse
* `dialect`: [optional] hash with keys as per the [CSV dialect description
  format][csvddf]. It also supports the following additional keys:

  * `skipInitialRows`: [optional] integer number of rows to skip (default 0)

  For backwards compatability with earlier versions of the library the `dialect`
  also supports the following:

  * `trim`: mapped to `skipInitialSpace` in [CSV dialect description
    format][csvddf]

### Serialize

Convert an Object or a simple array of arrays into a Comma
Separated Values string.

    var out = CSV.serialize(dataToSerialize, dialect);

Returns a string representing the array serialized as a CSV.

`dataToSerialize` is an Object or array of arrays to convert. Object structure
must be as follows:

    {
      fields: [ {id: .., ...}, {id: ..., 
      records: [ { record }, { record }, ... ]
      ... // more attributes we do not care about
    }

  Nulls are converted to empty fields and integers or floats are converted to
  non-quoted numbers.

* `dialect`: dialect options for serializing the CSV file as per [CSV Dialect
  Description Format][csvddf]

----

## Other JS CSV Libs

* http://www.uselesscode.org/javascript/csv/ - basic CSV parser on which this library was originally based 
* https://github.com/maxogden/browser-csv-stream - Pure browser version of node-csv from @maxogden via browserify 
* https://github.com/onyxfish/csvkit.js - pure JS CSV reader from @onyxfish (author of the "legendary" python csvkit)
* https://github.com/mholt/PapaParse - fast CSV parser that can handle large files and malformed data

### Node

* https://github.com/wdavidw/node-csv - this is the Node CSV lib we use by preference
* https://github.com/maxogden/binary-csv - new CSV lib from @maxogden with a focus on being very fast

### Development
**Requirements**
* webpack
* jquery
```
npm install
npm install jquery
webpack-dev-server
```

### Run tests
**Requirements**
* karma
* phantomjs

```
npm -g install karma karma-cli phantomjs-prebuilt
npm install
npm install jquery
npm test
```
