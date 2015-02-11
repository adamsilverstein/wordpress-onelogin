<?php
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>SSO/SAML Settings &lsaquo; Demo Onelogin wordpress &#8212; WordPress</title>
</head>
<body>

<?php

if (!current_user_can('delete_plugins')) {
     header("HTTP/1.0 403 Forbidden");
     echo '<h1>'.esc_html__("Access Forbidden!", 'onelogin-saml-sso').'</h1>';
     exit();
}

echo '<h1>'.esc_html__('OneLogin SSO/SAML Settings validation', 'onelogin-saml-sso').'</h1>';

echo esc_html__('Debug mode', 'onelogin-saml-sso').' '. ($settings['strict']?'<strong>on</strong>. '.esc_html__("In production turn it off", 'onelogin-saml-sso'):'<strong>off</strong>').'<br>';
echo esc_html__('Strict mode', 'onelogin-saml-sso').' '. ($settings['debug']?'<strong>on</strong>':'<strong>off</strong>. '.esc_html__("In production we recommend to turn it on.", 'onelogin-saml-sso')).'<br>';

$spPrivatekey = $settings['sp']['x509cert'];
$spCert = $settings['sp']['privateKey'];

try {
	$samlSettings = new OneLogin_Saml2_Settings($settings);
	echo '<br>'.esc_html__("SAML settings are", 'onelogin-saml-sso').' <strong>ok</strong>.<br>';
} catch (Exception $e) {
	echo '<br>'.esc_html__("Found errors while validating SAML settings info.", 'onelogin-saml-sso');
	print_r($e->getMessage());
	echo '<br>';
}

$forcelogin = get_option('onelogin_saml_forcelogin');
if ($forcelogin) {
	echo '<br>'.esc_html__("Force SAML Login is enabled, that means that the user will be redirected to the IdP before getting access to Wordpress.", 'onelogin-saml-sso').'<br>';
}

$slo = get_option('onelogin_saml_slo');
if ($slo) {
	echo '<br>'.esc_html__("Single Log Out is enabled. If the SLO process fail, close your browser to be sure that session of the apps are closed.", 'onelogin-saml-sso').'<br>';
} else {
	echo '<br>'.esc_html__("Single Log Out is disabled. If you log out from Wordpress your session at the IdP keeps alive.", 'onelogin-saml-sso').'<br>';
}

$fileSystemKeyExists = file_exists(plugin_dir_path(__FILE__).'certs/sp.key');
$fileSystemCertExists = file_exists(plugin_dir_path(__FILE__).'certs/sp.crt');
if ($fileSystemKeyExists) {
	$privatekey_url = plugins_url('php/certs/sp.key', dirname(__FILE__));
	echo '<br>'.esc_html__("There is a private key stored at the filesystem. Protect the 'certs' path. Nobody should be allowed to access:", 'onelogin-saml-sso').'<br>'.$privatekey_url.'<br>';
}

if ($spPrivatekey && !empty($spPrivatekey)) {
	echo '<br>'.esc_html__("There is a private key stored at the database. (An attacker could own your database and get it. Take care)", 'onelogin-saml-sso').'<br>';
}

if (($spPrivatekey && !empty($spPrivatekey) && $fileSystemKeyExists) ||
	($spCert && !empty($spCert) && $fileSystemCertExists)) {
	echo '<br>'.esc_html__("Private key/certs stored on database have priority over the private key/cert stored at filesystem", 'onelogin-saml-sso').'<br>';
}

$autocreate = get_option('onelogin_saml_autocreate');
$updateuser = get_option('onelogin_saml_updateuser');

if ($autocreate) {
	echo '<br>'.esc_html__("User will be created if not exists, based on the data sent by the IdP.", 'onelogin-saml-sso').'<br>';
} else {
	echo '<br>'.esc_html__("If the user not exists, access is prevented.", 'onelogin-saml-sso').'<br>';
}

if ($updateuser) {
	echo '<br>'.esc_html__("User account will be updated with the data sent by the IdP.", 'onelogin-saml-sso').'<br>';
}

if ($autocreate || $updateuser) {
	echo '<br>'.esc_html__("Is important to set the attribute and the role mapping before auto-provisioning or updating an account.", 'onelogin-saml-sso').'<br>';
}

$attr_mappings = array (
	'onelogin_saml_attr_mapping_username' => esc_html__('Username', 'onelogin-saml-sso'),
	'onelogin_saml_attr_mapping_mail' => esc_html__('E-mail', 'onelogin-saml-sso'),
	'onelogin_saml_attr_mapping_firstname' => esc_html__('First Name', 'onelogin-saml-sso'),
	'onelogin_saml_attr_mapping_lastname' => esc_html__('Last Name', 'onelogin-saml-sso'),
	'onelogin_saml_attr_mapping_role' => esc_html__('Role', 'onelogin-saml-sso'),
);

$account_matcher = get_option('onelogin_saml_account_matcher', 'username');

$lacked_attr_mappings = array();
foreach ($attr_mappings as $field => $name) {
	$value = get_option($field);
	if (empty($value)) {
		if ($account_matcher == 'username' && $field == 'onelogin_saml_attr_mapping_username') {
			echo '<br>'. esc_html__("Username mapping is required in order to enable the SAML Single Sign On", 'onelogin-saml-sso').'<br>';
		}
		if ($account_matcher == 'email' && $field == 'onelogin_saml_attr_mapping_mail') {
			echo '<br>'. esc_html__("E-mail mapping is required in order to enable the SAML Single Sign On", 'onelogin-saml-sso').'<br>';
		}
		$lacked_attr_mappings[] = $name;
	}
}

if (!empty($lacked_attr_mappings)) {
	echo '<br>'. esc_html__("Notice that there are attributes without mapping:", 'onelogin-saml-sso').'<br>';
	print_r(implode('<br>', $lacked_attr_mappings).'</br>');
}

$role_mappings = array (
	'onelogin_saml_role_mapping_administrator' => esc_html__('Administrator', 'onelogin-saml-sso'),
	'onelogin_saml_role_mapping_editor' => esc_html__('Editor', 'onelogin-saml-sso'),
	'onelogin_saml_role_mapping_author' => esc_html__('Author', 'onelogin-saml-sso'),
	'onelogin_saml_role_mapping_contributor' => esc_html__('Contributor', 'onelogin-saml-sso'),
	'onelogin_saml_role_mapping_subscriber' => esc_html__('Subscriber', 'onelogin-saml-sso')
);

$lacked_role_mappings = array();
foreach ($role_mappings as $field => $name) {
	$value = get_option($field);
	if (empty($value)) {
		$lacked_role_mappings[] = $name;
	}
}

if (!empty($lacked_role_mappings)) {
	echo '<br>'. esc_html__("Notice that there are roles without mapping:", 'onelogin-saml-sso').'<br>';
	print_r(implode('<br>', $lacked_role_mappings).'</br>');
}
?>

</body>
</html>