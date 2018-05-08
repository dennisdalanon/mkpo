jQuery.getJSON('/rest/ver/1/pregnancy', function(data) {
  jQuery('.loading').addClass('hidden');
  jQuery('.pregnancy-slider').show();
  var active;
  var perc = 90/data.length;
  jQuery.each(data, function (i, week) {
    active = '';
    if (i === 0) {
      active = ' active';
    }
    appendImage('baby', week, active);
    appendImage('mum', week, active);
    //set text
    if (typeof week['title'][0] != 'undefined') {
        //jQuery('.stage-holder').append('<div class="stage' + active + '" style="left: ' + ((i*perc) + '%') + '">' + week['title'][0].value + '</div>');
        //holder text for client, will need to splice on space or add to api
        jQuery('.stage-holder').append('<div class="stage' + active + '" style="left: ' + ((i*perc) + '%') + '"><span id="week">WEEK</span><span id="number">' + week['field_week_number'][0].value  + '</span></div>');
    }

  });
  function appendImage(target, week, active){
    if (typeof week['field_' + target + '_images'][0] != 'undefined') {
      jQuery('.' + target + ' .image').append('<div class="slide' + active + '"><img title="' + week['field_' + target + '_images'][0].alt + '" src="' + week['field_' + target + '_images'][0].url + '"></div>');
    } else {
      //empty slide holder
      jQuery('.' + target + ' .image').append('<div class="slide' + active + '"></div>');
    }
    if (typeof week['field_' + target + '_text'][0] != 'undefined') {
      jQuery('.' + target + ' .text').append('<div class="slide' + active + '">' + week['field_' + target + '_text'][0].value + '</div>');
    } else {
      jQuery('.' + target + ' .text').append('<div class="slide' + active + '"></div>');
    }
  }
  jQuery('.left').click(function(e){
    e.preventDefault();
    jQuery('.stage.active').fadeOut(200, function(){
      jQuery(this).removeClass('active');
      jQuery(this).prev('.stage').fadeIn(200, function(){
        jQuery(this).addClass('active');
        /*if(!jQuery('.image .slide.active', '.baby').next('.slide').length) {
          jQuery('.right').addClass('hidden');
        }*/
      });
    });
    jQuery('.slide.active').fadeOut(200, function(){
      jQuery(this).removeClass('active');
      jQuery(this).prev('.slide').fadeIn(200, function(){
        jQuery(this).addClass('active');
      });
    });
  });
  jQuery('.right').click(function(e){
    e.preventDefault();
    jQuery('.left').removeClass('hidden');
    jQuery('.stage.active').fadeOut(200, function(){
      jQuery(this).removeClass('active');
      jQuery(this).next('.stage').fadeIn(200, function(){
        jQuery(this).addClass('active');
        /*if(!jQuery('.image .slide.active', '.baby').prev('.slide').length) {
          jQuery('.left').addClass('hidden');
        }*/
      });
    });
    jQuery('.slide.active').fadeOut(200, function(){
      jQuery(this).removeClass('active');
      jQuery(this).next('.slide').fadeIn(200, function(){
        jQuery(this).addClass('active');
      });
    });
  });
});
