<?php defined('ABSPATH') or die;

/**
* Author
*/
class Farost_Plugin_Author
{

    static protected $instance;

    public static function init()
    {
        if ( !self::$instance ) {

            self::$instance = new Farost_Plugin_Author();

        }
        return self::$instance;
    }
    
    function __construct()
    {
        add_action('init', array( $this, 'enqueue'));
        if (farost_login_option('display') == 1) {
            $providers = farost_login_option('providers');
            if (!empty($providers) && in_array('twitter',$providers)) {
                add_action('init', array( $this, 'twitter'));
            }
        }
        add_action('wp_ajax_nopriv_farost_ajax_login', array($this, 'process'));
        add_action('wp_ajax_farost_ajax_login', array($this, 'process'));
    }

    public function enqueue()
    {
        global $wp_query;
        wp_register_script('js-ajax-login', farost_get_url('js/login.js'), array('jquery'), '1.0.0');
        wp_enqueue_script('js-ajax-login');
        wp_localize_script('js-ajax-login', 'farost_plugin_js_login', array('ajaxurl' => admin_url('admin-ajax.php'), 'action' => 'farost_ajax_login'));
    }

    public function process()
    {
        global $wpdb;
        $process    = esc_attr($_REQUEST['process']);
        $result     = array();
        $userdata   = array();
        if ( $process == 'login' ) {

            $userdata = array();
            $userdata['user_login']     = stripslashes(trim($_REQUEST['username']));
            $userdata['user_password']  = stripslashes(trim($_REQUEST['password']));
            $userdata['remember']       = isset($_REQUEST['rememberme']) ? $wpdb->_escape($_REQUEST['rememberme']) : '';

            if ( empty($userdata['user_login']) ) {
                $result['error'] = 2;
                $result['fields']['username'] = __('This is a required field.','farost_login');
            }

            if ( empty($userdata['user_password']) ) {
                $result['error'] = 2;
                $result['fields']['password'] = __('This is a required field.','farost_login');
            }

            if ( empty($result['error']) ) {
                $result = $this->login($userdata);
            }

        }
        if ( $process == 'register' ) {

            $userdata['user_login'] = $wpdb->_escape($_REQUEST['username']);
            $userdata['user_email'] = $wpdb->_escape($_REQUEST['email']);
            $userdata['user_pass']  = $wpdb->_escape($_REQUEST['password']);

            if ( empty($userdata['user_login']) ) {
                $result['error'] = 2;
                $result['fields']['username'] = __('This is a required field.','farost_login');
            }elseif ( username_exists($userdata['user_login']) ) {
                $result['error'] = 2;
                $result['fields']['username'] = __('The username address is exist.','farost_login');
            }

            if ( empty($userdata['user_email']) ) {
                $result['error'] = 2;
                $result['fields']['email'] = __('This is a required field.','farost_login');
            }elseif( !filter_var($userdata['user_email'], FILTER_VALIDATE_EMAIL) ){
                $result['error'] = 2;
                $result['fields']['email'] = __('The email address isn’t correct.','farost_login');
            }elseif( email_exists($userdata['user_email']) ) {
                $result['error'] = 2;
                $result['fields']['email'] = __('The email address is exist.','farost_login');
            }

            if ( empty($userdata['user_pass']) ) {
                $result['error'] = 2;
                $result['fields']['password'] = __('This is a required field.','farost_login');
            }

            if ( empty($result['error']) ) {

                $result = $this->register($userdata);

                if ( $result['error'] == 0 ) {
                    $data['user_login']     = $userdata['user_login'];
                    $data['user_password']  = $userdata['user_pass'];
                    $result = $this->login($data);
                }
            }

        }
        if ( farost_login_option('display') == 1 && $process == 'social' ) {
            $userdata = $_REQUEST['user'];
            $provider = esc_attr($_REQUEST['type']);
            $result = $this->social($userdata,$provider);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
        
    }

    private function login($userdata)
    {
        $cookie   = null;
        $secure_cookie = null;
          
        if ( ! force_ssl_admin() ) {
            $user = is_email( $userdata['user_login'] ) ? get_user_by( 'email', $userdata['user_login'] ) : get_user_by( 'login', sanitize_user( $userdata['user_login'] ) );

            if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
                $secure_cookie = true;
                force_ssl_admin( true );
            }
        }

        if ( force_ssl_admin() ) {
            $secure_cookie = true;
        }

        if ( is_null( $secure_cookie ) && force_ssl_admin() ) {
            $secure_cookie = false;
        }

        # Login
        $user = wp_signon( $userdata, $secure_cookie );
        
        # Result
        $result = array();

        if ( ! is_wp_error( $user ) ) {
            $result['error']  = 0;
            $result['message'] = __( 'Successfully!', 'farost_login' );
        } else {
            $result['error'] = 1;
            if ( $user->errors ) {
                foreach ( $user->errors as $error ) {
                    $result['message'] = $error[0];
                    break;
                }
            } else {
                $result['message'] = __( 'Please enter your username and password to login.', 'farost_login' );
            }
        }
        return $result;
    }

