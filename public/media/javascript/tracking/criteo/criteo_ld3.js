/* CRITEO Widgets Loader Version 1.1e */
var CRITEO=function(){
    var G={
        I:[],
        M:function(){
            if(arguments.callee.bo){
                return;
            }
            arguments.callee.bo=true;
            for(var i=0;i<G.I.length;i++){
                G.I[i]();
            }
            },
    N:function(aR){
        this.I[this.I.length]=aR;
       G.M();
       
}
};

function ab(s){
    if(document.getElementsByTagName){
        var F=document.getElementsByTagName('head')[0];
        if(!F){
            F=document.createElement('HEAD');
            document.documentElement.appendChild(F);
        }
        if(F&&F.appendChild){
            F.appendChild(s);
        }
    }
};

function aj(){
    if(typeof(window.encodeURIComponent)==='undefined'){
        var R={
            bk:function(K){
                K=""+K;
                var c,s,C="",i=0;
                while(i<K.length){
                    c=K.charCodeAt(i++);
                    if(c>=0xDC00&&c<0xE000){
                        continue;
                    }
                    if(c>=0xD800&&c<0xDC00){
                        if(i>=K.length){
                            continue;
                        }
                        s=K.charCodeAt(i++);
                        if(s<0xDC00||c>=0xDE00){
                            continue;
                        }
                        c=((c-0xD800)<<10)+(s-0xDC00)+0x10000;
                    }
                    if(c<0x80){
                        C+=String.fromCharCode(c);
                    }else if(c<0x800){
                        C+=String.fromCharCode(0xC0+(c>>6),0x80+(c&0x3F));
                    }else if(c<0x10000){
                        C+=String.fromCharCode(0xE0+(c>>12),0x80+(c>>6&0x3F),0x80+(c&0x3F));
                    }else{
                        C+=String.fromCharCode(0xF0+(c>>18),0x80+(c>>12&0x3F),0x80+(c>>6&0x3F),0x80+(c&0x3F));
                    }
                }
                return C;
        },
        aU:"0123456789ABCDEF",
        ak:function(n){
            return R.aU.charAt(n>>4)+R.aU.charAt(n&0xF);
        },
        aV:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-"
    };
    
    window.encodeURIComponent=function(s){
        s=R.bk(s);
        var C="";
        for(var i=0;i<s.length;i++){
            if(R.aV.indexOf(s.charAt(i))== -1){
                C+="%"+R.ak(s.charCodeAt(i));
            }else{
                C+=s.charAt(i);
            }
        }
        return C;
};

}
};

function bD(){
    if(document.getElementsByTagName){
        var n=1;
        var t=[];
        var l=[];
        var V,L,T,bp,aQ,as,J;
        var a=document.getElementsByTagName('div');
        var an=false;
        for(var i=0;i<a.length;i++){
            if(a[i].id&&a[i].id.substring(0,3).toLowerCase()=="cto"&&a[i].childNodes){
                V=L=T=bp=as=J=null;
                aQ=2;
                for(var j=0;j<a[i].childNodes.length;j++){
                    var c=a[i].childNodes[j];
                    if(c&&c.tagName&&c.tagName.toLowerCase()=="div"&&c.className&&c.className.substring(0,3).toLowerCase()=="cto"){
                        var O=(c.textContent?c.textContent:(c.innerText?c.innerText:null));
                        switch(c.className.toLowerCase()){
                            case 'ctowidgetserver':
                                V=O;
                                break;
                            case 'ctodatatype':
                                L=O;
                                break;
                            case 'ctowidgettype':
                                T=O;
                                break;
                            case 'ctoparams':
                                bp=O;
                                break;
                            case 'ctoversion':
                                aQ=O;
                                break;
                            case 'ctodata':
                                as=c.innerHTML;
                                break;
                            case 'ctokeyword':
                                J=O;
                                break;
                        }
                    }
                }
                if(V&&((!L&&T)||(L&& !T))){
            var u="v="+aQ;
            if(bp){
                u+="&"+bp;
            }
            u="p"+n+"="+encodeURIComponent(u);
            if(as){
                u+="&d"+n+"="+encodeURIComponent(as);
            }
            if(T){
                u+="&w"+n+"="+T;
            }else{
                u+="&t"+n+"="+L;
            }
            if(J){
                u+="&kw="+encodeURIComponent(J);
            }
            if(an==false){
                try{
                    u+='&ref='+encodeURIComponent(document.referrer);
                }catch(e){}
                try{
                    u+='&sc_r='+encodeURIComponent(screen.width+'x'+screen.height);
                }catch(e){}
                try{
                    u+='&sc_d='+encodeURIComponent(screen.colorDepth);
                }catch(e){}
                an=true;
            }
            var s;
            for(s=0;s<t.length;s++){
                if(t[s]==V){
                    break;
                }
            }
            if(s!=t.length&&l[s]&&(l[s].length+u.length)>2000){
            CRITEO.au(l[s]);
            l[s]=null;
        }
        if(s==t.length|| !l[s]){
            t[s]=V;
            l[s]=V+"display.js?";
        }else{
            l[s]+="&";
        }
        l[s]+=u;
        n++;
        }
    }
}
for(var H=0;H<l.length;H++){
    if(l[H]){
        CRITEO.au(l[H]);
    }
}
}
};

function ad(){
    aj();
    bD();
};

return{
    N:function(){
        if(typeof(CRITEO_Loaded)!="undefined"){
            return;
        }
        CRITEO_Loaded=1;
        G.N(function(){
            ad();
        });
    },
    bG:function(def){
        if(document.createElement){
            var s=document.createElement('style');
            if(s){
                s.setAttribute('type','text/css');
                ab(s);
                if(s.styleSheet){
                    try{
                        s.styleSheet.cssText=def;
                    }catch(e){}
                }else{
                var c=document.createTextNode(def);
                s.appendChild(c);
            }
        }
    }
},
au:function(u){
    if(document.createElement){
        var s=document.createElement('script');
        if(s){
            s.type='text/javascript';
            s.src=u.substring(0,2000);
            ab(s);
        }
    }
},
aG:function(a,u){
    if(document.createElement){
        var bj=document.createElement('IMG');
        if(bj){
            if(a){
                var d=document.getElementById(a);
                if(d===null){
                    d=document.createElement('DIV');
                    d.id=a;
                    d.style.display="none";
                    document.body.appendChild(d);
                }
                if(d!==null&&d.appendChild){
                    d.appendChild(bj);
                }
            }
            bj.src=u;
    }
}
},
ac:function(m){
    var g="";
    for(var i=0;i<m;i++){
        g+=Math.floor(Math.random()*0xF).toString(0xF);
    }
    return g+"";
}
};

}();
var CRITEO_Loaded;
