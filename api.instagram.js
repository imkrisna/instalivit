var IM_INSTALIVIT_CLIENT_ID = "3534061e76fc4c52b7b3ec28c605f49a";

function im_instalivit_getUserId(usernames, index, callback, ids){
	if (!ids) ids = [];
	
	jQuery.ajax({
		url		: "https://api.instagram.com/v1/users/search?q=" + usernames[index] + "&client_id=" + IM_INSTALIVIT_CLIENT_ID,
		type	: "GET",
		dataType: "jsonp",
		success	: function(response){
			var id = null;
			if (response.data){
				for(var i in response.data){
					if (response.data[i].username === usernames[index]){
						id = response.data[i].id;
						break;
					}
				}
			}
			ids.push(id);
			
			if (index < usernames.length - 1){
				im_instalivit_getUserId(usernames, index + 1, callback, ids);
			}
			else{
				callback(ids);
			}
		}
	});
}

function im_instalivit_getUserMedia(userids, index, tags, callback, medias){
	if (!medias) medias = [];
	
	jQuery.ajax({
		url		: "https://api.instagram.com/v1/users/" + userids[index] + "/media/recent" + "?client_id=" + IM_INSTALIVIT_CLIENT_ID,
		type	: "GET",
		dataType: "jsonp",
		success	: function(response){
			if (response.data){
				for(var i in response.data){
					medias.push({
						id		: response.data[i].id,
						src		: response.data[i].images.standard_resolution.url,
						text	: response.data[i].caption.text
					});
				}
			}
			
			if (index < userids.length - 1){
				im_instalivit_getUserMedia(userids, index + 1, tags, callback, medias);
			}
			else{
				callback(medias);
			}
		}
	});
}