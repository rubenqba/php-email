<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="chrome=1"/>
	<title>Testing is_email()</title>
	<link rel="stylesheet" href="tests.css"/>
</head>

<body>
	<h2 id="top">RFC-compliant email address validation</h2>
<?php
// Incorporates formatting suggestions from Daniel Marschall (uni@danielmarschall.de)
require_once '../is_email.php';
require_once '../extras/is_email_statustext.php';
is_email(''); // Ensure constants are defined

/*.string.*/ function innerHTML($node, $document) {
	if ($node->childNodes->length > 1) {
		$html = '';
		foreach ($node->childNodes as $childNode) $html .= $document->saveXML($childNode);	// May need LIBXML_NOEMPTYTAG if you're worried about <script/>
	} else {
		$html = $node->nodeValue;
	}

	return $html;
}

/*.array[string]mixed.*/ function unitTest ($email, $expected_category = -1, $expected_diagnosis = -1) {
	$result					= /*.(array[string]mixed).*/ array();
	$address_parts				= /*.(array[string]string).*/ array();
	$diagnosis				= is_email($email, true, ISEMAIL_DIAGNOSE, $address_parts);
	$category				= ($diagnosis === 0) ? 0 : (int) pow(2, ceil(log($diagnosis + 1, 2))) - 1;	// Calculate category from diagnosis: is there a better way to do this?

	$result[ISEMAIL_COMPONENT_LOCALPART]	= $address_parts[ISEMAIL_COMPONENT_LOCALPART];
	$result[ISEMAIL_COMPONENT_DOMAIN]	= $address_parts[ISEMAIL_COMPONENT_DOMAIN];
	$result['category']			= $category;
	$result['diagnosis']			= $diagnosis;
	$result['expected_category']		= $expected_category;
	$result['expected_diagnosis']		= $expected_diagnosis;
	$result['constant_category']		= is_email_statustext($category,			ISEMAIL_STATUSTEXT_CONSTANT);
	$result['constant_diagnosis']		= is_email_statustext($diagnosis,			ISEMAIL_STATUSTEXT_CONSTANT);
	$result['constant_expected_category']	= is_email_statustext($expected_category,		ISEMAIL_STATUSTEXT_CONSTANT);
	$result['constant_expected_diagnosis']	= is_email_statustext($expected_diagnosis,	ISEMAIL_STATUSTEXT_CONSTANT);
	$result['text_category']		= is_email_statustext($category,			ISEMAIL_STATUSTEXT_EXPLANATORY);
	$result['text_diagnosis']		= is_email_statustext($diagnosis,			ISEMAIL_STATUSTEXT_EXPLANATORY);
	$result['smtpcode']			= is_email_statustext($diagnosis,			ISEMAIL_STATUSTEXT_SMTPCODE);

	$result['alert_category']		= ($expected_category	=== -1) ? false : ($category	!== $expected_category);
	$result['alert_diagnosis']		= ($expected_diagnosis	=== -1) ? false : ($diagnosis	!== $expected_diagnosis);

	return $result;
}

