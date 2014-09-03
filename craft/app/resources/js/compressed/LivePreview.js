/*
 Copyright (c) 2014, Pixel & Tonic, Inc.
 @license   http://buildwithcraft.com/license Craft License Agreement
 @see       http://buildwithcraft.com
 @package   craft.app.resources
*/
(function(a){Craft.LivePreview=Garnish.Base.extend({$form:null,$settingsContainer:null,$btn:null,$spinner:null,$shade:null,$editor:null,$dragHandle:null,$iframeContainer:null,$iframe:null,$fieldPlaceholder:null,postUrl:null,locale:null,basePostData:null,inPreviewMode:!1,fields:null,lastPostData:null,updateIframeInterval:null,loading:!1,checkAgain:!1,editorWidth:null,dragger:null,dragStartEditorWidth:null,init:function(b,c){this.postUrl=b?b:Craft.baseSiteUrl.replace(/\/+$/,"")+"/";this.locale=c;"https:"==
document.location.protocol&&(this.postUrl=this.postUrl.replace(/^http:/,"https:"));this.$form=a("#entry-form");this.$settingsContainer=a("#settings");this.$btn=a("#livepreview-btn");this.$spinner=a("#livepreview-spinner");this.$fieldPlaceholder=a("<div/>");this.basePostData={action:"entries/previewEntry",locale:this.locale};for(var d=this.$form.children("input[type=hidden]"),e=0;e<d.length;e++){var f=a(d[e]);this.basePostData[f.attr("name")]=f.val()}this.editorWidth=Craft.getLocalStorage("LivePreview.editorWidth",
Craft.LivePreview.defaultEditorWidth);this.containEditorWidth();this.addListener(this.$btn,"activate","toggle");Craft.cp.on("beforeSaveShortcut",a.proxy(function(){this.inPreviewMode&&this.moveFieldsBack()},this))},containEditorWidth:function(){return 200>this.editorWidth?(this.editorWidth=200,!0):this.editorWidth>Garnish.$win.width()-200?(this.editorWidth=Garnish.$win.width()-200,!0):!1},toggle:function(){this.inPreviewMode?this.exit():this.enter()},enter:function(){if(!this.inPreviewMode){this.trigger("beforeEnter");
a(document.activeElement).blur();if(!this.$editor){this.$shade=a('<div class="modal-shade dark"></div>').appendTo(Garnish.$bod).css("z-index",2);this.$editor=a('<div id="livepreview-editor"></div>').appendTo(Garnish.$bod);this.$iframeContainer=a('<div id="livepreview-iframe-container" />').appendTo(Garnish.$bod);this.$iframe=a('<iframe id="livepreview-iframe" frameborder="0" />').appendTo(this.$iframeContainer);this.$dragHandle=a('<div id="livepreview-draghandle"></div>').appendTo(Garnish.$bod);var b=
a('<header class="header"></header>').appendTo(this.$editor),c=a('<div class="btn">'+Craft.t("Done")+"</div>").appendTo(b);a("<h1>"+Craft.t("Live Preview")+"</h1>").appendTo(b);this.dragger=new Garnish.BaseDrag(this.$dragHandle,{axis:Garnish.X_AXIS,onDragStart:a.proxy(this,"_onDragStart"),onDrag:a.proxy(this,"_onDrag"),onDragStop:a.proxy(this,"_onDragStop")});this.addListener(c,"click","exit")}this.$editor.css(Craft.left,-this.editorWidth+"px");this.$editor.css("width",this.editorWidth+"px");b=this.getIframeWidth();
this.$iframeContainer.css(Craft.right,-b);this.$iframeContainer.css("width",b);this.addListener(Garnish.$win,"resize","updateWidths");this.updateWidths();this.fields=[];b=a("#fields > .field, #fields > div > div > .field");for(c=0;c<b.length;c++){var d=a(b[c]),e=d.clone();this.$fieldPlaceholder.insertAfter(d);d.detach();this.$fieldPlaceholder.replaceWith(e);d.appendTo(this.$editor);this.fields.push({$field:d,$clone:e})}Garnish.$win.trigger("resize");this.updateIframe()?(this.$spinner.removeClass("hidden"),
this.addListener(this.$iframe,"load",function(){this.slideIn();this.removeListener(this.$iframe,"load")})):this.slideIn();this.inPreviewMode=!0;this.trigger("enter")}},slideIn:function(){a("html").addClass("noscroll");this.$spinner.addClass("hidden");this.$shade.fadeIn();this.$editor.show().stop().animateLeft(0,"slow",a.proxy(function(){this.trigger("slideIn");Garnish.$win.trigger("resize")},this));this.$iframeContainer.show().stop().animateRight(0,"slow",a.proxy(function(){this.updateIframeInterval=
setInterval(a.proxy(this,"updateIframe"),1E3);this.addListener(Garnish.$bod,"keyup",function(a){a.keyCode==Garnish.ESC_KEY&&this.exit()})},this))},exit:function(){this.inPreviewMode&&(this.trigger("beforeExit"),a("html").removeClass("noscroll"),this.removeListener(Garnish.$win,"resize"),this.removeListener(Garnish.$bod,"keyup"),this.updateIframeInterval&&clearInterval(this.updateIframeInterval),this.moveFieldsBack(),Garnish.$win.width(),this.$shade.delay(200).fadeOut(),this.$editor.stop().animateLeft(-this.editorWidth,
"slow",a.proxy(function(){for(var a=0;a<this.fields.length;a++)this.fields[a].$newClone.remove();this.$editor.hide();this.trigger("slideOut")},this)),this.$iframeContainer.stop().animateRight(-this.getIframeWidth(),"slow",a.proxy(function(){this.$iframeContainer.hide()},this)),this.inPreviewMode=!1,this.trigger("exit"))},moveFieldsBack:function(){for(var a=0;a<this.fields.length;a++){var c=this.fields[a];c.$newClone=c.$field.clone();this.$fieldPlaceholder.insertAfter(c.$field);c.$field.detach();this.$fieldPlaceholder.replaceWith(c.$newClone);
c.$clone.replaceWith(c.$field)}Garnish.$win.trigger("resize")},getIframeWidth:function(){return Garnish.$win.width()-this.editorWidth},updateWidths:function(){this.containEditorWidth();this.$editor.css("width",this.editorWidth+"px");this.$dragHandle.css(Craft.left,this.editorWidth+"px");this.$iframeContainer.width(this.getIframeWidth())},updateIframe:function(b){b&&(this.lastPostData=null);if(!this.inPreviewMode)return!1;if(this.loading)return this.checkAgain=!0,!1;b=a.extend(Garnish.getPostData(this.$editor),
Garnish.getPostData(this.$settingsContainer));if(this.lastPostData&&Craft.compare(b,this.lastPostData))return!1;this.lastPostData=b;this.loading=!0;b=a.extend({},b,this.basePostData);var c=a(this.$iframe[0].contentWindow.document).scrollTop();a.post(this.postUrl,b,a.proxy(function(b){b=b+'<script type="text/javascript">document.body.scrollTop = '+c+";\x3c/script>";this.$iframe.css("background",a(this.$iframe[0].contentWindow.document.body).css("background"));this.$iframe[0].contentWindow.document.open();
this.$iframe[0].contentWindow.document.write(b);this.$iframe[0].contentWindow.document.close();this.loading=!1;this.checkAgain&&(this.checkAgain=!1,this.updateIframe())},this));return!0},_onDragStart:function(){this.dragStartEditorWidth=this.editorWidth;this.$iframeContainer.addClass("dragging")},_onDrag:function(){this.editorWidth="ltr"==Craft.orientation?this.dragStartEditorWidth+this.dragger.mouseDistX:this.dragStartEditorWidth-this.dragger.mouseDistX;this.updateWidths()},_onDragStop:function(){this.$iframeContainer.removeClass("dragging");
Craft.setLocalStorage("LivePreview.editorWidth",this.editorWidth)}},{defaultEditorWidth:400})})(jQuery);

//# sourceMappingURL=LivePreview.min.map
