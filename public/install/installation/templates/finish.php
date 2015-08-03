<h1 class="pageTitle"><span>Step <?php echo $current_step->getStepNumber() ?>:</span> Finish</h1>
<p>Installation process:</p>
<?php if(isset($status_messages)) { ?>
<ul>
<?php foreach($status_messages as $status_message) { ?>
  <li><?php echo $status_message ?></li>
<?php } // foreach ?>
</ul>
<?php } // if ?>

<?php if(isset($all_ok) && $all_ok) { ?>
<h1>Success!</h1>
<p>You have installed Feng Office <strong>successfully</strong>. Go to <a href="<?php echo $absolute_url ?>" onclick="window.open('<?php echo $absolute_url ?>'); return false;"><?php echo clean($absolute_url) ?></a> and start managing your projects (Feng Office will ask you to create administrator user and provide some details about your company first).</p>
<p><strong>Visit <a href="http://www.fengoffice.com/">www.fengoffice.com</a> regularly for news, updates and support</strong>.
Visit our forums at <a href="http://fengoffice.com/web/forums/">Feng Office</a> and join the growing community of Feng Office users. Thank you!</p>
<?php } // if ?>