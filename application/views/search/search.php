<?php
require_javascript("og/DateField.js");
$genid = gen_id();
?>

<?php
$selected_option = null;
$options = array();
foreach ($object_types as $type) {
	if ($type_object == $type[0]) {
		$selected = 'selected="selected"';
		$selected_option = $type[1];
	} else {
		$selected = '';
	}
	$options[] = '<option value="'.$type[0].'" '.$selected.'>'.$type[1].'</option>';
}

$context_menu_style = "font-weight:bold";
$context_div_display ="display:none;";
$strDisabled = count($options) > 1 ? '' : 'disabled';
$disabled = $type_object ? false : true;
?>
<form style='height: 100%; background-color: white' class="internalForm" action="<?php echo get_url('search', 'search')  ?>" method="post">
<input type="hidden" name="search[search_object_type_id]" id="search[search_object_type_id]" value="<?php echo $type_object?>" />
<input type="hidden" name="search[text]" id="search[text]" value="<?php echo $search_string?>" />
<input type="hidden" name="advanced" id="advanced" value="<?php echo $advanced?>" />

<div id="headerDiv" class="searchDescription">
<?php 
        if ( current_member_search() && $search_dimension != 0){            
            $context = '';
            foreach (current_member_search() as $context_){
                 $context .= $context_->getName() . ", ";
            }
            echo lang("search for in project", clean($search_string), substr($context, 0, -2));
        }else{
            echo lang("search for", clean($search_string));
        }
	if ( current_member_search() && $search_dimension != 0) { ?>
	<br/><a class="internalLink" href="<?php echo get_url('search','search',array("search_for" => array_var($_GET, 'search_for'), "search_dimension" => "0", "advanced" => $advanced)) ?>"><?php echo lang('search in all workspaces') ?></a>
        <?php } ?>
</div>
<div id="<?php echo $genid; ?>Search" class="search-container">
        <div>
            <?php echo label_tag(lang('object type'), $genid . 'searchFormObjectType', true) ?>
            <?php echo select_box('objectTypeSelSearch', $options, array('id' => 'objectTypeSelSearch' ,'onchange' => 'og.searchObjectTypeChanged("'.$genid.'", "", 1, "")', 'style' => 'width:200px;', $strDisabled => '', 'tabindex' => '10')) ?>

            <fieldset><legend><?php echo lang('conditions') ?></legend>
                    <div id="<?php echo $genid ?>"></div>
                    <div style="margin-top:10px;">
                            <a href="#" class="link-ico ico-add" onclick="og.addConditionSearch('<?php echo $genid ?>', 0, 0, '', '', '')"><?php echo lang('add condition')?></a>
                    </div>
            </fieldset>            
            <?php 
            if($disabled){
                echo button(lang('search'), 's', array('id' => 'buttonSubmit', 'tabindex' => '20000', "disabled" => "true"));
            }else{
                echo button(lang('search'), 's', array('id' => 'buttonSubmit', 'tabindex' => '20000'));
            }
            echo submit_button(lang('search'), 's', array('id' => 'realButtonSubmit', 'tabindex' => '20000', "style" => "display:none"));
            ?>
            <div style="margin-bottom: 15px;"></div>
        </div>
    <?php if (count($search_results) > 0){?>
	<div class="search-summary" >
<!--		<p class="results-for"><?php echo lang("search results for") ?>: <em>'<?php echo (isset ($search_string)) ? $search_string : '' ?>'</em> </p>-->
		<?php if ( !empty($extra) && isset($extra->time) ):?>
			<p>Search speed: <?php echo $extra->time ?>s</p>
		<?php endif ;?>
	</div>

	<div class="search-results">
		<?php 
			foreach ($search_results AS $result){ 
				tpl_assign("result", $result);
				$this->includeTemplate(get_template_path('result_item', 'search'));

			}
		?>
	</div>
	<div class="search-footer">
		<div class="pagination">
			<?php $this->includeTemplate(get_template_path('pagination', 'search'));?>

		</div>
	</div>
    <?php }else{?>
        <?php if($msg_advanced){?>
        <div id="<?php echo $genid; ?>Search" class="search-container">
                <div class="no-results">
                        <?php echo lang("no search result");?>
                </div>
        </div>
        <?php }?>
    <?php }?>
