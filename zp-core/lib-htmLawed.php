<?php

/**
 * htmLawed.php – Filter/sanitize HTM text with PHP.
 *
 * Use: $out = htmLawed($in, $config, $spec)
 * See htmLawed_README.
 *
 * Code overview in htmLawed_README §5.6. Familiarity with HTML standards and
 * documentation on htmLawed configuration required to understand code.
 *
 * A PHP Labware internal utility - bioinformatics.org/phplabware.
 *
 * @author     Santosh Patnaik <drpatnaikREMOVECAPS@yahoo.com>
 * @copyright  (c) 2007-, Santosh Patnaik
 * @dependency None
 * @license    LGPL 3 and GPL 2+ dual license
 * @link       https://bioinformatics.org/phplabware/internal_utilities/htmLawed
 * @package    htmLawed
 * @php        >=4.4
 * @time       2022-06-06
 * @version    1.2.8
 */

/*
 * Main function.
 * Calls all other functions (alphabetically ordered further below).
 *
 * @param  string $t HTM.
 * @param  mixed  $C $config configuration option.
 * @param  mixed  $S $spec specification option.
 * @return string    Filtered/sanitized $t.
 */
function htmLawed($t, $C=1, $S=array())
{
    // Standard elements including some now deprecated (122).

    $eleAr = array('a'=>1, 'abbr'=>1, 'acronym'=>1, 'address'=>1, 'applet'=>1, 'area'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'blockquote'=>1, 'br'=>1, 'button'=>1, 'canvas'=>1, 'caption'=>1, 'center'=>1, 'cite'=>1, 'code'=>1, 'col'=>1, 'colgroup'=>1, 'command'=>1, 'data'=>1, 'datalist'=>1, 'dd'=>1, 'del'=>1, 'details'=>1, 'dialog'=>1, 'dfn'=>1, 'dir'=>1, 'div'=>1, 'dl'=>1, 'dt'=>1, 'em'=>1, 'embed'=>1, 'fieldset'=>1, 'figcaption'=>1, 'figure'=>1, 'font'=>1, 'footer'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hgroup'=>1, 'hr'=>1, 'i'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'ins'=>1, 'isindex'=>1, 'kbd'=>1, 'keygen'=>1, 'label'=>1, 'legend'=>1, 'li'=>1, 'link'=>1, 'main'=>1, 'map'=>1, 'mark'=>1, 'menu'=>1, 'meta'=>1, 'meter'=>1, 'nav'=>1, 'noscript'=>1, 'object'=>1, 'ol'=>1, 'optgroup'=>1, 'option'=>1, 'output'=>1, 'p'=>1, 'param'=>1, 'picture'=>1, 'pre'=>1, 'progress'=>1, 'q'=>1, 'rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'slot'=>1, 'small'=>1, 'source'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'style'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'template'=>1, 'textarea'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'time'=>1, 'tr'=>1, 'track'=>1, 'tt'=>1, 'u'=>1, 'ul'=>1, 'var'=>1, 'video'=>1, 'wbr'=>1);

    // Set $C array ($config), using default parameters as needed.

    $C = is_array($C) ? $C : array();
    if (!empty($C['valid_xhtml'])) {
        $C['elements'] = empty($C['elements']) ? '*-acronym-big-center-dir-font-isindex-s-strike-tt' : $C['elements'];
        $C['make_tag_strict'] = isset($C['make_tag_strict']) ? $C['make_tag_strict'] : 2;
        $C['xml:lang'] = isset($C['xml:lang']) ? $C['xml:lang'] : 2;
    }

    // -- Configure for elements.

    if (!empty($C['safe'])) {
        unset($eleAr['applet'], $eleAr['audio'], $eleAr['canvas'], $eleAr['dialog'], $eleAr['embed'], $eleAr['iframe'], $eleAr['object'], $eleAr['script'], $eleAr['video']);
    }
    $x = !empty($C['elements']) ? str_replace(array("\n", "\r", "\t", ' '), '', strtolower($C['elements'])) : '*';
    if ($x == '-*') {
        $eleAr = array();
    } elseif (strpos($x, '*') === false) {
        $eleAr = array_flip(explode(',', $x));
    } else {
        if (isset($x[1])) {
            if (strpos($x, '(')) { // Temporarily replace hyphen in custom element name (minus being special character)
                $x = preg_replace_callback(
                         '`\([^()]+\)`',
                         function ($m) {
                             return str_replace(array('(', ')', '-'), array('', '', 'A'), $m[0]);
                         },
                         $x
                     );
            }
            preg_match_all('`(?:^|-|\+)[^\-+]+?(?=-|\+|$)`', $x, $m, PREG_SET_ORDER);
            for ($i=count($m); --$i>=0;) {
                $m[$i] = $m[$i][0];
            }
            foreach ($m as $v) {
                $v = str_replace('A', '-', $v);
                if ($v[0] == '+') {
                    $eleAr[substr($v, 1)] = 1;
                } elseif ($v[0] == '-') {
                    if (strpos($v, '-', 1)) {
                        $eleAr[$v] = 1;
                    } elseif (isset($eleAr[($v = substr($v, 1))]) && !in_array('+'. $v, $m)) {
                        unset($eleAr[$v]);
                    }
                }
            }
        }
    }
    $C['elements'] =& $eleAr;

    // -- Configure for attributes.

    $x = !empty($C['deny_attribute']) ? strtolower(preg_replace('"\s+-"', '/', trim($C['deny_attribute']))) : '';
    $x = array_flip(
             (isset($x[0]) && $x[0] == '*')
             ? explode('/', $x)
             : explode(',', $x. (!empty($C['safe']) ? ',on*' : ''))
         );
    $C['deny_attribute'] = $x;

    // -- Configure URL handling.

    $x = (isset($C['schemes'][2]) && strpos($C['schemes'], ':')
          ? strtolower($C['schemes'])
          : 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, tel, telnet')
         . (empty($C['safe'])
            ? ', app, javascript; *: data, javascript, '
            : '; *:')
         . 'file, http, https';
    $C['schemes'] = array();
    foreach (explode(';', trim(str_replace(array(' ', "\t", "\r", "\n"), '', $x), ';')) as $v) {
        $x = $y = null;
        list($x, $y) = explode(':', $v, 2);
        if ($y) {
            $C['schemes'][$x] = array_flip(explode(',', $y));
        }
    }
    if (!isset($C['schemes']['*'])) {
        $C['schemes']['*'] = array('file'=>1, 'http'=>1, 'https'=>1);
        if (empty($C['safe'])) {
            $C['schemes']['*'] += array('data'=>1, 'javascript'=>1);
        }
    }
    if (!empty($C['safe']) && empty($C['schemes']['style'])) {
        $C['schemes']['style'] = array('!'=>1);
    }
    $C['abs_url'] = isset($C['abs_url']) ? $C['abs_url'] : 0;
    if (!isset($C['base_url']) || !preg_match('`^[a-zA-Z\d.+\-]+://[^/]+/(.+?/)?$`', $C['base_url'])) {
        $C['base_url'] = $C['abs_url'] = 0;
    }

    // -- Configure other parameters.

    $C['and_mark'] = empty($C['and_mark']) ? 0 : 1;
    $C['anti_link_spam'] = (isset($C['anti_link_spam'])
                            && is_array($C['anti_link_spam'])
                            && count($C['anti_link_spam']) == 2
                            && (empty($C['anti_link_spam'][0])
                                || hl_regex($C['anti_link_spam'][0]))
                            && (empty($C['anti_link_spam'][1])
                                || hl_regex($C['anti_link_spam'][1]))
                           )
                           ? $C['anti_link_spam']
                           : 0;
    $C['anti_mail_spam'] = isset($C['anti_mail_spam']) ? $C['anti_mail_spam'] : 0;
    $C['any_custom_element'] = (!isset($C['any_custom_element']) || !empty($C['any_custom_element'])) ? 1 : 0;
    $C['balance'] = isset($C['balance']) ? (bool)$C['balance'] : 1;
    $C['cdata'] = isset($C['cdata']) ? $C['cdata'] : (empty($C['safe']) ? 3 : 0);
    $C['clean_ms_char'] = empty($C['clean_ms_char']) ? 0 : $C['clean_ms_char'];
    $C['comment'] = isset($C['comment']) ? $C['comment'] : (empty($C['safe']) ? 3 : 0);
    $C['css_expression'] = empty($C['css_expression']) ? 0 : 1;
    $C['direct_list_nest'] = empty($C['direct_list_nest']) ? 0 : 1;
    $C['hexdec_entity'] = isset($C['hexdec_entity']) ? $C['hexdec_entity'] : 1;
    $C['hook'] = (!empty($C['hook']) && function_exists($C['hook'])) ? $C['hook'] : 0;
    $C['hook_tag'] = (!empty($C['hook_tag']) && function_exists($C['hook_tag'])) ? $C['hook_tag'] : 0;
    $C['keep_bad'] = isset($C['keep_bad']) ? $C['keep_bad'] : 6;
    $C['lc_std_val'] = isset($C['lc_std_val']) ? (bool)$C['lc_std_val'] : 1;
    $C['make_tag_strict'] = isset($C['make_tag_strict']) ? $C['make_tag_strict'] : 1;
    $C['named_entity'] = isset($C['named_entity']) ? (bool)$C['named_entity'] : 1;
    $C['no_deprecated_attr'] = isset($C['no_deprecated_attr']) ? $C['no_deprecated_attr'] : 1;
    $C['parent'] = isset($C['parent'][0]) ? strtolower($C['parent']) : 'body';
    $C['show_setting'] = !empty($C['show_setting']) ? $C['show_setting'] : 0;
    $C['style_pass'] = empty($C['style_pass']) ? 0 : 1;
    $C['tidy'] = empty($C['tidy']) ? 0 : $C['tidy'];
    $C['unique_ids'] = isset($C['unique_ids']) && (!preg_match('`\W`', $C['unique_ids'])) ? $C['unique_ids'] : 1;
    $C['xml:lang'] = isset($C['xml:lang']) ? $C['xml:lang'] : 0;

    if (isset($GLOBALS['C'])) {
        $oldC = $GLOBALS['C'];
    }
    $GLOBALS['C'] = $C;

    // Set $S array ($spec).

    $S = is_array($S) ? $S : hl_spec($S);
    if (isset($GLOBALS['S'])) {
        $oldS = $GLOBALS['S'];
    }
    $GLOBALS['S'] = $S;

    // Handle characters.

    $t = preg_replace('`[\x00-\x08\x0b-\x0c\x0e-\x1f]`', '', $t); // Remove illegal
    if ($C['clean_ms_char']) { // Convert MS Windows CP-1252
        $x = array("\x7f"=>'', "\x80"=>'&#8364;', "\x81"=>'', "\x83"=>'&#402;', "\x85"=>'&#8230;', "\x86"=>'&#8224;', "\x87"=>'&#8225;', "\x88"=>'&#710;', "\x89"=>'&#8240;', "\x8a"=>'&#352;', "\x8b"=>'&#8249;', "\x8c"=>'&#338;', "\x8d"=>'', "\x8e"=>'&#381;', "\x8f"=>'', "\x90"=>'', "\x95"=>'&#8226;', "\x96"=>'&#8211;', "\x97"=>'&#8212;', "\x98"=>'&#732;', "\x99"=>'&#8482;', "\x9a"=>'&#353;', "\x9b"=>'&#8250;', "\x9c"=>'&#339;', "\x9d"=>'', "\x9e"=>'&#382;', "\x9f"=>'&#376;');
        $x = $x + ($C['clean_ms_char'] == 1
                   ? array("\x82"=>'&#8218;', "\x84"=>'&#8222;', "\x91"=>'&#8216;', "\x92"=>'&#8217;', "\x93"=>'&#8220;', "\x94"=>'&#8221;')
                   : array("\x82"=>'\'', "\x84"=>'"', "\x91"=>'\'', "\x92"=>'\'', "\x93"=>'"', "\x94"=>'"')
             );
        $t = strtr($t, $x);
    }

    // Handle CDATA, comments, and entities.

    if ($C['cdata'] or $C['comment']) {
        $t = preg_replace_callback('`<!(?:(?:--.*?--)|(?:\[CDATA\[.*?\]\]))>`sm', 'hl_cmtcd', $t);
    }
    $t = preg_replace_callback('`&amp;([a-zA-Z][a-zA-Z0-9]{1,30}|#(?:[0-9]{1,8}|[Xx][0-9A-Fa-f]{1,7}));`', 'hl_ent', str_replace('&', '&amp;', $t));
    if ($C['unique_ids'] && !isset($GLOBALS['hl_Ids'])) {
        $GLOBALS['hl_Ids'] = array();
    }

    if ($C['hook']) {
        $t = $C['hook']($t, $C, $S);
    }

    // Handle remaining text.

    $t = preg_replace_callback('`<(?:(?:\s|$)|(?:[^>]*(?:>|$)))|>`m', 'hl_tag', $t);
    $t = $C['balance'] ? hl_bal($t, $C['keep_bad'], $C['parent']) : $t;
    $t = (($C['cdata'] || $C['comment']) && strpos($t, "\x01") !== false)
         ? str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05"), array('', '', '&', '<', '>'), $t)
         : $t;
    $t = $C['tidy'] ? hl_tidy($t, $C['tidy'], $C['parent']) : $t;

    // Cleanup.

    if ($C['show_setting'] && preg_match('`^[a-z][a-z0-9_]*$`i', $C['show_setting'])) {
        $GLOBALS[$C['show_setting']] = array('config'=>$C, 'spec'=>$S, 'time'=>microtime(true), 'version'=>hl_version());
    }
    unset($C, $eleAr);
    if (isset($oldC)) {
        $GLOBALS['C'] = $oldC;
    }
    if (isset($oldS)) {
        $GLOBALS['S'] = $oldS;
    }

    return $t;
}

/**
 * Validate attribute value and possibly change to a default value if invalid.
 *
 * @param  string  $attr   Attribute name.
 * @param  string  $value  Attribute value;
 *                         may be string of multiple separated values.
 * @param  array   $ruleAr Array of rules derived from $spec.
 * @return mixed           0 if invalid $value, or string with validated
 *                         value or value reset to a default.
 */
function hl_attrval($attr, $value, $ruleAr)
{
    static $spacedValsAttrAr = array('accesskey', 'class', 'itemtype', 'rel'); // Some attributes have multiple values
    $valSep = in_array($attr, $spacedValsAttrAr) ? ' ' : ($attr == 'srcset' ? ',' : '');
    $out = array();
    $valAr = !empty($valSep) ? explode($valSep, $value) : array($value);
    foreach ($valAr as $v) {
        $ok = 1;
        $v = trim($v);
        $lengthVal = strlen($v);
        foreach ($ruleAr as $ruleType=>$ruleVal) {
            if (!$lengthVal) {
                continue;
            }
            switch ($ruleType) {
                case 'maxlen': if ($lengthVal > $ruleVal) {
                    $ok = 0;
                }
                break; case 'minlen': if ($lengthVal < $ruleVal) {
                    $ok = 0;
                }
                break; case 'maxval': if ((float)($v) > $ruleVal) {
                    $ok = 0;
                }
                break; case 'minval': if ((float)($v) < $ruleVal) {
                    $ok = 0;
                }
                break; case 'match': if (!preg_match($ruleVal, $v)) {
                    $ok = 0;
                }
                break; case 'nomatch': if (preg_match($ruleVal, $v)) {
                    $ok = 0;
                }
                break; case 'oneof': if(!in_array($v, explode('|', $ruleVal))) {
                    $ok = 0;
                }
                break; case 'noneof': if(in_array($v, explode('|', $ruleVal))) {
                    $ok = 0;
                }
                break; default:
                break;
            }
            if (!$ok) {
                break;
            }
        }
        if ($ok) {
            $out[] = $v;
        }
    }
    $out = implode($valSep == ',' ? ', ' : ' ', $out);
    return (isset($out[0]) ? $out : (isset($ruleAr['default']) ? $ruleAr['default'] : 0));
}

/*
 * Enforce parent-child validity of elements and balance tags.
 *
 * @param  string $t         HTM. Previously sanitized/filtered except
 *                           for this validity enforcement and balancing.
 *                           CDATA/comment sections have </> chars hidden.
 * @param  int    $act       $config keep_bad parameter option.
 * @param  string $parentEle $t's parent element option.
 * @return string            $t with parent-child validity of elements
 *                           and balanced tags.
 */
function hl_bal($t, $act=1, $parentEle='div')
{
    // Categorize elements in different ways.

    $closingTagOmitableEleAr = array('colgroup'=>1, 'dd'=>1, 'dt'=>1, 'li'=>1, 'option'=>1, 'p'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1);

    // -- By type (block, flow, etc.).

    $blockEleAr = array('a'=>1, 'address'=>1, 'article'=>1, 'aside'=>1, 'blockquote'=>1, 'center'=>1, 'del'=>1, 'details'=>1, 'dialog'=>1, 'dir'=>1, 'dl'=>1, 'div'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'form'=>1, 'ins'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hr'=>1, 'isindex'=>1, 'main'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'section'=>1, 'slot'=>1, 'style'=>1, 'table'=>1, 'template'=>1, 'ul'=>1);
    $inlineEleAr = array('#pcdata'=>1, 'a'=>1, 'abbr'=>1, 'acronym'=>1, 'applet'=>1, 'audio'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'br'=>1, 'button'=>1, 'canvas'=>1, 'cite'=>1, 'code'=>1, 'command'=>1, 'data'=>1, 'datalist'=>1, 'del'=>1, 'dfn'=>1, 'em'=>1, 'embed'=>1, 'figcaption'=>1, 'font'=>1, 'i'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'ins'=>1, 'kbd'=>1, 'label'=>1, 'link'=>1, 'map'=>1, 'mark'=>1, 'meta'=>1, 'meter'=>1, 'object'=>1, 'output'=>1, 'picture'=>1, 'progress'=>1, 'q'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'select'=>1, 'script'=>1, 'small'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'textarea'=>1, 'time'=>1, 'tt'=>1, 'u'=>1, 'var'=>1, 'video'=>1, 'wbr'=>1);
    $otherEleAr = array('area'=>1, 'caption'=>1, 'col'=>1, 'colgroup'=>1, 'command'=>1, 'dd'=>1, 'dt'=>1, 'hgroup'=>1, 'keygen'=>1, 'legend'=>1, 'li'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1, 'script'=>1, 'source'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'thead'=>1, 'th'=>1, 'tr'=>1, 'track'=>1);
    $flowEleAr = $blockEleAr + $inlineEleAr;

    // -- By type of allowed child element.

    $blockKidEleAr = array('blockquote'=>1, 'form'=>1, 'map'=>1, 'noscript'=>1);
    $flowKidEleAr = array('a'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'button'=>1, 'canvas'=>1, 'del'=>1, 'details'=>1, 'dialog'=>1, 'div'=>1, 'dd'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'header'=>1, 'iframe'=>1, 'ins'=>1, 'li'=>1, 'main'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'object'=>1, 'section'=>1, 'slot'=>1, 'style'=>1, 'td'=>1, 'template'=>1, 'th'=>1, 'video'=>1); // Later context-wise dynamic move of ins & del to $inlineKidEleAr
    $inlineKidEleAr = array('abbr'=>1, 'acronym'=>1, 'address'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'caption'=>1, 'cite'=>1, 'code'=>1, 'data'=>1, 'datalist'=>1, 'dfn'=>1, 'dt'=>1, 'em'=>1, 'figcaption'=>1, 'font'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hgroup'=>1, 'i'=>1, 'kbd'=>1, 'label'=>1, 'legend'=>1, 'mark'=>1, 'meter'=>1, 'output'=>1, 'p'=>1, 'picture'=>1, 'pre'=>1, 'progress'=>1, 'q'=>1, 'rb'=>1, 'rt'=>1, 's'=>1, 'samp'=>1, 'small'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'time'=>1, 'tt'=>1, 'u'=>1, 'var'=>1);
    $noKidEleAr = array('area'=>1, 'br'=>1, 'col'=>1, 'command'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'keygen'=>1, 'link'=>1, 'meta'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1);

    // Special parent-child relations.

    $invalidMomKidAr = array('a'=>array('a'=>1, 'address'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'keygen'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'address'=>array('address'=>1, 'article'=>1, 'aside'=>1, 'header'=>1, 'keygen'=>1, 'footer'=>1, 'nav'=>1, 'section'=>1), 'button'=>array('a'=>1, 'address'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'fieldset'=>1, 'form'=>1, 'iframe'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'fieldset'=>array('fieldset'=>1), 'footer'=>array('header'=>1, 'footer'=>1), 'form'=>array('form'=>1), 'header'=>array('header'=>1, 'footer'=>1), 'label'=>array('label'=>1), 'main'=>array('main'=>1), 'meter'=>array('meter'=>1), 'noscript'=>array('script'=>1), 'pre'=>array('big'=>1, 'font'=>1, 'img'=>1, 'object'=>1, 'script'=>1, 'small'=>1, 'sub'=>1, 'sup'=>1), 'progress'=>array('progress'=>1), 'rb'=>array('ruby'=>1), 'rt'=>array('ruby'=>1), 'time'=>array('time'=>1), );
    $invalidKidEleAr = array('a'=>1, 'address'=>1, 'article'=>1, 'aside'=>1, 'big'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'fieldset'=>1, 'font'=>1, 'footer'=>1, 'form'=>1, 'header'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'meter'=>1, 'nav'=>1, 'object'=>1, 'progress'=>1, 'ruby'=>1, 'script'=>1, 'select'=>1, 'small'=>1, 'sub'=>1, 'sup'=>1, 'textarea'=>1, 'time'=>1); // $invalidMomKidAr values
    $invalidMomEleAr = array_keys($invalidMomKidAr);
    $validMomKidAr = array('colgroup'=>array('col'=>1), 'datalist'=>array('option'=>1), 'dir'=>array('li'=>1), 'dl'=>array('dd'=>1, 'dt'=>1), 'hgroup'=>array('h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1), 'menu'=>array('li'=>1), 'ol'=>array('li'=>1), 'optgroup'=>array('option'=>1), 'option'=>array('#pcdata'=>1), 'rbc'=>array('rb'=>1), 'rp'=>array('#pcdata'=>1), 'rtc'=>array('rt'=>1), 'ruby'=>array('rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1), 'select'=>array('optgroup'=>1, 'option'=>1), 'script'=>array('#pcdata'=>1), 'table'=>array('caption'=>1, 'col'=>1, 'colgroup'=>1, 'tfoot'=>1, 'tbody'=>1, 'tr'=>1, 'thead'=>1), 'tbody'=>array('tr'=>1), 'tfoot'=>array('tr'=>1), 'textarea'=>array('#pcdata'=>1), 'thead'=>array('tr'=>1), 'tr'=>array('td'=>1, 'th'=>1), 'ul'=>array('li'=>1)); // Immediate parent-child relation
    if ($GLOBALS['C']['direct_list_nest']) {
        $validMomKidAr['ol'] = $validMomKidAr['ul'] = $validMomKidAr['menu'] += array('menu'=>1, 'ol'=>1, 'ul'=>1);
    }
    $otherValidMomKidAr = array('address'=>array('p'=>1), 'applet'=>array('param'=>1), 'audio'=>array('source'=>1, 'track'=>1), 'blockquote'=>array('script'=>1), 'details'=>array('summary'=>1), 'fieldset'=>array('legend'=>1, '#pcdata'=>1),  'figure'=>array('figcaption'=>1),'form'=>array('script'=>1), 'map'=>array('area'=>1), 'object'=>array('param'=>1, 'embed'=>1), 'video'=>array('source'=>1, 'track'=>1));

    // Valid elements for top-level parent.

    $mom = ((isset($flowEleAr[$parentEle]) && $parentEle != '#pcdata') || isset($otherEleAr[$parentEle])) ? $parentEle : 'div';
    if (isset($noKidEleAr[$mom])) {
        return (!$act ? '' : str_replace(array('<', '>'), array('&lt;', '&gt;'), $t));
    }
    if (isset($validMomKidAr[$mom])) {
        $validInMomEleAr = $validMomKidAr[$mom];
    } elseif (isset($inlineKidEleAr[$mom])) {
        $validInMomEleAr = $inlineEleAr;
        $inlineKidEleAr['del'] = 1;
        $inlineKidEleAr['ins'] = 1;
    } elseif (isset($flowKidEleAr[$mom])) {
        $validInMomEleAr = $flowEleAr;
        unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
    } elseif (isset($blockKidEleAr[$mom])) {
        $validInMomEleAr = $blockEleAr;
        unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
    }
    if (isset($otherValidMomKidAr[$mom])) {
        $validInMomEleAr = $validInMomEleAr + $otherValidMomKidAr[$mom];
    }
    if (isset($invalidMomKidAr[$mom])) {
        $validInMomEleAr = array_diff_assoc($validInMomEleAr, $invalidMomKidAr[$mom]);
    }
    if (strpos($mom, '-')) { // Custom element
        $validInMomEleAr = array('*' => 1, '#pcdata' =>1);
    }

    // Loop over elements

    $t = explode('<', $t);
    $validKidsOfMom = $openEleQueue = array(); // Queue of opened elements
    ob_start();
    for ($i=-1, $eleCount=count($t); ++$i<$eleCount;) {

        // Check element validity as child. Same code as section Finishing below.

        if ($queueLength = count($openEleQueue)) {
            $eleNow = array_pop($openEleQueue);
            $openEleQueue[] = $eleNow;
            if (isset($validMomKidAr[$eleNow])) {
                $validKidsOfMom = $validMomKidAr[$eleNow];
            } elseif (isset($inlineKidEleAr[$eleNow])) {
                $validKidsOfMom = $inlineEleAr;
                $inlineKidEleAr['del'] = 1;
                $inlineKidEleAr['ins'] = 1;
            } elseif (isset($flowKidEleAr[$eleNow])) {
                $validKidsOfMom = $flowEleAr;
                unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
            } elseif (isset($blockKidEleAr[$eleNow])) {
                $validKidsOfMom = $blockEleAr;
                unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
            }
            if (isset($otherValidMomKidAr[$eleNow])) {
                $validKidsOfMom = $validKidsOfMom + $otherValidMomKidAr[$eleNow];
            }
            if (isset($invalidMomKidAr[$eleNow])) {
                $validKidsOfMom = array_diff_assoc($validKidsOfMom, $invalidMomKidAr[$eleNow]);
            }
            if (strpos($eleNow, '-')) { // Custom element
                $validKidsOfMom = array('*'=>1, '#pcdata'=>1);
            }
        } else {
            $validKidsOfMom = $validInMomEleAr;
            unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
        }
        if (isset($ele)
            && ($act == 1
                || (isset($validKidsOfMom['#pcdata'])
                    && ($act == 3
                        || $act == 5
                       )
                   )
               )
            ) {
            echo '&lt;', $slash, $ele, $attrs, '&gt;';
        }
        if (isset($content[0])) {
            if (strlen(trim($content))
                && (($queueLength && isset($blockKidEleAr[$eleNow]))
                    || (isset($blockKidEleAr[$mom]) && !$queueLength)
                   )
            ) {
                echo '<div>', $content, '</div>';
            } elseif ($act < 3 || isset($validKidsOfMom['#pcdata'])) {
                echo $content;
            } elseif (strpos($content, "\x02\x04")) {
                foreach (preg_split('`(\x01\x02[^\x01\x02]+\x02\x01)`', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $m) {
                    echo(substr($m, 0, 2) == "\x01\x02"
                         ? $m
                         : ($act > 4
                            ? preg_replace('`\S`', '', $m)
                            : ''));
                }
            } elseif ($act > 4) {
                echo preg_replace('`\S`', '', $content);
            }
        } // End of section 'Check element validity as child'

        // Get parts of element.

        if (!preg_match('`^(/?)([a-z][^ >]*)([^>]*)>(.*)`sm', $t[$i], $m)) {
            $content = $t[$i];
            continue;
        }
        $slash = null; // Closing tag's slash
        $ele = null; // Name
        $attrs = null; // Attribute string
        $content = null; // Content
        list($all, $slash, $ele, $attrs, $content) = $m;

         // Handle closing tag.

        if ($slash) {
            if (isset($noKidEleAr[$ele]) || !in_array($ele, $openEleQueue)) { // Element empty type or unopened
                continue;
            }
            if ($eleNow == $ele) { // Last open tag
                array_pop($openEleQueue);
                echo '</', $ele, '>';
                unset($ele);
                continue;
            }
            $closedTags = ''; // Nesting, so close open elements as necessary
            for ($j=-1, $cj=count($openEleQueue); ++$j<$cj;) {
                if (($closableEle = array_pop($openEleQueue)) == $ele) {
                    break;
                } else {
                    $closedTags .= "</{$closableEle}>";
                }
            }
            echo $closedTags, '</', $ele, '>';
            unset($ele);
            continue;
        }

        // Handle opening tag.

        if (isset($blockKidEleAr[$ele]) && strlen(trim($content))) { // $blockKidEleAr element needs $blockEleAr element
            $t[$i] = "{$ele}{$attrs}>";
            array_splice($t, $i+1, 0, 'div>'. $content);
            unset($ele, $content);
            ++$eleCount;
            --$i;
            continue;
        }
        if (strpos($ele, '-')) { // Custom element
            $validKidsOfMom[$ele] = 1;
        }
        if ((($queueLength && isset($blockKidEleAr[$eleNow]))
             || (isset($blockKidEleAr[$mom]) && !$queueLength)
            )
            && !isset($blockEleAr[$ele])
            && !isset($validKidsOfMom[$ele])
            && !isset($validKidsOfMom['*'])
           ) {
            array_splice($t, $i, 0, 'div>');
            unset($ele, $content);
            ++$eleCount;
            --$i;
            continue;
        }
        if (!$queueLength
            || !isset($invalidKidEleAr[$ele])
            || !array_intersect($openEleQueue, $invalidMomEleAr)
           ) { // If no open element; mostly immediate parent-child relation should hold
            if (!isset($validKidsOfMom[$ele]) && !isset($validKidsOfMom['*'])) {
                if ($queueLength && isset($closingTagOmitableEleAr[$eleNow])) {
                    echo '</', array_pop($openEleQueue), '>';
                    unset($ele, $content);
                    --$i;
                }
                continue;
            }
            if (!isset($noKidEleAr[$ele])) {
                $openEleQueue[] = $ele;
            }
            echo '<', $ele, $attrs, '>';
            unset($ele);
            continue;
        }
        if (isset($validMomKidAr[$eleNow][$ele])) { // Specific parent-child relation
            if (!isset($noKidEleAr[$ele])) {
                $openEleQueue[] = $ele;
            }
            echo '<', $ele, $attrs, '>';
            unset($ele);
            continue;
        }
        $closedTags = ''; // Nesting, so close open elements as necessary
        $openEleQueue2 = array();
        for ($k=-1, $kc=count($openEleQueue); ++$k<$kc;) {
            $closableEle = $openEleQueue[$k];
            $validKids2 = array();
            if (isset($validMomKidAr[$closableEle])) {
                $openEleQueue2[] = $closableEle;
                continue;
            }
            $validKids2 = isset($inlineKidEleAr[$closableEle]) ? $inlineEleAr : $flowEleAr;
            if (isset($otherValidMomKidAr[$closableEle])) {
                $validKids2 = $validKids2 + $otherValidMomKidAr[$closableEle];
            }
            if (isset($invalidMomKidAr[$closableEle])) {
                $validKids2 = array_diff_assoc($validKids2, $invalidMomKidAr[$closableEle]);
            }
            if (!isset($validKids2[$ele]) && !strpos($ele, '-')) {
                if (!$k && !isset($validInMomEleAr[$ele]) && !isset($validInMomEleAr['*'])) {
                    continue 2;
                }
                $closedTags = "</{$closableEle}>";
                for (;++$k<$kc;) {
                    $closedTags = "</{$openEleQueue[$k]}>{$closedTags}";
                }
                break;
            } else {
                $openEleQueue2[] = $closableEle;
            }
        }
        $openEleQueue = $openEleQueue2;
        if (!isset($noKidEleAr[$ele])) {
            $openEleQueue[] = $ele;
        }
        echo $closedTags, '<', $ele, $attrs, '>';
        unset($ele);
        continue;
    } // End of For - loop over elements

    // Finishing. Same code as section 'Check element validity as child'.

    if ($queueLength = count($openEleQueue)) {
        $eleNow = array_pop($openEleQueue);
        $openEleQueue[] = $eleNow;
        if (isset($validMomKidAr[$eleNow])) {
            $validKidsOfMom = $validMomKidAr[$eleNow];
        } elseif (isset($inlineKidEleAr[$eleNow])) {
            $validKidsOfMom = $inlineEleAr;
            $inlineKidEleAr['del'] = 1;
            $inlineKidEleAr['ins'] = 1;
        } elseif (isset($flowKidEleAr[$eleNow])) {
            $validKidsOfMom = $flowEleAr;
            unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
        } elseif (isset($blockKidEleAr[$eleNow])) {
            $validKidsOfMom = $blockEleAr;
            unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
        }
        if (isset($otherValidMomKidAr[$eleNow])) {
            $validKidsOfMom = $validKidsOfMom + $otherValidMomKidAr[$eleNow];
        }
        if (isset($invalidMomKidAr[$eleNow])) {
            $validKidsOfMom = array_diff_assoc($validKidsOfMom, $invalidMomKidAr[$eleNow]);
        }
        if (strpos($eleNow, '-')) { // Custom element
            $validKidsOfMom = array('*'=>1, '#pcdata'=>1);
        }
    } else {
        $validKidsOfMom = $validInMomEleAr;
        unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
    }
    if (isset($ele)
        && ($act == 1
            || (isset($validKidsOfMom['#pcdata'])
                && ($act == 3
                    || $act == 5
                   )
               )
           )
        ) {
        echo '&lt;', $slash, $ele, $attrs, '&gt;';
    }
    if (isset($content[0])) {
        if (strlen(trim($content))
            && (($queueLength && isset($blockKidEleAr[$eleNow]))
                || (isset($blockKidEleAr[$mom]) && !$queueLength)
               )
        ) {
            echo '<div>', $content, '</div>';
        } elseif ($act < 3 || isset($validKidsOfMom['#pcdata'])) {
            echo $content;
        } elseif (strpos($content, "\x02\x04")) {
            foreach (preg_split('`(\x01\x02[^\x01\x02]+\x02\x01)`', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $m) {
                echo(substr($m, 0, 2) == "\x01\x02"
                     ? $m
                     : ($act > 4
                        ? preg_replace('`\S`', '', $m)
                        : ''));
            }
        } elseif ($act > 4) {
            echo preg_replace('`\S`', '', $content);
        }
    } // End of section 'Finishing'

    while (!empty($openEleQueue) && ($ele = array_pop($openEleQueue))) {
        echo '</', $ele, '>';
    }
    $o = ob_get_contents();
    ob_end_clean();
    return $o;
}

/**
 * Handle comment/CDATA section.
 *
 * Filter/sanitize as per $config and disguise special characters.
 *
 * @param  array  $t Array result of preg_replace, with potential comment/CDATA.
 * @return string    Sanitized comment/CDATA with disguised special characters.
 */
function hl_cmtcd($t)
{
    $t = $t[0];
    global $C;
    if (!($rule = $C[$type = $t[3] == '-' ? 'comment' : 'cdata'])) {
        return $t;
    }
    if ($rule == 1) {
        return '';
    }
    if ($type == 'comment') {
        if (substr(($t = preg_replace('`--+`', '-', substr($t, 4, -3))), -1) != ' ') {
            $t .= $rule == 4 ? '' : ' ';
        }
    } else {
        $t = substr($t, 1, -1);
    }
    $t = $rule == 2 ? str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $t) : $t;
    return str_replace(array('&', '<', '>'), array("\x03", "\x04", "\x05"), ($type == 'comment' ? "\x01\x02\x04!--$t--\x05\x02\x01" : "\x01\x01\x04$t\x05\x01\x01"));
}

/**
 * Handle entity.
 *
 * As needed, convert between named and hexadecimal form,
 * or neutralize by changing '&' to '&amp;'.
 *
 * @param  array  $t Array result of preg_replace, with potential entity.
 * @return string    Neutralized or converted entity.
 */
function hl_ent($t)
{
    global $C;
    $t = $t[1];
    static $reservedEntAr = array('amp'=>1, 'gt'=>1, 'lt'=>1, 'quot'=>1);
    static $entNameAr = array('Aacute'=>'193', 'aacute'=>'225', 'Acirc'=>'194', 'acirc'=>'226', 'acute'=>'180', 'AElig'=>'198', 'aelig'=>'230', 'Agrave'=>'192', 'agrave'=>'224', 'alefsym'=>'8501', 'Alpha'=>'913', 'alpha'=>'945', 'and'=>'8743', 'ang'=>'8736', 'apos'=>'39', 'Aring'=>'197', 'aring'=>'229', 'asymp'=>'8776', 'Atilde'=>'195', 'atilde'=>'227', 'Auml'=>'196', 'auml'=>'228', 'bdquo'=>'8222', 'Beta'=>'914', 'beta'=>'946', 'brvbar'=>'166', 'bull'=>'8226', 'cap'=>'8745', 'Ccedil'=>'199', 'ccedil'=>'231', 'cedil'=>'184', 'cent'=>'162', 'Chi'=>'935', 'chi'=>'967', 'circ'=>'710', 'clubs'=>'9827', 'cong'=>'8773', 'copy'=>'169', 'crarr'=>'8629', 'cup'=>'8746', 'curren'=>'164', 'dagger'=>'8224', 'Dagger'=>'8225', 'darr'=>'8595', 'dArr'=>'8659', 'deg'=>'176', 'Delta'=>'916', 'delta'=>'948', 'diams'=>'9830', 'divide'=>'247', 'Eacute'=>'201', 'eacute'=>'233', 'Ecirc'=>'202', 'ecirc'=>'234', 'Egrave'=>'200', 'egrave'=>'232', 'empty'=>'8709', 'emsp'=>'8195', 'ensp'=>'8194', 'Epsilon'=>'917', 'epsilon'=>'949', 'equiv'=>'8801', 'Eta'=>'919', 'eta'=>'951', 'ETH'=>'208', 'eth'=>'240', 'Euml'=>'203', 'euml'=>'235', 'euro'=>'8364', 'exist'=>'8707', 'fnof'=>'402', 'forall'=>'8704', 'frac12'=>'189', 'frac14'=>'188', 'frac34'=>'190', 'frasl'=>'8260', 'Gamma'=>'915', 'gamma'=>'947', 'ge'=>'8805', 'harr'=>'8596', 'hArr'=>'8660', 'hearts'=>'9829', 'hellip'=>'8230', 'Iacute'=>'205', 'iacute'=>'237', 'Icirc'=>'206', 'icirc'=>'238', 'iexcl'=>'161', 'Igrave'=>'204', 'igrave'=>'236', 'image'=>'8465', 'infin'=>'8734', 'int'=>'8747', 'Iota'=>'921', 'iota'=>'953', 'iquest'=>'191', 'isin'=>'8712', 'Iuml'=>'207', 'iuml'=>'239', 'Kappa'=>'922', 'kappa'=>'954', 'Lambda'=>'923', 'lambda'=>'955', 'lang'=>'9001', 'laquo'=>'171', 'larr'=>'8592', 'lArr'=>'8656', 'lceil'=>'8968', 'ldquo'=>'8220', 'le'=>'8804', 'lfloor'=>'8970', 'lowast'=>'8727', 'loz'=>'9674', 'lrm'=>'8206', 'lsaquo'=>'8249', 'lsquo'=>'8216', 'macr'=>'175', 'mdash'=>'8212', 'micro'=>'181', 'middot'=>'183', 'minus'=>'8722', 'Mu'=>'924', 'mu'=>'956', 'nabla'=>'8711', 'nbsp'=>'160', 'ndash'=>'8211', 'ne'=>'8800', 'ni'=>'8715', 'not'=>'172', 'notin'=>'8713', 'nsub'=>'8836', 'Ntilde'=>'209', 'ntilde'=>'241', 'Nu'=>'925', 'nu'=>'957', 'Oacute'=>'211', 'oacute'=>'243', 'Ocirc'=>'212', 'ocirc'=>'244', 'OElig'=>'338', 'oelig'=>'339', 'Ograve'=>'210', 'ograve'=>'242', 'oline'=>'8254', 'Omega'=>'937', 'omega'=>'969', 'Omicron'=>'927', 'omicron'=>'959', 'oplus'=>'8853', 'or'=>'8744', 'ordf'=>'170', 'ordm'=>'186', 'Oslash'=>'216', 'oslash'=>'248', 'Otilde'=>'213', 'otilde'=>'245', 'otimes'=>'8855', 'Ouml'=>'214', 'ouml'=>'246', 'para'=>'182', 'part'=>'8706', 'permil'=>'8240', 'perp'=>'8869', 'Phi'=>'934', 'phi'=>'966', 'Pi'=>'928', 'pi'=>'960', 'piv'=>'982', 'plusmn'=>'177', 'pound'=>'163', 'prime'=>'8242', 'Prime'=>'8243', 'prod'=>'8719', 'prop'=>'8733', 'Psi'=>'936', 'psi'=>'968', 'radic'=>'8730', 'rang'=>'9002', 'raquo'=>'187', 'rarr'=>'8594', 'rArr'=>'8658', 'rceil'=>'8969', 'rdquo'=>'8221', 'real'=>'8476', 'reg'=>'174', 'rfloor'=>'8971', 'Rho'=>'929', 'rho'=>'961', 'rlm'=>'8207', 'rsaquo'=>'8250', 'rsquo'=>'8217', 'sbquo'=>'8218', 'Scaron'=>'352', 'scaron'=>'353', 'sdot'=>'8901', 'sect'=>'167', 'shy'=>'173', 'Sigma'=>'931', 'sigma'=>'963', 'sigmaf'=>'962', 'sim'=>'8764', 'spades'=>'9824', 'sub'=>'8834', 'sube'=>'8838', 'sum'=>'8721', 'sup'=>'8835', 'sup1'=>'185', 'sup2'=>'178', 'sup3'=>'179', 'supe'=>'8839', 'szlig'=>'223', 'Tau'=>'932', 'tau'=>'964', 'there4'=>'8756', 'Theta'=>'920', 'theta'=>'952', 'thetasym'=>'977', 'thinsp'=>'8201', 'THORN'=>'222', 'thorn'=>'254', 'tilde'=>'732', 'times'=>'215', 'trade'=>'8482', 'Uacute'=>'218', 'uacute'=>'250', 'uarr'=>'8593', 'uArr'=>'8657', 'Ucirc'=>'219', 'ucirc'=>'251', 'Ugrave'=>'217', 'ugrave'=>'249', 'uml'=>'168', 'upsih'=>'978', 'Upsilon'=>'933', 'upsilon'=>'965', 'Uuml'=>'220', 'uuml'=>'252', 'weierp'=>'8472', 'Xi'=>'926', 'xi'=>'958', 'Yacute'=>'221', 'yacute'=>'253', 'yen'=>'165', 'yuml'=>'255', 'Yuml'=>'376', 'Zeta'=>'918', 'zeta'=>'950', 'zwj'=>'8205', 'zwnj'=>'8204');
    if ($t[0] != '#') {
        return ($C['and_mark'] ? "\x06" : '&')
        . (isset($reservedEntAr[$t])
           ? $t
           : (isset($entNameAr[$t])
              ? (!$C['named_entity']
                 ? '#'. ($C['hexdec_entity'] > 1
                         ? 'x'. dechex($entNameAr[$t])
                         : $entNameAr[$t])
                 : $t)
              : 'amp;'. $t))
        . ';';
    }
    if (($n = ctype_digit($t = substr($t, 1)) ? intval($t) : hexdec(substr($t, 1))) < 9
        || ($n > 13 && $n < 32)
        || $n == 11
        || $n == 12
        || ($n > 126 && $n < 160 && $n != 133)
        || ($n > 55295
            && ($n < 57344
                || ($n > 64975 && $n < 64992)
                || $n == 65534
                || $n == 65535
                || $n > 1114111)
            )
        ) {
        return ($C['and_mark'] ? "\x06" : '&'). "amp;#{$t};";
    }
    return ($C['and_mark'] ? "\x06" : '&'). '#'. (((ctype_digit($t) && $C['hexdec_entity'] < 2) or !$C['hexdec_entity']) ? $n : 'x'. dechex($n)). ';';
}

/**
 * Handle URL to convert to relative/absolute type,
 * block scheme, or add anti-spam text.
 *
 * @param  mixed  $url  URL string, or array with URL value (if $attr is null).
 * @param  mixed  $attr Attribute name string, or null (if $url is array).
 * @return string       With URL after any conversion/obfuscation.
 */
function hl_prot($url, $attr=null)
{
    global $C;
    $preUrl = $postUrl = '';
    static $blocker = 'denied:';
    if ($attr == null) { // style attribute value
        $attr = 'style';
        $preUrl = $url[1];
        $postUrl = $url[3];
        $url = trim($url[2]);
    }
    $okSchemeAr = isset($C['schemes'][$attr]) ? $C['schemes'][$attr] : $C['schemes']['*'];
    if (isset($okSchemeAr['!']) && substr($url, 0, 7) != $blocker) {
        $url = "{$blocker}{$url}";
    }
    if (isset($okSchemeAr['*'])
        || !strcspn($url, '#?;')
        || substr($url, 0, strlen($blocker)) == $blocker
       ) {
        return "{$preUrl}{$url}{$postUrl}";
    }
    if (preg_match('`^([^:?[@!$()*,=/\'\]]+?)(:|&#(58|x3a);|%3a|\\\\0{0,4}3a).`i', $url, $m)
        && !isset($okSchemeAr[strtolower($m[1])]) // Block specially crafted scheme (suggests malice)
       ) {
        return "{$preUrl}{$blocker}{$url}{$postUrl}";
    }
    if ($C['abs_url']) {
        if ($C['abs_url'] == -1 && strpos($url, $C['base_url']) === 0) { // Make URL relative
            $url = substr($url, strlen($C['base_url']));
        } elseif (empty($m[1])) { // Make URL absolute
            if (substr($url, 0, 2) == '//') {
                $url = substr($C['base_url'], 0, strpos($C['base_url'], ':') + 1). $url;
            } elseif ($url[0] == '/') {
                $url = preg_replace('`(^.+?://[^/]+)(.*)`', '$1', $C['base_url']). $url;
            } elseif (strcspn($url, './')) {
                $url = $C['base_url']. $url;
            } else {
                preg_match('`^([a-zA-Z\d\-+.]+://[^/]+)(.*)`', $C['base_url'], $m);
                $url = preg_replace('`(?<=/)\./`', '', $m[2]. $url);
                while (preg_match('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', $url)) {
                    $url = preg_replace('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', '', $url);
                }
                $url = $m[1]. $url;
            }
        }
    }
    return "{$preUrl}{$url}{$postUrl}";
}

/**
 * Check regex pattern for PHP error.
 *
 * @param  string $t Pattern including limiters/modifiers.
 * @return int       0 or 1 if pattern is invalid or valid, respectively.
 */
function hl_regex($t)
{
    if (empty($t) || !is_string($t)) {
        return 0;
    }
    if ($funcsExist = function_exists('error_clear_last') && function_exists('error_get_last')) {
        error_clear_last();
    } else {
        if ($valTrackErr = ini_get('track_errors')) {
            $valMsgErr = isset($php_errormsg) ? $php_errormsg : null;
        } else {
            ini_set('track_errors', '1');
        }
        unset($php_errormsg);
    }
    if (($valShowErr = ini_get('display_errors'))) {
        ini_set('display_errors', '0');
    }
    preg_match($t, '');
    if ($funcsExist) {
        $out = error_get_last() == null ? 1 : 0;
    } else {
        $out = isset($php_errormsg) ? 0 : 1;
        if ($valTrackErr) {
            $php_errormsg = isset($valMsgErr) ? $valMsgErr : null;
        } else {
            ini_set('track_errors', '0');
        }
    }
    if ($valShowErr) {
        ini_set('display_errors', '1');
    }
    return $out;
}

/**
 * Parse $spec htmLawed argument as array.
 *
 * See documentation on $spec specification.
 *
 * @param  string $t Value of $spec.
 * @return array     Multidimensional array of form: tag -> attribute -> rule.
 */
function hl_spec($t)
{
    $out  = array();

    // Hide special characters used for rules.

    if (!function_exists('hl_aux1')) {
        function hl_aux1($m) {
            return substr(str_replace(array(";", "|", "~", " ", ",", "/", "(", ")", '`"'), array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", '"'), $m[0]), 1, -1);
        }
    }
    $t  = str_replace(array("\t", "\r", "\n", ' '), '', preg_replace_callback('/"(?>(`.|[^"])*)"/sm', 'hl_aux1', trim($t)));

    // Tag, attribute, and rule separators: semi-colon, comma, and slash respectively.

    for ($i = count(($t = explode(';', $t))); --$i>=0;) {
        $ele = $t[$i];
        if (empty($ele)
            || ($tagPos = strpos($ele, '=')) === false
            || !strlen(($tagSpec = substr($ele, $tagPos + 1)))
        ) {
            continue;
        }
        $ruleAr = $denyAttrAr = array();
        foreach (explode(',', $tagSpec) as $v) {
            if (!preg_match('`^([a-z:\-\*]+)(?:\((.*?)\))?`i', $v, $m)) {
                continue;
            }
            if (($attr = strtolower($m[1])) == '-*') {
                $denyAttrAr['*'] = 1;
                continue;
            }
            if ($attr[0] == '-') {
                $denyAttrAr[substr($attr, 1)] = 1;
                continue;
            }
            if (!isset($m[2])) {
                $ruleAr[$attr] = 1;
                continue;
            }
            foreach (explode('/', $m[2]) as $m) {
                if (empty($m)
                    || ($rulePos = strpos($m, '=')) == 0
                    || $rulePos < 5 // Shortest rule: oneof
                ) {
                    $ruleAr[$attr] = 1;
                    continue;
                }
                $rule = strtolower(substr($m, 0, $rulePos));
                $ruleAr[$attr][$rule] = str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08"), array(";", "|", "~", " ", ",", "/", "(", ")"), substr($m, $rulePos + 1));
            }
            if (isset($ruleAr[$attr]['match']) && !hl_regex($ruleAr[$attr]['match'])) {
                unset($ruleAr[$attr]['match']);
            }
            if (isset($ruleAr[$attr]['nomatch']) && !hl_regex($ruleAr[$attr]['nomatch'])) {
                unset($ruleAr[$attr]['nomatch']);
            }
        }

        if (!count($ruleAr) && !count($denyAttrAr)) {
            continue;
        }
        foreach (explode(',', substr($ele, 0, $tagPos)) as $tag) {
            if (!strlen(($tag = strtolower($tag)))) {
                continue;
            }
            if (count($ruleAr)) {
                $out[$tag] = !isset($out[$tag]) ? $ruleAr : array_merge($out[$tag], $ruleAr);
            }
            if (count($denyAttrAr)) {
                $out[$tag]['deny'] = !isset($out[$tag]['deny']) ? $denyAttrAr : array_merge($out[$tag]['deny'], $denyAttrAr);
            }
        }
    }

    return $out;
}

/**
 * Handle tag text with </> limiters.
 *
 * Also handles attributes in opening tags.
 *
 * @param  array  $t Result of preg_replace call.
 * @return string    Tag, with any attribute string,
 *                   or text with </> neutralized into entities, or empty.
 */
function hl_tag($t)
{
    $t = $t[0];
    global $C;

    // Check if </> character not in tag.

    if ($t == '< ') {
        return '&lt; ';
    }
    if ($t == '>') {
        return '&gt;';
    }
    if (!preg_match('`^<(/?)([a-zA-Z][^\s>]*)([^>]*?)\s?>$`m', $t, $m)) { // Get tag with element name and attributes
        return str_replace(array('<', '>'), array('&lt;', '&gt;'), $t);
    }

    // Check if element not permitted. Custom element names have certain requirements.

    $ele = strtolower($m[2]);
    static $invalidCustomEleAr = array('annotation-xml'=>1, 'color-profile'=>1, 'font-face'=>1, 'font-face-src'=>1, 'font-face-uri'=>1, 'font-face-format'=>1, 'font-face-name'=>1, 'missing-glyph'=>1);
    if ((!strpos($ele, '-') && !isset($C['elements'][$ele])) // Not custom element
        || (strpos($ele, '-') && (isset($C['elements']['-' . $ele])
                                  || (!$C['any_custom_element'] && !isset($C['elements'][$ele]))
                                  || isset($invalidCustomEleAr[$ele])
                                  || preg_match('`[^-._0-9a-z\xb7\xc0-\xd6\xd8-\xf6\xf8-\x{2ff}'.
                                         '\x{370}-\x{37d}\x{37f}-\x{1fff}\x{200c}-\x{200d}\x{2070}-\x{218f}'.
                                         '\x{2c00}-\x{2fef}\x{3001}-\x{d7ff}\x{f900}-\x{fdcf}'.
                                         '\x{fdf0}-\x{fffd}\x{10000}-\x{effff}]`u'
                                         , $ele)
                                 )
           )
       ) {
        return (($C['keep_bad']%2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');
    }

    // Attribute string.

    $attrStr = str_replace(array("\n", "\r", "\t"), ' ', trim($m[3]));

    // Transform deprecated element.

    static $deprecatedEleAr = array('acronym'=>1, 'applet'=>1, 'big'=>1, 'center'=>1, 'dir'=>1, 'font'=>1, 'isindex'=>1, 's'=>1, 'strike'=>1, 'tt'=>1);
    if ($C['make_tag_strict'] && isset($deprecatedEleAr[$ele])) {
        $eleTransformed = hl_tag2($ele, $attrStr, $C['make_tag_strict']); // hl_tag2 uses referencing
        if (!$ele) {
            return (($C['keep_bad'] % 2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');
        }
    }

    // Handle closing tag.

    static $emptyEleAr = array('area'=>1, 'br'=>1, 'col'=>1, 'command'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'keygen'=>1, 'link'=>1, 'meta'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1);
    if (!empty($m[1])) {
        return (!isset($emptyEleAr[$ele])
                ? (empty($C['hook_tag'])
                   ? "</$ele>"
                   : $C['hook_tag']($ele))
                : (($C['keep_bad']) % 2
                   ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t)
                   : '')
        );
    }

    // Handle opening tag.

    // -- Sets of possible attributes.

    // .. Element-specific (not global).

    static $attrEleAr = array('abbr'=>array('td'=>1, 'th'=>1), 'accept'=>array('form'=>1, 'input'=>1), 'accept-charset'=>array('form'=>1), 'action'=>array('form'=>1), 'align'=>array('applet'=>1, 'caption'=>1, 'col'=>1, 'colgroup'=>1, 'div'=>1, 'embed'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'allowfullscreen'=>array('iframe'=>1), 'alt'=>array('applet'=>1, 'area'=>1, 'img'=>1, 'input'=>1), 'archive'=>array('applet'=>1, 'object'=>1), 'async'=>array('script'=>1), 'autocomplete'=>array('form'=>1, 'input'=>1), 'autofocus'=>array('button'=>1, 'input'=>1, 'keygen'=>1, 'select'=>1, 'textarea'=>1), 'autoplay'=>array('audio'=>1, 'video'=>1), 'axis'=>array('td'=>1, 'th'=>1), 'bgcolor'=>array('embed'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1), 'border'=>array('img'=>1, 'object'=>1, 'table'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellpadding'=>array('table'=>1), 'cellspacing'=>array('table'=>1), 'challenge'=>array('keygen'=>1), 'char'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charoff'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charset'=>array('a'=>1, 'script'=>1), 'checked'=>array('command'=>1, 'input'=>1), 'cite'=>array('blockquote'=>1, 'del'=>1, 'ins'=>1, 'q'=>1), 'classid'=>array('object'=>1), 'clear'=>array('br'=>1), 'code'=>array('applet'=>1), 'codebase'=>array('applet'=>1, 'object'=>1), 'codetype'=>array('object'=>1), 'color'=>array('font'=>1), 'cols'=>array('textarea'=>1), 'colspan'=>array('td'=>1, 'th'=>1), 'compact'=>array('dir'=>1, 'dl'=>1, 'menu'=>1, 'ol'=>1, 'ul'=>1), 'content'=>array('meta'=>1), 'controls'=>array('audio'=>1, 'video'=>1), 'coords'=>array('a'=>1, 'area'=>1), 'crossorigin'=>array('img'=>1), 'data'=>array('object'=>1), 'datetime'=>array('del'=>1, 'ins'=>1, 'time'=>1), 'declare'=>array('object'=>1), 'default'=>array('track'=>1), 'defer'=>array('script'=>1), 'dirname'=>array('input'=>1, 'textarea'=>1), 'disabled'=>array('button'=>1, 'command'=>1, 'fieldset'=>1, 'input'=>1, 'keygen'=>1, 'optgroup'=>1, 'option'=>1, 'select'=>1, 'textarea'=>1), 'download'=>array('a'=>1), 'enctype'=>array('form'=>1), 'face'=>array('font'=>1), 'flashvars'=>array('embed'=>1), 'for'=>array('label'=>1, 'output'=>1), 'form'=>array('button'=>1, 'fieldset'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'object'=>1, 'output'=>1, 'select'=>1, 'textarea'=>1), 'formaction'=>array('button'=>1, 'input'=>1), 'formenctype'=>array('button'=>1, 'input'=>1), 'formmethod'=>array('button'=>1, 'input'=>1), 'formnovalidate'=>array('button'=>1, 'input'=>1), 'formtarget'=>array('button'=>1, 'input'=>1), 'frame'=>array('table'=>1), 'frameborder'=>array('iframe'=>1), 'headers'=>array('td'=>1, 'th'=>1), 'height'=>array('applet'=>1, 'canvas'=>1, 'embed'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'td'=>1, 'th'=>1, 'video'=>1), 'high'=>array('meter'=>1), 'href'=>array('a'=>1, 'area'=>1, 'link'=>1), 'hreflang'=>array('a'=>1, 'area'=>1, 'link'=>1), 'hspace'=>array('applet'=>1, 'embed'=>1, 'img'=>1, 'object'=>1), 'icon'=>array('command'=>1), 'ismap'=>array('img'=>1, 'input'=>1), 'keyparams'=>array('keygen'=>1), 'keytype'=>array('keygen'=>1), 'kind'=>array('track'=>1), 'label'=>array('command'=>1, 'menu'=>1, 'option'=>1, 'optgroup'=>1, 'track'=>1), 'language'=>array('script'=>1), 'list'=>array('input'=>1), 'longdesc'=>array('img'=>1, 'iframe'=>1), 'loop'=>array('audio'=>1, 'video'=>1), 'low'=>array('meter'=>1), 'marginheight'=>array('iframe'=>1), 'marginwidth'=>array('iframe'=>1), 'max'=>array('input'=>1, 'meter'=>1, 'progress'=>1), 'maxlength'=>array('input'=>1, 'textarea'=>1), 'media'=>array('a'=>1, 'area'=>1, 'link'=>1, 'source'=>1, 'style'=>1), 'mediagroup'=>array('audio'=>1, 'video'=>1), 'method'=>array('form'=>1), 'min'=>array('input'=>1, 'meter'=>1), 'model'=>array('embed'=>1), 'multiple'=>array('input'=>1, 'select'=>1), 'muted'=>array('audio'=>1, 'video'=>1), 'name'=>array('a'=>1, 'applet'=>1, 'button'=>1, 'embed'=>1, 'fieldset'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'keygen'=>1, 'map'=>1, 'object'=>1, 'output'=>1, 'param'=>1, 'select'=>1, 'slot'=>1, 'textarea'=>1), 'nohref'=>array('area'=>1), 'noshade'=>array('hr'=>1), 'novalidate'=>array('form'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'object'=>array('applet'=>1), 'open'=>array('details'=>1, 'dialog'=>1), 'optimum'=>array('meter'=>1), 'pattern'=>array('input'=>1), 'ping'=>array('a'=>1, 'area'=>1), 'placeholder'=>array('input'=>1, 'textarea'=>1), 'pluginspage'=>array('embed'=>1), 'pluginurl'=>array('embed'=>1), 'poster'=>array('video'=>1), 'pqg'=>array('keygen'=>1), 'preload'=>array('audio'=>1, 'video'=>1), 'prompt'=>array('isindex'=>1), 'pubdate'=>array('time'=>1), 'radiogroup'=>array('command'=>1), 'readonly'=>array('input'=>1, 'textarea'=>1), 'rel'=>array('a'=>1, 'area'=>1, 'link'=>1), 'required'=>array('input'=>1, 'select'=>1, 'textarea'=>1), 'rev'=>array('a'=>1), 'reversed'=>array('ol'=>1), 'rows'=>array('textarea'=>1), 'rowspan'=>array('td'=>1, 'th'=>1), 'rules'=>array('table'=>1), 'sandbox'=>array('iframe'=>1), 'scope'=>array('td'=>1, 'th'=>1), 'scoped'=>array('style'=>1), 'scrolling'=>array('iframe'=>1), 'seamless'=>array('iframe'=>1), 'selected'=>array('option'=>1), 'shape'=>array('a'=>1, 'area'=>1), 'size'=>array('font'=>1, 'hr'=>1, 'input'=>1, 'select'=>1), 'sizes'=>array('link'=>1), 'span'=>array('col'=>1, 'colgroup'=>1), 'src'=>array('audio'=>1, 'embed'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'script'=>1, 'source'=>1, 'track'=>1, 'video'=>1), 'srcdoc'=>array('iframe'=>1), 'srclang'=>array('track'=>1), 'srcset'=>array('img'=>1), 'standby'=>array('object'=>1), 'start'=>array('ol'=>1), 'step'=>array('input'=>1), 'summary'=>array('table'=>1), 'target'=>array('a'=>1, 'area'=>1, 'form'=>1), 'type'=>array('a'=>1, 'area'=>1, 'button'=>1, 'command'=>1, 'embed'=>1, 'input'=>1, 'li'=>1, 'link'=>1, 'menu'=>1, 'object'=>1, 'ol'=>1, 'param'=>1, 'script'=>1, 'source'=>1, 'style'=>1, 'ul'=>1), 'typemustmatch'=>array('object'=>1), 'usemap'=>array('img'=>1, 'input'=>1, 'object'=>1), 'valign'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'value'=>array('button'=>1, 'data'=>1, 'input'=>1, 'li'=>1, 'meter'=>1, 'option'=>1, 'param'=>1, 'progress'=>1), 'valuetype'=>array('param'=>1), 'vspace'=>array('applet'=>1, 'embed'=>1, 'img'=>1, 'object'=>1), 'width'=>array('applet'=>1, 'canvas'=>1, 'col'=>1, 'colgroup'=>1, 'embed'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'pre'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'video'=>1), 'wmode'=>array('embed'=>1), 'wrap'=>array('textarea'=>1));

    // .. Empty (value not required).

    static $emptyAttrAr = array('allowfullscreen'=>1, 'checkbox'=>1, 'checked'=>1, 'command'=>1, 'compact'=>1, 'declare'=>1, 'defer'=>1, 'default'=>1, 'disabled'=>1, 'hidden'=>1, 'inert'=>1, 'ismap'=>1, 'itemscope'=>1, 'multiple'=>1, 'nohref'=>1, 'noresize'=>1, 'noshade'=>1, 'nowrap'=>1, 'open'=>1, 'radio'=>1, 'readonly'=>1, 'required'=>1, 'reversed'=>1, 'selected'=>1);

    // .. Global.

    static $ariaAttrAr = array('aria-activedescendant'=>1, 'aria-atomic'=>1, 'aria-autocomplete'=>1, 'aria-braillelabel'=>1, 'aria-brailleroledescription'=>1, 'aria-busy'=>1, 'aria-checked'=>1, 'aria-colcount'=>1, 'aria-colindex'=>1, 'aria-colindextext'=>1, 'aria-colspan'=>1, 'aria-controls'=>1, 'aria-current'=>1, 'aria-describedby'=>1, 'aria-description'=>1, 'aria-details'=>1, 'aria-disabled'=>1, 'aria-dropeffect'=>1, 'aria-errormessage'=>1, 'aria-expanded'=>1, 'aria-flowto'=>1, 'aria-grabbed'=>1, 'aria-haspopup'=>1, 'aria-hidden'=>1, 'aria-invalid'=>1, 'aria-keyshortcuts'=>1, 'aria-label'=>1, 'aria-labelledby'=>1, 'aria-level'=>1, 'aria-live'=>1, 'aria-multiline'=>1, 'aria-multiselectable'=>1, 'aria-orientation'=>1, 'aria-owns'=>1, 'aria-placeholder'=>1, 'aria-posinset'=>1, 'aria-pressed'=>1, 'aria-readonly'=>1, 'aria-relevant'=>1, 'aria-required'=>1, 'aria-roledescription'=>1, 'aria-rowcount'=>1, 'aria-rowindex'=>1, 'aria-rowindextext'=>1, 'aria-rowspan'=>1, 'aria-selected'=>1, 'aria-setsize'=>1, 'aria-sort'=>1, 'aria-valuemax'=>1, 'aria-valuemin'=>1, 'aria-valuenow'=>1, 'aria-valuetext'=>1);
    static $eventAttrAr = array('onabort'=>1, 'onblur'=>1, 'oncanplay'=>1, 'oncanplaythrough'=>1, 'onchange'=>1, 'onclick'=>1, 'oncontextmenu'=>1, 'oncopy'=>1, 'oncuechange'=>1, 'oncut'=>1, 'ondblclick'=>1, 'ondrag'=>1, 'ondragend'=>1, 'ondragenter'=>1, 'ondragleave'=>1, 'ondragover'=>1, 'ondragstart'=>1, 'ondrop'=>1, 'ondurationchange'=>1, 'onemptied'=>1, 'onended'=>1, 'onerror'=>1, 'onfocus'=>1, 'onformchange'=>1, 'onforminput'=>1, 'oninput'=>1, 'oninvalid'=>1, 'onkeydown'=>1, 'onkeypress'=>1, 'onkeyup'=>1, 'onload'=>1, 'onloadeddata'=>1, 'onloadedmetadata'=>1, 'onloadstart'=>1, 'onlostpointercapture'=>1, 'onmousedown'=>1, 'onmousemove'=>1, 'onmouseout'=>1, 'onmouseover'=>1, 'onmouseup'=>1, 'onmousewheel'=>1, 'onpaste'=>1, 'onpause'=>1, 'onplay'=>1, 'onplaying'=>1, 'onpointercancel'=>1, 'ongotpointercapture'=>1, 'onpointerdown'=>1, 'onpointerenter'=>1, 'onpointerleave'=>1, 'onpointermove'=>1, 'onpointerout'=>1, 'onpointerover'=>1, 'onpointerup'=>1, 'onprogress'=>1, 'onratechange'=>1, 'onreadystatechange'=>1, 'onreset'=>1, 'onsearch'=>1, 'onscroll'=>1, 'onseeked'=>1, 'onseeking'=>1, 'onselect'=>1, 'onshow'=>1, 'onstalled'=>1, 'onsubmit'=>1, 'onsuspend'=>1, 'ontimeupdate'=>1, 'ontoggle'=>1, 'ontouchcancel'=>1, 'ontouchend'=>1, 'ontouchmove'=>1, 'ontouchstart'=>1, 'onvolumechange'=>1, 'onwaiting'=>1, 'onwheel'=>1, 'onauxclick'=>1, 'oncancel'=>1, 'onclose'=>1, 'oncontextlost'=>1, 'oncontextrestored'=>1, 'onformdata'=>1, 'onmouseenter'=>1, 'onmouseleave'=>1, 'onresize'=>1, 'onsecuritypolicyviolation'=>1, 'onslotchange'=>1);
    static $otherGlobalAttrAr = array('accesskey'=>1, 'autocapitalize'=>1, 'autofocus'=>1, 'class'=>1, 'contenteditable'=>1, 'contextmenu'=>1, 'dir'=>1, 'draggable'=>1, 'dropzone'=>1, 'enterkeyhint'=>1, 'hidden'=>1, 'id'=>1, 'inert'=>1, 'inputmode'=>1, 'is'=>1, 'itemid'=>1, 'itemprop'=>1, 'itemref'=>1, 'itemscope'=>1, 'itemtype'=>1, 'lang'=>1, 'nonce'=>1, 'role'=>1, 'slot'=>1, 'spellcheck'=>1, 'style'=>1, 'tabindex'=>1, 'title'=>1, 'translate'=>1, 'xmlns'=>1, 'xml:base'=>1, 'xml:lang'=>1, 'xml:space'=>1);
static $urlAttrAr = array('action'=>1, 'cite'=>1, 'classid'=>1, 'codebase'=>1, 'data'=>1, 'href'=>1, 'itemtype'=>1, 'longdesc'=>1, 'model'=>1, 'pluginspage'=>1, 'pluginurl'=>1, 'src'=>1, 'srcset'=>1, 'usemap'=>1); // Need scheme check; excludes style, on*

    // .. Deprecated.

    $alterDeprecAttr = 0;
    if ($C['no_deprecated_attr']) {
        static $deprecAttrEleAr = array('align'=>array('caption'=>1, 'div'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1), 'bgcolor'=>array('table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1), 'border'=>array('object'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellspacing'=>array('table'=>1), 'clear'=>array('br'=>1), 'compact'=>array('dl'=>1, 'ol'=>1, 'ul'=>1), 'height'=>array('td'=>1, 'th'=>1), 'hspace'=>array('img'=>1, 'object'=>1), 'language'=>array('script'=>1), 'name'=>array('a'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'map'=>1), 'noshade'=>array('hr'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'size'=>array('hr'=>1), 'vspace'=>array('img'=>1, 'object'=>1), 'width'=>array('hr'=>1, 'pre'=>1, 'table'=>1, 'td'=>1, 'th'=>1));
        static $deprecAttrPossibleEleAr = array('a'=>1, 'br'=>1, 'caption'=>1, 'div'=>1, 'dl'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'map'=>1, 'object'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'script'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1, 'ul'=>1);
        $alterDeprecAttr = isset($deprecAttrPossibleEleAr[$ele]) ? 1 : 0;
    }

    // -- Standard attribute values that may need to be lowercased.

    if ($C['lc_std_val']) {
        static $lCaseStdAttrValAr = array('all'=>1, 'auto'=>1, 'baseline'=>1, 'bottom'=>1, 'button'=>1, 'captions'=>1, 'center'=>1, 'chapters'=>1, 'char'=>1, 'checkbox'=>1, 'circle'=>1, 'col'=>1, 'colgroup'=>1, 'color'=>1, 'cols'=>1, 'data'=>1, 'date'=>1, 'datetime'=>1, 'datetime-local'=>1, 'default'=>1, 'descriptions'=>1, 'email'=>1, 'file'=>1, 'get'=>1, 'groups'=>1, 'hidden'=>1, 'image'=>1, 'justify'=>1, 'left'=>1, 'ltr'=>1, 'metadata'=>1, 'middle'=>1, 'month'=>1, 'none'=>1, 'number'=>1, 'object'=>1, 'password'=>1, 'poly'=>1, 'post'=>1, 'preserve'=>1, 'radio'=>1, 'range'=>1, 'rect'=>1, 'ref'=>1, 'reset'=>1, 'right'=>1, 'row'=>1, 'rowgroup'=>1, 'rows'=>1, 'rtl'=>1, 'search'=>1, 'submit'=>1, 'subtitles'=>1, 'tel'=>1, 'text'=>1, 'time'=>1, 'top'=>1, 'url'=>1, 'week'=>1);
        static $lCaseStdAttrValPossibleEleAr = array('a'=>1, 'area'=>1, 'bdo'=>1, 'button'=>1, 'col'=>1, 'fieldset'=>1, 'form'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'ol'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'script'=>1, 'select'=>1, 'table'=>1, 'td'=>1, 'textarea'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1, 'track'=>1, 'xml:space'=>1);
        $lCaseStdAttrVal = isset($lCaseStdAttrValPossibleEleAr[$ele]) ? 1 : 0;
    }

    // -- Get attribute name-value pairs.

    if (strpos($attrStr, "\x01") !== false) { // Remove CDATA/comment
        $attrStr = preg_replace('`\x01[^\x01]*\x01`', '', $attrStr);
    }
    $attrStr = trim($attrStr, ' /');
    $attrAr = array();
    $state = 0;
    while (strlen($attrStr)) {
        $ok = 0; // For parsing errors, to deal with space, ", and ' characters
        switch ($state) {
            case 0: if (preg_match('`^[^=\s/\x7f-\x9f]+`', $attrStr, $m)) { // Name
                $attr = strtolower($m[0]);
                $ok = $state = 1;
                $attrStr = ltrim(substr_replace($attrStr, '', 0, strlen($m[0])));
            }
            break; case 1: if ($attrStr[0] == '=') {
                $ok = 1;
                $state = 2;
                $attrStr = ltrim($attrStr, '= ');
            } else { // No value
                $ok = 1;
                $state = 0;
                $attrStr = ltrim($attrStr);
                $attrAr[$attr] = '';
            }
            break; case 2: if (preg_match('`^((?:"[^"]*")|(?:\'[^\']*\')|(?:\s*[^\s"\']+))(.*)`', $attrStr, $m)) { // Value
                $attrStr = ltrim($m[2]);
                $m = $m[1];
                $ok = 1;
                $state = 0;
                $attrAr[$attr] = trim(str_replace('<', '&lt;', ($m[0] == '"' || $m[0] == '\'')
                                                               ? substr($m, 1, -1)
                                                               : $m));
            }
            break;
        }
        if (!$ok) {
            $attrStr = preg_replace('`^(?:"[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*`', '', $attrStr);
            $state = 0;
        }
    }
    if ($state == 1) {
        $attrAr[$attr] = '';
    }

    // -- Clean attributes.

    global $S;
    $eleSpec = isset($S[$ele]) ? $S[$ele] : array();
    $filtAttrAr = array(); // Finalized attributes
    $deniedAttrAr = $C['deny_attribute'];

    foreach ($attrAr as $attr=>$v) {

        // .. Check if attribute is permitted.

        if (((isset($deniedAttrAr['*'])
              ? isset($deniedAttrAr[$attr])
              : !isset($deniedAttrAr[$attr]))
             && (isset($attrEleAr[$attr][$ele])
                 || isset($otherGlobalAttrAr[$attr])
                 || (isset($eventAttrAr[$attr])
                     && !isset($deniedAttrAr['on*']))
                 || (isset($ariaAttrAr[$attr])
                     && !isset($deniedAttrAr['aria*']))
                 || (!isset($deniedAttrAr['data*'])
                     && preg_match('`data-((?!xml)[^:]+$)`', $attr))
                 || strpos($ele, '-'))
             && !isset($eleSpec['deny'][$attr])
             && !isset($eleSpec['deny']['*']))
            || isset($eleSpec[$attr])
        ) {

            // .. Attribute with no value or standard value.

            if (isset($emptyAttrAr[$attr])) {
                $v = $attr;
            } elseif (!empty($lCaseStdAttrVal)  // ! Rather loose but should be ok
                      && (($ele != 'button' || $ele != 'input')
                          || $attr == 'type')
                ) {
                $v = (isset($lCaseStdAttrValAr[($vNew = strtolower($v))])) ? $vNew : $v;
            }

            // .. URLs and CSS expressions in style attribute.

            if ($attr == 'style' && !$C['style_pass']) {
                if (false !== strpos($v, '&#')) { // Change any entity to character
                    static $entityAr = array('&#32;'=>' ', '&#x20;'=>' ', '&#58;'=>':', '&#x3a;'=>':', '&#34;'=>'"', '&#x22;'=>'"', '&#40;'=>'(', '&#x28;'=>'(', '&#41;'=>')', '&#x29;'=>')', '&#42;'=>'*', '&#x2a;'=>'*', '&#47;'=>'/', '&#x2f;'=>'/', '&#92;'=>'\\', '&#x5c;'=>'\\', '&#101;'=>'e', '&#69;'=>'e', '&#x45;'=>'e', '&#x65;'=>'e', '&#105;'=>'i', '&#73;'=>'i', '&#x49;'=>'i', '&#x69;'=>'i', '&#108;'=>'l', '&#76;'=>'l', '&#x4c;'=>'l', '&#x6c;'=>'l', '&#110;'=>'n', '&#78;'=>'n', '&#x4e;'=>'n', '&#x6e;'=>'n', '&#111;'=>'o', '&#79;'=>'o', '&#x4f;'=>'o', '&#x6f;'=>'o', '&#112;'=>'p', '&#80;'=>'p', '&#x50;'=>'p', '&#x70;'=>'p', '&#114;'=>'r', '&#82;'=>'r', '&#x52;'=>'r', '&#x72;'=>'r', '&#115;'=>'s', '&#83;'=>'s', '&#x53;'=>'s', '&#x73;'=>'s', '&#117;'=>'u', '&#85;'=>'u', '&#x55;'=>'u', '&#x75;'=>'u', '&#120;'=>'x', '&#88;'=>'x', '&#x58;'=>'x', '&#x78;'=>'x', '&#39;'=>"'", '&#x27;'=>"'");
                    $v = strtr($v, $entityAr);
                }
                $v = preg_replace_callback('`(url(?:\()(?: )*(?:\'|"|&(?:quot|apos);)?)(.+?)((?:\'|"|&(?:quot|apos);)?(?: )*(?:\)))`iS', 'hl_prot', $v);
                $v = !$C['css_expression'] ? preg_replace('`expression`i', ' ', preg_replace('`\\\\\S|(/|(%2f))(\*|(%2a))`i', ' ', $v)) : $v;

            // .. URLs in other attributes.

            } elseif (isset($urlAttrAr[$attr]) || isset($eventAttrAr[$attr])) {
                $v = str_replace("­", ' ', (strpos($v, '&') !== false  // ! Double-quoted character = soft-hyphen
                                            ? str_replace(array('&#xad;', '&#173;', '&shy;'), ' ', $v)
                                            : $v));
                if ($attr == 'srcset') {
                    $vNew = '';
                    foreach (explode(',', $v) as $k=>$x) {
                        $x = explode(' ', ltrim($x), 2);
                        $k = isset($x[1]) ? trim($x[1]) : '';
                        $x = trim($x[0]);
                        if (isset($x[0])) {
                            $vNew .= hl_prot($x, $attr). (empty($k) ? '' : ' '. $k). ', ';
                        }
                    }
                    $v = trim($vNew, ', ');
                }
                if ($attr == 'itemtype') {
                    $vNew = '';
                    foreach (explode(' ', $v) as $x) {
                        if (isset($x[0])) {
                            $vNew .= hl_prot($x, $attr). ' ';
                        }
                    }
                    $v = trim($vNew, ' ');
                } else {
                    $v = hl_prot($v, $attr);
                }

                // Anti-spam measure.

                if ($attr == 'href') {
                    if ($C['anti_mail_spam'] && strpos($v, 'mailto:') === 0) {
                        $v = str_replace('@', htmlspecialchars($C['anti_mail_spam']), $v);
                    } elseif ($C['anti_link_spam']) {
                        $x = $C['anti_link_spam'][1];
                        if (!empty($x) && preg_match($x, $v)) {
                            continue;
                        }
                        $x = $C['anti_link_spam'][0];
                        if (!empty($x) && preg_match($x, $v)) {
                            if (isset($filtAttrAr['rel'])) {
                                if (!preg_match('`\bnofollow\b`i', $filtAttrAr['rel'])) {
                                    $filtAttrAr['rel'] .= ' nofollow';
                                }
                            } elseif (isset($attrAr['rel'])) {
                                if (!preg_match('`\bnofollow\b`i', $attrAr['rel'])) {
                                    $addNofollow = 1;
                                }
                            } else {
                                $filtAttrAr['rel'] = 'nofollow';
                            }
                        }
                    }
                }
            }

            // .. Check attribute value against any $spec rule.

            if (isset($eleSpec[$attr]) && is_array($eleSpec[$attr]) && ($v = hl_attrval($attr, $v, $eleSpec[$attr])) === 0) {
                continue;
            }

            $filtAttrAr[$attr] = str_replace('"', '&quot;', $v);
        }
    }

    // -- Add nofollow.

    if (isset($addNofollow)) {
        $filtAttrAr['rel'] = isset($filtAttrAr['rel']) ? $filtAttrAr['rel']. ' nofollow' : 'nofollow';
    }

    // -- Add required attributes.

    static $requiredAttrAr = array('area'=>array('alt'=>'area'), 'bdo'=>array('dir'=>'ltr'), 'command'=>array('label'=>''), 'form'=>array('action'=>''), 'img'=>array('src'=>'', 'alt'=>'image'), 'map'=>array('name'=>''), 'optgroup'=>array('label'=>''), 'param'=>array('name'=>''), 'style'=>array('scoped'=>''), 'textarea'=>array('rows'=>'10', 'cols'=>'50'));
    if (isset($requiredAttrAr[$ele])) {
        foreach ($requiredAttrAr[$ele] as $k=>$v) {
            if (!isset($filtAttrAr[$k])) {
                $filtAttrAr[$k] = isset($v[0]) ? $v : $k;
            }
        }
    }

    // -- Transform deprecated attributes into CSS declarations in style attribute.

    if ($alterDeprecAttr) {
        $css = array();
        foreach ($filtAttrAr as $name=>$val) {
            if ($name == 'style' || !isset($deprecAttrEleAr[$name][$ele])) {
                continue;
            }
            $val = str_replace(array('\\', ':', ';', '&#'), '', $val);
            if ($name == 'align') {
                unset($filtAttrAr['align']);
                if ($ele == 'img' && ($val == 'left' || $val == 'right')) {
                    $css[] = 'float: '. $val;
                } elseif (($ele == 'div' || $ele == 'table') && $val == 'center') {
                    $css[] = 'margin: auto';
                } else {
                    $css[] = 'text-align: '. $val;
                }
            } elseif ($name == 'bgcolor') {
                unset($filtAttrAr['bgcolor']);
                $css[] = 'background-color: '. $val;
            } elseif ($name == 'border') {
                unset($filtAttrAr['border']);
                $css[] = "border: {$val}px";
            } elseif ($name == 'bordercolor') {
                unset($filtAttrAr['bordercolor']);
                $css[] = 'border-color: '. $val;
            } elseif ($name == 'cellspacing') {
                unset($filtAttrAr['cellspacing']);
                $css[] = "border-spacing: {$val}px";
            } elseif ($name == 'clear') {
                unset($filtAttrAr['clear']);
                $css[] = 'clear: '. ($val != 'all' ? $val : 'both');
            } elseif ($name == 'compact') {
                unset($filtAttrAr['compact']);
                $css[] = 'font-size: 85%';
            } elseif ($name == 'height' || $name == 'width') {
                unset($filtAttrAr[$name]);
                $css[] = $name. ': '. (isset($val[0]) && $val[0] != '*'
                                       ? $val. (ctype_digit($val) ? 'px' : '')
                                       : 'auto');
            } elseif ($name == 'hspace') {
                unset($filtAttrAr['hspace']);
                $css[] = "margin-left: {$val}px; margin-right: {$val}px";
            } elseif ($name == 'language' && !isset($filtAttrAr['type'])) {
                unset($filtAttrAr['language']);
                $filtAttrAr['type'] = 'text/'. strtolower($val);
            } elseif ($name == 'name') {
                if ($C['no_deprecated_attr'] == 2 || ($ele != 'a' && $ele != 'map')) {
                    unset($filtAttrAr['name']);
                }
                if (!isset($filtAttrAr['id']) && !preg_match('`\W`', $val)) {
                    $filtAttrAr['id'] = $val;
                }
            } elseif ($name == 'noshade') {
                unset($filtAttrAr['noshade']);
                $css[] = 'border-style: none; border: 0; background-color: gray; color: gray';
            } elseif ($name == 'nowrap') {
                unset($filtAttrAr['nowrap']);
                $css[] = 'white-space: nowrap';
            } elseif ($name == 'size') {
                unset($filtAttrAr['size']);
                $css[] = 'size: '. $val. 'px';
            } elseif ($name == 'vspace') {
                unset($filtAttrAr['vspace']);
                $css[] = "margin-top: {$val}px; margin-bottom: {$val}px";
            }
        }
        if (count($css)) {
            $css = implode('; ', $css);
            $filtAttrAr['style'] = isset($filtAttrAr['style'])
                                   ? rtrim($filtAttrAr['style'], ' ;'). '; '. $css. ';'
                                   : $css. ';';
        }
    }

    // -- Enforce unique id attribute values.

    if ($C['unique_ids'] && isset($filtAttrAr['id'])) {
        if (preg_match('`\s`', ($id = $filtAttrAr['id'])) || (isset($GLOBALS['hl_Ids'][$id]) && $C['unique_ids'] == 1)) {
            unset($filtAttrAr['id']);
        } else {
            while (isset($GLOBALS['hl_Ids'][$id])) {
                $id = $C['unique_ids']. $id;
            }
            $GLOBALS['hl_Ids'][($filtAttrAr['id'] = $id)] = 1;
        }
    }

    // -- Handle lang attributes.

    if ($C['xml:lang'] && isset($filtAttrAr['lang'])) {
        $filtAttrAr['xml:lang'] = isset($filtAttrAr['xml:lang']) ? $filtAttrAr['xml:lang'] : $filtAttrAr['lang'];
        if ($C['xml:lang'] == 2) {
            unset($filtAttrAr['lang']);
        }
    }

    // -- If transformed element, modify style attribute.

    if (!empty($eleTransformed)) {
        $filtAttrAr['style'] = isset($filtAttrAr['style'])
                               ? rtrim($filtAttrAr['style'], ' ;'). '; '. $eleTransformed
                               : $eleTransformed;
    }

    // -- Return opening tag with attributes.

    if (empty($C['hook_tag'])) {
        $attrStr = '';
        foreach ($filtAttrAr as $k=>$v) {
            $attrStr .= " {$k}=\"{$v}\"";
        }
        return "<{$ele}{$attrStr}". (isset($emptyEleAr[$ele]) ? ' /' : ''). '>';
    } else {
        return $C['hook_tag']($ele, $filtAttrAr);
    }
}

/**
 * Transform deprecated element, with any attribute, into a new element.
 *
 *
 * @param  string $ele     Deprecated element.
 * @param  string $attrStr Attribute string of element.
 * @param  int    $act     No transformation if 2.
 * @return mixed           New attribute string (may be empty) or 0.
 */
function hl_tag2(&$ele, &$attrStr, $act=1)
{
    if ($ele == 'big') {
        $ele = 'span';
        return 'font-size: larger;';
    }
    if ($ele == 's' || $ele == 'strike') {
        $ele = 'span';
        return 'text-decoration: line-through;';
    }
    if ($ele == 'tt') {
        $ele = 'code';
        return '';
    }
    if ($ele == 'center') {
        $ele = 'div';
        return 'text-align: center;';
    }
    static $fontSizeAr = array('0'=>'xx-small', '1'=>'xx-small', '2'=>'small', '3'=>'medium', '4'=>'large', '5'=>'x-large', '6'=>'xx-large', '7'=>'300%', '-1'=>'smaller', '-2'=>'60%', '+1'=>'larger', '+2'=>'150%', '+3'=>'200%', '+4'=>'300%');
    if ($ele == 'font') {
        $attrStrNew = '';
        while (preg_match('`(^|\s)(color|size)\s*=\s*(\'|")?(.+?)(\\3|\s|$)`i', $attrStr, $m)) {
            $attrStr = str_replace($m[0], ' ', $attrStr) ;
            $attrStrNew .= strtolower($m[2]) == 'color'
                           ? ' color: '. str_replace(array('"', ';', ':'), '\'', trim($m[4])). ';'
                           : (isset($fontSizeAr[($m = trim($m[4]))])
                              ? ' font-size: '. $fontSizeAr[$m]. ';'
                              : '');
        }
        while (preg_match('`(^|\s)face\s*=\s*(\'|")?([^=]+?)\\2`i', $attrStr, $m)
               || preg_match('`(^|\s)face\s*=(\s*)(\S+)`i', $attrStr, $m)
              ) {
            $attrStr = str_replace($m[0], ' ', $attrStr) ;
            $attrStrNew .= ' font-family: '. str_replace(array('"', ';', ':'), '\'', trim($m[3])). ';';
        }
        $ele = 'span';
        return ltrim(str_replace('<', '', $attrStrNew));
    }
    if ($ele == 'acronym') {
        $ele = 'abbr';
        return '';
    }
    if ($ele == 'dir') {
        $ele = 'ul';
        return '';
    }
    if ($act == 2) {
        $ele = 0;
        return 0;
    }
    return '';
}

/**
 * Tidy/beautify HTM by adding newline and other spaces (padding),
 * or compact by removing unnecessary spaces.
 *
 * @param  string $t         HTM.
 * @param  mixed  $format    -1 (compact) or string (type of padding).
 * @param  string $parentEle Parent element of $t.
 * @return mixed             Transformed attribute string (may be empty) or 0.
 */
function hl_tidy($t, $format, $parentEle)
{
    if (strpos(' pre,script,textarea', "$parentEle,")) {
        return $t;
    }

    // To ignore CDATA/comment sections.

    if (!function_exists('hl_aux2')) {
        function hl_aux2($m) {
            return $m[1]. str_replace(array("<", ">", "\n", "\r", "\t", ' '), array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), $m[3]). $m[4];
        }
    }
    $t = preg_replace(
             array('`(<\w[^>]*(?<!/)>)\s+`', '`\s+`', '`(<\w[^>]*(?<!/)>) `'),
             array(' $1', ' ', '$1'),
             preg_replace_callback(
                 array('`(<(!\[CDATA\[))(.+?)(\]\]>)`sm', '`(<(!--))(.+?)(-->)`sm', '`(<(pre|script|textarea)[^>]*?>)(.+?)(</\2>)`sm'),
                 'hl_aux2',
                 $t
             )
         );

    if (($format = strtolower($format)) == -1) {
        return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $t);
    }
    $padChar = strpos(" $format", 't') ? "\t" : ' ';
    $padStr = preg_match('`\d`', $format, $m) ? str_repeat($padChar, intval($m[0])) : str_repeat($padChar, ($padChar == "\t" ? 1 : 2));
    $leadN = preg_match('`[ts]([1-9])`', $format, $m) ? intval($m[1]) : 0;

    // Group elements by line-break requirement.

    $postCloseEleAr = array('br'=>1); // After closing
    $preEleAr = array('button'=>1, 'command'=>1, 'input'=>1, 'option'=>1, 'param'=>1, 'track'=>1); // Before opening or closing
    $preOpenPostCloseEleAr = array('audio'=>1, 'canvas'=>1, 'caption'=>1, 'dd'=>1, 'dt'=>1, 'figcaption'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'isindex'=>1, 'label'=>1, 'legend'=>1, 'li'=>1, 'object'=>1, 'p'=>1, 'pre'=>1, 'style'=>1, 'summary'=>1, 'td'=>1, 'textarea'=>1, 'th'=>1, 'video'=>1); // Before opening and after closing
    $prePostEleAr = array('address'=>1, 'article'=>1, 'aside'=>1, 'blockquote'=>1, 'center'=>1, 'colgroup'=>1, 'datalist'=>1, 'details'=>1, 'dialog'=>1, 'dir'=>1, 'div'=>1, 'dl'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'form'=>1, 'header'=>1, 'hgroup'=>1, 'hr'=>1, 'iframe'=>1, 'main'=>1, 'map'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'ol'=>1, 'optgroup'=>1, 'picture'=>1, 'rbc'=>1, 'rtc'=>1, 'ruby'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'table'=>1, 'tbody'=>1, 'template'=>1, 'tfoot'=>1, 'thead'=>1, 'tr'=>1, 'ul'=>1); // Before and after opening and closing

    $doPad = 1;
    $t = explode('<', $t);
    while ($doPad) {
        $n = $leadN;
        $eleAr = $t;
        ob_start();
        if (isset($prePostEleAr[$parentEle])) {
            echo str_repeat($padStr, ++$n);
        }
        echo ltrim(array_shift($eleAr));
        for ($i=-1, $j=count($eleAr); ++$i<$j;) {
            $rest = '';
            list($tag, $rest) = explode('>', $eleAr[$i]);
            $open = $tag[0] == '/' ? 0 : (substr($tag, -1) == '/' ? 1 : ($tag[0] != '!' ? 2 : -1));
            $ele = !$open ? ltrim($tag, '/') : ($open > 0 ? substr($tag, 0, strcspn($tag, ' ')) : 0);
            $tag = "<$tag>";
            if (isset($prePostEleAr[$ele])) {
                if (!$open) {
                    if ($n) {
                        echo "\n", str_repeat($padStr, --$n), "$tag\n", str_repeat($padStr, $n);
                    } else {
                        ++$leadN;
                        ob_end_clean();
                        continue 2;
                    }
                } else {
                    echo "\n", str_repeat($padStr, $n), "$tag\n", str_repeat($padStr, ($open != 1 ? ++$n : $n));
                }
                echo $rest;
                continue;
            }
            $pad = "\n". str_repeat($padStr, $n);
            if (isset($preOpenPostCloseEleAr[$ele])) {
                if (!$open) {
                    echo $tag, $pad, $rest;
                } else {
                    echo $pad, $tag, $rest;
                }
            } elseif (isset($preEleAr[$ele])) {
                echo $pad, $tag, $rest;
            } elseif (isset($postCloseEleAr[$ele])) {
                echo $tag, $pad, $rest;
            } elseif (!$ele) {
                echo $pad, $tag, $pad, $rest;
            } else {
                echo $tag, $rest;
            }
        }
        $doPad = 0;
    }
    $t = str_replace(array("\n ", " \n"), "\n", preg_replace('`[\n]\s*?[\n]+`', "\n", ob_get_contents()));
    ob_end_clean();
    if (($newline = strpos(" $format", 'r') ? (strpos(" $format", 'n') ? "\r\n" : "\r") : 0)) {
        $t = str_replace("\n", $newline, $t);
    }
    return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $t);
}

/**
 * Report version.
 */
function hl_version()
{
    return '1.2.8';
}
