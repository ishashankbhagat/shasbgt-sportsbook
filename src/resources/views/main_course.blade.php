<style>
  .market_container {
    border: 1px solid #000;
margin: 10px;
padding: 10px;
margin-bottom: 50px;
  }

  .market_name_container {
    font-weight: 900 !important;
font-size: 18px;
  }

  .outcome_container {
    border: 1px solid #000;
margin: 10px;
padding: 10px;
  }
</style>

<script>
var g_producer_id = null;
var g_producer_is_down = false;
var url_event_id = '{{$url_event_id}}';
var do_producer_check = true;


setInterval(function(){
  if (g_producer_is_down == false) {
    getOdds(url_event_id);
  }
},1000);


setInterval(function(){
  if (do_producer_check == true && g_producer_id != null) {
    getProducerStatus(g_producer_id);
  }
},1000);


function getOdds(event_id) {

  let g_event_id_ = event_id.replaceAll(':','_');

  let url = '';
  url = decodeURIComponent('{{\Storage::disk("sr_s3")->url("sigma/json/:event_id.json")}}');
  url = url.replace(':event_id',g_event_id_);
  // url = '{{asset("/feeds/abc.json")}}';

  $('.oddsVal').removeClass('flashy')

  $.ajax({
    type: 'get',
    url: url,
    cache: false,
    success: function(data){
      data = JSON.parse(data);
      g_producer_id = data.other_data.host_producer;
      parseMarkets(event_id,data.odds_data);
    },
    error:function(data){
      checkForNoMarkets();
    }
  });

}


function getProducerStatus(producerId) {
  let url = '';
  url = decodeURIComponent('{{\Storage::disk("sr_s3")->url("sigma/producer/:producerId/status.json")}}');
  url = url.replace(':producerId',producerId);
  url = '{{asset("/feeds/producer.json")}}';

  $.ajax({
    type: 'get',
    url: url,
    cache: false,
    // data: formdata,
    success: function(data){
        // data = JSON.parse(data);
        // data.isDown = "true";
        if (data.isDown == "true") {
          $('.mainContentsBox').hide();
          $('.producerDownMainDiv').show();
          g_producer_is_down = true;
        } else {
          $('.mainContentsBox').show();
          $('.producerDownMainDiv').hide();
          g_producer_is_down = false;
        }
    }
  });
}


function parseMarkets(event_id,markets) {

  let market_url = null;
  let market_block = null;


  $.each(markets, function(i,m){

    if (m == null) {
      return;
    }

    market_url = filterElementUrl(event_id+'/'+m.m+'/'+m.s);
    market_block = $('.sb-mdm[data-market_url='+market_url+']');

    if (m.t != 'Active' && m.t != 'Suspended' && market_block.length > 0) {
      removeMarket(market_url);
    }

    if (m.t == 'Active' || m.t == 'Suspended') {
      if (market_block.length == 0) {
        appendMarket(event_id,m);
      } else if (m.t == 'Suspended') {
        suspendedMarket(market_url);
      } else {
        checkMarketUpdate(event_id,m);
      }
    }

  });

  checkForNoMarkets();
  checkForMarketTabs();
}

function suspendedMarket(market_url) {
  $('.sb-mdm[data-market_url='+market_url+']').addClass('market_status_suspended');
  $('.sb-mdm[data-market_url='+market_url+']').find('.sb-aodm').html('');
}

function appendMarket(event_id,m) {

  let market_url = filterElementUrl(event_id+'/'+m.m+'/'+m.s);
  let market_html = outcome_html = '';
  let market_ele = outcome_ele = null;
  let market_params = outcome_params = {};

  if (m.t == 'Suspended') {
    market_status_class = 'suspend_sec';
  } else {
    market_status_class = '';
  }

    market_params = {};
    market_params.m = m;
    market_params.event_id = event_id;
    market_params.market_url = market_url;
    market_params.market_status_class = market_status_class;

    try {
      market_params.market_filter_class = getMarketTypeFilters(m.m);
    } catch (e) {
      market_params.market_filter_class = getMarketTypeFiltersDefault(m.m);
    }

    market_html = '';

    try {
      market_html = getSingleMarketHtml(market_params);
    } catch (e) {
      market_html = getSingleMarketHtmlDefault(market_params);
    }

    market_ele = $(market_html);

    if (m.t == 'Active') {
      outcome_html = makeOutcomeBlock(event_id,m);
    }

    market_ele.find('.sb-aodm').html(outcome_html);

    if ($('.marketBox_'+m.m).length == 0) {
        marketBoxHtml = '<div class="marketBox marketBox_'+m.m+'"></div>';
        $('.sb-amdm').append(marketBoxHtml);
    }

    $('.marketBox_'+m.m).append(market_ele)

}

function makeOutcomeBlock(event_id,m) {

  let ticket_status = '';

  let outcome_html = '';

  let outcome_params = {};

  let market_url = filterElementUrl(event_id+m.m+m.s);

  $.each(m.o,function(j,o){

    outcome_url = '';
    outcome_url+=market_url+'/'+o.o;
    outcome_url=filterElementUrl(outcome_url);

    if (o.t == 'Active' && m.t == 'Active') {
        ticket_status = 'accept_tickets';
    }else{
        ticket_status = 'reject_tickets';
    }

    outcome_params = {};
    outcome_params.m = m;
    outcome_params.o = o;
    outcome_params.event_url = filterElementUrl(event_id);
    outcome_params.market_url = market_url;
    outcome_params.outcome_url = outcome_url;
    outcome_params.ticket_status = ticket_status;

    try {
      outcome_html+=getSingleOutcomeHtml(outcome_params);
    } catch (e) {
      outcome_html+=getSingleOutcomeHtmlDefault(outcome_params);
    }

  });

  return outcome_html;
}


