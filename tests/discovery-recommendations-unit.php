<?php
define('ABSPATH',__DIR__); $meta=[];
function absint($x){return abs((int)$x);} function get_post_meta($id,$key,$single){return $GLOBALS['meta'][$id][$key]??'';} function ag_site_name(){return 'tunetofond';}
require __DIR__.'/../plugin/discovery.php';
function song($id,$artist,$themes=[],$moods=[]){return ['id'=>$id,'artist'=>$artist,'themes'=>array_fill_keys($themes,'Theme'),'moods'=>array_fill_keys($moods,'Feeling')];}
$n=0;function ok($value,$name){if(!$value){fwrite(STDERR,'FAIL: '.$name.PHP_EOL);exit(1);} $GLOBALS['n']++;echo 'PASS: '.$name.PHP_EOL;}
$current=song(1,10,['relationships'],['reflection']);
$catalog=[1=>$current,2=>song(2,20,['relationships'],['reflection']),3=>song(3,10,['relationships']),4=>song(4,30,['relationships']),5=>song(5,40,[],['reflection']),6=>song(6,50,['unrelated']),7=>song(7,60,['unrelated']),8=>song(8,70,['unrelated'])];
$meta[1]['_ag_related']='7';$meta[10]['_ag_related']='50';
$prefs=['feeling'=>'any','theme'=>'any'];$same=['feeling'=>'any','theme'=>'same'];
$feature=ag_discovery_recommend($current,$catalog,$same,[],1)[0];ok((bool)array_intersect_key($feature['themes'],$current['themes']),'Featured result shares a current theme');
$alternatives=ag_discovery_recommend($current,$catalog,$prefs,[$feature['id']],3,true);
$ids=array_column($alternatives,'id');ok(!in_array($feature['id'],$ids,true) && !in_array(1,$ids,true),'Alternatives omit featured and current songs');ok(count($ids)===3 && count(array_unique($ids))===3,'Three distinct alternatives when enough relevant artists exist');
ok(count(array_filter($alternatives,fn($s)=>$s['artist']===10))===1,'One relevant same-artist alternative reserved');ok(count(array_unique(array_column($alternatives,'artist')))===count($ids),'Alternatives diversify remaining artists');
ok(!in_array(8,$ids,true),'Unrelated candidate is not used');
$onlyUnrelated=[1=>$current,8=>$catalog[8]];ok(ag_discovery_recommend($current,$onlyUnrelated,$prefs)===[],'No unrelated fallback in a sparse catalog');
$relatedOnly=[1=>$current,6=>$catalog[6],7=>$catalog[7]];ok(ag_discovery_recommend($current,$relatedOnly,$same,[],1)===[],'Editorial links cannot bypass featured shared-theme rule');ok(count(ag_discovery_recommend($current,$relatedOnly,$prefs))===2,'Explicit editorial connections can be alternatives');
$noThemes=song(9,90,[],['reflection']);ok(ag_discovery_recommend($noThemes,$catalog,$same,[],1)===[],'No themes means no arbitrary featured result');
$trail=[2,3,4];$rows=ag_discovery_recommend($current,$catalog,$prefs,$trail,3,true);ok(!array_intersect($trail,array_column($rows,'id')),'Recently visited songs are excluded');
$one=song(11,10,['relationships']);$two=song(12,10,['relationships']);$sameArtist=[1=>$current,11=>$one,12=>$two];$rows=ag_discovery_recommend($current,$sameArtist,$prefs,[],3,true);ok(count($rows)===1,'At most one same-artist alternative even if catalog is sparse');
$rows=ag_discovery_recommend($current,$catalog,['feeling'=>'reflection','theme'=>'any'],[2],3,true);ok(array_reduce($rows,fn($a,$s)=>$a&&isset($s['moods']['reflection']),true),'Alternative feeling filter remains strict');
$rows=ag_discovery_recommend($current,$catalog,['feeling'=>'any','theme'=>'unrelated'],[],3,true);ok(array_reduce($rows,fn($a,$s)=>$a&&isset($s['themes']['unrelated']),true),'Alternative theme selection stays strict');
ok(ag_discovery_recommend($current,$catalog,$prefs,[],3,true)===ag_discovery_recommend($current,$catalog,$prefs,[],3,true),'Ordering is deterministic');
$rows=ag_discovery_recommend($current,$catalog,$prefs,[],3);ok($rows[0]['id']===2,'Shared theme and feeling rank above mood-only or editorial-only choices');
$default=ag_discovery_defaults($current,$catalog);ok($default['theme']==='same','Default alternatives follow the current theme');
$ui=file_get_contents(__DIR__.'/../theme/discovery-ui.php');ok(str_contains($ui,"\$song = \$context['featured'] ?? null;"),'Renderer uses separate featured selection');ok(!str_contains($ui,"\$song = \$context['results'][0] ?? null;"),'Renderer no longer duplicates first alternative');

$base=song(100,10,[],['reflection']);
$pool=[100=>$base,101=>song(101,10,[],['reflection']),102=>song(102,10),103=>song(103,20,['other']),104=>song(104,30,[],['reflection'])];
$f=ag_discovery_featured($base,$pool);ok($f['id']===101 && $f['match_type']==='same_artist','No shared theme promotes a same-artist song');
ok($f['different_artist']===false && !str_contains($f['reason'],'theme'),'Fallback reason never claims a shared theme or different artist');
$alts=ag_discovery_recommend($base,$pool,['theme'=>'any','feeling'=>'any'],[$f['id']],3,true);ok(!in_array($f['id'],array_column($alts,'id'),true),'Promoted artist fallback is excluded from alternatives');
$f=ag_discovery_featured($base,$pool,[101]);ok($f['id']===102,'Fallback respects recently visited songs');
ok(ag_discovery_featured($base,$pool,[101,102])===null,'No eligible same-artist song keeps an honest empty state');
ok(ag_discovery_featured($base,[100=>$base,103=>$pool[103]])===null,'An unrelated different artist is never the featured fallback');
$feature=ag_discovery_featured($current,$catalog);ok($feature['match_type']==='shared_theme','Shared-theme match always takes priority over artist fallback');
$untagged=song(110,10);$meta[110]['_ag_related']='102';$f=ag_discovery_featured($untagged,[110=>$untagged,101=>$pool[101],102=>$pool[102]]);ok($f['id']===102,'Curated same-artist choice breaks untagged fallback ties meaningfully');
ok(ag_discovery_featured($base,[$base['id']=>$base])===null,'Current song cannot be its own fallback');
ok(ag_discovery_featured($base,$pool)===ag_discovery_featured($base,$pool),'Fallback ordering is deterministic');

echo $n.' checks passed.'.PHP_EOL;
