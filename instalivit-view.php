<?php
	global $wpdb;
	
	$im_instalivit_default 	= ini_get('max_execution_time');
	$im_instalivit_dbname	= $wpdb->prefix . "instalivit";
	
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
					'id'		=> $data->id,
					'src'		=> $data->images->standard_resolution->url,
					'src_l'		=> $data->images->low_resolution->url,
					'text'		=> $data->caption->text,
					'comment'	=> $wpdb->get_results("SELECT * FROM $im_instalivit_dbname WHERE id='$data->id' ORDER BY timestamp DESC LIMIT 0,3", ARRAY_A)
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
						'id'		=> $data->id,
						'src'		=> $data->images->standard_resolution->url,
						'src_l'		=> $data->images->low_resolution->url,
						'text'		=> $data->caption->text,
						'comment'	=> $wpdb->get_results("SELECT * FROM $im_instalivit_dbname WHERE id='$data->id' ORDER BY timestamp DESC LIMIT 0,3", ARRAY_A)
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
					'id'		=> $data->id,
					'src'		=> $data->images->standard_resolution->url,
					'src_l'		=> $data->images->low_resolution->url,
					'text'		=> $data->caption->text,
					'comment'	=> $wpdb->get_results("SELECT * FROM $im_instalivit_dbname WHERE id='$data->id' ORDER BY timestamp DESC LIMIT 0,3", ARRAY_A)
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
						<div id="instalivit-comments-container-<?php echo $image["id"]; ?>">
							<?php
							foreach ($image["comment"] as $comment){
								?>								
								<div class="instalivit-comments-item">									
									<?php 
									echo "<img src='". plugins_url('images/rate/' . $comment["rate"] . ".png", __FILE__) ."' style='width:25px' />&nbsp;&nbsp;&nbsp;";
									echo stripslashes($comment["comment"]); 
									?>
								</div>
								<?php
							}
							if (count($image["comment"]) == 0){
								?>
								<div class="instalivit-comments-item">Still No Comment...</div>
								<?php
							}
							?>
						</div>
						<div class="instalivit-comments-more" onclick="window.location='<?php echo home_url() . '/instalivit?id=' . $image["id"]; ?>'">read more...</div>
						<br />
						<div class="instalivit-write">
							<img id="im_instalivit_rate1-<?php echo $image["id"]; ?>" src="<?php echo plugins_url('images/rate/1.png', __FILE__); ?>" style="margin-left:3px;cursor:pointer;width:25px;border:2px solid transparent" onclick="im_instalivit_rate(1, '<?php echo $image["id"]; ?>')" />
							<img id="im_instalivit_rate2-<?php echo $image["id"]; ?>" src="<?php echo plugins_url('images/rate/2.png', __FILE__); ?>" style="margin-left:3px;cursor:pointer;width:25px;border:2px solid transparent" onclick="im_instalivit_rate(2, '<?php echo $image["id"]; ?>')" />
							<img id="im_instalivit_rate3-<?php echo $image["id"]; ?>" src="<?php echo plugins_url('images/rate/3.png', __FILE__); ?>" style="margin-left:3px;cursor:pointer;width:25px;border:2px dashed black" onclick="im_instalivit_rate(3, '<?php echo $image["id"]; ?>')" />
							<img id="im_instalivit_rate4-<?php echo $image["id"]; ?>" src="<?php echo plugins_url('images/rate/4.png', __FILE__); ?>" style="margin-left:3px;cursor:pointer;width:25px;border:2px solid transparent" onclick="im_instalivit_rate(4, '<?php echo $image["id"]; ?>')" />
							<img id="im_instalivit_rate5-<?php echo $image["id"]; ?>" src="<?php echo plugins_url('images/rate/5.png', __FILE__); ?>" style="margin-left:3px;cursor:pointer;width:25px;border:2px solid transparent" onclick="im_instalivit_rate(5, '<?php echo $image["id"]; ?>')" />
							<input id="instalivit_comment_rate-<?php echo $image["id"]; ?>" name="instalivit_comment_rate" type="hidden" value="3" />
							<input id="instalivit-write-<?php echo $image["id"]; ?>" type="text" style="width:280px;margin-left:10px;margin-right:10px;margin-top:5px;" />
							<input type="button" style="width:280px;margin-left:10px;margin-right:10px;margin-top:5px;margin-bottom:10px" class="button button-primary" value="Post Comment" onclick="im_instalivit_comment_ajax('<?php echo $image["id"]; ?>')" />
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

function im_instalivit_rate(rate, id){
	for (var i=1;i<=5;i++){
		if (i == rate){
			document.getElementById("im_instalivit_rate" + i + "-" + id).style.border = "2px dashed black";
		}
		else{
			document.getElementById("im_instalivit_rate" + i + "-" + id).style.border = "2px dashed transparent";
		}
	}
	document.getElementById("instalivit_comment_rate-" + id).value = "" + rate;
}

function im_instalivit_comment_ajax(id){
	var comment = document.getElementById("instalivit-write-" + id).value;
	var rate = document.getElementById("instalivit_comment_rate-" + id).value;
	
	jQuery.ajax({
		url: "<?php echo home_url(); ?>/instalivit/?id=" + id + "&ajax=true",
		type: "POST",
		dataType: "json",
		data: {
			"instalivit_comment_text": comment,
			"instalivit_comment_rate": rate
		},
		success: function(data){
			if (data){
				if (data.length > 0){
					var innerHTML = '';
					for (var i in data){
						innerHTML += '<div class="instalivit-comments-item"><img src="<?php echo plugins_url('images/rate/', __FILE__); ?>' + data[i].rate + '.png" style="width:30px;" />&nbsp;&nbsp;' + data[i].comment.replace(/\\(.)/mg, "$1") + '</div>'
					}
					document.getElementById("instalivit-comments-container-" + id).innerHTML = innerHTML;
				}
				else{
					document.getElementById("instalivit-comments-container-" + id).innerHTML = '<div class="instalivit-comments-item">Still No Comment...</div>';
				}
				
				document.getElementById("instalivit-write-" + id).value = "";
				document.getElementById("instalivit-write-" + id).focus();
				
				setTimeout(function(){
					jQuery("#instalivit-item-" + id).animate({height: (310 + document.getElementById("instalivit-comments-" + id).offsetHeight) + "px"}, 100, function(){
						jQuery(".isotope").isotope();
					});								
				}, 100);
			}
		}
	});
};

</script>
