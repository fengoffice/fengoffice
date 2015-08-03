<script id="breadcrumb-popover-template" type="text/x-handlebars-template"> 
	    <div class='popover' onmouseenter="og.showBreadcrumbsPopover('{{btn_id}}')" onmouseleave="og.hideBreadcrumbsPopover('{{btn_id}}',this.id)" onclick="og.hideBreadcrumbsPopover('{{btn_id}}',this.id)" style="width:{{max_width}}px;max-width:{{max_width}}px;min-width:150px;">
          <div class='arrow'></div>
            <div class='popover-inner'>
              <div>
                  <ul class="breadcrumb-list-popover">
                      {{#each breadcrumbs}}
                        <li class="breadcrumb-object-type-title">
                          {{lang @key}}                         
                        </li>
                        {{#each this}}
                        	<li>
                         	 {{{html}}}                         
                        	</li>                        	                      
                      	{{/each}}                      
                      {{/each}}                                          
                  </ul>
                </div>
          </div>
      </div>    
</script>

