<?php
/*
    Plugin Name: MF Datablocks WP Widget
    Description: Enables integration of Modular Finance Datablocks&trade; widgets to your site's sections and pages.
    Author: Alexander Forrest // Modular Finance
*/

// Create widget
class mf_widget extends WP_Widget
{

    private $widgetTitle = 'MF Datablocks WP Widget';
    private $widgetDesc = 'Enables integration of Modular Finance Datablocks&trade; widgets to your site\'s sections and pages.';
    private $mfDomain = 'mf_text_domain';
    private $widgetURL = '';
    private $datablocksURL = '';

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
        echo '
        <script>
            if(!window._MF){
                let b = document.createElement("script");
                b.type = "text/javascript";
                b.async = true;
                b.src =  "' . $this->widgetURL . '";
                document.getElementsByTagName("body")[0].appendChild(b);

                window._MF = window._MF || {
                    data: [],
                    url: "' . $this->datablocksURL . '",
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
        $title = apply_filters('widget_title', $instance['title']);

        $name = $instance['name'] !== "" ? $instance['name'] : 'mfdb';

        $date = mt_rand(1, time());
        $elementId = "widget-" . $name . "-" . $date;

        $widget = new stdClass();

        $widget->query = '#' . $elementId;
        $widget->widget = $instance['name'];
        $widget->locale = $instance['locale'];
        $widget->c = $instance['company'];
        $widget->token = $instance['token'];
        $widget->demo = $instance[ 'demo' ] ? 'true' : 'false';
        $widget->class = $instance['classname'] ? "class='" . $instance['classname'] . "'" : '';

        $this->_inject($widget);

        echo $args['before_widget'];

        // If title is set - display the title
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo "<div id='" . $elementId . "'" . $widget->class . "></div>";

        echo $args['after_widget'];
    }

    // Input form field
    private function inputFormField($label, $field, $var, $domain) {
        echo '
            <label for="' . $this->get_field_id($field) . "'>'" . _e($label, $domain) . '"</label>
            <input type="text" class="mf-input widefat" id=' . $this->get_field_id($field) . ' name=' . $this->get_field_name($field) . '  value=' . esc_attr($var) . '>
        ';
    }

    // Populate form data
    private function formData($instance) {

        $deflang = 'en';

        $l = determine_locale();

        $autolocale = is_string($l) ? explode("_", $l)[0]: $deflang;

        $w = array(
            'name' => $instance['name'] ? "name='" . $instance['name'] . "' " : '',
            'locale' => $instance['locale'] ? "'" . $instance['locale'] . "' " : "'" . $autolocale . "' ",
            'company' => $instance['company'] ? "company='" . $instance['company'] . "' " : '',
            'token' => $instance['token'] ? "token='" . $instance['token'] . "' " : '',
            'demo' => $instance['demo'] ? "'true'" : "'false'"
        );

        $shortcodeProps = $w['name'] . 'locale=' . $w['locale'] . $w['company'] . $w['token'] . 'demo=' . $w['demo'];

        ?>
        <div id="mf-form-wrapper" class="mf-form-wrapper">
            <?php
                $this->inputFormField('Title:', 'title', $instance['title'], $this->mfDomain);
            ?>
            <h3>
                <?php _e( 'Datablocks Widget Details',$this->mfDomain ); ?>
            </h3>
            <?php
                $this->inputFormField('Name (i.e. stock-charts):', 'name', $instance['name'], $this->mfDomain);
                $this->inputFormField('Locale (Detected: ' . $autolocale . '):', 'locale', $instance['locale'], $this->mfDomain);
                $this->inputFormField('Company (Your company Id):', 'company', $instance['company'], $this->mfDomain);
                $this->inputFormField('Token (Your token goes here):', 'token', $instance['token'], $this->mfDomain);
                $this->inputFormField('Classname: (Optional - Adds a class to this widget parent div):', 'classname', $instance['classname'], $this->mfDomain);
            ?>
            <div class="md-checkbox-label">
                <label for="<?php echo $this->get_field_id( 'demo' ); ?>">
                    <input class="mf-input widefat" type="checkbox" <?php checked( $instance['demo'], 'on' ); ?> id="<?php echo $this->get_field_id('demo'); ?>" name="<?php echo $this->get_field_name('demo'); ?>" />
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
        $stylesheet_url = plugin_dir_url( __FILE__ ) . 'public/css/mf-db.css';

        wp_enqueue_style( $parent, $stylesheet_url );

        wp_enqueue_style( 'mf-input', $stylesheet_url, array( $parent ) );
        wp_enqueue_style( 'mf-form-wrapper', $stylesheet_url, array( $parent ) );
        wp_enqueue_style( 'mf-input', $stylesheet_url, array( $parent ) );
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

        $instance['classname'] = (!empty($new_instance['classname'])) ? strip_tags($new_instance['classname']) : '';
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['name'] = (!empty($new_instance['name'])) ? strip_tags($new_instance['name']) : '';
        $instance['locale'] = (!empty($new_instance['locale'])) ? strip_tags($new_instance['locale']) : '';
        $instance['company'] = (!empty($new_instance['company'])) ? strip_tags($new_instance['company']) : '';
        $instance['token'] = (!empty($new_instance['token'])) ? strip_tags($new_instance['token']) : '';
        $instance['demo'] = (!empty($new_instance['demo'])) ? strip_tags($new_instance['demo']) : '';

        return $instance;
    }
}

// Load MF Datablocks widget
function load_mf_widget()
{
    register_widget('mf_widget');
}

// Load from shortcode
function load_shortcode_widget($atts) {
    ob_start();

    the_widget( 'mf_widget', $atts);

    $contents = ob_get_clean();

    return $contents;
}

add_action('widgets_init', 'load_mf_widget');
add_shortcode('mfdb_widget', 'load_shortcode_widget');