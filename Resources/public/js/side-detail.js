define(["jquery","routing","bootstrap"],function(a,b){var c={$container:null};return c.load=function(c){var d=this;if(c.data("side-detail-content"))return void d.open(c);var e,f=c.data("side-detail");if("object"==typeof f)e=b.generate(f.route,f.parameters);else{if("string"!=typeof f)throw"Unexpected summary config.";e=f}var g=a.ajax({url:e,dataType:"html"});g.done(function(a){c.data("side-detail-content",a),c.removeData("side-detail-xhr"),d.open(c)}),c.data("side-detail-xhr",g),c.removeData("side-detail-timeout")},c.open=function(a){this.$container.addClass("opened").find("> div").html(a.data("side-detail-content"))},c.close=function(){this.$container.removeClass("opened")},c.isOpened=function(){return this.$container.hasClass("opened")},c.lock=function(){this.$container.addClass("locked")},c.unlock=function(){this.$container.removeClass("locked")},c.isLocked=function(){return this.$container.hasClass("locked")},c.init=function(){"ontouchstart"in window||(this.$container=a('<div id="side-detail"><span><i class="glyphicon glyphicon-pushpin"/></span><div></div></div>').appendTo("body"),a(document).on("mouseenter","[data-side-detail]",function(b){if(!c.isLocked()){var d=a(b.currentTarget);d.data("side-detail-xhr")||d.data("side-detail-timeout")||d.data("side-detail-timeout",setTimeout(function(){c.load(d)},300))}}).on("mouseleave","[data-side-detail]",function(b){if(!c.isLocked()){var d=a(b.currentTarget);if(!d.data("side-detail-xhr")){var e=d.data("side-detail-timeout");clearTimeout(e),d.removeData("side-detail-timeout"),c.close()}}}).on("keyup",function(a){17===a.which?c.isLocked()?c.unlock():c.isOpened()&&c.lock():27===a.which&&(c.isLocked()&&c.unlock(),c.isOpened()&&c.close())}))},c});