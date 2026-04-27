<?php
/**
 * Plugin Name: Tableau Embed Shortcode
 * Description: Provides an accessible Tableau embed shortcode for reusable dashboard embeds.
 * Version: 1.0.4
 * Author: Lobsang Wangdu
 * Author URI: https://ucnature.org/
 * License: GPLv2 or later
 * Text Domain: tableau-embed-shortcode
 *
 * @package Tableau_Embed_Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tableau_embed_shortcode_sanitize_name' ) ) {
	/**
	 * Sanitizes the Tableau Public workbook/view path.
	 *
	 * @param string $name Tableau workbook/view path.
	 * @return string
	 */
	function tableau_embed_shortcode_sanitize_name( $name ) {
		$charset = get_bloginfo( 'charset' );
		$charset = '' !== $charset ? $charset : 'UTF-8';
		$name    = html_entity_decode( wp_unslash( (string) $name ), ENT_QUOTES, $charset );
		$name    = rawurldecode( $name );
		$parsed  = wp_parse_url( $name );

		if ( is_array( $parsed ) && isset( $parsed['host'], $parsed['path'] ) ) {
			$host = strtolower( $parsed['host'] );

			if ( 'public.tableau.com' === $host && 0 === strpos( $parsed['path'], '/views/' ) ) {
				$name = substr( $parsed['path'], strlen( '/views/' ) );
			}
		}

		$name = sanitize_text_field( $name );
		$name = preg_split( '/[?#]/', $name );
		$name = is_array( $name ) ? reset( $name ) : '';
		$name = trim( (string) $name, " \t\n\r\0\x0B/" );
		$name = preg_replace( '/[^A-Za-z0-9_.\/-]/', '', $name );
		$name = preg_replace( '#/+#', '/', $name );

		if ( false === strpos( $name, '/' ) ) {
			return '';
		}

		return $name;
	}
}

if ( ! function_exists( 'tableau_embed_shortcode_build_public_url' ) ) {
	/**
	 * Builds a safe Tableau Public embed URL.
	 *
	 * @param string $name       Sanitized Tableau workbook/view path.
	 * @param string $public_url Optional custom Tableau Public URL.
	 * @return string
	 */
	function tableau_embed_shortcode_build_public_url( $name, $public_url ) {
		$path_segments = array_map( 'rawurlencode', explode( '/', $name ) );
		$generated_url = 'https://public.tableau.com/views/' . implode( '/', $path_segments ) . '?:showVizHome=no&:embed=true';
		$public_url    = esc_url_raw( $public_url, array( 'https' ) );

		if ( '' === $public_url ) {
			return $generated_url;
		}

		$parsed_public_url = wp_parse_url( $public_url );
		$public_host       = isset( $parsed_public_url['host'] ) ? strtolower( $parsed_public_url['host'] ) : '';
		$public_path       = isset( $parsed_public_url['path'] ) ? $parsed_public_url['path'] : '';
		$public_scheme     = isset( $parsed_public_url['scheme'] ) ? strtolower( $parsed_public_url['scheme'] ) : '';

		if ( 'https' !== $public_scheme ) {
			return $generated_url;
		}

		if ( 'public.tableau.com' !== $public_host ) {
			return $generated_url;
		}

		if ( 0 !== strpos( $public_path, '/views/' ) ) {
			return $generated_url;
		}

		return $public_url;
	}
}

