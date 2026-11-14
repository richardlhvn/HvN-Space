<?php
require __DIR__ . '/fingerprint.php';
$manifest=json_decode(file_get_contents(getenv('TF_MIGRATION_DIR').'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
$actual=tf_hosting_fingerprint();
if ($actual !== $manifest['expected']) {
    $different=[];
    foreach ($manifest['expected']['records'] as $id=>$hash) { if (($actual['records'][$id]??null)!==$hash) { $different[]=$id; } }
    $options=[];
    foreach ($manifest['expected']['options'] as $key=>$value) { if (($actual['options'][$key]??null)!==$value) { $options[]=$key; } }
    throw new RuntimeException('Preservation verification failed. Post IDs: '.implode(',',$different).'; option keys: '.implode(',',$options));
}
WP_CLI::success(count($actual['records']).' complete post/meta/relationship fingerprints and all catalog, theme, taxonomy and visibility checks match.');
