/**
  * A class that implements the W3C DOM's EventTarget interface.
  * http://www.w3.org/TR/DOM-Level-2-Events/events.html#Events-EventTarget
  */
var EventTarget = function() {
	var self = this;
	self.listeners = {};
};

EventTarget.prototype.addEventListener = function(type, listener, useCapture) {
	var self = this;
	if (!self.listeners.hasOwnProperty(type)) {
		self.listeners[type] = [];
	}
	self.listeners[type].push(listener);
};

EventTarget.prototype.removeEventListener = function(type, listener, useCapture) {
	var self = this;
	if (self.listeners.hasOwnProperty(type)) {
		var index = self.listeners.indexOf(listener);
		if (index > -1) {
			self.listeners.splice(index, 1);
		}
	}
};

EventTarget.prototype.dispatchEvent = function(evt) {
	var self = this;
	if (self.listeners.hasOwnProperty(evt.type)) {
		for (var i = 0; i < self.listeners[evt.type].length; i++) {
			self.listeners[evt.type][i](evt);
		}
	}
};

/**
  * A class that holds the state of computation and executes opcodes.
  */
var Karel = function(world) {
	var self = this;

	self.debug = false;
	self.world = world;
	
	self.program = [['HALT']];
	self.startstate = {
		i: 0,
		j: 0,
		orientation: 0,
		buzzers: 0
	};
	
	self.reset();
};

Karel.prototype = new EventTarget();

Karel.prototype.move = function(i, j) {
	var self = this;

	self.state.i = self.startstate.i = parseInt(i);
	self.state.j = self.startstate.j = parseInt(j);
};

Karel.prototype.rotate = function(orientation) {
	var self = this;

	var orientations = ['OESTE', 'NORTE', 'ESTE', 'SUR'];
	self.state.orientation = self.startstate.orientation = Math.max(0, orientations.indexOf(orientation));
};

Karel.prototype.setBuzzers = function(buzzers) {
	var self = this;

	self.state.buzzers = self.startstate.buzzers = buzzers;
};

Karel.prototype.load = function(opcodes) {
	var self = this;

	self.program = opcodes;
	self.reset();
};

Karel.prototype.reset = function() {
	var self = this;

	self.state = {
		i: self.startstate.i,
		j: self.startstate.j,
		pc: 0,
		sp: -1,
		line: 0,
		stack: [],
		orientation: self.startstate.orientation,
		buzzers: self.startstate.buzzers,
		
		// Flags
		jumped: false,
		running: true
	};

	if (self.debug) {
		var ev = new Event('debug');
		ev.target = self;
		ev.message = JSON.stringify(self.program);
		self.dispatchEvent(ev);
	}
};

Karel.prototype.step = function() {
	var self = this;

	while (self.state.running) {
		self.next();
		
		if (self.state.running && self.program[self.state.pc][0] == 'LINE') {
			self.state.line = self.program[self.state.pc][1];
			break;
		}
	}
	
	return self.state.running;
};