if ( ! function_exists( 'tableau_embed_shortcode_render' ) ) {
	/**
	 * Renders a reusable Tableau embed shortcode.
	 *
	 * Example:
	 * [tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	function tableau_embed_shortcode_render( $atts ) {
		static $instance = 0;
		static $styles_printed = false;

		$atts = shortcode_atts(
			array(
				'title'         => '',
				'name'          => '',
				'public_url'    => '',
				'height'        => '827',
				'mobile_height' => '727',
				'heading'       => 'h2',
				'summary'       => '',
				'show_link'     => 'true',
				'hide_title'    => 'false',
			),
			$atts,
			'tableau_embed'
		);

		$title   = sanitize_text_field( $atts['title'] );
		$name    = tableau_embed_shortcode_sanitize_name( $atts['name'] );
		$summary = sanitize_textarea_field( $atts['summary'] );

		if ( '' === $title || '' === $name ) {
			return '';
		}

		$allowed_names = apply_filters( 'tableau_embed_shortcode_allowed_names', array() );
		if ( is_array( $allowed_names ) && ! empty( $allowed_names ) && ! in_array( $name, $allowed_names, true ) ) {
			return '';
		}

		$instance++;

		$heading_levels = array( 'h2', 'h3', 'h4' );
		$heading        = strtolower( $atts['heading'] );
		$heading_tag    = in_array( $heading, $heading_levels, true ) ? $heading : 'h2';
		$height         = max( 200, absint( $atts['height'] ) );
		$mobile_height  = max( 200, absint( $atts['mobile_height'] ) );
		$show_link      = filter_var( $atts['show_link'], FILTER_VALIDATE_BOOLEAN );
		$hide_title     = filter_var( $atts['hide_title'], FILTER_VALIDATE_BOOLEAN );
		$slug           = sanitize_title( $title );
		$slug           = '' !== $slug ? $slug : 'tableau-chart-' . $instance;
		$title_id       = 'tableau-title-' . $slug . '-' . $instance;
		$summary_id     = 'tableau-summary-' . $slug . '-' . $instance;
		$fallback_id    = 'tableau-fallback-' . $slug . '-' . $instance;
		$viz_id         = 'tableau-viz-' . $slug . '-' . $instance;
		$describedby    = array();

		if ( '' !== $summary ) {
			$describedby[] = $summary_id;
		}

		if ( $show_link ) {
			$describedby[] = $fallback_id;
		}

		$describedby_attr = implode( ' ', $describedby );
		$public_url       = tableau_embed_shortcode_build_public_url( $name, $atts['public_url'] );
		$iframe_title     = sprintf(
			/* translators: %s: Tableau visualization title. */
			__( '%s interactive Tableau visualization', 'tableau-embed-shortcode' ),
			$title
		);
		$fallback_label   = sprintf(
			/* translators: %s: Tableau visualization title. */
			__( 'Open %s on Tableau Public', 'tableau-embed-shortcode' ),
			$title
		);
		$fallback_text    = $hide_title ? __( 'Open this chart on Tableau Public', 'tableau-embed-shortcode' ) : $fallback_label;
		$noscript_label   = sprintf(
			/* translators: %s: Tableau visualization title. */
			__( 'View %s on Tableau Public', 'tableau-embed-shortcode' ),
			$title
		);
		$noscript_text    = $hide_title ? __( 'View this chart on Tableau Public', 'tableau-embed-shortcode' ) : $noscript_label;
		$title_class      = $hide_title ? 'screen-reader-text tableau-screen-reader-text' : '';
		$height_style     = sprintf(
			'--tableau-desktop-height: %1$dpx; --tableau-mobile-height: %2$dpx;',
			$height,
			$mobile_height
		);

		ob_start();

		if ( ! $styles_printed ) {
			$styles_printed = true;
			?>
			<style>
				.tableau-chart {
					margin: 1.5rem 0;
				}

				.screen-reader-text.tableau-screen-reader-text,
				.tableau-screen-reader-text {
					border: 0;
					clip: rect(1px, 1px, 1px, 1px);
					-webkit-clip-path: inset(50%);
					clip-path: inset(50%);
					height: 1px;
					margin: -1px;
					overflow: hidden;
					padding: 0;
					position: absolute !important;
					width: 1px;
					word-wrap: normal !important;
				}

				.tableau-placeholder {
					max-width: 100%;
					min-height: var(--tableau-mobile-height, 727px);
					position: relative;
					width: 100%;
				}

				.tableau-embed-frame {
					border: 0;
					display: block;
					height: var(--tableau-mobile-height, 727px);
					max-width: 100%;
					width: 100%;
				}

				@media (min-width: 783px) {
					.tableau-placeholder {
						min-height: var(--tableau-desktop-height, 827px);
					}

					.tableau-embed-frame {
						height: var(--tableau-desktop-height, 827px);
					}
				}
			</style>
			<?php
		}
		?>
		<section class="tableau-chart" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
			<<?php echo tag_escape( $heading_tag ); ?> id="<?php echo esc_attr( $title_id ); ?>"<?php if ( '' !== $title_class ) : ?> class="<?php echo esc_attr( $title_class ); ?>"<?php endif; ?>>
				<?php echo esc_html( $title ); ?>
			</<?php echo tag_escape( $heading_tag ); ?>>

			<?php if ( '' !== $summary ) : ?>
				<p id="<?php echo esc_attr( $summary_id ); ?>"><?php echo esc_html( $summary ); ?></p>
			<?php endif; ?>

			<div
				class="tableau-placeholder"
				id="<?php echo esc_attr( $viz_id ); ?>"
				style="<?php echo esc_attr( $height_style ); ?>"
				role="region"
				aria-labelledby="<?php echo esc_attr( $title_id ); ?>"
				<?php if ( ! empty( $describedby_attr ) ) : ?>
					aria-describedby="<?php echo esc_attr( $describedby_attr ); ?>"
				<?php endif; ?>
			>
				<noscript>
					<p>
						<a href="<?php echo esc_url( $public_url ); ?>" aria-label="<?php echo esc_attr( $noscript_label ); ?>">
							<?php echo esc_html( $noscript_text ); ?>
						</a>
					</p>
				</noscript>

				<iframe
					class="tableau-embed-frame"
					src="<?php echo esc_url( $public_url ); ?>"
					title="<?php echo esc_attr( $iframe_title ); ?>"
					loading="lazy"
					referrerpolicy="strict-origin-when-cross-origin"
					sandbox="allow-downloads allow-forms allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
					allow="fullscreen"
					allowfullscreen
					<?php if ( ! empty( $describedby_attr ) ) : ?>
						aria-describedby="<?php echo esc_attr( $describedby_attr ); ?>"
					<?php endif; ?>
				></iframe>
			</div>

			<?php if ( $show_link ) : ?>
				<p id="<?php echo esc_attr( $fallback_id ); ?>">
					<a href="<?php echo esc_url( $public_url ); ?>" aria-label="<?php echo esc_attr( $fallback_label ); ?>">
						<?php echo esc_html( $fallback_text ); ?>
					</a>
				</p>
			<?php endif; ?>
		</section>
		<?php

		return ob_get_clean();
	}
}

