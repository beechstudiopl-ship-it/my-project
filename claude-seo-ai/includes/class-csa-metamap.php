<?php
/**
 * Automatyczne ustawianie meta title / meta description z pliku CSV.
 *
 * Uzytkownik wgrywa CSV (URL, tytul, opis), a wtyczka sama podmienia
 * <title> i <meta name="description"> na pasujacych stronach — bez recznej
 * edycji kazdej strony. Integruje sie z Rank Math i Yoast (bez duplikatow).
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_MetaMap
 */
class CSA_MetaMap {

	const MAP_OPTION   = 'csa_meta_map';
	const META_OPTION  = 'csa_meta_map_info';
	const PAGE_SLUG    = 'claude-seo-ai-import';
	const NONCE_ACTION = 'csa_import_csv';
	const NONCE_NAME   = 'csa_import_nonce';

	/**
	 * Konstruktor: menu w kokpicie + hooki frontendu.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_csa_import_csv', array( $this, 'handle_import' ) );
		add_action( 'admin_post_csa_clear_map', array( $this, 'handle_clear' ) );
		add_action( 'admin_post_csa_load_preset', array( $this, 'handle_preset' ) );

		// Integracja z wtyczkami SEO (najczystsza droga — bez duplikatow tagow).
		add_filter( 'rank_math/frontend/title', array( $this, 'filter_title' ), 99 );
		add_filter( 'rank_math/frontend/description', array( $this, 'filter_description' ), 99 );
		add_filter( 'wpseo_title', array( $this, 'filter_title' ), 99 );
		add_filter( 'wpseo_metadesc', array( $this, 'filter_description' ), 99 );

		// Fallback, gdy zadna wtyczka SEO nie kontroluje tytulu/opisu.
		add_filter( 'pre_get_document_title', array( $this, 'fallback_title' ), 99 );
		add_action( 'wp_head', array( $this, 'fallback_description' ), 1 );
	}

	/* -------------------------------------------------------------------------
	 *  ADMIN
	 * ---------------------------------------------------------------------- */

