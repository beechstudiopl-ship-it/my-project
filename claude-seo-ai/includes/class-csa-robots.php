<?php
/**
 * Automatyczne dodawanie botow AI do robots.txt.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Robots
 */
class CSA_Robots {

	/**
	 * Lista botow AI, ktore chcemy jawnie wpuscic.
	 *
	 * @return array
	 */
	public static function ai_bots() {
		return array(
			'GPTBot',            // OpenAI / ChatGPT
			'OAI-SearchBot',     // ChatGPT Search
			'ChatGPT-User',      // ChatGPT (dzialania uzytkownika)
			'ClaudeBot',         // Anthropic / Claude
			'Claude-Web',        // Claude (przegladanie)
			'anthropic-ai',      // Anthropic (starszy UA)
			'PerplexityBot',     // Perplexity
			'Perplexity-User',   // Perplexity (dzialania uzytkownika)
			'Google-Extended',   // Google Gemini / AI Overviews
			'Applebot-Extended', // Apple Intelligence
			'Amazonbot',         // Amazon
			'Bytespider',        // TikTok / Doubao
			'CCBot',             // Common Crawl (zrodlo treningowe wielu modeli)
			'meta-externalagent',// Meta AI
		);
	}

	/**
	 * Konstruktor: podpina filtr robots.txt.
	 */
	public function __construct() {
		add_filter( 'robots_txt', array( $this, 'append_ai_bots' ), 20, 2 );
	}

	/**
	 * Dopisuje reguly dla botow AI do wygenerowanego robots.txt.
	 *
	 * Uwaga: dziala tylko dla wirtualnego robots.txt generowanego przez WordPress
	 * (gdy w katalogu glownym NIE ma statycznego pliku robots.txt).
	 *
	 * @param string $output Aktualna zawartosc robots.txt.
	 * @param bool   $public Czy witryna jest publiczna.
	 * @return string
	 */
	public function append_ai_bots( $output, $public ) {
		$settings = csa_get_settings();

		if ( empty( $settings['enable_ai_bots'] ) ) {
			return $output;
		}

		// Jesli witryna jest ustawiona jako niepubliczna, nie forsujemy dostepu.
		if ( ! $public ) {
			return $output;
		}

		$lines   = array();
		$lines[] = '';
		$lines[] = '# --- Claude SEO: boty AI (dostep do treningu i cytowania) ---';

		foreach ( self::ai_bots() as $bot ) {
			$lines[] = 'User-agent: ' . $bot;
			$lines[] = 'Allow: /';
			$lines[] = '';
		}

		$lines[] = '# --- koniec sekcji Claude SEO ---';

		return $output . "\n" . implode( "\n", $lines ) . "\n";
	}
}
