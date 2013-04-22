var arrows = ['&#8678;', '&#8679;', '&#8680;', '&#8681;'];

var KarelIDE = function(elem, code, w, h) {
	var self = this;

	this.root = $(elem);
	
	self.running = false;
	self.interval = null;
	self.lastActiveLine = 0;
	self.breakpoints = {};
	self.world = new World(100, 100);
	self.karel = self.world.karel;
	self.w = w;
	self.h = h;
	self.di = 0;
	self.dj = 0;
	
	self.editor = CodeMirror.fromTextArea(code, {
		mode: 'text/x-karelruby',
		lineNumbers: true,
		tabSize: 4,
		gutters: ["CodeMirror-linenumbers", "breakpoints"]
	});
	
	self.editor.on("gutterClick", function(cm, n) {
		var info = cm.lineInfo(n);
		cm.setGutterMarker(n, "breakpoints", info.gutterMarkers ? null : makeMarker());
		self.breakpoints[n] = info.gutterMarkers ? false : true;
	});

	function makeMarker() {
		var marker = document.createElement("div");
		marker.innerHTML = "&#9679;";
		marker.className = "breakpoint";
		return marker;
	}
	
	function step() {
		if (!self.karel.step()) {
			clearInterval(self.interval);
			self.interval = null;
			self.running = false;
			
			$('.toolbar .play', self.root).button("option", { label: 'play', icons: {primary: 'ui-icon-play'}});
		}
		
		self.redraw();
	
		self.editor.removeLineClass(self.lastActiveLine, 'text', 'active');
		self.lastActiveLine = self.karel.state.line;
		self.editor.addLineClass(self.lastActiveLine, 'text', 'active');
		self.editor.scrollIntoView({line: self.lastActiveLine, pos: 0}, 5);
		
		if (self.breakpoints[self.lastActiveLine] || self.karel.debug) { 
			clearInterval(self.interval);
			self.interval = null;
		}
	}
	
	self.root.append(
		$('<div>')
			.attr('class', "toolbar ui-widget-header ui-corner-all")
			.append($('<button>')
				.addClass('play')
				.button({
					text: false,
					label: 'play',
					icons: {
						primary: "ui-icon-play"
					}
				})
				.click(function() {
					if (!self.running) {
						$(this).button("option", { label: 'pause', icons: {primary: 'ui-icon-pause'}});
						
						self.reset();
						self.karel.load(new karelruby.Parser().parse(self.editor.getValue()));
						self.running = true;
		
						self.interval = setInterval(step, 50);
					} else {
						$(this).button("option", { label: 'play', icons: {primary: 'ui-icon-play'}});
						
						if (self.interval) {
							clearInterval(self.interval);
							self.interval = null;
						}
					}
				})
			)
			.append($('<button>')
				.addClass('step')
				.button({
					text: false,
					label: 'step',
					icons: {
						primary: "ui-icon-seek-end"
					}
				})
				.click(function() {
					if (!self.running) {
						self.reset();
						self.karel.load(new karelruby.Parser().parse(self.editor.getValue()));
						self.running = true;
					}
					
					$('.toolbar .play', self.root).button("option", { label: 'play', icons: {primary: 'ui-icon-play'}});
		
					step();
				})
			)
			.append($('<button>')
				.addClass('run')
				.button({
					text: false,
					label: 'run',
					icons: {
						primary: "ui-icon-seek-next"
					}
				})
				.click(function() {
					if (!self.running) {
						self.reset();
						self.karel.load(new karelruby.Parser().parse(self.editor.getValue()));
						self.running = true;
					}
		
					while (self.karel.step() && !self.breakpoints[self.karel.state.line]);
					self.redraw();
					self.running = self.karel.state.running;
													
					$('.toolbar .play', self.root).button("option", { label: 'play', icons: {primary: 'ui-icon-play'}});
		
					self.editor.removeLineClass(self.lastActiveLine, 'text', 'active');
					self.lastActiveLine = self.karel.state.line;
					self.editor.addLineClass(self.lastActiveLine, 'text', 'active');
					self.editor.scrollIntoView({line: self.lastActiveLine, pos: 0}, 5);
				})
			)
			.append($('<button>')
				.addClass('stop')
				.button({
					text: false,
					label: 'stop',
					icons: {
						primary: "ui-icon-stop"
					}
				})
				.click(function() {
					self.running = false;
					$('.toolbar .play', self.root).button("option", { label: 'play', icons: {primary: 'ui-icon-play'}});
					if (self.interval) {
						clearInterval(self.interval);
						self.interval = null;
					}
					self.reset();
				})
			)
			.append($('<input>')
				.attr({'id': 'world_edit', 'type': 'checkbox'})
				.click(function() {
					var worldTable = $('table', self.root);
					
					if (!$(this).prop('checked')) {
						worldTable.off('click');
						worldTable.off('mousemove');
				
						worldTable.removeClass('edit');
					} else {
						worldTable.on('click', function (ev) {
							var dx = ev.clientX - worldTable.offset().left;
							var dy = ev.clientY - worldTable.offset().top;
							var jj = Math.floor(dx / 31) - 1 - self.dj;
							var ii = (20 - Math.floor(dy / 31) - 1) - self.di;

							var orientation = 4;

							if (ev.offsetX <= 10) {
								orientation = 0;
							} else if (ev.offsetY <= 10) {
								orientation = 1;
							} else if (ev.offsetX >= 20) {
								orientation = 2;
							} else if (ev.offsetY >= 20) {
								orientation = 3;
							}
						
							if (orientation != 4) {	
								self.toggleWall(ii, jj, orientation);
								self.world.dirty = true;
								self.redraw();
							} else {
								(function(ii, jj, target, self) {
									var buzzers = target.html();
									target.html('');
									var input = $('<input>')
										.addClass('buzzer-edit')
										.attr('type', 'text')
										.val(buzzers)
										.blur(function() {
											self.setBuzzers(ii, jj, parseInt($(this).val()));
											target.empty();
											self.redraw();
										})
										.keypress(function(ev) {
											if(!(0x30 <= ev.which && ev.which <= 0x39)) {
												self.setBuzzers(ii, jj, parseInt($(this).val()));
												target.empty();
												self.redraw();
											}
										});
									target.append(input);
									input.focus().select();
								})(ii, jj, $(ev.target), self);
							}
						});

						worldTable.on('mousemove', function (ev) {
							var orientation = 4;

							if (ev.offsetX <= 10) orientation = 0;
							else if (ev.offsetY % 31 <= 10) orientation = 1;
							else if (ev.offsetX >= 20) orientation = 2;
							else if (ev.offsetY >= 20) orientation = 3;

							var target = ev.target;
							$(target).removeClass('west north east south none').addClass(['west', 'north', 'east', 'south', 'none'][orientation]);
						});
				
						worldTable.addClass('edit');
					}
				})
			)
			.append($('<label for="world_edit">Editar</label>'))
			.append($('<input>')
				.attr('type', 'file')
				.button()
				.change(function (evt) {
					var reader = new FileReader();
					reader.onload = function(e) {
						self.load(e.target.result);
					};
					reader.readAsText(evt.target.files[0], 'UTF-8');
				})
			)
			.append($('<button>')
				.addClass('export')
				.button({
					text: false,
					label: 'export',
					icons: {
						primary: "ui-icon-copy"
					}
				})
				.click(function() {
					console.log(self.save());
				})
			)
			.append($('<button>')
				.addClass('left')
				.button({
					text: false,
					label: 'left',
					icons: {
						primary: "ui-icon-arrowthick-1-w"
					}
				})
				.click(function() {
					self.dj++;
					self.world.dirty = true;
					self.redraw();
				})
			)
			.append($('<button>')
				.addClass('down')
				.button({
					text: false,
					label: 'down',
					icons: {
						primary: "ui-icon-arrowthick-1-s"
					}
				})
				.click(function() {
					self.di++;
					self.world.dirty = true;
					self.redraw();
				})
			)
			.append($('<button>')
				.addClass('up')
				.button({
					text: false,
					label: 'up',
					icons: {
						primary: "ui-icon-arrowthick-1-n"
					}
				})
				.click(function() {
					self.di--;
					self.world.dirty = true;
					self.redraw();
				})
			)
			.append($('<button>')
				.addClass('right')
				.button({
					text: false,
					label: 'right',
					icons: {
						primary: "ui-icon-arrowthick-1-e"
					}
				})
				.click(function() {
					self.dj--;
					self.world.dirty = true;
					self.redraw();
				})
			)
	);
	
	$('#world_edit').button();
	
	var table = $('<table>');
	
	for (var i = 0; i < this.h; i++) {
		var tr = $('<tr>');
		tr.append($('<th>').addClass('row' + (this.h - i - 1)));
		for (var j = 0; j < this.w; j++) {
			var td = $('<td>').addClass('c' + (this.h - i - 1) + '_' + j).addClass('cell');
			tr.append(td);
		}
		table.append(tr);
	}
	
	var tr = $('<tr>');
	tr.append($('<th>'));
	for (var j = 0; j < this.w; j++) {
		tr.append($('<th>').addClass('col' + j));
	}
	table.append(tr);
	
	this.root.append(table);
	this.root.append($('<div>').addClass('karel'));
	this.world.load($('script', this.root)[0].textContent);
	this.redraw();
};