	/**
	 * Dodaje strone importu w menu Ustawienia.
	 */
	public function add_menu() {
		add_options_page(
			__( 'Claude SEO — Import CSV', 'claude-seo-ai' ),
			__( 'Claude SEO: Import CSV', 'claude-seo-ai' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Obsluga wgrania pliku CSV.
	 */
	public function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Brak uprawnien.', 'claude-seo-ai' ) );
		}
		check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME );

		$rows_text = '';

		if ( isset( $_FILES['csv_file'] ) && ! empty( $_FILES['csv_file']['tmp_name'] ) && UPLOAD_ERR_OK === (int) $_FILES['csv_file']['error'] ) {
			$tmp = sanitize_text_field( $_FILES['csv_file']['tmp_name'] ); // phpcs:ignore
			if ( is_uploaded_file( $tmp ) ) {
				$rows_text = (string) file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
		}

		// Wariant awaryjny: wklejenie tresci CSV do pola tekstowego.
		if ( '' === $rows_text && ! empty( $_POST['csv_text'] ) ) {
			$rows_text = wp_unslash( $_POST['csv_text'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		$parsed = $this->parse_csv( $rows_text );
		$map    = array();

		foreach ( $parsed as $row ) {
			$path = $this->normalize_path( $row['url'] );
			if ( '' === $path ) {
				continue;
			}
			$map[ $path ] = array(
				'title'       => sanitize_text_field( $row['title'] ),
				'description' => sanitize_text_field( $row['description'] ),
			);
		}

		update_option( self::MAP_OPTION, $map, false );
		update_option(
			self::META_OPTION,
			array(
				'count'   => count( $map ),
				'updated' => current_time( 'mysql' ),
			),
			false
		);

		$this->redirect( count( $map ) > 0 ? 'imported' : 'empty', count( $map ) );
	}

	/**
	 * Wgrywa gotowy zestaw NorthTC (10 stron) jednym kliknieciem.
	 */
	public function handle_preset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Brak uprawnien.', 'claude-seo-ai' ) );
		}
		check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME );

		$map = array();
		foreach ( $this->preset_data() as $row ) {
			$path = $this->normalize_path( $row[0] );
			if ( '' === $path ) {
				continue;
			}
			$map[ $path ] = array(
				'title'       => sanitize_text_field( $row[1] ),
				'description' => sanitize_text_field( $row[2] ),
			);
		}

		update_option( self::MAP_OPTION, $map, false );
		update_option(
			self::META_OPTION,
			array(
				'count'   => count( $map ),
				'updated' => current_time( 'mysql' ),
			),
			false
		);

		$this->redirect( 'imported', count( $map ) );
	}

	/**
	 * Gotowy zestaw tytulow/opisow dla NorthTC (oparty na danych z Google Search Console).
	 *
	 * @return array Tablica [url, title, description].
	 */
	private function preset_data() {
		return array(
			array( 'https://northtc.pl/', 'Szkolenia GWO Gdynia, Gdansk, Reda | North Training Centre', 'Akredytowane szkolenia i kursy GWO dla offshore i energetyki wiatrowej. Certyfikaty uznawane na swiecie. Reda k. Trojmiasta. Sprawdz terminy!' ),
			array( 'https://northtc.pl/jak-zdobyc-numer-gwo-winda/', 'Numer WINDA ID - jak zdobyc krok po kroku do szkolen GWO', 'Czym jest numer WINDA ID i jak go bezplatnie zalozyc? Przewodnik dla technikow wiatrowych przed szkoleniem GWO. Sprawdz, jak zaczac!' ),
			array( 'https://northtc.pl/szkolenia-gwo/', 'Szkolenia i kursy GWO - pelna oferta i terminy | NorthTC', 'Wszystkie kursy GWO: BST, BTT, ART, Sea Survival, Working at Heights. Akredytowany osrodek w Redzie. Zobacz terminy i ceny szkolen!' ),
			array( 'https://northtc.pl/gwo-kurs-cena/', 'Ile kosztuje kurs GWO? Cennik szkolen 2026 | NorthTC', 'Aktualne ceny szkolen GWO: BST, BTT, ART i odnowienia. Sprawdz, ile kosztuje kurs i pakiety GWO w Redzie k. Gdyni. Przejrzysty cennik!' ),
			array( 'https://northtc.pl/kontakt-certyfikat-gwo/', 'Certyfikat GWO - jak uzyskac i sprawdzic waznosc | NorthTC', 'Zdobadz certyfikat GWO w akredytowanym osrodku North Training Centre. Zobacz, jak wyglada szkolenie i jak zweryfikowac certyfikat. Napisz!' ),
			array( 'https://northtc.pl/szkolenia-gwo-pierwszy-raz/', 'Szkolenie GWO pierwszy raz - od czego zaczac? | NorthTC', 'Pierwsze szkolenie GWO? Dowiedz sie, jakie kursy BST sa wymagane do pracy przy turbinach wiatrowych i jak sie przygotowac. Zapisz sie!' ),
			array( 'https://northtc.pl/gwo-gdynia/', 'Szkolenia GWO Gdynia - kursy offshore i wind | NorthTC', 'Kursy GWO blisko Gdyni - osrodek w Redzie (Trojmiasto). Szkolenia BST, ART, Sea Survival. Dojazd z Gdyni i Gdanska. Sprawdz terminy!' ),
			array( 'https://northtc.pl/szkolenia/gwo-working-at-heights-wah/', 'Kurs GWO Praca na Wysokosciach (WAH) | NorthTC Reda', 'Szkolenie GWO Working at Heights - bezpieczna praca na wysokosci przy turbinach wiatrowych. Certyfikat GWO. Osrodek w Redzie. Zapisz sie!' ),
			array( 'https://northtc.pl/szkolenia/gwo-efa-enhanced-first-aid/', 'Kurs GWO Enhanced First Aid (EFA) | NorthTC Reda', 'Zaawansowany kurs pierwszej pomocy GWO EFA dla technikow offshore. Ratownictwo w ekstremalnych warunkach. Certyfikat GWO. Sprawdz terminy!' ),
			array( 'https://northtc.pl/szkolenia-gwo-odnowienie/', 'Odnowienie GWO (Refresher) - wszystkie kursy | NorthTC', 'Odswiez certyfikat GWO: BST, ART, Sea Survival, Working at Heights Refresher. Zgodnie ze standardem GWO. Reda k. Gdyni. Zobacz terminy!' ),
		);
	}

	/**
	 * Czysci zapisana mape.
	 */
	public function handle_clear() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Brak uprawnien.', 'claude-seo-ai' ) );
		}
		check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME );

		delete_option( self::MAP_OPTION );
		delete_option( self::META_OPTION );
		$this->redirect( 'cleared', 0 );
	}

	/**
	 * Przekierowanie po akcji.
	 *
	 * @param string $status Kod statusu.
	 * @param int    $count  Liczba wpisow.
	 */
	private function redirect( $status, $count ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'     => self::PAGE_SLUG,
					'csa_msg'  => $status,
					'csa_num'  => (int) $count,
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Renderuje strone importu.
	 */
	public function render_page() {
		$map  = get_option( self::MAP_OPTION, array() );
		$info = get_option( self::META_OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Claude SEO — Import CSV (tytuly i opisy)', 'claude-seo-ai' ); ?></h1>
			<p><?php esc_html_e( 'Wgraj plik CSV z kolumnami: URL, meta_title, meta_description. Wtyczka sama ustawi tytul i opis na kazdej pasujacej stronie — bez recznej edycji.', 'claude-seo-ai' ); ?></p>

			<?php $this->notice(); ?>

			<div class="card" style="max-width:820px;padding:16px 20px;background:#f0f6fc;border-left:4px solid #2271b1;">
				<h2 style="margin-top:0;">⚡ <?php esc_html_e( 'Najszybciej: gotowy zestaw NorthTC', 'claude-seo-ai' ); ?></h2>
				<p><?php esc_html_e( 'Wgraj od razu 10 zoptymalizowanych tytulow i opisow (przygotowanych na podstawie Twoich danych z Google Search Console) — bez wgrywania pliku.', 'claude-seo-ai' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="csa_load_preset" />
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
					<?php submit_button( __( 'Wgraj gotowy zestaw NorthTC (10 stron)', 'claude-seo-ai' ), 'primary', 'submit', false ); ?>
				</form>
			</div>

			<div class="card" style="max-width:820px;padding:16px 20px;">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Wgraj plik', 'claude-seo-ai' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="csa_import_csv" />
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
					<p>
						<label><strong><?php esc_html_e( 'Plik CSV:', 'claude-seo-ai' ); ?></strong></label><br />
						<input type="file" name="csv_file" accept=".csv,text/csv" />
					</p>
					<p><em><?php esc_html_e( 'lub wklej zawartosc CSV ponizej:', 'claude-seo-ai' ); ?></em></p>
					<p><textarea name="csv_text" class="large-text code" rows="6" placeholder="URL,meta_title,meta_description&#10;https://twojastrona.pl/,Tytul...,Opis..."></textarea></p>
					<?php submit_button( __( 'Wgraj i zastosuj', 'claude-seo-ai' ) ); ?>
				</form>
			</div>

			<?php if ( ! empty( $map ) ) : ?>
				<h2><?php esc_html_e( 'Aktualnie zastosowane', 'claude-seo-ai' ); ?>
					<span class="count">(<?php echo (int) ( isset( $info['count'] ) ? $info['count'] : count( $map ) ); ?>)</span></h2>
				<?php if ( ! empty( $info['updated'] ) ) : ?>
					<p class="description"><?php echo esc_html( sprintf( /* translators: %s: date */ __( 'Ostatnia aktualizacja: %s', 'claude-seo-ai' ), $info['updated'] ) ); ?></p>
				<?php endif; ?>
				<table class="widefat striped">
					<thead><tr>
						<th><?php esc_html_e( 'Sciezka', 'claude-seo-ai' ); ?></th>
						<th><?php esc_html_e( 'Tytul', 'claude-seo-ai' ); ?></th>
						<th><?php esc_html_e( 'Opis', 'claude-seo-ai' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $map as $path => $data ) : ?>
						<tr>
							<td><code><?php echo esc_html( $path ); ?></code></td>
							<td><?php echo esc_html( $data['title'] ); ?></td>
							<td><?php echo esc_html( $data['description'] ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;" onsubmit="return confirm('<?php echo esc_js( __( 'Na pewno usunac wszystkie zastosowane tytuly/opisy?', 'claude-seo-ai' ) ); ?>');">
					<input type="hidden" name="action" value="csa_clear_map" />
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
					<?php submit_button( __( 'Wyczysc wszystko', 'claude-seo-ai' ), 'delete', 'submit', false ); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Komunikaty po akcjach.
	 */
	private function notice() {
		if ( empty( $_GET['csa_msg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$msg = sanitize_text_field( wp_unslash( $_GET['csa_msg'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$num = isset( $_GET['csa_num'] ) ? (int) $_GET['csa_num'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$map_txt = array(
			'imported' => array( 'success', sprintf( /* translators: %d: count */ __( 'Zastosowano tytuly/opisy dla %d stron.', 'claude-seo-ai' ), $num ) ),
			'cleared'  => array( 'success', __( 'Wyczyszczono wszystkie tytuly/opisy.', 'claude-seo-ai' ) ),
			'empty'    => array( 'error', __( 'Nie znaleziono poprawnych wierszy. Sprawdz naglowki: URL, meta_title, meta_description.', 'claude-seo-ai' ) ),
		);
		if ( ! isset( $map_txt[ $msg ] ) ) {
			return;
		}
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $map_txt[ $msg ][0] ),
			esc_html( $map_txt[ $msg ][1] )
		);
	}

	/* -------------------------------------------------------------------------
	 *  PARSOWANIE CSV
	 * ---------------------------------------------------------------------- */

	/**
	 * Parsuje tekst CSV do tablicy wierszy url/title/description.
	 *
	 * @param string $text Zawartosc CSV.
	 * @return array
	 */
	private function parse_csv( $text ) {
		$text = trim( (string) $text );
		if ( '' === $text ) {
			return array();
		}
		// Usun BOM.
		$text = preg_replace( '/^\xEF\xBB\xBF/', '', $text );

		$lines = preg_split( '/\r\n|\r|\n/', $text );
		if ( empty( $lines ) ) {
			return array();
		}

		// Wykryj separator (przecinek lub srednik).
		$first = $lines[0];
		$delim = ( substr_count( $first, ';' ) > substr_count( $first, ',' ) ) ? ';' : ',';

		$header = str_getcsv( array_shift( $lines ), $delim );
		$idx    = $this->map_columns( $header );

		// Brak rozpoznanych naglowkow: zaloz kolejnosc url,title,description bez naglowka.
		if ( null === $idx['url'] ) {
			array_unshift( $lines, implode( $delim, $header ) );
			$idx = array(
				'url'         => 0,
				'title'       => 1,
				'description' => 2,
			);
		}

		$out = array();
		foreach ( $lines as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			$cols = str_getcsv( $line, $delim );
			$url  = isset( $cols[ $idx['url'] ] ) ? trim( $cols[ $idx['url'] ] ) : '';
			if ( '' === $url ) {
				continue;
			}
			$out[] = array(
				'url'         => $url,
				'title'       => ( null !== $idx['title'] && isset( $cols[ $idx['title'] ] ) ) ? trim( $cols[ $idx['title'] ] ) : '',
				'description' => ( null !== $idx['description'] && isset( $cols[ $idx['description'] ] ) ) ? trim( $cols[ $idx['description'] ] ) : '',
			);
		}

		return $out;
	}

	/**
	 * Dopasowuje indeksy kolumn na podstawie naglowkow.
	 *
	 * @param array $header Naglowki.
	 * @return array
	 */
	private function map_columns( $header ) {
		$idx = array(
			'url'         => null,
			'title'       => null,
			'description' => null,
		);
		foreach ( $header as $i => $h ) {
			$h = strtolower( trim( $h ) );
			if ( null === $idx['url'] && ( false !== strpos( $h, 'url' ) || false !== strpos( $h, 'adres' ) || false !== strpos( $h, 'strona' ) ) ) {
				$idx['url'] = $i;
			} elseif ( null === $idx['title'] && ( false !== strpos( $h, 'title' ) || false !== strpos( $h, 'tytu' ) ) ) {
				$idx['title'] = $i;
			} elseif ( null === $idx['description'] && ( false !== strpos( $h, 'desc' ) || false !== strpos( $h, 'opis' ) ) ) {
				$idx['description'] = $i;
			}
		}
		return $idx;
	}

	/**
	 * Normalizuje URL/sciezke do postaci "/sciezka/".
	 *
	 * @param string $url Adres lub sciezka.
	 * @return string
	 */
	private function normalize_path( $url ) {
		$url  = trim( $url );
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( null === $path || '' === $path ) {
			$path = $url; // Byc moze podano sama sciezke.
		}
		if ( '' === $path ) {
			return '';
		}
		$path = '/' . trim( $path, '/' );
		if ( '/' !== $path ) {
			$path .= '/';
		}
		return $path;
	}

	/* -------------------------------------------------------------------------
	 *  FRONTEND
	 * ---------------------------------------------------------------------- */

	/**
	 * Zwraca wpis (title/description) dla aktualnego adresu lub null.
	 *
	 * @return array|null
	 */
	private function current_entry() {
		static $cache = false;
		if ( false !== $cache ) {
			return $cache;
		}
		$cache = null;

		if ( is_admin() ) {
			return null;
		}

		$map = get_option( self::MAP_OPTION, array() );
		if ( empty( $map ) || ! is_array( $map ) ) {
			return null;
		}

		$req  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/'; // phpcs:ignore
		$path = wp_parse_url( $req, PHP_URL_PATH );
		$path = '/' . trim( (string) $path, '/' );
		if ( '/' !== $path ) {
			$path .= '/';
		}

		if ( isset( $map[ $path ] ) ) {
			$cache = $map[ $path ];
		}
		return $cache;
	}

	/**
	 * Czy dziala wtyczka SEO kontrolujaca tytul/opis.
	 *
	 * @return bool
	 */
	private function seo_plugin_active() {
		return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' );
	}

	/**
	 * Filtr tytulu dla Rank Math / Yoast.
	 *
	 * @param string $title Aktualny tytul.
	 * @return string
	 */
	public function filter_title( $title ) {
		$entry = $this->current_entry();
		if ( $entry && '' !== $entry['title'] ) {
			return $entry['title'];
		}
		return $title;
	}

	/**
	 * Filtr opisu dla Rank Math / Yoast.
	 *
	 * @param string $desc Aktualny opis.
	 * @return string
	 */
	public function filter_description( $desc ) {
		$entry = $this->current_entry();
		if ( $entry && '' !== $entry['description'] ) {
			return $entry['description'];
		}
		return $desc;
	}

	/**
	 * Fallback tytulu, gdy brak wtyczki SEO.
	 *
	 * @param string $title Aktualny tytul.
	 * @return string
	 */
	public function fallback_title( $title ) {
		if ( $this->seo_plugin_active() ) {
			return $title; // Obsluzone przez filtry wtyczki SEO.
		}
		$entry = $this->current_entry();
		if ( $entry && '' !== $entry['title'] ) {
			return $entry['title'];
		}
		return $title;
	}

	/**
	 * Fallback meta description, gdy brak wtyczki SEO.
	 */
	public function fallback_description() {
		if ( $this->seo_plugin_active() ) {
			return; // Unikamy duplikatu tagu.
		}
		$entry = $this->current_entry();
		if ( $entry && '' !== $entry['description'] ) {
			echo "\n" . '<meta name="description" content="' . esc_attr( $entry['description'] ) . '" />' . "\n";
		}
	}
}
