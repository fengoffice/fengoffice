og.reportsFiltersData = {
	temporal_selection:null,
	count_choosen:0,
	listeners_called:0
};

og.loadFiltersReport = function(report_id, skip_member = false){
	var temporal_selection = JSON.parse(localStorage.getItem('preload_filters_'+report_id));
	og.reportsFiltersData.temporal_selection = temporal_selection;
	og.reportsFiltersData.listeners_called = 0;

	var total_preload_col = 0;


	setTimeout(function(){
		total_preload_col = $(".cols_doble_contol select").eq(1).find('option').length;
		var count_choosen = 0;

		$(temporal_selection).each(function(v,j){
			var current_element = $('[name="'+j.name+'"]');
			var type = '';
			var tag_name = '';

			try{
				var tag_name = $(current_element).get(0).tagName;
				var type = $(current_element).get(0).type;

			}catch(e){}

			if(j.custom == 'choosen'){
				if(!skip_member){
					var obejct = $('.feng-dimention-selector').eq(count_choosen);
					$(obejct).find('input').focus();
					$(obejct).find('input').focusout();
          setTimeout(function(){ $('.submit').focus();}, 1000);
          
					count_choosen++;
					og.reportsFiltersData.count_choosen = count_choosen;
				}
			}else if(j.custom == 'combo'){
				if(comboBoxes != "undefined" && Object.keys(comboBoxes).length === 0) {
					tsContactCombo.setValue(j.values);
				} else {
					var combo = comboBoxes[j.name];
					combo.setValue(j.values);
				}
			}else if(j.custom == 'second_col'){
				$(j.values).each(function(c,v){
					if(c > total_preload_col){
						$(".cols_doble_contol").find('select:first').find('option[value="'+v+'"]').prop('selected','selected');
						$('.ico-arrowright').click();
					}
				})
			}else if(tag_name == 'INPUT' && type == 'checkbox'){
				var check_value = $('[name="'+j.name+'"][value="'+j.value+'"]');
				$(check_value).prop('checked', 'checked');
			}else if(tag_name == 'INPUT' && type == 'radio'){
				var check_value = $('[name="'+j.name+'"][value="'+j.value+'"]');
				$(check_value).prop('checked', 'checked');
			}else if(tag_name == 'INPUT'){
				$(current_element).val(j.value);
			}else if(tag_name == 'SELECT'){
				if(j.value != 0){
					var current_select = $("option[value='"+j.value+"']", current_element);
						current_select.prop('selected', true);
						current_select.change();
				}
			}
		})
	}, 500);
}

og.setMemoryFilttersReport = function(object, report_id){
    var form = $(object).closest("form");

    var information = [];
	var standar_controls = $('select,input',form).serializeArray();
	var select_obj = $(".cols_doble_contol select").eq(1);
	var values_selected = {"name":$(select_obj).attr('name'),"custom":"second_col","values":[]};

	// Read doble cols component
	$(select_obj).find('option').each(function(){
		values_selected.values.push($(this).val());
	})

	// Read combobox ExtJS
	$('.x-form-field-wrap').each(function(k,o){
		var have_combo = $(o).find('.assigned-to-combo');
		if(have_combo.length){
			var value = $(o).find('[type="hidden"]:first').val();
			var name = $(o).find('[type="hidden"]:first').prop("name");

			information.push({"name":name,"custom":"combo","values":value});
		}
	})

	// Read choosen
	$('.feng-dimention-selector').each(function(k,o){
		var value = $(o).find('input:hidden:first').val();
		var text = $(o).find('.member-path').find('span').attr('title');

		information.push({"name":"","custom":"choosen","values":value,"text":text});
	})

	// Read standar controls
	$(standar_controls).each(function(k,o){
		var current_object = $("[name='"+o.name+"']");
		var is_hidden = $(current_object).prop('type');
		var is_text_filter = $(current_object).hasClass('dimension-panel-textfilter');

		if(is_hidden != 'hidden' && !is_text_filter){
			information.push({"name":o.name,"custom":"","value":o.value});
		}
	})

	information.push(values_selected);
	localStorage.setItem('preload_filters_'+report_id, JSON.stringify(information));
}

// Set values for type choosen 
og.eventManager.addListener('end_callback member_tree loaded', reportLoadFilttersValues);
og.eventManager.addListener('cache member_tree loaded', reportLoadFilttersValues);

function reportLoadFilttersValues(params){
	// only in latest fire

	var total_choosen = 0;
	$(og.reportsFiltersData.temporal_selection).each(function(v,j){
		if(j.custom == 'choosen'){total_choosen++;}
	})

	if(total_choosen == (og.reportsFiltersData.listeners_called + 1)){
		setTimeout(function(){
			var current_choosen = 0;
			$(og.reportsFiltersData.temporal_selection).each(function(v,j){
				if(j.custom == 'choosen'){
					var genid = $('.feng-dimention-selector').eq(current_choosen).attr('data-generic-id');

					member_selector.set_selected(genid, JSON.parse(j.values), null);
					current_choosen++;
				}
			});
		},2000);
	}

	og.reportsFiltersData.listeners_called++;
}