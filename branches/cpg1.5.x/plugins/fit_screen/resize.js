function tableWidth()
{return windowWidth()-200;}

function windowWidth() {
if (navigator.appName=="Netscape")
return window.innerWidth;

return document.body.offsetWidth;
}

function scaleImg() {
what = document.getElementById('thepic');
actualHeight=what.height; 
actualWidth=what.width;
if (  fitsInWindow() ) 
return;
if(what.width==tableWidth())
 {
what.width=actualWidth;
what.height=actualHeight;
}
else
{

what.width=tableWidth();
what.height = (actualHeight/actualWidth) * what.width;
}
}

function showOnclick()
{
what = document.getElementById('thepic');

if (actualWidth == what.width)
        return scaleImg();
else if (actualWidth > what.width)
{
what.width=actualWidth;
what.height=actualHeight;
}
}

function liveResize() {
what = document.getElementById('thepic');
actualHeight=what.height; actualWidth=what.width;
 if (fitsInWindow())return;
if (what.width!=actualWidth) {
what.width=tableWidth();what.height = (actualHeight/actualWidth) * what.width;
}
}

function setImgWidth(){
 if (fitsInWindow())return;
document.getElementById('thepic').width=tableWidth();
}
function fitsInWindow() {
   what = document.getElementById('thepic');
   var  actualWidth= what.width;
  if (actualWidth<tableWidth()) {

   return true;
  } else {
  return false;
  }
  }