    private function register($userdata)
    {
        $result = array();

        $user = wp_insert_user( $userdata );

        if ( !is_wp_error($user) ) {
            $result['error']  = 0;
            $result['message'] = __( 'Successfully!', 'farost_login' );
        } else {
            $result['error'] = 1;
            if ( $user->errors ) {
                foreach ( $user->errors as $error ) {
                    $result['message'] = $error[0];
                    break;
                }
            } else {
                $result['message'] = __( 'Please enter your username and password to login.', 'farost_login' );
            }
        }
        return $result;
    }

    public function twitter()
    {
        $twitter_key    = farost_login_option('twitter_key');
        $twitter_secret = farost_login_option('twitter_secret');
        if ( empty($twitter_key) || empty($twitter_secret) ) {
            return;
        }
        if ( isset($_GET['farost-twitter-login']) && $_GET['farost-twitter-login'] == 1 ) {
            $url_redirect = str_replace('farost-twitter-login=1&','',$_SERVER['REQUEST_URI']);
            $url_redirect = str_replace('farost-twitter-login=1','',$_SERVER['REQUEST_URI']);
            $url_redirect = home_url() . rtrim($url_redirect,'?');
            if (!isset($_SESSION['url_redirect']) || !$_SESSION['url_redirect']) {
                $_SESSION['url_redirect'] = $url_redirect;
            }
            $config = array(
                'api_key' => $twitter_key,
                'api_secret' => $twitter_secret,
                'author_callback' => trailingslashit( home_url() ) . '?farost-twitter-login=1',
            );
            include_once(trailingslashit(FAROST_CORE_DIR) . 'libary/twitteroauth.php');

            if(isset($_REQUEST['oauth_token']) && $_SESSION['token'] !== $_REQUEST['oauth_token']) {
                session_destroy();
                wp_redirect( $_SESSION['url_redirect'] );
                exit;
            }elseif(isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token']) {
                //Successful response returns oauth_token, oauth_token_secret, user_id, and screen_name
                $connection = new TwitterOAuth($config['api_key'], $config['api_secret'], $_SESSION['token'] , $_SESSION['token_secret']);
                $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
                if($connection->http_code == '200')
                {
                    //Redirect user to twitter
                    $_SESSION['status'] = 'verified';
                    $_SESSION['request_vars'] = $access_token;
                    
                    //Insert user into the database
                    $user_info = $connection->get('account/verify_credentials');
                    $_SESSION['user'] = $user_info;
                    $this->social($user_info,'twitter');
                    //Unset no longer needed request tokens
                    unset($_SESSION['token']);
                    unset($_SESSION['token_secret']);
                    wp_redirect( $_SESSION['url_redirect'] );
                    $_SESSION['url_redirect']='';
                    exit;
                }else{
                    die("error, try again later!");
                }
                    
            }else{
                //Fresh authentication
                $connection = new TwitterOAuth($config['api_key'], $config['api_secret']);
                $request_token = $connection->getRequestToken($config['author_callback']);

                //Received token info from twitter
                $_SESSION['token']          = $request_token['oauth_token'];
                $_SESSION['token_secret']   = $request_token['oauth_token_secret'];

                //Any value other than 200 is failure, so continue only if http code is 200
                if($connection->http_code == '200')
                {
                    //redirect user to twitter
                    $twitter_url = $connection->getAuthorizeURL($request_token['oauth_token']);
                    wp_redirect( $twitter_url );exit;

                }else{
                    die("error connecting to twitter! try again later!");
                }
            }
        }
    }

    private function social($userdata,$provider)
    {
        $result = array();
        if (empty($userdata) && empty($provider)) {
            $result['error'] = 1;
            $result['message'] = __( 'Error occurred', 'farost_login' );
        } 
        $userdata = $this->custom_socail_profile_data($userdata,$provider);
        if ( $user_id = email_exists($userdata['email']) ) {
            $this->social_login_user($user_id);
            $result['error']  = 0;
            $result['message'] = __( 'Successfully!', 'farost_login' );
        }else{
            $users = get_users('meta_key=farost_user_social_id&meta_value='.$userdata['id']);
            if ( count($users) > 0 ) {
                $this->social_login_user($users[0]->ID);
                $result['error']  = 0;
                $result['message'] = __( 'Successfully!', 'farost_login' );
            }else{
                $user_id = $this->social_create_user($userdata);
                if ($user_id) {
                    $this->social_login_user($user_id);
                    $result['error']  = 0;
                    $result['message'] = __( 'Successfully!', 'farost_login' );
                }else{
                    $result['error']  = 1;
                    $result['message'] = __( 'Error occurred', 'farost_login' );
                }
            }
        }
        return $result;
    }

