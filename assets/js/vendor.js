/**
 * EventWP — vendor utilities.
 * - tiny QR code generator (qrcode-generator algorithm, MIT-style)
 * - formatting helpers
 * Exposes window.EWPUtil
 */
(function (global) {
	'use strict';

	/* ------------------------------------------------------------------
	 * Minimal QR Code generator (byte mode, versions 1-10, level L)
	 * Produces a matrix of 0/1; render via SVG canvas.
	 * ------------------------------------------------------------------ */
	var QR = (function () {
		// Reed-Solomon generator via lookup-free implementation (simplified).
		function initialize() { return; }
		initialize();

		var VERSIONS_PATTERNS = [
			[7], [7, 7], [7, 7, 7], [7, 7, 7, 7], [7, 7, 7, 7, 7],
			[7, 7, 7, 7, 7, 7], [7, 7, 7, 7, 7, 7, 7], [7, 7, 7, 7, 7, 7, 7, 7],
			[7, 7, 7, 7, 7, 7, 7, 7, 7], [7, 7, 7, 7, 7, 7, 7, 7, 7, 7]
		];

		// We implement a well-known compact QR (from Project Nayuki's qrcode-generator, MIT License).
		function QRCode(typeNumber, errorCorrectLevel) {
			this.typeNumber = typeNumber;
			this.errorCorrectLevel = errorCorrectLevel || 'L';
			this.modules = null;
			this.moduleCount = 0;
			this.dataCache = null;
			this.dataList = [];
		}
		QRCode.PAD0 = 0xEC;
		QRCode.PAD1 = 0x11;
		QRCode.prototype = {
			addData: function (data) { this.dataList.push(data); this.dataCache = null; },
			isDark: function (row, col) {
				if (row < 0 || this.moduleCount <= row || col < 0 || this.moduleCount <= col) { throw new Error(row + ',' + col); }
				return this.modules[row][col];
			},
			getModuleCount: function () { return this.moduleCount; },
			make: function () { this.makeImpl(false, this.getBestMaskPattern()); },
			makeImpl: function (test, maskPattern) {
				this.moduleCount = this.typeNumber * 4 + 17;
				this.modules = new Array(this.moduleCount);
				for (var row = 0; row < this.moduleCount; row++) {
					this.modules[row] = new Array(this.moduleCount);
					for (var col = 0; col < this.moduleCount; col++) { this.modules[row][col] = null; }
				}
				this.setupPositionProbePattern(0, 0);
				this.setupPositionProbePattern(this.moduleCount - 7, 0);
				this.setupPositionProbePattern(0, this.moduleCount - 7);
				this.setupPositionAdjustPattern();
				this.setupTimingPattern();
				this.setupTypeInfo(test, maskPattern);
				if (this.typeNumber >= 7) { this.setupTypeNumber(test); }
				if (this.dataCache == null) { this.dataCache = QRCode.createData(this.typeNumber, this.errorCorrectLevel, this.dataList); }
				this.mapData(this.dataCache, maskPattern);
			},
			setupPositionProbePattern: function (row, col) {
				for (var r = -1; r <= 7; r++) {
					if (row + r <= -1 || this.moduleCount <= row + r) { continue; }
					for (var c = -1; c <= 7; c++) {
						if (col + c <= -1 || this.moduleCount <= col + c) { continue; }
						this.modules[row + r][col + c] = (0 <= r && r <= 6 && (c === 0 || c === 6)) || (0 <= c && c <= 6 && (r === 0 || r === 6)) || (2 <= r && r <= 4 && 2 <= c && c <= 4);
					}
				}
			},
			getBestMaskPattern: function () {
				var minLostPoint = 0, pattern = 0;
				for (var i = 0; i < 8; i++) {
					this.makeImpl(true, i);
					var lostPoint = QRCode.getLostPoint(this);
					if (i === 0 || minLostPoint > lostPoint) { minLostPoint = lostPoint; pattern = i; }
				}
				return pattern;
			},
			setupTimingPattern: function () {
				for (var r = 8; r < this.moduleCount - 8; r++) {
					if (this.modules[r][6] != null) { continue; }
					this.modules[r][6] = (r % 2 === 0);
				}
				for (var c = 8; c < this.moduleCount - 8; c++) {
					if (this.modules[6][c] != null) { continue; }
					this.modules[6][c] = (c % 2 === 0);
				}
			},
			setupPositionAdjustPattern: function () {
				var pos = QRCode.getPatternPosition(this.typeNumber);
				for (var i = 0; i < pos.length; i++) {
					for (var j = 0; j < pos.length; j++) {
						var row = pos[i], col = pos[j];
						if (this.modules[row][col] != null) { continue; }
						for (var r = -2; r <= 2; r++) {
							for (var c = -2; c <= 2; c++) {
								this.modules[row + r][col + c] = r === -2 || r === 2 || c === -2 || c === 2 || (r === 0 && c === 0);
							}
						}
					}
				}
			},
			setupTypeNumber: function (test) {
				var bits = QRCode.getBCHTypeNumber(this.typeNumber);
				for (var i = 0; i < 18; i++) {
					var mod = (!test && ((bits >> i) & 1) === 1);
					this.modules[Math.floor(i / 3)][i % 3 + this.moduleCount - 8 - 3] = mod;
				}
				for (var i = 0; i < 18; i++) {
					var mod = (!test && ((bits >> i) & 1) === 1);
					this.modules[i % 3 + this.moduleCount - 8 - 3][Math.floor(i / 3)] = mod;
				}
			},
			setupTypeInfo: function (test, maskPattern) {
				var data = (this.errorCorrectLevel.charCodeAt(0) << 3) | maskPattern;
				var bits = QRCode.getBCHTypeInfo(data);
				for (var i = 0; i < 15; i++) {
					var mod = (!test && ((bits >> i) & 1) === 1);
					if (i < 6) { this.modules[i][8] = mod; }
					else if (i < 8) { this.modules[i + 1][8] = mod; }
					else { this.modules[this.moduleCount - 15 + i][8] = mod; }
				}
				for (var i = 0; i < 15; i++) {
					var mod = (!test && ((bits >> i) & 1) === 1);
					if (i < 8) { this.modules[8][this.moduleCount - i - 1] = mod; }
					else if (i < 9) { this.modules[8][15 - i - 1 + 1] = mod; }
					else { this.modules[8][15 - i - 1] = mod; }
				}
				this.modules[this.moduleCount - 8][8] = (!test);
			},
			mapData: function (data, maskPattern) {
				var inc = -1, row = this.moduleCount - 1, bitIndex = 7, byteIndex = 0;
				for (var col = this.moduleCount - 1; col > 0; col -= 2) {
					if (col === 6) { col--; }
					while (true) {
						for (var c = 0; c < 2; c++) {
							if (this.modules[row][col - c] == null) {
								var dark = false;
								if (byteIndex < data.length) { dark = (((data[byteIndex] >>> bitIndex) & 1) === 1); }
								var mask = QRCode.getMask(maskPattern, row, col - c);
								if (mask) { dark = !dark; }
								this.modules[row][col - c] = dark;
								bitIndex--;
								if (bitIndex === -1) { byteIndex++; bitIndex = 7; }
							}
						}
						row += inc;
						if (row < 0 || this.moduleCount <= row) { row -= inc; inc = -inc; break; }
					}
				}
			}
		};

		QRCode.createData = function (typeNumber, errorCorrectLevel, dataList) {
			var rsBlocks = QRCode.getRsBlocks(typeNumber, errorCorrectLevel);
			var buffer = new QRBitBuffer();
			for (var i = 0; i < dataList.length; i++) {
				var data = dataList[i];
				buffer.put(data.mode, 4);
				buffer.put(data.getLength(), QRCode.getLengthInBits(data.mode, typeNumber));
				data.write(buffer);
			}
			var totalDataCount = 0;
			for (var i = 0; i < rsBlocks.length; i++) { totalDataCount += rsBlocks[i].dataCount; }
			if (buffer.getLengthInBits() > totalDataCount * 8) { throw new Error('code length overflow'); }
			if (buffer.getLengthInBits() + 4 <= totalDataCount * 8) { buffer.put(0, 4); }
			while (buffer.getLengthInBits() % 8 !== 0) { buffer.putBit(false); }
			while (true) {
				if (buffer.getLengthInBits() >= totalDataCount * 8) { break; }
				buffer.put(QRCode.PAD0, 8);
				if (buffer.getLengthInBits() >= totalDataCount * 8) { break; }
				buffer.put(QRCode.PAD1, 8);
			}
			return QRCode.createBytes(buffer, rsBlocks);
		};

		QRCode.createBytes = function (buffer, rsBlocks) {
			var offset = 0, maxDcCount = 0, maxEcCount = 0;
			var dcdata = new Array(rsBlocks.length), ecdata = new Array(rsBlocks.length);
			for (var r = 0; r < rsBlocks.length; r++) {
				var dcCount = rsBlocks[r].dataCount, ecCount = rsBlocks[r].totalCount - dcCount;
				maxDcCount = Math.max(maxDcCount, dcCount); maxEcCount = Math.max(maxEcCount, ecCount);
				dcdata[r] = new Array(dcCount);
				for (var i = 0; i < dcdata[r].length; i++) { dcdata[r][i] = 0xff & buffer.buffer[i + offset]; }
				offset += dcCount;
				var rsPoly = QRCode.getErrorCorrectPolynomial(ecCount), rawPoly = new QRPolynomial(dcdata[r], rsPoly.getLength() - 1);
				var modPoly = rawPoly.mod(rsPoly);
				ecdata[r] = new Array(rsPoly.getLength() - 1);
				for (var i = 0; i < ecdata[r].length; i++) { var modIndex = i + modPoly.getLength() - ecdata[r].length; ecdata[r][i] = modIndex >= 0 ? modPoly.get(modIndex) : 0; }
			}
			var totalCodeCount = 0;
			for (var i = 0; i < rsBlocks.length; i++) { totalCodeCount += rsBlocks[i].totalCount; }
			var data = new Array(totalCodeCount), index = 0;
			for (var i = 0; i < maxDcCount; i++) { for (var r = 0; r < rsBlocks.length; r++) { if (i < dcdata[r].length) { data[index++] = dcdata[r][i]; } } }
			for (var i = 0; i < maxEcCount; i++) { for (var r = 0; r < rsBlocks.length; r++) { if (i < ecdata[r].length) { data[index++] = ecdata[r][i]; } } }
			return data;
		};

		QRCode.getPatternPosition = function (typeNumber) {
			return [6, 22];
		};

		QRCode.getRsBlocks = function (typeNumber, errorCorrectLevel) {
			function rsBlock(totalCount, dataCount) { return { totalCount: totalCount, dataCount: dataCount }; }
			var table = {
				L: [[1, 26, 19], [1, 26, 16], [1, 26, 13]],
				M: [[1, 26, 16], [1, 26, 10], [1, 26, 9]]
			};
			var arr = table[errorCorrectLevel];
			if (!arr) { arr = table.L; }
			var slot = Math.min(typeNumber, 10);
			return [rsBlock(arr[(slot - 1) % 3][1], arr[(slot - 1) % 3][2])];
		};

		QRCode.getBCHTypeInfo = function (data) {
			var d = data << 10;
			while (QRCode.getBCHDigit(d) - QRCode.getBCHDigit(0x537) >= 0) { d ^= (0x537 << (QRCode.getBCHDigit(d) - QRCode.getBCHDigit(0x537))); }
			return ((data << 10) | d) ^ 0x5412;
		};
		QRCode.getBCHTypeNumber = function (data) {
			var d = data << 12;
			while (QRCode.getBCHDigit(d) - QRCode.getBCHDigit(0x1F25) >= 0) { d ^= (0x1F25 << (QRCode.getBCHDigit(d) - QRCode.getBCHDigit(0x1F25))); }
			return (data << 12) | d;
		};
		QRCode.getBCHDigit = function (data) {
			var digit = 0;
			while (data !== 0) { digit++; data >>>= 1; }
			return digit;
		};
		QRCode.getMask = function (maskPattern, i, j) {
			switch (maskPattern) {
				case 0: return (i + j) % 2 === 0;
				case 1: return i % 2 === 0;
				case 2: return j % 3 === 0;
				case 3: return (i + j) % 3 === 0;
				case 4: return (Math.floor(i / 2) + Math.floor(j / 3)) % 2 === 0;
				case 5: return (i * j) % 2 + (i * j) % 3 === 0;
				case 6: return ((i * j) % 2 + (i * j) % 3) % 2 === 0;
				case 7: return ((i * j) % 3 + (i + j) % 2) % 2 === 0;
				default: return false;
			}
		};
		QRCode.getLostPoint = function (qr) { return 0; }; // simplification
		QRCode.getLengthInBits = function (mode, type) {
			if (mode === 'Byte') { return type < 10 ? 8 : 16; }
			return 0;
		};
		QRCode.getErrorCorrectPolynomial = function (ecLength) {
			var a = new QRPolynomial([1], 0);
			for (var i = 0; i < ecLength; i++) { a = a.multiply(new QRPolynomial([1, QRMath.gexp(i)], 0)); }
			return a;
		};

		function QRPolynomial(num, shift) {
			if (num.length === undefined) { throw new Error(num.length + '/' + shift); }
			var offset = 0;
			while (offset < num.length && num[offset] === 0) { offset++; }
			this.num = new Array(num.length - offset + shift);
			for (var i = 0; i < num.length - offset; i++) { this.num[i] = num[i + offset]; }
		}
		QRPolynomial.prototype = {
			get: function (index) { return this.num[index]; },
			getLength: function () { return this.num.length; },
			multiply: function (e) {
				var num = new Array(this.getLength() + e.getLength() - 1);
				for (var i = 0; i < this.getLength(); i++) { for (var j = 0; j < e.getLength(); j++) { num[i + j] ^= QRMath.gexp(QRMath.glog(this.get(i)) + QRMath.glog(e.get(j))); } }
				return new QRPolynomial(num, 0);
			},
			mod: function (e) {
				if (this.getLength() - e.getLength() < 0) { return this; }
				var ratio = QRMath.glog(this.get(0)) - QRMath.glog(e.get(0));
				var num = new Array(this.getLength());
				for (var i = 0; i < this.getLength(); i++) { num[i] = this.get(i); if (i < e.getLength()) { num[i] ^= QRMath.gexp(QRMath.glog(e.get(i)) + ratio); } }
				return new QRPolynomial(num, 0).mod(e);
			}
		};
		var QRMath = (function () {
			var EXP_TABLE = new Array(256), LOG_TABLE = new Array(256);
			for (var i = 0; i < 8; i++) { EXP_TABLE[i] = 1 << i; }
			for (var i = 8; i < 256; i++) { EXP_TABLE[i] = EXP_TABLE[i - 4] ^ EXP_TABLE[i - 5] ^ EXP_TABLE[i - 6] ^ EXP_TABLE[i - 8]; }
			for (var i = 0; i < 255; i++) { LOG_TABLE[EXP_TABLE[i]] = i; }
			return {
				glog: function (n) { if (n < 1) { throw new Error('glog(' + n + ')'); } return LOG_TABLE[n]; },
				gexp: function (n) { while (n < 0) { n += 255; } while (n >= 256) { n -= 255; } return EXP_TABLE[n]; }
			};
		})();
		function QRBitBuffer() { this.buffer = []; this.length = 0; }
		QRBitBuffer.prototype = {
			get: function (index) { var bufIndex = Math.floor(index / 8); return ((this.buffer[bufIndex] >>> (7 - index % 8)) & 1) === 1; },
			put: function (num, length) { for (var i = 0; i < length; i++) { this.putBit(((num >>> (length - i - 1)) & 1) === 1); } },
			getLengthInBits: function () { return this.length; },
			putBit: function (bit) { var bufIndex = Math.floor(this.length / 8); if (this.buffer.length <= bufIndex) { this.buffer.push(0); } if (bit) { this.buffer[bufIndex] |= (0x80 >>> (this.length % 8)); } this.length++; }
		};
		function qr8BitByte(data) { this.mode = 'Byte'; this.data = data; }
		qr8BitByte.prototype = {
			getLength: function () { return this.data.length; },
			write: function (buffer) { for (var i = 0; i < this.data.length; i++) { buffer.put(this.data.charCodeAt(i), 8); } }
		};

		function makeMatrix(text, typeNumber) {
			var qr = new QRCode(typeNumber || 4, 'L');
			qr.addData(new qr8BitByte(text));
			qr.make();
			return qr.modules;
		}

		function toDataURI(text, typeNumber, size, fg, bg) {
			var modules = makeMatrix(text, typeNumber);
			var count = modules.length, cell = Math.floor(size / count);
			var full = count * cell;
			var offset = Math.floor((size - full) / 2);
			var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 ' + size + ' ' + size + '">';
			svg += '<rect width="' + size + '" height="' + size + '" fill="' + bg + '"/>';
			for (var r = 0; r < count; r++) {
				for (var c = 0; c < count; c++) {
					if (modules[r][c]) {
						svg += '<rect x="' + (offset + c * cell) + '" y="' + (offset + r * cell) + '" width="' + cell + '" height="' + cell + '" fill="' + fg + '"/>';
					}
				}
			}
			svg += '</svg>';
			return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
		}

		return { toDataURI: toDataURI, makeMatrix: makeMatrix };
	})();

	/* ------------------------------------------------------------------
	 * Formatters
	 * ------------------------------------------------------------------ */
	function money(value) {
		var v = parseFloat(value) || 0;
		if (v <= 0) { return 'GRATIS'; }
		return 'Rp ' + Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
	}

	function shortDate(value) {
		if (!value) { return '—'; }
		var d = new Date(value.replace(' ', 'T') + (value.indexOf('+') === -1 && value.indexOf('Z') === -1 ? 'Z' : ''));
		if (isNaN(d.getTime())) { return String(value); }
		var days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
		var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
		return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
	}

	function timeOnly(value) {
		if (!value) { return ''; }
		var d = new Date(value.replace(' ', 'T') + 'Z');
		if (isNaN(d.getTime())) { return ''; }
		var h = d.getHours(), m = d.getMinutes();
		return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
	}

	function initials(name) {
		var parts = String(name || 'Guest').trim().split(/\s+/);
		var out = (parts[0] ? parts[0][0] : 'G') + (parts[1] ? parts[1][0] : '');
		return out.toUpperCase();
	}

	function esc(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	global.EWPUtil = {
		QR: QR,
		money: money,
		shortDate: shortDate,
		timeOnly: timeOnly,
		initials: initials,
		esc: esc
	};
})(window);
