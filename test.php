<?php
// Create plugin menu in the WordPress dashboard
function rewards_system_menu() {
	add_menu_page( 'Rewards System', 'Rewards System', 'manage_options', 'rewards_system', 'rewards_system_settings' );
	add_submenu_page( 'rewards_system', 'Rewards Settings', 'Rewards Settings', 'manage_options', 'rewards_settings', 'rewards_system_settings' );
	add_submenu_page( 'rewards_system', 'Spend Points', 'Spend Points', 'manage_options', 'spend_points', 'spend_points_settings' );
}
add_action( 'admin_menu', 'rewards_system_menu' );
 
// Plugin settings page callback function
function rewards_system_settings() {
	// Display rewards system settings
}
 
// Plugin spend points page callback function
function spend_points_settings() {
	// Display spend points page
	// Allow user to spend points
	// Convert points to BAM (500 points = -50BAM of order)
}