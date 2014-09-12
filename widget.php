<?php



class CF_User_Status_Widget extends WP_Widget {

	function __construct() {

		load_plugin_textdomain( 'cf-user-status' );

		parent::__construct(
			'cf_user_status_widget',
			__( 'User Status Widget' , 'cf-user-status'),
			array( 'description' => __( 'Display a list of users and whether they are online of offline' , 'cf-user-status' ) )
		);

	}

	function widget($args, $instance) {
		$cf_users_status = new CF_User_Status;
		$cf_users_status->output_users();
	}

}

function cf_user_status_register_widgets() {
	register_widget( 'CF_User_Status_Widget' );
}
add_action( 'widgets_init', 'cf_user_status_register_widgets' );
