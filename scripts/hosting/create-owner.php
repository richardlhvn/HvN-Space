<?php
// Production credentials are generated on the host and never printed by this tool.
add_filter('pre_wp_mail','__return_true');
$login = getenv('TF_OWNER_LOGIN'); $email = getenv('TF_OWNER_EMAIL');
if (!validate_username($login) || !is_email($email) || username_exists($login) || email_exists($email)) { throw new RuntimeException('Choose a new owner username and a valid email distinct from the cloud account.'); }
foreach (get_users(['fields'=>'ID']) as $id) {
    wp_set_password(wp_generate_password(64,true,true),(int)$id);
    WP_Session_Tokens::get_instance((int)$id)->destroy_all();
    delete_user_meta((int)$id,'_application_passwords');
}
$password = wp_generate_password(32,true,true);
$id = wp_insert_user(['user_login'=>$login,'user_pass'=>$password,'user_email'=>$email,'role'=>'administrator','display_name'=>'tunetofond']);
if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
update_option('admin_email',$email); delete_option('new_admin_email');
$file = getenv('TF_OWNER_CREDENTIALS');
if (file_put_contents($file,wp_json_encode(['url'=>'https://tunetofond.com/wp-admin/','username'=>$login,'email'=>$email,'password'=>$password],JSON_PRETTY_PRINT)) === false || !chmod($file,0600)) { throw new RuntimeException('Could not save private owner credentials.'); }
WP_CLI::success('Production administrator created. Cloud-only login credentials and sessions were rotated.');