</div>
</form>
<script>
    
    
    $(document).ready(function() {
        $('#objectTypeSelSearch').change(function() {
            if($(this).val() != ""){
                $('#buttonSubmit').attr("disabled",false)
            }else{
                $('#buttonSubmit').attr("disabled",true)
            }
        });
        $('#buttonSubmit').click(function() {
			$('#search[text]').val($('#search_for_in').val());
			$('#buttonSubmit').prop("disabled",true);
        	$('#realButtonSubmit').click();
        });
    });
    
var modifiedSearch = false;
var selectedObjTypeIndexSearch = -1;
var fieldValuesSearch = {};

og.addConditionSearch = function(genid, id, cpId, fieldName, condition, value){

	var get_object_fields = 'get_object_fields';
	var type = document.getElementById('search[search_object_type_id]').value;
	if(type == ""){
		alert(lang('object type not selected'));
		return;
	}

	var condDiv = Ext.getDom(genid);
	var count = condDiv.childNodes.length;
	var classname = "";
	if (count % 2 != 0) {
		classname = "odd";
	}
	var style = 'style="width:130px;padding-right:10px;"';
	var table = '<table><tr>' +
	'<td ' + style + ' id="tdFields' + count + '"></td>' +
	'<td ' + style + ' id="tdConditions' + count + '"></td>' +
	'<td ' + style + ' id="tdValue' + count + '"><b>' + lang('value') + '</b>:<br/>' +
	'<input type="text" style="width:100px;" id="conditions[' + count + '][value]" name="conditions[' + count + '][value]" name="conditions[' + count + '][value]" value="{1}" ></td>';	

	table = table +'<td style="padding-left:20px;"><div class="clico ico-delete" onclick="og.deleteConditionSearch(' + count + ',\'' + genid + '\')"></div></td>' +
  	'</tr></table>';

	var newConditionSearch = document.createElement('div');
	newConditionSearch.id = "Condition" + count;
	newConditionSearch.style.padding = "5px";
	newConditionSearch.className = classname;
	newConditionSearch.innerHTML = table;
	condDiv.appendChild(newConditionSearch);
	og.openLink(og.getUrl('search', get_object_fields, {object_type: type}), {
		callback: function(success, data) {
			if (success) {
				var fields = '<span class="bold">' + lang('field') +
				'</span>:<br/><select class="searchConditionDD" onchange="og.fieldChangedSearch(' + count + ', \'\', \'\')" id="conditions[' + count + '][custom_property_id]" name="conditions[' + count + '][custom_property_id]">';					
				for(var i=0; i < data.fields.length; i++){
					var field = data.fields[i];
                                        var sel = '';
					if(field.id == cpId) {sel = "selected"};
					fields += '<option value="' + field.id + '" class="' + field.type + '" ' + sel + '>' + og.clean(field.name) + '</option>';
					if(field.values){
						if(!fieldValuesSearch[count]){
							fieldValuesSearch[count] = {};
						}
						if(id == 0){
							fieldValuesSearch[count][i] = field.values;
						}else{
							fieldValuesSearch[count][0] = field.values;
						}
					}						
				}
				fields += '</select>';	
				if(cpId > 0){
					fields += '<input type="hidden" name="conditions[' + count + '][custom_property_id]" value="' + cpId + '">';
				}
				document.getElementById('tdFields' + count).innerHTML = fields;
				og.fieldChangedSearch(count, (condition != "" ? condition : ""), (value != "" ? value : ""));

				og.eventManager.fireEvent('replace all empty breadcrumb', null);				
			}
		},
		scope: this
	});
	if(id == 0){
		modifiedSearch = true;
	}
};

og.deleteConditionSearch = function(id, genid){
        
        Ext.get('Condition' + id).remove();
};

