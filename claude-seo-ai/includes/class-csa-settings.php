<?php
/**
 * Panel ustawien wtyczki w kokpicie WordPress.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Settings
 */
class CSA_Settings {

	const PAGE_SLUG    = 'claude-seo-ai';
	const NONCE_ACTION = 'csa_save_settings';
	const NONCE_NAME   = 'csa_settings_nonce';

	/**
	 * Konstruktor: podpina menu i obsluge zapisu.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_csa_save', array( $this, 'handle_save' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( CSA_PLUGIN_FILE ),
			array( $this, 'action_links' )
		);
	}

	/**
	 * Dodaje pozycje w menu "Ustawienia".
	 */
	public function add_menu() {
		add_options_page(
			__( 'Claude SEO — AI Visibility', 'claude-seo-ai' ),
			__( 'Claude SEO (AI)', 'claude-seo-ai' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Skrot "Ustawienia" na liscie wtyczek.
	 *
	 * @param array $links Istniejace linki.
	 * @return array
	 */
	public function action_links( $links ) {
		$url  = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Ustawienia', 'claude-seo-ai' ) . '</a>';
		array_unshift( $links, $link );

		return $links;
	}

	/**
	 * Obsluga zapisu formularza (admin-post).
	 */
	public function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Brak uprawnien.', 'claude-seo-ai' ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME );

		$in  = wp_unslash( $_POST );
		$out = array(
			'enable_ai_bots' => empty( $in['enable_ai_bots'] ) ? 0 : 1,
			'enable_org'     => empty( $in['enable_org'] ) ? 0 : 1,
			'enable_faq'     => empty( $in['enable_faq'] ) ? 0 : 1,
			'org_type'       => $this->pick(
				isset( $in['org_type'] ) ? $in['org_type'] : '',
				array( 'Organization', 'EducationalOrganization', 'LocalBusiness' ),
				'Organization'
			),
			'name'           => sanitize_text_field( $in['name'] ?? '' ),
			'alternate_name' => sanitize_text_field( $in['alternate_name'] ?? '' ),
			'logo'           => esc_url_raw( $in['logo'] ?? '' ),
			'description'    => sanitize_textarea_field( $in['description'] ?? '' ),
			'email'          => sanitize_email( $in['email'] ?? '' ),
			'telephone'      => sanitize_text_field( $in['telephone'] ?? '' ),
			'street'         => sanitize_text_field( $in['street'] ?? '' ),
			'postal_code'    => sanitize_text_field( $in['postal_code'] ?? '' ),
			'city'           => sanitize_text_field( $in['city'] ?? '' ),
			'region'         => sanitize_text_field( $in['region'] ?? '' ),
			'country'        => sanitize_text_field( $in['country'] ?? '' ),
			'area_served'    => sanitize_textarea_field( $in['area_served'] ?? '' ),
			'same_as'        => sanitize_textarea_field( $in['same_as'] ?? '' ),
			'faq_scope'      => $this->pick(
				isset( $in['faq_scope'] ) ? $in['faq_scope'] : '',
				array( 'front', 'all' ),
				'front'
			),
		);

		update_option( CSA_OPTION_KEY, $out );

		$redirect = add_query_arg(
			array(
				'page'    => self::PAGE_SLUG,
				'updated' => 'true',
			),
			admin_url( 'options-general.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Zwraca wartosc, jesli jest na liscie dozwolonych, inaczej domyslna.
	 *
	 * @param string $value   Wartosc wejsciowa.
	 * @param array  $allowed Dozwolone wartosci.
	 * @param string $default Wartosc domyslna.
	 * @return string
	 */
	private function pick( $value, $allowed, $default ) {
		$value = sanitize_text_field( $value );
		return in_array( $value, $allowed, true ) ? $value : $default;
	}

	/**
	 * Renderuje strone ustawien.
	 */
	public function render_page() {
		$s          = csa_get_settings();
		$robots_url = home_url( '/robots.txt' );
		$has_static = $this->has_static_robots();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Claude SEO — AI Visibility', 'claude-seo-ai' ); ?></h1>
			<p><?php esc_html_e( 'Automatyczne SEO pod AI: boty AI w robots.txt oraz dane strukturalne (firma, FAQ, kursy). Wypelnij dane firmy raz — reszta dzieje sie sama.', 'claude-seo-ai' ); ?></p>

			<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Zapisano ustawienia.', 'claude-seo-ai' ); ?></p></div>
			<?php endif; ?>

			<?php if ( $has_static ) : ?>
				<div class="notice notice-warning"><p>
					<strong><?php esc_html_e( 'Uwaga:', 'claude-seo-ai' ); ?></strong>
					<?php esc_html_e( 'W katalogu glownym istnieje statyczny plik robots.txt. WordPress (i ta wtyczka) NIE moga go nadpisac. Dodaj boty AI recznie do tego pliku albo go usun, aby WordPress generowal robots.txt dynamicznie.', 'claude-seo-ai' ); ?>
				</p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="csa_save" />
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>

				<h2 class="title"><?php esc_html_e( '1. Automatyzacja', 'claude-seo-ai' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Boty AI w robots.txt', 'claude-seo-ai' ); ?></th>
						<td>
							<label><input type="checkbox" name="enable_ai_bots" value="1" <?php checked( $s['enable_ai_bots'] ); ?> /> <?php esc_html_e( 'Wpuszczaj boty AI (GPTBot, ClaudeBot, PerplexityBot, Google-Extended...)', 'claude-seo-ai' ); ?></label>
							<p class="description"><?php echo esc_html__( 'Sprawdz efekt pod adresem:', 'claude-seo-ai' ); ?> <a href="<?php echo esc_url( $robots_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $robots_url ); ?></a></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Dane firmy (JSON-LD)', 'claude-seo-ai' ); ?></th>
						<td><label><input type="checkbox" name="enable_org" value="1" <?php checked( $s['enable_org'] ); ?> /> <?php esc_html_e( 'Wstrzykuj schema firmy na kazdej stronie', 'claude-seo-ai' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'FAQ (JSON-LD)', 'claude-seo-ai' ); ?></th>
						<td>
							<label><input type="checkbox" name="enable_faq" value="1" <?php checked( $s['enable_faq'] ); ?> /> <?php esc_html_e( 'Buduj schema FAQPage z wpisow FAQ', 'claude-seo-ai' ); ?></label>
							<p class="description">
								<?php esc_html_e( 'Pytania dodajesz w menu "FAQ (AI SEO)". Zasieg:', 'claude-seo-ai' ); ?>
								<select name="faq_scope">
									<option value="front" <?php selected( $s['faq_scope'], 'front' ); ?>><?php esc_html_e( 'tylko strona glowna', 'claude-seo-ai' ); ?></option>
									<option value="all" <?php selected( $s['faq_scope'], 'all' ); ?>><?php esc_html_e( 'cala witryna', 'claude-seo-ai' ); ?></option>
								</select>
							</p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( '2. Dane firmy', 'claude-seo-ai' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$this->select_row(
						'org_type',
						__( 'Typ organizacji', 'claude-seo-ai' ),
						array(
							'Organization'            => 'Organization (ogolna)',
							'EducationalOrganization' => 'EducationalOrganization (szkolenia)',
							'LocalBusiness'           => 'LocalBusiness (firma lokalna)',
						),
						$s['org_type']
					);
					$this->text_row( 'name', __( 'Nazwa', 'claude-seo-ai' ), $s['name'] );
					$this->text_row( 'alternate_name', __( 'Nazwa alternatywna', 'claude-seo-ai' ), $s['alternate_name'], 'np. NorthTC' );
					$this->text_row( 'logo', __( 'URL logo', 'claude-seo-ai' ), $s['logo'], 'https://...' );
					$this->textarea_row( 'description', __( 'Opis', 'claude-seo-ai' ), $s['description'] );
					$this->text_row( 'email', __( 'E-mail', 'claude-seo-ai' ), $s['email'] );
					$this->text_row( 'telephone', __( 'Telefon', 'claude-seo-ai' ), $s['telephone'] );
					?>
				</table>

				<h2 class="title"><?php esc_html_e( '3. Adres i zasieg', 'claude-seo-ai' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$this->text_row( 'street', __( 'Ulica i numer', 'claude-seo-ai' ), $s['street'] );
					$this->text_row( 'postal_code', __( 'Kod pocztowy', 'claude-seo-ai' ), $s['postal_code'] );
					$this->text_row( 'city', __( 'Miejscowosc', 'claude-seo-ai' ), $s['city'] );
					$this->text_row( 'region', __( 'Region / wojewodztwo', 'claude-seo-ai' ), $s['region'] );
					$this->text_row( 'country', __( 'Kraj (kod)', 'claude-seo-ai' ), $s['country'], 'PL' );
					$this->textarea_row( 'area_served', __( 'Obslugiwane obszary', 'claude-seo-ai' ), $s['area_served'], __( 'Po przecinku, np. Reda, Gdynia, Gdansk', 'claude-seo-ai' ) );
					$this->textarea_row( 'same_as', __( 'Profile (sameAs)', 'claude-seo-ai' ), $s['same_as'], __( 'Po jednym URL w linii: Facebook, LinkedIn, Google Maps...', 'claude-seo-ai' ) );
					?>
				</table>

				<?php submit_button( __( 'Zapisz ustawienia', 'claude-seo-ai' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Sprawdza, czy istnieje statyczny plik robots.txt w katalogu glownym.
	 *
	 * @return bool
	 */
	private function has_static_robots() {
		return file_exists( ABSPATH . 'robots.txt' );
	}

	/**
	 * Wiersz pola tekstowego.
	 */
	private function text_row( $name, $label, $value, $placeholder = '' ) {
		?>
		<tr>
			<th scope="row"><label for="csa_<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="text" class="regular-text" id="csa_<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" /></td>
		</tr>
		<?php
	}

	/**
	 * Wiersz pola wieloliniowego.
	 */
	private function textarea_row( $name, $label, $value, $placeholder = '' ) {
		?>
		<tr>
			<th scope="row"><label for="csa_<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><textarea class="large-text" rows="3" id="csa_<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $value ); ?></textarea></td>
		</tr>
		<?php
	}

	/**
	 * Wiersz pola wyboru.
	 */
	private function select_row( $name, $label, $options, $current ) {
		?>
		<tr>
			<th scope="row"><label for="csa_<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<select id="csa_<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<?php foreach ( $options as $val => $text ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $text ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}
}
