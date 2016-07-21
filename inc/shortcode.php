<?php

if (!function_exists('farost_shortcode_form_login'))
{
    /**
     * farost_shortcode_form_login
     * @param $atts
     * @param $content
     * @return string
     */
    function farost_shortcode_form_login($atts, $content)
    {
        return Farost_Plugin_Author::form_login($atts);
    }
    add_shortcode( 'farost_form_login', 'farost_shortcode_form_login' );
}

if (!function_exists('farost_shortcode_form_register'))
{
    /**
     * farost_shortcode_form_register
     * @param $atts
     * @param $content
     * @return string
     */
    function farost_shortcode_form_register($atts, $content)
    {
        return Farost_Plugin_Author::form_register($atts);
    }
    add_shortcode( 'farost_form_register', 'farost_shortcode_form_register' );
}

if (!function_exists('farost_shortcode_form_login_social'))
{
    /**
     * farost_shortcode_form_login_social
     * @param $atts
     * @param $content
     * @return string
     */
    function farost_shortcode_form_login_social($atts, $content)
    {
        return Farost_Plugin_Author::button_login_social($atts);
    }
    add_shortcode( 'farost_login_social', 'farost_shortcode_form_login_social' );
}

if (!function_exists('farost_shortcode_logged'))
{
    /**
     * farost_shortcode_logged
     * @param $atts
     * @param $content
     * @return string
     */
    function farost_shortcode_logged($atts, $content)
    {
        if (is_user_logged_in()) {
            global $current_user;
    ?>
            <a href="#" title="">
                <?php echo get_avatar($current_user->ID, 80);?>
            </a>
            <a href="#" onclick="signOut();">Sign out</a>
            <script>
                function signOut() {
                    var auth2 = gapi.auth2.getAuthInstance();
                    auth2.signOut().then(function () {
                        console.log('User signed out.');
                    });
                }
                // Load the SDK google
                (function(d, s, id) {
                var e, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                e = d.createElement(s); e.id = id;
                e.src = "//apis.google.com/js/api.js?onload=handleClientLoad";
                fjs.parentNode.insertBefore(e, fjs);
                }(document, 'script', 'google-jssdk'));
            </script>
    <?php
        }
    }
    add_shortcode( 'farost_logged', 'farost_shortcode_logged' );
}