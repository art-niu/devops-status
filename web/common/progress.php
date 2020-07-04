<?php
echo ' <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<!-- <meta http-equiv="refresh" content="30" /> -->
<title>IT of Toronto Children\'s Services</title>
<link rel="stylesheet" href="/css/rotatingText.css" type="text/css" />
';

$description = $_GET['desc'];

echo '
</head><body>
<div id="container">
<div id="circle">
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="200px" height="200px" viewBox="0 0 200 200" enable-background="new 0 0 200 200" xml:space="preserve">
<defs>
<path id="circlePath"
d="
M 100, 100
m 60, 0
a -60,60 0 0,0 -120,0
a -60,60 0 0,0 120,0
"
/>
</defs>
<g>
<use xlink:href="#circlePath" fill="#CCEEFF" />
<text fill="#000">
<textPath xlink:href="#circlePath"> ' . $description . '</textPath>
</text>
</g>
</svg>
</div>
</div>
<desc><path id="circlePath" d="M 100, 100 m -60, 0 a 60,60 0 0,0 120,0 a 60,60 0 0,0 -120,0 " /></desc>
</body></html>
';

