<?php
global $wsko_data;
$token = ($wsko_data && isset($wsko_data['token'])) ? $wsko_data['token'] : false;

if ($token)
{
	if (!function_exists('wsko_get_ga_client'))
		require_once(plugin_dir_path( __FILE__ ) . '/functions.php');
	
	wsko_require_google_lib();
	$client = wsko_get_ga_client();
	if ($client)
	{
		$client->setAccessToken($token);
		if ($client->isAccessTokenExpired())
		{
			$client->refreshToken($token['refresh_token']);
			$token_c = $client->getAccessToken();
			if (!isset($token_c['refresh_token']))
			{
				$token_c['refresh_token'] = $token['refresh_token'];
			}
			$token = $token_c;
			if ($token)
			{
				$wsko_data['token'] = $token;
			}
			else
			{
				$wsko_data['token'] = false;
			}
			
			update_option('wsko_init', $wsko_data);
		}
	}
}
?>