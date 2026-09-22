<?php
/**
 * Shared "PDF options" export modal for report wrappers.
 *
 * Opened via og.openPDFOptions(), which replaces the {gen_id} placeholders with the real genid
 * at runtime, so the markup must keep the literal {gen_id} in the field ids and the Export onclick.
 *
 * Set $pdf_export_function before including to choose the JS function called on Export (it receives
 * the genid). Defaults to og.submit_pdf_form.
 */
if (!isset($pdf_export_function)) $pdf_export_function = 'og.submit_pdf_form';

// Get config options to set default pdf layout and pdf size
$pdf_layout = user_config_option('pdf_page_layout');
$pdf_page_size = user_config_option('pdf_page_size');
if ($pdf_page_size == '') $pdf_page_size = 'A4';
?>
<div id="pdfOptions" style="display:none;">
  <div class="pdfopt-modal">
	<div class="pdfopt-header">
		<h3 class="pdfopt-title"><?php echo lang('report pdf options') ?></h3>
	</div>

	<div class="pdfopt-body">
		<div class="pdfopt-field">
			<label class="pdfopt-label"><?php echo lang('report pdf page layout') ?></label>
			<select class="pdfopt-select" name="pdfPageLayout" id="{gen_id}pdfPageLayout">
				<option value="P" <?php if($pdf_layout == 'Portrait') echo 'selected' ?>><?php echo lang('report pdf vertical') ?></option>
				<option value="L" <?php if($pdf_layout == 'Landscape') echo 'selected' ?>><?php echo lang('report pdf landscape') ?></option>
			</select>
		</div>

		<div class="pdfopt-field">
			<label class="pdfopt-label"><?php echo lang('page size') ?></label>
			<select class="pdfopt-select" name="pdfPageSize" id="{gen_id}pdfPageSize">
				<option value="A0"<?php if($pdf_page_size == 'A0') echo 'selected' ?>>A0</option>
				<option value="A1"<?php if($pdf_page_size == 'A1') echo 'selected' ?>>A1</option>
				<option value="A2" <?php if($pdf_page_size == 'A2') echo 'selected' ?>>A2</option>
				<option value="A3" <?php if($pdf_page_size == 'A3') echo 'selected' ?>>A3</option>
				<option value="A4" <?php if($pdf_page_size == 'A4') echo 'selected' ?>>A4</option>
				<option value="A5" <?php if($pdf_page_size == 'A5') echo 'selected' ?>>A5</option>
				<option value="Legal" <?php if($pdf_page_size == 'Legal') echo 'selected' ?>>Legal</option>
				<option value="Letter" <?php if($pdf_page_size == 'Letter') echo 'selected' ?>>Letter</option>
			</select>
		</div>

		<div class="pdfopt-field" style="display:none;">
			<label class="pdfopt-label"><?php echo lang('report font size') ?></label>
			<select class="pdfopt-select" name="pdfFontSize" id="{gen_id}pdfFontSize">
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12" selected>12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
				<option value="16">16</option>
			</select>
		</div>
	</div>

	<div class="pdfopt-footer">
		<button type="button" class="pdfopt-btn pdfopt-btn-secondary" onclick="$.modal.close();return false;"><?php echo lang('cancel') ?></button>
		<button type="submit" class="pdfopt-btn pdfopt-btn-primary" name="exportPDF" onclick="<?php echo $pdf_export_function ?>('{gen_id}');"><?php echo lang('export') ?></button>
	</div>
  </div>
</div>
