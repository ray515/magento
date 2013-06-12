/* Erics funky filter factory
*  KTS Web Team
*  
*  Dependancies - jquery, jquery.cookie, gfilter module
*/
jQuery(document).ready(function($){

// dialog home end	
/*	if($('#fStat').html()=='ct'){
		//init selection
		if(!fSel){
			var fSel = new Array();
			fSel[0]="";
		}
		$.each(fJSON, function(i,item){
			$.each(item,function(j,k){
				$('#narrow-by-list').append('<dt class="'+i+'" id="'+i+'">'+j+'</dt><dd id="'+i+'"><ol></ol></dd>');
				var ata= new Array();
				ata = k.split(',');
				$.each(ata,function(l,i1){
					$('#'+i+' ol').append('<li id="'+i+'" data-code="'+i+'" data-val="'+i1+'">'+i1+'</li>');
				});
			});
		});
//		$('#narrow-by-list').append('<dt>Reset Filter</dt><dd><button id="fResetBut">Reset Filter</button></dd>');
//		$('#narrow-by-list').append('<button id="fResetBut">Reset Filter</button>');

		//init cookies
		if(!$.cookie('KTS_FS1')){
			$.cookie('KTS_FS1',fSel.toString());
		}else{
			pageInit(jsCol);
		}
	
		$('#narrow-by-list').accordion({
			collapsible:true,
			active:false
		});
	
		$('#narrow-by-list ol>li').click(function(){
			var fOut;
			var ff=$(this).data('code');
			if($(this).attr('class')=="liTouch"){
				$(this).removeClass('liTouch').addClass('liStart');
				$('#'+ff+' #selPar').remove();
				$('#'+ff+' #selLi').remove();
				fOut=$(this).data('code')+':'+$(this).html();
				killTest=killSel(fOut,fSel,'kill');
				//filterInit(fSel,jsCol);
			}else{
				$(this).siblings().removeClass('liTouch').addClass('liStart');
				$('#'+ff+' #selPar').remove();
				$('#'+ff+' #selLi').remove();
				fOut=$(this).data('code')+':'+$(this).html();
				killTest=killSel(fOut,fSel,'updt');
				if(killTest="dead"){
					addSel(fOut);
					$(this).removeClass('liStart').addClass('liTouch');
					$('#'+ff).append('<div id="selPar"></div>');
					$(this).append('<div id="selLi"></div>');
					
					//reload test
					//reloading test
					//var nURL = document.URL.split('?');
					//var nURL1;
					//if(nURL.length!=0){
					//	nURL1=nURL[0]+"?"+nURL[1]+"&fSel="+fSel+"&length="+nURL.length;
					//}else if(nURL.length==0){
					//	nURL1=nURL[0]+"?fSel="+fSel;
					//}else{
					//	nURL1=nURL[0];
					//}
					
					//filterInit(fSel,jsCol);
				};
			};
			window.location.replace(document.URL);				
		});
	}else{ }

	function pageInit(jsCol){
		
		var cTemp = $.cookie('KTS_FS1');
		if(cTemp.charAt(0)==','){
			cTemp=cTemp.substring(1,cTemp.length);
			
		}
		fSel=deDup(cTemp.split(','));
		$.cookie('KTS_FS1',fSel.toString());
		var i;
		for(i=0;i<fSel.length;i++){
			var t1=fSel[i].split(':');
			$('#narrow-by-list #'+t1[0]).find("[data-val='"+t1[1]+"']").append('<div id="selLi"></div>').addClass('liTouch');
			$('#narrow-by-list .'+t1[0] ).append('<div id="selPar"></div>');
			$('#filterList p').html('Your Current Filter:');
			$('#filterList ul').append('<li><strong>'+$('#narrow-by-list #'+t1[0]).html()+'</strong>:'+t1[1]+'</li>');
		}
		//filterInit(fSel,jsCol);
	}
	
	function filterInit(fSel,jsCol){
		var cTemp = fSel.toString();
		if(cTemp.charAt(0)==','){
			cTemp=cTemp.substring(1,cTemp.length);
		}
		var fSel=cTemp.split(',');
		if(fSel[0].length!=0){
		var filterJSON='{';
		for(var i=0; i<fSel.length;i++){
			var x=fSel[i].split(':');
			filterJSON = filterJSON+'"'+x[0]+'": "'+x[1]+'"';
			if(i != fSel.length-1){filterJSON=filterJSON+',';}
		}
		filterJSON = filterJSON+'}';

		
/*
 * for ajax (no paging)
 * 
		var nURL = 'http://'+document.URL.split('/')[2]+'/gfilter/index/index/';
		var bURL = nURL;
		var getit = $.post(bURL,{JSONin:filterJSON,pCol:jsCol});
		getit.done(function(data){
			$('#fRes').html(data);
		});
		
		$("#fResTab").show();
		$('#fRes').show();
		$("#tabs").tabs('option','active',0);
		
		}else{
			$("#tabs").tabs('option','active',1);
			$("#fResTab").hide();
			//$('#fRes').hide();html("We did not find any results based on your filter options.<br/>Please change the last option you selected to another value.");
		}
		
	}
	
	function addSel(newSel){
		
		fSel[fSel.length]=newSel;
		fSel=deDup(fSel);		
		$.cookie('KTS_FS1', fSel.toString());
	}
	
	function killSel(killSel,fSel,action){
		var killSel1;
		var pos2;
		var killSel0;

		killSel0  = killSel.split(':');
		killSel1  = killSel0[0];
		if(action=='updt'){
			pos2=0;
			for (var i=0; i<fSel.length;i++){
				if(fSel[i].match(killSel1)==killSel1){
					fSel.splice(i,1);
					pos2++;
				}
			}
		}
		if(action=='kill'){
			
			for (var i=0; i<fSel.length;i++){
				if(fSel[i].match(killSel)==killSel){
					fSel.splice(i,1);
				}
			}
		}
		
		fSel=deDup(fSel);
		$.removeCookie('KTS_FS1');
		$.cookie('KTS_FS1',fSel.toString());
		return "dead";
	}*/
/*	
	function cleanTxt(ttc){
		tIn=ttc.replace(/(?:\$| *\([^)]*\) *)/g, "");
		return tIn;
	}

	function deDup(arr) {
		  var i,len=arr.length,out=[],obj={};for (i=0;i<len;i++) {  obj[arr[i]]=0;  } for (i in obj) { out.push(i);  } return out;
		}


	 $('#clrBut').click(function(){
			$('#ctFilter').append('<input type="hidden" name="rf" value="true" />');
			$('#ctFilter').submit();	
		});

	 $('#goBut').click(function(){
			$('#ctFilter').submit();
	});
*/
});

