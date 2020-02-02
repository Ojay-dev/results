var elem = document.createElement('div');
elem.id = 'ice_icebox';
var styleBox = $('<style></style>');
styleBox.attr('type', 'text/css');
styleBox.attr('id', 'ice_icestyles');
$('head').append(styleBox);
document.body.append(elem);
var e=document.getElementsByClassName('ice');
var startInitIce = 0;
function initIce(){
    for(var i=startInitIce;i<e.length;i++){
        var elemtype = e[i].outerHTML.substring(1, e[i].outerHTML.indexOf(' '));
        e[i].classList.add('ice_icetmp'+i)
        $('#ice_icebox').append(c(i,elemtype));
    }
    $('.ice-trigger').eq(startInitIce).click(function(){ var t=this.attributes[1].value;var e=$('.ice-'+t);if(!e.hasClass('ice-hidden')){e.toggleClass('ice-hidden');setTimeout(function(){$('.ice-'+t+' textarea').attr('style','display:none;');},200);}else{$('.ice-'+t+' textarea').attr('style','display:block;');setTimeout(function(){e.toggleClass('ice-hidden');},10);}});
    $('.ice-textbox textarea').eq(startInitIce).on('input',function(){ 
        var t =this.attributes[0].value;
        sp(rsn(this.value), '.ice_icetmp'+t);
    });
    startInitIce = i;
}
setInterval(initIce, 1);
function c(n,m){
    var Elem = m;
    var id = (document.getElementsByClassName('ice')[n].id)? '#'+document.getElementsByClassName('ice')[n].id: '';
    var e=$('<div></div>');
    e.attr('data-target',n);
    e.addClass('ice-textbox');
    e.html("<span>"+Elem+"<i style=\"color:red\">"+id+"</i></span>"+".<i style=\"color:red\">"+document.getElementsByClassName('ice')[n].classList[0]+"</i></span><br>"+'<button class="ice-trigger" data-target="'+n+'"><span></span><span></span><span></span></button>');
    e.html(e.html()+'<textarea data-target="'+n+'" style="display: none;">/*small devices*/\n\n/*medium devices*/\n\n/*large devices*/\n\n/*extra large devices*/\n\n</textarea>');
    var firstDiv = $('<div></div>');
    firstDiv.addClass('ice-hidden');
    firstDiv.addClass('ice-content');
    firstDiv.addClass('ice-'+n);
    firstDiv.append(e);
    return firstDiv;
}
function rsn(n){var tmp='';var nl=`
`;for(var i=0;i<n.length;i++){if(n[i]==' '||n[i]=='\n'||n[i]==nl){continue;}tmp+=n[i];}return tmp;}
function g(n,m){ 
    var t='';
    var c='';
    for(var i=0;i<n.length;i++){
        if(n[i]+n[i+1]=='\/*'){
            if(t == m) return c
            c='';
            t='';
            for(var j= i;j<n.length;j++){
                t+=n[j];
                if(n[j-1]+n[j]=='*\/'){break;}
            }
            i=j;
            continue;
        }
        c+=n[i];
    }
    return false;
}
var sm=new Array();
var md=new Array();
var lr=new Array();
var xl=new Array();
function ds(arr){
    var tmp = '';
    for(var i in arr){
        tmp += i+'{'+arr[i][1]+'}';
    }
    return tmp;
}
function sp(n,m){
    var x_s = g(n,'/*smalldevices*/');
    var x_m = g(n, '/*mediumdevices*/');
    var x_l = g(n, '/*largedevices*/');
    var x_xl = g(n, '/*extralargedevices*/');
    sm[m] = [m, x_s];
    md[m] = [m, x_m];
    lr[m] = [m, x_l];
    xl[m] = [m, x_xl];
    styleBox.text(ds(sm));
    styleBox.text(styleBox.text()+'@media (min-width: 768px){'+ds(md)+'}');
    styleBox.text(styleBox.text()+'@media (min-width: 992px){'+ds(lr)+'}');
    styleBox.text(styleBox.text()+'@media (min-width: 1200px){'+ds(xl)+'}');
}