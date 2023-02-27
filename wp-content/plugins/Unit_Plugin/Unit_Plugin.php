<?php
/**
 * Plugin Name:       Unit Plugin
 * Plugin URI:        https://www.google.com
 * Description:       This is a basic plugin that creates cpt.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            Bilal Khalid
 */

if( !defined( 'ABSPATH' ) ) {
   die;
}

require_once('includes/shortcodes.php');

class UnitManager {

    public $customMetaBoxes;

    //constructor

    public function __construct() {

        $this->customMetaBoxes = ['asset_id'=>'Asset ID','building_id'=>'Building Id','floor_id' => 'Floor Id','floor_plan_id'=>'Floor Plan Id','area'=>"Area"];

        //action for creating unit cpt
        add_action( "init", array( $this, "unitCptCreator" ) );

        //action for adding scripts & style user side
        add_action( "wp_enqueue_scripts", array( $this, "userEnqueueScripts" ) );

        //action for adding scripts & style admin side
        add_action( "admin_enqueue_scripts", array( $this, "adminEnqueueScripts" ) );

        // For Creating Posts From AJAX ON the button click
        add_action( 'wp_ajax_my_action', [$this,'my_action'] );

        //action for adding admin page
        add_action( "admin_menu", array( $this, "createAdminPage" ) );

        // Add unit meta boxes
        add_action( 'add_meta_boxes', array($this, 'add_unit_meta_boxes') );
    }

    //callback for activation
    public function activator() {
        $this -> unitCptCreator();
        flush_rewrite_rules();
    }

    //callback for deactivation
    public function deactivator() {
        flush_rewrite_rules();

        // removing all units posts
        $unitPosts= get_posts( array('post_type'=>'units','numberposts'=>-1,'post_status' =>'published') );

        foreach ($unitPosts as $post) {
            
            wp_delete_post( $post->ID, true);
        }

        // removing meta fields values
        $deletable =  array_keys($this->customMetaBoxes);
        foreach( $deletable as $to_delete ) {
            delete_metadata( 'units', 0, $to_delete, false, true );
        }
    }

    //callback for cpt registration

    public function unitCptCreator() {
        $labels = array(
            'name'                  => _x( 'Units', 'Post type general name', 'textdomain' ),
            'singular_name'         => _x( 'Unit', 'Post type singular name', 'textdomain' ),
            'menu_name'             => _x( 'Units', 'Admin Menu text', 'textdomain' ),
            'name_admin_bar'        => _x( 'Unit', 'Add New on Toolbar', 'textdomain' ),
            'add_new'               => __( 'Add New', 'textdomain' ),
            'add_new_item'          => __( 'Add New Unit', 'textdomain' ),
            'new_item'              => __( 'New Unit', 'textdomain' ),
            'edit_item'             => __( 'Edit Unit', 'textdomain' ),
            'view_item'             => __( 'View Unit', 'textdomain' ),
            'all_items'             => __( 'All Units', 'textdomain' ),
            'search_items'          => __( 'Search Units', 'textdomain' ),
            'parent_item_colon'     => __( 'Parent Units:', 'textdomain' ),
            'not_found'             => __( 'No Units found.', 'textdomain' ),
            'not_found_in_trash'    => __( 'No Units found in Trash.', 'textdomain' ),
            'featured_image'        => _x( 'Unit Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
            'archives'              => _x( 'Unit archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain' ),
            'insert_into_item'      => _x( 'Insert into unit', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this unit', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain' ),
            'filter_items_list'     => _x( 'Filter units list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain' ),
            'items_list_navigation' => _x( 'Units list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain' ),
            'items_list'            => _x( 'Units list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain' ),
        );
     
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'unit' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' ),
        );

        register_post_type('Units', $args);
    }

    // create custom meta boxes with cpt
    public function add_unit_meta_boxes($post_type) {
        // echo '<pre>'; print_r($post_type); exit;
		add_meta_box(
			'unit_meta_boxs',
			'Unit Fields',
			array($this,'commom_callback_fuction'),
			'units',
			'normal',
			'default'
		);
    }

    // This function renders the custon input fields
    public function commom_callback_fuction($post) {
        foreach ($this->customMetaBoxes as $fieldName=>$fieldLabel) {
            $customMetaBox = get_post_meta( $post->ID, $fieldName, true ); 
            ?>
            <label for="<?php echo $fieldName; ?>"><?php echo $fieldLabel ?>:</label> 
            <input type="text" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>" value="<?php echo $customMetaBox; ?>">
            <?php
        }
    }

    // This function saves the price value in the database against the post id
    public function save_unit_meta_boxes( $post_id ) {
        foreach ($this->customMetaBoxes as $fieldName=>$fieldLabel) {
            if ( isset( $_POST[$fieldName] ) ) { 
                update_post_meta( $post_id, $fieldName, sanitize_text_field( $_POST[$fieldName] ) );
            }
        }
    }

    //callback for registering styles & scripts user side
    public function userEnqueueScripts() {
        wp_enqueue_style(  "my-plugin-style", plugins_url( "assets/css/my-style.css", __FILE__ ) );
        wp_enqueue_style(  'bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" );
        wp_enqueue_script( 'bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js", array( 'jquery' ),'',true);        
    }

    //callback for registering styles and scripts admin side
    public function adminEnqueueScripts(){
        wp_enqueue_style(  "my-plugin-style", plugins_url( "assets/css/my-style.css", __FILE__ ) );
        // These scripts define on the frontend which function should be called on the backend on the button click
        wp_enqueue_script( 'ajax-script', plugins_url( '/assets/js/api-manager.js', __FILE__ ), array('jquery') );
        wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
    }

    // Function to create posts as per the data from api
    public function my_action() {
        foreach($_POST['unitData'] as $unitPost){
            $postFlag = post_exists($unitPost['unit_number']);
            if($postFlag == 0){
                $postId = wp_insert_post(array(
                    'post_title'  => $unitPost['unit_number'], 
                    'post_type'   => 'units', 
                    'post_status' => 'publish'
                ));
    
                foreach ($this->customMetaBoxes as $meta_key=>$fieldLabel) {
                    update_post_meta( $postId, $meta_key, $unitPost[$meta_key], $prev_value = '' );
                }
            }
        }
        echo "success";
    }

    //callback for adding admin page
    public function createAdminPage() {
        add_menu_page( 
            "Unit Plugin Admin Page",
            "Unit Plugin",
            "manage_options",
            "unit-plugin",
            "renderAdminPageContent"
        );

        //callback for add_menu_page 

        function renderAdminPageContent() {
            global $title;
            print "<h1>$title</h1>";
            print "<div class='plugin-div'>";
            print "<button class='plugin-btn api-button'>Click Here To Fetch Unit Data From API</button>";
            print "<p class='info-message'>Use this shortcode to display lists of units per area <b>[get_unit_list]</b></p>";
            print "</div>";
            print "<div class='message-div'>";
            print "</div>";
        }
    }

}

if( class_exists( "UnitManager" ) ) {
    $unit_manager_obj = new UnitManager();
}

register_activation_hook( __FILE__, array( $unit_manager_obj, "activator" ) );

register_deactivation_hook( __FILE__, array($unit_manager_obj, "deactivator") );

// To save meta box fields
add_action( 'save_post', array( $unit_manager_obj, "save_unit_meta_boxes" ) );