/**
 * EventWP — Landing Page interactions.
 * Scroll reveals, navbar state, FAQ accordion, back-to-top, ambient parallax.
 */
(function () {
	'use strict';

	var root = document.getElementById('eventwp-landing');
	if (!root) {
		return;
	}

	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ------------------------------------------------------------------
	 * Scroll reveal (IntersectionObserver)
	 * ---------------------------------------------------------------- */
	var reveals = root.querySelectorAll('[data-reveal]');
	if ('IntersectionObserver' in window && !reduceMotion) {
		var revealObserver = new IntersectionObserver(
			function (entries, obs) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						obs.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
		);
		reveals.forEach(function (el) {
			revealObserver.observe(el);
		});
	} else {
		reveals.forEach(function (el) {
			el.classList.add('is-visible');
		});
	}

	/* ------------------------------------------------------------------
	 * Navbar scroll state
	 * ---------------------------------------------------------------- */
	var nav = root.querySelector('[data-ewp-nav]');
	var backtop = root.querySelector('[data-ewp-backtop]');
	var lastY = 0;

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		nav.classList.toggle('is-scrolled', y > 24);
		if (backtop) {
			backtop.classList.toggle('is-visible', y > 600);
		}
		lastY = y;
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	if (backtop) {
		backtop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
		});
	}

	/* ------------------------------------------------------------------
	 * Mobile nav
	 * ---------------------------------------------------------------- */
	var burger = root.querySelector('[data-ewp-burger]');
	if (burger) {
		burger.addEventListener('click', function () {
			var open = root.querySelector('.ewp-nav').classList.toggle('is-open');
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		// Close on link tap.
		root.querySelectorAll('.ewp-nav__link, .ewp-nav__cta').forEach(function (link) {
			link.addEventListener('click', function () {
				nav.classList.remove('is-open');
				burger.setAttribute('aria-expanded', 'false');
			});
		});
	}

	/* ------------------------------------------------------------------
	 * FAQ accordion
	 * ---------------------------------------------------------------- */
	root.querySelectorAll('[data-ewp-faq]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var item = btn.closest('.ewp-faq__item');
			var answer = item.querySelector('.ewp-faq__a');
			var isOpen = item.classList.contains('is-open');
			// Close others.
			root.querySelectorAll('.ewp-faq__item.is-open').forEach(function (other) {
				if (other !== item) {
					other.classList.remove('is-open');
					other.querySelector('.ewp-faq__a').style.maxHeight = null;
					var q = other.querySelector('[data-ewp-faq]');
					if (q) { q.setAttribute('aria-expanded', 'false'); }
				}
			});
			item.classList.toggle('is-open', !isOpen);
			answer.style.maxHeight = isOpen ? null : answer.scrollHeight + 'px';
			btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

			if (!isOpen) {
				setTimeout(function () {
					answer.style.maxHeight = 'none';
				}, 450);
			}
		});
	});

	/* ------------------------------------------------------------------
	 * Ambient parallax on orbs
	 * ---------------------------------------------------------------- */
	if (!reduceMotion) {
		var orb = root.querySelector('[data-ewp-sports]');
		var heroOrbs = root.querySelectorAll('.ewp-orb');
		var ticking = false;

		function parallax() {
			var y = window.scrollY || 0;
			heroOrbs.forEach(function (el, i) {
				var speed = 0.04 + i * 0.03;
				el.style.transform = 'translateY(' + y * speed + 'px)';
			});
			ticking = false;
		}

		window.addEventListener(
			'scroll',
			function () {
				if (!ticking) {
					window.requestAnimationFrame(parallax);
					ticking = true;
				}
			},
			{ passive: true }
		);
	}

	/* ------------------------------------------------------------------
	 * Smooth anchor scroll (respect reduced motion)
	 * ---------------------------------------------------------------- */
	root.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
		anchor.addEventListener('click', function (e) {
			var id = anchor.getAttribute('href');
			if (!id || id.length < 2) {
				return;
			}
			var target = document.getElementById(id.substring(1));
			if (!target) {
				return;
			}
			e.preventDefault();
			var navHeight = 74;
			var top = target.getBoundingClientRect().top + (window.scrollY || 0) - navHeight;
			window.scrollTo({ top: top, behavior: reduceMotion ? 'auto' : 'smooth' });
			history.replaceState(null, '', id);
		});
	});

	/* ------------------------------------------------------------------
	 * Mouse tilt micro-interaction for hero phone
	 * ---------------------------------------------------------------- */
	if (!reduceMotion && window.matchMedia('(pointer: fine)').matches) {
		var phone = root.querySelector('.ewp-hero__phone');
		var hero = root.querySelector('.ewp-hero');
		if (phone && hero) {
			hero.addEventListener('mousemove', function (e) {
				var r = hero.getBoundingClientRect();
				var x = (e.clientX - r.left) / r.width - 0.5;
				var y = (e.clientY - r.top) / r.height - 0.5;
				phone.style.setProperty('--tilt-x', (y * -8).toFixed(2) + 'deg');
				phone.style.setProperty('--tilt-y', (x * 10).toFixed(2) + 'deg');
				phone.style.transform = 'rotate(' + (3 + x * 4).toFixed(2) + 'deg) translateY(' + (y * -6).toFixed(2) + 'px)';
			});
			hero.addEventListener('mouseleave', function () {
				phone.style.transform = '';
			});
		}
	}
})();
