<?php
//$extra_header = isset($mail_conversation_block) && $mail_conversation_block != '';
$ret = 0;
Hook::fire("render_page_actions", $object, $ret);
$coId = $object->getObjectId();
if (!isset($iconclass))
	$iconclass = "ico-large-" . $object->getObjectTypeName();

$genid = gen_id();
$isUser = $object instanceof Contact && $object->isUser() ? true : false;
if ($object instanceof ContentDataObject && (!$isUser && $object->canView(logged_user())) || $isUser) {
	add_page_action(lang('view history'), $object->getViewHistoryUrl(), 'ico-history', null, null, false);
}
?>
<table style="width:100%" id="<?php echo $genid ?>-co">
	<tr>
		<td>
			<table style="width:100%;border-collapse:collapse;table-layout:fixed;min-width:600px;">

				<tr>
					<?php
					/**
					 * This section displays the content object header. 
					 */
					?>
					<td class="coViewIcon" colspan=2 rowspan=2>
						<?php if (isset($image)) {
							echo $image;
						} else { ?>
							<div id="<?php echo $coId; ?>_iconDiv" class="coViewIconImage <?php echo $iconclass ?>"></div>
						<?php } ?>
					</td>

					<td class="coViewHeader" rowspan=2>
						<div class="coViewTitleContainer">
							<div class="coViewTitle">
								<table>
									<tr>
										<td>
											<?php echo isset($title) ? $title : $object->getObjectTypeNameLang() . ": " . clean($object->getObjectName()); ?>
										</td>

									</tr>
								</table>
							</div>
							<?php $oncloseclick = "og.closeView()"; //$oncloseclick = $object instanceof Contact && Plugins::instance()->isActivePlugin('core_dimensions') ? "og.onPersonClose()" : "og.closeView()" 
							?>
							<div title="<?php echo lang('close') ?>" onclick="<?php echo $oncloseclick; ?>" class="coViewClose"><?php echo lang('close') ?>&nbsp;&nbsp;X</div>
						</div>

						<?php
						/**
						 * This section displays the content object description. 
						 */
						?>
						<div class="coViewDesc">
							<?php if (!isset($description)) $description = "";
							Hook::fire("render_object_description", $object, $description);
							echo $description;
							?>
						</div>
					</td>

					<td class="coViewTopRight" style="width:12px"></td>
				</tr>
				<tr>
					<td class="coViewRight" rowspan=3 style="width:12px"></td>
				</tr>
				<tr>
					<td class="coViewHeader coViewSubHeader" style="padding:10px" colspan=3>
						<?php if (isset($mail_conversation_block) && $mail_conversation_block != '') echo $mail_conversation_block;

						if (!isset($show_linked_objects)) $show_linked_objects = true;
						if ($object->isLinkableObject() && !$object->isTrashed() && $show_linked_objects) {
							echo render_object_links_main($object, $object->canEdit(logged_user()));
						}
						?>
					</td>
				</tr>

				<tr>
					<?php
					/**
					 * This section displays the rest of the content object body. 
					 */
					?>

					<td class="coViewBody" colspan=3>
						<?php
							// INITIAL TEMPLATE PART FOR INVOICE VIEW TABS ::
							if ($object->getObjectTypeName() == "invoice") {
								Hook::fire('render_invoice_view_before_tab', array(), $invoice);
							}
						?>
						<div>
							<?php
							if (isset($content_template) && is_array($content_template)) {
								tpl_assign('object', $object);
								if (isset($variables)) {
									tpl_assign('variables', $variables);
								}
								$this->includeTemplate(get_template_path($content_template[0], $content_template[1], array_var($content_template, 2)));
							} else if (isset($content)) echo $content;

							if (false && !isset($is_user) && user_config_option("show_object_direct_url")) { ?>
								<div id="<?php echo $genid ?>direct_url" class="direct-url">
									<b><?php echo lang('direct url') ?>:</b>
									<a id="<?php echo $genid ?>task_url" href="<?php echo ($object->getViewUrl()) ?>" target="_blank"><?php echo ($object->getViewUrl()) ?></a>
								</div>
							<?php } ?>
						</div>
						<?php if (isset($internalDivs)) {
							foreach ($internalDivs as $idiv) {
								echo $idiv;
							}
						}



						$more_content_templates = array();
						Hook::fire("more_content_templates", $object, $more_content_templates);
						foreach ($more_content_templates as $ct) {
							tpl_assign('genid', $genid);
							tpl_assign('object', $object);
							$this->includeTemplate(get_template_path($ct[0], $ct[1], array_var($ct, 2)));
						}

						// Main properties
						if ($object instanceof ApplicationDataObject) {
							echo render_custom_properties($object, 'visible_by_default');
						}

						//Extra templates to include
						if (isset($extra_templates_to_include) && is_array($extra_templates_to_include)) {
							tpl_assign('genid', $genid);
							foreach ($extra_templates_to_include as $extra_template) {
								$this->includeTemplate($extra_template['path']);
							}
						}

						// Classification
						if ($object instanceof ContentDataObject) {
							echo render_co_view_member_path($object);
						}

						// Other properties
						if ($object instanceof ApplicationDataObject) {
							echo render_custom_properties($object, 'other');
						}

						// Time entries list
						$logged_user_pgs = logged_user()->getPermissionGroupIds();
						if ($object instanceof ContentDataObject && $object->allowsTimeslots()) {
							$show_timeslot_section = config_option('use_task_work_performed');
							if ($show_timeslot_section) {
								echo render_object_timeslots($object, $object->getViewUrl());
							}
							tpl_assign('show_timeslot_section', $show_timeslot_section);
						}



						if ($object instanceof ProjectTask) {

							$null = null;
							// used to render budgeted and actual expenses list
							Hook::fire('task_view_after_timeslots_list', array('task' => $object, 'genid' => $genid), $null);
							// used to render other sections after the ones rendered with the hook above
							Hook::fire('task_view_after_other_sections', array('task' => $object, 'genid' => $genid), $null);

							// render subtasks and task dependency sections
							if ($object instanceof ProjectTask || $object instanceof TemplateTask) {
								tpl_assign('genid', $genid);
								$object instanceof TemplateTask ? tpl_assign('template_task', 1) : tpl_assign('template_task', 0);
	
								$this->includeTemplate(get_template_path('subtasks_info', 'task'));
							}

						?><div id="<?php echo $genid ?>_work_performed_summary"><?php
							// Work performed summary section
							$this->includeTemplate(get_template_path('work_performed', 'task'));

						?></div><?php
							// Let plugins add more info to the Work performed summary section
							$estimated_executed_info = '';
							Hook::fire("add_estimated_executed_info_to_view", array('object'=>$object, 'genid'=>$genid), $estimated_executed_info);
							echo $estimated_executed_info;
						}




						$isUser = ($object instanceof Contact && $object->isUser());
						if ($object instanceof ContentDataObject &&	$object->canView(logged_user()) || ($isUser && (logged_user()->getId() == get_id() || logged_user()->isAdministrator()))) {
							//echo render_object_latest_activity($object); //TODO SE rompe
						}

						// END TEMPLATE PART FOR INVOICE VIEW TABS ::
						if ($object instanceof IncomeInvoice) Hook::fire('render_invoice_view_after_tab', array(), $invoice);

						if (!$isUser && $object instanceof ContentDataObject && $object->isCommentable())
							echo render_object_comments($object, $object->getViewUrl());
						?>
					</td>
				</tr>
				<tr>
					<td class="coViewBottomLeft"></td>
					<td class="coViewBottom" colspan=2></td>
					<td class="coViewBottomRight" style="width:12px">&nbsp;</td>
				</tr>
			</table>
		</td>
		<td style="width:250px; padding-left:10px">
			<?php
			tpl_assign('genid', $genid);
			$this->includeTemplate(get_template_path('actions', 'co'));
			$this->includeTemplate(get_template_path('properties', 'co'));
			?>
		</td>
	</tr>
</table>