og.fieldChangedSearch = function(id, condition, value){
	var fields = document.getElementById('conditions[' + id + '][custom_property_id]');
	var selField = fields.selectedIndex;
	if(selField != -1){
		var fieldType = fields[selField].className;
		var type_and_name = '<input type="hidden" name="conditions[' + id + '][field_name]" value="' + fields[selField].value + '"/>' +
		'<input type="hidden" name="conditions[' + id + '][field_type]" value="' + fieldType + '"/>'; 
		var conditions = '<b>' + lang('condition') + '</b>:<br/><select class="searchConditionDD" id="conditions[' + id + '][condition]" name="conditions[' + id + '][condition]">';
		var textValueField = '<b>' + lang('value') + '</b>:<br/><input type="text" style="width:100px;" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]" value="' + value + '"/>' + type_and_name;
		var dateValueField = '<b>' + lang('value') + '</b>:<br/>' + '<span id="containerConditions[' + id + '][value]"></span>' + type_and_name; 
		var wsValueField = '<b>' + lang('value') + '</b>:<br/>' + '<span id="containerConditions[' + id + '][value]"></span>' + type_and_name;
		var tagValueField = '<b>' + lang('value') + '</b>:<br/>' + '<div class="og-csvcombo-container"><input type="text" style="width:100px;" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]" value="' + value + '"/></div>' + type_and_name;
		
		if(fieldType == "text" || fieldType == "memo"){
			document.getElementById('tdValue' + id).innerHTML = textValueField;
			conditions += '<option value="start with">' + lang('start with') + '</option>';
			conditions += '<option value="like">' + lang('like') + '</option>';
			conditions += '<option value="not like">' + lang('not like') + '</option>';
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '<option value="<>">' + lang('not equals') + '</option>';
			conditions += '<option value="ends with">' + lang('ends with') + '</option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}else if(fieldType == "numeric"){
			document.getElementById('tdValue' + id).innerHTML = textValueField;
			conditions += '<option value=">">&gt;</option>';
			conditions += '<option value=">=">&ge;</option>';
			conditions += '<option value="<">&lt;</option>';
			conditions += '<option value="<=">&le;</option>';
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}else if(fieldType == "boolean"){
			var values = '<b>' + lang('value') + '</b>:<br/><select class="searchConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			values += '<option value="1" ' + (value != "" && value == true ? "selected" : "") + '>' + lang('true') + '</option>';
			values += '<option value="0"' + (value == false ? "selected" : "") + '>' + lang('false') + '</option>';
			values += '</select>' + type_and_name;
			document.getElementById('tdValue' + id).innerHTML = values;
			conditions += '<option value="=">' + lang('equals') + '</option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		} else if(fieldType == "date"){
			document.getElementById('tdValue' + id).innerHTML = dateValueField;
			conditions += '<option value=">">&gt;</option>';
			conditions += '<option value=">=">&ge;</option>';
			conditions += '<option value="<">&lt;</option>';
			conditions += '<option value="<=">&le;</option>';
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
			
			var dateCond = new og.DateField({
				renderTo:'containerConditions[' + id + '][value]',
				name: 'conditions[' + id + '][value]',
				id: 'conditions[' + id + '][value]',
				value: Ext.util.Format.date(value, og.preferences['date_format'])
			});
		}else if(fieldType == "list"){
			var valuesList = fieldValuesSearch[id][selField].split(',');
			var listValueField = '<b>' + lang('value') + '</b>:<br/><select class="searchConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
			for(var i=0; i < valuesList.length; i++){
				listValueField += '<option ' + (valuesList[i] == value ? "selected" : "") + '>' + valuesList[i] + '</option>';
			}
			listValueField += '</select>' + type_and_name; 
			document.getElementById('tdValue' + id).innerHTML = listValueField;
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}else if(fieldType == "external"){
			if(fields[selField].value == 'workspace'){
				document.getElementById('tdValue' + id).innerHTML = wsValueField;
				og.drawWorkspaceSelector('containerConditions[' + id + '][value]', value, 'conditions[' + id + '][value]', true, '');
			}else if(fields[selField].value == 'tag'){
				document.getElementById('tdValue' + id).innerHTML = tagValueField;
				
				var arr = [];
				for (var i=0; i < tags.length; i++) {
					arr.push([tags[i].name, og.clean(tags[i].name)]);
				}
				var tagSel = new og.CSVCombo({
					store: new Ext.data.SimpleStore({
		        		fields: ["value", "clean"],
		        		data: arr
					}),
					valueField: "value",
					displayField: "value",
					mode: "local",
					forceSelection: true,
					tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{clean}</div></tpl>",
					emptyText: "",
					applyTo: "conditions[" + id + "][value]"
		    	});			
			}else{
				var objectTypeSelSearch = document.getElementById('objectTypeSelSearch');
				og.openLink(og.getUrl('search', 'get_external_field_values', {external_field: fields[selField].value, report_type: objectTypeSelSearch[objectTypeSelSearch.selectedIndex].value}), {
					callback: function(success, data) {
						if (success) {
							var externalValueField = '<b>' + lang('value') + '</b>:<br/><select class="searchConditionDD" id="conditions[' + id + '][value]" name="conditions[' + id + '][value]">';
							for(var j=0; j < data.values.length; j++){
								var extValue = data.values[j];
								externalValueField += '<option value="' + extValue.id + '" ' + (extValue.id == value ? "selected" : "") + '>' + extValue.name + '</option>';
							}
							externalValueField += '</select>' + type_and_name; 
							document.getElementById('tdValue' + id).innerHTML = externalValueField;
							
							if(condition != ""){
								var valueField = document.getElementById('conditions[' + id + '][value]');
								valueField.disabled = parametrizable;
							}
						}
					}
				});
			}
			conditions += '<option value="=">=</option>';
			conditions += '<option value="<>"><></option>';
			conditions += '</select>';
			document.getElementById('tdConditions' + id).innerHTML = conditions;
		}
		
		var conditionSel = document.getElementById('conditions[' + id + '][condition]');
		for(var j=0; j < conditionSel.options.length; j++){ 
			if(conditionSel.options[j].value == condition){
				conditionSel.selectedIndex = j;
			}
		}
		if(condition == "") {
			var isparam_el = document.getElementById('conditions[' + id + '][is_parametrizable]');
			if (isparam_el) isparam_el.checked = false;
			modifiedSearch = true;
		}else{
			var isparam_el = document.getElementById('conditions[' + id + '][is_parametrizable]');
			var parametrizable = isparam_el && isparam_el.checked;
			var valueField = document.getElementById('conditions[' + id + '][value]');
			if(valueField){
				valueField.disabled = parametrizable;
			}
		}
	}
};