KarelIDE.prototype.redraw = function() {
	var self = this;
	
	var ii = self.karel.state.i + self.di;
	var jj = self.karel.state.j + self.dj;
	
	if (ii < 3 || (ii + 3) >= self.h) {
		self.di = self.h / 2 - self.karel.state.i;
		self.world.dirty = true;
	}
	if (jj < 3 || (jj + 3) >= self.w) {
		self.dj = self.w / 2 - self.karel.state.j;
		self.world.dirty = true;
	}

	if (self.world.dirty) {
		for (var i = 0; i < self.h; i++) {
			$('.row' + i, self.root).html(i - self.di);
			for (var j = 0; j < self.w; j++) {
				var cell = $('.c' + i + '_' + j, self.root);
				var ii = i - self.di;
				var jj = j - self.dj;
				var buzzers = self.world.buzzers(ii, jj);
				if (buzzers == 0) {
					cell.html('');
				} else if (buzzers == -1) {
					cell.html('&infin;');
				} else {
					cell.html(buzzers);
				}
				var walls = self.world.walls(ii, jj);
				cell.css('border-left-color', (walls & 0x1) ? '#000' : '#ccc');
				cell.css('border-top-color', (walls & 0x2) ? '#000' : '#ccc');
				cell.css('border-right-color', (walls & 0x4) ? '#000' : '#ccc');
				cell.css('border-bottom-color', (walls & 0x8) ? '#000' : '#ccc');
			}
		}
		for (var j = 0; j < self.w; j++) {
			$('.col' + j, self.root).html(j - self.dj);
		}
		self.world.dirty = false;
	}

	$('.karel', this.root).html(arrows[self.karel.state.orientation]);
	var cell = self.cell(self.karel.state.i, self.karel.state.j);
	if (cell) {
		$('.karel', this.root).offset(cell.offset()).show();
	} else {
		$('.karel', this.root).hide();
	}
};

KarelIDE.prototype.reset = function() {
	var self = this;

	self.world.reset();
	self.di = self.h / 2 - self.karel.startstate.i;
	self.dj = self.w / 2 - self.karel.startstate.j;

	self.redraw();
};

KarelIDE.prototype.cell = function(i, j) {
	var ii = parseInt(i) + this.di;
	var jj = parseInt(j) + this.dj;
	if (0 <= ii && ii < this.h && 0 <= jj && jj < this.w) {
		return $('.c' + ii + '_' + jj, this.root);
	} else {
		return null;
	}
};

$('body').ready(function() {
	var ide = new KarelIDE(document.getElementById('world'), document.getElementById('source'), 20, 20);
});
