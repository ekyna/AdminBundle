require(["require","jquery","routing","bootstrap"],function(a,b,c){function d(a){var d;if(a.hasOwnProperty("added")){var e=b('<span data-id="'+a.added.id+'"></span>'),f=b('<a href="'+a.added.path+'">'+a.added.label+"</a>"),g=c.generate("ekyna_admin_pin_remove",{id:a.added.id}),h='<a href="'+g+'"><i class="fa fa-remove"></i></a>';e.append(f).append(h).prependTo(i),d=b('a.user-pin[data-resource="'+a.added.resource+'"][data-identifier="'+a.added.identifier+'"]'),1===d.size()&&d.addClass("unpin").attr("href",c.generate("ekyna_admin_pin_resource_unpin",{name:a.added.resource,identifier:a.added.identifier}))}else a.hasOwnProperty("removed")&&(i.find("span[data-id="+a.removed.id+"]").remove(),d=b('a.user-pin[data-resource="'+a.removed.resource+'"][data-identifier="'+a.removed.identifier+'"]'),1===d.size()&&d.removeClass("unpin").attr("href",c.generate("ekyna_admin_pin_resource_pin",{name:a.removed.resource,identifier:a.removed.identifier})))}function e(a){j.empty(),a&&(j.append(k),b.ajax({url:c.generate("ekyna_setting_api_helper_fetch"),data:{reference:a},type:"GET",dataType:"xml"}).done(function(a){k.remove();var c=b(a).find("content");if(1===c.length){var d=b("<div />");b(c.text()).appendTo(d),d.appendTo(j)}}).always(function(){k.remove()}))}b("#sidebar-menu").on("click",".dropdown-toggle",function(a){a.preventDefault();var c=b(this).parent();c.toggleClass("active"),c.hasClass("active")?c.find(".submenu").slideDown("fast"):c.find(".submenu").slideUp("fast")});var f=b("#sidebar-nav");b("body").click(function(){b(this).hasClass("menu")&&b(this).removeClass("menu")}),f.click(function(a){a.stopPropagation()}),b("#menu-toggler").click(function(a){a.stopPropagation(),b("body").toggleClass("menu")}),b(window).resize(function(){b(this).width()>769&&b("body.menu").removeClass("menu")}),f.height()>b(".content").height()&&b("html").addClass("small"),b(document).on("click",".nav-tabs a",function(a){a.preventDefault(),b(this).tab("show")});var g=b("form");g.size()>0&&a(["ekyna-form"],function(a){g.each(function(b,c){var d=a.create(c);d.init()})});var h=b(".ekyna-table");h.size()>0&&a(["ekyna-table"],function(a){h.each(function(b,c){a.create(c)})}),b(document).on("click","a[data-toggle-details]",function(a){a.preventDefault();var c=b(this),d=b("#"+c.data("toggle-details"));return 1===d.size()&&(d.is(":visible")?d.hide():d.show()),!1});var i=b(".navbar li.user-pins > div");b(document).on("click","a.user-pin",function(a){a.preventDefault();var c=b(this),e=c.attr("href");return b.ajax({url:e,method:"GET",dataType:"json"}).done(d),!1}),b("li.user-pins").on("click","a:last-child",function(a){a.preventDefault();var c=b(this),e=c.attr("href");return b.ajax({url:e,method:"GET",dataType:"json"}).done(d),!1});var j=b("#helper-content:visible"),k=b('<p id="helper-content-loading"><i class="fa fa-spinner fa-spin fa-2x"></i></p>');if(1===j.length){var l=j.data("helper")||null;e(l),g.on("focus","*[data-helper]",function(){e(b(this).data("helper"))}).on("blur","*[data-helper]",function(){e(l)})}});