og.searchObjectTypeChanged = function(genid, order_by, order_by_asc, cols){
	var objectTypeSelSearch = document.getElementById('objectTypeSelSearch');
	if(modifiedSearch){
		if(!confirm(lang('confirm discard changes'))){
			objectTypeSelSearch.selectedIndex = selectedObjTypeIndexSearch;
			return;
		}
	}
	modifiedSearch = false;
	selectedObjTypeIndexSearch = objectTypeSelSearch.selectedIndex;
	if(selectedObjTypeIndexSearch != -1){
		var conditionsDiv = Ext.getDom(genid);
		while(conditionsDiv.firstChild){
			conditionsDiv.removeChild(conditionsDiv.firstChild);
		}
		var type = objectTypeSelSearch[selectedObjTypeIndexSearch].value;
		
		document.getElementById('search[search_object_type_id]').value = type;

		var column_list_container = Ext.get(genid+'columnListContainer');
		if (column_list_container) {
			column_list_container.load({
				url: og.getUrl('search', 'get_object_column_list_task', {object_type: type, columns:cols, orderby:order_by, orderbyasc:order_by_asc, genid:genid}),
				scripts: true
			});
		}
	}
};

<?php if(isset($conditions)){ ?>
        <?php foreach($conditions as $condition){ ?>
            og.addConditionSearch('<?php echo $genid?>','<?php echo $condition['id'] ?>', '<?php echo $condition['custom_property_id'] ?>' , '<?php echo $condition['field_name'] ?>', '<?php echo $condition['condition'] ?>', '<?php echo $condition['value'] ?>');		
        <?php 
        }//foreach ?>
<?php }//if ?>
</script>