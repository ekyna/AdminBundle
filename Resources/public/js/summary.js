define(["jquery","routing","bootstrap"],function(a,b){var c={};return c.load=function(c){var d=c.data("summary"),e=a.ajax({url:b.generate(d.route,d.parameters),dataType:"html"});e.done(function(a){c.popover({content:a,template:'<div class="popover summary" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',container:"body > div.content",html:!0,placement:"auto",trigger:"hover"}),c.is(":hover")&&c.popover("show")}),c.data("summary-xhr",e),c.removeData("summary-timeout")},c.init=function(){"ontouchstart"in window||a(document).on("mouseenter","[data-summary]",function(){var b=a(this);if(!b.data("summary-xhr")&&!b.data("summary-timeout")){var d=setTimeout(function(){c.load(b)},300);b.data("summary-timeout",d)}}).on("mouseleave","[data-summary]",function(){var b=a(this);if(!b.data("summary-xhr")){var c=b.data("summary-timeout");clearTimeout(c),b.removeData("summary-timeout")}})},c});