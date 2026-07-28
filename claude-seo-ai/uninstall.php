<?php
/**
 * Sprzatanie danych przy odinstalowaniu wtyczki.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Usun zapisane ustawienia.
delete_option( 'csa_settings' );

// Uwaga: wpisow FAQ (typ tresci csa_faq) NIE usuwamy automatycznie,
// aby nie skasowac tresci przygotowanej przez uzytkownika.
