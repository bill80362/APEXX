<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__."/app")->name('*.php');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true, // 使用 PSR-12 的程式碼風格標準
    'strict_param' => true, // 開啟嚴格模式，禁止 PHP 自行轉換類型
    'array_syntax' => ['syntax' => 'short'], // 陣列宣告使用
])->setFinder($finder);