add_shortcode( 'tableau_embed', 'tableau_embed_shortcode_render' );

if ( ! function_exists( 'tableau_embed_shortcode_examples_page' ) ) {
	/**
	 * Renders the admin examples page.
	 *
	 * @return void
	 */
	function tableau_embed_shortcode_examples_page() {
		$examples = array(
			'[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827" mobile_height="727"]',
			'[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827" mobile_height="727" summary="Interactive Tableau dashboard showing user numbers."]',
			'[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" hide_title="true" height="827" mobile_height="727"]',
			'[tableau_embed title="Reserve Map" name="YOURWORKBOOK/YOURDASHBOARD" height="827" mobile_height="727"]',
			'[tableau_embed title="Reserve Visits" name="YOURWORKBOOK/YOURDASHBOARD" height="600" mobile_height="420" summary="Interactive Tableau chart showing reserve visits."]',
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tableau Embed Shortcode Examples', 'tableau-embed-shortcode' ); ?></h1>
			<p><?php esc_html_e( 'Plugin version: 1.0.4', 'tableau-embed-shortcode' ); ?></p>
			<p><?php esc_html_e( 'Copy one of the shortcode examples below into a page, post, or shortcode-enabled block.', 'tableau-embed-shortcode' ); ?></p>

			<h2><?php esc_html_e( 'Examples', 'tableau-embed-shortcode' ); ?></h2>
			<?php foreach ( $examples as $example ) : ?>
				<p><code><?php echo esc_html( $example ); ?></code></p>
			<?php endforeach; ?>

			<h2><?php esc_html_e( 'Attributes', 'tableau-embed-shortcode' ); ?></h2>
			<ul style="list-style: disc; padding-left: 1.5rem;">
				<li><code>title</code> <?php esc_html_e( 'Required. Visible chart title and accessible label.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>name</code> <?php esc_html_e( 'Required. Tableau workbook/dashboard name in the format WORKBOOK_NAME/DASHBOARD_NAME.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>public_url</code> <?php esc_html_e( 'Optional. Exact Tableau Public iframe and fallback URL.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>height</code> <?php esc_html_e( 'Optional. Desktop height in pixels. Default: 827.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>mobile_height</code> <?php esc_html_e( 'Optional. Mobile height in pixels. Default: 727.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>heading</code> <?php esc_html_e( 'Optional. Allowed values: h2, h3, h4. Default: h2.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>summary</code> <?php esc_html_e( 'Optional. Short explanatory text shown under the title.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>show_link</code> <?php esc_html_e( 'Optional. Use false to hide the visible Tableau Public fallback link. Default: true.', 'tableau-embed-shortcode' ); ?></li>
				<li><code>hide_title</code> <?php esc_html_e( 'Optional. Use true to hide the title visually while keeping it available to screen readers.', 'tableau-embed-shortcode' ); ?></li>
			</ul>
		</div>
		<?php
	}
}

if ( ! function_exists( 'tableau_embed_shortcode_admin_menu' ) ) {
	/**
	 * Adds the examples page under Settings.
	 *
	 * @return void
	 */
	function tableau_embed_shortcode_admin_menu() {
		add_options_page(
			__( 'Tableau Embed Shortcode', 'tableau-embed-shortcode' ),
			__( 'Tableau Embed', 'tableau-embed-shortcode' ),
			'manage_options',
			'tableau-embed-shortcode',
			'tableau_embed_shortcode_examples_page'
		);
	}
}
add_action( 'admin_menu', 'tableau_embed_shortcode_admin_menu' );

if ( ! function_exists( 'tableau_embed_shortcode_action_links' ) ) {
	/**
	 * Adds a quick link to the examples page from the Plugins list.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	function tableau_embed_shortcode_action_links( $links ) {
		$links[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'options-general.php?page=tableau-embed-shortcode' ) ),
			esc_html__( 'Shortcode Examples', 'tableau-embed-shortcode' )
		);

		return $links;
	}
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tableau_embed_shortcode_action_links' );
