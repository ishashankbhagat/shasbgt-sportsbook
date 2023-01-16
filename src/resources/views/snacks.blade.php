<style>
.flashy {
      animation: sparkBack .5s ease!important;
  }
  @keyframes sparkBack {
      0% {
          background-color: #B7D5EB;
          color: #1E1E1E
      }
      50% {
          background-color: #F8E71C;
          color: #fff
      }
      100% {
          background-color: #B7D5EB;
          color: #1E1E1E
      }
  }
</style>

<script>
var tmt_update_interval = '{{config("shasbgt.tmt.tmt_update_interval")}}';

var tmt_event_box_class = '{{config("shasbgt.tmt.tmt_event_box_class")}}';
var sport_url_attr = '{{config("shasbgt.tmt.sport_url_attr")}}';
var event_url_attr = '{{config("shasbgt.tmt.event_url_attr")}}';
var event_id_attr = '{{config("shasbgt.tmt.event_id_attr")}}';

var tmt_market_box_class = '{{config("shasbgt.tmt.tmt_market_box_class")}}';
var market_url_attr = '{{config("shasbgt.tmt.market_url_attr")}}';

var tmt_outcome_box_class = '{{config("shasbgt.tmt.tmt_outcome_box_class")}}';
var outcome_url_attr = '{{config("shasbgt.tmt.outcome_url_attr")}}';

var tmt_outcome_name_class = '{{config("shasbgt.tmt.tmt_outcome_name_class")}}';

var tmt_odds_val_class = '{{config("shasbgt.tmt.tmt_odds_val_class")}}';

var tmt_spark_class = '{{config("shasbgt.tmt.tmt_spark_class")}}';

var tmt_sb_disabled = '{{config("shasbgt.tmt.tmt_sb_disabled")}}';

var tmt_market_count = '{{config("shasbgt.tmt.tmt_market_count")}}';

var bs_item_class = '{{config("shasbgt.tmt.bs_item_class")}}';

var tmt_odds_json_base_url = decodeURIComponent('{{\Storage::disk(config("shasbgt.tmt.sb_disk_name"))->url(config("shasbgt.tmt.sb_json_path"))}}');

var sport_markets = @json(config('shasbgt.tmt.sport_markets'));

var tmt_lock_html = '{{config("shasbgt.tmt.tmt_lock_html")}}';



setInterval(fetchTmtList,tmt_update_interval);


function fetchTmtList() {
  $('.'+tmt_event_box_class+':visible').each(function(i,e){
    if (checkTmtIsInViewport($(this))) {
      getTmtMarketsOdds($(this).attr(event_id_attr));
    }
  });
}



function filterTmtElementUrl(str) {
  str=str.replaceAll('_','');
  str=str.replaceAll('/','');
  str=str.replaceAll('{','');
  str=str.replaceAll('=','');
  str=str.replaceAll(', ','');
  str=str.replaceAll('}','');
  str=str.replaceAll('.','');
  str=str.replaceAll(':','');
  str=str.replaceAll('+','');
  return str;
}



function checkTmtIsInViewport(that) {
  if ($(that).length == 0) {
    return false;
  }
  var elementTop = $(that).offset().top;
  var elementBottom = elementTop + $(that).outerHeight();
  var viewportTop = $(window).scrollTop();
  var viewportBottom = viewportTop + $(window).height();
  return elementBottom > viewportTop && elementTop < viewportBottom;
}



function getTmtMarketsOdds(event_id) {

  let event_id_ = event_id.replaceAll(':','_');

  let sport_url = market_url = outcome_url = '';

  let event_url = filterTmtElementUrl(event_id);

  let event_odds_url = tmt_odds_json_base_url.replace(':event_id',event_id_);

  let odd_len=0;

  let tmt_event_box = $('.'+tmt_event_box_class+'['+event_url_attr+'='+event_url+']');

  $.ajax({
    type: 'get',
    url: event_odds_url,
    cache: false,
    success: function(data){

      data = JSON.parse(data);

      sport_url = tmt_event_box.attr(sport_url_attr);

      $.each(data.odds_data,function(i,m){

        if (m == null) {
          return;
        }

        market_url=filterTmtElementUrl(event_id+'/'+m.m+'/'+m.s);

        tmt_market_box = $('.'+tmt_market_box_class+'['+market_url_attr+'='+market_url+']');

        inBetSlip = $('.'+bs_item_class+'['+market_url_attr+'='+market_url+']').length;
        if (inBetSlip > 0) {
          try {
            parseMarketBetslip(event_id,m)
          } catch (e) {
            console.log('Betslip parse package is missing from your project. Please install to update betslip data');
          }
        }

        if (m.t != 'Active') {
          tmt_market_box.find('.'+tmt_odds_val_class).html(tmt_lock_html);
          tmt_market_box.find('.'+tmt_outcome_box_class).addClass(tmt_sb_disabled);
        }

        if (m.t == 'Active') {
          if (sport_markets[sport_url] && sport_markets[sport_url].market_id.includes(m.m)) {
            updateTmtMarket(event_id,m);
          } else if (!sport_markets[sport_url] && sport_markets['default'].market_id.includes(m.m)) {
            updateTmtMarket(event_id,m);
          }
        }

        if (m.t == 'Active' || m.t == 'Suspended') {
          odd_len+=1;
        }

      });

      $('.'+tmt_event_box_class+'['+event_url_attr+'='+event_url+']').find('.'+tmt_market_count).html('+'+odd_len);

    }
  });

}

function updateTmtMarket(event_id,m) {

  let market_url = filterTmtElementUrl(event_id+m.m+m.s);

  $.each(m.o,function(io,o){

      outcome_url = '';
      outcome_url+=market_url+'/'+o.o;
      outcome_url=filterTmtElementUrl(outcome_url);

      tmt_outcome_box = $('.'+tmt_outcome_box_class+'['+outcome_url_attr+'='+outcome_url+']');

      old_odds = tmt_outcome_box.find('.'+tmt_odds_val_class).html();

      if(o.d != old_odds){

        tmt_outcome_box.find('.'+tmt_odds_val_class).html(o.d);

        tmt_outcome_box.find('.'+tmt_outcome_name_class).html(o.n);

        sparkTmtOutcomeBox(tmt_outcome_box);

      }

      tmt_outcome_box.removeClass(tmt_sb_disabled)

    });

}

function sparkTmtOutcomeBox(tmt_outcome_box) {
  $(tmt_outcome_box).addClass(tmt_spark_class)
  setTimeout(function(){
    $(tmt_outcome_box).removeClass(tmt_spark_class);
  },600);
}

</script>
