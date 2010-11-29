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
// Incorporates formatting suggestions from Daniel Marschall
require_once '../is_email.php';
require_once './meta.php';

/*.string.*/ function innerHTML($node, $document) {
	if ($node->childNodes->length > 1) {
		$html = '';
		foreach ($node->childNodes as $childNode) $html .= $document->saveXML($childNode); // May need LIBXML_NOEMPTYTAG
	} else {
		$html = $node->nodeValue;
	}

	return $html;
}

/*string.*/ function alternate_diagnoses(/*.array[int]int.*/ $diagnoses, /*.int.*/ $diagnosis) {
		// Other diagnoses
		$alternates	= '';
		$separator	= '';

		foreach ($diagnoses as $alternate) {
			if ($alternate !== $diagnosis) {
				$alternates	.= $separator . is_email_analysis($alternate, ISEMAIL_META_CONSTANT);
				$separator	= ', ';
			}
		}

		return ($alternates !== '') ? "Other diagnoses: $alternates" : '';
}

/*.array[string]mixed.*/ function unitTest ($email, $expected_category_test = '', $expected_diagnosis = '') {
	$result			= /*.(array[string]mixed).*/ array('actual' => array());
	$parsedata		= /*.(array[string]string).*/ array();

	$diagnosis_value		= is_email($email, true, true, &$parsedata);

	$result['actual']['diagnosis']	= $diagnosis_value;
	$result['actual']['parsedata']	= $parsedata;
	$result['actual']['analysis']	= is_email_analysis($diagnosis_value, ISEMAIL_META_ALL);

	if ($expected_diagnosis === '') {
		$result['actual']['alert_category']	= false;
		$result['actual']['alert_diagnosis']	= false;
	} else {
		$result['expected']			= array();
		$result['expected']['diagnosis']	= $expected_diagnosis;
		$result['expected']['analysis']		= is_email_analysis($expected_diagnosis, ISEMAIL_META_ALL);

		$category				= $result['actual']['analysis'][ISEMAIL_META_CATEGORY];
		$expected_category			= $result['expected']['analysis'][ISEMAIL_META_CATEGORY];
		$diagnosis				= $result['actual']['analysis'][ISEMAIL_META_CONSTANT];

		$result['actual']['alert_category']	= ($category	!== $expected_category);
		$result['actual']['alert_diagnosis']	= ($diagnosis	!== $expected_diagnosis);
	}

	// Sanity check expected category
	// (this is necessary because we decided to keep both category
	// and diagnosis in the test data)
	if (($expected_category_test !== '') && ($expected_category_test !== $expected_category))
		die("The expected category $expected_category_test from the test data for '$email' does not match the true expected category $expected_category");

	return $result;
}