Karel.prototype.next = function() {
	var self = this;

	if (!self.state.running) return;
	
	var world = self.world;
	
	var opcodes = {
		'HALT': function(state, params) {
			state.running = false;
		},
		
		'LINE': function(state, params) {
			state.line = params[0];
		},
		
		'LEFT': function(state, params) {
			state.orientation--;
			if (state.orientation < 0) {
				state.orientation = 3;
			}
		},
		
		'WORLDWALLS': function(state, params) {
			state.stack.push(world.walls(state.i, state.j));
		},
		
		'ORIENTATION': function(state, params) {
			state.stack.push(state.orientation);
		},
		
		'ROTL': function(state, params) {
			var rot = state.stack.pop() - 1;
			if (rot < 0) {
				rot = 3;
			}						
			state.stack.push(rot);
		},
		
		'ROTR': function(state, params) {
			var rot = state.stack.pop() + 1;
			if (rot > 3) {
				rot = 0;
			}						
			state.stack.push(rot);
		},
		
		'MASK': function(state, params) {
			state.stack.push(1 << state.stack.pop());
		},
		
		'NOT': function(state, params) {
			state.stack.push((state.stack.pop() == 0) ? 1 : 0);
		},
		
		'AND': function(state, params) {
			state.stack.push((state.stack.pop() & state.stack.pop()) ? 1 : 0);
		},
		
		'OR': function(state, params) {
			state.stack.push((state.stack.pop() | state.stack.pop()) ? 1 : 0);
		},
		
		'EQ': function(state, params) {
			state.stack.push((state.stack.pop() == state.stack.pop()) ? 1 : 0);
		},
		
		'EZ': function(state, params) {
			if (state.stack.pop() == 0) {
				state.error = params[0];
				state.running = false;
			}
		},
		
		'JZ': function(state, params) {
			if (state.stack.pop() == 0) {
				state.pc += params[0];
			}
		},
		
		'JNZ': function(state, params) {
			if (state.stack.pop() != 0) {
				state.pc += params[0];
			}
		},
		
		'JLEZ': function(state, params) {
			if (state.stack.pop() <= 0) {
				state.pc += params[0];
			}
		},
		
		'JMP': function(state, params) {
			state.pc += params[0];
		},
		
		'FORWARD': function(state, params) {
			var di = [0, 1, 0, -1];
			var dj = [-1, 0, 1, 0];
			
			state.i += di[state.orientation];
			state.j += dj[state.orientation];
		},
		
		'WORLDBUZZERS': function(state, params) {
			state.stack.push(world.buzzers(state.i, state.j));
		},
		
		'BAGBUZZERS': function(state, params) {
			state.stack.push(state.buzzers);
		},
		
		'PICKBUZZER': function(state, params) {
			world.pickBuzzer(state.i, state.j);
			if (state.buzzers != -1) {
				state.buzzers++;
			}
		},
		
		'LEAVEBUZZER': function(state, params) {
			world.leaveBuzzer(state.i, state.j);
			if (state.buzzers != -1) {
				state.buzzers--;
			}
		},
		
		'LOAD': function(state, params) {
			state.stack.push(params[0]);
		},
		
		'POP': function(state, params) {
			state.stack.pop();
		},
		
		'DUP': function(state, params) {
			state.stack.push(state.stack[state.stack.length - 1]);
		},
		
		'DEC': function(state, params) {
			state.stack.push(state.stack.pop() - 1);
		},
		
		'INC': function(state, params) {
			state.stack.push(state.stack.pop() + 1);
		},
		
		'CALL': function(state, params) {
			// sp, pc, param
			var param = state.stack.pop();
			var newSP = state.stack.length;
			
			state.stack.push(state.sp);
			state.stack.push(state.pc);
			state.stack.push(param);
			
			state.sp = newSP;
			state.pc = params[0];
			state.jumped = true;
		},
		
		'RET': function(state, params) {
			var oldSP = state.sp;
			state.pc = state.stack[state.sp + 1];
			state.sp = state.stack[state.sp];
			
			while (state.stack.length > oldSP) {
				state.stack.pop();
			}
		},
		
		'PARAM': function(state, params) {
			state.stack.push(state.stack[state.sp + 2 + params[0]]);
		},
	};
	
	try {
		var opcode = self.program[self.state.pc];
		if (!opcodes[opcode[0]]) {
			self.state.running = false;
			if (self.debug) {
				var ev = new Event('debug');
				ev.target = self;
				ev.message = 'Missing opcode ' + opcode[0];
				ev.debugType = 'opcode';
				self.dispatchEvent(ev);
			}
			return false;
		}
		
		opcodes[opcode[0]](self.state, opcode.slice(1));
	
		if (self.state.jumped) {
			self.state.jumped = false;
		} else {
			self.state.pc += 1;
		}
		
		if (self.debug) {
			var ev = new Event('debug');
			ev.target = self;
			ev.message = JSON.stringify(opcode);
			ev.debugType = 'opcode';
			self.dispatchEvent(ev);

			var ev2 = new Event('debug');
			ev2.target = self;
			ev2.message = JSON.stringify(self.state);
			self.dispatchEvent(ev2);
		}
	} catch (e) {
		self.state.running = false;
		throw e;
	}
	
	return true;
};

