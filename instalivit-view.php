<?php
	$im_instalivit_default = ini_get('max_execution_time');
	set_time_limit(60 * 5);

	$IM_INSTALIVIT_CLIENT_ID	= get_option("im_instalivit_clientid");
	$IM_INSTALIVIT_ACCESS_TOKEN	= get_option("im_instalivit_accesstoken");
	
	$im_instalivit_AuthData		= "";
	
	if (!$IM_INSTALIVIT_CLIENT_ID && !$IM_INSTALIVIT_ACCESS_TOKEN){
		echo "Please Input Client ID or Access Token in InstaLivit Settings Page";
		die();
	}
	else if ($IM_INSTALIVIT_ACCESS_TOKEN){
		$im_instalivit_AuthData = "access_token=" . $IM_INSTALIVIT_ACCESS_TOKEN;
	}
	else if ($IM_INSTALIVIT_CLIENT_ID){
		$im_instalivit_AuthData = "client_id=" . $IM_INSTALIVIT_CLIENT_ID;
	}
	
	if (!$IM_INSTALIVIT_USERS) $IM_INSTALIVIT_USERS = array();
	
	$im_instalivit_userIds	= array();	
	$im_instalivit_images	= array();
	
	foreach ($IM_INSTALIVIT_USERS as $user){
		$r_get = wp_remote_get("https://api.instagram.com/v1/users/search?q=" . $user . "&" .$im_instalivit_AuthData, array(
			'sslverify'	=> false,
			'timeout' => 30
		));
		
		if (is_array($r_get)) {
			$r_object = json_decode($r_get["body"]);
			if (isset($r_object->meta) && isset($r_object->meta->error_message)){
				echo $r_object->meta->error_message . '<br />';
				continue;
			}
			foreach ($r_object->data as $data){
				if ($data->username == $user){
					array_push($im_instalivit_userIds, $data->id);
					break;
				}
			}
		}
		else echo "[Network Error] Cannot Load Instagram Content (".$user.")<br />";
	}
	
	foreach ($im_instalivit_userIds as $uid){
		$r_get = wp_remote_get("https://api.instagram.com/v1/users/" . $uid . "/media/recent?count=60&" . $im_instalivit_AuthData, array(
			'sslverify'	=> false,
			'timeout' => 30
		));
		
		if (is_array($r_get)){
			$r_object = json_decode($r_get["body"]);
			if (isset($r_object->meta) && isset($r_object->meta->error_message)){
				echo $r_object->meta->error_message . '<br />';
				continue;
			}
			foreach ($r_object->data as $data){			
			
				if (count($IM_INSTALIVIT_TAGS) > 0){
					$hasTag = false;
					foreach ($IM_INSTALIVIT_TAGS as $tag){
						foreach ($data->tags as $dtag){
							if (mb_convert_case($dtag, MB_CASE_LOWER, "UTF-8") == mb_convert_case($tag, MB_CASE_LOWER, "UTF-8")){
								$hasTag = true;
								break;
							}
						}
						if ($hasTag == true) break;
					}
					
					if ($hasTag == false) continue;
				}
				
				if ($data->type != "image") continue;
				array_push($im_instalivit_images, array(
					'id'	=> $data->id,
					'src'	=> $data->images->standard_resolution->url,
					'src_l'	=> $data->images->low_resolution->url,
					'text'	=> $data->caption->text
				));
			}	
		}
		else echo "[Network Error] Cannot Load Instagram Content (".$uid.")<br />";
	}

	if (count($IM_INSTALIVIT_USERS) == 0 && count($IM_INSTALIVIT_TAGS) > 0){
		foreach ($IM_INSTALIVIT_TAGS as $tag){
			$r_get = wp_remote_get("https://api.instagram.com/v1/tags/" . mb_convert_case($tag, MB_CASE_LOWER, "UTF-8") . "/media/recent?count=60&" . $im_instalivit_AuthData, array(
				'sslverify'	=> false,
				'timeout' => 30
			));
			
			if (is_array($r_get)){
				$r_object = json_decode($r_get["body"]);
				if (isset($r_object->meta) && isset($r_object->meta->error_message)){
					echo $r_object->meta->error_message . '<br />';
					continue;
				}
				
				foreach ($r_object->data as $data){		
					if ($data->type != "image") continue;
					array_push($im_instalivit_images, array(
						'id'	=> $data->id,
						'src'	=> $data->images->standard_resolution->url,
						'src_l'	=> $data->images->low_resolution->url,
						'text'	=> $data->caption->text
					));
				}	
			}
			else echo "[Network Error] Cannot Load Instagram Content (".$tag.")<br />";
		}
	}
	
	if (count($IM_INSTALIVIT_USERS) == 0 && count($IM_INSTALIVIT_TAGS) == 0){
		$r_get = wp_remote_get("https://api.instagram.com/v1/media/popular?count=60&" . $im_instalivit_AuthData, array(
			'sslverify'	=> false,
			'timeout' => 30
		));
		
		if (is_array($r_get)){
			$r_object = json_decode($r_get["body"]);
			if (isset($r_object->meta) && isset($r_object->meta->error_message)){
				echo $r_object->meta->error_message . '<br />';
				continue;
			}
			foreach ($r_object->data as $data){		
				if ($data->type != "image") continue;
				array_push($im_instalivit_images, array(
					'id'	=> $data->id,
					'src'	=> $data->images->standard_resolution->url,
					'src_l'	=> $data->images->low_resolution->url,
					'text'	=> $data->caption->text
				));
			}	
		}
		else echo "[Network Error] Cannot Load Instagram Content (".$tag.")<br />";
	}

	set_time_limit($im_instalivit_default);
?>

<div class="wrap">
	<div id="instalivit" class="isotope">
		<?php
			foreach ($im_instalivit_images as $image){
				?>
				<div id="instalivit-item-<?php echo $image["id"]; ?>" class="instalivit-item">
					<img src="<?php echo $image["src_l"]; ?>" style="width:300px;height:300px;" class="instalivit-image" onclick="im_instalivit_expand('<?php echo $image["id"]; ?>')" />
					<div id="instalivit-comments-<?php echo $image["id"]; ?>" class="instalivit-comments">
						<div class="instalivit-comments-item"></div>
						<div class="instalivit-comments-item"></div>
						<div class="instalivit-comments-item"></div>
						<div class="instalivit-comments-more">more...</div>
						<br />
						<div class="instalivit-write">
							<input type="text" style="width:96%;margin:2%" />
							<input type="button" style="width:96%;margin:2%" class="button button-primary" value="Post Comment" />
						</div>
					</div>					
				</div>
				<?php
			}
		?>
	</div>
</div>

<script>
jQuery(document).ready(function($) {	
	$('.isotope').isotope({
		itemSelector: '.instalivit-item',
		masonry: {
			gutter: 10
		}
	}); 
});
</script>
