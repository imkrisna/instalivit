function im_instalivit_expand(item){	
	if (jQuery("#instalivit-item-" + item).height() > 300){
		jQuery("#instalivit-item-" + item).animate({height:"300px"}, 500, function(){
			jQuery(".isotope").isotope();
		});
	}
	else{	
		jQuery(".instalivit-item").css({height:"300px"});
		jQuery("#instalivit-item-" + item).css({height:"auto"});
		jQuery(".isotope").isotope();
		jQuery("#instalivit-item-" + item).css({height:"300px"});
		jQuery("#instalivit-item-" + item).animate({height: (310 + document.getElementById("instalivit-comments-" + item).offsetHeight) + "px"}, 500);	
	}
};