var World = function(w, h) {
	var self = this;

	self.w = w;
	self.h = h;
	self.karel = new Karel(self);
	if (ArrayBuffer) {
		self.map = new Int16Array(new ArrayBuffer(self.w * self.h * 2));
		self.currentMap = new Int16Array(new ArrayBuffer(self.w * self.h * 2));
		self.wallMap = new Uint8Array(new ArrayBuffer(self.w * self.h));
	} else {
		self.map = [];
		self.currentMap = [];
		self.wallMap = [];

		for (var i = 0; i < self.h; i++) {
			for (var j = 0; j < self.w; j++) {
				self.map.push(0);
				self.currentMap.push(0);
				self.wallMap.push(0);
			}
		}
	}
	self.dirty = true;
};

World.prototype.walls = function(i, j) {
	var self = this;

	if (0 > i || i >= self.h || 0 > j || j >= self.w) return 0;
	return self.wallMap[self.w * i + j];
};

World.prototype.toggleWall = function(i, j, orientation) {
	var self = this;

	if (orientation < 0 || orientation >= 4 || 0 > i || i >= self.h || 0 > j || j >= self.w) return;
	self.wallMap[self.w * i + j] ^= (1 << orientation);
	
	if (orientation == 0 && j > 1) {
		self.wallMap[self.w * i + (j - 1)] ^= (1 << 2);
	} else if (orientation == 1 && i < self.h) {
		self.wallMap[self.w * (i + 1) + j] ^= (1 << 3);
	} else if (orientation == 2 && j < self.w) {
		self.wallMap[self.w * i + (j + 1)] ^= (1 << 0);
	} else if (orientation == 3 && i > 1) {
		self.wallMap[self.w * (i - 1) + j] ^= (1 << 1);
	}
	
	self.dirty = true;
};

World.prototype.addWall = function(i, j, orientation) {
	var self = this;

	if (orientation < 0 || orientation >= 4 || 0 > i || i >= self.h || 0 > j || j >= self.w) return;
	self.wallMap[self.w * i + j] |= (1 << orientation);
	
	if (orientation == 0 && j > 1) self.wallMap[self.w * i + (j - 1)] |= (1 << 2);
	else if (orientation == 1 && i < self.h) self.wallMap[self.w * (i + 1) + j] |= (1 << 3);
	else if (orientation == 2 && j < self.w) self.wallMap[self.w * i + (j + 1)] |= (1 << 0);
	else if (orientation == 3 && i > 1) self.wallMap[self.w * (i - 1) + j] |= (1 << 1);
	
	self.dirty = true;
};

World.prototype.setBuzzers = function(i, j, count) {
	var self = this;

	if (0 > i || i >= self.h || 0 > j || j >= self.w) return;
	self.map[self.w * i + j] = self.currentMap[self.w * i + j] = count;
	self.dirty = true;
};

World.prototype.buzzers = function(i, j) {
	var self = this;

	if (0 > i || i >= self.h || 0 > j || j >= self.w) return 0;
	return self.currentMap[self.w * i + j];
};

World.prototype.pickBuzzer = function(i, j) {
	var self = this;

	if (0 > i || i >= self.h || 0 > j || j >= self.w) return;
	if (self.currentMap[self.w * i + j] != -1)
		self.currentMap[self.w * i + j]--;
	self.dirty = true;
};

World.prototype.leaveBuzzer = function(i, j) {
	var self = this;

	if (0 > i || i >= self.h || 0 > j || j >= self.w) return;
	if (self.currentMap[self.w * i + j] != -1)
		self.currentMap[self.w * i + j]++;
	self.dirty = true;
};

