$(function(){var ul=$('#upload ul');$('#drop a').click(function(){$(this).parent().find('input').click();});$('#upload').fileupload({dropZone:$('html'),add:function(e,data){checkBan(data);var tpl=$('<li class="working"> \
                        <input type="text" value="0" data-width="48" data-height="48"'+' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /> \
                        <p></p> \
                        <span></span> \
                   </li>');tpl.find('p').text(data.files[0].name).append('<i>'+formatFileSize(data.files[0].size)+'</i>');data.context=tpl.appendTo(ul);tpl.find('input').knob();tpl.find('span').click(function(){if(tpl.hasClass('working')){jqXHR.abort();}
tpl.fadeOut(function(){tpl.remove();});});var jqXHR=data.submit();if(!checkSizeLimitations(data.files[0].size)){tpl.find('p').text('File size limitations.');tpl.fadeOut(2500,function(){jqXHR.abort();});return;}},progress:function(e,data){var progress=parseInt(data.loaded/data.total*100,10);data.context.find('input').val(progress).change();if(progress==100){data.context.removeClass('working');}},fail:function(e,data){data.context.addClass('error');},done:function(e,data){if(data.textStatus==='success'){var http=location.protocol;var slashes=http.concat("//");var host=slashes.concat(window.location.hostname)
if(location.port!=='80'&&location.port!==''){host=host.concat(":"+location.port);}
host=host.concat('/');var urls=data.result.split(' ');var urlLong=host.concat(urls[0]);var urlShort=host.concat(urls[1]);data.context.find('p').append('<i class="short_download_url">'+urlShort+'</i>');}}});$(document).on('drop dragover',function(e){e.preventDefault();});function formatFileSize(bytes){if(typeof bytes!=='number'){return'';}
if(bytes>=(1024*1024*1024)){return(bytes/(1024*1024*1024)).toFixed(2)+' GiB';}
if(bytes>=(1024*1024)){return(bytes/(1024*1024)).toFixed(2)+' MiB';}
return(bytes/1024).toFixed(2)+' KiB';}});function checkSizeLimitations(size,type){if(type===undefined){type='free';}
var sizeLimit=(type=='premium'?sizeLimitPremium():sizeLimitFree());if(size>sizeLimit){return false;}
return true;}
function sizeLimitFree(){return 1024*1024*100;}
function sizeLimitPremium(){return 1024*1024*300;}
function checkBan(data){$.ajax({url:"/checkban",type:"POST",data:$('#upload').serialize(),cache:true,success:function(html){if(html==='true'){data.jqXHR.abort();var li=$('li.error');li.find('p').text('Error because of user limitations.');li.fadeOut(2500,function(){li.remove();});}}});}