<?php
	global $wpdb;
	
	$im_instalivit_default 	= ini_get('max_execution_time');
	$im_instalivit_dbname	= $wpdb->prefix . "instalivit";
	set_time_limit(60 * 5);

	if (isset($_GET["id"]) && isset($_POST["instalivit_comment_text"]) && $_POST["instalivit_comment_text"] != ""){
		$wpdb->insert($im_instalivit_dbname, array(
			'id'		=> $_GET["id"],
			'rate'		=> $_POST["instalivit_comment_rate"],
			'comment'	=> $_POST["instalivit_comment_text"],
			'timestamp'	=> time()
		), array(
			'%s',
			'%d',
			'%s',
			'%s'
		));
	}
	
	if (isset($_GET["ajax"]) && $_GET["ajax"] == "true"){
		$id = $_GET["id"];
		wp_send_json($wpdb->get_results("SELECT * FROM $im_instalivit_dbname WHERE id='$id' ORDER BY timestamp DESC LIMIT 0,3", ARRAY_A));
		exit;
	}
	
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
	
	$r_get = wp_remote_get("https://api.instagram.com/v1/media/" . $_GET["id"] . "?" . $im_instalivit_AuthData, array(
		'sslverify'	=> false,
		'timeout' => 30
	));
	
	if (is_array($r_get)){
		$r_object = json_decode($r_get["body"]);
		if (isset($r_object->meta) && isset($r_object->meta->error_message)){
			echo $r_object->meta->error_message . '<br />';
			continue;
		}
		
		$data = $r_object->data;				
		if ($data->type != "image") continue;
		
		$content = array(
			'id'		=> $data->id,
			'src'		=> $data->images->standard_resolution->url,
			'src_l'		=> $data->images->low_resolution->url,
			'text'		=> stripslashes($data->caption->text),
			'comment'	=> $wpdb->get_results("SELECT * FROM $im_instalivit_dbname WHERE id='$data->id' ORDER BY timestamp DESC", ARRAY_A)
		);	
	}
	else echo "[Network Error] Cannot Load Instagram Content (".$uid.")<br />";
	
	set_time_limit($im_instalivit_default);
?>

<div class="wrap">
	<img src="<?php echo $content["src"]; ?>" style="width:100%;max-width:600px;text-align:center" />
	<div><?php echo $content["text"]; ?></div>
	
	<h4>Say Something...</h4>
	<form method="POST" action="#">
		<img id="im_instalivit_rate1" src="<?php echo plugins_url('images/rate/1.png', __FILE__); ?>" style="cursor:pointer;width:40px;border:2px solid transparent" onclick="im_instalivit_rate(1)" />
		<img id="im_instalivit_rate2" src="<?php echo plugins_url('images/rate/2.png', __FILE__); ?>" style="cursor:pointer;width:40px;border:2px solid transparent" onclick="im_instalivit_rate(2)" />
		<img id="im_instalivit_rate3" src="<?php echo plugins_url('images/rate/3.png', __FILE__); ?>" style="cursor:pointer;width:40px;border:2px dashed black" onclick="im_instalivit_rate(3)" />
		<img id="im_instalivit_rate4" src="<?php echo plugins_url('images/rate/4.png', __FILE__); ?>" style="cursor:pointer;width:40px;border:2px solid transparent" onclick="im_instalivit_rate(4)" />
		<img id="im_instalivit_rate5" src="<?php echo plugins_url('images/rate/5.png', __FILE__); ?>" style="cursor:pointer;width:40px;border:2px solid transparent" onclick="im_instalivit_rate(5)" />
		<input id="instalivit_comment_rate" name="instalivit_comment_rate" type="hidden" value="3" />
		<input name="instalivit_comment_text" type="text" style="width:100%;margin-top:10px" />
		<input type="submit" class="button button-primary" style="width:100%;margin-top:10px;" />
	</form>
	<br />
	<?php
	foreach ($content["comment"] as $comment){
		?>
		<div style="background:rgba(255,255,255,0.7);box-shadow:0px 0px 10px;padding:10px;margin-top:10px;">
			<?php 
			if ($comment["rate"] != -1){
				echo "<img src='". plugins_url('images/rate/' . $comment["rate"] . ".png", __FILE__) ."' style='width:40px' />&nbsp;&nbsp;&nbsp;";
			}
			echo stripslashes($comment["comment"]); 
			?>
		</div>
		<?php
	}
	?>
</div>

<script>
function im_instalivit_rate(rate){
	for (var i=1;i<=5;i++){
		if (i == rate){
			document.getElementById("im_instalivit_rate" + i).style.border = "2px dashed black";
		}
		else{
			document.getElementById("im_instalivit_rate" + i).style.border = "2px dashed transparent";
		}
	}
	document.getElementById("instalivit_comment_rate").value = "" + rate;
}
</script>