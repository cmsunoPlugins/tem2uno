//
// CMSUno
// Plugin tem2uno
//
function f_analyze_tem2uno(){
	var a=document.getElementById('tem2unoZip').value;
	var o=document.getElementById('tem2unoCms').options[document.getElementById('tem2unoCms').selectedIndex].value;
	document.getElementById('tem2unoZip').value='';
	jQuery(document).ready(function(){
		jQuery.post('uno/plugins/tem2uno/tem2uno.php',{'action':'analyze','unox':Unox,'z':a,'o':o},function(r){
			if(r.substr(0,1)=='!')f_alert(r);
			else{
				a=document.getElementById('listTem2uno');
				a.innerHTML=r;
				document.getElementById('anaTem2uno').style.display="block";
			}
		});
	});
}
//
function f_create_tem2uno(f,n,d,o){
	document.getElementById('anaTem2uno').style.display="none";
	jQuery(document).ready(function(){
		jQuery.post('uno/plugins/tem2uno/tem2uno.php',{'action':'create','unox':Unox,'f':f,'n':n,'d':d,'o':o},function(r){
			if(!document.getElementById(r)&&r.substr(0,1)!='!'){
				a=document.getElementById('outTem2uno');
				b=document.createElement('tr');
				b.id=r;
				c=document.createElement('td');
				c.innerHTML=r;
				b.appendChild(c);
				c=document.createElement('td');
				c.style.backgroundImage='url(uno/includes/img/close.png)';
				c.style.backgroundPosition='center center';
				c.style.backgroundRepeat='no-repeat';
				c.style.cursor='pointer';
				c.width='30px';
				c.onclick=function(){f_supp_tem2uno(this.parentNode.id);}
				b.appendChild(c);
				a.appendChild(b);
				a=document.getElementById('tem');
				b=document.createElement("option");
				b.text=r;
				a.add(b);
			}
			f_alert(r);
		});
	});
}
//
function f_supp_tem2uno(f){
	jQuery(document).ready(function(){
		jQuery.post('uno/plugins/tem2uno/tem2uno.php',{'action':'supp','unox':Unox,'s':f},function(r){
			a=document.getElementById(f);
			a.parentNode.removeChild(a); 
			f_alert(r);
			a=document.getElementById('tem');
			for(v=0;v<a.length;v++){if(a.options[v].value==f)a.options[v]=null;}
		});
	});
}
//
