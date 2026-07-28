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

	const META_ENABLED = '_csa_course_enabled';
	const NONCE_ACTION = 'csa_course_save';
	const NONCE_NAME   = 'csa_course_nonce';

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
		$enabled = (bool) get_post_meta( $post->ID, self::META_ENABLED, true );
		?>
		<label>
			<input type="checkbox" name="csa_course_enabled" value="1" <?php checked( $enabled ); ?> />
			<?php esc_html_e( 'Ta strona opisuje kurs (dodaj schema Course)', 'claude-seo-ai' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Tytul i opis kursu pobierzemy automatycznie z tej strony. Dostawca = dane firmy z ustawien wtyczki.', 'claude-seo-ai' ); ?>
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
			'provider'    => array(
				'@type' => $s['org_type'] ? $s['org_type'] : 'Organization',
				'name'  => $s['name'],
				'url'   => home_url( '/' ),
			),
		);

		CSA_Schema::print_jsonld( $data );
	}
}
