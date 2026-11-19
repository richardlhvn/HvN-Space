<?php
$admin = false; $callback = null; $lookups = [];
function add_action($hook, $fn) { $GLOBALS['callback'] = $fn; }
function is_admin() { return $GLOBALS['admin']; }
function sanitize_title($s) { return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $s), '-')); }
function wp_unslash($s) { return stripslashes($s); }
function sanitize_text_field($s) { return trim(strip_tags($s)); }
function ag_taxonomy_filter_slugs($tax, $s) { return [sanitize_title($s)]; }
function get_posts($args) { $GLOBALS['lookups'][] = $args; return ['norway'=>[10], 'sweden'=>[20]][$args['tax_query'][0]['terms']] ?? []; }
class FixtureQuery {
    public $vars; public $type; public $main; public $search;
    function __construct($type='song', $vars=[], $main=true, $search=false) { $this->type=$type; $this->vars=$vars; $this->main=$main; $this->search=$search; }
    function is_main_query() { return $this->main; }
    function is_search() { return $this->search; }
    function is_post_type_archive($types) { return in_array($this->type, (array)$types, true); }
    function is_tax($tax) { return false; }
    function set($k,$v) { $this->vars[$k]=$v; }
    function get($k) { return $this->vars[$k] ?? null; }
}
// Extract the real registered callback, so this fixture tests repository source.
$core = file_get_contents(__DIR__.'/../plugin/afterglow-core.php');
$start = strpos($core, "add_action('pre_get_posts', function (\$q)");
$end = strpos($core, "\n});", $start);
if ($start === false || $end === false) { throw new RuntimeException('Archive query hook not found.'); }
eval(substr($core, $start, $end + 5 - $start));
$passed=0;
function verify($condition,$name) { if (!$condition) { fwrite(STDERR, 'FAIL: '.$name.PHP_EOL); exit(1); } $GLOBALS['passed']++; echo 'PASS: '.$name.PHP_EOL; }
function run_query($get,$type='song',$vars=[],$main=true) { $_GET=$get; $q=new FixtureQuery($type,$vars,$main); ($GLOBALS['callback'])($q); return $q; }
$q=run_query(['filter_country'=>'norway']);
verify(($q->get('meta_query')[0]['key'] ?? '') === '_ag_artist_id' && $q->get('meta_query')[0]['value'] === [10], 'Norway songs filter uses the linked Norwegian artist');
verify($q->get('tax_query') === null, 'Song country does not require a direct song country term');
$args=$lookups[0]; verify($args['post_type']==='artist' && $args['post_status']==='publish' && $args['fields']==='ids', 'Country lookup only requests published artist IDs');
$q=run_query(['filter_country'=>'norway','filter_genre'=>'pop','filter_mood'=>'reflection','q'=>'Think']);
verify(count($q->get('tax_query'))===3 && $q->get('tax_query')['relation']==='AND', 'Genre and mood remain intersected with country');
verify($q->get('s')==='Think' && $q->get('search_columns')===['post_title'], 'Search remains active with country and taxonomy filters');
$q=run_query(['filter_country'=>'norway'],'artist'); verify($q->get('tax_query')[0]['taxonomy']==='country' && $q->get('meta_query')===null, 'Artist country filtering remains unchanged');
$q=run_query(['filter_country'=>'unknown']); verify($q->get('post__in')===[0], 'Unknown country fails closed even for unassigned artist references');
$q=run_query(['filter_country'=>['norway']]); verify($q->get('meta_query')===null && $q->get('tax_query')===null, 'Malformed array country is ignored');
$q=run_query([]); verify($q->get('meta_query')===null, 'Unfiltered song archive remains unrestricted by country');
$existing=['relation'=>'OR',['key'=>'a','value'=>'1'],['key'=>'b','value'=>'2']];
$q=run_query(['filter_country'=>'norway'],'song',['meta_query'=>$existing,'post__not_in'=>[99],'paged'=>2]);
verify($q->get('meta_query')['relation']==='AND' && $q->get('meta_query')[0]===$existing, 'Existing OR metadata conditions stay grouped under AND');
verify($q->get('post__not_in')===[99] && $q->get('paged')===2, 'Visibility exclusions and pagination survive');
$admin=true; $q=run_query(['filter_country'=>'norway']); verify($q->vars===[], 'Administration queries are untouched'); $admin=false;
$q=run_query(['filter_country'=>'norway'],'song',[],false); verify($q->vars===[], 'Secondary queries are untouched');
function _n($one,$many,$n,$domain) { return $n===1 ? $one : $many; }
function number_format_i18n($n) { return number_format($n); }
function esc_html($s) { return htmlspecialchars((string)$s); }
$archive=file_get_contents(__DIR__.'/../theme/archive.php'); preg_match('/echo esc_html\(sprintf\(_n[\s\S]*?;/', $archive,$m);
foreach([0=>'0 discoveries',1=>'1 discovery',2=>'2 discoveries',1000=>'1,000 discoveries'] as $count=>$expected) { $wp_query=(object)['found_posts'=>$count]; ob_start(); eval($m[0]); $actual=ob_get_clean(); verify($actual===$expected, 'Result count: '.$expected); }
echo $passed.' regression checks passed.'.PHP_EOL;
