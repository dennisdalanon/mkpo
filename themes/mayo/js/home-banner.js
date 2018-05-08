var aSlides = [
  {
    text:'Pellentesque et purus,<br />accumsan arcu ac,<br/>volutpat lorem.',
    image: '2.jpg'
  },
  {
    text:'Fusce nibh ante,<br/>dignissim ut lacus,<br/>ullamcorper pretium.',
    image: '1.jpg'
  },
  {
    text:'Nullam vehicula neque<br /> eget turpis mattis,<br/>ut ipsum.',
    image: '2.jpg'
  }
];
jQuery(document).ready(function(){
  jQuery.each(aSlides, function(i, slide) {
    console.log('/themes/mayo/images/home_banner/"' + slide.image);
    var html = '<div class="slide" id="slide' + (i+1) + '"><img src="/themes/mayo/images/home_banner/' + slide.image + '"><span>' + slide.text + '</span></div>';
    jQuery('.home-banner-inner').append(html)
  });
  var i = 1;
  setInterval(function() {
    jQuery('.slide').removeClass('active');
    jQuery('#slide'+(i%4)).addClass('active');
    i++;
  }, 5000);
});
