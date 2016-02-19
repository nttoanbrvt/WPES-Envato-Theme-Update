<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}
if( ! class_exists( 'WPES_Envato_Theme_Update' ) ){
	/**
	 * @author Toan Nguyen
	 * @Class:  WPES_Envato_Theme_Update
	 * @Version: 1.1
	 * @URL: http://themeforest.net/user/phpface
	 * @Description: Automatic update your theme through Envato APIs, made for Theme Authors on Envato Marketplace.
	 * @Required permissions: "Download your purchased items" and "View your purchases of the app creator's items"
	 */
	class WPES_Envato_Theme_Update {
		
		/**
		 * Holds the verify api url
		 * @var string
		 */
		private $apiurl_verify_purchase_code = 'https://api.envato.com/v3/market/buyer/purchase?code={purchase_code}';
		
		/**
		 * Holds the download api url.
		 * @var string
		 */
		private $apiurl_download_purchase = 'https://api.envato.com/v3/market/buyer/download?purchase_code={purchase_code}&shorten_url=true';
		
		/**
		 * Holds the Personal Token.
		 * https://build.envato.com/create-token/
		 * @var string
		 */
		private $access_token = '';
		
		/**
		 * Holds the theme slug
		 * @var string
		 */
		private $slug = '';
		
		/**
		 * Holds the purchase code
		 * @var string
		 */
		private $purchase_code = ''; 
		
		/**
		 * Holds the remote theme name
		 * @var string
		 */
		private $remote_theme_name = '';
		
		/**
		 * Holds the remote theme version.
		 * @var string
		 */
		private $remote_theme_version = '';
		
		/**
		 * Holds the request method.
		 * @var unknown_type
		 */
		private $request_method = 'GET';
		
		/**
		 * Holds the request timeout
		 * @var unknown_type
		 */
		private $request_timeout = '45';
		
		/**
		 * Holds the current WP version
		 * @var string
		 */
		public $wp_version = '';
		
		/**
		 * Holds the require PHP version.
		 * @var unknown_type
		 */
		public $require_php_version = '5.3.0';
		
		/**
		 * Enable debug mode.
		 * @var unknown_type
		 */
		private $debug = false;
		
		/**
		 * 
		 * @param string $slug
		 * @param string $purchase_code
		 * @param string $access_token
		 * @param boolean $debug
		 */
		function __construct( $slug, $purchase_code = '', $access_token = '', $debug = false ) {
			
			global $wp_version;
			$this->apiurl_verify_purchase_code	=	str_replace( "{purchase_code}" , $purchase_code , $this->apiurl_verify_purchase_code );
			$this->apiurl_download_purchase		=	str_replace( "{purchase_code}" , $purchase_code , $this->apiurl_download_purchase );
			$this->slug 						=	$slug;
			$this->purchase_code 				=	$purchase_code;	
			$this->access_token 				=	$access_token;
			$this->wp_version 					=	$wp_version;
			
			add_filter( 'pre_set_site_transient_update_themes', array( $this , 'check_for_update' ) );
			if( $debug === true ){
				// For debug purpose.
				set_site_transient( 'update_themes', null );
			}
			add_action( 'wp_ajax_wpes_view_changelogs' , array( $this , 'get_changelogs'  ) );
		}
		
		/**
		 * Update the changelogs, known as item description.
		 * @param string $theme_name
		 * @param string $changelogs
		 */
		function update_changelogs( $theme, $changelogs ){
			if( ! empty( $changelogs ) ){
				update_option(  '___changelogs_' . $theme , $changelogs );
			}
		}
		
		function get_changelogs(){
			$theme = isset( $_REQUEST['theme'] ) ? esc_attr( $_REQUEST['theme'] ) : '';
			if( $theme ){
				print get_option( '___changelogs_' . $theme );
				exit;
			}
		}
		
		/**
		 * Get the download url.
		 * Download Limit Reached? https://help.market.envato.com/hc/en-us/articles/202821300?_ga=1.102499521.1105276486.1445180258
		 * @param string $apiurl
		 * @return mixed
		 */
		function get_remote_data( $apiurl ){
			$args = array(
				'method' => $this->request_method,
				'timeout' => 45,
				'user-agent'  => 'WordPress/' . $this->wp_version . '; ' . esc_url( home_url() ),
				'redirection' => $this->request_timeout,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers'	=>	array( 'Authorization' => 'Bearer ' . $this->access_token ),
				'cookies' => array()
			);		
			
			$args = apply_filters( 'wpes_get_remote_data' , $args );
			
			$response = wp_remote_get( $apiurl , $args );
			
			if( is_wp_error( $response ) ){
				if( defined( 'PHP_VERSION' ) && version_compare( PHP_VERSION , $this->require_php_version, '>' ) ){
					// Print the error.
					$error_code 	=	$response->get_error_code();
					$error_message	=	$response->get_error_message();
					add_action( 'admin_notices' , function() use( $error_code, $error_message ){
						$output = ' <div class="error notice">';
							$output .= '<p>'. sprintf( '[%s] %s', $error_code, $error_message ) .'</p>';
						$output .= '</div>';
						print $output;
					});
				}
			}
			else{
				if( wp_remote_retrieve_response_code( $response ) == 200 ){
					return json_decode( wp_remote_retrieve_body( $response ) );
				}				
			}
		}
		
		function get_download_url(){
			
			if( get_transient( $this->slug ) !== false ){
				return get_transient( $this->slug );
			}
			else{
				$remoteData = $this->get_remote_data( $this->apiurl_download_purchase );
				$download_url = ! empty( $remoteData->wordpress_theme ) ? rawurldecode( $remoteData->wordpress_theme ) : '';
				if( $download_url ){
					set_transient( $this->slug , $download_url, 3600 );
					return $download_url;
				}
			}
		}
		
		/**
		 * Check if update.
		 * @param object $transient
		 */
		function check_for_update( $transient ){
			
			$current_theme_name	=	'';
			$current_theme_version	=	'';
			
			$theme = wp_get_theme();
			
			if( $theme->parent() ){
				$current_theme_name	=	$theme->parent()->get('Name');
				$current_theme_version	=	$theme->parent()->get('Version');
			}
			else{
				$current_theme_name	=	$theme->get('Name');
				$current_theme_version	=	$theme->get('Version');
			}

			if ( empty ($transient->checked ) ){
				return $transient;
			}

			$remoteData = $this->get_remote_data( $this->apiurl_verify_purchase_code );

			$this->remote_theme_name = isset( $remoteData->item->wordpress_theme_metadata->theme_name ) ? rawurldecode( $remoteData->item->wordpress_theme_metadata->theme_name ) : '';
			$this->remote_theme_version = isset( $remoteData->item->wordpress_theme_metadata->version ) ? rawurldecode( $remoteData->item->wordpress_theme_metadata->version ) : '';
			
			if( $this->remote_theme_name == $current_theme_name && version_compare( $this->remote_theme_version, $current_theme_version, '>' ) ){
				
				// Update the changelogs
				$this->update_changelogs( $theme , $remoteData->item->description );
				
				$data['new_version']	=	$this->remote_theme_version;
				$data['url']			=	esc_url( admin_url( 'admin-ajax.php' ) ) . '?action=wpes_view_changelogs&theme=' . $this->remote_theme_name;
				$data['package']		=	$this->get_download_url();
			}
			
			if( ! empty( $data ) ){
				// For debugging purpose.
				//print_r( $data );
				$transient->response[ $this->slug ] = $data;
			}
			return $transient;
		}
	}
}