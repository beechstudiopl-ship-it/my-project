<?php
/**
 * Oznaczanie stron jako "Kurs" (schema Course) przez metabox.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Course
 */
class CSA_Course {

	const META_ENABLED   = '_csa_course_enabled';
	const META_PRICE     = '_csa_course_price';
	const META_CURRENCY  = '_csa_course_currency';
	const META_NEXT_DATE = '_csa_course_next_date';
	const NONCE_ACTION   = 'csa_course_save';
	const NONCE_NAME     = 'csa_course_nonce';

	/**
	 * Konstruktor: podpina metabox, zapis i wypis schema.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ) );
		add_action( 'wp_head', array( $this, 'output_course' ), 22 );
	}

	/**
	 * Dodaje metabox na stronach i wpisach.
	 */
	public function add_metabox() {
		foreach ( array( 'page', 'post' ) as $screen ) {
			add_meta_box(
				'csa_course',
				__( 'Claude SEO: oznacz jako kurs', 'claude-seo-ai' ),
				array( $this, 'render_metabox' ),
				$screen,
				'side',
				'default'
			);
		}
	}

	/**
	 * Renderuje pole metaboxu.
	 *
	 * @param WP_Post $post Aktualny wpis.
	 */
	public function render_metabox( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$enabled  = (bool) get_post_meta( $post->ID, self::META_ENABLED, true );
		$price    = get_post_meta( $post->ID, self::META_PRICE, true );
		$currency = get_post_meta( $post->ID, self::META_CURRENCY, true );
		$next     = get_post_meta( $post->ID, self::META_NEXT_DATE, true );
		?>
		<label>
			<input type="checkbox" name="csa_course_enabled" value="1" <?php checked( $enabled ); ?> />
			<?php esc_html_e( 'Ta strona opisuje kurs (dodaj schema Course)', 'claude-seo-ai' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Tytul i opis kursu pobierzemy automatycznie z tej strony. Dostawca = dane firmy z ustawien wtyczki.', 'claude-seo-ai' ); ?>
		</p>
		<p>
			<label for="csa_course_price"><?php esc_html_e( 'Cena (opcjonalnie)', 'claude-seo-ai' ); ?></label><br />
			<input type="text" id="csa_course_price" name="csa_course_price" class="widefat" value="<?php echo esc_attr( $price ); ?>" placeholder="np. 1200" />
		</p>
		<p>
			<label for="csa_course_currency"><?php esc_html_e( 'Waluta', 'claude-seo-ai' ); ?></label><br />
			<input type="text" id="csa_course_currency" name="csa_course_currency" class="widefat" value="<?php echo esc_attr( $currency ? $currency : 'PLN' ); ?>" placeholder="PLN" />
		</p>
		<p>
			<label for="csa_course_next_date"><?php esc_html_e( 'Najbliższy termin (opcjonalnie)', 'claude-seo-ai' ); ?></label><br />
			<input type="text" id="csa_course_next_date" name="csa_course_next_date" class="widefat" value="<?php echo esc_attr( $next ); ?>" placeholder="np. 2026-10-06" />
		</p>
		<?php
	}

	/**
	 * Zapisuje wartosc metaboxu.
	 *
	 * @param int $post_id ID wpisu.
	 */
	public function save_metabox( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! empty( $_POST['csa_course_enabled'] ) ) {
			update_post_meta( $post_id, self::META_ENABLED, 1 );
		} else {
			delete_post_meta( $post_id, self::META_ENABLED );
		}

		$price = isset( $_POST['csa_course_price'] ) ? sanitize_text_field( wp_unslash( $_POST['csa_course_price'] ) ) : '';
		if ( '' !== $price ) {
			update_post_meta( $post_id, self::META_PRICE, $price );
		} else {
			delete_post_meta( $post_id, self::META_PRICE );
		}

		$currency = isset( $_POST['csa_course_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['csa_course_currency'] ) ) : '';
		if ( '' !== $currency ) {
			update_post_meta( $post_id, self::META_CURRENCY, $currency );
		} else {
			delete_post_meta( $post_id, self::META_CURRENCY );
		}

		$next_date = isset( $_POST['csa_course_next_date'] ) ? sanitize_text_field( wp_unslash( $_POST['csa_course_next_date'] ) ) : '';
		if ( '' !== $next_date ) {
			update_post_meta( $post_id, self::META_NEXT_DATE, $next_date );
		} else {
			delete_post_meta( $post_id, self::META_NEXT_DATE );
		}
	}

	/**
	 * Wypisuje schema Course na oznaczonych stronach.
	 */
	public function output_course() {
		if ( ! is_singular( array( 'page', 'post' ) ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! get_post_meta( $post_id, self::META_ENABLED, true ) ) {
			return;
		}

		$s = csa_get_settings();

		$excerpt = has_excerpt( $post_id )
			? get_the_excerpt( $post_id )
			: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 40, '' );

		$data = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => wp_strip_all_tags( get_the_title( $post_id ) ),
			'description' => trim( preg_replace( '/\s+/', ' ', $excerpt ) ),
			'url'         => get_permalink( $post_id ),
			'inLanguage'  => 'pl-PL',
			'provider'    => array(
				'@type' => $s['org_type'] ? $s['org_type'] : 'Organization',
				'name'  => $s['name'],
				'url'   => home_url( '/' ),
			),
		);

		if ( has_post_thumbnail( $post_id ) ) {
			$data['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		$price    = get_post_meta( $post_id, self::META_PRICE, true );
		$currency = get_post_meta( $post_id, self::META_CURRENCY, true );
		if ( '' !== $price ) {
			$data['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => $currency ? $currency : 'PLN',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink( $post_id ),
			);
		}

		$next_date = get_post_meta( $post_id, self::META_NEXT_DATE, true );
		if ( '' !== $next_date ) {
			$data['hasCourseInstance'] = array(
				'@type'      => 'CourseInstance',
				'courseMode' => 'Onsite',
				'startDate'  => $next_date,
				'location'   => array(
					'@type' => 'Place',
					'name'  => $s['name'],
				),
			);
		}

		CSA_Schema::print_jsonld( $data );
	}
}
