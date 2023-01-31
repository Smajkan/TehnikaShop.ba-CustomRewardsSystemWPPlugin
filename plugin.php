<?php

/*
* Plugin Name: CustomPluginRewards By Smajkan
* Description: A rewards system plugin for WordPress.
* Version: 1.0
* Author: Aldin Smajkan
* Author URI: Your Website URL
*/


// Create the main plugin class
class Rewards_System {

    public function __construct() {
        // Add the plugin menu item and submenus
        add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action('admin_init', array($this, 'register_ajax_actions'));
        add_action( 'woocommerce_payment_complete', array( $this, 'track_order_event' ) );
        add_action( 'register_new_user', array( $this, 'add_user_to_rewards_table' ) );

    }

    public function add_user_to_rewards_table( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . "user_rewards";
        $wpdb->insert( 
            $table_name, 
            array( 
                'user_id' => $user_id, 
                'points' => 0 
            ), 
            array( 
                '%d', 
                '%d' 
            ) 
        );
    }


 public function register_ajax_actions() {
    add_action('wp_ajax_update_rewards_system_status', array($this, 'update_rewards_system_status'));
  }
    public function add_menu_item() {
        add_menu_page(
            'Rewards System By Smajkan',
            'Rewards Sistem',
            'manage_options',
            'rewards-system',
            array( $this, 'rewards_system_page' ),
            'dashicons-awards',
            80
        );

        add_submenu_page(
            'rewards-system',
            'Order Points',
            'Order Points',
            'manage_options',
            'rewards-system-order-points',
            array( $this, 'order_points_page' )
        );

        add_submenu_page(
            'rewards-system',
            'User Rewards',
            'User Rewards',
            'manage_options',
            'rewards-system-user-rewards',
            array( $this, 'user_rewards_page' )
        );

        add_submenu_page(
            'rewards-system',
            'Redemption Settings',
            'Redemption Settings',
            'manage_options',
            'rewards-system-redemption-settings',
            array( $this, 'redemption_settings_page' )
        );
    }

    // Main plugin page
public function rewards_system_page() {
   $status = get_option('rewards_system_status');
   echo '<style>
     .wrap {
       background-color: #f1f1f1;
       padding: 20px;
       border-radius: 10px;
       text-align: center;
     }
     h1 {
       font-size: 32px;
       color: #333;
       margin-bottom: 20px;
     }
     p {
       font-size: 18px;
       margin-bottom: 20px;
     }
     span.status-dot {
       display: inline-block;
       width: 15px;
       height: 15px;
       border-radius: 50%;
       margin-left: 10px;
     }
     span.active {
       background-color: green;
     }
     span.inactive {
       background-color: red;
     }
     #rewards-system-toggle {
       -webkit-appearance: none;
       appearance: none;
       width: 40px;
       height: 20px;
       background-color: #ddd;
       outline: none;
       border-radius: 20px;
       cursor: pointer;
       transition: background-color 0.2s;
     }
     #rewards-system-toggle:checked {
       background-color: green;
     }
   </style>';
   echo '<div class="wrap">';
   echo '<h1>Rewards System By Smajkan Aldin</h1>';
   echo '<p>Trenutni status: <strong>' . ($status ? 'Active' : 'Inactive') . '</strong><span style="font-size:35px;color:' . ($status ? 'green' : 'red') . '">&#x25cf;</span></p>';
   echo '<p>Uključite/Isključite Plugin: <input type="checkbox" id="rewards-system-toggle" ' . ($status ? 'checked' : '') . '></p>';
   echo '</div>';
   ?>
   <script>
     document.getElementById('rewards-system-toggle').addEventListener('change', function() {
       var status = this.checked;
       var xhr = new XMLHttpRequest();
       xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
       xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
       xhr.onload = function() {
         if (xhr.status === 200) {
           alert('Rewards system status updated');
         } else {
           alert('Failed to update rewards system status');
         }
       };
       xhr.send('action=update_rewards_system_status&status=' + status);
     });
   </script>
   <?php
 }

  // Handle XHR Request
  public function update_rewards_system_status() {
    if (current_user_can('manage_options')) {
      $status = $_POST['status'] === 'true';
      update_option('rewards_system_status', $status);
      wp_send_json_success();
    } else {
      wp_send_json_error();
    }
  }



    // Order Points page
    public function order_points_page() {
        echo '<div class="wrap">';
        echo '<h1>Order Points</h1>';
        echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="order_amount_start">From order amount (BAM):</label></th>';
        echo '<td><input type="number" name="order_amount_start" id="order_amount_start" value=""></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="order_amount_end">To order amount (BAM):</label></th>';
        echo '<td><input type="number" name="order_amount_end" id="order_amount_end" value=""></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="points">Points:</label></th>';
        echo '<td><input type="number" name="points" id="points" value=""></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
        echo '</form>';
        echo '</div>';
     
        if (isset($_POST['submit'])) {
          global $wpdb;
          $table_name = $wpdb->prefix . 'order_points';
          $order_amount_start = $_POST['order_amount_start'];
          $order_amount_end = $_POST['order_amount_end'];
          $points = $_POST['points'];
          $data = array(
            'order_amount_start' => $order_amount_start,
            'order_amount_end' => $order_amount_end,
            'points' => $points
          );
          $format = array('%d', '%d', '%d');
          $wpdb->insert($table_name, $data, $format);
        }
      }
     
   //Handler of event on order complete
   function track_order_event($order_id) {
    // Get order object
    $order = wc_get_order($order_id);
 
    // Get order amount
    $order_amount = $order->get_total();
 
    // Get user ID
    $user_id = $order->get_user_id();
 
    // Get the rules for assigning points from the database
    $rules = get_rules_from_db();
 
    // Loop through the rules and see if any match the order amount
    foreach ($rules as $rule) {
       if ($order_amount >= $rule['order_amount_start'] && $order_amount <= $rule['order_amount_end']) {
          // Assign points to the user
          $points = $rule['points'];
          save_user_rewards( $user_id, $points );
 
          break;
       }
    }
 }
 
 public function get_rules_from_db() {
    global $wpdb;
    $table_name = $wpdb->prefix . "reward_rules";
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    return $results;
}

    // Save user rewards
