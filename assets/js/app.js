/**
 * EventWP — Active Nation Sport Event App (SPA)
 * Vanilla JS, mobile-first, hash-routed. Talks to the WordPress REST API.
 */
(function () {
	'use strict';

	if (typeof EVENTWP === 'undefined') {
		return;
	}
	var ROOT = document.getElementById('ewp-root');
	if (!ROOT) {
		return;
	}

	var U = (typeof window.EWPUtil !== 'undefined') ? window.EWPUtil : {
		QR: { toDataURI: function (t) { return 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#fff"/><text x="150" y="150" font-size="12" text-anchor="middle">' + t.slice(0, 20) + '</text></svg>'); } },
		money: function (v) { var x = parseFloat(v) || 0; return x <= 0 ? 'GRATIS' : 'Rp ' + Math.round(x).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
		shortDate: function (v) { return v || '—'; },
		timeOnly: function (v) { return ''; },
		initials: function (n) { var p = String(n || 'G').trim().split(/\s+/); return ((p[0] ? p[0][0] : 'G') + (p[1] ? p[1][0] : '')).toUpperCase(); },
		esc: function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	};

	/* ------------------------------------------------------------------
	 * Icons (Feather-style inline SVG)
	 * ------------------------------------------------------------------ */
	var ICONS = {
		home: '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
		ticket: '<path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2 2.5 2.5 0 0 0 0 5 2 2 0 0 1-2 2H5a2 2 0 0 1-2-2 2.5 2.5 0 0 0 0-5Z"/><path d="M13 6.5v1.8m0 3.4v1.8m0 3.4v1.8"/>',
		calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
		pin: '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
		search: '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
		users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
		star: '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
		qr: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3z"/><path d="M17 17h4v4h-4z"/>',
		chev: '<polyline points="9 18 15 12 9 6"/>',
		back: '<polyline points="15 18 9 12 15 6"/>',
		check: '<polyline points="20 6 9 17 4 12"/>',
		plus: '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
		minus: '<line x1="5" y1="12" x2="19" y2="12"/>',
		wallet: '<rect x="2" y="6" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
		clock: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
		phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
		camera: '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
		clipboard: '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>',
		shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		bell: '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
		arrow: '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
		card: '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
		activity: '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
		award: '<circle cx="12" cy="8" r="6"/><path d="M15.5 13 17 22l-5-3-5 3 1.5-9"/>',
		eye: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
		x: '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
		whatsapp: '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
		download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
		grid: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'
	};

	function icon(name, size) {
		var path = ICONS[name] || ICONS.bolt;
		return '<svg viewBox="0 0 24 24" width="' + (size || 22) + '" height="' + (size || 22) + '" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + path + '</svg>';
	}

	/* ------------------------------------------------------------------
	 * State
	 * ------------------------------------------------------------------ */
	var state = {
		token: null,
		user: null,
		route: 'home',
		categories: [],
		payments: [],
		events: [],
		cities: ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Bali', 'Semua'],
		config: { brand: 'Active Nation', city: 'Jakarta', max_tickets: 5 },
		search: '',
		category: 'all',
		eventId: null,
		qty: 1,
		voucher: '',
		voucherResult: null,
		paymentMethod: '',
		proof: ''
	};

	function persist() {
		try {
			if (state.token) { localStorage.setItem('eventwp_token', state.token); }
			else { localStorage.removeItem('eventwp_token'); }
		} catch (e) { /* storage may be blocked */ }
	}

	function restore() {
		try { state.token = localStorage.getItem('eventwp_token'); } catch (e) { state.token = null; }
	}

	/* ------------------------------------------------------------------
	 * API
	 * ------------------------------------------------------------------ */
	function api(path, method, body) {
		var opts = {
			method: method || 'GET',
			headers: {
				'X-WP-Nonce': EVENTWP.nonce || ''
			}
		};
		if (state.token) {
			opts.headers.Authorization = 'Bearer ' + state.token;
		}
		if (body !== undefined) {
			opts.headers['Content-Type'] = 'application/json';
			opts.body = JSON.stringify(body);
		}
		return fetch(EVENTWP.root + path, opts).then(function (res) {
			return res.json().catch(function () { return { success: false, message: 'Respon server tidak valid.' }; }).then(function (json) {
				if (json && json.success === false && typeof json.message === 'string') {
					var err = new Error(json.message);
					err.code = res.status;
					throw err;
				}
				return json;
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Toast
	 * ------------------------------------------------------------------ */
	var toastTimer = null;
	function toast(msg, type) {
		var host = document.getElementById('ewp-toast');
		if (!host) {
			host = document.createElement('div');
			host.id = 'ewp-toast';
			host.setAttribute('role', 'status');
			host.setAttribute('aria-live', 'polite');
			document.body.appendChild(host);
		}
		host.innerHTML = '<div class="ewp-toast ewp-toast--' + (type || 'ok') + '">' + U.esc(msg) + '</div>';
		host.classList.add('is-show');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { host.classList.remove('is-show'); }, 3200);
	}

	/* ------------------------------------------------------------------
	 * Routing
	 * ------------------------------------------------------------------ */
	function parseHash() {
		var h = (location.hash || '#/').replace(/^#\/?/, '');
		var parts = h.split('/');
		return { path: parts[0] || '', param: parts[1] || '' };
	}

	function go(hash) {
		if (location.hash === hash) { render(); }
		else { location.hash = hash; }
	}

	function onRoute() {
		var r = parseHash();
		if (r.path === 'login' || r.path === 'register') { state.route = r.path; }
		else if (r.path === 'event') { state.route = 'event'; state.eventId = parseInt(r.param, 10) || null; }
		else if (r.path === 'tickets') { state.route = 'tickets'; }
		else if (r.path === 'history') { state.route = 'history'; }
		else if (r.path === 'profile') { state.route = 'profile'; }
		else { state.route = 'home'; }
		render();
	}

	/* ------------------------------------------------------------------
	 * Boot / data
	 * ------------------------------------------------------------------ */
	function boot() {
		restore();
		return Promise.all([
			api('config').then(function (r) { if (r && r.data) { state.config = r.data; } }).catch(noop),
			api('categories').then(function (r) { if (r && r.data) { state.categories = r.data; } }).catch(noop),
			api('payments', 'GET').then(function (r) { if (r && r.data) { state.payments = r.data; } }).catch(noop).catch(noop)
		]).then(function () {
			if (state.token) {
				return api('me').then(function (r) { if (r && r.data) { state.user = r.data; } }).catch(function () { state.user = null; state.token = null; persist(); });
			}
		});
	}

	function noop() {}

	/* ------------------------------------------------------------------
	 * Render
	 * ------------------------------------------------------------------ */
	function render() {
		ROOT.innerHTML = view();
		afterRender();
	}

	function view() {
		if (state.route === 'login') { return shell(viewAuth('login')); }
		if (state.route === 'register') { return shell(viewAuth('register')); }

		// Guard: require auth for inner routes.
		if (!state.user) {
			return shell(viewAuth('login'));
		}

		var inner = '';
		if (state.route === 'event' && state.eventId) { inner = viewEvent(); }
		else if (state.route === 'tickets') { inner = viewTickets(); }
		else if (state.route === 'history') { inner = viewHistory(); }
		else if (state.route === 'profile') { inner = viewProfile(); }
		else {
			if (state.user.role === 'instructor') { inner = viewInstructor(); }
			else if (state.user.role === 'admin') { inner = viewAdmin(); }
			else { inner = viewHome(); }
		}
		return shell(inner);
	}

	function shell(inner) {
		var isAuth = state.route === 'login' || state.route === 'register';
		if (isAuth) {
			return '<div class="ewp-app">' + inner + '</div>';
		}
		var nav = bottomNav();
		return '<div class="ewp-app">' + inner + nav + '</div>';
	}

	function bottomNav() {
		var homeActive = state.route === 'home' || state.route === 'event';
		var ticketsActive = state.route === 'tickets';
		var historyActive = state.route === 'history';
		var profileActive = state.route === 'profile';
		var items = '';
		if (state.user.role === 'instructor' || state.user.role === 'admin') {
			items = navItem('home', 'Beranda', homeActive, '#/') +
				navItem('tickets', state.user.role === 'admin' ? 'Pesanan' : 'Jadwal', ticketsActive, '#/tickets') +
				navItem('activity', 'Laporan', historyActive, '#/history') +
				navItem('user', state.user.role === 'admin' ? 'Panel' : 'Profil', profileActive, '#/profile');
		} else {
			items = navItem('home', 'Beranda', homeActive, '#/') +
				navItem('ticket', 'Tiket', ticketsActive, '#/tickets') +
				navItem('clock', 'Riwayat', historyActive, '#/history') +
				navItem('user', 'Profil', profileActive, '#/profile');
		}
		return '<nav class="ewp-bottomnav" aria-label="Navigasi utama">' + items + '</nav>';
	}

	function navItem(ic, label, active, href) {
		return '<a class="ewp-bottomnav__item' + (active ? ' is-active' : '') + '" href="' + href + '">' +
			icon(ic, 23) + '<span>' + label + '</span></a>';
	}

	/* ------------------------------------------------------------------
	 * Auth views
	 * ------------------------------------------------------------------ */
	function viewAuth(mode) {
		var login = mode === 'login';
		return '' +
			'<div class="ewp-auth">' +
			'  <div class="ewp-auth__bg" aria-hidden="true"><i></i><i></i><i></i></div>' +
			'  <div class="ewp-auth__card">' +
			'    <div class="ewp-brand"><span class="ewp-brand__mark">' + icon('activity', 22) + '</span><b>' + U.esc(state.config.brand || 'Active Nation') + '</b></div>' +
			'    <h1 class="ewp-auth__title">' + (login ? 'Selamat Datang Kembali 👋' : 'Daftar & Mulai Aktif 🎉') + '</h1>' +
			'    <p class="ewp-auth__sub">' + (login ? 'Login dengan nomor HP atau ID pengguna untuk melanjutkan.' : 'Buat akunmu — kami filter event berdasarkan kota domisilimu.') + '</p>' +
			(formAuth(login)) +
			'    <p class="ewp-auth__alt">' + (login ? 'Belum punya akun? ' : 'Sudah punya akun? ') + '<a href="' + (login ? '#/register' : '#/login') + '">' + (login ? 'Daftar sekarang' : 'Login') + '</a></p>' +
			'  </div>' +
			'  <p class="ewp-auth__hint">' + (login ? 'Demo: ID <b>6281230000003</b> · pass <b>customer123</b>' : 'Nomor HP aktif diperlukan untuk menerima e-ticket.') + '</p>' +
			'</div>';
	}

	function formAuth(login) {
		if (login) {
			return '<form class="ewp-form" id="ewp-login" novalidate>' +
				'<label class="ewp-field"><span>Nomor HP / ID Pengguna</span><input name="identifier" type="text" inputmode="tel" placeholder="62812xxxxxxx" required autocomplete="username" /></label>' +
				'<label class="ewp-field"><span>Kata Sandi</span><input name="password" type="password" placeholder="••••••••" required autocomplete="current-password" /></label>' +
				'<button class="ewp-btn-primary" type="submit">' + icon('arrow') + ' Masuk</button>' +
				'<p class="ewp-form__note">Demo instructor: <b>6281230000002</b> / <b>coach123</b></p>' +
				'</form>';
		}
		return '<form class="ewp-form" id="ewp-register" novalidate>' +
			'<label class="ewp-field"><span>Nama Lengkap</span><input name="name" type="text" placeholder="Nama kamu" required autocomplete="name" /></label>' +
			'<label class="ewp-field"><span>Nomor Handphone</span><input name="phone" type="tel" inputmode="tel" placeholder="62812xxxxxxx" required autocomplete="tel" /></label>' +
			'<label class="ewp-field"><span>Email (opsional)</span><input name="email" type="email" placeholder="nama@email.com" autocomplete="email" /></label>' +
			'<label class="ewp-field"><span>Kota Domisili</span>' +
			'  <select name="city">' + state.cities.map(function (c) { return '<option value="' + c + '"' + (c === state.config.city ? ' selected' : '') + '>' + c + '</option>'; }).join('') + '</select>' +
			'</label>' +
			'<label class="ewp-field"><span>Kata Sandi</span><input name="password" type="password" placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password" /></label>' +
			'<button class="ewp-btn-primary" type="submit">' + icon('check') + ' Daftar Akun</button>' +
			'</form>';
	}

	/* ------------------------------------------------------------------
	 * Customer — Home (Discovery)
	 * ------------------------------------------------------------------ */
	function viewHome() {
		loadEvents();
		var filtered = filteredEvents();
		var city = state.user ? state.user.city : '';
		return '' +
			'<header class="ewp-topbar">' +
			'  <div>' +
			'    <p class="ewp-topbar__hi">Halo, ' + U.esc(state.user.name.split(' ')[0]) + ' 👋</p>' +
			'    <h1 class="ewp-topbar__title">' + (city ? '<span class="ewp-chip-city">' + icon('pin', 13) + U.esc(city) + '</span>' : '') + ' Event di Kotamu</h1>' +
			'  </div>' +
			'  <button class="ewp-topbar__avatar" data-action="profile" aria-label="Profil">' + U.esc(U.initials(state.user.name)) + '</button>' +
			'</header>' +
			'<form class="ewp-search" id="ewp-search">' +
			'  ' + icon('search', 18) +
			'  <input type="search" placeholder="Cari event, lokasi, atau kategori…" value="' + U.esc(state.search) + '" name="q" aria-label="Cari event" />' +
			'</form>' +
			'<div class="ewp-chips" role="tablist" aria-label="Filter kategori">' +
			'  <button type="button" class="ewp-chip' + (state.category === 'all' ? ' is-active' : '') + '" data-cat="all">Semua</button>' +
			state.categories.map(function (c) {
				return '<button type="button" class="ewp-chip' + (state.category === c.slug ? ' is-active' : '') + '" data-cat="' + U.esc(c.slug) + '" style="--cc:' + U.esc(c.color || '#0a84ff') + '">' + U.esc(c.name) + '</button>';
			}).join('') +
			'</div>' +
			'<section class="ewp-list" aria-live="polite">' +
			'  <h2 class="ewp-list__head">Upcoming Events <span>' + filtered.length + '</span></h2>' +
			(filtered.length ? filtered.map(eventsCard).join('') : '<div class="ewp-empty"><div class="ewp-empty__icon">' + icon('search', 34) + '</div><p>Tidak ada event ditemukan.</p><small>Coba kata kunci lain atau ubah filter kategori.</small></div>') +
			'</section>';
	}

	function filteredEvents() {
		var list = state.events;
		if (state.search) {
			var q = state.search.toLowerCase();
			list = list.filter(function (e) {
				return (e.title + ' ' + e.location + ' ' + e.category_name).toLowerCase().indexOf(q) !== -1;
			});
		}
		if (state.category !== 'all') {
			list = list.filter(function (e) { return e.category_slug === state.category; });
		}
		if (state.category === 'all' && state.user && state.user.city && state.user.city !== 'Semua') {
			// Soft city filter (fallback to all if none match).
			var cityList = list.filter(function (e) { return (e.location || '').toLowerCase().indexOf(state.user.city.toLowerCase()) !== -1; });
			if (cityList.length) { list = cityList; }
		}
		return list;
	}

	function eventsCard(e) {
		var color = e.category_color || '#2f7bff';
		return '' +
			'<a class="ewp-evt" href="#/event/' + e.id + '" data-reveal>' +
			'  <span class="ewp-evt__media">' +
			'    <img src="' + U.esc(e.banner) + '" alt="" loading="lazy" />' +
			'    <span class="ewp-evt__cat" style="background:' + U.esc(color) + '">' + U.esc(e.category_name) + '</span>' +
			'    <span class="ewp-evt__price">' + U.esc(e.price_fmt) + '</span>' +
			'  </span>' +
			'  <span class="ewp-evt__body">' +
			'    <b class="ewp-evt__title">' + U.esc(e.title) + '</b>' +
			'    <span class="ewp-evt__meta">' + icon('calendar', 14) + U.esc(e.date_start_fmt) + '</span>' +
			'    <span class="ewp-evt__meta">' + icon('pin', 14) + U.esc(e.location) + '</span>' +
			'    <span class="ewp-evt__foot">' +
			'      <span class="ewp-progress"><i style="width:' + Math.min(100, e.progress) + '%"></i></span>' +
			'      <span class="ewp-evt__left">' + (e.left > 0 ? 'Sisa ' + e.left + ' slot' : 'Penuh') + '</span>' +
			'    </span>' +
			'  </span>' +
			'</a>';
	}

	function loadEvents() {
		if (state.events.length || state.eventsLoading) { return; }
		state.eventsLoading = true;
		api('events').then(function (r) {
			state.eventsLoading = false;
			if (r && r.data) { state.events = r.data; if (state.route === 'home') { render(); } }
		}).catch(function () { state.eventsLoading = false; });
	}

	/* ------------------------------------------------------------------
	 * Customer — Event detail
	 * ------------------------------------------------------------------ */
	var currentEvent = null;

	function viewEvent() {
		var e = currentEvent;
		if (!e || e.id !== state.eventId) { return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		var color = e.category_color || '#2f7bff';
		var formNotice = e.form_template_id ? '<div class="ewp-notice">' + icon('clipboard', 18) + '<span><b>Form persyaratan wajib</b> diisi setelah pembelian tiket.</span></div>' : '';
		var reviews = (e.reviews || []);
		var gallery = (e.gallery || []);

		return '' +
			'<div class="ewp-evt-hero">' +
			'  <img src="' + U.esc(e.banner) + '" alt="' + U.esc(e.title) + '" />' +
			'  <a class="ewp-backbtn" href="#/" aria-label="Kembali">' + icon('back', 20) + '</a>' +
			'  <span class="ewp-evt-hero__cat" style="background:' + U.esc(color) + '">' + U.esc(e.category_name) + '</span>' +
			'</div>' +
			'<div class="ewp-page">' +
			'  <h1 class="ewp-dt__title">' + U.esc(e.title) + '</h1>' +
			'  <div class="ewp-dt__meta">' +
			'    <span>' + icon('calendar', 15) + U.esc(e.date_start_fmt) + '</span>' +
			'    <span>' + icon('clock', 15) + U.esc(U.timeOnly(e.date_start)) + ' WIB</span>' +
			'  </div>' +
			'  <a class="ewp-dt__loc" href="' + U.esc(e.map_url) + '" target="_blank" rel="noopener">' + icon('pin', 17) + '<span><b>' + U.esc(e.location) + '</b><small>Buka di Google Maps →</small></span></a>' +
			formNotice +
			'  <h2 class="ewp-dt__section">Tentang Event</h2>' +
			'  <p class="ewp-dt__desc">' + U.esc(e.description) + '</p>' +
			'  <div class="ewp-dt__coach">' +
			'    <span class="ewp-avatar" style="background:' + U.esc(color) + '">' + U.esc(U.initials(e.instructor_name)) + '</span>' +
			'    <span><b>Coach ' + U.esc(e.instructor_name) + '</b><small>Instruktur · ' + U.esc(e.instructor_city || 'Jakarta') + '</small></span>' +
			'  </div>' +
			( gallery.length ? '<h2 class="ewp-dt__section">Galeri Event</h2><div class="ewp-gallery">' + gallery.map(function (g) { return '<img src="' + U.esc(g.image_url) + '" alt="' + U.esc(g.caption || 'Galeri event') + '" loading="lazy" />'; }).join('') + '</div>' : '' ) +
			( reviews.length ? '<h2 class="ewp-dt__section">Ulasan Peserta</h2><div class="ewp-reviews">' + reviews.map(reviewItem).join('') + '</div>' : '' ) +
			'  <div class="ewp-dt__spacer"></div>' +
			'</div>' +
			'<div class="ewp-bottombar">' +
			'  <div class="ewp-bottombar__info"><span>Harga Tiket</span><b>' + U.esc(e.price_fmt) + '</b></div>' +
			'  <button class="ewp-btn-primary" data-action="checkout-open"' + (e.left <= 0 ? ' disabled' : '') + '>' + (e.left <= 0 ? 'Habis' : 'Beli Tiket') + ' ' + icon('ticket', 18) + '</button>' +
			'</div>';
	}

	function reviewItem(r) {
		var stars = '';
		for (var i = 1; i <= 5; i++) { stars += '<i class="' + (i <= r.rating ? 'on' : '') + '">' + icon('star', 14) + '</i>'; }
		return '<div class="ewp-review"><div class="ewp-review__head"><b>' + U.esc(r.author) + '</b><span class="ewp-stars">' + stars + '</span></div><p>' + U.esc(r.comment) + '</p></div>';
	}

	function loadEvent(id) {
		return api('events/' + id).then(function (r) {
			if (r && r.data) { currentEvent = r.data; render(); }
		}).catch(function (err) { toast(err.message, 'err'); go('#/'); });
	}

	/* ------------------------------------------------------------------
	 * Checkout sheet
	 * ------------------------------------------------------------------ */
	function checkoutSheet() {
		var e = currentEvent;
		if (!e) { return; }
		var discount = state.voucherResult ? state.voucherResult.discount : 0;
		var subtotal = e.price * state.qty;
		var total = Math.max(0, subtotal - discount);
		var payments = state.payments.length ? state.payments : [];
		var chosen = state.paymentMethod || (payments[0] ? payments[0].id : '');
		var selectedPayment = null;
		for (var i = 0; i < payments.length; i++) { if (String(payments[i].id) === String(chosen)) { selectedPayment = payments[i]; } }

		return '<div class="ewp-sheet" id="ewp-checkout" role="dialog" aria-modal="true" aria-label="Checkout tiket">' +
			'<div class="ewp-sheet__scrim" data-action="sheet-close"></div>' +
			'<div class="ewp-sheet__panel">' +
			'  <div class="ewp-sheet__grab"></div>' +
			'  <div class="ewp-sheet__head"><h2>Checkout Tiket</h2><button class="ewp-close" data-action="sheet-close" aria-label="Tutup">' + icon('x', 18) + '</button></div>' +
			'  <div class="ewp-sheet__body">' +
			'    <div class="ewp-co-evt"><img src="' + U.esc(e.banner) + '" alt="" /><div><b>' + U.esc(e.title) + '</b><small>' + U.esc(e.date_start_fmt) + ' · ' + U.esc(e.location) + '</small></div></div>' +
			'    <div class="ewp-co-row"><span>Kuantitas <small>(maks ' + (state.config.max_tickets || 5) + ')</small></span>' +
			'      <div class="ewp-stepper">' +
			'        <button type="button" data-action="qty-minus" aria-label="Kurangi">' + icon('minus', 16) + '</button>' +
			'        <b id="ewp-qty">' + state.qty + '</b>' +
			'        <button type="button" data-action="qty-plus" aria-label="Tambah">' + icon('plus', 16) + '</button>' +
			'      </div></div>' +
			'    <div class="ewp-voucher">' +
			'      <input id="ewp-voucher-input" type="text" placeholder="Punya voucher? Masukkan kode" value="' + U.esc(state.voucher) + '" aria-label="Kode voucher" />' +
			'      <button type="button" data-action="apply-voucher">Klaim</button>' +
			'    </div>' +
			(state.voucherResult && state.voucherResult.voucher ? '<p class="ewp-voucher__ok">' + icon('check', 14) + ' Voucher <b>' + U.esc(state.voucherResult.voucher.code) + '</b> diterapkan.</p>' : (state.voucherResult && state.voucherResult.discount === 0 && state.voucher ? '<p class="ewp-voucher__err">Kode voucher tidak valid.</p>' : '')) +
			'    <h3 class="ewp-co-sub">Metode Pembayaran</h3>' +
			'    <div class="ewp-paylist">' +
			payments.map(function (p) {
				return '<button type="button" class="ewp-pay' + (String(p.id) === String(chosen) ? ' is-active' : '') + '" data-pay="' + p.id + '">' +
					'<span class="ewp-pay__ic">' + (p.type === 'qris' ? icon('qr', 18) : icon('card', 18)) + '</span>' +
					'<span class="ewp-pay__tx"><b>' + U.esc(p.name) + '</b><small>' + (p.type === 'qris' ? 'Scan QRIS' : U.esc(p.account_holder || '') + ' · ' + U.esc(p.account_number || '')) + '</small></span>' +
					'<span class="ewp-pay__radio"></span></button>';
			}).join('') +
			'    </div>' +
			(selectedPayment && selectedPayment.instructions ? '<p class="ewp-pay__note">' + U.esc(selectedPayment.instructions) + '</p>' : '') +
			'    <label class="ewp-upload">' +
			'      <input type="file" accept="image/*" data-action="proof-upload" />' +
			'      <span class="ewp-upload__box">' + (state.proof ? '<img src="' + state.proof + '" alt="Bukti" />' : icon('camera', 20) + '<b>Unggah Bukti Transfer</b><small>JPG/PNG, maks 5MB</small>') + '</span>' +
			'    </label>' +
			'    <div class="ewp-summary">' +
			'      <div><span>Subtotal</span><b>' + U.money(subtotal) + '</b></div>' +
			'      <div><span>Diskon Voucher</span><b class="ewp-summary__disc">- ' + U.money(discount) + '</b></div>' +
			'      <div class="ewp-summary__total"><span>Total Bayar</span><b>' + U.money(total) + '</b></div>' +
			'    </div>' +
			'    <button class="ewp-btn-primary ewp-btn-block" data-action="checkout-submit">Bayar & Dapatkan E-Ticket ' + icon('arrow', 18) + '</button>' +
			'    <p class="ewp-co__secure">' + icon('shield', 13) + ' Pembayaran divalidasi admin · E-ticket terbit setelah verifikasi</p>' +
			'  </div>' +
			'</div></div>';
	}

	/* ------------------------------------------------------------------
	 * Tickets
	 * ------------------------------------------------------------------ */
	var tickets = [];

	function viewTickets() {
		if (state.user.role === 'instructor') { return viewInstructorSchedule(); }
		if (state.user.role === 'admin') { return viewAdminOrders(); }
		if (!tickets.length) { loadTickets(); return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		return '' +
			'<header class="ewp-subhead"><a class="ewp-backbtn" href="#/" aria-label="Kembali">' + icon('back', 20) + '</a><h1>Tiket Aktif</h1><span></span></header>' +
			'<div class="ewp-page">' +
			(tickets.length ? tickets.map(ticketCard).join('') : emptyState('ticket', 'Belum ada tiket aktif', 'Beli tiket event pertamamu dan QR code akan muncul di sini.')) +
			'</div>';
	}

	function ticketCard(t) {
		var qr = '';
		try { qr = U.QR.toDataURI(t.qr || t.ticket_code || 'EVT', 4, 260, '#0b1224', '#ffffff'); } catch (e) { qr = ''; }
		return '<article class="ewp-ticket">' +
			'  <div class="ewp-ticket__head" style="--tc:' + U.esc(t.event.category_color || '#2f7bff') + '">' +
			'    <span class="ewp-ticket__cat">' + U.esc(t.event.category_name) + '</span>' +
			'    <span class="ewp-ticket__code">' + U.esc(t.ticket_code) + '</span>' +
			'  </div>' +
			'  <div class="ewp-ticket__body">' +
			'    <div class="ewp-ticket__qr"><img src="' + qr + '" alt="QR code tiket ' + U.esc(t.ticket_code) + '" width="180" height="180" /></div>' +
			'    <h3>' + U.esc(t.event.title) + '</h3>' +
			'    <p class="ewp-ticket__meta">' + icon('calendar', 14) + U.esc(t.event.date_start_fmt) + '</p>' +
			'    <p class="ewp-ticket__meta">' + icon('pin', 14) + U.esc(t.event.location) + '</p>' +
			'    <div class="ewp-ticket__ck">Kode Check-in<br/><b>' + U.esc(t.checkin_code || t.ticket_code) + '</b></div>' +
			'  </div>' +
			(t.requires_form ? '<div class="ewp-ticket__actions">' +
				(t.form_filled ? '<span class="ewp-tag-ok">' + icon('check', 14) + ' Form terisi</span>' : '<button class="ewp-btn-secondary" data-action="open-form" data-ticket="' + t.id + '">' + icon('clipboard', 16) + ' Isi Form Wajib</button>') +
				'</div>' : '') +
			'</article>';
	}

	function loadTickets() {
		api('tickets').then(function (r) { tickets = (r && r.data) || []; render(); }).catch(function (err) { toast(err.message, 'err'); });
	}

	/* ------------------------------------------------------------------
	 * Dynamic form
	 * ------------------------------------------------------------------ */
	function formSheet(ticket) {
		var fields = (ticket.required_form && ticket.required_form.fields) ? ticket.required_form.fields : [];
		var html = '';
		// Prefill from a previous partial submission if present.
		var existing = ticket.existing_responses || {};
		fields.forEach(function (f, idx) {
			var val = existing && typeof existing[f.label] !== 'undefined' ? existing[f.label] : '';
			if (f.type === 'select') {
				html += '<label class="ewp-field"><span>' + U.esc(f.label) + (f.required ? ' *' : '') + '</span><select name="f' + idx + '" data-label="' + U.esc(f.label) + '"' + (f.required ? ' required' : '') + '><option value="">Pilih…</option>' +
					(f.options || []).map(function (o) { return '<option value="' + U.esc(o) + '"' + (o === val ? ' selected' : '') + '>' + U.esc(o) + '</option>'; }).join('') + '</select></label>';
			} else {
				html += '<label class="ewp-field"><span>' + U.esc(f.label) + (f.required ? ' *' : '') + '</span><input name="f' + idx + '" data-label="' + U.esc(f.label) + '" type="text" placeholder="' + U.esc(f.label) + '" value="' + U.esc(val) + '"' + (f.required ? ' required' : '') + ' /></label>';
			}
		});
		if (!fields.length) {
			html = '<p class="ewp-empty-text">Template form belum memiliki field. Hubungi admin.</p>';
		}
		return '<div class="ewp-sheet" id="ewp-form-sheet" data-ticket="' + ticket.id + '" role="dialog" aria-modal="true" aria-label="Form persyaratan">' +
			'<div class="ewp-sheet__scrim" data-action="sheet-form-close"></div>' +
			'<div class="ewp-sheet__panel">' +
			'  <div class="ewp-sheet__grab"></div>' +
			'  <div class="ewp-sheet__head"><h2>Form Persyaratan</h2><button class="ewp-close" data-action="sheet-form-close" aria-label="Tutup">' + icon('x', 18) + '</button></div>' +
			'  <div class="ewp-sheet__body">' +
			'    <p class="ewp-voucher__ok">' + U.esc(ticket.event.title) + '</p>' +
			'    <form id="ewp-form-submit">' + html +
			'      <button class="ewp-btn-primary ewp-btn-block" type="submit">Kirim Form ' + icon('check', 18) + '</button>' +
			'    </form>' +
			'  </div>' +
			'</div></div>';
	}

	/* ------------------------------------------------------------------
	 * History (reviews + gallery)
	 * ------------------------------------------------------------------ */
	var history = [];

	function viewHistory() {
		if (!history.length) { loadHistory(); return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		return '' +
			'<header class="ewp-subhead"><a class="ewp-backbtn" href="#/" aria-label="Kembali">' + icon('back', 20) + '</a><h1>Riwayat Kehadiran</h1><span></span></header>' +
			'<div class="ewp-page">' +
			(history.length ? history.map(historyCard).join('') : emptyState('award', 'Belum ada riwayat', 'Event yang sudah kamu hadiri akan muncul di sini untuk diberi ulasan.')) +
			'</div>';
	}

	function historyCard(t) {
		return '<article class="ewp-history">' +
			'  <div class="ewp-history__head">' +
			'    <img src="' + U.esc(t.event.banner) + '" alt="" />' +
			'    <div><b>' + U.esc(t.event.title) + '</b><small>' + icon('calendar', 13) + U.esc(t.event.date_start_fmt) + '</small><span class="ewp-tag-ok">' + icon('check', 13) + ' Hadir</span></div>' +
			'  </div>' +
			'  <div class="ewp-history__rate" data-ticket-rate="' + t.id + '">' +
			'    <span>Berikan rating:</span>' +
			'    <div class="ewp-rate">' + [1, 2, 3, 4, 5].map(function (s) { return '<button type="button" data-star="' + s + '" aria-label="' + s + ' bintang">' + icon('star', 22) + '</button>'; }).join('') + '</div>' +
			'    <input class="ewp-rate__comment" type="text" placeholder="Tulis ulasanmu… (opsional)" data-comment />' +
			'    <button class="ewp-btn-secondary" data-action="submit-review" data-ticket="' + t.id + '">Kirim Ulasan</button>' +
			'  </div>' +
			'  <div class="ewp-history__share">' +
			'    <label class="ewp-btn-secondary ewp-btn-upload">' + icon('camera', 16) + ' Tambah Foto ke Galeri<input type="file" accept="image/*" data-upload-ticket="' + t.id + '" /></label>' +
			'  </div>' +
			'</article>';
	}

	function loadHistory() {
		api('tickets/history').then(function (r) { history = (r && r.data) || []; render(); }).catch(function (err) { toast(err.message, 'err'); });
	}

	/* ------------------------------------------------------------------
	 * Profile
	 * ------------------------------------------------------------------ */
	function viewProfile() {
		var u = state.user;
		return '' +
			'<header class="ewp-profile">' +
			'  <a class="ewp-backbtn" href="#/" aria-label="Kembali">' + icon('back', 20) + '</a>' +
			'  <span class="ewp-avatar ewp-avatar--lg" style="background:linear-gradient(135deg,#2f7bff,#00e0c6)">' + U.esc(U.initials(u.name)) + '</span>' +
			'  <h1>' + U.esc(u.name) + '</h1>' +
			'  <p><span class="ewp-badge-role">' + U.esc(u.role) + '</span> · ' + U.esc(u.city) + '</p>' +
			'</header>' +
			'<div class="ewp-page">' +
			'  <div class="ewp-menu-card">' +
			'    <div class="ewp-menu-row"><span>' + icon('phone', 17) + ' Nomor HP</span><b>' + U.esc(u.phone) + '</b></div>' +
			'    <div class="ewp-menu-row"><span>' + icon('user', 17) + ' ID Pengguna</span><b>#' + U.esc(String(u.id).padStart(5, '0')) + '</b></div>' +
			'    <div class="ewp-menu-row"><span>' + icon('pin', 17) + ' Kota Domisili</span><b>' + U.esc(u.city) + '</b></div>' +
			'  </div>' +
			'  <div class="ewp-menu-card">' +
			'    <a class="ewp-menu-row" href="#/tickets"><span>' + icon('ticket', 17) + ' Tiket Aktif</span>' + icon('chev', 16) + '</a>' +
			'    <a class="ewp-menu-row" href="#/history"><span>' + icon('clock', 17) + ' Riwayat Kehadiran</span>' + icon('chev', 16) + '</a>' +
			'    <a class="ewp-menu-row" href="' + U.esc(state.config.whatsapp_support || '#') + '" target="_blank" rel="noopener"><span>' + icon('whatsapp', 17) + ' Bantuan WhatsApp</span>' + icon('chev', 16) + '</a>' +
			'  </div>' +
			'  <button class="ewp-btn-logout" data-action="logout">' + icon('logout', 17) + ' Keluar</button>' +
			'  <p class="ewp-app-credit">' + U.esc(state.config.brand) + ' · v' + U.esc(state.config.version || '1.0') + '</p>' +
			'</div>';
	}

	/* ------------------------------------------------------------------
	 * Instructor panel
	 * ------------------------------------------------------------------ */
	var instructorData = { stats: null, schedule: [], feedback: [] };

	function viewInstructor() {
		if (!instructorData.stats) { loadInstructor(); return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		var s = instructorData.stats;
		return '' +
			'<header class="ewp-topbar">' +
			'  <div><p class="ewp-topbar__hi">Halo, Coach ' + U.esc(state.user.name.split(' ')[0]) + '</p><h1 class="ewp-topbar__title">Dashboard Instruktur</h1></div>' +
			'  <button class="ewp-topbar__avatar" data-action="profile" aria-label="Profil">' + U.esc(U.initials(state.user.name)) + '</button>' +
			'</header>' +
			'<div class="ewp-page">' +
			'  <div class="ewp-stats">' +
			'    <div class="ewp-stat"><b>' + s.total_classes + '</b><span>Total Kelas</span></div>' +
			'    <div class="ewp-stat"><b>' + s.active_attendees + '</b><span>Peserta Aktif</span></div>' +
			'    <div class="ewp-stat"><b>' + s.total_attended + '</b><span>Total Hadir</span></div>' +
			'  </div>' +
			'  <h2 class="ewp-list__head">Jadwal Kelas Kamu</h2>' +
			instructorData.schedule.map(scheduleRow).join('') +
			'  <h2 class="ewp-list__head">Feedback Peserta</h2>' +
			(instructorData.feedback.length ? instructorData.feedback.map(feedbackRow).join('') : '<p class="ewp-empty-text">Belum ada ulasan masuk.</p>') +
			'</div>';
	}

	function scheduleRow(e) {
		return '<div class="ewp-sched">' +
			'<div class="ewp-sched__head"><b>' + U.esc(e.title) + '</b><span>' + U.esc(e.date_start_fmt) + '</span></div>' +
			'<div class="ewp-sched__cap"><div class="ewp-progress"><i style="width:' + Math.min(100, e.progress) + '%"></i></div><small>' + e.sold + ' / ' + e.capacity + ' peserta</small></div>' +
			'</div>';
	}

	function feedbackRow(f) {
		return '<div class="ewp-fb"><div class="ewp-fb__head"><b>' + U.esc(f.title) + '</b><span class="ewp-fb__avg">' + icon('star', 14) + ' ' + f.avg_rating + ' (' + f.count + ')</span></div>' +
			(f.reviews || []).slice(0, 2).map(function (r) { return '<p class="ewp-fb__item">“' + U.esc(r.comment) + '”</p>'; }).join('') +
			'</div>';
	}

	function loadInstructor() {
		return Promise.all([
			api('me').then(function (r) { if (r && r.data && r.data.stats) { instructorData.stats = r.data.stats; } }).catch(noop),
			api('instructor/schedule').then(function (r) { instructorData.schedule = (r && r.data) || []; }).catch(noop),
			api('instructor/feedback').then(function (r) { instructorData.feedback = (r && r.data) || []; }).catch(noop)
		]).then(function () { render(); });
	}

	function viewInstructorSchedule() {
		if (!instructorData.stats) { loadInstructor(); return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		return '' +
			'<header class="ewp-subhead"><a class="ewp-backbtn" href="#/" aria-label="Kembali">' + icon('back', 20) + '</a><h1>Jadwal Kelas</h1><span></span></header>' +
			'<div class="ewp-page">' + instructorData.schedule.map(scheduleRow).join('') + '</div>';
	}

	/* ------------------------------------------------------------------
	 * Admin panel
	 * ------------------------------------------------------------------ */
	var adminData = { overview: null, orders: [], reports: [], audit: [] };

	function viewAdmin() {
		if (!adminData.overview) { loadAdmin(); return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		var o = adminData.overview;
		return '' +
			'<header class="ewp-topbar">' +
			'  <div><p class="ewp-topbar__hi">Panel Administrator</p><h1 class="ewp-topbar__title">Ringkasan Bisnis</h1></div>' +
			'  <button class="ewp-topbar__avatar" data-action="profile" aria-label="Profil">' + U.esc(U.initials(state.user.name)) + '</button>' +
			'</header>' +
			'<div class="ewp-page">' +
			'  <div class="ewp-revenue"><span>Total Pendapatan</span><b>' + U.money(o.revenue) + '</b></div>' +
			'  <div class="ewp-stats">' +
			'    <div class="ewp-stat"><b>' + o.events + '</b><span>Events</span></div>' +
			'    <div class="ewp-stat"><b>' + o.pending + '</b><span>Pending</span></div>' +
			'    <div class="ewp-stat"><b>' + o.tickets + '</b><span>Tiket</span></div>' +
			'    <div class="ewp-stat"><b>' + o.attended + '</b><span>Hadir</span></div>' +
			'    <div class="ewp-stat"><b>' + o.customers + '</b><span>Pelanggan</span></div>' +
			'    <div class="ewp-stat"><b>' + o.instructors + '</b><span>Instruktur</span></div>' +
			'  </div>' +
			'  <div class="ewp-menu-card">' +
			'    <a class="ewp-menu-row" href="#/tickets"><span>' + icon('card', 17) + ' Validasi Pesanan</span>' + icon('chev', 16) + '</a>' +
			'    <a class="ewp-menu-row" href="' + U.esc(EVENTWP.wpAdmin || '#') + '" target="_blank" rel="noopener"><span>' + icon('grid', 17) + ' Kelola Lengkap (wp-admin)</span>' + icon('chev', 16) + '</a>' +
			'    <button class="ewp-menu-row" data-action="backup"><span>' + icon('download', 17) + ' Export Backup JSON</span>' + icon('chev', 16) + '</button>' +
			'  </div>' +
			'  <h2 class="ewp-list__head">Check-in Peserta</h2>' +
			'  <form class="ewp-checkin" id="ewp-checkin">' +
			'    <input name="code" type="text" placeholder="Kode check-in / ID tiket" aria-label="Kode check-in" />' +
			'    <button class="ewp-btn-primary" type="submit">' + icon('qr', 17) + ' Check-in</button>' +
			'  </form>' +
			'  <p class="ewp-co__secure">' + icon('shield', 13) + ' Kehadiran tercatat real-time dan tersimpan di database.</p>' +
			'</div>';
	}

	function viewAdminOrders() {
		if (!adminData.overview) { loadAdmin(); return '<div class="ewp-center"><div class="ewp-spinner"></div></div>'; }
		return '' +
			'<header class="ewp-subhead"><a class="ewp-backbtn" href="#/" aria-label="Kembali">' + icon('back', 20) + '</a><h1>Validasi Pesanan</h1><span></span></header>' +
			'<div class="ewp-page">' +
			(adminData.orders.length ? adminData.orders.map(orderRow).join('') : emptyState('ticket', 'Tidak ada pesanan', 'Pesanan masuk akan muncul di sini untuk divalidasi.')) +
			'</div>';
	}

	function orderRow(o) {
		return '<article class="ewp-order">' +
			'  <div class="ewp-order__head"><b>' + U.esc(o.event_title) + '</b><span class="ewp-badge-' + o.status + '">' + o.status + '</span></div>' +
			'  <div class="ewp-order__meta"><span>' + U.esc(o.customer) + ' · ' + U.esc(o.customer_phone) + '</span><span>' + o.qty + ' tiket · ' + U.money(o.total) + '</span></div>' +
			(o.payment_proof ? '<a class="ewp-order__proof" href="' + U.esc(o.payment_proof) + '" target="_blank" rel="noopener">' + icon('eye', 14) + ' Lihat bukti transfer</a>' : '<span class="ewp-order__noproof">Belum ada bukti</span>') +
			(o.status === 'pending' ? '<div class="ewp-order__actions"><button class="ewp-btn-approve" data-order-approve="' + o.id + '">' + icon('check', 15) + ' Setujui</button><button class="ewp-btn-reject" data-order-reject="' + o.id + '">' + icon('x', 15) + ' Tolak</button></div>' : '') +
			'</article>';
	}

	function loadAdmin() {
		return Promise.all([
			api('admin/overview').then(function (r) { adminData.overview = (r && r.data) || {}; }).catch(noop),
			api('admin/orders').then(function (r) { adminData.orders = (r && r.data) || []; }).catch(noop)
		]).then(function () { render(); });
	}

	/* ------------------------------------------------------------------
	 * Shared partials
	 * ------------------------------------------------------------------ */
	function emptyState(ic, title, sub) {
		return '<div class="ewp-empty"><div class="ewp-empty__icon">' + icon(ic, 34) + '</div><p>' + title + '</p><small>' + sub + '</small></div>';
	}

	/* ------------------------------------------------------------------
	 * Actions / events (delegation)
	 * ------------------------------------------------------------------ */
	function afterRender() {
		attachReveal();
		var sheets = document.querySelectorAll('.ewp-sheet');
		if (sheets.length) {
			requestAnimationFrame(function () { sheets.forEach(function (s) { s.classList.add('is-open'); }); });
		}
	}

	function attachReveal() {
		if (!('IntersectionObserver' in window)) { return; }
		ROOT.querySelectorAll('[data-reveal]').forEach(function (el) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
				});
			}, { threshold: 0.1 });
			io.observe(el);
		});
	}

	/* Open event detail */
	function openEvent(id) {
		state.eventId = id;
		state.route = 'event';
		render();
		loadEvent(id);
	}

	/* Close sheet helper */
	function closeSheet() {
		var s = document.querySelector('.ewp-sheet');
		if (s) { s.classList.remove('is-open'); setTimeout(function () { s.remove(); }, 250); }
	}

	/* File → base64 data URL */
	function fileToDataURL(file) {
		return new Promise(function (resolve, reject) {
			if (!file) { return reject(new Error('empty')); }
			if (file.size > 5 * 1024 * 1024) { return reject(new Error('Maksimal 5MB')); }
			var reader = new FileReader();
			reader.onload = function () { resolve(reader.result); };
			reader.onerror = function () { reject(new Error('Gagal membaca file')); };
			reader.readAsDataURL(file);
		});
	}

	/* Open the required form sheet for a ticket */
	function openFormSheet(ticketId) {
		var t = null;
		tickets.forEach(function (x) { if (String(x.id) === String(ticketId)) { t = x; } });
		if (!t) { toast('Tiket tidak ditemukan.', 'err'); return; }
		var sheet = formSheet(t);
		document.body.insertAdjacentHTML('beforeend', sheet);
		// Re-find because existing sheet is re-rendered.
		var el = document.getElementById('ewp-form-sheet');
		requestAnimationFrame(function () { if (el) { el.classList.add('is-open'); } });
	}

	/* ------------------------------------------------------------------
	 * DOM event wiring
	 * ------------------------------------------------------------------ */
	ROOT.addEventListener('click', function (ev) {
		var t = ev.target.closest('[data-action]');
		if (!t) { return; }
		var action = t.getAttribute('data-action');
		switch (action) {
			case 'profile':
				go('#/profile');
				break;
			case 'checkout-open':
				state.qty = 1; state.voucher = ''; state.voucherResult = null; state.paymentMethod = ''; state.proof = '';
				document.body.insertAdjacentHTML('beforeend', checkoutSheet());
				requestAnimationFrame(function () { var el = document.getElementById('ewp-checkout'); if (el) { el.classList.add('is-open'); } });
				break;
			case 'sheet-close':
			case 'sheet-form-close':
				closeSheet();
				break;
			case 'qty-minus':
				state.qty = Math.max(1, state.qty - 1);
				setQtyUI();
				break;
			case 'qty-plus':
				state.qty = Math.min(state.config.max_tickets || 5, state.qty + 1);
				setQtyUI();
				break;
			case 'apply-voucher':
				applyVoucher();
				break;
			case 'checkout-submit':
				submitCheckout();
				break;
			case 'open-form':
				openFormSheet(t.getAttribute('data-ticket'));
				break;
			case 'submit-review':
				submitReview(t.getAttribute('data-ticket'));
				break;
			case 'logout':
				state.token = null; state.user = null; tickets = []; history = []; adminData = {}; instructorData = { stats: null, schedule: [], feedback: [] };
				persist();
				go('#/login');
				break;
			case 'backup':
				exportBackup();
				break;
			default:
				break;
		}
	});

	/* Payment select buttons inside sheet (data-pay) */
	document.body.addEventListener('click', function (ev) {
		var p = ev.target.closest('[data-pay]');
		if (p) {
			state.paymentMethod = p.getAttribute('data-pay');
			var sheet = document.getElementById('ewp-checkout');
			if (sheet) { sheet.innerHTML = checkoutSheet(); }
		}
		var cat = ev.target.closest('[data-cat]');
		if (cat && ROOT.contains(cat)) {
			state.category = cat.getAttribute('data-cat');
			render();
		}
		var orderApprove = ev.target.closest('[data-order-approve]');
		if (orderApprove) { validateOrder(orderApprove.getAttribute('data-order-approve'), 'approve'); }
		var orderReject = ev.target.closest('[data-order-reject]');
		if (orderReject) { validateOrder(orderReject.getAttribute('data-order-reject'), 'reject'); }

		var starBtn = ev.target.closest('[data-star]');
		if (starBtn) {
			var rateBox = starBtn.closest('.ewp-rate');
			var val = parseInt(starBtn.getAttribute('data-star'), 10);
			rateBox.querySelectorAll('button').forEach(function (b, i) { b.classList.toggle('is-on', i < val); });
			rateBox.setAttribute('data-value', val);
		}
	});

	/* Inputs & file uploads (body-level, since sheets are appended to body) */
	document.body.addEventListener('change', function (ev) {
		var fileInput = ev.target.closest('[data-action="proof-upload"]');
		if (fileInput && fileInput.files.length) {
			fileToDataURL(fileInput.files[0]).then(function (data) {
				state.proof = data;
				var sheet = document.getElementById('ewp-checkout');
				if (sheet) { sheet.innerHTML = checkoutSheet(); }
			}).catch(function (err) { toast(err.message, 'err'); });
		}
		var uploadTicket = ev.target.closest('[data-upload-ticket]');
		if (uploadTicket && uploadTicket.files.length) {
			var tid = uploadTicket.getAttribute('data-upload-ticket');
			var el = uploadTicket;
			fileToDataURL(uploadTicket.files[0]).then(function (data) {
				el.disabled = true;
				api('gallery', 'POST', { event_id: historyEventId(tid), image: data, caption: '' }).then(function (r) {
					toast(r.message || 'Foto diunggah ke galeri event.'); el.disabled = false;
				}).catch(function (err) { toast(err.message, 'err'); el.disabled = false; });
			}).catch(function (err) { toast(err.message, 'err'); });
		}
	});

	document.body.addEventListener('submit', function (ev) {
		var form = ev.target;
		if (form.id === 'ewp-login') {
			ev.preventDefault();
			var d = new FormData(form);
			submitLogin(d.get('identifier'), d.get('password'));
		} else if (form.id === 'ewp-register') {
			ev.preventDefault();
			var d = new FormData(form);
			submitRegister(d.get('name'), d.get('phone'), d.get('email'), d.get('city'), d.get('password'));
		} else if (form.id === 'ewp-search') {
			ev.preventDefault();
			var d = new FormData(form);
			state.search = (d.get('q') || '').trim();
			state.events = []; loadEvents(); render();
		} else if (form.id === 'ewp-form-submit') {
			ev.preventDefault();
			var d = new FormData(form);
			submitFormResponses(form, d);
		} else if (form.id === 'ewp-checkin') {
			ev.preventDefault();
			var d = new FormData(form);
			api('admin/checkin', 'POST', { code: (d.get('code') || '').trim() }).then(function (r) {
				toast(r.message || 'Check-in berhasil.'); ev.target.reset();
			}).catch(function (err) { toast(err.message, 'err'); });
		}
	});

	function setQtyUI() {
		var el = document.getElementById('ewp-qty');
		if (el) { el.textContent = state.qty; }
		var sheet = document.getElementById('ewp-checkout');
		if (sheet) { sheet.innerHTML = checkoutSheet(); }
	}

	/* ------------------------------------------------------------------
	 * Auth actions
	 * ------------------------------------------------------------------ */
	function submitLogin(identifier, password) {
		if (!identifier || !password) { toast('Lengkapi nomor HP/ID dan kata sandi.', 'err'); return; }
		btnBusy(true);
		api('auth/login', 'POST', { identifier: identifier, password: password }).then(function (r) {
			state.token = r.data.token; state.user = r.data.user; persist();
			resetCaches();
			toast('Login berhasil. Selamat beraktivitas! 👋');
			go('#/');
		}).catch(function (err) { toast(err.message, 'err'); }).then(function () { btnBusy(false); });
	}

	function submitRegister(name, phone, email, city, password) {
		if (!name || !phone || !password) { toast('Nama, nomor HP, dan kata sandi wajib diisi.', 'err'); return; }
		btnBusy(true);
		api('auth/register', 'POST', { name: name, phone: phone, email: email, city: city, password: password }).then(function (r) {
			state.token = r.data.token; state.user = r.data.user; persist();
			resetCaches();
			toast(r.message || 'Pendaftaran berhasil! 🎉');
			go('#/');
		}).catch(function (err) { toast(err.message, 'err'); }).then(function () { btnBusy(false); });
	}

	function btnBusy(busy) {
		var btn = document.querySelector('.ewp-auth button[type="submit"]');
		if (btn) { btn.disabled = busy; btn.style.opacity = busy ? 0.6 : 1; }
	}

	function resetCaches() {
		tickets = []; history = []; adminData = {}; instructorData = { stats: null, schedule: [], feedback: [] }; state.events = []; currentEvent = null;
	}

	/* ------------------------------------------------------------------
	 * Checkout
	 * ------------------------------------------------------------------ */
	function applyVoucher() {
		var input = document.getElementById('ewp-voucher-input');
		var code = input ? input.value.trim() : '';
		state.voucher = code;
		if (!code) { toast('Masukkan kode voucher terlebih dahulu.', 'err'); return; }
		var e = currentEvent;
		api('vouchers/check', 'POST', { code: code, subtotal: e.price * state.qty }).then(function (r) {
			state.voucherResult = { voucher: r.data.voucher, discount: r.data.discount };
			var sheet = document.getElementById('ewp-checkout');
			if (sheet) { sheet.innerHTML = checkoutSheet(); }
			if (r.data.voucher) { toast('Voucher diterapkan! 🎉'); }
			else { toast('Kode voucher tidak valid.', 'err'); }
		}).catch(function (err) { toast(err.message, 'err'); state.voucherResult = null; });
	}

	function submitCheckout() {
		var e = currentEvent;
		if (!e) { return; }
		var qty = state.qty;
		if (!state.paymentMethod && state.payments.length) { toast('Pilih metode pembayaran.', 'err'); return; }
		var body = {
			event_id: e.id,
			qty: qty,
			voucher_code: state.voucher || '',
			payment_method: state.paymentMethod || '',
			payment_proof: state.proof || ''
		};
		var btn = document.querySelector('[data-action="checkout-submit"]');
		if (btn) { btn.disabled = true; }
		api('checkout', 'POST', body).then(function (r) {
			closeSheet();
			showOrderSuccess(r.data);
		}).catch(function (err) { toast(err.message, 'err'); if (btn) { btn.disabled = false; } });
	}

	function showOrderSuccess(data) {
		var ticketsHtml = (data.tickets || []).map(function (t) { return '<div class="ewp-modal__ticket"><b>Tiket ' + (t.ticket_code || t.checkin_code) + '</b><small>Kode check-in: ' + (t.checkin_code || '—') + '</small></div>'; }).join('');
		var formNote = data.requires_form ? '<a class="ewp-btn-secondary" href="#/tickets">' + icon('clipboard', 16) + ' Isi Form Wajib Sekarang</a>' : '';
		var modal = '' +
			'<div class="ewp-sheet ewp-sheet--center" id="ewp-success" role="dialog" aria-modal="true" aria-label="Pesanan berhasil">' +
			'  <div class="ewp-sheet__scrim" data-action="success-close"></div>' +
			'  <div class="ewp-sheet__panel ewp-modal">' +
			'    <div class="ewp-modal__check">' + icon('check', 30) + '</div>' +
			'    <h2>Pesanan Dibuat! 🎉</h2>' +
			'    <p>' + U.esc(data.event.title) + ' × ' + data.qty + ' tiket<br/><b>' + U.money(data.total) + '</b></p>' +
			'    <div class="ewp-modal__tickets">' + ticketsHtml + '</div>' +
			'    <p class="ewp-co__secure">Selesaikan pembayaran sesuai metode yang dipilih. E-ticket aktif setelah admin memverifikasi bukti transfer.</p>' +
			'    <button class="ewp-btn-primary ewp-btn-block" data-action="success-close">Lihat Tiket Saya ' + icon('ticket', 17) + '</button>' +
			formNote +
			'  </div>' +
			'</div>';
		document.body.insertAdjacentHTML('beforeend', modal);
		requestAnimationFrame(function () { var el = document.getElementById('ewp-success'); if (el) { el.classList.add('is-open'); } });
	}

	document.body.addEventListener('click', function (ev) {
		var x = ev.target.closest('[data-action="success-close"]');
		if (x) { closeSheet(); go('#/tickets'); }
	});

	/* ------------------------------------------------------------------
	 * Form responses
	 * ------------------------------------------------------------------ */
	function submitFormResponses(form, data) {
		var sheet = form.closest('.ewp-sheet');
		var ticketId = sheet ? sheet.getAttribute('data-ticket') : null;
		if (!ticketId) { toast('Data tiket tidak valid.', 'err'); return; }
		var inputs = [].slice.call(form.querySelectorAll('input[type="text"], select'));
		var responses = {};
		inputs.forEach(function (inp) {
			var label = inp.getAttribute('data-label') || inp.name;
			responses[label] = inp.value;
		});
		api('forms/submit', 'POST', { ticket_id: parseInt(ticketId, 10), responses: responses }).then(function (r) {
			toast(r.message || 'Form berhasil dikirim. 🎉');
			closeSheet();
			tickets = []; loadTickets();
		}).catch(function (err) { toast(err.message, 'err'); });
	}

	function historyEventId(ticketId) {
		var id = 0;
		history.forEach(function (h) { if (String(h.id) === String(ticketId)) { id = h.event.id; } });
		return id;
	}

	/* ------------------------------------------------------------------
	 * Review + gallery
	 * ------------------------------------------------------------------ */
	function submitReview(ticketId) {
		var card = document.querySelector('[data-ticket-rate="' + ticketId + '"]');
		if (!card) { return; }
		var rateBox = card.querySelector('.ewp-rate');
		var rating = rateBox ? parseInt(rateBox.getAttribute('data-value') || '5', 10) : 5;
		var comment = card.querySelector('[data-comment]');
		var text = comment ? comment.value.trim() : '';
		api('reviews', 'POST', { ticket_id: parseInt(ticketId, 10), rating: rating, comment: text }).then(function (r) {
			toast(r.message || 'Ulasan terkirim. Terima kasih! ⭐');
		}).catch(function (err) { toast(err.message, 'err'); });
	}

	/* ------------------------------------------------------------------
	 * Admin actions
	 * ------------------------------------------------------------------ */
	function validateOrder(id, action) {
		api('admin/orders/validate', 'POST', { id: parseInt(id, 10), action: action }).then(function (r) {
			toast(r.message || 'Pesanan diperbarui.');
			adminData.overview = null; loadAdmin();
		}).catch(function (err) { toast(err.message, 'err'); });
	}

	function exportBackup() {
		api('admin/backup/export').then(function (r) {
			var data = r.data;
			if (data && data.content) {
				var blob = new Blob([data.content], { type: 'application/json' });
				var a = document.createElement('a');
				a.href = URL.createObjectURL(blob);
				a.download = data.filename || 'eventwp-backup.json';
				document.body.appendChild(a);
				a.click();
				a.remove();
				toast('Backup JSON berhasil diunduh.');
			}
		}).catch(function (err) { toast(err.message, 'err'); });
	}

	/* ------------------------------------------------------------------
	 * Init
	 * ------------------------------------------------------------------ */
	window.addEventListener('hashchange', onRoute);
	boot().then(function () { onRoute(); });
})();
