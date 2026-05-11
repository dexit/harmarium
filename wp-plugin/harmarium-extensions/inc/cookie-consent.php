<?php
/**
 * GDPR-compliant cookie consent banner.
 * No third-party scripts. Consent stored in localStorage.
 * Blocks analytics/non-essential scripts until consent is granted.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', 'harmarium_cookie_consent_banner' );

function harmarium_cookie_consent_banner(): void {
	if ( is_admin() ) return;
	$cookie_page = get_page_by_path( 'cookie-policy' );
	$cookie_url  = $cookie_page ? get_permalink( $cookie_page ) : '#';
	$privacy_url = get_privacy_policy_url() ?: '#';
	?>
	<div id="hm-cookie-banner" class="hm-cookie-banner" role="dialog" aria-modal="false" aria-label="<?php esc_attr_e( 'Cookie consent', 'harmarium-ext' ); ?>" style="display:none">
		<div class="hm-cookie-banner__inner">
			<p class="hm-cookie-banner__text">
				<?php
				printf(
					/* translators: 1: cookie policy link, 2: privacy policy link */
					wp_kses(
						__( 'We use cookies to improve your experience. By continuing, you agree to our <a href="%1$s">Cookie Policy</a> and <a href="%2$s">Privacy Policy</a>.', 'harmarium-ext' ),
						[ 'a' => [ 'href' => [] ] ]
					),
					esc_url( $cookie_url ),
					esc_url( $privacy_url )
				);
				?>
			</p>
			<div class="hm-cookie-banner__actions">
				<button id="hm-cookie-accept-all" class="hm-cookie-btn hm-cookie-btn--primary" type="button">
					<?php esc_html_e( 'Accept all', 'harmarium-ext' ); ?>
				</button>
				<button id="hm-cookie-accept-essential" class="hm-cookie-btn hm-cookie-btn--secondary" type="button">
					<?php esc_html_e( 'Essential only', 'harmarium-ext' ); ?>
				</button>
				<button id="hm-cookie-reject" class="hm-cookie-btn hm-cookie-btn--ghost" type="button">
					<?php esc_html_e( 'Reject all', 'harmarium-ext' ); ?>
				</button>
			</div>
		</div>
	</div>
	<script>
	(function() {
		var STORE_KEY = 'hm_cookie_consent';
		var banner    = document.getElementById('hm-cookie-banner');

		function getConsent() {
			try { return JSON.parse(localStorage.getItem(STORE_KEY)); } catch(e) { return null; }
		}

		function setConsent(level) {
			var data = { level: level, ts: Date.now() };
			try { localStorage.setItem(STORE_KEY, JSON.stringify(data)); } catch(e) {}
			document.cookie = 'hm_consent=' + encodeURIComponent(level) + '; path=/; max-age=31536000; SameSite=Lax';
			banner.style.display = 'none';
			banner.removeAttribute('aria-hidden');
			document.dispatchEvent(new CustomEvent('hm:consent', { detail: data }));
		}

		var consent = getConsent();
		if (!consent) {
			banner.style.display = '';
			document.getElementById('hm-cookie-accept-all').addEventListener('click', function() { setConsent('all'); });
			document.getElementById('hm-cookie-accept-essential').addEventListener('click', function() { setConsent('essential'); });
			document.getElementById('hm-cookie-reject').addEventListener('click', function() { setConsent('rejected'); });
		}

		// Expose for use by analytics snippets
		window.hmCookieConsent = {
			getLevel: function() { return getConsent() ? getConsent().level : null; },
			hasAnalytics: function() { return getConsent() && getConsent().level === 'all'; }
		};
	})();
	</script>
	<?php
}

/* ── Enqueue banner CSS ── */
add_action( 'wp_enqueue_scripts', function (): void {
	wp_enqueue_style(
		'hm-cookie-consent',
		HARMARIUM_EXT_URI . 'assets/css/cookie-consent.css',
		[],
		HARMARIUM_EXT_VERSION
	);
} );
