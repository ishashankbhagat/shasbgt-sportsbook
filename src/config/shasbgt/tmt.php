<?php


return [

  'tmt_update_interval' => 3000,

  'tmt_event_box_class' => 'sb_event_box',
  'sport_url_attr' => 'data-sport_url',
  'event_url_attr' => 'data-event_url',
  'event_id_attr' => 'data-event_id',

  'tmt_market_box_class' => 'sb_market_box',
  'market_url_attr' => 'data-market_url',

  'tmt_outcome_box_class' => 'sb_outcome_box',
  'outcome_url_attr' => 'data-outcome_url',

  'tmt_outcome_name_class' => 'sb_outcome_name',

  'tmt_odds_val_class' => 'sb_odds_val',

  'tmt_spark_class' => 'flashy',

  'tmt_sb_disabled' => 'sb_disabled',

  'tmt_market_count' => 'sb_event_market_count',

  'bs_item_class' => 'user_selection',

  'sb_disk_name' => 'sr_s3',

  'sb_json_path' => 'sigma/json/:event_id.json',

  'sport_markets' => [
    'default' => [ 'market_id' => [ 1 ] ],
    'srsport21' => [ 'market_id' => [ 340 ] ],
    'srsport1' => [ 'market_id' => [ 1 ] ],
    'srsport2' => [ 'market_id' => [ 219 ] ],
    'srsport5' => [ 'market_id' => [ 186 ] ],
    'srsport22' => [ 'market_id' => [ 186 ] ],
    'srsport117' => [ 'market_id' => [ 186 ] ],
    'srsport23' => [ 'market_id' => [ 186 ] ],
    'srsport13' => [ 'market_id' => [ 186 ] ],
    'srsport19' => [ 'market_id' => [ 186 ] ],
    'srsport20' => [ 'market_id' => [ 186 ] ],
    'srsport3' => [ 'market_id' => [ 251 ] ],
  ],

  'tmt_lock_html' => '/sportsbook/assets/lock.svg',

];
