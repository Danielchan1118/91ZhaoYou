var pt_logout={ret:0,appDomain:"",mainDomain:"",httpWhiteDomain:{"qq.com":1,"soso.com":1,"paipai.com":1,"tenpay.com":1,"taotao.com":1,"tencent.com":1,"oa.com":1,"webdev.com":1,"3366.com":1,"imqq.com":1,"pengyou.com":1,"qplus.com":1,"qzone.com":1,"server.com":1,"myapp.com":1,"kuyoo.cn":1,"weiyun.com":1,"wechatapp.com":1,"51buy.com":1,"yixun.com":1,"qcloud.com":1,"wechat.com":1,"weishi.com":1},getCookie:function(b){var a=document.cookie.match(new RegExp("(^| )"+b+"=([^;]*)(;|$)"));return !a?"":decodeURIComponent(a[2])},delCookie:function(a,b,c){document.cookie=a+"=; expires=Mon, 26 Jul 1997 05:00:00 GMT; path="+(c?c:"/")+"; "+(b?("domain="+b+";"):"")},jsonp:function(b){var a=document.createElement("script");a.setAttribute("src",b);document.getElementsByTagName("head")[0].appendChild(a);a.onerror=function(){a.onerror=null;pt_logout.set_ret(0,"")}},nlog:function(h,d,g){if(Math.random()<=(g||1)){var a="http://badjs.qq.com/cgi-bin/js_report?";if(location.protocol=="https:"){a="https://ssl.qq.com//badjs/cgi-bin/js_report?"}var e=location.href;var f=encodeURIComponent(h+"|_|"+e+"|_|"+window.navigator.userAgent);var c=a+"bid=110&level=2&mid="+d+"&msg="+f+"&v="+Math.random();var b=new Image();b.src=c}},init:function(){var a=location.hostname.match(/\w*\.(com|cn)$/);pt_logout.mainDomain=a?a[0]:"";if(!pt_logout.httpWhiteDomain[pt_logout.mainDomain]){pt_logout.nlog("公司外部域名引用logout","350448")}var b=location.hostname.match(/\w+(\.\w+){2}$/);pt_logout.appDomain=b?b[0]:"";if(pt_logout.mainDomain!="qq.com"){pt_logout.appDomain=pt_logout.mainDomain}pt_logout.nlog("logout pv","365716",0.05)},getLogoutUrl:function(){var f=pt_logout.getCookie("pt4_token");var a=pt_logout.getCookie("skey");var c=pt_logout.time33(a);var b=pt_logout.getCookie("ptcz");var e=pt_logout.hash33(b);var d="";d=((location.protocol=="https:")?"https://ssl.":"http://")+"ptlogin2."+pt_logout.mainDomain+"/logout?";d+=("pt4_token="+f+"&pt4_hkey="+c+"&pt4_ptcz="+e+"&deep_logout=1");return d},time33:function(d){var c=0;for(var a=0,b=d.length;a<b;a++){c=c*33+d.charCodeAt(a)}return c%4294967296},hash33:function(d){var c=0;for(var a=0,b=d.length;a<b;++a){c+=(c<<5)+d.charCodeAt(a)}return c&2147483647},clearCookie:function(a){a=a||pt_logout.appDomain;pt_logout.delCookie("p_uin",a);pt_logout.delCookie("p_skey",a);pt_logout.delCookie("p_luin",a);pt_logout.delCookie("p_lskey",a);pt_logout.delCookie("pt4_token",a);pt_logout.delCookie("pt_mbkey",pt_logout.mainDomain||"qq.com");if(pt_logout.appDomain!=""&&pt_logout.getCookie("skey_m")!=""){pt_logout.delCookie("uin_m",pt_logout.appDomain);pt_logout.delCookie("skey_m",pt_logout.appDomain)}},set_ret:function(d,b){try{var a=pt_logout.getCookie("pt4_token");pt_logout.clearCookie(b);if(d>0){}else{pt_logout.delCookie("uin",pt_logout.mainDomain);pt_logout.delCookie("skey",pt_logout.mainDomain);pt_logout.nlog("logout fail","285851",0.1)}if(typeof pt_logout.callback=="function"){pt_logout.callback(2)}if(b!=""&&b!=pt_logout.appDomain){pt_logout.nlog("domain unsame:n="+d+":pt4_token="+a+":cgi_domain="+b+":js_domain="+pt_logout.appDomain,"285852",0.1)}}catch(c){return}},logout:function(e){pt_logout.init();var d=pt_logout.getCookie("pt4_token");var a=pt_logout.getCookie("skey");var b=pt_logout.getCookie("ptcz");if(typeof e=="function"){pt_logout.callback=e}var c=pt_logout.getLogoutUrl();if(!d&&!a&&!b){if(typeof e=="function"){e(2)}pt_logout.nlog("logout without cookie",365715)}else{pt_logout.jsonp(c)}}};