public function save_user_rewards( $user_id, $points ) {
    global $wpdb;
    $table_name = $wpdb->prefix . "user_rewards";
    $wpdb->insert( 
        $table_name, 
        array( 
            'user_id' => $user_id, 
            'points' => $points 
        ), 
        array( 
            '%d', 
            '%d' 
        ) 
    );
}

// Get user rewards
public function get_user_rewards( $user_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . "user_rewards";
    $results = $wpdb->get_results( 
        $wpdb->prepare( 
            "
            SELECT points 
            FROM $table_name 
            WHERE user_id = %d
            ", 
            $user_id 
        ) 
    );
    return $results[0]->points;
} 

public function user_rewards_page() {
    $users = get_users(); // retrieve an array of all users
    echo '<table>';
    echo '<tr><th>User ID</th><th>Username</th><th>Points</th><th>Edit Points</th></tr>';
    foreach ($users as $user) {
        $points = $this->get_user_rewards($user->ID);
        echo '<tr>';
        echo '<td>' . $user->ID . '</td>';
        echo '<td>' . $user->user_login . '</td>';
        echo '<td>' . $points . '</td>';
        echo '<td>';
        echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
        echo '<input type="hidden" name="user_id" value="' . $user->ID . '">';
        echo '<input type="text" name="points" value="' . $points . '">';
        echo '<input type="submit" name="edit_points" value="Edit Points">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    if (isset($_POST['edit_points'])) {
        $user_id = $_POST['user_id'];
        $points = $_POST['points'];
        echo "Updating user $user_id with $points points";
        $this->update_user_rewards($user_id, $points);
    }
    
}
public function update_user_rewards($user_id, $points) {
    global $wpdb;
    $table_name = $wpdb->prefix . "user_rewards";
    $wpdb->show_errors();
    $result = $wpdb->update( 
        $table_name, 
        array( 
            'points' => $points 
        ), 
        array( 
            'user_id' => $user_id 
        ), 
        array( 
            '%d' 
        ), 
        array( 
            '%d' 
        ) 
    );
    if ($result === false) {
        echo "Update failed: " . $wpdb->last_error;
    } else {
        echo "Update successful: $result rows updated";
    }
    if ($wpdb->last_error !== '') {
        echo 'Error: ' . $wpdb->last_error;
    }
    
}

 
    // Redemption Settings page
    public function redemption_settings_page() {
        echo 'Redemption Settings';
    }
}
register_activation_hook(__FILE__, 'my_activation_hook');
register_activation_hook( __FILE__, 'populate_user_rewards_table' );

function populate_user_rewards_table() {
    $users = get_users();
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_rewards';
    foreach ($users as $user) {
        $user_id = $user->ID;
        $points = 0;
        $wpdb->insert( 
            $table_name, 
            array( 
                'user_id' => $user_id, 
                'points' => $points 
            ), 
            array( 
                '%d', 
                '%d' 
            ) 
        );
    }
}

function my_activation_hook() {
    global $wpdb;
    $table_name = $wpdb->prefix . "user_rewards";
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql2 = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      user_id mediumint(9) NOT NULL,
      points mediumint(9) NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql2 );
    
    $table_name = $wpdb->prefix . "reward_rules";
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      from_amount decimal(10,2) NOT NULL,
      to_amount decimal(10,2) NOT NULL,
      points mediumint(9) NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta( $sql );
}

// Initialize the plugin
new Rewards_System();

