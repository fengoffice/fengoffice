Handlebars.registerHelper("lang", function(text) {	
  return lang(text);
});

Handlebars.registerHelper('escape', function(variable) {
	return variable.replace(/(['"])/g, '\$1');
});