<?php
// Declare a namespace for this file
namespace Roots;

// Check if WooCommerce is defined by looking for the WC_ABSPATH constant
if (defined('WC_ABSPATH')) {

    /**
     * Set WooCommerce Theme Support
     */
    // After the theme is set up, add support for WooCommerce
    add_action('after_setup_theme', function () {
        add_theme_support('woocommerce');
    });

    /**
     * Set template path to /woocommerce
     */
    // Alter the WooCommerce template path to point to the /woocommerce directory
    add_filter(
        'woocommerce_template_path',
        function () {
            return 'woocommerce/';
        },
        100,
        1
    );

    /**
     * @param string $template
     *
     * @return string
     */
    // Custom template loader function for WooCommerce
    function wc_template_loader(string $template)
    {
        // If the template path doesn't end with WC_ABSPATH, return the original template
        if (strpos($template, WC_ABSPATH) === -1) {
            return $template;
        }

        // Locate and return the new template based on provided logic
        return locate_template(
            app('sage.finder')->locate(
                WC()->template_path() . str_replace(
                    WC_ABSPATH . 'templates/',
                    '',
                    $template
                )
            )
        ) ?: $template;
    }

    // Apply the custom template loader to the main template and comments template
    add_filter('template_include', __NAMESPACE__ . '\\wc_template_loader', 90, 1);
    add_filter('comments_template', __NAMESPACE__ . '\\wc_template_loader', 100, 1);

    // Modify WooCommerce's default template part behavior
    add_filter(
        'wc_get_template_part',
        function ($template) {
            // Locate the theme template
            $theme_template = locate_template(
                app('sage.finder')->locate(
                    WC()->template_path() . str_replace(WC_ABSPATH . 'templates/', '', $template)
                )
            );

            // If theme template exists, modify its output
            if ($theme_template) {
                $view = app('view.finder')
                    ->getPossibleViewNameFromPath($file = realpath($theme_template));

                $view = trim($view, '\\/.');

                // Gather and reduce data to be passed to the view
                $data = array_reduce(
                    get_body_class(),
                    function ($data, $class) use ($view, $file) {
                        return apply_filters("sage/template/{$class}/data", $data, $view, $file);
                    },
                    array()
                );

                // Render the view with the collected data
                echo view($view, $data)->render();

                // Prevent the default WooCommerce behavior by returning an empty string
                return '';
            } else {
                return $template;
            }
        },
        PHP_INT_MAX,
        1
    );

    // Modify the WooCommerce output before a template part is rendered
    add_action(
        'woocommerce_before_template_part',
        function ($template_name, $template_path, $located, $args) {
            $theme_template = locate_template(app('sage.finder')->locate(WC()->template_path() . $template_name));

            // If a matching theme template exists, modify its output
            if ($theme_template) {
                $view = app('view.finder')
                    ->getPossibleViewNameFromPath($file = realpath($theme_template));

                $view = trim($view, '\\/.');

                // Gather and reduce data to be passed to the view
                $data = array_reduce(
                    get_body_class(),
                    function ($data, $class) use ($view, $file) {
                        return apply_filters("sage/template/{$class}/data", $data, $view, $file);
                    },
                    array()
                );

                // Merge the data and render the view
                echo view(
                    $view,
                    array_merge(
                        compact(explode(' ', 'template_name template_path located args')),
                        $data,
                        $args
                    )
                )->render();
            }
        },
        PHP_INT_MAX,
        4
    );

    // Alter WooCommerce's default template behavior
    add_filter(
        'wc_get_template',
        function ($template, $template_name, $args) {
            $theme_template = locate_template(app('sage.finder')->locate(WC()->template_path() . $template_name));

            // Check if we're on the WooCommerce status screen in WP admin
            if (
                is_admin() &&
                !wp_doing_ajax() &&
                function_exists('get_current_screen') &&
                get_current_screen() &&
                get_current_screen()->id === 'woocommerce_page_wc-status'
            ) {
                return $theme_template ?: $template;
            }

            // For other admin screens, render output via the 'woocommerce_before_template_part' hook
            return $theme_template ? view('empty')->getPath() : $template;
        },
        100,
        3
    );
}
