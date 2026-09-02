<?php
/**
 * Zarzadzanie FAQ przez wlasny typ tresci (CPT) -> schema FAQPage.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Faq
 */
class CSA_Faq {

	const POST_TYPE   = 'csa_faq';
	const META_PAGES  = '_csa_faq_pages';
	const NONCE_ACTION = 'csa_faq_pages_save';
	const NONCE_NAME   = 'csa_faq_pages_nonce';

	/**
	 * Konstruktor: rejestruje CPT i metabox przypisania do stron.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_metabox' ) );
	}

	/**
	 * Dodaje metabox przypisania pytania do konkretnych stron/wpisow.
	 */
	public function add_metabox() {
		add_meta_box(
			'csa_faq_pages',
			__( 'Claude SEO: pokaż też na stronach', 'claude-seo-ai' ),
			array( $this, 'render_metabox' ),
			self::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Renderuje pole metaboksu.
	 *
	 * @param WP_Post $post Aktualny wpis FAQ.
	 */
	public function render_metabox( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$value = get_post_meta( $post->ID, self::META_PAGES, true );
		?>
		<textarea name="csa_faq_pages" class="widefat" rows="3" placeholder="np. /szkolenia-gwo/, /o-nas/"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Adresy URL stron (po przecinku lub w osobnych liniach), na których to pytanie ma się dodatkowo pojawić w schema FAQPage — niezależnie od zasięgu globalnego (strona główna/cała witryna).', 'claude-seo-ai' ); ?>
		</p>
		<?php
	}

	/**
	 * Zapisuje przypisanie do stron.
	 *
	 * @param int $post_id ID wpisu FAQ.
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

		$value = isset( $_POST['csa_faq_pages'] ) ? sanitize_textarea_field( wp_unslash( $_POST['csa_faq_pages'] ) ) : '';
		update_post_meta( $post_id, self::META_PAGES, $value );
	}

	/**
	 * Rozbija zapisaną listę URL-i na tablicę znormalizowanych ścieżek.
	 *
	 * @param string $raw Surowy tekst z metaboksu.
	 * @return array
	 */
	private static function parse_pages( $raw ) {
		if ( empty( $raw ) ) {
			return array();
		}
		$parts = preg_split( '/[\r\n,]+/', $raw );
		$paths = array();
		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( '' === $part ) {
				continue;
			}
			$path    = wp_parse_url( $part, PHP_URL_PATH );
			$path    = $path ? $path : $part;
			$paths[] = untrailingslashit( $path );
		}

		return $paths;
	}

	/**
	 * Rejestracja typu tresci "FAQ".
	 *
	 * Tytul wpisu = pytanie, tresc wpisu = odpowiedz.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'FAQ (AI SEO)', 'claude-seo-ai' ),
			'singular_name'      => __( 'Pytanie FAQ', 'claude-seo-ai' ),
			'add_new'            => __( 'Dodaj pytanie', 'claude-seo-ai' ),
			'add_new_item'       => __( 'Dodaj nowe pytanie', 'claude-seo-ai' ),
			'edit_item'          => __( 'Edytuj pytanie', 'claude-seo-ai' ),
			'new_item'           => __( 'Nowe pytanie', 'claude-seo-ai' ),
			'view_item'          => __( 'Zobacz pytanie', 'claude-seo-ai' ),
			'search_items'       => __( 'Szukaj w FAQ', 'claude-seo-ai' ),
			'not_found'          => __( 'Brak pytan', 'claude-seo-ai' ),
			'menu_name'          => __( 'FAQ (AI SEO)', 'claude-seo-ai' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => $labels,
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-editor-help',
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
				'has_archive'  => false,
				'rewrite'      => false,
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Pobiera opublikowane wpisy FAQ jako pary pytanie/odpowiedz.
	 *
	 * @param string $target_path Sciezka biezacej strony (np. z wp_parse_url), lub '' aby pobrac wszystkie.
	 * @return array
	 */
	public static function get_items( $target_path = '' ) {
		$query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		$target_path = $target_path ? untrailingslashit( $target_path ) : '';

		$items = array();
		foreach ( $query->posts as $post ) {
			if ( '' !== $target_path ) {
				$assigned = self::parse_pages( get_post_meta( $post->ID, self::META_PAGES, true ) );
				if ( ! in_array( $target_path, $assigned, true ) ) {
					continue;
				}
			}

			$question = wp_strip_all_tags( get_the_title( $post ) );
			$answer   = wp_strip_all_tags( wp_trim_words( $post->post_content, 120, '' ) );
			$answer   = trim( preg_replace( '/\s+/', ' ', $answer ) );

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$items[] = array(
				'question' => $question,
				'answer'   => $answer,
			);
		}

		wp_reset_postdata();

		return $items;
	}
}