World.prototype.load = function(text) {
	var self = this;

	self.xml = $.parseXML(text);
	
	for (var i = 0; i < self.wallMap.length; i++) {
		self.wallMap[i] = 0;
	}
	
	for (var i = 0; i < self.map.length; i++) {
		self.map[i] = self.currentMap[i] = 0;
	}
	
	for (var i = 0; i < self.h; i++) {
		self.addWall(1, i, 3);
		self.addWall(i, 1, 0);
		self.addWall(self.h - 1, i, 1);
		self.addWall(i, self.w - 1, 2);
	}
	
	$('monton', self.xml).each(function(index) {
		var monton = $(this);
		var i = parseInt(monton.attr('y'));
		var j = parseInt(monton.attr('x'));
		self.setBuzzers(i, j, parseInt(monton.attr('zumbadores')));
	});
	
	$('pared', self.xml).each(function(index) {
		var pared = $(this);
		var i = parseInt(pared.attr('y1')) + 1;
		var j = parseInt(pared.attr('x1')) + 1;
		
		if (pared.attr('x2')) {
			var j2 = parseInt(pared.attr('x2')) + 1;
			
			if (j2 > j) self.addWall(i, j, 3);
			else self.addWall(i, j2, 3);
		} else if(pared.attr('y2')) {
			var i2 = parseInt(pared.attr('y2')) + 1;
			
			if (i2 > i) self.addWall(i, j, 0);
			else self.addWall(i2, j, 0);
		}
	});
	
	var programa = $('programa', self.xml);
	self.di = self.h / 2 - parseInt(programa.attr('yKarel'));
	self.dj = self.w / 2 - parseInt(programa.attr('xKarel'));
	self.karel.rotate(programa.attr('direccionKarel'));
	self.karel.move(parseInt(programa.attr('yKarel')), parseInt(programa.attr('xKarel')));
	
	self.reset();
};

World.prototype.save = function() {
	var self = this;

	var root = self.xml;
	var mundo = $('mundo', self.xml).empty();
	
	for (var i = 0; i < self.h; i++) {
		for (var j = 0; j < self.w; j++) {
			var buzzers = self.world.buzzers(i, j);
			if (buzzers == -1) {
				mundo.append($('<' + 'monton x="' + j + '" y="' + i + '" zumbadores="INFINITO" /' + '>'));
			} else if (buzzers > 0) {
				mundo.append($('<' + 'monton x="' + j + '" y="' + i + '" zumbadores="' + buzzers + '" /' + '>'));
			}
		}
	}
	
	for (var i = 0; i < self.h; i++) {
		for (var j = 0; j < self.w; j++) {
			var walls = self.world.walls(i, j);
			for (var k = 1; k < 16; k <<= 1) {
				if ((walls & k) == k) {
					mundo.append($('<' + 'pared x1="' + j + '" y1="' + i + '" zumbadores="INFINITO" /' + '>'));
				}
			}
		}
	}
	
	function serialize(xml, indentation) {
		if (xml.nodeType == xml.TEXT_NODE) return "";
		
		var result = "";
		for (var i = 0; i < indentation; i++) {
			result += "\t";
		}
		var childResult = "";
		
		for (var i = 0; i < xml.childNodes.length; i++) {
			childResult += serialize(xml.childNodes[i], indentation + 1);
		}
		
		result += "&lt;" + xml.nodeName;
		
		for (var i = 0; i < xml.attributes.length; i++) {
			result += ' ' + xml.attributes[i].name + '="' + xml.attributes[i].value + '"';
		}
		
		if (childResult == "") {
			result += " /&gt;\n";
		} else {
			result += ">\n";
			result += childResult;
			for (var i = 0; i < indentation; i++) {
				result += "\t";
			}
			result += "&lt;/" + xml.nodeName + "&gt;\n";
		}
		return result;
	}

	return serialize(root.documentElement, 0);
};

World.prototype.reset = function() {
	var self = this;
	
	self.karel.state.orientation = self.karel.startstate.orientation;
	self.karel.move(self.karel.startstate.i, self.karel.startstate.j);
	
	for (var i = 0; i < self.currentMap.length; i++) {
		self.currentMap[i] = self.map[i];
	}
	
	self.dirty = true;
};
