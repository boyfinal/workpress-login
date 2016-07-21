<?php

if (!function_exists('farost_login_option')) {
    function farost_login_option($key = '', $default = '')
    {
        global $farost_login_options;
        if ( empty($farost_login_options) ) {
            return $default;
        }
        if ($key) {
            if ( empty($farost_login_options[$key]) ) {
                return $default;
            }
            return $farost_login_options[$key];
        } else {
            return $farost_login_options;
        }
    }
}
if (!function_exists('farost_button_login_facebook'))
{
    function farost_button_login_facebook($show = 'yes')
    {   
        if ( empty(farost_login_option('providers')) || !in_array('facebook',farost_login_option('providers')) || empty(farost_login_option('fb_key')) || $show != 'yes' ) {
            return;
        }
        ?>
        <div class="box-btn-facebook">
            <button id="facebook-login-btn" class="facebook-login-btn" onclick="facebook_signin();">
                <i class="ti-facebook"></i>
            </button>
        </div>
        <script>
        /* Facebook */

        // This is called with the results from from FB.getLoginStatus().
        // This function is called when someone finishes with the Login
        // Button.  See the onlogin handler attached to it in the sample
        // code below.
        function facebook_signin() {
            FB.getLoginStatus(function(response) {
                if (response.status === 'connected') {
                    FB.api('/me', { locale: 'en_US', fields: 'id,name,bio,about,link,email,first_name,last_name' }, function(response) {
                        var data = {};
                        data.action  = farost_plugin_js_login.action;
                        data.process = 'social';
                        data.type    = 'facebook';
                        data.user    = response;
                        jQuery.ajax({
                            url: farost_plugin_js_login.ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function (results) {
                                if(results.error != 0){
                                    console.log(results.message);
                                }else{
                                    window.location.href = location.href;
                                }
                            }
                        });
                    });
                } else {
                    FB.login();
                }
            });
        }
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '<?php echo farost_login_option('fb_key');?>',
                cookie     : true,  // enable cookies to allow the server to access 
                                    // the session
                xfbml      : true,  // parse social plugins on this page
                version    : 'v2.6' // use version 2.2
            });
        };
        // Load the SDK asynchronously
        (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        </script>
        <?php
    }
}
if (!function_exists('farost_button_login_google'))
{
    function farost_button_login_google($show = 'yes')
    {   
        if ( empty(farost_login_option('providers')) || !in_array('google',farost_login_option('providers')) || empty(farost_login_option('google_key')) || $show != 'yes' ) {
            return;
        }
        ?>
        <div class="box-btn-google">
            <button id="google-login-btn" class="google-login-btn"><i class="ti-google"></i></button>
        </div>
        <script>
        function handleClientLoad() {
            gapi.load('client:auth2', function(){
                // Retrieve the singleton for the GoogleAuth library and set up the client.
                auth2 = gapi.auth2.init({
                    client_id: '<?php echo farost_login_option('google_key');?>',
                    cookiepolicy: 'single_host_origin',
                    // Request scopes in addition to 'profile' and 'email'
                    'scope': 'profile email',
                });
                google_signin(document.getElementById('google-login-btn'));
            });
        };
        function google_signin(element) {
            auth2.attachClickHandler(element, {},
                function(googleUser) {
                    var data = {}, baseProfile = googleUser.getBasicProfile();
                    data.action  = farost_plugin_js_login.action;
                    data.process = 'social';
                    data.type    = 'google';
                    data.user    = {
                        email: baseProfile.getEmail(),
                        lastname: baseProfile.getFamilyName(),
                        fistname: baseProfile.getGivenName(),
                        id: baseProfile.getId(),
                        image: baseProfile.getImageUrl(),
                        name: baseProfile.getName(),
                    };
                    jQuery.ajax({
                        url: farost_plugin_js_login.ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function (results) {
                            console.log(results)
                            if(results.error != 0){
                                console.log(results.message);
                            }else{
                                window.location.href = location.href;
                            }
                        }
                    });
                }, function(error) {
                    console.log(JSON.stringify(error, undefined, 2));
                }
            );
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
if (!function_exists('farost_button_login_twitter'))
{
    function farost_button_login_twitter($show = 'yes')
    {   
        if ( empty(farost_login_option('providers')) || !in_array('twitter',farost_login_option('providers')) || empty(farost_login_option('twitter_key')) || empty(farost_login_option('twitter_secret')) || $show != 'yes' ) {
            return;
        }
        ?>
        <div class="box-btn-twitter">
            <a href="<?php echo trailingslashit(home_url());?>?farost-twitter-login=1" class="twtter-login-btn">
                <i class="ti-twitter-alt"></i>
            </a>
        </div>
        <?php
    }
}

// Apply filter
add_filter( 'get_avatar' , 'farost_custom_avatar' , 1 , 5 );

function farost_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
    $user = false;

    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );   
    }

    if($user && is_object( $user )){
        $avatar_size = 'farost_user_large_avatar';
        if(get_user_meta($user->ID, $avatar_size, true) == ''){
            $avatar_size = 'farost_user_avatar';
        }

        if ( ($avatar = get_user_meta($user->ID, $avatar_size, true)) !== false && strlen(trim($avatar)) > 0) {
                $avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";

        }
    }
    return $avatar;
}