/*.string.*/ function all_tests($test_set = 'tests.xml') {
	$document = new DOMDocument();
	$document->load($test_set);

	// Get version
	$suite		= $document->getElementsByTagName('tests')->item(0);
	$version	= ($suite->hasAttribute('version')) ? $suite->getAttribute('version') : '';
	$nodeList	= $document->getElementsByTagName('description');
	$description	= ($nodeList->length === 0) ? '' : "\t" . '<p class="rubric">' . innerHTML($nodeList->item(0), $document) . '</p>';

	echo <<<PHP
	<h3>Test package version $version</h3>
$description

PHP;

	$testList			= $document->getElementsByTagName('test');
	$testCount			= $testList->length;
	$statistics_count		= 0;
	$statistics_alert_category	= 0;
	$statistics_alert_diagnosis	= 0;
	$html				= '';

	// Can't store ASCII or Unicode characters below U+0020 in XML file so we put a token in the XML
	// (except for HTAB, CR & LF)
	// The tokens we have chosen are the Unicode Characters 'SYMBOL FOR xxx' (U+2400 onwards)
	// Here we convert the token to the actual character.
	$needles		= array();
	$substitutes		= array();

	for ($i = 0; $i < 32; $i++) {
		$needles[]	= mb_convert_encoding('&#' . (string) (9216 + $i) . ';', 'UTF-8', 'HTML-ENTITIES');	// PHP bug doesn't allow us to use hex notation (http://bugs.php.net/48645)
		$substitutes[]	= chr($i);
	}

	for ($i = 0; $i < $testCount; $i++) {
		$test_node	= $testList->item($i);
		$id		= $test_node->hasAttribute('id') ? $test_node->getAttribute('id') : '';
		$tagList	= $test_node->childNodes;

		$address	= '';
		$category	= -1;
		$diagnosis	= -1;
		$comment	= '';
		unset($warning);

		for ($j = 0; $j < $tagList->length; $j++) {
			$node = $tagList->item($j);

			if ($node->nodeType === XML_ELEMENT_NODE) {
				$name	= $node->nodeName;
				$$name	= innerHTML($node, $document);
			}
		}

		$category		= (int) $category;
		$diagnosis		= (int) $diagnosis;
		$email			= str_replace($needles, $substitutes, $address);
		$comment		= str_replace($needles, $substitutes, $comment);

		$result			= unitTest($email, $category, $diagnosis);	// This is why we're here

		$category_result	= $result['category'];
		$diagnosis_result	= $result['diagnosis'];
		$constant_category	= $result['constant_category'];
		$constant_diagnosis	= $result['constant_diagnosis'];
		$text			= $result['text_diagnosis'];

		$comments		= /*.(array[int]string).*/ array();

		if (strlen($comment) !== 0)	$comments[] = '<em>' . stripslashes($comment) . '</em>';
		if ($text !== '')		$comments[] = stripslashes($text);

		if ($result['alert_category']) {
			$class_category	= ' unexpected';
			$rag_category	= ' red';
			$comments[]	= 'Expected category was ' . $result['constant_expected_category'];
		} else {
			$class_category	= '';
			$rag_category	= '';
		}

		if ($result['alert_diagnosis']) {
			$class_diagnosis= ' unexpected';
			$rag_diagnosis	= ' amber';
			$comments[]	= 'Expected diagnosis was ' . $result['constant_expected_diagnosis'];
		} else {
			$class_diagnosis= '';
			$rag_diagnosis	= '';
		}

		$comments_html	= implode('<br/>', $comments);
		$address_length	= (strlen($address) > 41) ? 'long' : 'short';
		$address_html	= str_replace(array(chr(9), chr(13).chr(10), chr(10), chr(13)), array('&#x2409;&#x2003;', '&#x240D;&#x240A;<br/>', '&#x240A;<br/>', '&#x240D;<br/>'), htmlspecialchars($address));
		if ($email === '') $email = "&nbsp;";

		$html .= <<<HTML
			<tr id="$id">
				<td><p class="address $address_length">$address_html</p></td>
				<td><div class="infoblock">
					<div class="label">Test #</div>		<div class="id">$id</div><br/>
					<div class="label">Category</div>	<div class="category$class_category$rag_category">$constant_category</div><br/>
					<div class="label">Diagnosis</div>	<div class="diagnosis$class_diagnosis$rag_diagnosis">$constant_diagnosis</div><br/>
				</div></td>
				<td><div class="comment">$comments_html</div></td>
			</tr>

HTML;

		// Update statistics for this test
		$statistics_count++;
		$statistics_alert_category	+= ($result['alert_category'])	? 1 : 0;
		$statistics_alert_diagnosis	+= ($result['alert_diagnosis'])	? 1 : 0;
	}

	// Revision 2.7: Added test run statistics
	if	($statistics_alert_category	!== 0)	$statistics_class = 'red';
	else if	($statistics_alert_diagnosis	!== 0)	$statistics_class = 'amber';
	else						$statistics_class = 'green';

	$statistics_plural_count	= ($statistics_count		=== 1)	? '' : 's';
	$statistics_plural_category	= ($statistics_alert_category	=== 1)	? 'y' : 'ies';
	$statistics_plural_diagnosis	= ($statistics_alert_diagnosis	=== 1)	? 'is' : 'es';

	echo <<<PHP
	<p class="statistics $statistics_class">$statistics_count test$statistics_plural_count: $statistics_alert_category unexpected categor$statistics_plural_category, $statistics_alert_diagnosis unexpected diagnos$statistics_plural_diagnosis</p>
	<table>
		<thead>
			<tr>
				<th><p class="heading address">Address</p></th>
				<th class="heading infoblock">Results</th>
				<th class="heading comment">Comments</th>
			</tr>
		</thead>
		<tbody>
$html		</tbody>
	</table>
	<a id="bottom" href="#top">&laquo; back to top</a>
PHP;
}

/*.string.*/ function test_single_address(/*.string.*/ $email) {
	$result			= unitTest($email);

	$category		= $result['category'];
	$diagnosis		= $result['diagnosis'];
	$constant_category	= $result['constant_category'];
	$constant_diagnosis	= $result['constant_diagnosis'];
	$text_category		= $result['text_category'];
	$text_diagnosis		= $result['text_diagnosis'];
	$smtpcode		= $result['smtpcode'];

	echo <<<HTML
	<div class="results">
		<p>Email address tested was <em>$email</em></p>
		<p>Category: $text_category</p>
		<p>Diagnosis: $text_diagnosis</p>
		<p>The SMTP enhanced status code is <em>$smtpcode</em></p>
	</div>

HTML;
}

/*.string.*/ function forms_html(/*.string.*/ $email = '') {
	$value = ($email === '') ? '' : ' value="' . htmlspecialchars($email) . '"';

	return <<<PHP
	<form>
		<input type="submit" value="Test this" class="menu"/>
		<input type="text"$value name="address" class="text"/>
	</form>
	<a href="?all" >Run all tests</a>
	<a href="?set=tests-beta.xml" >Run beta test set</a>	<!-- Revision 2.11: evaluating Michael Rushton's new test set -->
	<a href="http://www.dominicsayers.com/isemail" target="_blank">Read more...</a>
	<a href="mailto:dominic@sayers.cc?subject=is_email()">Contact</a>
	<br/>

PHP;
}

if (isset($_GET) && is_array($_GET)) {
	if (array_key_exists('address', $_GET)) {
		$email = $_GET['address'];
		if (get_magic_quotes_gpc() !== 0) $email = stripslashes($email); // Version 2.6: BUG: The online test page didn't take account of the magic_quotes_gpc setting that some hosting providers insist on setting. Including mine.
		echo forms_html($email);
		test_single_address($email);
	} else if (array_key_exists('all', $_GET)) {
		echo forms_html();
		all_tests();
	} else if (array_key_exists('set', $_GET)) {	// Revision 2.11: Run any arbitrary test set
		echo forms_html();
		all_tests($_GET['set']);
	} else {
		echo forms_html();
	}
}
?>

</body>
</html>
