<?php
//phpinfo();
$variable = 1;
$literally = 'My $variable will not print!\\n';
print($literally);
echo "This will print in the ", "uses's browser window.";

function customized_greeting ()
{
	print("You are being greeted in a customized way!<BR>");
}
customized_greeting();
$my_greeting = "customized_greeting";
echo $my_greeting;

$var = 1;
exit(isset($var));
