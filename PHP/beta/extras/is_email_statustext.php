<?php
// Revision 2.10: Changed $type to integer to allow for additional types, starting with SMTP codes

// What type of status text to return
if (!defined('ISEMAIL_STATUSTEXT_EXPLANATORY')) {
	define('ISEMAIL_STATUSTEXT_EXPLANATORY'	, 1);	// Explanatory text for this $status
	define('ISEMAIL_STATUSTEXT_CONSTANT'	, 2);	// The name of the constant for this $status
	define('ISEMAIL_STATUSTEXT_SMTPCODE'	, 3);	// The SMTP enhanced status code for this $status (the bounce message)
	define('ISEMAIL_STATUSTEXT_INVALIDTYPE'	, -1);	// Unrecognised $type

	// SMTP enhanced status messages
	define('ISEMAIL_STATUSTEXT_SMTP_250_215'	, '250 2.1.5 ok');
	define('ISEMAIL_STATUSTEXT_SMTP_553_510'	, '553 5.1.0 Other address status');
	define('ISEMAIL_STATUSTEXT_SMTP_553_511'	, '553 5.1.1 Bad destination mailbox address');
	define('ISEMAIL_STATUSTEXT_SMTP_553_512'	, '553 5.1.2 Bad destination system address');
	define('ISEMAIL_STATUSTEXT_SMTP_553_513'	, '553 5.1.3 Bad destination mailbox address syntax');
}

/*
 * Return a text status message depending on the is_email() return status
 */