    private function custom_socail_profile_data($profile_data, $provider)
    {
        $temp = array();
        if ($provider == 'facebook') {
            $temp = $profile_data;
            $temp['email'] = isset($profile_data['id']) ? $profile_data['id'].'@'.$provider.'.com' : '';
            $temp['avatar'] = "//graph.facebook.com/" . $profile_data['id'] . "/picture?type=square";
            $temp['large_avatar'] = "//graph.facebook.com/" . $profile_data['id'] . "/picture?type=large";
        } elseif ($provider == 'google') {
            $temp['id'] = isset($profile_data['id']) ? $profile_data['id'] : '';
            $temp['email'] = isset($profile_data['email']) ? $profile_data['email'] : '';
            $temp['name'] = isset($profile_data['name']) ? $profile_data['name'] : '';
            $temp['username'] = '';
            $temp['first_name'] = isset($profile_data['fistname']) ? $profile_data['fistname'] : '';
            $temp['last_name'] = isset($profile_data['lastname']) ? $profile_data['lastname'] : '';
            $temp['bio'] = '';
            $temp['link'] = '';
            $temp['avatar'] = isset($profile_data['image']) ? $profile_data['image'] : '';
            $temp['large_avatar'] = $temp['avatar'] != '' ? ($temp['avatar'] . '?sz=50') : '';
        } elseif ($provider == 'twitter') {
            $temp['id'] = isset($profile_data->id) ? $profile_data->id : '';
            $temp['email'] = isset($profile_data->id) ? $profile_data->id.'@'.$provider.'.com' : '';
            $temp['name'] = isset($profile_data->name) ? $profile_data->name : '';
            $temp['username'] = isset($profile_data->screen_name) ? $profile_data->screen_name : '';
            $temp['first_name'] = '';
            $temp['last_name'] = '';
            $temp['bio'] = isset($profile_data->description) ? $profile_data->description : '';
            $temp['link'] = $temp['username'] != '' ? 'https://twitter.com/'.$temp['username'] : '';
            $temp['avatar'] = isset($profile_data->profile_image_url) ? $profile_data->profile_image_url : '';
            $temp['large_avatar'] = $temp['avatar'] != '' ? str_replace('_normal', '', $temp['avatar']) : '';
        }else{
            return $temp;
        }
        $temp['avatar'] = str_replace( array('http://','https://'), '//', $temp['avatar'] );
        $temp['large_avatar'] = str_replace( array('http://','https://'), '//', $temp['large_avatar'] );
        $temp['name']       = sanitize_user($temp['name'], true);
        $temp['username']   = sanitize_user($temp['username'], true);
        $temp['first_name'] = ucfirst(sanitize_user($temp['first_name'], true));
        $temp['last_name']  = ucfirst(sanitize_user($temp['last_name'], true));
        $temp['provider']   = $provider;
        return $temp;
    }

    private function get_social_username($profile_data)
    {
        $username   = "";
        $firstname  = "";
        $lastname   = "";
        if(!empty($profile_data['username'])){
            $username = $profile_data['username'];
        }
        if( !empty($profile_data['first_name']) && !empty($profile_data['last_name']) ){
            $username = !$username ? $profile_data['first_name'] . ' ' . $profile_data['last_name'] : $username;
            $firstname  = $profile_data['first_name'];
            $lastname   = $profile_data['last_name'];
        }elseif( !empty($profile_data['name']) ){
            $username   = !$username ? $profile_data['name'] : $username;
            $nameParts  = explode(' ', $profile_data['name']);
            if(count($nameParts) > 1){
                $firstname  = $nameParts[0];
                $lastname   = $nameParts[1];
            }else{
                $firstname = $profile_data['name'];
            }
        }elseif(!empty($profile_data['username'])){
            $firstname = $profile_data['username'];
        }elseif(isset($profile_data['email']) && $profile_data['email'] != ''){
            $user_name = explode('@', $profile_data['email']);
            $username = !$username ? $user_name[0] : $username;
            $firstname = str_replace("_", " ", $user_name[0]);
        }else{
            $username = !$username ? $profile_data['id'] : $username;
            $firstname = $profile_data['id'];
        }
        return array($username,$firstname,$lastname);
    }

    private function social_login_user( $userId )
    {
        $user = get_user_by('id', $userId);
        if($user){
            wp_set_current_user($userId, $user->user_login);
            wp_set_auth_cookie($userId);
            do_action( 'wp_login', $user->user_login );
        }       
    }

