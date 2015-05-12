<?php
	$clientId 	= get_option("im_instalivit_clientid");
	$token		= get_option("im_instalivit_accesstoken");
	
	if (!$clientId){
		add_option("im_instalivit_clientid");
		add_option("im_instalivit_accesstoken");
	}
	
	if (isset($_POST["im_instalivit_clientid"])){
		update_option("im_instalivit_clientid", $_POST["im_instalivit_clientid"]);
		$clientId = get_option("im_instalivit_clientid");
	}
	
	if (isset($_POST["im_instalivit_accesstoken"])){
		update_option("im_instalivit_accesstoken", $_POST["im_instalivit_accesstoken"]);
		$token = get_option("im_instalivit_accesstoken");
	}
?>

<div class="wrap">
	<form id="im_instalivit_form" method="POST" action="#">
		<h2>InstaLivit Settings</h2>
		<br />
		
		<h3>Instagram Client ID</h3>
		<input id="im_instalivit_clientid" name="im_instalivit_clientid" style="width:95%" type="text" value="<?php echo $clientId; ?>" />
		<p style="font-style:italic;width:95%">
			Find or Create your Instagram Client ID at <a href="https://instagram.com/developer" target="_blank">Instagram Developer</a> page
			and uncheck the <strong>Disable implicit OAuth</strong> option on Security tab.<br />
			Enter <strong><?php echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; ?></strong> as Redirect URI
		</p>
		
		<br />
		<h3>Instagram Access Token</h3>
		<input type="button" class="button" value="Get" style="width:15%" onclick="im_instalivit_oauth()" />
		<input id="im_instalivit_accesstoken" name="im_instalivit_accesstoken" style="width:80%" type="text" value="<?php echo $token; ?>" readonly />
		<p style="font-style:italic;width:95%">
			Please enter a valid Client ID before requesting Access Token.
			<a href="javascript:" onclick="im_instalivit_removetoken()">Click Here</a> to Remove Access Token
		</p>
		
		
		<br /><br />
		<input id="im_instalivit_submit" type="submit" class="button button-primary" value="Save Settings" />	
	</form>
</div>

<script>
	function im_instalivit_oauth(){
		var clientId = document.getElementById("im_instalivit_clientid").value;
		
		if (clientId){
			window.location = "https://instagram.com/oauth/authorize/?client_id=" + clientId + "&redirect_uri=" + window.location + "&response_type=token";
		}
		else{
			alert("Please enter a valid Instagram Client ID");
		}
	}
	
	function im_instalivit_removetoken(){
		document.getElementById("im_instalivit_accesstoken").value = "";
		document.getElementById("im_instalivit_form").submit();
	}
	
	if (window.location.href.indexOf("#access_token=") > -1){
		var accessToken = window.location.href.substring(window.location.href.indexOf("#access_token=") + 14);
		document.getElementById("im_instalivit_accesstoken").value = accessToken;
		
		document.getElementById("im_instalivit_form").submit();
	}
</script>
