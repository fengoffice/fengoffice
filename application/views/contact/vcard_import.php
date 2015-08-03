<?php
	$submit_url = get_url('contact', 'import_from_vcard');
	$genid = gen_id();
?>

<script>
og.submitVcard = function(genid) {
	fname = document.getElementById(genid + 'filenamefield');
	ok = true;
        
        if (fname.value.lastIndexOf('.vcf') == -1 || fname.value.lastIndexOf('.vcf') != fname.value.length - 4 ) {
                ok = confirm(lang('not vcf file continue'));
        }	
	if (ok) {
                form = document.getElementById(genid + 'vcard_import');
                og.submit(form, {
                        callback: og.openLink(og.getUrl('contact', 'import_from_vcard'))
                });
	}        
}
</script>

<form style="height:100%;background-color:white" id="<?php echo $genid ?>vcard_import" name="<?php echo $genid ?>vcard_import" class="internalForm" action="<?php echo $submit_url ?>" method="post" enctype="multipart/form-data">

<div class="file">
    <div class="coInputHeader">
        <div class="coInputHeaderUpperRow">
            <div class="coInputTitle">
                    <table style="width:535px">
                        <tr>
                            <td><?php echo lang('import contacts from vcard');?></td>
                            <?php if (!isset($import_result)) { ?>
                            <td style="text-align: right">
                                    <?php //echo submit_button(lang('import'),'s',array("onclick" => 'og.submitVcard(\'' . $genid .'\');','style'=>'margin-top:0px;margin-left:10px','id' => $genid.'add_file_submit1', 'tabindex' => '210')) ?>
                            </td>
                            <?php } ?>
                        </tr>
                    </table>
            </div>
            </div>
            <?php if (!isset($import_result)) { ?>
                <div id="<?php echo $genid ?>selectFileControlDiv">
                    <?php echo label_tag(lang('file'), $genid . 'filenamefield', true) ?>
                    <?php echo file_field('vcard_file', null, array('id' => $genid . 'filenamefield', 'class' => 'title', 'tabindex' => 10, 'size' => '88', "onchange" => 'og.submitVcard(\'' . $genid .'\');')) ?>
                </div>
            <?php } //if ?>
            </div>
    <div class="coInputMainBlock adminMainBlock">
        <?php
            if (!isset($import_result)) { ?>
                    <p>
                        <b>
                        <?php 
                            lang('select a vcard file to load its data');
                        ?>
                        </b>
                    </p>
        <?php	}
            if (isset($import_result)) {
                    if (count($import_result['import_ok'])) {
                            $isAlt = false;
        ?>
            <br><table><tr><th colspan="2" style="text-align:center"><?php echo lang('contacts succesfully imported') ?></th>
                                       <th style="text-align:center"><?php echo lang('status') ?></th></tr>
        <?php 		foreach ($import_result['import_ok'] as $reg) { ?>
                                    <tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
									<td style="padding-left:10px;"><?php echo array_var($reg, 'name')?></td>
                                    <td style="padding-left:10px;"><?php echo array_var($reg, 'email') ?></td>
                                    <td style="padding-left:10px;"><span class="desc"><?php echo array_var($reg, 'import_status') ?></span></td></tr>
        <?php 			$isAlt = !$isAlt;
                            } ?>
            </table>
        <?php 	} //if
                    if (count($import_result['import_fail'])) {
                            $isAlt = false;
        ?>
            <br><table><tr><th colspan="2" style="text-align:center"><?php echo lang('contacts import fail') ?></th>
                                       <th style="text-align:center"><?php echo lang('import fail reason') ?></th></tr>
        <?php 		foreach ($import_result['import_fail'] as $reg) { ?>
                                    <tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
                                    <td style="padding-left:10px;"><?php echo array_var($reg, 'firstname') . ' ' . array_var($reg, 'lastname')?></td>
                                    <td style="padding-left:10px;"><?php echo array_var($reg, 'email') ?></td>
                                    <td style="padding-left:10px;"><?php echo array_var($reg, 'fail_message') ?></td></tr>
        <?php 			$isAlt = !$isAlt;
                        } ?>
            </table>
        <?php 	}
            } //if
        ?>
            </div>
    </div>
</form>

<script>
	btn = Ext.get('<?php echo $genid ?>filenamefield');
	if (btn != null) btn.focus();
</script>