
	<h2 class="quick-form-title"><?php echo lang("title expense widget") ?></h2>
        <input type="hidden" value="<?php //echo $id?>" name="filter[id]"/>
        <input type="hidden" value="0" name="filter[apply_everywhere]" id="everywhere"/>
        <div class="field">
			<table>
			<script>
			var data = [];
			</script>
			<?php			    
                foreach ($options as $option) {
            ?>
                <script>
                	data.push("<?php echo $option->getName(); ?>");
                </script>
                    
        		<tr style='height:30px;'>
        			<td><span class="bold"><?php echo lang("title ".$option->getName()) ?>:&nbsp;</span></td>
        			<td align='left'>
                    	<?php
        			         echo $option->render($option->getName());        			     
        			    ?>
        			</td>
        		</tr>
        	<?php 
                }
        	?>
    		</table>		
		
        </div>

<script>
	$( function() {
		og.eventManager.fireEvent('load input events', data);
	});
</script>