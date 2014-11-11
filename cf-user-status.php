<?php
/**
 * @package user-status
 */
/*
Plugin Name: User Status
Plugin URI: http://crowdfavorite.com
Description: A plugin to show who is currently on or offline
Version: 0.1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
License: GPLv2 or later
*/

/*
 * Copyright (c) 2012-2014 Crowd Favorite, Ltd. All rights reserved.
 * http://crowdfavorite.com
 *
 * **********************************************************************
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * **********************************************************************
 */

include_once( 'widget.php' );

class CF_User_Status {

	function __construct() {}

	public static function hooks() {
		add_action( 'init', array( get_called_class(), 'update_activity' ) );
		// This fires right as the user is being "logged out" the wp_logout hook does not return a user id.
		add_action( 'clear_auth_cookie', array( get_called_class(), 'logout' ) );
	}

	public static function update_activity( $user_id = null, $time_override = null) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( $user_id ) {
			$users_activity = self::get_users_last_activity();
			if ( is_array( $users_activity ) ) {
				if ( null !== $time_override ) {
					$users_activity[ $user_id ] = $time_override;
				}
				else {
					$users_activity[ $user_id ] = time();
				}
				self::set_users_activity( $users_activity );
			}
			else {
				self::set_users_activity( array() );
			}
		}
	}

	public static function user_idle_time() {
		// Length of time a user can be idle and still be considered online
		return apply_filters( 'cf_user_status_idle_time', 60 * 10 );
	}

	public static function get_users_last_activity() {
		return get_option( 'cf_users_activity', array() );
	}

	public static function set_users_activity( $activity_array ) {
		/*
		 * A single option is used instead of transients to prevent having to
		 * make a database call per user, now its just one and processing is
		 * done via php.
		 */
		return update_option( 'cf_users_activity', $activity_array );
	}


	function is_user_online( $user ) {
		if ( ! is_object( $user ) ) {
			$user = $user->ID;
		} else if ( is_array( $user ) && isset( $user['ID'] ) ) {
			$user = $user['ID'];
		}

		if ( $user ) {
			$users_last_activity = self::get_users_last_activity();
			if ( isset( $users_last_activity[ $user ] ) ) {
				$last_update = $users_last_activity[ $user ];
				if ( $last_update && self::user_idle_time() > time() - $last_update ) {
					return true;
				}
			}
		}

		return false;
	}

	function get_users() {
		$users_last_activity = self::get_users_last_activity();
		$users_online = array();
		$cur_time = time();
		$idle_time = self::user_idle_time();

		$user_statuses = array();
		foreach ($users_last_activity as $user_id => $last_update) {
			if ( $last_update && $idle_time > $cur_time - $last_update ) {
				$user_statuses[ $user_id ] = true;
			}
		}

		$user_query_args = apply_filters( cf_user_status_query_args, array(
			'number' => false
		) );

		$users_query = new WP_User_Query( $user_query_args );

		$users = array();
		if ( $users_query->results ) {
			foreach ($users_query->results as $user) {
				if ( array_key_exists( $user->ID, $user_statuses ) ) {
					$user->online = true;
				} else {
					$user->online = false;
				}
				$users[] = $user;
			}
		}

		return apply_filters( 'cf_user_status_users', $users );
	}

	function output_users() {
		$users = $this->get_users();
		$markup = apply_filters( 'cf_user_status_output_before', '<ul class="cf-user-status">' );
		foreach ( $users as $user ) {
			$online = $user->online  ? 'Online' : 'Offline';
			$row = '<li>' . get_avatar( $user->ID, 34 ) . esc_html( $user->display_name . ' ' . $online ) . '</li>';
			$markup .= apply_filters( 'cf_user_status_output_row', $row, $user );
		}
		$markup .= apply_filters( 'cf_user_status_output_after', '</ul>' );
		echo $markup;
	}

	function logout() {
		$user_id = get_current_user_id();
		self::update_activity( $user_id, 0 );
	}

}

function cf_user_status_output() {
	$cf_user_status = new CF_User_Status;
	$cf_user_status->output_users;
}

CF_User_Status::hooks();
