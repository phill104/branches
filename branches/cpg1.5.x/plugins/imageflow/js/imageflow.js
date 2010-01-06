/**
 *	ImageFlow 0.9
 *
 *	This code is based on Michael L. Perrys Cover flow in Javascript.
 *	For he wrote that "You can take this code and use it as your own" [1]
 *	this is my attempt to improve some things. Feel free to use it! If
 *	you have any questions on it leave me a message in my shoutbox [2].
 *
 *	The reflection is generated server-sided by a slightly hacked  
 *	version of Richard Daveys easyreflections [3] written in PHP.
 *	
 *	The mouse wheel support is an implementation of Adomas Paltanavicius
 *	JavaScript mouse wheel code [4].
 *
 *	Thanks to Stephan Droste ImageFlow is now compatible with Safari 1.x.
 *
 *
 *	[1] http://www.adventuresinsoftware.com/blog/?p=104#comment-1981
 *	[2] http://shoutbox.finnrudolph.de/
 *	[3] http://reflection.corephp.co.uk/v2.php
 *	[4] http://adomas.org/javascript-mouse-wheel/
 */


var cpgif_auto=1;var cpgif_autotime=4;var cpgif_usewheel=1;var cpgif_usekeys=1;var cpgif_conf_reflection_p=0.4;var cpgif_conf_focus=3;var cpgif_conf_slider_width=14;var cpgif_conf_images_cursor='pointer';var cpgif_conf_slider_cursor='default';var cpgif_conf_imageflow='imageflow';var cpgif_conf_loading='imgflowloading';var cpgif_conf_images='imgflowimages';var cpgif_conf_captions='imgflowcaptions';var cpgif_conf_scrollbar='imgflowscrollbar';var cpgif_conf_slider='imgflowslider';var cpgif_caption_id=0;var cpgif_new_caption_id=0;var cpgif_current=0;var cpgif_target=0;var cpgif_mem_target=0;var cpgif_timer=0;var cpgif_array_images=new Array();var cpgif_new_slider_pos=0;var cpgif_dragging=false;var cpgif_dragobject=null;var cpgif_dragx=0;var cpgif_posx=0;var cpgif_new_posx=0;var cpgif_xstep=150;function cpgif_addLoad(cpgif_func){var cpgif_oldonload=window.onload;if(typeof window.onload!='function'){window.onload=cpgif_func}else{window.onload=function(){if(cpgif_oldonload){cpgif_oldonload()}cpgif_func()}}}function cpgif_step(){switch(cpgif_target<cpgif_current-1||cpgif_target>cpgif_current+1){case true:cpgif_moveTo(cpgif_current+(cpgif_target-cpgif_current)/3);window.setTimeout(cpgif_step,50);cpgif_timer=1;break;default:cpgif_timer=0;break}}function cpgif_glideTo(cpgif_x,cpgif_new_caption_id){cpgif_target=cpgif_x;cpgif_mem_target=cpgif_x;if(cpgif_timer==0){window.setTimeout(cpgif_step,50);cpgif_timer=1}cpgif_caption_id=cpgif_new_caption_id;cpgif_caption=cpgif_img_div.childNodes.item(cpgif_array_images[cpgif_caption_id]).getAttribute('alt');if(cpgif_caption=='')cpgif_caption='&nbsp;';cpgif_caption_div.innerHTML=cpgif_caption;if(cpgif_dragging==false){cpgif_new_slider_pos=(cpgif_scrollbar_width*(-(cpgif_x*100/((cpgif_max-1)*cpgif_xstep)))/100)-cpgif_new_posx;cpgif_slider_div.style.marginLeft=(cpgif_new_slider_pos-cpgif_conf_slider_width)+'px'}}function cpgif_moveTo(cpgif_x){cpgif_current=cpgif_x;var cpgif_zIndex=cpgif_max;for(var cpgif_index=0;cpgif_index<cpgif_max;cpgif_index++){var cpgif_image=cpgif_img_div.childNodes.item(cpgif_array_images[cpgif_index]);var cpgif_current_image=cpgif_index*-cpgif_xstep;if((cpgif_current_image+cpgif_max_conf_focus)<cpgif_mem_target||(cpgif_current_image-cpgif_max_conf_focus)>cpgif_mem_target){cpgif_image.style.visibility='hidden';cpgif_image.style.display='none'}else{var cpgif_z=Math.sqrt(10000+cpgif_x*cpgif_x)+100;var cpgif_xs=cpgif_x/cpgif_z*cpgif_size+cpgif_size;cpgif_image.style.display='block';var cpgif_new_img_h=(cpgif_image.cpgif_h/cpgif_image.cpgif_w*cpgif_image.cpgif_pc)/cpgif_z*cpgif_size;switch(cpgif_new_img_h>cpgif_max_height){case false:var cpgif_new_img_w=cpgif_image.cpgif_pc/cpgif_z*cpgif_size;break;default:cpgif_new_img_h=cpgif_max_height;var cpgif_new_img_w=cpgif_image.cpgif_w*cpgif_new_img_h/cpgif_image.cpgif_h;break}var cpgif_new_img_top=(cpgif_images_width*0.34-cpgif_new_img_h)+cpgif_images_top+((cpgif_new_img_h/(cpgif_conf_reflection_p+1))*cpgif_conf_reflection_p);cpgif_image.style.left=cpgif_xs-(cpgif_image.cpgif_pc/2)/cpgif_z*cpgif_size+cpgif_images_left+'px';if(cpgif_new_img_w&&cpgif_new_img_h){cpgif_image.style.height=cpgif_new_img_h+'px';cpgif_image.style.width=cpgif_new_img_w+'px';cpgif_image.style.top=cpgif_new_img_top+'px'}cpgif_image.style.visibility='visible';switch(cpgif_x<0){case true:cpgif_zIndex++;break;default:cpgif_zIndex=cpgif_zIndex-1;break}switch(cpgif_image.cpgif_i==cpgif_caption_id){case false:cpgif_image.onclick=function(){cpgif_glideTo(this.cpgif_x_pos,this.cpgif_i)};break;default:cpgif_zIndex=cpgif_zIndex+1;cpgif_image.onclick=function(){document.location=this.URL};break}cpgif_image.style.zIndex=cpgif_zIndex}cpgif_x+=cpgif_xstep}}function cpgif_refresh(onload){cpgif_imageflow_div=document.getElementById(cpgif_conf_imageflow);cpgif_img_div=document.getElementById(cpgif_conf_images);cpgif_scrollbar_div=document.getElementById(cpgif_conf_scrollbar);cpgif_slider_div=document.getElementById(cpgif_conf_slider);cpgif_caption_div=document.getElementById(cpgif_conf_captions);cpgif_images_width=cpgif_img_div.offsetWidth;cpgif_images_top=cpgif_imageflow_div.offsetTop;cpgif_images_left=cpgif_imageflow_div.offsetLeft;cpgif_max_conf_focus=cpgif_conf_focus*cpgif_xstep;cpgif_size=cpgif_images_width*0.5;cpgif_scrollbar_width=cpgif_images_width*0.6;cpgif_conf_slider_width=cpgif_conf_slider_width*0.5;cpgif_max_height=cpgif_images_width*0.51;cpgif_imageflow_div.style.height=cpgif_max_height+'px';cpgif_img_div.style.height=cpgif_images_width*0.338+'px';cpgif_caption_div.style.width=cpgif_images_width+'px';cpgif_caption_div.style.marginTop=cpgif_images_width*0.03+'px';cpgif_scrollbar_div.style.marginTop=cpgif_images_width*0.02+'px';cpgif_scrollbar_div.style.marginLeft=cpgif_images_width*0.2+'px';cpgif_scrollbar_div.style.width=cpgif_scrollbar_width+'px';cpgif_slider_div.onmousedown=function(){cpgif_dragstart(this);return false};cpgif_slider_div.style.cursor=cpgif_conf_slider_cursor;cpgif_max=cpgif_img_div.childNodes.length;var cpgif_i=0;for(var cpgif_index=0;cpgif_index<cpgif_max;cpgif_index++){var cpgif_image=cpgif_img_div.childNodes.item(cpgif_index);if(cpgif_image.nodeType==1){cpgif_array_images[cpgif_i]=cpgif_index;cpgif_image.onclick=function(){cpgif_glideTo(this.cpgif_x_pos,this.cpgif_i)};cpgif_image.cpgif_x_pos=(-cpgif_i*cpgif_xstep);cpgif_image.cpgif_i=cpgif_i;if(onload==true){cpgif_image.cpgif_w=cpgif_image.width;cpgif_image.cpgif_h=cpgif_image.height}switch((cpgif_image.cpgif_w+1)>(cpgif_image.cpgif_h/(cpgif_conf_reflection_p+1))){case true:cpgif_image.cpgif_pc=118;break;default:cpgif_image.cpgif_pc=90;break}cpgif_image.URL=cpgif_image.getAttribute('longdesc');cpgif_image.ondblclick=function(){document.location=this.URL};cpgif_image.style.cursor=cpgif_conf_images_cursor;cpgif_i++}}cpgif_max=cpgif_array_images.length;cpgif_moveTo(cpgif_current);cpgif_glideTo(cpgif_current,cpgif_caption_id)}function cpgif_show(cpgif_id){var cpgif_element=document.getElementById(cpgif_id);cpgif_element.style.visibility='visible'}function cpgif_hide(cpgif_id){var cpgif_element=document.getElementById(cpgif_id);cpgif_element.style.visibility='hidden';cpgif_element.style.display='none'}function startimageflow(){cpgif_auto=parseInt(js_vars.cpgif_auto);cpgif_usewheel=parseInt(js_vars.cpgif_usewheel);cpgif_usekeys=parseInt(js_vars.cpgif_usekeys);cpgif_autotime=parseInt(js_vars.cpgif_autotime);if(document.getElementById(cpgif_conf_imageflow)){cpgif_hide(cpgif_conf_loading);cpgif_refresh(true);cpgif_show(cpgif_conf_images);cpgif_show(cpgif_conf_scrollbar);if(cpgif_usewheel)cpgif_initMouseWheel();cpgif_initMouseDrag();cpgif_glideTo(-450,3);if(cpgif_auto)setInterval('cpgif_autorun()',cpgif_autotime*1000);if(cpgif_usewheel){document.onkeydown=function(cpgif_event){var cpgif_charCode=cpgif_getKeyCode(cpgif_event);switch(cpgif_charCode){case 39:cpgif_handle(-1);break;case 37:cpgif_handle(1);break}}}}}function cpgif_autorun(){var cpgif_change=false;if(cpgif_caption_id<(cpgif_max-1)){cpgif_target=cpgif_target-cpgif_xstep;cpgif_new_caption_id=cpgif_caption_id+1;cpgif_change=true}else{cpgif_target=0;cpgif_new_caption_id=0;cpgif_change=true}if(cpgif_change==true){cpgif_glideTo(cpgif_target,cpgif_new_caption_id)}}window.onresize=function(){if(document.getElementById(cpgif_conf_imageflow))cpgif_refresh()};window.onunload=function(){document=null};function cpgif_handle(cpgif_delta){var cpgif_change=false;switch(cpgif_delta>0){case true:if(cpgif_caption_id>=1){cpgif_target=cpgif_target+cpgif_xstep;cpgif_new_caption_id=cpgif_caption_id-1;cpgif_change=true}break;default:if(cpgif_caption_id<(cpgif_max-1)){cpgif_target=cpgif_target-cpgif_xstep;cpgif_new_caption_id=cpgif_caption_id+1;cpgif_change=true}break}if(cpgif_change==true){cpgif_glideTo(cpgif_target,cpgif_new_caption_id)}}function cpgif_wheel(cpgif_event){var cpgif_delta=0;if(!cpgif_event)cpgif_event=window.event;if(cpgif_event.wheelDelta){cpgif_delta=cpgif_event.wheelDelta/120}else if(cpgif_event.detail){cpgif_delta=-cpgif_event.detail/3}if(cpgif_delta)cpgif_handle(cpgif_delta);if(cpgif_event.preventDefault)cpgif_event.preventDefault();cpgif_event.returnValue=false}function cpgif_initMouseWheel(){if(window.addEventListener)cpgif_imageflow_div.addEventListener('DOMMouseScroll',cpgif_wheel,false);cpgif_imageflow_div.onmousewheel=cpgif_wheel}function cpgif_dragstart(cpgif_element){cpgif_dragobject=cpgif_element;cpgif_dragx=cpgif_posx-cpgif_dragobject.offsetLeft+cpgif_new_slider_pos}function cpgif_dragstop(){cpgif_dragobject=null;cpgif_dragging=false}function cpgif_drag(cpgif_e){cpgif_posx=document.all?window.event.clientX:cpgif_e.pageX;if(cpgif_dragobject!=null){cpgif_dragging=true;cpgif_new_posx=(cpgif_posx-cpgif_dragx)+cpgif_conf_slider_width;if(cpgif_new_posx<(-cpgif_new_slider_pos))cpgif_new_posx=-cpgif_new_slider_pos;if(cpgif_new_posx>(cpgif_scrollbar_width-cpgif_new_slider_pos))cpgif_new_posx=cpgif_scrollbar_width-cpgif_new_slider_pos;var cpgif_slider_pos=(cpgif_new_posx+cpgif_new_slider_pos);var cpgif_step_width=cpgif_slider_pos/((cpgif_scrollbar_width)/(cpgif_max-1));var cpgif_image_number=Math.round(cpgif_step_width);var cpgif_new_target=(cpgif_image_number)*-cpgif_xstep;var cpgif_new_caption_id=cpgif_image_number;cpgif_dragobject.style.left=cpgif_new_posx+'px';cpgif_glideTo(cpgif_new_target,cpgif_new_caption_id)}}function cpgif_initMouseDrag(){document.onmousemove=cpgif_drag;document.onmouseup=cpgif_dragstop;document.onselectstart=function(){if(cpgif_dragging==true){return false}else{return true}}}function cpgif_getKeyCode(cpgif_event){cpgif_event=cpgif_event||window.event;return cpgif_event.keyCode}cpgif_addLoad(startimageflow);