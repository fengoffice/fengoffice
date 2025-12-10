<div class="calendar-container">

	<?php foreach ($ical_data as $i => $event) : 
		
		$event_description = $event['parsed_ical']['description'];
		$event_description = str_replace(CalFormatUtilities::ICAL_DESC_PROTECTED_SECTION_DELIMITER, "", $event_description);
		$event_description = html_entity_decode($event_description);
	?>
	
	<div class="event">
		<div class="event-header">
			<h3><?php echo $event['name'] ?></h3>
			
			<?php if (!empty($event['start'])) : ?>
			<div class="event-time">
				<strong><?php echo lang('date'); ?>: </strong><?php 
					$start = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $event['start']);
					echo format_descriptive_date($start) . ' ' . format_time($start);

					$end = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $event['duration']);
					if (!empty($end)) {
						echo ' - ' . format_time($end);
					}
				?>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($event['parsed_ical']['location'])) : ?>
			<div class="event-location">
				<strong><?php echo lang('Where') ?>:</strong> <?php echo $event['parsed_ical']['location'] ?>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($event['parsed_ical']['organizer'])) : 
					$organizer = $event['parsed_ical']['organizer'];
				?>
			<div class="event-organizer">
				<strong><?php echo lang('Organizer') ?>:</strong> 
				<?php 
				if (is_string($organizer)) {
					if (str_starts_with($organizer, "mailto:")) {
						$organizer = str_replace("mailto:", "", $organizer);
					}
					echo $organizer;
				} elseif (is_array($organizer)) {
					if(!empty($organizer['name'])) :
						echo $organizer['name'];
						if (!empty($organizer['email'])) :
							echo ' &lt;' . $organizer['email'] . '&gt;';
						endif;
					elseif (!empty($organizer['email'])) :
						echo $organizer['email'];
					endif;
				}
				?>
			</div>
			<?php endif; ?>
		</div>

		<?php if (!empty($event['parsed_ical']['attendee'])) :
			$attendees = $event['parsed_ical']['attendee'];
		?>
		<div class="event-attendees">
			<h4><?php echo lang('Attendees') ?>:</h4>
			<ul>
				<?php foreach($attendees as $attendee) : ?>
				<li>
					<?php 
					if (!empty($attendee['name'])) {
						echo $attendee['name'];
						if (!empty($attendee['mailto']) && $attendee['mailto'] !== $attendee['name']) {
							echo ' &lt;' . $attendee['mailto'] . '&gt;';
						}
					} elseif (!empty($attendee['mailto'])) {
						echo $attendee['mailto'];
					}
					
					if (!empty($attendee['status']) && $attendee['status'] !== 'NEEDS-ACTION') {
						$status_color = '';
						if ($attendee['status'] === 'ACCEPTED') {
							$status_color = '#34A853';
						} elseif ($attendee['status'] === 'DECLINED') {
							$status_color = '#EA4335';
						} elseif ($attendee['status'] === 'TENTATIVE') {
							$status_color = '#FBBC05';
						} elseif ($attendee['status'] === 'DELEGATED') {
							$status_color = '#FBBC05';
						}
						?>
						- <span style="color: <?php echo $status_color ?>;"><?php 
							echo lang('event '.strtolower($attendee['status']));
						?></span>
						<?php
					}
					?>
				</li>
				<?php endforeach ?>
			</ul>
		</div>
		<?php endif ?>

		<?php if(!empty($event['parsed_ical']['status'])) : 
				$status_css = '';
				if ($event['parsed_ical']['status'] === 'CANCELLED') {
					$status_css = 'color:#EA4335;';
				}
			?>
		<div class="event-status">
			<strong><?php echo lang('status') ?>:</strong> <span style="<?php echo $status_css ?>;"><?php echo lang('event '. strtolower($event['parsed_ical']['status'])); ?></span>
		</div>
		<?php endif ?>

		<?php if(!empty($event['parsed_ical']['rrule'])) { 
				$rrule = $event['parsed_ical']['rrule'];
			?>
		<div class="event-recurrence">
			<strong><?php echo lang('Recurrence') ?>:</strong> 
			<?php 
			if(!empty($rrule['FREQ'])) {
				echo strtolower($rrule['FREQ']);
				if(!empty($rrule['INTERVAL'])) {
					echo lang('every') .' '. $rrule['INTERVAL'];
					if($rrule['FREQ'] === 'DAILY') echo lang('days');
					elseif($rrule['FREQ'] === 'WEEKLY') echo lang('weeks');
					elseif($rrule['FREQ'] === 'MONTHLY') echo lang('months');
					elseif($rrule['FREQ'] === 'YEARLY') echo lang('years');
				}
				if(!empty($rrule['UNTIL'])) {
					echo lang('until') . ' ' . date('F j, Y', strtotime($rrule['UNTIL']));
				}
			} elseif (!empty($rrule['raw'])) {
				echo $rrule['raw'];
			}
			?>
		</div>
		<?php } ?>

		<?php if ($show_confirm_attendance[$i]) : ?>

		<div class="confirm-attendance">
			<h4><?php echo lang('confirm attendance'); ?>:</h4>
			<div class="event-invitation-buttons">
				<div class="confirm-att-btn" data-attendance="<?php echo EventInvitations::EVENT_INVITATION_ACCEPTED ?>"><?php echo lang('yes')?></div>
				<div class="confirm-att-btn" data-attendance="<?php echo EventInvitations::EVENT_INVITATION_TENTATIVE ?>"><?php echo lang('maybe')?></div>
				<div class="confirm-att-btn" data-attendance="<?php echo EventInvitations::EVENT_INVITATION_DECLINED ?>"><?php echo lang('no')?></div>
			</div>
		</div>

		<?php endif; ?>

		
		<?php if ($event_description) : ?>
		<div id="event-description-show" class="event-description-toggle"><?php echo lang('show description'); ?></div>
		<div class="event-description" id="event-description" style="display:none;">
			<strong><?php echo lang('description'); ?>: </strong>
			<div class="event-description-content"><?php echo $event_description; ?></div>
			<div id="event-description-hide" class="event-description-toggle"><?php echo lang('hide description'); ?></div>
		</div>
		<?php endif; ?>
		
	</div>
	
	<?php endforeach; ?>
	
</div>
<script>
$('#event-description-show').click(function() {
	$('#event-description').show();
	$(this).hide();
	$('#event-description-hide').show();
});
$('#event-description-hide').click(function() {
	$('#event-description').hide();
	$(this).hide();
	$('#event-description-show').show();
});
$('.event-invitation-buttons .confirm-att-btn').click(function() {
	var attendance = $(this).data('attendance');
	var event_uid = '<?php echo $event['uid'] ?>';
	var user_id = '<?php echo logged_user()->getId() ?>';

	og.openLink(og.getUrl('event', 'change_invitation_state'), {
		preventPanelLoad: true,
		post: {
			event_attendance: attendance,
			event_uid: event_uid,
			user_id: user_id
		}
	});
});
</script>
