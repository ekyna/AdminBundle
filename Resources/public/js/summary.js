define(["jquery","routing","bootstrap"],function(a,b){var c={};return c.load=function(c){var d=c.data("summary"),e=a.ajax({url:b.generate(d.route,d.parameters),dataType:"html"});e.done(function(a){c.popover({content:a,template:'<div class="popover summary" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',container:"body",html:!0,placement:"auto",trigger:"hover"}),c.is(":hover")&&c.popover("show")}),c.data("summary-xhr",e),c.removeData("summary-timeout")},c.init=function(){"ontouchstart"in window||a(document).on("mouseenter","[data-summary]",function(b){b.stopPropagation(),b.preventDefault();var d=a(this);if(!d.data("summary-xhr")&&!d.data("summary-timeout")){var e=setTimeout(function(){c.load(d)},300);d.data("summary-timeout",e)}}).on("mouseleave","[data-summary]",function(){var b=a(this);if(!b.data("summary-xhr")){var c=b.data("summary-timeout");clearTimeout(c),b.removeData("summary-timeout")}})},c});