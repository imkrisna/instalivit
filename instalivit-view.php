<div class="wrap">
	<div id="instalivit" class="isotope">
	</div>
</div>
<script>
jQuery(document).ready(function($) {
  
	im_instalivit_getUserId(["indtravel", "travelingindonesia"], 0, function(ids){
		im_instalivit_getUserMedia(ids, 0, null, function(images){
			var inflateHtml = "";
			for (var i in images){
				inflateHtml += "<div class='instalivit-item'><img src='" + images[i].src + "' style='width:100%' /></div>";
			}
			document.getElementById("instalivit").innerHTML = inflateHtml;
			
			setTimeout(function(){
				$('.isotope').isotope({
					itemSelector: '.instalivit-item',
					masonry: {
						gutter: 10
					}
				});
			}, 100);			
		});
	});

});
</script>
