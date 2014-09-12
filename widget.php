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

		function form( $instance ) {
		if ( $instance ) {
			$title = $instance['title'];
		} else {
			$title = __( 'User Status' , 'cf-user-status');
		}
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'cf-user-status'); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p>

<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function widget( $args, $instance ) {
		$cf_users_status = new CF_User_Status;

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
		$cf_users_status->output_users();
		echo $args['after_widget'];
	}
}

function cf_user_status_register_widgets() {
	register_widget( 'CF_User_Status_Widget' );
}
add_action( 'widgets_init', 'cf_user_status_register_widgets' );