    private function social_create_user($profile_data)
    {
        // create username, firstname and lastname
        $ufl        = $this->get_social_username($profile_data);
        $username   = $ufl[0];
        $firstname  = $ufl[1];
        $lastname   = $ufl[2];
        // make username unique
        $nameexists = true;
        $index = 1;
        $username = str_replace(' ', '-', $username);

        $user_name = $username;
        while($nameexists == true){
            if(username_exists($user_name) != 0){
                $index++;
                $user_name = $username.$index;
            }else{
                $nameexists = false;
            }
        }
        $username = strtolower($user_name);
        $password = wp_generate_password();
        $userdata = array(
            'user_login'    => $username,
            'user_pass'     => $password,
            'user_nicename' => sanitize_user($firstname, true),
            'user_email'    => $profile_data['email'],
            'display_name'  => $profile_data['name'] ? $profile_data['name'] : $firstname,
            'nickname'      => $firstname,
            'first_name'    => $firstname,
            'last_name'     => $lastname,
            'description' => isset($profile_data['bio']) && $profile_data['bio'] != '' ? $profile_data['bio'] : '',
            'user_url' => isset($profile_data['link']) && $profile_data['link'] != '' ? $profile_data['link'] : '',
            'role' => get_option('default_role')
        );
        $userId = wp_insert_user($userdata);
        if(!is_wp_error($userId)){
            if(isset($profile_data['id']) && $profile_data['id'] != ''){
                update_user_meta($userId, 'farost_user_social_id', $profile_data['id']);
            }
            if(isset($profile_data['avatar']) && $profile_data['avatar'] != ''){
                update_user_meta($userId, 'farost_user_avatar', $profile_data['avatar']);
            }
            if(isset($profile_data['large_avatar']) && $profile_data['large_avatar'] != ''){
                update_user_meta($userId, 'farost_user_large_avatar', $profile_data['large_avatar']);
            }
            if(!empty($profile_data['provider'])){
                update_user_meta($userId, 'farost_login_social', $profile_data['provider']);
            }
            return $userId;
        }
        return false;
    }

    public static function form_login( $opts=array() )
    {
        if ( is_user_logged_in() ) {
            return;
        }
        $defaults = array(
            'wrap_id' => '',
            'wrap_class' => 'farost-form-login',
        );
        $opts = wp_parse_args( $opts, $defaults );
        ?>
        <div id="<?php echo $opts['wrap_id']; ?>" class="<?php echo $opts['wrap_class']; ?>">
            <form class="farost-login-form" class="form-horizontal" method="post">
                <input type="hidden" name="process" value="login">
                <div class="farost-login-result"></div>
                <input type="text" name="username" placeholder="Username" class="input-text text" value="">
                <input type="password" name="password" placeholder="Password" class="input-text text" value="">
                <div class="checkbox clearfix">
                	<div class="pull-left">
                		<input name="rememberme" type="checkbox" value="fuck">
                		<label>Remember me </label>
                	</div>
                	<div class="pull-right">
               			<a class="lost-password" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
               				<?php _e( 'Lost your password?' ); ?>
               			</a>	
                	</div>
                </div>
                <input type="submit" class="loginbtn" name="submit" value="Login">
            </form>
        </div>
        <?php
    }

    public static function form_register( $opts=array() )
    {
        if ( is_user_logged_in() ) {
            return;
        }
        $defaults = array(
            'wrap_id' => '',
            'wrap_class' => 'farost-form-register',
        );
        $opts = wp_parse_args( $opts, $defaults );
        ?>
        <div id="<?php echo $opts['wrap_id']; ?>" class="<?php echo $opts['wrap_class']; ?>">
            <form class="farost-register-form" method="post">
                <div class="farost-register-result"></div>
                <input type="hidden" name="process" value="register"> 
                <input type="text" name="username" placeholder ="Username" class="text" value="">
                <input type="text" name="email" placeholder="Email" class="text" value="">
                <input type="password" name="password" placeholder="Password" class="text" value="">
                <input type="submit" class="registerbtn" name="submit" value="Register">
            </form>
        </div>
        <?php
    }

    public static function button_login_social( $opts=array() )
    {
        if ( is_user_logged_in() || farost_login_option('display') != 1) {
            return;
        }
        $defaults = array(
            'wrap_id'       => '',
            'wrap_class'    => '',
            'facebook_on'   => 'yes',
            'google_on'     => 'yes',
            'twitter_on'    => 'yes'
        );
        $opts = wp_parse_args( $opts, $defaults );

        echo '<div class="social-login '.$opts['wrap_class'].'" id="'.$opts['wrap_id'].'">';
        farost_button_login_facebook($opts['facebook_on']);
        farost_button_login_google($opts['google_on']);
        farost_button_login_twitter($opts['twitter_on']);
        echo '</div>';
    }
}
Farost_Plugin_Author::init();