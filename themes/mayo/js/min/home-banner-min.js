var aSlides=[{text:"Pellentesque et purus eleifend, <br/>accumsan arcu ac,<br/>volutpat lorem.",image:"2.jpg"},{text:"Fusce nibh ante,<br/>dignissim ut lacus sed,<br/>ullamcorper pretium justo.",image:"1.jpg"},{text:"Nullam vehicula neque eget turpis mattis,<br/>ut dignissim ipsum condimentum.",image:"2.jpg"}];jQuery(document).ready(function(){jQuery.each(aSlides,function(e,a){console.log('/themes/mayo/images/home_banner/"'+a.image);var i='<div class="slide" id="slide'+(e+1)+'"><img src="/themes/mayo/images/home_banner/'+a.image+'"><span>'+a.text+"</span></div>";jQuery(".home-banner-inner").append(i)});var e=1;setInterval(function(){jQuery(".slide").removeClass("active"),jQuery("#slide"+e%4).addClass("active"),e++},5e3)});