<?php
/*
    Plugin Name: MF Datablocks WP Widget
    Description: Enables integration of Modular Finance Datablocks&trade;
    widgets to your site's sections and pages.
    Author: Alexander Forrest // Modular Finance
*/

// Require file for admin panel
require_once plugin_dir_path(__FILE__) . 'admin/class-db-wp-widget-admin.php';

const DATABLOCKS_DEFAULT_LOADER_URL = 'https://widget.datablocks.se/api/rose/assets/js/loader-v3.js';
const DATABLOCKS_DEFAULT_URL = ' https://widget.datablocks.se/api/rose';

// Create widget
class DB_WP_Widget extends WP_Widget
{

    private $widgetTitle = 'MF Datablocks WP Widget';
    private $widgetDesc = 'Enables integration of Modular Finance Datablocks&trade; widgets to your site\'s sections and pages.';
    private $mfDomain = 'mf_text_domain';

    function __construct()
    {
        parent::__construct(
            'mf_widget',
            __($this->widgetTitle, 'mf_text_domain'),
            array('description' => __($this->widgetDesc, $this->mfDomain),
            )
        );
    }

    // Inject widget loader
    private function _inject($widget) {

        $loaderURLOption = get_option('mfdb_widget_options')['loader_url_option'] ?? '';
        $datablocksURLOption = get_option('mfdb_widget_options')['datablocks_url_option'] ?? '';

        // If options are not set use default URLs
        $loaderURL = !empty($loaderURLOption) ? $loaderURLOption : DATABLOCKS_DEFAULT_LOADER_URL;
        $datablocksURL = !empty($datablocksURLOption) ? $datablocksURLOption : DATABLOCKS_DEFAULT_URL;

        echo '
        <script>
            if(!window._MF){
                let b = document.createElement("script");
                b.type = "text/javascript";
                b.async = true;
                b.src =  "' . $loaderURL . '";
                document.getElementsByTagName("body")[0].appendChild(b);

                window._MF = window._MF || {
                    data: [],
                    url: "' . $datablocksURL . '",
                    ready: !!0,
                    render: function() {
                        window._MF.ready = !0
                    },
                    push: function(conf){
                        this.data.push(conf)
                    }
                }
            }
            var widget = ' . json_encode($widget) . ';

            window._MF.push(widget);
        </script>
        ';
    }

    // Widget frontend
    public function widget($args, $instance)
    {
        $type = ($instance['type'] ?? '') !== '' ? $instance['type'] : 'mfdb';

        $date = mt_rand(1, time());
        $elementId = "widget-" . $type . "-" . $date;

        $widget = new stdClass();

        $widget->query = '#' . $elementId;
        $widget->widget = $instance['type'] ?? '';
        $widget->locale = $instance['locale'] ?? '';
        $widget->c = $instance['c'] ?? '';
        $widget->token = $instance['token'] ?? '';
        $widget->demo = $instance['demo'] ?? false;
        $widget->class = isset($instance['classname']) ? "class='" . $instance['classname'] . "'" : '';

        $this->_inject($widget);

        echo $args['before_widget'];

        // If title is set - display the title
        if (!empty($instance['title'])) {
            echo $args['before_title'] . $instance['title'] . $args['after_title'];
        }

        echo "<div id='" . $elementId . "'" . $widget->class . "></div>";

        echo $args['after_widget'];
    }

    // Input form field
    private function inputFormField($label, $field, $var, $domain) {

        $placeholder = '';

        switch($field) {
            case 'type':
                $placeholder = 'Widget type';
            break;
            case 'token':
                $placeholder = 'Widget token';
            break;
            case 'c':
                $placeholder = 'Widget c';
            break;
            default: $placeholder = $field;
        }

        echo '
            <label for="' . $this->get_field_id($field) . "'>'" . _e($label, $domain) . '"</label>
            <input type="text" class="mf-input widefat" placeholder="' . ucfirst($placeholder) . '" id="' . $this->get_field_id($field) . '" name="' . $this->get_field_name($field) . '"  value="' . esc_attr($var) . '">
        ';
    }

