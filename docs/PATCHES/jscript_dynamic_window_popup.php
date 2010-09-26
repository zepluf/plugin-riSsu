<script language="javascript" type="text/javascript"><!--
// JavaScript Document
// Script Source: CodeLifter.com
// Copyright 2003
// Do not remove this notice.

// SETUPS:
// =========
// Set the horizontal and vertical position for the popup
PositionX = 100;
PositionY = 100;
// Set these value approximately 20 pixels greater than the
// size of the largest image to be used (needed for Netscape)
defaultWidth  = 420;
defaultHeight = 780;
// Set autoclose true to have the window close automatically
// Set autoclose false to allow multiple popup windows
var AutoClose = true;
// Do not edit below this line...
// =========
if (parseInt(navigator.appVersion.charAt(0))>=4){
var isNN=(navigator.appName=="Netscape")?1:0;
var isIE=(navigator.appName.indexOf("Microsoft")!=-1)?1:0;}
var optNN='scrollbars=no,width='+defaultWidth+',height='+defaultHeight+',left='+PositionX+',top='+PositionY;
var optIE='scrollbars=no,width=150,height=100,left='+PositionX+',top='+PositionY;
function popImage(imageURL,imageTitle,imageDesc){
if (isNN) {imgWin=window.open('about:blank','',optNN);} else
if (isIE) {imgWin=window.open('about:blank','',optIE);} else
{imgWin=window.open('about:blank','',optIE);isIE=true;}
with (imgWin.document){
writeln('<html><head><base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" /><title>Loading...</title><style>body{margin-bottom:32px;margin-left:8px;margin-right:8px;margin-top:8px;font-family:tahoma, arial, helvetica, Geneva, sans-serif;}</style>');writeln('<sc'+'ript>');
writeln('var isNN,isIE;');writeln('if (parseInt(navigator.appVersion.charAt(0))>=4){');
writeln('isNN=(navigator.appName=="Netscape")?1:0;');writeln('isIE=(navigator.appName.indexOf("Microsoft")!=-1)?1:0;}');
writeln('function reSizeToImage(){');writeln('if (isIE){');writeln('window.resizeTo(100,100);');
writeln('width=parseInt(document.images[0].width)+16;');
writeln('height=parseInt(document.images[0].height)+40;');
writeln('window.resizeTo(width+12,height+84);}');writeln('if (isNN){');       
writeln('window.innerWidth=document.images["George"].width+16;');writeln('window.innerHeight=document.images["George"].height+40;}}');
writeln('function doTitle(){document.title="'+imageTitle+'";}');writeln('</sc'+'ript>');
if (!AutoClose) writeln('</head><body bgcolor=000000 scroll="no" onload="reSizeToImage();doTitle();self.focus()">')
else writeln('</head><body bgcolor=ffffff scroll="no" onload="reSizeToImage();doTitle();self.focus()" onblur="self.close()">');
writeln('<img name="George" src='+imageURL+' style="display:block"> <center>' + imageDesc + '<br/><a href="#" onclick=" self.close();">Close Window</a></center></body></html>');
close();                 
}}
//-->
</script>