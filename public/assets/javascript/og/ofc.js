ofc_chart = {
	image_binary: function(src) {
		swf_cont = document.getElementById(src);
		swf = null;
		for (i=0; i<swf_cont.childNodes.length; i++) {
			child = swf_cont.childNodes.item(i);
			if (child.id == 'chart' || child.id == 'ie_chart') { swf = child; break; }
		}
		return swf.get_img_binary();
	},
	
	image_tag: function(src) {
		return "<img src='data:image/png;base64," + ofc_chart.image_binary(src) + "' />";
	},
	
  	post_image_from_swf: function(genid, filename) {

  		var form = document.createElement('form');
  		form.method = 'post';
  		form.enctype = 'multipart/form-data';
  		form.encoding = 'multipart/form-data';
  		form.action = og.getUrl('report', 'upload_image');
  		form.style.display = 'none';

  		i_name = document.createElement("input");
  		i_name.type = 'hidden';
  		i_name.name = 'filename';
  		i_name.value = genid + filename;
  		form.appendChild(i_name);
  		
  		i_data = document.createElement("input");
  		i_data.type = 'hidden';
  		i_data.name = 'imgdata';
  		i_data.value = ofc_chart.image_binary(genid);
  		form.appendChild(i_data);
  		
  		document.body.appendChild(form);

  		og.submit(form, {
  	  		hideLoading: true,
  			callback: function() {
  				form.removeChild(input);
  				document.body.removeChild(form);
  			}
  		});
  	}
};