/*.string.*/ function all_tests($test_set = 'tests.xml') {
	$document = new DOMDocument();
	$document->load($test_set);
	$document->schemaValidate('./tests.xsd');

	// Get version
	$suite		= $document->getElementsByTagName('tests')->item(0);
	$version	= ($suite->hasAttribute('version')) ? $suite->getAttribute('version') : '';
	$nodeList	= $document->getElementsByTagName('description');
	$description	= ($nodeList->length === 0) ? '' : "\t" . '<div class="rubric">' . innerHTML($nodeList->item(0), $document) . '</div>';

	echo <<<PHP
	<h3>Test package version $version</h3>
$description

PHP;

	$testList			= $document->getElementsByTagName('test');
	$testCount			= $testList->length;
	$coverage_actual		= array();	// List of diagnoses returned by the test set
	$statistics_count		= 0;
	$statistics_alert_category	= 0;
	$statistics_alert_diagnosis	= 0;
	$html				= '';

	// Can't store ASCII or Unicode characters below U+0020 in XML file so we put a token in the XML
	// (except for HTAB, CR & LF)
	// The tokens we have chosen are the Unicode Characters 'SYMBOL FOR xxx' (U+2400 onwards)
	// Here we convert the symbol to the actual character.
	$span_start		= '<span class="controlcharacter">';
	$span_end		= '</span>';

	$needles		= array(' ', mb_convert_encoding('&#9229;&#9226;', 'UTF-8', 'HTML-ENTITIES'));
	$substitutes		= array(' ', chr(13).chr(10));
	$substitutes_html	= array("$span_start&#x2420;$span_end", "$span_start&#x240D;&#x240A;$span_end<br/>");

	for ($i = 0; $i < 32; $i++) {
		$entity			= mb_convert_encoding('&#' . (string) (9216 + $i) . ';', 'UTF-8', 'HTML-ENTITIES');	// PHP bug doesn't allow us to use hex notation (http://bugs.php.net/48645)
		$entity_html		= '&#x24' . substr('0'.dechex($i), -2) . ';';
		$needles[]		= $entity;
		$substitutes[]		= chr($i);
		$substitutes_html[]	= "$span_start$entity_html$span_end";
	}

	// Additional output modifications
	$substitutes_html[12]		.= '<br/>';	// Add a visible line break to LF
	$substitutes_html[15]		.= '<br/>';	// Add a visible line break to CR

	for ($i = 0; $i < $testCount; $i++) {
		$test_node	= $testList->item($i);
		$id		= $test_node->hasAttribute('id') ? $test_node->getAttribute('id') : '';
		$tagList	= $test_node->childNodes;

		$address	= '';
		$category	= '';
		$diagnosis	= '';
		$comment	= '';

		for ($j = 0; $j < $tagList->length; $j++) {
			$node = $tagList->item($j);

			if ($node->nodeType === XML_ELEMENT_NODE) {
				$name	= $node->nodeName;
				$$name	= innerHTML($node, $document);
			}
		}

		$email			= str_replace($needles, $substitutes, $address);
		$address_html		= str_replace($needles, $substitutes_html, $address);
		$comment		= str_replace($needles, $substitutes, $comment);

		$result			= unitTest($email, $category, $diagnosis);	// This is why we're here

		$category_result	= $result['actual']['analysis'][ISEMAIL_META_CAT_VALUE];
		$diagnosis_result	= $result['actual']['diagnosis'];
		$constant_category	= $result['actual']['analysis'][ISEMAIL_META_CATEGORY];
		$constant_diagnosis	= $result['actual']['analysis'][ISEMAIL_META_CONSTANT];
		$text			= $result['actual']['analysis'][ISEMAIL_META_DESC];
		$references		= (array_key_exists(ISEMAIL_META_REF_ALT, $result['actual']['analysis'])) ? '<span>' . $result['actual']['analysis'][ISEMAIL_META_REF_ALT] . '</span>' : '';

		$comments		= /*.(array[int]string).*/ array();

		if (strlen($comment) !== 0)	$comments[] = '<em>' . stripslashes($comment) . '</em>';
		if ($text !== '')		$comments[] = stripslashes($text);

		if ($result['actual']['alert_category']) {
			$class_category	= ' unexpected';
			$rag_category	= ' red';
			$comments[]	= 'Expected category was ' . $result['expected']['analysis'][ISEMAIL_META_CATEGORY];
		} else {
			$class_category	= '';
			$rag_category	= '';
		}

		if ($result['actual']['alert_diagnosis']) {
			$class_diagnosis= ' unexpected';
			$rag_diagnosis	= ' amber';
			$comments[]	= 'Expected diagnosis was ' . $result['expected']['analysis'][ISEMAIL_META_CONSTANT];
		} else {
			$class_diagnosis= '';
			$rag_diagnosis	= '';
		}

		// Validity
		$valid = ($diagnosis_result < ISEMAIL_THRESHOLD) ? 'valid' : 'invalid';

		// Other diagnoses
		$alternates = alternate_diagnoses($result['actual']['parsedata']['status'], $diagnosis_result);
		if ($alternates !== '') $comments[] = $alternates;

		$comments_html	= implode('<br/>', $comments);
		$address_length = strlen($address);
		$address_class	= ($address_length > 39) ? 'small' : (($address_length < 29) ? 'large' : 'medium');

		$html .= <<<HTML
			<tr id="$id">
				<td><p class="address $address_class">$address_html</p></td>
				<td>
					<div class="infoblock">
						<div class="validity"><p class="$valid $address_class"/></div>
						<div>
							<div class="label">Test #</div>		<div class="id">$id</div><br/>
							<div class="label">Category</div>	<div class="category$class_category$rag_category">$constant_category</div><br/>
							<div class="label">Diagnosis</div>	<div class="diagnosis$class_diagnosis$rag_diagnosis">$constant_diagnosis</div><br/>
$references
						</div>
					</div>
				</td>
				<td><div class="comment">$comments_html</div></td>
			</tr>

HTML;

		// Update statistics for this test
		$coverage_actual[]		= $diagnosis_result;

		$statistics_count++;
		$statistics_alert_category	+= ($result['actual']['alert_category'])	? 1 : 0;
		$statistics_alert_diagnosis	+= ($result['actual']['alert_diagnosis'])	? 1 : 0;
	}

	// Revision 2.7: Added test run statistics
	if	($statistics_alert_category	!== 0)	$statistics_class = 'red';
	else if	($statistics_alert_diagnosis	!== 0)	$statistics_class = 'amber';
	else						$statistics_class = 'green';

	$statistics_plural_count	= ($statistics_count		=== 1)	? '' : 's';
	$statistics_plural_category	= ($statistics_alert_category	=== 1)	? 'y' : 'ies';
	$statistics_plural_diagnosis	= ($statistics_alert_diagnosis	=== 1)	? 'is' : 'es';

	// Coverage
	$coverage_actual	= array_unique($coverage_actual, SORT_NUMERIC);
	$coverage_theory	= is_email_list(ISEMAIL_META_VALUE);
	$coverage_count_actual	= count($coverage_actual);
	$coverage_count_theory	= count($coverage_theory);
	$coverage_percent	= sprintf('%d', 100 * $coverage_count_actual / $coverage_count_theory);
	$coverage_diff		= array_diff($coverage_theory, $coverage_actual);
	$coverage_missing	= '';
	$separator		= '';

	foreach($coverage_diff as $value) {
		$constant		= is_email_analysis((int) $value, ISEMAIL_META_CONSTANT);
		$coverage_missing	.= $separator . $constant;
		$separator		= ', ';
	}

	if ($coverage_missing !== '') $coverage_missing = " Missing outcomes: $coverage_missing";

	echo <<<PHP
	<p class="rubric">Coverage: $coverage_percent% ($coverage_count_actual outcomes recorded / $coverage_count_theory defined).$coverage_missing</p>
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

	$constant_category	= $result['actual']['analysis'][ISEMAIL_META_CATEGORY];
	$constant_diagnosis	= $result['actual']['analysis'][ISEMAIL_META_CONSTANT];
	$text_category		= $result['actual']['analysis'][ISEMAIL_META_CAT_DESC];
	$text_diagnosis		= $result['actual']['analysis'][ISEMAIL_META_DESC];
	$smtpcode		= $result['actual']['analysis'][ISEMAIL_META_SMTP];
	$reference		= (array_key_exists(ISEMAIL_META_REF_ALT, $result['actual']['analysis'])) ? "\t\t<p>The following reference is relevant:</p>\r\n" . $result['actual']['analysis'][ISEMAIL_META_REF_ALT] : '';

	// Other diagnoses
	$alternates = alternate_diagnoses($result['actual']['parsedata']['status'], $result['actual']['diagnosis']);
	if ($alternates !== '') $alternates = "<p>$alternates</p>";

	echo <<<HTML
	<div class="results">
		<p>Email address tested was <em>$email</em></p>
		<p>Category: [$constant_category] $text_category</p>
		<p>Diagnosis: [$constant_diagnosis] $text_diagnosis</p>
$alternates		<p>The SMTP enhanced status code would be <em>$smtpcode</em></p>
$reference	</div>

HTML;
}

/*.string.*/ function forms_html(/*.string.*/ $email = '') {
	$value = ($email === '') ? '' : ' value="' . htmlspecialchars($email) . '"';

	return <<<PHP
	<form>
		<input type="submit" value="Test this" class="menu"/>
		<input type="text"$value name="address" class="text"/>
	</form>
	<a class="menu" href="?all" >Run all tests</a>
	<a class="menu" href="?set=tests-original.xml" >Run original test set</a>
	<a class="menu" href="http://www.dominicsayers.com/isemail" target="_blank">Read more...</a>
	<a class="menu" href="mailto:dominic@sayers.cc?subject=is_email()">Contact</a>
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
	} elseif (count($_GET) > 0) {
		$keys	= array_keys($_GET);
		$email	= array_shift($keys);
		if (get_magic_quotes_gpc() !== 0) $email = stripslashes($email); // Version 2.6: BUG: The online test page didn't take account of the magic_quotes_gpc setting that some hosting providers insist on setting. Including mine.
		echo forms_html($email);
		test_single_address($email);
	} else {
		echo forms_html();
	}
}
?>

</body>
</html>
