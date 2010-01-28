/**
 *  author:		Timothy Groves - http://www.brandspankingnew.net
 *	version:	1.2 - 2006-11-17
 *              1.3 - 2006-12-04
 *              2.0 - 2007-02-07
 *
 */

var useBSNns;if(useBSNns){if(typeof(bsn)=="undefined")bsn={};_bsn=bsn}else{_bsn=this}if(typeof(_bsn.Autosuggest)=="undefined")_bsn.Autosuggest={};_bsn.AutoSuggest=function(id,param){if(!document.getElementById)return false;this.fld=_bsn.DOM.gE(id);if(!this.fld)return false;this.sInput="";this.nInputChars=0;this.aSuggestions=[];this.iHighlighted=0;this.oP=(param)?param:{};var def={minchars:1,meth:"get",varname:"input",className:"autosuggest",timeout:2500,delay:500,offsety:-5,shownoresults:true,noresults:"No results!",maxheight:250,cache:true,type:'Files'};for(k in def)if(typeof(this.oP[k])!=typeof(def[k]))this.oP[k]=def[k];var p=this;this.fld.onkeypress=function(ev){return p.onKeyPress(ev)};this.fld.onkeyup=function(ev){return p.onKeyUp(ev)};this.fld.setAttribute("autocomplete","off")};_bsn.AutoSuggest.prototype.onKeyPress=function(ev){var key=(window.event)?window.event.keyCode:ev.keyCode;var RETURN=13;var TAB=9;var ESC=27;var bubble=true;switch(key){case RETURN:this.setHighlightedValue();bubble=false;break;case ESC:this.clearSuggestions();break}return bubble};_bsn.AutoSuggest.prototype.onKeyUp=function(ev){var key=(window.event)?window.event.keyCode:ev.keyCode;var ARRUP=38;var ARRDN=40;var bubble=true;switch(key){case ARRUP:this.changeHighlight(key);bubble=false;break;case ARRDN:this.changeHighlight(key);bubble=false;break;default:this.getSuggestions(this.fld.value)}return bubble};_bsn.AutoSuggest.prototype.getSuggestions=function(val){if(val==this.sInput)return false;if(val.length<this.oP.minchars){this.sInput="";return false}if(val.length>this.nInputChars&&this.aSuggestions.length&&this.oP.cache){var arr=[];for(var i=0;i<this.aSuggestions.length;i++){if(this.aSuggestions[i].value.substr(0,val.length).toLowerCase()==val.toLowerCase())arr.push(this.aSuggestions[i])}this.sInput=val;this.nInputChars=val.length;this.aSuggestions=arr;this.createList(this.aSuggestions);return false}else{this.sInput=val;this.nInputChars=val.length;var pointer=this;clearTimeout(this.ajID);this.ajID=setTimeout(function(){pointer.doAjaxRequest()},this.oP.delay)}return false};_bsn.AutoSuggest.prototype.doAjaxRequest=function(){var pointer=this;if(typeof(this.oP.script)=="function")var url=this.oP.script(escape(this.fld.value));else var url=this.oP.script+this.oP.varname+"="+escape(this.fld.value);if(!url)return false;var meth=this.oP.meth;var onSuccessFunc=function(req){pointer.setSuggestions(req)};var onErrorFunc=function(status){alert("AJAX error: "+status)};var myAjax=new _bsn.Ajax();myAjax.makeRequest(url,meth,onSuccessFunc,onErrorFunc)};_bsn.AutoSuggest.prototype.setSuggestions=function(req){this.aSuggestions=[];if(this.oP.json){var jsondata=eval('('+req.responseText+')');for(var i=0;i<jsondata.results.length;i++){this.aSuggestions.push({'id':jsondata.results[i].id,'value':jsondata.results[i].value,'info':jsondata.results[i].info})}}else{var xml=req.responseXML;var results=xml.getElementsByTagName('results')[0].childNodes;for(var i=0;i<results.length;i++){if(results[i].hasChildNodes())this.aSuggestions.push({'id':results[i].getAttribute('id'),'value':results[i].childNodes[0].nodeValue,'info':results[i].getAttribute('info')})}}this.idAs="as_"+this.fld.id;this.createList(this.aSuggestions)};_bsn.AutoSuggest.prototype.createList=function(arr){var pointer=this;_bsn.DOM.remE(this.idAs);this.killTimeout();if(arr.length==0&&!this.oP.shownoresults)return false;var div=_bsn.DOM.cE("div",{id:this.idAs,className:this.oP.className});var hcorner=_bsn.DOM.cE("div",{className:"as_corner"});var hbar=_bsn.DOM.cE("div",{className:"as_bar"});var header=_bsn.DOM.cE("div",{className:"as_header"});header.appendChild(hcorner);header.appendChild(hbar);div.appendChild(header);var ul=_bsn.DOM.cE("ul",{id:"as_ul"});for(var i=0;i<arr.length;i++){var val=arr[i].value;var idn=arr[i].id;if(this.oP.type=='Files'){var lhref='displayimage.php?pos=-'}else if(this.oP.type=='Albums'){var lhref='thumbnails.php?album='}var st=val.toLowerCase().indexOf(this.sInput.toLowerCase());var output=val.substring(0,st)+"<em>"+val.substring(st,st+this.sInput.length)+"</em>"+val.substring(st+this.sInput.length);var span=_bsn.DOM.cE("span",{},output,true);if(arr[i].info!=""){var br=_bsn.DOM.cE("br",{});span.appendChild(br);var small=_bsn.DOM.cE("small",{},arr[i].info);span.appendChild(small)}var a=_bsn.DOM.cE("a",{href:lhref+idn});var tl=_bsn.DOM.cE("span",{className:"tl"}," ");var tr=_bsn.DOM.cE("span",{className:"tr"}," ");a.appendChild(tl);a.appendChild(tr);a.appendChild(span);a.name=i+1;a.onclick=function(){return};a.onmouseover=function(){pointer.setHighlight(this.name)};var li=_bsn.DOM.cE("li",{},a);ul.appendChild(li)}if(arr.length==0&&this.oP.shownoresults){var li=_bsn.DOM.cE("li",{className:"as_warning"},this.oP.noresults);ul.appendChild(li)}div.appendChild(ul);var fcorner=_bsn.DOM.cE("div",{className:"as_corner"});var fbar=_bsn.DOM.cE("div",{className:"as_bar"});var footer=_bsn.DOM.cE("div",{className:"as_footer"});footer.appendChild(fcorner);footer.appendChild(fbar);div.appendChild(footer);var pos=_bsn.DOM.getPos(this.fld);div.style.left=pos.x+"px";div.style.top=(pos.y+this.fld.offsetHeight+this.oP.offsety)+"px";div.style.width=this.fld.offsetWidth+"px";div.onmouseover=function(){pointer.killTimeout()};div.onmouseout=function(){pointer.resetTimeout()};document.getElementsByTagName("body")[0].appendChild(div);this.iHighlighted=0;var pointer=this;this.toID=setTimeout(function(){pointer.clearSuggestions()},this.oP.timeout)};_bsn.AutoSuggest.prototype.changeHighlight=function(key){var list=_bsn.DOM.gE("as_ul");if(!list)return false;var n;if(key==40)n=this.iHighlighted+1;else if(key==38)n=this.iHighlighted-1;if(n>list.childNodes.length)n=list.childNodes.length;if(n<1)n=1;this.setHighlight(n)};_bsn.AutoSuggest.prototype.setHighlight=function(n){var list=_bsn.DOM.gE("as_ul");if(!list)return false;if(this.iHighlighted>0)this.clearHighlight();this.iHighlighted=Number(n);list.childNodes[this.iHighlighted-1].className="as_highlight";this.killTimeout()};_bsn.AutoSuggest.prototype.clearHighlight=function(){var list=_bsn.DOM.gE("as_ul");if(!list)return false;if(this.iHighlighted>0){list.childNodes[this.iHighlighted-1].className="";this.iHighlighted=0}};_bsn.AutoSuggest.prototype.setHighlightedValue=function(){if(this.iHighlighted){this.sInput=this.fld.value=this.aSuggestions[this.iHighlighted-1].value;this.fld.focus();if(this.fld.selectionStart)this.fld.setSelectionRange(this.sInput.length,this.sInput.length);this.clearSuggestions();if(typeof(this.oP.callback)=="function")this.oP.callback(this.aSuggestions[this.iHighlighted-1])}};_bsn.AutoSuggest.prototype.killTimeout=function(){clearTimeout(this.toID)};_bsn.AutoSuggest.prototype.resetTimeout=function(){clearTimeout(this.toID);var pointer=this;this.toID=setTimeout(function(){pointer.clearSuggestions()},1000)};_bsn.AutoSuggest.prototype.clearSuggestions=function(){this.killTimeout();var ele=_bsn.DOM.gE(this.idAs);var pointer=this;if(ele){var fade=new _bsn.Fader(ele,1,0,250,function(){_bsn.DOM.remE(pointer.idAs)})}};if(typeof(_bsn.Ajax)=="undefined")_bsn.Ajax={};_bsn.Ajax=function(){this.req={};this.isIE=false};_bsn.Ajax.prototype.makeRequest=function(url,meth,onComp,onErr){if(meth!="POST")meth="GET";this.onComplete=onComp;this.onError=onErr;var pointer=this;if(window.XMLHttpRequest){this.req=new XMLHttpRequest();this.req.onreadystatechange=function(){pointer.processReqChange()};this.req.open("GET",url,true);this.req.send(null)}else if(window.ActiveXObject){this.req=new ActiveXObject("Microsoft.XMLHTTP");if(this.req){this.req.onreadystatechange=function(){pointer.processReqChange()};this.req.open(meth,url,true);this.req.send()}}};_bsn.Ajax.prototype.processReqChange=function(){if(this.req.readyState==4){if(this.req.status==200){this.onComplete(this.req)}else{this.onError(this.req.status)}}};if(typeof(_bsn.DOM)=="undefined")_bsn.DOM={};_bsn.DOM.cE=function(type,attr,cont,html){var ne=document.createElement(type);if(!ne)return false;for(var a in attr)ne[a]=attr[a];if(typeof(cont)=="string"&&!html)ne.appendChild(document.createTextNode(cont));else if(typeof(cont)=="string"&&html)ne.innerHTML=cont;else if(typeof(cont)=="object")ne.appendChild(cont);return ne};_bsn.DOM.gE=function(e){if(typeof(e)=="undefined")return false;else if(typeof(e)=="string"){var re=document.getElementById(e);if(!re)return false;else if(typeof(re.appendChild)!="undefined"){return re}else{return false}}else if(typeof(e.appendChild)!="undefined")return e;else return false};_bsn.DOM.remE=function(ele){var e=this.gE(ele);if(!e)return false;else if(e.parentNode.removeChild(e))return true;else return false};_bsn.DOM.getPos=function(e){var e=this.gE(e);var obj=e;var curleft=0;if(obj.offsetParent){while(obj.offsetParent){curleft+=obj.offsetLeft;obj=obj.offsetParent}}else if(obj.x)curleft+=obj.x;var obj=e;var curtop=0;if(obj.offsetParent){while(obj.offsetParent){curtop+=obj.offsetTop;obj=obj.offsetParent}}else if(obj.y)curtop+=obj.y;return{x:curleft,y:curtop}};if(typeof(_bsn.Fader)=="undefined")_bsn.Fader={};_bsn.Fader=function(ele,from,to,fadetime,callback){if(!ele)return false;this.ele=ele;this.from=from;this.to=to;this.callback=callback;this.nDur=fadetime;this.nInt=50;this.nTime=0;var p=this;this.nID=setInterval(function(){p._fade()},this.nInt)};_bsn.Fader.prototype._fade=function(){this.nTime+=this.nInt;var ieop=Math.round(this._tween(this.nTime,this.from,this.to,this.nDur)*100);var op=ieop/100;if(this.ele.filters){try{this.ele.filters.item("DXImageTransform.Microsoft.Alpha").opacity=ieop}catch(e){this.ele.style.filter='progid:DXImageTransform.Microsoft.Alpha(opacity='+ieop+')'}}else{this.ele.style.opacity=op}if(this.nTime==this.nDur){clearInterval(this.nID);if(this.callback!=undefined)this.callback()}};_bsn.Fader.prototype._tween=function(t,b,c,d){return b+((c-b)*(t/d))};function togglesearch(){if(document.getElementById("stype").value=="Files"){options_xml['type']="Albums";document.getElementById("stype").value="Albums"}else{document.getElementById("stype").value="Files";options_xml['type']="Files"}}