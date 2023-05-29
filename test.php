<?php
require 'vendor/autoload.php';

echo Tiacx\ChineseConverter::convert('心里', 's2t'), "\n";
echo Tiacx\ChineseConverter::convert('心里', 's2tw'), "\n";
echo Tiacx\ChineseConverter::convert('心里', 's2hk'), "\n";
echo Tiacx\ChineseConverter::convert('心裏', 't2s'), "\n";
echo Tiacx\ChineseConverter::convert('心裡', 'tw2s'), "\n";
echo Tiacx\ChineseConverter::convert('心裏', 'hk2s'), "\n";

print_r(Tiacx\ChineseConverter::convertGetAll('心里'));
print_r(Tiacx\ChineseConverter::convertGetAll('心裡'));
print_r(Tiacx\ChineseConverter::convertGetAll('心裏'));
