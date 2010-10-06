<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8"/>
	<meta http-equiv="X-UA-Compatible" content="chrome=1"/>
	<title>Finding any duplicate tests or IDs</title>
</head>

<body>
<?php
require_once '../is_email.php';

$emailes		= array();
$ids			= array();

$duplicates		= array();
$duplicates['address']	= array();
$duplicates['id']	= array();

$dirty 			= false;

$document		= new DOMDocument();

$document->load('../test/tests.xml');

$tests		= $document->getElementsByTagName('tests')->item(0);
$version	= $tests->getAttribute("version");
$testList	= $document->getElementsByTagName('test');

for ($i = 0; $i < $testList->length; ++$i) {
	$test		= $testList->item($i);
	$tagList	= $test->childNodes;

	unset($id);

	for ($j = 0; $j < $tagList->length; $j++) {
		$node = $tagList->item($j);
		if ($node->nodeType === XML_ELEMENT_NODE) {
			$name	= $node->nodeName;
			$$name	= $node->nodeValue;
		}
	}

	if (in_array($email, $emailes)) {
		$duplicates['address'][]	= $email;
		$duplicates['addressIDs'][]	= $id;
	} else {
		$emailes[]			= $email;

		//	Add ID if it hasn't got one
		if (!isset($id)) {
			$dirty = true;
			$test->appendChild($document->createElement((string) 'id', $i + 1));
			$test->appendChild($document->createTextNode("\n")); // Just for pretty
		}
	}

	if (in_array($id, $ids)) {
		$duplicates['id'][]	= $id;
	} else {
		$ids[]			= $id;
	}
}

if ($dirty) $document->save('new_tests.xml');

//-$count = $results['count'];

echo "<p><strong>Duplicate addresses</strong></p>\n";

foreach ($duplicates['address'] as $email) {
	echo "<p>$email</p>\n";
}

echo "<br /><p><strong>Duplicate IDs</strong></p>\n";

foreach ($duplicates['id'] as $id) {
	echo "<p>$id</p>\n";
}

?>
</body>
</html>
