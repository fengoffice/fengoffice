<?php
$genid = gen_id();
if (isset($error_msg)) {
	echo "<div class='quick-form-error'>$error_msg</div>";
	return;
}
?>

<form action="<?php echo $form_action ?>" method="post" >
	<h2 class="quick-form-title"><?php echo (!empty($object_type_name)) ? lang("new ".$object_type_name) : "" ?></h2>
	<label for="member-name" ><?php echo lang("name")?>:</label>
	<input type="text" required="required" autofocus id="member-name" name="member[name]" /> 
	<input type="hidden" id="dim_id" name="member[dimension_id]" value = "<?php echo $dimension_id ?>" />
	<input type="hidden" id="parent_id" name="member[parent_member_id]" value = "<?php echo $parent_member_id ?>" />
<?php if (!empty($object_type)):?>
	<input type="hidden" id="object_type_id" name="member[object_type_id]" value = "<?php echo $object_type->getObjectTypeId() ?>" />
<?php else: ?>
	<div class="field" >
		<label><?php echo lang('type') ?>:</label>
		<select name="member[object_type_id]" class="quick-add-object-type" >
			<?php foreach ($object_types as $dot): /* @var $dot DimensionObjectType */ ?>
			<option  value="<?php echo $dot->getObjectTypeId()?>" name="<?php echo $dot->getObjectType()->getName()?>" >
				<?php echo lang($dot->getObjectType()->getName()) ?>
			</option>
			<?php endforeach;?>
		</select>
	</div>
<?php endif;?>
	<div class="extra-fields" >
		<?php Hook::fire("quickadd_extra_fields", array('dimension_id' => $dimension_id, 'parent_id' => $parent_member_id), $ret)?>
	</div>

	<div class="action">
		<input type="submit" class="submit" value="<?php echo lang("save")?>" />
		<?php foreach ($editUrls as $k => $url) : ?>
			<a onclick="return false;" class="object-type-<?php echo $k ?> more coViewAction ico-edit" href="<?php echo $url ?>"><?php echo lang ('details') ?></a>
		<?php endforeach;?>
	</div>
</form>

<script>
	$( function() {
		
            // To make ajax submit:
            og.captureLinks("quick-form");

            // Auto focus member name:
            $("#quick-form #member-name").focus();

            // Show only one "more link" 
            $(".more").hide().eq(0).show();

            // Set form title based on combo if it has no title
            if (!$(".quick-form-title").html()){
                    var type =  $(".quick-add-object-type option:selected").attr('name') ;
                    if (type){	
                            $(".quick-form-title").html(lang('new '+type));
                    }else{
                            $(".quick-form-title").html(lang('new'));
                    }		
            }

            // Handle "more" link click
            $("#quick-form .more").click(function(){
                    $("#quick-form").slideUp();
                    var title = $("#member-name").val();
                    var parent = $("#parent_id").val();

                    var url = og.makeAjaxUrl($(this).attr('href')+"&name="+encodeURIComponent(title));
                    if (parent) {
                            url += "&parent="+parent;
                    }		
                    og.openLink(url);		
            });


            // After sumbmit hide form 
            $("#quick-form form").submit(function(a){
                    $("#quick-form").slideUp();
            });

            // Fire submit on 'enter'
            $('#quick-form #member-name').keypress(function(e){
                    if(e.which == 13){
                            $('#quick-form .submit').click();
                            e.preventDefault();
                            return false ;
                    }
	    });

	    // Select Handler 
            $('.quick-add-object-type').change(function () {
                    var otId = $(this).find("option:selected").val() ;
                    var type = $(this).find("option:selected").attr('name');
                    if (otId){ 
                            $(".more").hide();
                            $(".more" + ".object-type-"+otId).show();
                            $(".quick-form-title").html(lang('new '+type));
                    }

            });

            og.eventManager.fireEvent("after quickadd render");	    
				
	});
</script>