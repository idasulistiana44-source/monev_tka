<?php

$pageName=$pageName??'';
$pageView=$pageView??'';
$pageData=$pageData??[];
$pageAsset=$pageAsset??null;
if(str_starts_with($pageName,'visits/instrument')){
    $pageAsset='visits-instrument';
}elseif(str_starts_with($pageName,'schools/')){
    $pageAsset='schools';
}elseif(str_starts_with($pageName,'users/')){
    $pageAsset='users';
}elseif(str_starts_with($pageName,'assignments/')){
    $pageAsset='assignments';
}elseif(str_starts_with($pageName,'visits/')){
    $pageAsset='visits';
}elseif(str_starts_with($pageName,'dashboard/')){
    $pageAsset='dashboard';
}elseif(str_starts_with($pageName,'instruments/')){
    $pageAsset='instruments';
}elseif(str_starts_with($pageName,'monev/index')){
    $pageAsset='monev';
}elseif(str_starts_with($pageName,'reports/index')){
    $pageAsset='reports';
}elseif(str_starts_with($pageName,'monev/index')){
    $pageAsset='monev';
}

?>
<?= view('layout/header',['title'=>$title??'Monev TKA','pageAsset'=>$pageAsset]) ?>
<?= view('layout/navbar') ?>
<?= view('layout/sidebar') ?>
<main class="app-main">
    <?= view($pageView,$pageData) ?>
</main>
<?= view('layout/footer',['pageAsset'=>$pageAsset]) ?> 

