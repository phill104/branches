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


eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('7 S=0.4;7 1x=3;7 L=14;7 1y=\'2c\';7 1z=\'y\';7 T=\'2d\';7 1A=\'2e\';7 1f=\'2f\';7 1B=\'2g\';7 1g=\'2h\';7 1C=\'2i\';7 n=0;7 t=0;7 u=0;7 j=0;7 U=0;7 M=0;7 N=2j 2k();7 o=0;7 O=k;7 F=P;7 1h=0;7 V=0;7 p=0;7 q=2l;9 1D(1i){7 1j=g.G;a(2m g.G!=\'9\'){g.G=1i}W{g.G=9(){a(1j){1j()}1i()}}}9 1k(){z(j<u-1||j>u+1){v h:1l(u+(j-u)/3);g.1E(1k,1F);M=1;e;y:M=0;e}}9 A(i,t){j=i;U=i;a(M==0){g.1E(1k,1F);M=1}n=t;X=B.Y.1m(N[n]).1G(\'2n\');a(X==\'\')X=\'&2o;\';Z.2p=X;a(O==k){o=(H*(-(i*1n/((w-1)*q)))/1n)-p;10.b.1H=(o-L)+\'f\'}}9 1l(i){u=i;7 C=w;1I(7 l=0;l<w;l++){7 8=B.Y.1m(N[l]);7 1o=l*-q;a((1o+1p)<U||(1o-1p)>U){8.b.11=\'1J\';8.b.1q=\'1K\'}W{7 Q=1L.2q(2r+i*i)+1n;7 1M=i/Q*I+I;8.b.1q=\'2s\';7 x=(8.12/8.13*8.R)/Q*I;z(x>15){v k:7 16=8.R/Q*I;e;y:x=15;7 16=8.13*x/8.12;e}7 1N=(m*0.34-x)+1O+((x/(S+1))*S);8.b.1P=1M-(8.R/2)/Q*I+1Q+\'f\';a(16&&x){8.b.17=x+\'f\';8.b.18=16+\'f\';8.b.2t=1N+\'f\'}8.b.11=\'1R\';z(i<0){v h:C++;e;y:C=C-1;e}z(8.r==n){v k:8.19=9(){A(J.1r,J.r)};e;y:C=C+1;8.19=9(){2u(J)};e}8.b.2v=C}i+=q}}9 1s(G){K=d.s(T);B=d.s(1f);1a=d.s(1g);10=d.s(1C);Z=d.s(1B);m=B.2w;1O=K.2x;1Q=K.1S;1p=1x*q;I=m*0.5;H=m*0.6;L=L*0.5;15=m*0.2y;K.b.17=15+\'f\';B.b.17=m*0.2z+\'f\';Z.b.18=m+\'f\';Z.b.1T=m*0.2A+\'f\';1a.b.1T=m*0.2B+\'f\';1a.b.1H=m*0.2+\'f\';1a.b.18=H+\'f\';10.19=9(){1U(J);1b k};10.b.1V=1z;w=B.Y.1W;7 r=0;1I(7 l=0;l<w;l++){7 8=B.Y.1m(l);a(8.2C==1){N[r]=l;8.19=9(){A(J.1r,J.r)};8.1r=(-r*q);8.r=r;a(G==h){8.13=8.18;8.12=8.17}z((8.13+1)>(8.12/(S+1))){v h:8.R=2D;e;y:8.R=2E;e}8.2F=8.1G(\'2G\');8.2H=P;8.b.1V=1y;r++}}w=N.1W;1l(u);A(u,n)}9 1t(1c){7 D=d.s(1c);D.b.11=\'1R\'}9 1X(1c){7 D=d.s(1c);D.b.11=\'1J\';D.b.1q=\'1K\'}9 1Y(){a(d.s(T)){1X(1A);1s(h);1t(1f);1t(1g);1Z();A(-2I,3)}}g.2J=9(){a(d.s(T))1s()};g.2K=9(){2L{d=P}2M(2N){}};9 1d(E){7 1e=k;z(E>0){v h:a(n>=1){j=j+q;t=n-1;1e=h}e;y:a(n<(w-1)){j=j-q;t=n+1;1e=h}e}a(1e==h){A(j,t)}}9 1u(c){7 E=0;a(!c)c=g.1v;a(c.20){E=c.20/2O}W a(c.21){E=-c.21/3}a(E)1d(E);a(c.22)c.22();c.2P=k}9 2Q(){a(g.23)K.23(\'2R\',1u,k);K.2S=1u}9 1U(D){F=D;1h=V-F.1S+o}9 24(){F=P;O=k}9 25(26){V=d.2T?g.1v.2U:26.2V;a(F!=P){O=h;p=(V-1h)+L;a(p<(-o))p=-o;a(p>(H-o))p=H-o;7 27=(p+o);7 28=27/((H)/(w-1));7 1w=1L.2W(28);7 29=(1w)*-q;7 t=1w;F.b.1P=p+\'f\';A(29,t)}}9 1Z(){d.2X=25;d.2Y=24;d.2Z=9(){a(O==h){1b k}W{1b h}}}9 2a(c){c=c||g.1v;1b c.30}d.31=9(c){7 2b=2a(c);z(2b){v 32:1d(-1);e;v 33:1d(1);e}};1D(1Y);',62,191,'|||||||var|cpgif_image|function|if|style|cpgif_event|document|break|px|window|true|cpgif_x|cpgif_target|false|cpgif_index|cpgif_images_width|cpgif_caption_id|cpgif_new_slider_pos|cpgif_new_posx|cpgif_xstep|cpgif_i|getElementById|cpgif_new_caption_id|cpgif_current|case|cpgif_max|cpgif_new_img_h|default|switch|cpgif_glideTo|cpgif_img_div|cpgif_zIndex|cpgif_element|cpgif_delta|cpgif_dragobject|onload|cpgif_scrollbar_width|cpgif_size|this|cpgif_imageflow_div|cpgif_conf_slider_width|cpgif_timer|cpgif_array_images|cpgif_dragging|null|cpgif_z|cpgif_pc|cpgif_conf_reflection_p|cpgif_conf_imageflow|cpgif_mem_target|cpgif_posx|else|cpgif_caption|childNodes|cpgif_caption_div|cpgif_slider_div|visibility|cpgif_h|cpgif_w||cpgif_max_height|cpgif_new_img_w|height|width|onmousedown|cpgif_scrollbar_div|return|cpgif_id|cpgif_handle|cpgif_change|cpgif_conf_images|cpgif_conf_scrollbar|cpgif_dragx|cpgif_func|cpgif_oldonload|cpgif_step|cpgif_moveTo|item|100|cpgif_current_image|cpgif_max_conf_focus|display|cpgif_x_pos|cpgif_refresh|cpgif_show|cpgif_wheel|event|cpgif_image_number|cpgif_conf_focus|cpgif_conf_images_cursor|cpgif_conf_slider_cursor|cpgif_conf_loading|cpgif_conf_captions|cpgif_conf_slider|cpgif_addLoad|setTimeout|50|getAttribute|marginLeft|for|hidden|none|Math|cpgif_xs|cpgif_new_img_top|cpgif_images_top|left|cpgif_images_left|visible|offsetLeft|marginTop|cpgif_dragstart|cursor|length|cpgif_hide|startimageflow|cpgif_initMouseDrag|wheelDelta|detail|preventDefault|addEventListener|cpgif_dragstop|cpgif_drag|cpgif_e|cpgif_slider_pos|cpgif_step_width|cpgif_new_target|cpgif_getKeyCode|cpgif_charCode|pointer|imageflow|imgflowloading|imgflowimages|imgflowcaptions|imgflowscrollbar|imgflowslider|new|Array|150|typeof|alt|nbsp|innerHTML|sqrt|10000|block|top|enlarge|zIndex|offsetWidth|offsetTop|51|338|03|02|nodeType|118|90|URL|longdesc|ondblclick|450|onresize|onunload|try|catch|cpgif_errorli|120|returnValue|cpgif_initMouseWheel|DOMMouseScroll|onmousewheel|all|clientX|pageX|round|onmousemove|onmouseup|onselectstart|keyCode|onkeydown|39|37|'.split('|'),0,{}))