/*.string.*/ function is_email_statustext(/*.integer.*/ $status, /*.mixed.*/ $type = true) {
	// For backward compatibility we recognise a boolean $type
	if	(is_int($type))		$effective_type	= $type;
	else if	(is_bool($type))	$effective_type	= ((bool) $type) ? ISEMAIL_STATUSTEXT_EXPLANATORY : ISEMAIL_STATUSTEXT_CONSTANT;
	else				$effective_type	= ISEMAIL_STATUSTEXT_INVALIDTYPE;

	// Return status text depending on $effective_type and $status
	switch ($effective_type) {
	case ISEMAIL_STATUSTEXT_EXPLANATORY:
		switch ($status) {
		case ISEMAIL_VALID:			return 'Address is valid';											break;	// 0
		// DNS irregularities (if checked)
		case ISEMAIL_DNSWARN_NO_MX_RECORD:	return 'Couldn\'t find an MX record for this domain but an A-record does exist';				break;	// 4;
		case ISEMAIL_DNSWARN_NO_RECORD:		return 'Couldn\'t find an MX record or an A-record for this domain';						break;	// 5;
		case ISEMAIL_DNSWARN_DOMAINNOTFOUND:	return 'Couldn\'t find this domain in the Domain Name System';							break;	// 6;
		case ISEMAIL_DNSWARN:			return 'Address is valid but a DNS check was not successful';							break;	// 7;
		// Warnings (but still RFC 5321 valid)
		case ISEMAIL_RFC5321_TLD:		return 'Address is valid but at a Top Level Domain';								break;	// 9;
		case ISEMAIL_RFC5321_TLDNUMERIC:	return 'Address is valid but the Top Level Domain begins with a number';					break;	// 10;
		case ISEMAIL_RFC5321_QUOTEDSTRING:	return 'Address is valid but contains a quoted string';								break;	// 11;
		case ISEMAIL_RFC5321_ADDRESSLITERAL:	return 'Address is valid but at a literal address not a domain';						break;	// 12;
		case ISEMAIL_RFC5321_IPV6DEPRECATED:	return 'Address is valid but contains a :: that only elides one zero group';					break;	// 13;
		case ISEMAIL_RFC5321_UNNECESSARY:	return 'Address is valid but has an unnecessary quoted string or quoted pair';					break;	// 14;
		case ISEMAIL_RFC5321:			return 'Address is valid for SMTP but has unusual elements';							break;	// 15;
		// RFC 5322 valid
		case ISEMAIL_CFWS_COMMENT:		return 'Address contains comments';										break;	// 17;
		case ISEMAIL_CFWS_FWS:			return 'Address contains Folding White Space';									break;	// 18;
		case ISEMAIL_CFWS:			return 'Address is valid within the message but cannot be used unmodified for the envelope';			break;	// 31;
		// Validity threshold
		case ISEMAIL_THRESHOLD:			return 'Address is invalid for most all purposes';								break;	// 32;
		// RFC 5322 valid but contains obsolete or deprecated syntax
		case ISEMAIL_DEPREC_FWS:		return 'Address contains an obsolete form of Folding White Space';						break;	// 33;
		case ISEMAIL_DEPREC_QTEXT:		return 'A quoted string contains a deprecated character';							break;	// 34;
		case ISEMAIL_DEPREC_QP:			return 'A quoted pair contains a deprecated character';								break;	// 35;
		case ISEMAIL_DEPREC_COMMENT:		return 'Address contains a comment in a position that is deprecated';						break;	// 36;
		case ISEMAIL_DEPREC_CTEXT:		return 'A comment contains a deprecated character';								break;	// 37;
		case ISEMAIL_DEPREC_DOMAIN:		return '(placeholder for ISEMAIL_DEPREC_DOMAIN)';						break;	// 38;
		case ISEMAIL_DEPREC_LOCALPART:		return 'The local part is in a deprecated form';								break;	// 39;
		case ISEMAIL_DEPREC_DTEXT:		return 'Address contains obsolete elements in a domain literal';						break;	// 40;
		case ISEMAIL_DEPREC_CFWS_NEAR_AT:	return 'Address contains a comment or Folding White Space around the @ sign';					break;	// 49;
		case ISEMAIL_DEPREC:			return 'Address contains deprecated elements but may still be valid';						break;	// 63;
		// RFC 5322 valid but ignores related RFCs 5321 & 1035
		case ISEMAIL_RFC5322_DOMAIN:		return 'Address is RFC 5322 compliant but contains domain characters that are not allowed by DNS';		break;	// 65;
		case ISEMAIL_RFC5322_TOOLONG:		return 'Address is too long';											break;	// 66;
		case ISEMAIL_RFC5322_LOCAL_TOOLONG:	return 'The local part of the address is too long';								break;	// 67;
		case ISEMAIL_RFC5322_DOMAIN_TOOLONG:	return 'The domain part is too long';										break;	// 68;
		case ISEMAIL_RFC5322_LABEL_TOOLONG:	return 'The domain part contains an element that is too long';							break;	// 69;
		case ISEMAIL_RFC5322_DOMLIT_PREFIX:	return 'The domain literal is not IPv4 and has an unrecognised prefix';						break;	// 70;
		case ISEMAIL_RFC5322_IPV6_GRPCOUNT:	return 'The IPv6 literal address contains the wrong number of groups';						break;	// 71;
		case ISEMAIL_RFC5322_IPV6_2X2XCOLON:	return 'The IPv6 literal address contains too many :: sequences';						break;	// 72;
		case ISEMAIL_RFC5322_IPV6_BADCHAR:	return 'The IPv6 address contains an illegal group of characters';						break;	// 73;
		case ISEMAIL_RFC5322_IPV6_MAXGRPS:	return 'The IPv6 address has too many groups';									break;	// 74;
		case ISEMAIL_RFC5322_IPV6_COLONSTRT:	return 'IPv6 address starts with a single colon';								break;	// 75;
		case ISEMAIL_RFC5322_IPV6_COLONEND:	return 'IPv6 address ends with a single colon';									break;	// 76;
		case ISEMAIL_RFC5322:			return 'The address is only valid according to the broad definition of RFC 5322. It is otherwise invalid.';	break;	// 127;
		// Errors (invalid address)
		case ISEMAIL_ERR_EXPECTING_DTEXT:	return 'A domain literal contains a character that is not allowed';						break;	// 129;
		case ISEMAIL_ERR_NOAT:			return 'Address has no @ sign';											break;	// 130;
		case ISEMAIL_ERR_NOLOCALPART:		return 'Address has no local part';										break;	// 131;
		case ISEMAIL_ERR_NODOMAIN:		return 'Address has no domain part';										break;	// 132;
		case ISEMAIL_ERR_CONSECUTIVEDOTS:	return 'The address may not contain consecutive dots';								break;	// 133;
		case ISEMAIL_ERR_ATEXT_AFTER_CFWS:	return 'Address contains text after a comment or Folding White Space';						break;	// 134;
		case ISEMAIL_ERR_ATEXT_AFTER_QS:	return 'Address contains text after a quoted string';								break;	// 135;
		case ISEMAIL_ERR_ATEXT_AFTER_DOMLIT:	return 'Extra characters were found after the end of the domain literal';					break;	// 136;
		case ISEMAIL_ERR_EXPECTING_QPAIR:	return 'The address contains a character that is not allowed in a quoted pair';					break;	// 137;
		case ISEMAIL_ERR_EXPECTING_ATEXT:	return 'Address contains a character that is not allowed';							break;	// 138;
		case ISEMAIL_ERR_EXPECTING_QTEXT:	return 'A quoted string contains a character that is not allowed';						break;	// 139;
		case ISEMAIL_ERR_EXPECTING_CTEXT:	return 'A comment contains a character that is not allowed';							break;	// 140;
		case ISEMAIL_ERR_BACKSLASHEND:		return 'The address can\'t end with a backslash';								break;	// 141;
		case ISEMAIL_ERR_DOT_START:		return 'Neither part of the address may begin with a dot';							break;	// 142;
		case ISEMAIL_ERR_DOT_END:		return 'Neither part of the address may end with a dot';							break;	// 143;
		case ISEMAIL_ERR_DOMAINHYPHENSTART:	return 'A domain or subdomain cannot begin with a hyphen';							break;	// 144;
		case ISEMAIL_ERR_DOMAINHYPHENEND:	return 'A domain or subdomain cannot end with a hyphen';							break;	// 145;
		case ISEMAIL_ERR_UNCLOSEDQUOTEDSTR:	return 'Unclosed quoted string';										break;	// 146;
		case ISEMAIL_ERR_UNCLOSEDCOMMENT:	return 'Unclosed comment';											break;	// 147;
		case ISEMAIL_ERR_UNCLOSEDDOMLIT:	return 'Domain literal is missing its closing bracket';								break;	// 148;
		case ISEMAIL_ERR_FWS_CRLF_X2:		return 'Folding White Space contains consecutive CRLF sequences';						break;	// 149;
		case ISEMAIL_ERR_FWS_CRLF_END:		return 'Folding White Space ends with a CRLF sequence';								break;	// 150;
		case ISEMAIL_ERR_CR_NO_LF:		return 'Address contains a carriage return that is not followed by a line feed';				break;	// 151;
		case ISEMAIL_ERR:			return 'Address is invalid for any purpose';									break;	// 255;
		default:				return "Undefined status: $status";
		}
	case ISEMAIL_STATUSTEXT_CONSTANT:
		switch ($status) {
		case ISEMAIL_VALID:			return 'ISEMAIL_VALID';				break;	// 0
		// DNS irregularities (if checked)
		case ISEMAIL_DNSWARN_NO_MX_RECORD:	return 'ISEMAIL_DNSWARN_NO_MX_RECORD';		break;	// 4;
		case ISEMAIL_DNSWARN_NO_RECORD:		return 'ISEMAIL_DNSWARN_NO_RECORD';		break;	// 5;
		case ISEMAIL_DNSWARN_DOMAINNOTFOUND:	return 'ISEMAIL_DNSWARN_DOMAINNOTFOUND';	break;	// 6;
		case ISEMAIL_DNSWARN:			return 'ISEMAIL_DNSWARN';			break;	// 7;
		// Warnings (but still RFC 5321 valid)
		case ISEMAIL_RFC5321_TLD:		return 'ISEMAIL_RFC5321_TLD';			break;	// 9;
		case ISEMAIL_RFC5321_TLDNUMERIC:	return 'ISEMAIL_RFC5321_TLDNUMERIC';		break;	// 10;
		case ISEMAIL_RFC5321_QUOTEDSTRING:	return 'ISEMAIL_RFC5321_QUOTEDSTRING';		break;	// 11;
		case ISEMAIL_RFC5321_ADDRESSLITERAL:	return 'ISEMAIL_RFC5321_ADDRESSLITERAL';	break;	// 12;
		case ISEMAIL_RFC5321_IPV6DEPRECATED:	return 'ISEMAIL_RFC5321_IPV6DEPRECATED';	break;	// 13;
		case ISEMAIL_RFC5321_UNNECESSARY:	return 'ISEMAIL_RFC5321_UNNECESSARY';		break;	// 14;
		case ISEMAIL_RFC5321:			return 'ISEMAIL_RFC5321';			break;	// 15;
		// RFC 5322 valid
		case ISEMAIL_CFWS_COMMENT:		return 'ISEMAIL_CFWS_COMMENT';			break;	// 17;
		case ISEMAIL_CFWS_FWS:			return 'ISEMAIL_CFWS_FWS';			break;	// 18;
		case ISEMAIL_CFWS:			return 'ISEMAIL_CFWS';				break;	// 31;
		// Validity threshold
		case ISEMAIL_THRESHOLD:			return 'ISEMAIL_THRESHOLD';			break;	// 32;
		// RFC 5322 valid but contains obsolete or deprecated syntax
		case ISEMAIL_DEPREC_FWS:		return 'ISEMAIL_DEPREC_FWS';			break;	// 33;
		case ISEMAIL_DEPREC_QTEXT:		return 'ISEMAIL_DEPREC_QTEXT';			break;	// 34;
		case ISEMAIL_DEPREC_QP:			return 'ISEMAIL_DEPREC_QP';			break;	// 35;
		case ISEMAIL_DEPREC_COMMENT:		return 'ISEMAIL_DEPREC_COMMENT';		break;	// 36;
		case ISEMAIL_DEPREC_CTEXT:		return 'ISEMAIL_DEPREC_CTEXT';			break;	// 37;
		case ISEMAIL_DEPREC_DOMAIN:		return 'ISEMAIL_DEPREC_DOMAIN';			break;	// 38;
		case ISEMAIL_DEPREC_LOCALPART:		return 'ISEMAIL_DEPREC_LOCALPART';		break;	// 39;
		case ISEMAIL_DEPREC_DTEXT:		return 'ISEMAIL_DEPREC_DTEXT';			break;	// 40;
		case ISEMAIL_DEPREC_CFWS_NEAR_AT:	return 'ISEMAIL_DEPREC_CFWS_NEAR_AT';		break;	// 49;
		case ISEMAIL_DEPREC:			return 'ISEMAIL_DEPREC';			break;	// 63;
		// RFC 5322 valid but ignores related RFCs 5321 & 1035
		case ISEMAIL_RFC5322_DOMAIN:		return 'ISEMAIL_RFC5322_DOMAIN';		break;	// 65;
		case ISEMAIL_RFC5322_TOOLONG:		return 'ISEMAIL_RFC5322_TOOLONG';		break;	// 66;
		case ISEMAIL_RFC5322_LOCAL_TOOLONG:	return 'ISEMAIL_RFC5322_LOCAL_TOOLONG';		break;	// 67;
		case ISEMAIL_RFC5322_DOMAIN_TOOLONG:	return 'ISEMAIL_RFC5322_DOMAIN_TOOLONG';	break;	// 68;
		case ISEMAIL_RFC5322_LABEL_TOOLONG:	return 'ISEMAIL_RFC5322_LABEL_TOOLONG';		break;	// 69;
		case ISEMAIL_RFC5322_DOMLIT_PREFIX:	return 'ISEMAIL_RFC5322_DOMLIT_PREFIX';	break;	// 70;
		case ISEMAIL_RFC5322_IPV6_GRPCOUNT:	return 'ISEMAIL_RFC5322_IPV6_GRPCOUNT';		break;	// 71;
		case ISEMAIL_RFC5322_IPV6_2X2XCOLON:	return 'ISEMAIL_RFC5322_IPV6_2X2XCOLON';	break;	// 72;
		case ISEMAIL_RFC5322_IPV6_BADCHAR:	return 'ISEMAIL_RFC5322_IPV6_BADCHAR';		break;	// 73;
		case ISEMAIL_RFC5322_IPV6_MAXGRPS:	return 'ISEMAIL_RFC5322_IPV6_MAXGRPS';		break;	// 74;
		case ISEMAIL_RFC5322_IPV6_COLONSTRT:	return 'ISEMAIL_RFC5322_IPV6_COLONSTRT';	break;	// 75;
		case ISEMAIL_RFC5322_IPV6_COLONEND:	return 'ISEMAIL_RFC5322_IPV6_COLONEND';		break;	// 76;
		case ISEMAIL_RFC5322:			return 'ISEMAIL_RFC5322';			break;	// 127;
		// Errors (invalid address)
		case ISEMAIL_ERR_EXPECTING_DTEXT:	return 'ISEMAIL_ERR_EXPECTING_DTEXT';		break;	// 129;
		case ISEMAIL_ERR_NOAT:			return 'ISEMAIL_ERR_NOAT';			break;	// 130;
		case ISEMAIL_ERR_NOLOCALPART:		return 'ISEMAIL_ERR_NOLOCALPART';		break;	// 131;
		case ISEMAIL_ERR_NODOMAIN:		return 'ISEMAIL_ERR_NODOMAIN';			break;	// 132;
		case ISEMAIL_ERR_CONSECUTIVEDOTS:	return 'ISEMAIL_ERR_CONSECUTIVEDOTS';		break;	// 133;
		case ISEMAIL_ERR_ATEXT_AFTER_CFWS:	return 'ISEMAIL_ERR_ATEXT_AFTER_CFWS';		break;	// 134;
		case ISEMAIL_ERR_ATEXT_AFTER_QS:	return 'ISEMAIL_ERR_ATEXT_AFTER_QS';		break;	// 135;
		case ISEMAIL_ERR_ATEXT_AFTER_DOMLIT:	return 'ISEMAIL_ERR_ATEXT_AFTER_DOMLIT';	break;	// 136;
		case ISEMAIL_ERR_EXPECTING_QPAIR:	return 'ISEMAIL_ERR_EXPECTING_QPAIR';		break;	// 137;
		case ISEMAIL_ERR_EXPECTING_ATEXT:	return 'ISEMAIL_ERR_EXPECTING_ATEXT';		break;	// 138;
		case ISEMAIL_ERR_EXPECTING_QTEXT:	return 'ISEMAIL_ERR_EXPECTING_QTEXT';		break;	// 139;
		case ISEMAIL_ERR_EXPECTING_CTEXT:	return 'ISEMAIL_ERR_EXPECTING_CTEXT';		break;	// 140;
		case ISEMAIL_ERR_BACKSLASHEND:		return 'ISEMAIL_ERR_BACKSLASHEND';		break;	// 141;
		case ISEMAIL_ERR_DOT_START:		return 'ISEMAIL_ERR_DOT_START';			break;	// 142;
		case ISEMAIL_ERR_DOT_END:		return 'ISEMAIL_ERR_DOT_END';			break;	// 143;
		case ISEMAIL_ERR_DOMAINHYPHENSTART:	return 'ISEMAIL_ERR_DOMAINHYPHENSTART';		break;	// 144;
		case ISEMAIL_ERR_DOMAINHYPHENEND:	return 'ISEMAIL_ERR_DOMAINHYPHENEND';		break;	// 145;
		case ISEMAIL_ERR_UNCLOSEDQUOTEDSTR:	return 'ISEMAIL_ERR_UNCLOSEDQUOTEDSTR';		break;	// 146;
		case ISEMAIL_ERR_UNCLOSEDCOMMENT:	return 'ISEMAIL_ERR_UNCLOSEDCOMMENT';		break;	// 147;
		case ISEMAIL_ERR_UNCLOSEDDOMLIT:	return 'ISEMAIL_ERR_UNCLOSEDDOMLIT';		break;	// 148;
		case ISEMAIL_ERR_FWS_CRLF_X2:		return 'ISEMAIL_ERR_FWS_CRLF_X2';		break;	// 149;
		case ISEMAIL_ERR_FWS_CRLF_END:		return 'ISEMAIL_ERR_FWS_CRLF_END';		break;	// 150;
		case ISEMAIL_ERR_CR_NO_LF:		return 'ISEMAIL_ERR_CR_NO_LF';			break;	// 151;
		case ISEMAIL_ERR:			return 'ISEMAIL_ERR';				break;	// 255;
		case ISEMAIL_DIAGNOSE:			return 'ISEMAIL_DIAGNOSE';			break;	// 256;
		default:				return "Unknown constant: $status";
		}
	case ISEMAIL_STATUSTEXT_SMTPCODE:
		// These codes assume we are validating a recipient address
		// The correct use of reply code 553 is documented in RFCs 821, 2821 & 5321.
		//	http://tools.ietf.org/html/rfc5321#section-4.2

		// The SMTP enhanced status codes (5.1.x) are maintained in the IANA registry
		// 	http://www.iana.org/assignments/smtp-enhanced-status-codes
		// as defined in RFC 5428.
		//
		// A reminder:
		// define('ISEMAIL_STATUSTEXT_SMTP_250_215'	, '250 2.1.5 ok');
		// define('ISEMAIL_STATUSTEXT_SMTP_553_510'	, '553 5.1.0 Other address status');
		// define('ISEMAIL_STATUSTEXT_SMTP_553_511'	, '553 5.1.1 Bad destination mailbox address');
		// define('ISEMAIL_STATUSTEXT_SMTP_553_512'	, '553 5.1.2 Bad destination system address');
		// define('ISEMAIL_STATUSTEXT_SMTP_553_513'	, '553 5.1.3 Bad destination mailbox address syntax');
		if ($status < ISEMAIL_CFWS) {
			// We can infer a valid RFC 5321 Mailbox by stripping any CFWS
			return ISEMAIL_STATUSTEXT_SMTP_250_215;
		} else {
			// We cannot infer a valid RFC 5321 Mailbox
			switch ($status) {
			// RFC 5322 valid but contains obsolete or deprecated syntax
			case ISEMAIL_DEPREC_FWS:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 33;
			case ISEMAIL_DEPREC_QTEXT:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 34;
			case ISEMAIL_DEPREC_QP:			return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 35;
			case ISEMAIL_DEPREC_COMMENT:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 36;
			case ISEMAIL_DEPREC_CTEXT:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 37;
			case ISEMAIL_DEPREC_DOMAIN:		return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 38;
			case ISEMAIL_DEPREC_LOCALPART:		return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 39;
			case ISEMAIL_DEPREC_DTEXT:		return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 40;
			case ISEMAIL_DEPREC_CFWS_NEAR_AT:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 49;
			// RFC 5322 valid but ignores related RFCs 5321 & 1035
			case ISEMAIL_RFC5322_DOMAIN:		return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 65;
			case ISEMAIL_RFC5322_TOOLONG:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 66;
			case ISEMAIL_RFC5322_LOCAL_TOOLONG:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 67;
			case ISEMAIL_RFC5322_DOMAIN_TOOLONG:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 68;
			case ISEMAIL_RFC5322_LABEL_TOOLONG:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 69;
			case ISEMAIL_RFC5322_DOMLIT_PREFIX:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 70;
			case ISEMAIL_RFC5322_IPV6_GRPCOUNT:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 71;
			case ISEMAIL_RFC5322_IPV6_2X2XCOLON:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 72;
			case ISEMAIL_RFC5322_IPV6_BADCHAR:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 73;
			case ISEMAIL_RFC5322_IPV6_MAXGRPS:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 74;
			case ISEMAIL_RFC5322_IPV6_COLONSTRT:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 75;
			case ISEMAIL_RFC5322_IPV6_COLONEND:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 76;
			// Errors (invalid address)
			case ISEMAIL_ERR_EXPECTING_DTEXT:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 129;
			case ISEMAIL_ERR_NOAT:			return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 130;
			case ISEMAIL_ERR_NOLOCALPART:		return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 131;
			case ISEMAIL_ERR_NODOMAIN:		return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 132;
			case ISEMAIL_ERR_CONSECUTIVEDOTS:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 133;
			case ISEMAIL_ERR_ATEXT_AFTER_CFWS:	return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 134;
			case ISEMAIL_ERR_ATEXT_AFTER_QS:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 135;
			case ISEMAIL_ERR_ATEXT_AFTER_DOMLIT:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 136;
			case ISEMAIL_ERR_EXPECTING_QPAIR:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 137;
			case ISEMAIL_ERR_EXPECTING_ATEXT:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 138;
			case ISEMAIL_ERR_EXPECTING_QTEXT:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 139;
			case ISEMAIL_ERR_EXPECTING_CTEXT:	return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 140;
			case ISEMAIL_ERR_BACKSLASHEND:		return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 141;
			case ISEMAIL_ERR_DOT_START:		return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 142;
			case ISEMAIL_ERR_DOT_END:		return ISEMAIL_STATUSTEXT_SMTP_553_511;	break;	// 143;
			case ISEMAIL_ERR_DOMAINHYPHENSTART:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 144;
			case ISEMAIL_ERR_DOMAINHYPHENEND:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 145;
			case ISEMAIL_ERR_UNCLOSEDQUOTEDSTR:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 146;
			case ISEMAIL_ERR_UNCLOSEDCOMMENT:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 147;
			case ISEMAIL_ERR_UNCLOSEDDOMLIT:	return ISEMAIL_STATUSTEXT_SMTP_553_512;	break;	// 148;
			case ISEMAIL_ERR_FWS_CRLF_X2:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 149;
			case ISEMAIL_ERR_FWS_CRLF_END:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 150;
			case ISEMAIL_ERR_CR_NO_LF:		return ISEMAIL_STATUSTEXT_SMTP_553_513;	break;	// 151;
			default:				return ISEMAIL_STATUSTEXT_SMTP_553_510;
			}
		}
	default:
		return "Status is $status. Unknown status text type: passed as " . gettype($type) . ' with value "' . strval($type) . '"';
	}
}
?>
