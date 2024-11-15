{{ mb_substr(html_entity_decode(strip_tags($string)), 0, $snippet) }}@if(strlen($string) > $snippet)... @endif
