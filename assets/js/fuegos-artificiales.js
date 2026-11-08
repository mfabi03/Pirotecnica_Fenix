(function() {
	async function initializeFireworks() {
		const login = document.getElementById('loginContainer');
		const usuario = document.getElementById('usuario');
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		if (login) login.classList.add('login-visible');
		if (usuario) {
			window.requestAnimationFrame(function() {
				usuario.focus({ preventScroll: true });
			});
		}

		if (
			!document.getElementById('tsparticles') ||
			prefersReducedMotion ||
			!window.tsParticles ||
			typeof window.loadFireworksPreset !== 'function'
		) {
			return;
		}

		try {
			await window.loadFireworksPreset(window.tsParticles);
			const container = await window.tsParticles.load({
				id: 'tsparticles',
				options: {
					fullScreen: {
						enable: true,
						zIndex: 2
					},
					background: {
						color: {
							value: 'transparent'
						}
					},
					detectRetina: true,
					fpsLimit: 60,
					preset: 'fireworks',
					emitters: {
						direction: 'top',
						rate: {
							delay: 0.45,
							quantity: 1
						},
						size: {
							width: 100,
							height: 0
						},
						position: {
							x: 50,
							y: 100
						}
					},
					sounds: {
						enable: false
					}
				}
			});

			window.addEventListener('pagehide', function() {
				if (container) container.destroy();
			}, { once: true });
		} catch (error) {
			console.error('No se pudieron iniciar los fuegos artificiales:', error);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initializeFireworks, { once: true });
	} else {
		initializeFireworks();
	}
})();
