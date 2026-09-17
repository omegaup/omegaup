(function () {
  self.importScripts('karel.js');
  self.importScripts('../lib/tinyxmlsax.js');
  self.importScripts('../lib/tinyxmlw3cdom.js');

  var callbacks = {
    error: function (msg) {
      self.postMessage(
        JSON.stringify({ data: { type: 'error', message: msg } }),
      );
    },
    info: function (msg) {
      self.postMessage(
        JSON.stringify({ data: { type: 'info', message: msg } }),
      );
    },
    invalidCell: function (x, y) {
      self.postMessage(
        JSON.stringify({ data: { type: 'invalidCell', x: x, y: y } }),
      );
    },
  };

  self.addEventListener(
    'message',
    function (message) {
      try {
        var data = JSON.parse(message.data);
        self.importScripts(data.script);
        var world = new World(100, 100);
        var worldDoc = new DOMImplementation().loadXML(data.mundo);
        world.load(worldDoc);
        world.reset();
        self.postMessage(
          JSON.stringify({
            data: { type: 'result', value: validate(world, callbacks) },
          }),
        );
      } catch (ex) {
        console.error(ex, ex.name);
        self.postMessage(
          JSON.stringify({ error: (ex && (ex.message || ex.name)) || '' }),
        );
      }
    },
    false,
  );

  self.Worker = void 0;
}.call(this));
