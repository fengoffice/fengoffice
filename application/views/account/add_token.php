<?php 
	$genid = gen_id();	
	$on_submit = "og.submit_modal_form('".$genid."generate_token_form'); return false;";
	
?>
<div class="adminConfiguration" style="height:100%;background-color:white">
	<div class="coInputHeader">
        <div>
        	<div class="coInputName">
        		<div class="coInputTitle">
        		  	<?php echo lang('edit external tokens'); ?>
        		</div>
        	</div>
        	<div class="clear"></div>
        </div>
	</div>
  	<div class="adminMainBlock">
  
        <form class="internalForm" onsubmit="<?php echo $on_submit?>" id="<?php echo $genid ?>generate_token_form" action="<?php echo $user->getEditExternalTokensUrl() ?>" method="post">
        
          <?php tpl_display(get_template_path('form_errors'));
          		$first_tab = 1; 
          ?>
          
          <?php if (isset($action) && $action == 'edit'): ?>
          	  <input type="hidden" name="token_data[token_id]" value="<?php echo $token->getId() ?>" />
          	  <input type="hidden" name="token_data[action]" value="edit" />
              <div>
                <?php echo label_tag(lang('token title'), 'tokenFormExternalToken') ?>
                <?php echo text_field('token_data[external_token]',$token->getToken(), array('tabindex' => $first_tab,'disabled' => true, 'id'=>$genid."token_input")) ?>
               	<a onclick="og.resetUserExternalToken()" class="link-ico ico-recurrent" href="#">&nbsp;</a>
                             
              </div>
              
              <div>
                <?php echo label_tag(lang('token external key'), 'tokenFormExternalKey') ?>
                <?php echo text_field('token_data[external_key]', $token->getExternalKey(), array('tabindex' => $first_tab)) ?>
              </div>
              
              <div>
                <?php echo label_tag(lang('token external name'), 'tokenFormExternalName') ?>
                <?php echo text_field('token_data[external_name]', $token->getExternalName(), array('tabindex' => $first_tab + 100)) ?>
              </div>
              
              <div>
                <?php echo label_tag(lang('token external type'), 'tokenFormExternalType') ?>
                <?php echo text_field('token_data[type]', $token->getType(), array('tabindex' => $first_tab + 200)) ?>
              </div>
    
              <?php echo submit_button(lang('token edit'), 'C', array('tabindex' => $first_tab + 400, 'class'=>'generate_token_btn')) ?>
          <?php else: ?>
              <input type="hidden" name="token_data[action]" value="add" />          
              <div>
                <?php echo label_tag(lang('token external key'), 'tokenFormExternalKey') ?>
                <?php echo text_field('token_data[external_key]', null, array('tabindex' => $first_tab)) ?>
              </div>
              
              <div>
                <?php echo label_tag(lang('token external name'), 'tokenFormExternalName') ?>
                <?php echo text_field('token_data[external_name]', null, array('tabindex' => $first_tab + 100)) ?>
              </div>
              
              <div>
                <?php echo label_tag(lang('token external type'), 'tokenFormExternalType') ?>
                <?php echo text_field('token_data[type]', null, array('tabindex' => $first_tab + 200)) ?>
              </div>
    
              <?php echo submit_button(lang('token generate'), 'C', array('tabindex' => $first_tab + 400, 'class'=>'generate_token_btn')) ?>
          <?php endif;?>
          
          
        </form>
	</div>
</div>

<script type="text/javascript">
$( document ).ready(function() {

    $("#generate_token_form").on('submit', function (event) {
    	event.preventDefault();        
        $('button[type=submit]', this).attr('disabled', 'true');
        $("#generate_token_form").unbind().submit();

    });

    <?php if (isset($action) && $action == 'edit'): ?>
	    og.resetUserExternalToken = function(){
	    	og.openLink( og.makeAjaxUrl('<?php echo $user->getEditExternalTokensUrl() ?>', {'token_data[token_id]':<?php echo $token->getId() ?>, 'token_data[action]':'resetToken'}), {
	    		callback: function(success,data){
	    			$("#<?php echo $genid ."token_input" ?>").val(data.token);
	    		}
	        } );
	    }
    <?php endif; ?>
	

    
});

</script>