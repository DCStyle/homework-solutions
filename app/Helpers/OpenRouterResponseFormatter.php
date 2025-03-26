<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class OpenRouterResponseFormatter
{
    /**
     * Format and decode OpenRouter API response for display
     *
     * @param mixed $response The response from OpenRouter
     * @param bool $parseMarkdown Whether to parse markdown formatting
     * @return string The formatted and decoded content
     */
    public static function formatResponse($response, $parseMarkdown = true)
    {
        // Convert to string if needed
        if (is_array($response) || is_object($response)) {
            $content = self::extractContentFromObject($response);
        } else {
            $content = (string)$response;
        }

        // Log the raw content for debugging
        Log::debug('Raw content before processing', [
            'preview' => mb_substr($content, 0, 150)
        ]);

        // Extract content from JSON structure if present
        $content = self::extractContentFromJson($content);

        // Extract content between markers if present
        $content = self::extractBetweenMarkers($content);

        // Fix Vietnamese Unicode characters - this is a crucial step
        $content = self::fixVietnameseUnicode($content);

        // Handle HTML entities and escape sequences
        $content = self::decodeEntities($content);

        // Convert markdown to HTML (focused on bold formatting)
        if ($parseMarkdown) {
            $content = self::convertMarkdownToHTML($content);
        }

        // Add proper line breaks for TinyMCE and cross-platform compatibility
        $content = self::formatLineBreaks($content);

        // Final cleanup
        $content = self::finalCleanup($content);

        // Log the processed content
        Log::debug('Content after processing', [
            'preview' => mb_substr($content, 0, 150)
        ]);

        return $content;
    }

    /**
     * Extract content from an object or array response
     */
    private static function extractContentFromObject($response)
    {
        // Handle array
        if (is_array($response)) {
            if (isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            }
            if (isset($response['message']['content'])) {
                return $response['message']['content'];
            }
        }

        // Handle object
        if (is_object($response)) {
            if (isset($response->choices) && !empty($response->choices)) {
                if (isset($response->choices[0]->message->content)) {
                    return $response->choices[0]->message->content;
                }
            }
            if (isset($response->message) && isset($response->message->content)) {
                return $response->message->content;
            }
            if (isset($response->content)) {
                return $response->content;
            }
        }

        // Fallback: convert to JSON string
        return json_encode($response);
    }

    /**
     * Extract content from JSON structure
     */
    private static function extractContentFromJson($content)
    {
        // Check if the content is a JSON string itself
        if (self::isJson($content)) {
            $decoded = json_decode($content, true);

            // Try to find content in the decoded structure
            if (isset($decoded['choices'][0]['message']['content'])) {
                return $decoded['choices'][0]['message']['content'];
            }
            if (isset($decoded['message']['content'])) {
                return $decoded['message']['content'];
            }
        }

        // Look for the assistant's message content pattern
        if (preg_match('/"message"\s*:\s*{\s*"role"\s*:\s*"assistant"\s*,\s*"content"\s*:\s*"(.*?)(?:"[^"]*?$|"[^"]*$)/s', $content, $matches)) {
            // We found the content part
            $extracted = $matches[1];

            // Unescape the content
            $extracted = str_replace('\\"', '"', $extracted);
            $extracted = str_replace('\\\\', '\\', $extracted);

            return $extracted;
        }

        // If we can't extract JSON patterns, try to clean up known artifacts
        $content = preg_replace('/^\s*\{\s*"logprobs"\s*:\s*null\s*,\s*"finish_reason"\s*:.*?"message"\s*:\s*\{\s*"role"\s*:\s*"assistant"\s*,\s*"content"\s*:\s*"/s', '', $content);
        $content = preg_replace('/"[^\}]*\}\s*\}\s*$/s', '', $content);

        // Remove code block markers
        $content = preg_replace('/```(?:html|json)?\s*(.*?)\s*```$/s', '$1', $content);

        return $content;
    }

    /**
     * Extract content between START_CONTENT and END_CONTENT markers
     */
    private static function extractBetweenMarkers($content)
    {
        // Look for START_CONTENT and END_CONTENT markers
        if (preg_match('/<START_CONTENT>(.*?)<END_CONTENT>/s', $content, $matches)) {
            return $matches[1];
        }

        // Alternative without brackets
        if (preg_match('/START_CONTENT\s*(.*?)\s*END_CONTENT/s', $content, $matches)) {
            return $matches[1];
        }

        return $content;
    }

    /**
     * Fix Vietnamese Unicode characters
     */
    private static function fixVietnameseUnicode($content)
    {
        // Create a map of commonly encountered Vietnamese character codes
        $vietnameseChars = [
            'u00e0' => 'à', 'u00e1' => 'á', 'u00e2' => 'â', 'u00e3' => 'ã', 'u00e8' => 'è', 'u00e9' => 'é',
            'u00ea' => 'ê', 'u00ec' => 'ì', 'u00ed' => 'í', 'u00f2' => 'ò', 'u00f3' => 'ó', 'u00f4' => 'ô',
            'u00f5' => 'õ', 'u00f9' => 'ù', 'u00fa' => 'ú', 'u00fd' => 'ý', 'u00c0' => 'À', 'u00c1' => 'Á',
            'u00c2' => 'Â', 'u00c3' => 'Ã', 'u00c8' => 'È', 'u00c9' => 'É', 'u00ca' => 'Ê', 'u00cc' => 'Ì',
            'u00cd' => 'Í', 'u00d2' => 'Ò', 'u00d3' => 'Ó', 'u00d4' => 'Ô', 'u00d5' => 'Õ', 'u00d9' => 'Ù',
            'u00da' => 'Ú', 'u00dd' => 'Ý', 'u0103' => 'ă', 'u0110' => 'Đ', 'u0111' => 'đ', 'u01A0' => 'Ơ',
            'u01A1' => 'ơ', 'u01AF' => 'Ư', 'u01B0' => 'ư',

            // Common Vietnamese tone marks and combinations
            'u1EA0' => 'Ạ', 'u1EA1' => 'ạ', 'u1EA2' => 'Ả', 'u1EA3' => 'ả', 'u1EA4' => 'Ấ', 'u1EA5' => 'ấ',
            'u1EA6' => 'Ầ', 'u1EA7' => 'ầ', 'u1EA8' => 'Ẩ', 'u1EA9' => 'ẩ', 'u1EAA' => 'Ẫ', 'u1EAB' => 'ẫ',
            'u1EAC' => 'Ậ', 'u1EAD' => 'ậ', 'u1EAE' => 'Ắ', 'u1EAF' => 'ắ', 'u1EB0' => 'Ằ', 'u1EB1' => 'ằ',
            'u1EB2' => 'Ẳ', 'u1EB3' => 'ẳ', 'u1EB4' => 'Ẵ', 'u1EB5' => 'ẵ', 'u1EB6' => 'Ặ', 'u1EB7' => 'ặ',
            'u1EB8' => 'Ẹ', 'u1EB9' => 'ẹ', 'u1EBA' => 'Ẻ', 'u1EBB' => 'ẻ', 'u1EBC' => 'Ẽ', 'u1EBD' => 'ẽ',
            'u1EBE' => 'Ế', 'u1EBF' => 'ế', 'u1EC0' => 'Ề', 'u1EC1' => 'ề', 'u1EC2' => 'Ể', 'u1EC3' => 'ể',
            'u1EC4' => 'Ễ', 'u1EC5' => 'ễ', 'u1EC6' => 'Ệ', 'u1EC7' => 'ệ', 'u1EC8' => 'Ỉ', 'u1EC9' => 'ỉ',
            'u1ECA' => 'Ị', 'u1ECB' => 'ị', 'u1ECC' => 'Ọ', 'u1ECD' => 'ọ', 'u1ECE' => 'Ỏ', 'u1ECF' => 'ỏ',
            'u1ED0' => 'Ố', 'u1ED1' => 'ố', 'u1ED2' => 'Ồ', 'u1ED3' => 'ồ', 'u1ED4' => 'Ổ', 'u1ED5' => 'ổ',
            'u1ED6' => 'Ỗ', 'u1ED7' => 'ỗ', 'u1ED8' => 'Ộ', 'u1ED9' => 'ộ', 'u1EDA' => 'Ớ', 'u1EDB' => 'ớ',
            'u1EDC' => 'Ờ', 'u1EDD' => 'ờ', 'u1EDE' => 'Ở', 'u1EDF' => 'ở', 'u1EE0' => 'Ỡ', 'u1EE1' => 'ỡ',
            'u1EE2' => 'Ợ', 'u1EE3' => 'ợ', 'u1EE4' => 'Ụ', 'u1EE5' => 'ụ', 'u1EE6' => 'Ủ', 'u1EE7' => 'ủ',
            'u1EE8' => 'Ứ', 'u1EE9' => 'ứ', 'u1EEA' => 'Ừ', 'u1EEB' => 'ừ', 'u1EEC' => 'Ử', 'u1EED' => 'ử',
            'u1EEE' => 'Ữ', 'u1EEF' => 'ữ', 'u1EF0' => 'Ự', 'u1EF1' => 'ự', 'u1EF2' => 'Ỳ', 'u1EF3' => 'ỳ',
            'u1EF4' => 'Ỵ', 'u1EF5' => 'ỵ', 'u1EF6' => 'Ỷ', 'u1EF7' => 'ỷ', 'u1EF8' => 'Ỹ', 'u1EF9' => 'ỹ'
        ];

        // Convert standard Unicode escape sequences with backslash
        $content = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
            return html_entity_decode('&#x' . $matches[1] . ';', ENT_QUOTES, 'UTF-8');
        }, $content);

        // Replace all occurrences of Vietnamese characters directly using the map
        foreach ($vietnameseChars as $code => $char) {
            $content = str_replace($code, $char, $content);
            // Also handle uppercase version
            $content = str_replace(strtoupper($code), $char, $content);
        }

        // Convert remaining Vietnamese characters using regex
        $content = preg_replace_callback('/u([0-9a-fA-F]{4})/', function($matches) use ($vietnameseChars) {
            $code = strtolower('u' . $matches[1]);

            // Check if it's in our map
            if (isset($vietnameseChars[$code])) {
                return $vietnameseChars[$code];
            }

            // If not in map, but still looks like Vietnamese Unicode, convert it
            if (preg_match('/^u(00[a-f]|01[a-f]|1e[a-f])/i', $code)) {
                return html_entity_decode('&#x' . $matches[1] . ';', ENT_QUOTES, 'UTF-8');
            }

            return $matches[0]; // Return unchanged for non-Vietnamese
        }, $content);

        return $content;
    }

    /**
     * Decode HTML entities and escape sequences
     */
    private static function decodeEntities($content)
    {
        // Replace non-breaking spaces
        $content = str_replace('&nbsp;', ' ', $content);

        // Replace other common HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Handle escaped characters
        $replacements = [
            '\\"' => '"',
            '\\\'' => "'",
            '\\/' => '/',
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\\\' => '\\'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Convert markdown to HTML with focus on bold formatting
     */
    private static function convertMarkdownToHTML($content)
    {
        // Check if content already contains HTML
        $hasHTML = (strpos($content, '<') !== false && strpos($content, '>') !== false);

        // Focus on converting **text** to <b>text</b> (specifically using <b> as requested)
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<b>$1</b>', $content);

        // Only apply additional markdown formatting if content doesn't already have HTML
        if (!$hasHTML) {
            // Handle headers (## text) to <h2>
            $content = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $content);
            $content = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $content);

            // Handle italics
            $content = preg_replace('/\*(.*?)\*/s', '<i>$1</i>', $content);
            $content = preg_replace('/_(.*?)_/s', '<i>$1</i>', $content);

            // Handle bullet points
            $content = preg_replace('/^\s*-\s+(.*)$/m', '<li>$1</li>', $content);

            // Wrap lists if there are list items
            if (strpos($content, '<li>') !== false) {
                $content = preg_replace('/(?:<li>.*?<\/li>\s*)+/s', '<ul>$0</ul>', $content);
            }
        }

        return $content;
    }

    /**
     * Format line breaks for cross-platform compatibility and TinyMCE
     */
    private static function formatLineBreaks($content)
    {
        // Replace single newlines with <br> tags if not already in HTML
        if (strpos($content, '<p>') === false && strpos($content, '<div>') === false) {
            // Replace double newlines with paragraph breaks
            $paragraphs = preg_split('/\n\s*\n/', $content);

            $formattedContent = '';
            foreach ($paragraphs as $paragraph) {
                if (trim($paragraph) === '') continue;

                // Skip wrapping if already has HTML tags
                if (preg_match('/^<([a-z][a-z0-9]*)\b[^>]*>/i', trim($paragraph))) {
                    $formattedContent .= $paragraph . "\n\n";
                } else {
                    // Replace single newlines with <br>
                    $paragraph = str_replace("\n", "<br>\n", $paragraph);

                    // Wrap in paragraph tag if not already a heading, list, etc.
                    if (!preg_match('/^<(h[1-6]|ul|ol|li|blockquote|div)/i', trim($paragraph))) {
                        $formattedContent .= "<p>" . $paragraph . "</p>\n\n";
                    } else {
                        $formattedContent .= $paragraph . "\n\n";
                    }
                }
            }

            $content = $formattedContent;
        }

        // Ensure consistent line endings (use \n which TinyMCE handles well)
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        return $content;
    }

    /**
     * Final cleanup of content
     */
    private static function finalCleanup($content)
    {
        // Remove any remaining JSON artifacts
        $content = preg_replace('/^\s*\{\s*"[^"]+"\s*:/s', '', $content);
        $content = preg_replace('/\s*\}\s*\}\s*$/s', '', $content);

        // Replace any sequences of multiple spaces with a single space
        $content = preg_replace('/[ ]{2,}/', ' ', $content);

        // Ensure <b> tags are followed by line breaks where appropriate
        $content = preg_replace('/<\/b>(\s*?)(\n?)/', '</b>$1' . "\n\n", $content);

        // Remove excessive line breaks (more than 2 consecutive)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Remove empty paragraphs
        $content = preg_replace('/<p>\s*(&nbsp;)?\s*<\/p>/', '', $content);

        // Clean up any remaining "u" codes that should be Vietnamese characters
        $content = preg_replace_callback('/u([0-9a-fA-F]{4})/', function($matches) {
            $code = $matches[1];
            // Only convert codes in the Vietnamese Unicode ranges
            if (
                (hexdec($code) >= 0x00C0 && hexdec($code) <= 0x00FF) || // Latin-1 Supplement
                (hexdec($code) >= 0x0100 && hexdec($code) <= 0x01FF) || // Latin Extended-A
                (hexdec($code) >= 0x1E00 && hexdec($code) <= 0x1EFF)    // Latin Extended Additional
            ) {
                return html_entity_decode('&#x' . $code . ';', ENT_QUOTES, 'UTF-8');
            }
            return $matches[0]; // Return unchanged for non-Vietnamese patterns
        }, $content);

        return trim($content);
    }

    /**
     * Check if a string is valid JSON
     */
    private static function isJson($string)
    {
        if (!is_string($string)) return false;

        // Quick check for basic JSON pattern before running json_decode
        if (!preg_match('/^\s*[\[\{].*[\]\}]\s*$/s', $string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