function checkMarketUpdate(event_id,m) {

  let market_url = outcome_url = '';

  market_url = filterElementUrl(event_id+'/'+m.m+'/'+m.s);

  let marketBlock = $('.sb-mdm[data-market_url='+market_url+']');

  marketBlock.removeClass('suspend_sec');

  if (marketBlock.find('.sb-odm').length == 0) {
    html = makeOutcomeBlock(event_id,m);
    marketBlock.find('.sb-aodm').html(html);
  }

  $.each(m.o,function(j,o){

    outcome_url = '';
    outcome_url+=market_url+'/'+o.o;
    outcome_url=filterElementUrl(outcome_url);

    outcomeBlock = marketBlock.find('.sb-odm[data-outcome_url='+outcome_url+']');

    old_odds = outcomeBlock.find('.oddsVal').html();

    if (old_odds) {
      old_odds=old_odds.trim();
    }

    old_odds = parseFloat(old_odds);

    if (o.d=='NaN') {
      new_odds = '-';
    }else{
      new_odds = o.d;
    }

    if (old_odds != new_odds) {

      outcomeBlock.find('.oddsVal').addClass('flashy')
      outcomeBlock.find('.oddsVal').html(new_odds);

    }

    if (o.t == 'Active') {
      outcomeBlock.removeClass('reject_tickets').addClass('accept_tickets');
    } else {
      outcomeBlock.removeClass('accept_tickets').addClass('reject_tickets');
    }

  });

}

function checkForNoMarkets() {
  if ($('.sb-mdm').length == 0) {
    $('.sb-amdm').hide()
    $('.noDataFound').show()
  } else {
    $('.sb-amdm').show()
    $('.noDataFound').hide()
  }
}

function producerDown() {
  if ($('.sb-mdm').length == 0) {
    $('.sb-amdm').hide()
    $('.noDataFound').show()
  } else {
    $('.sb-amdm').show()
    $('.noDataFound').hide()
  }
}


function checkForMarketTabs() {
  $('.filterContainer').show()
  let tabs = @json($market_type_filters);
  $.each(tabs,function(i,e){
    if ($('.mh_'+e.slug).length == 0) {
      $('.__markettypeFilter[data-slug='+e.slug+']').hide();
    } else {
      $('.__markettypeFilter[data-slug='+e.slug+']').show();
    }
  });
  $('.__markettypeFilter[data-slug=all]').show();
  if ($('.mh_others').length == 0) {
    $('.__markettypeFilter[data-slug=others]').hide();
  } else {
    $('.__markettypeFilter[data-slug=others]').show();
  }
  if ($('.__markettypeFilterItems:visible').length == 0) {
    $('.__markettypeFilter[data-slug=all]').find('.Tab__text___3GNyH').html('All Markets');
    $('.__markettypeFilter[data-slug=others]').hide();
  }
}

$('body').on('click','.__markettypeFilter',function(e){

  let slug = $(this).attr('data-slug');

  $('.__markettypeFilter').removeClass('active');
  $(this).addClass('active');

  if (slug == 'all') {
    $('.sb-mdm').show();
  } else {
    $('.sb-mdm').hide();
    $('.mh_'+slug).show();
  }

  scrollMarketFilter(slug);

});

function scrollMarketFilter(slug) {
  let numItemsBeforeActive = null;
  let amtToMove = null;
  numItemsBeforeActive = $('.__markettypeFilter[data-slug="'+slug+'"]').index();
  for (let i = 0; i < numItemsBeforeActive; i++) {
    amtToMove += $(".__markettypeFilter").eq(i).outerWidth(true);
  }
  $(".__allMarkettypeFilterContainer").animate(
    {
      scrollLeft: amtToMove - 90,
    },
    1000
    );
  }

function getMarketTypeFiltersDefault(market_id) {
  return '';
}

function getSingleMarketHtmlDefault(params) {
  return `
  <div class="market_container sb-mdm ${params.market_status_class} ${params.market_filter_class}" data-event_id="${params.event_id}" data-market_url="${params.market_url}" data-market_id="${params.m.m}" data-specifiers="${params.m.s}">
    <div class="row">
      <div class="col-12">
        <div class="market_name_container">
        ${params.m.n}
        </div>
      </div>
    </div>
    <div class="row sb-aodm">

    </div>
  </div>
  `;
}

function getSingleOutcomeHtmlDefault(params) {
  return `
  <div class="col-12 outcome_container sb-odm ${params.ticket_status}" data-event_url="${params.event_url}" data-market_url="${params.market_url}" data-outcome_url="${params.outcome_url}" data-outcome_id="${params.o.o}">
    <div class="row">
      <div class="col-8">
      <span class="outcomeDescription" data-otname="${params.o.n}">${params.o.n}</span>
      </div>
      <div class="col-4">
       <span class="oddsVal sb_odds_val">${params.o.d}</span>
      </div>
    </div>
  </div>
  `;
}
</script>
