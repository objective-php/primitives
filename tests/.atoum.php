<?php
use mageekguy\atoum;
use mageekguy\atoum\report\fields\runner\failures\execute\macos;

define('COVERAGE_TITLE', 'Objective PHP / Primitives');
define('COVERAGE_DIRECTORY', __DIR__ . '/coverage');
define('COVERAGE_WEB_PATH', 'file://' . __DIR__ . '/coverage/index.html');
define('COLORIZED', true);

if(false === is_dir(COVERAGE_DIRECTORY))
{
    mkdir(COVERAGE_DIRECTORY, 0777, true);
}

$stdOutWriter = new atoum\writers\std\out();
$cliReport = new atoum\reports\realtime\cli();
$cliReport->addWriter($stdOutWriter);
$cliReport->addField(new macos\phpstorm());

$coverageField = new atoum\report\fields\runner\coverage\html(COVERAGE_TITLE, COVERAGE_DIRECTORY);
$coverageField->setRootUrl(COVERAGE_WEB_PATH);
$cliReport->addField($coverageField, array(atoum\runner::runStop));

if(COLORIZED)
{
    $cliReport->addField(new atoum\report\fields\runner\atoum\logo());
    $cliReport->addField(new atoum\report\fields\runner\result\logo());
}

$runner->addReport($cliReport);
