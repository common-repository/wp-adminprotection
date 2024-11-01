<?php
/*
Plugin Name: WP Adminprotection
Plugin URI: http://horttcore.de/plugin/wp-adminprotection/
Description: Protect your WP-Backend with an IP filter
Version: 2.0.2
Author: Ralf Hortt
Author URI: http://horttcore.de/
*/



/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists('add_action') ) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;



/**
 *
 * Plugin Definitions
 *
 */
define( 'RH_WPA_BASENAME', plugin_basename(__FILE__) );
define( 'RH_WPA_BASEDIR', dirname( plugin_basename(__FILE__) ) );



/**
* WP Adminprotection
*/
class WP_Adminprotection
{



	/**
	 *
	 * Constructor
	 *
	 */
	function __construct()
	{
		add_action( 'admin_init', array( $this, 'logout' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'settings_page_wp-adminprotection', array( $this, 'enqueue_script' ) );
		add_action( 'settings_page_wp-adminprotection', array( $this, 'enqueue_style' ) );
		add_action( 'login_head', array( $this, 'kill' ) );
		add_action( 'init', array( $this, 'init' ) );

		register_activation_hook( __FILE__, array( $this, 'install') );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall') );
	}



	/**
	 * Add Submenu Page
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function admin_menu()
	{
		add_submenu_page( 'options-general.php', __( 'WP Adminprotection', 'wp-adminprotection' ), __( 'Adminprotection', 'wp-adminprotection' ), 'delete_user', 'wp-adminprotection', array( $this, 'settings' ) );
	}



	/**
	 * Enqueue Javascript
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function enqueue_script()
	{
		wp_enqueue_script( 'wp-adminprotection', WP_PLUGIN_URL . '/' . RH_WPA_BASEDIR . '/javascript/wp-adminprotection.js', array('jquery') );
	}



	/**
	 * Enqueue CSS
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function enqueue_style()
	{
		wp_enqueue_style( 'wp-adminprotection', WP_PLUGIN_URL . '/' . RH_WPA_BASEDIR . '/css/wp-adminprotection.css' );
	}



	/**
	 * Kill Template
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	protected function error_template( $return = TRUE )
	{
		$temp = '</head>';
		$temp.= '<body class="login">';
		$temp.= '<div id="login"><h1><a href="' . esc_url( apply_filters( 'login_headerurl', 'http://wordpress.org/' ) ) . '" title="' . esc_attr( apply_filters( 'login_headertitle', __( 'Powered by WordPress' ) ) ) . '">' . get_bloginfo( 'name' ) . '</a></h1>';
		$temp.= '<form id="loginform">';
		$temp.= '<div class="message error"><p>' . apply_filters( 'wp-adminprotection-error-message', __( 'You are not welcome', 'wp-adminprotection' ) ) . '</p></div>';
		$temp.= '</form>';
		$temp.= '</body>';
		$temp.= '</html>';
		
		$temp = apply_filters( 'wp-adminprotection-kill-template', $temp );
		
		if ( TRUE === $return ) :
			return $temp;
		else :
			echo $temp;
		endif;
	}



	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function init()
	{
		load_plugin_textdomain( 'wp-adminprotection', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'  );
	}



	/**
	 * Install
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function install()
	{	
		if ( !get_option( 'wp-adminprotection' ) ) :
		
			// Backwards compability
			if ( $ips = get_option( 'ap_ips' ) ) :
				$options['active'] = get_option( 'ap_activated' );
				$options['ips'] = $ips;
				update_option( 'wp-adminprotection', $options );
				delete_option( 'ap_activated' );
				delete_option( 'ap_ips' );
			// Default options
			else :
				$options['activated'] = FALSE;
				$options['ips'] = $_SERVER['REMOTE_ADDR'];
				update_option( 'wp-adminprotection', $options );
			endif;

		endif;
	}



	/**
	 * Checks if Protection is activated
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function is_active()
	{
		$options = get_option( 'wp-adminprotection' );
		
		if ( 1 == $options['activated'] ) :
			return TRUE;
		else :
			return FALSE;
		endif;
	}



	/**
	 * Conditional function if it's an allowed IP
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function is_allowed_ip( $check_ip = FALSE )
	{
		$options = get_option( 'wp-adminprotection' );
		$ips = explode( "\n", $options['ips'] );
		$check_ip = ( $ip ) ? $ip : $_SERVER['REMOTE_ADDR'];
		
		$check = FALSE;
		
		if ( is_array( $ips ) ) :
		
			foreach( $ips as $ip ) :

				// Remove whitespaces
				$ip = trim( $ip );

				// Exact IP
				if ( $check_ip == $ip ) 
					return TRUE;

				// Pattern
				if ( FALSE !== strpos( $ip, '*') ) :
					$pattern = '&' . str_replace( array("\n", ' ', '.', '*'), array('', '', '\.', '.*'), $ip) . '&';
					
					if ( @preg_match( $pattern, $check_ip ) ) :
						return TRUE;
					endif;
				endif;
				
			endforeach;

		endif;
		
		return FALSE;
		
	}



	/**
	 * Kill
	 *
	 * @return void
	 * @author Ralf Hortt
	 */
	public function kill()
	{
		if ( !$this->is_allowed_ip() && $this->is_active() )
			die( $this->error_template() );
	}



	/**
	 * Logout
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function logout()
	{
		if ( !$this->is_allowed_ip() && $this->is_active() )
			header( 'Location:' . str_replace('&amp;', '&', wp_logout_url() ) );
	}



	/**
	 * Options Page
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function settings()
	{
		// Save Options
		if ( wp_verify_nonce( $_POST['wp-adminprotection-nonce'], 'wp-adminprotection-save-options' ) ) :
			$options['activated'] = ( 1 == $_POST['wpa-activated'] ) ? TRUE : FALSE;
			$options['ips'] = $_POST['allowed_ips'];
			update_option( 'wp-adminprotection', $options );
			?>
			<div class="fade updated" id="message">
				<p><strong><?php _e( 'Settings saved.', 'wp-adminprotection' ) ?></strong></p>
			</div>
			<?php
		endif;
		
		$options = get_option( 'wp-adminprotection' );
		?>
		
		<div class="wrap">
			<h2><img alt="" title="" src="<?php echo WP_PLUGIN_URL . '/' . RH_WPA_BASEDIR ?>/images/icon-lock.png"><?php _e( 'WP Adminprotection', 'wp-adminprotection' ) ?></h2>
			<form method="post">
				<table class="form-table">
					<tr>
					<th scope="row"><?php _e( 'Security', 'wp-adminprotection' ) ?></th>
					<td>
						<p>
							<input id="wpa-activated" type="checkbox" name="wpa-activated" value="1" <?php checked(1, $options['activated']) ?>>
							<label for="wpa-activated"><?php _e( 'I want to secure my WP-Backend with WP-Adminprotection', 'wp-adminprotection' ) ?></label>
						</p>
	
						<p>
							<textarea id="allowed_ips" style="width: 385px" name="allowed_ips" rows="10"><?php echo $options['ips'] ?></textarea><br />
							<label for="allowed_ips"><small><?php _e( '1 IP per line', 'wp-adminprotection' ) ?></small></label>
						</p>
					</td>
					</tr>
					<tr>
						<th><?php _e( 'Your current IP is', 'wp-adminprotection' ) ?></th>
						<td><strong><?php echo $_SERVER['REMOTE_ADDR'];?></strong></td>
					</tr>
					<tr>
						<th><?php _e( 'Checking your IP', 'wp-adminprotection' ) ?></th>
						<td>
							<?php
							if ( $this->is_allowed_ip() ) :
								_e( 'Your IP is <strong>accepted</strong> by WP Adminprotection',  'wp-adminprotection' );
							else :
								_e( 'Your IP <strong>failed</strong> WP-Adminprotection', 'wp-adminprotection' );
							endif;
							?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Usage', 'wp-adminprotection' ) ?></th>
						<td>
							<?php _e( 'Enter any IP that should be allowed to login<br />Use \'*\' as a Wildcard i.e. <em>192.*</em> or <em>192.168.*.*</em><br />Be sure that your own IP is allowed before you activate the protection,<br>else you have to delete the options ´wp-adminprotection´ in your options table', 'wp-adminprotection' ) ?>
						</td>
					</tr>
				</table>
				<p class="submit"><button class="button button-primary" type="submit"><?php _e('Save changes'); ?></button></p>
				<?php wp_nonce_field( 'wp-adminprotection-save-options', 'wp-adminprotection-nonce' ) ?> 
			</form>
		</div>
		<?php
	}



	/**
	 * Delete Option after deinstallation
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	static public function uninstall()
	{
		delete_option( 'wp-adminprotection' );
	}



}

$WP_Adminprotection = new WP_Adminprotection;