    // Populate form data
    private function formData($instance) {

        $deflang = 'en';

        $l = determine_locale();

        // Auto determine language
        $autolocale = is_string($l) ? explode("_", $l)[0]: $deflang;

        $locale = empty($instance['locale']) ? $autolocale : $instance['locale'];

        $w = array(
            'type' => isset($instance['type']) && $instance['type'] !== '' ? "type='" . $instance['type'] . "' " : '',
            'c' => isset($instance['c']) && $instance['c'] !== '' ? "c='" . $instance['c'] . "' " : '',
            'token' => isset($instance['token']) && $instance['token'] !== '' ? "token='" . $instance['token'] . "' " : '',
            'locale' => "locale='" .  $locale . "'",
            'demo' => isset($instance['demo']) && $instance['demo'] === 'on' ? "demo='true'" : '',
        );

        $spacing = isset($instance['demo']) && $instance['demo'] === 'on' ? ' ' : '';

        $shortcodeProps = $w['type'] . $w['c'] . $w['token'] . $w['locale'] . $spacing . $w['demo'];

        ?>
        <div id="mf-form-wrapper" class="mf-form-wrapper">
            <?php
                $this->inputFormField('Title:', 'title', $instance['title'] ?? '', $this->mfDomain);
            ?>
            <h3>
                <?php _e( 'Datablocks Widget Details' , $this->mfDomain); ?>
            </h3>
            <?php
                $this->inputFormField('Widget type (i.e. stock-chart):', 'type', $instance['type'] ?? '', $this->mfDomain);
                $this->inputFormField('Widget token (Your token):', 'token', $instance['token'] ?? '', $this->mfDomain);
                $this->inputFormField('Widget c (Your company Id):', 'c', $instance['c'] ?? '', $this->mfDomain);
                $this->inputFormField('Locale (Detected language: ' . $autolocale . '):', 'locale', $instance['locale'] ?? '', $this->mfDomain);
                $this->inputFormField('Classname: (Optional - Adds a class to this widget parent div):', 'classname', $instance['classname'] ?? '', $this->mfDomain);
            ?>
            <div class="md-checkbox-label">
                <label for="<?php printf($this->get_field_id( 'demo' )); ?>">
                    <input class="mf-input widefat" type="checkbox" <?php checked( $instance['demo'], 'on' ); ?> id="<?php printf($this->get_field_id('demo')); ?>" name="<?php printf($this->get_field_name('demo')); ?>" />
                </label>
                <?php _e( 'Demo (For demo usage. Will generate random data if enabled and is <i>not recommended in production</i>)', $this->mfDomain ); ?>
            </div>
            <p class="mf-shortcode">
                <?php _e( 'Copy shortcode:', $this->mfDomain ); ?>
            </p>
            <?php
                echo '
                    <input type="text" class="mf-input-shortcode widefat" id="shortcode" name="shortcode" value="[mfdb_widget ' . $shortcodeProps . ']" onClick="this.select();" readonly>
                ';
            ?>
        </div>
        <?php
    }

    // Load MF styles
    private function load_mf_styles() {
        $parent = 'mf-form-wrapper';
        $stylesheet_url = plugin_dir_url( __FILE__ ) . 'public/css/db-wp-widget.css';

        wp_enqueue_style( $parent, $stylesheet_url, array(), 'all' );
    }

    // Widget backend
    public function form($instance)
    {
        // Load form styles
        $this->load_mf_styles();

        // Form data
        $this->formData($instance);
    }

    // Updating widget instance
    public function update($new_instance, $old_instance) {
        $instance = array();

        $instance['classname'] = !empty($new_instance['classname']) ? strip_tags($new_instance['classname']) : '';
        $instance['title'] = !empty($new_instance['title']) ? strip_tags($new_instance['title']) : '';
        $instance['type'] = !empty($new_instance['type']) ? strip_tags($new_instance['type']) : '';
        $instance['locale'] = !empty($new_instance['locale']) ? strip_tags($new_instance['locale']) : '';
        $instance['c'] = !empty($new_instance['c']) ? strip_tags($new_instance['c']) : '';
        $instance['token'] = !empty($new_instance['token']) ? strip_tags($new_instance['token']) : '';
        $instance['demo'] = strip_tags($new_instance['demo']);

        return $instance;
    }
}

// Load MF Datablocks widget
function load_db_wp_widget()
{
    register_widget('DB_WP_Widget');
}

// Load widget from shortcode
function load_shortcode_widget($atts) {
    ob_start();

    the_widget( 'DB_WP_Widget', $atts);

    $contents = ob_get_clean();

    return $contents;
}

// Deactivation hook
function deactivate_db_wp_widget() {
    // Clean up settings
    delete_option('mfdb_widget_options');
}

// Call hooks
add_action( 'widgets_init', 'load_db_wp_widget' );
add_shortcode( 'mfdb_widget', 'load_shortcode_widget' );

register_deactivation_hook( __FILE__, 'deactivate_db_wp_widget' );

// Admin Panel
if ( is_admin() ) {

    new DB_WP_Widget_Admin (
        // Default Loader URL
        DATABLOCKS_DEFAULT_LOADER_URL,
        // Default Datablocks URL
        DATABLOCKS_DEFAULT_URL
    );

}
