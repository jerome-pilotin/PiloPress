<?php

if ( !class_exists( 'PIP_Styles_Settings' ) ) {
    class PIP_Styles_Settings {
        public function __construct() {
            // WP hooks
            add_action( 'init', array( $this, 'custom_image_sizes' ) );
            add_filter( 'image_size_names_choose', array( $this, 'custom_image_sizes_names' ) );

            // ACF hooks
            add_action( 'acf/save_post', array( $this, 'compile_styles_settings' ), 20, 2 );
            add_filter( 'acf/load_value/name=pip_wp_image_sizes', array( $this, 'pre_populate_wp_image_sizes' ), 10, 3 );
            add_filter( 'acf/prepare_field/name=pip_wp_image_sizes', array( $this, 'configure_wp_image_sizes' ) );
        }

        /**
         * Compile style on Styles page save
         *
         * @param $post_id
         * @param bool $force
         *
         * @return bool
         */
        public static function compile_styles_settings( $post_id = 'pip_styles_demo' ) {
            if ( strpos( $post_id, 'pip_styles_' ) !== 0 ) {
                return false;
            }

            // Save WP image sizes
            self::save_wp_image_sizes();

            // Compile base style for admin & front
            self::compile_bootstrap_styles();

            // Compile layouts styles
            self::compile_layouts_styles();

            return true;
        }

        /**
         * Get custom SCSS
         * @return string
         */
        public static function get_custom_scss() {
            // Get custom fonts SCSS
            $custom_scss = self::scss_custom_fonts();

            // Get custom colors SCSS
            $custom_scss .= self::scss_custom_colors();

            // Get custom options, breakpoints, containers and components SCSS
            $custom_scss .= self::scss_custom_options();

            // Get custom typography SCSS
            $custom_scss .= self::scss_custom_typography();

            // Get custom btn, forms and links SCSS
            $custom_scss .= self::scss_custom_btn_forms();

            // Get custom CSS/SCSS
            $custom_style = get_field( 'pip_custom_style', 'pip_styles_css' );
            $custom_scss  .= $custom_style ? $custom_style['custom_style'] : '';

            // Get custom spacers SCSS
            $custom_scss .= self::get_spacers();

            return $custom_scss;
        }

        /**
         * Compile bootstrap styles
         */
        private static function compile_bootstrap_styles() {
            $dirs = array();

            // Get custom SCSS
            $custom_scss = self::get_custom_scss();

            // Front-office
            $front = self::get_front_scss_code( $custom_scss );
            array_push( $dirs, array(
                'scss_dir'  => PIP_PATH . 'assets/libs/bootstrap/scss/',
                'scss_code' => $front,
                'css_dir'   => PIP_THEME_STYLE_PATH,
                'css_file'  => 'style-pilopress.css',
            ) );

            // Back-office
            $admin = self::get_admin_scss_code( $custom_scss );
            array_push( $dirs, array(
                'scss_dir'  => PIP_PATH . 'assets/scss/',
                'scss_code' => $admin,
                'css_dir'   => PIP_THEME_STYLE_PATH,
                'css_file'  => 'style-pilopress-admin.css',
            ) );

            // Compile style
            $class = new PIP_Scss_Php( array(
                'dirs'      => $dirs,
                'variables' => $custom_scss,
            ) );
            $class->compile();
        }

        /**
         * Compile layouts styles
         *
         * @param null $layout_id
         */
        public static function compile_layouts_styles( $layout_id = null ) {
            $dirs = array();

            // Get custom SCSS
            $custom_scss = self::get_custom_scss();

            if ( !$layout_id ) {
                // Layouts args
                $args = array(
                    'post_type'        => 'acf-field-group',
                    'posts_per_page'   => - 1,
                    'fields'           => 'ids',
                    'suppress_filters' => 0,
                    'post_status'      => array( 'acf-disabled' ),
                    'pip_post_content' => array(
                        'compare' => 'LIKE',
                        'value'   => 's:14:"_pip_is_layout";i:1',
                    ),
                );

                // Get layout dirs
                $posts = get_posts( $args );
            } else {
                // Use specified layout
                $posts[] = $layout_id;
            }

            if ( $posts ) {
                foreach ( $posts as $post_id ) {
                    // Get field group
                    $field_group = acf_get_field_group( $post_id );

                    // No field group, skip
                    if ( !$field_group ) {
                        continue;
                    }

                    // If no slug, skip
                    if ( !acf_maybe_get( $field_group, '_pip_layout_slug' ) ) {
                        continue;
                    }

                    // Get sanitized slug
                    $name = sanitize_title( $field_group['_pip_layout_slug'] );

                    // Path
                    $file_path = PIP_THEME_LAYOUTS_PATH . $name . '/';
                    if ( !file_exists( $file_path ) ) {
                        continue;
                    }

                    // No SCSS file, skip
                    if ( !acf_maybe_get( $field_group, '_pip_render_style_scss' ) ) {
                        continue;
                    }

                    // Get layout SCSS code
                    $layout_code = self::get_layout_scss_code( $custom_scss, $file_path, $field_group );

                    // Store data
                    array_push( $dirs, array(
                        'scss_dir'  => PIP_THEME_LAYOUTS_PATH . $name . '/', // For @import
                        'scss_code' => $layout_code,
                        'css_dir'   => $file_path,
                        'css_file'  => acf_maybe_get( $field_group, '_pip_render_style' ) ? $field_group['_pip_render_style'] : $name . '.css',
                    ) );
                }
            }

            // If no dirs, return
            if ( !$dirs ) {
                return;
            }

            // Compile style
            $class = new PIP_Scss_Php( array(
                'dirs'      => $dirs,
                'variables' => $custom_scss,
            ) );
            $class->compile();
        }

        /**
         * Get admin SCSS code
         *
         * @param $custom_scss
         *
         * @return false|string
         */
        private static function get_admin_scss_code( $custom_scss ) {
            ob_start(); ?>

            i.mce-i-wp_adv:before {
            content: "\f111" !important;
            }

            .-preview, body#tinymce{

            <?php echo $custom_scss; ?>

            // Import Bootstrap
            @import '../libs/bootstrap/scss/bootstrap';

            color: $body-color;
            font-family: $font-family-base;
            @include font-size($font-size-base);
            font-weight: $font-weight-base;
            line-height: $line-height-base;

            // Reset WP styles
            @import 'reset-wp';

            }

            <?php return ob_get_clean();
        }

        /**
         * Get front SCSS code
         *
         * @param $custom_scss
         *
         * @return false|string
         */
        private static function get_front_scss_code( $custom_scss ) {
            ob_start();

            echo $custom_scss;
            ?>

            // Import Bootstrap
            @import 'bootstrap';

            <?php

            return ob_get_clean();
        }

        /**
         * Get layout SCSS code
         *
         * @param $custom_scss
         * @param $file_path
         * @param $field_group
         *
         * @return false|string
         */
        private static function get_layout_scss_code( $custom_scss, $file_path, $field_group ) {
            // Path to bootstrap from layout folder
            $path_to_scss_bootstrap = apply_filters( 'pip/layouts/bootstrap_path', '../../../../../..' . parse_url( PIP_URL . 'assets/libs/bootstrap/scss/', PHP_URL_PATH ) );

            // Store folder and scss code
            ob_start();

            echo $custom_scss;
            ?>

            // Import Bootstrap utilities
            @import '<?php echo $path_to_scss_bootstrap; ?>functions';
            @import '<?php echo $path_to_scss_bootstrap; ?>variables';
            @import '<?php echo $path_to_scss_bootstrap; ?>mixins';
            @import '<?php echo $path_to_scss_bootstrap; ?>utilities';
            @import '<?php echo $path_to_scss_bootstrap; ?>type';

            <?php

            if ( file_exists( $file_path . $field_group['_pip_render_style_scss'] ) ) {
                echo file_get_contents( $file_path . $field_group['_pip_render_style_scss'] );
            }

            return ob_get_clean();
        }

        /**
         * Custom spacers
         *
         * @param string $format
         *
         * @return string
         */
        public static function get_spacers( $format = 'scss' ) {
            switch ( $format ) {
                case 'array':
                    $options = get_field( 'pip_bt_options', 'pip_styles_bt_options' );
                    $spacer  = $options['spacer'];
                    $spacer  = str_replace( 'rem', '', $spacer );
                    $spacer  = str_replace( 'em', '', $spacer );
                    $spacer  = str_replace( 'vh', '', $spacer );
                    $spacer  = str_replace( 'px', '', $spacer );

                    $return = array(
                        0,
                        $spacer * 0.25,
                        $spacer * 0.5,
                        $spacer * 1,
                        $spacer * 1.5,
                        $spacer * 3,
                        $spacer * 4.5,
                        $spacer * 6,
                        $spacer * 7.5,
                        $spacer * 9,
                        $spacer * 10.5,
                        $spacer * 12,
                        $spacer * 13.5,
                    );
                    break;
                default:
                case 'scss':
                    $return = '$spacers: (
                      0: 0,
                      1: ($spacer * .25),
                      2: ($spacer * .5),
                      3: $spacer,
                      4: ($spacer * 1.5),
                      5: ($spacer * 3),
                      6: ($spacer * 4.5),
                      7: ($spacer * 6),
                      8: ($spacer * 7.5),
                      9: ($spacer * 9),
                      10: ($spacer * 10.5),
                      11: ($spacer * 12),
                      12: ($spacer * 13.5),
                    );';
                    break;
            }

            return $return;
        }

        /**
         * Get SCSS to enqueue custom fonts
         *
         * @return string
         */
        private static function scss_custom_fonts() {
            $scss_custom = $tinymce_fonts = '';

            // Get fonts
            if ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                while ( have_rows( 'pip_fonts', 'pip_styles_fonts' ) ) {
                    the_row();

                    // Get sub fields
                    $name    = get_sub_field( 'name' );
                    $files   = get_sub_field( 'files' );
                    $weight  = get_sub_field( 'weight' );
                    $style   = get_sub_field( 'style' );
                    $enqueue = get_sub_field( 'enqueue' );
                    $tinymce = get_sub_field( 'tinymce' );

                    if ( $tinymce ) {
                        // Build font class
                        $tinymce_fonts .= '.font-' . $tinymce['value'] . ' {' . "\n";
                        $tinymce_fonts .= 'font-family: "' . $name . '";' . "\n";
                        $tinymce_fonts .= '}' . "\n";
                    }

                    // If not custom font, skip
                    if ( get_row_layout() !== 'custom_font' ) {
                        continue;
                    }

                    // Auto enqueue to false
                    if ( !$enqueue ) {
                        continue;
                    }

                    // Build @font-face
                    $scss_custom .= "@font-face {\n";
                    $scss_custom .= 'font-family: "' . $name . '";' . "\n";

                    // Get URLs
                    $url = array();
                    if ( $files ) {
                        foreach ( $files as $file ) {
                            // Format file name
                            $file_name = $file['file']['url'];
                            $file_name = pathinfo( $file_name, PATHINFO_FILENAME );

                            // Get format
                            $format = strtolower( pathinfo( $file['file']['filename'], PATHINFO_EXTENSION ) );

                            // Get post
                            $posts   = get_posts( array(
                                'post_name'      => $file_name,
                                'post_type'      => 'attachment',
                                'posts_per_page' => 1,
                                'fields'         => 'ids',
                            ) );
                            $post_id = reset( $posts );

                            // Store URL
                            $url[] = 'url(' . wp_get_attachment_url( $post_id ) . ') format("' . $format . '")';
                        }
                    }
                    // Implode URLs for src
                    $scss_custom .= 'src: ' . implode( ",\n", $url ) . ";\n";

                    // Font parameters
                    $scss_custom .= 'font-weight: ' . $weight . ";\n";
                    $scss_custom .= 'font-style: ' . $style . ";\n";

                    // End @font-face
                    $scss_custom .= "}\n";

                }
            }

            return $scss_custom . $tinymce_fonts;
        }

        /**
         * Get SCSS to enqueue custom colors
         * @return string
         */
        private static function scss_custom_colors() {
            $scss_custom = '';

            // Get colors
            self::add_to_scss_custom( $scss_custom, 'pip_colors', 'pip_styles_colors' );

            // Get grays
            self::add_to_scss_custom( $scss_custom, 'pip_grays', 'pip_styles_colors' );

            // Get theme colors
            self::add_to_scss_custom( $scss_custom, 'pip_theme_colors', 'pip_styles_colors' );

            // Get custom colors
            if ( have_rows( 'pip_custom_colors', 'pip_styles_colors' ) ) {
                while ( have_rows( 'pip_custom_colors', 'pip_styles_colors' ) ) {
                    the_row();

                    // Get sub fields
                    $name  = get_sub_field( 'name' );
                    $value = get_sub_field( 'value' );

                    // Add ";" if needed
                    $suffix = strpos( $value, ';' ) === ( strlen( $value ) - 1 ) ? '' : ';';

                    // Build $color variable
                    $scss_custom .= '$' . $name . ': ' . $value . $suffix . "\n";

                    // Build text-$color class
                    $scss_custom .= '.text-' . $name . "{\n";
                    $scss_custom .= 'color: $' . $name . ";\n";
                    $scss_custom .= "}\n";

                    // Build bg-$color class
                    $scss_custom .= '.bg-' . $name . "{\n";
                    $scss_custom .= 'background-color: $' . $name . ";\n";
                    $scss_custom .= "}\n";
                }
            }

            return $scss_custom;
        }

        /**
         * Get SCSS to enqueue custom options
         * @return string
         */
        private static function scss_custom_options() {
            $scss_custom = '';

            // Get options
            self::add_to_scss_custom( $scss_custom, 'pip_bt_options', 'pip_styles_bt_options', true );

            // Get breakpoints
            if ( have_rows( 'pip_breakpoints', 'pip_styles_bt_options' ) ) {
                while ( have_rows( 'pip_breakpoints', 'pip_styles_bt_options' ) ) {
                    the_row();

                    $scss_custom .= '$grid-breakpoints: (' . "\n";
                    foreach ( get_row() as $field_key => $value ) {

                        // Get field
                        $field_name = get_field_object( $field_key, 'pip_styles_bt_options' );
                        if ( !$field_name ) {
                            continue;
                        }

                        $scss_custom .= $field_name['name'] . ': ' . $value . ',' . "\n";
                    }
                    $scss_custom .= ');';
                }
            }

            // Get containers
            if ( have_rows( 'pip_containers', 'pip_styles_bt_options' ) ) {
                while ( have_rows( 'pip_containers', 'pip_styles_bt_options' ) ) {
                    the_row();

                    $scss_custom .= '$container-max-widths: (' . "\n";
                    foreach ( get_row() as $field_key => $value ) {

                        // Get field
                        $field_name = get_field_object( $field_key, 'pip_styles_bt_options' );
                        if ( !$field_name ) {
                            continue;
                        }

                        $scss_custom .= $field_name['name'] . ': ' . $value . ',' . "\n";
                    }
                    $scss_custom .= ');';
                }
            }

            // Get components
            self::add_to_scss_custom( $scss_custom, 'pip_components', 'pip_styles_bt_options' );

            return $scss_custom;
        }

        /**
         * Get SCSS to enqueue custom typography
         * @return string
         */
        private static function scss_custom_typography() {
            $scss_custom = '';

            // Get default
            self::add_to_scss_custom( $scss_custom, 'pip_default_typography', 'pip_styles_typography' );

            // Get headings
            self::add_to_scss_custom( $scss_custom, 'pip_headings_typography', 'pip_styles_typography' );

            // Get display
            self::add_to_scss_custom( $scss_custom, 'pip_display_typography', 'pip_styles_typography' );

            // Get lead
            self::add_to_scss_custom( $scss_custom, 'pip_lead_typography', 'pip_styles_typography' );

            // Get custom
            if ( have_rows( 'pip_custom_typography', 'pip_styles_typography' ) ) {
                while ( have_rows( 'pip_custom_typography', 'pip_styles_typography' ) ) {
                    the_row();

                    // Get sub fields
                    $class_name = get_sub_field( 'class_name' );
                    $value      = get_sub_field( 'value' );

                    // Build class
                    $scss_custom .= '.' . $class_name . "{\n";
                    $scss_custom .= $value . "\n";
                    $scss_custom .= "}\n";
                }
            }

            return $scss_custom;
        }

        /**
         * Get SCSS to enqueue custom buttons & forms
         * @return string
         */
        private static function scss_custom_btn_forms() {
            $scss_custom = '';

            // Get common
            self::add_to_scss_custom( $scss_custom, 'pip_btn_forms', 'pip_styles_btn_form' );

            // Get buttons
            self::add_to_scss_custom( $scss_custom, 'pip_btn', 'pip_styles_btn_form' );

            // Get forms
            self::add_to_scss_custom( $scss_custom, 'pip_forms', 'pip_styles_btn_form' );

            // Get links
            self::add_to_scss_custom( $scss_custom, 'pip_links', 'pip_styles_btn_form' );

            return $scss_custom;
        }

        /**
         * Save WP image sizes
         */
        private static function save_wp_image_sizes() {
            $posted_values = acf_maybe_get_POST( 'acf' );
            if ( !$posted_values ) {
                return;
            }

            // Browse values
            foreach ( $posted_values as $key => $posted_value ) {
                $field = acf_get_field( $key );

                // If not WP image sizes, continue
                if ( $field['name'] !== 'pip_wp_image_sizes' ) {
                    continue;
                }

                // Browse each repeater values
                foreach ( $posted_value as $image_key => $image_size ) {

                    // Format posted value array
                    foreach ( $image_size as $field_key => $value ) {
                        $image_field = acf_get_field( $field_key );
                        unset( $image_size[ $field_key ] );
                        $image_size[ $image_field['name'] ] = $value;
                    }

                    // Update values
                    update_option( $image_size['name'] . '_size_w', $image_size['width'] );
                    update_option( $image_size['name'] . '_size_h', $image_size['height'] );
                    update_option( $image_size['name'] . '_crop', $image_size['crop'] );
                }
            }
        }

        /**
         * Pre-populate repeater with WP native image sizes
         *
         * @param $value
         * @param $post_id
         * @param $field
         *
         * @return mixed
         */
        public function pre_populate_wp_image_sizes( $value, $post_id, $field ) {
            $image_sizes = $fields = $new_values = array();

            // Get only WP image sizes
            $all_image_sizes        = PIP_TinyMCE::get_all_image_sizes();
            $additional_image_sizes = wp_get_additional_image_sizes();
            foreach ( $additional_image_sizes as $key => $additional_image_size ) {
                unset( $all_image_sizes[ $key ] );
            }

            // Format image sizes array
            $i = 0;
            foreach ( $all_image_sizes as $key => $image_size ) {
                $image_sizes[ $i ]['name']   = $key;
                $image_sizes[ $i ]['width']  = $image_size['width'];
                $image_sizes[ $i ]['height'] = $image_size['height'];
                $image_sizes[ $i ]['crop']   = $image_size['crop'];
                $i ++;
            }

            // Get sub fields keys
            $sub_fields = acf_get_fields( $field );
            foreach ( $sub_fields as $sub_field ) {
                $fields[ $sub_field['name'] ] = $sub_field['key'];
            }

            // Set new values
            foreach ( $image_sizes as $image_key => $image_size ) {
                foreach ( $image_size as $key => $value ) {
                    $new_values[ $image_key ][ $fields[ $key ] ] = $value;
                }
            }

            return $new_values;
        }

        /**
         * Set max and min for wp_image_sizes field
         *
         * @param $field
         *
         * @return mixed
         */
        public function configure_wp_image_sizes( $field ) {
            $field['min'] = count( $field['value'] );
            $field['max'] = count( $field['value'] );

            return $field;
        }

        /**
         * Register custom image sizes
         */
        public function custom_image_sizes() {
            // Get custom sizes
            $custom_sizes = get_field( 'pip_image_sizes', 'pip_styles_image_sizes' );
            if ( !is_array( $custom_sizes ) ) {
                return;
            }

            // Register custom sizes
            foreach ( $custom_sizes as $size ) {
                add_image_size( $size['name'], $size['width'], $size['height'], $size['crop'] );
            }
        }

        /**
         * Add custom image sizes names
         *
         * @param $size_names
         *
         * @return mixed
         */
        public function custom_image_sizes_names( $size_names ) {
            // Get custom sizes
            $custom_sizes = get_field( 'pip_image_sizes', 'pip_styles_image_sizes' );
            if ( !$custom_sizes ) {
                return $size_names;
            }

            // Add custom sizes names
            foreach ( $custom_sizes as $size ) {
                $size_names[ $size['name'] ] = __( $size['name'], 'pilopress' );
            }

            return $size_names;
        }

        /**
         * Add custom style
         *
         * @param $scss_custom
         * @param $selector
         * @param $post_id
         * @param bool $format_value
         */
        private static function add_to_scss_custom( &$scss_custom, $selector, $post_id, $format_value = false ) {
            if ( have_rows( $selector, $post_id ) ) {
                while ( have_rows( $selector, $post_id ) ) {
                    the_row();

                    foreach ( get_row() as $field_key => $value ) {
                        // Get field
                        $field_name = get_field_object( $field_key, $post_id );
                        if ( !$field_name ) {
                            continue;
                        }

                        if ( $format_value ) {
                            // Format value
                            if ( $value === '1' ) {
                                $value = 'true';
                            } elseif ( $value === '0' ) {
                                $value = 'false';
                            }
                        }

                        $scss_custom .= '$' . $field_name['name'] . ': ' . $value . ';' . "\n";
                    }
                }
            }
        }

        /**
         * Get colors
         * @return array|null
         */
        public static function get_colors() {
            // Get colors
            $pip_colors        = get_field( 'pip_colors', 'pip_styles_colors' );
            $pip_grays         = get_field( 'pip_grays', 'pip_styles_colors' );
            $pip_theme_colors  = get_field( 'pip_theme_colors', 'pip_styles_colors' );
            $pip_custom_colors = get_field( 'pip_custom_colors', 'pip_styles_colors' );

            // If no theme colors, return
            if ( !$pip_theme_colors ) {
                return null;
            }

            // Change variable by values
            foreach ( $pip_theme_colors as $name => $color_var ) {

                // Remove $ from variables
                $color_var = str_replace( '$', '', $color_var );

                if ( acf_maybe_get( $pip_colors, $color_var ) ) {

                    // Get hexadecimal value from colors
                    $pip_theme_colors[ $name ] = $pip_colors[ $color_var ];

                } elseif ( acf_maybe_get( $pip_grays, $color_var ) ) {

                    // Get hexadecimal value from grays
                    $pip_theme_colors[ $name ] = $pip_grays[ $color_var ];

                }
            }

            // Change variable by values
            $custom_colors = array();
            foreach ( $pip_custom_colors as $color ) {

                if ( acf_maybe_get( $pip_colors, $color['value'] ) ) {

                    // Get hexadecimal value from colors
                    $custom_colors[ $color['name'] ] = $pip_colors[ $color['value'] ];

                } elseif ( acf_maybe_get( $pip_grays, $color['value'] ) ) {

                    // Get hexadecimal value from grays
                    $custom_colors[ $color['name'] ] = $pip_grays[ $color['value'] ];

                } else {

                    // Get hexadecimal value
                    $custom_colors[ $color['name'] ] = str_replace( ';', '', $color['value'] );

                }
            }

            return array_merge( $pip_theme_colors, $custom_colors );
        }
    }

    // Instantiate class
    new PIP_Styles_Settings();
}
