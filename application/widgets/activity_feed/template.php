<?php evx_widgets_render_feed_widget($widget_data); ?>
<script>
(function() {
	// getActivityDataView() (core) renders each feed entry's actor/object links as plain
	// <a href="..."> without the internalLink/onclick wiring the rest of the app uses to open
	// objects via AJAX — clicking them was doing a full page navigation. Rather than touch that
	// shared core method (used well beyond this widget), intercept clicks scoped to this
	// widget's own feed text and route them through og.openLink() instead. Bound once, globally
	// delegated, so it keeps working across widget reloads without stacking listeners.
	if (window._evxActivityLinkHandlerAdded) return;
	window._evxActivityLinkHandlerAdded = true;
	document.addEventListener('click', function(e) {
		var link = e.target.closest && e.target.closest('.fo-widget-feed-text a[href]');
		if (!link) return;
		var href = link.getAttribute('href');
		if (!href || href.indexOf('index.php?c=') === -1) return;
		e.preventDefault();
		og.openLink(href);
	});
})();
</script>

