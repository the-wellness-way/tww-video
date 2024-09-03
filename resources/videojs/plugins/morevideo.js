/**
 * Copyright (c) 2022 The Nuevodevel Team. All rights reserved.
 * Morevideo plugin for video.js
 * Version 2.0.0
 */
!function(e,s){"function"==typeof define&&define.amd?define([],s.bind(this,e,e.videojs)):"undefined"!=typeof module&&module.exports?module.exports=s(e,e.videojs):s(e,e.videojs)}(window,function(e,s){"use strict";const t=function(t,r){var l=function(e,s,t){var r=document.createElement(e);return void 0!==s&&""!==s&&(r.className=s),void 0!==t&&""!==t&&(r.innerHTML=t),r};if(Array.isArray(r)&&!(r.length<2)){var i=s.dom,a=t.el(),o=0,d=0,n=1,v=1,c=0,h=null,m=null,p=null,u=null,f=!1,j=null,y=null,C=null,g=null,x=null;return e.addEventListener("resize",b),t.on("playerresize",function(){b()}),t.one("playing",function(){if(null===y){var e=l("div","vjs-more-button");e.innerHTML='<span class="svg"><svg viewBox="0 0 448 448" xmlns="http://www.w3.org/2000/svg"><path d="M128 312v48c0 13-11 24-24 24H24c-13 0-24-11-24-24v-48c0-13 11-24 24-24h80c13 0 24 11 24 24zm0-128v48c0 13-11 24-24 24H24c-13 0-24-11-24-24v-48c0-13 11-24 24-24h80c13 0 24 11 24 24zm160 128v48c0 13-11 24-24 24h-80c-13 0-24-11-24-24v-48c0-13 11-24 24-24h80c13 0 24 11 24 24zM128 56v48c0 13-11 24-24 24H24c-13 0-24-11-24-24V56c0-13 11-24 24-24h80c13 0 24 11 24 24zm160 128v48c0 13-11 24-24 24h-80c-13 0-24-11-24-24v-48c0-13 11-24 24-24h80c13 0 24 11 24 24zm160 128v48c0 13-11 24-24 24h-80c-13 0-24-11-24-24v-48c0-13 11-24 24-24h80c13 0 24 11 24 24zM288 56v48c0 13-11 24-24 24h-80c-13 0-24-11-24-24V56c0-13 11-24 24-24h80c13 0 24 11 24 24zm160 128v48c0 13-11 24-24 24h-80c-13 0-24-11-24-24v-48c0-13 11-24 24-24h80c13 0 24 11 24 24zm0-128v48c0 13-11 24-24 24h-80c-13 0-24-11-24-24V56c0-13 11-24 24-24h80c13 0 24 11 24 24z"></path></svg></span>'+t.localize("More videos");var s=a.querySelector(".vjs-control-bar");s.insertBefore(e,s.firstChild),y=l("div","vjs-more-video vjs-hidden"),e.onclick=function(s){s.preventDefault(),s.stopImmediatePropagation(),i.removeClass(y,"vjs-hidden"),f=!1,b(),k(),i.removeClass(a,"vjs-more-touch"),i.addClass(e,"vjs-hidden")},e.addEventListener("touchstart",function(s){s.stopImmediatePropagation(),i.removeClass(y,"vjs-hidden"),i.addClass(e,"vjs-hidden"),i.addClass(j,"more-anim-touch"),i.addClass(a,"vjs-more-touch"),f=!0,b(),k()},{passive:!0});var S=l("div","vjs-more-header");S.innerHTML="<span>"+t.localize("More videos")+"...</span>";var w=l("div","vjs-more-close");S.appendChild(w),y.appendChild(S),x=l("div","vjs-more-inside"),(g=l("div","vjs-more-line")).innerHTML='</div><div class="vjs-more-arrow vjs-more-arrow-prev vjs-disabled"><div class="vjs-prev"></div></div><div class="vjs-more-arrow vjs-more-arrow-next vjs-disabled"><div class="vjs-next"></div></div>',m=g.querySelector(".vjs-more-arrow-next"),h=g.querySelector(".vjs-more-arrow-prev"),j=l("div","vjs-more-list"),x.appendChild(g),g.appendChild(j),y.appendChild(x),s.insertBefore(y,s.firstChild),w.onclick=w.ontouchstart=function(s){s.preventDefault(),s.stopImmediatePropagation(),i.addClass(y,"vjs-hidden"),i.removeClass(e,"vjs-hidden"),i.removeClass(a,"vjs-more-touch")};var q=.97*t.controlBar.el_.offsetWidth,M=q-80;j.style.left="40px";var z=6;q<1020&&(z=5),q<830&&(z=4),q<600&&(z=3),q<400&&(z=2),c=z,v=1;var I=parseInt(M/z,10),_=parseInt(.5625*I,10);j.style.maxWidth=I*z+"px",g.style.height=_+"px",x.style.height=_+16+"px",C=l("div","more-block"),j.appendChild(C),o=r.length,d=Math.ceil(o/z);for(var W=0;W<o;W++){var H=l("div");H.className=0===W?"more-item-parent vjs-more-first":"more-item-parent",H.style.width=I+"px";var P=l("div");P.className="more-item",H.style.left=W*I+"px",H.appendChild(P),C.appendChild(H),P.innerHTML='<a target="_blank" href="'+r[W].url+'" title="'+r[W].title+'"><span class="more-item-bg" style="background-image:url('+r[W].thumb+');"></span><label>'+r[W].title+"</label><i>"+r[W].duration+"</i></a>"}p=g.querySelector(".vjs-more-arrow-next"),u=g.querySelector(".vjs-more-arrow-prev"),k(),p.onclick=function(e){e.stopImmediatePropagation(),function(){if(m.className.indexOf("vjs-disabled")>-1)return;var e=.97*a.offsetWidth,s=6;e<1020&&(s=5);e<830&&(s=4);e<600&&(s=3);e<400&&(s=2);c=s;var t=j.offsetWidth,r=j.querySelector(".vjs-more-first").offsetWidth*o;d=Math.ceil(o/c),n=Math.ceil(v/c);var l=r-t;if(n===d)return i.addClass(m,"vjs-disabled"),void i.removeClass(h,"vjs-disabled");v=c*(++n-1)+1;var p=(n-1)*t;p>l&&(p=l);y.querySelector(".more-block").style.left="-"+p+"px",n===d&&i.addClass(m,"vjs-disabled");i.removeClass(h,"vjs-disabled")}()},u.onclick=function(e){e.stopImmediatePropagation(),function(){if(h.className.indexOf("vjs-disabled")>-1)return;var e=j.offsetWidth,s=.97*a.offsetWidth,t=6;s<1020&&(t=5);s<830&&(t=4);s<600&&(t=3);s<400&&(t=2);if(c=t,d=Math.ceil(o/c),1===(n=Math.ceil(v/c)))return;v=c*((n-=1)-1)+1;var r=(n-1)*e;y.querySelector(".more-block").style.left="-"+r+"px",1===n&&i.addClass(h,"vjs-disabled");i.removeClass(m,"vjs-disabled")}()}}}),this}function b(){if(null!==y){var e=.97*t.controlBar.el_.offsetWidth,s=e;f?j.style.left=0:(s=e-80,j.style.left="40px");var r=6;e<1020&&(r=5),e<830&&(r=4),e<600&&(r=3),e<400&&(r=2),c=r;var l=parseInt(s/r,10);j.style.maxWidth=l*r+"px";var a=parseInt(.5625*l,10);n>d?(n=d,i.addClass(m,"vjs-disabled")):i.removeClass(m,"vjs-disabled");var b=j.offsetWidth,S=l*o-b,w=(v-1)*l;w>S&&(w=S),C.style.left="-"+w+"px",g.style.height=a+"px",x.style.height=a+16+"px";for(var q=0,M=C.children,z=0;z<M.length;z++)M[z].style.width=l+"px",M[z].style.left=q+"px",q+=l;f?(i.addClass(u,"vjs-hidden"),i.addClass(p,"vjs-hidden")):(i.removeClass(u,"vjs-hidden"),i.removeClass(p,"vjs-hidden"));var I=l*o;j.style.width=I+"px",I<=s&&(i.addClass(h,"vjs-hidden"),i.addClass(m,"vjs-hidden")),k()}}function k(){var e=t.el_.querySelector(".vjs-control-bar"),s=(parseInt(getComputedStyle(e).getPropertyValue("height")),parseInt(getComputedStyle(e).getPropertyValue("bottom")),30),r=y.offsetHeight+5,l=a.offsetWidth;t.el_.querySelector(".vjs-skin-shaka ")&&l>1080&&(s=40,r+=10),t.el_.querySelector(".vjs-skin-treso")&&(l>1080?(s=60,r+=30):(s=50,r+=15)),t.el_.querySelector(".vjs-skin-roundal")&&(s+=5,r+=5),t.el_.querySelector(".vjs-skin-nuevo")&&(s=35),t.el_.querySelector(".vjs-skin-party")&&(l>1080?(s=40,r+=10):(s=30,r+=5)),t.el_.querySelector(".vjs-skin-mockup")&&(l>1080?(s=80,r+=50):(s=60,r+=30)),t.el_.querySelector(".vjs-skin-jwlike")&&(l>1080?(s=45,r+=15):(s=35,r+=5)),t.el_.querySelector(".vjs-skin-chrome")&&(s=40,r+=10),y.style.top=0,a.querySelector(".vjs-more-button").style.top="-"+s+"px",y.style.top="-"+parseInt(r,10)+"px"}};s.registerPlugin("morevideo",function(e){this.ready(function(){t(this,e)})})});