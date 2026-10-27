(function() {
	function initializeLogin() {
		const login = document.getElementById('loginContainer');
		const usuario = document.getElementById('usuario');
		const hasLoginError = document.body.dataset.loginError === 'true';
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		if (login) login.classList.add('login-visible');
		if (usuario) {
			window.requestAnimationFrame(function() {
				usuario.focus({ preventScroll: true });
			});
		}

		if (hasLoginError || prefersReducedMotion || typeof window.fireworks !== 'function') return;

		const overlay = document.createElement('div');
		const canvas = document.createElement('canvas');
		let instance = null;
		let cleaned = false;

		overlay.id = 'fireworksWelcome';
		overlay.className = 'fireworks-overlay';
		overlay.setAttribute('aria-hidden', 'true');
		canvas.id = 'loginFireworksCanvas';
		overlay.appendChild(canvas);
		document.body.appendChild(overlay);

		function cleanup() {
			if (cleaned) return;
			cleaned = true;
			if (instance) instance.stop();
			overlay.remove();
		}

		window.setTimeout(cleanup, 2200);
		window.fireworks.create(canvas, {
			background: 'none',
			brightness: 15,
			colors: ['#f39c12', '#dc3545', '#0d6efd', '#28a745', '#9b59b6', '#ffffff'],
			rate: { min: 3, max: 4 },
			sounds: false,
			speed: { min: 8, max: 16 },
			splitCount: { min: 45, max: 70 }
		}).then(function(fireworksInstance) {
			instance = fireworksInstance;
			if (cleaned && instance) instance.stop();
		}).catch(cleanup);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeLogin, { once: true });
	} else {
		initializeLogin();
	}
})();
