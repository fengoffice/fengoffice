Handlebars.registerHelper("lang", function(text) {	
  return lang(text);
});

Handlebars.registerHelper('escape', function(variable) {
	return variable.replace(/(['"])/g, '\$1');
});

Handlebars.registerHelper('if_eq', function(a, b, opts) {
    if(a == b) 
        return opts.fn(this);
    else
        return opts.inverse(this);
});