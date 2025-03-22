# OpenRouter Response Parser Fix

After analyzing the expanded error logs, I can now see the exact format of the "unexpected response structure" that's causing issues. This requires a more targeted fix.

## The Issue

The response contains:
1. Escaped null bytes (`\u0000*\u0000_additional`)
2. Nested JSON structure inside a string property (`response`)
3. The actual content we need is buried in `choices[0].message.content`

## Solution

Let's modify the `OpenRouterResponseFormatter` class to handle this specific response format:

```php
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
     * @return string|array The formatted and decoded content
     */
    public static function formatResponse($response, $parseMarkdown = true)
    {
        // Special handling for the "unexpected response structure" case
        if (is_string($response) && self::isJson($response)) {
            $decodedResponse = json_decode($response, true);
            
            // Check if this is the special case with the odd format
            if (isset($decodedResponse['response']) && is_string($decodedResponse['response'])) {
                Log::debug('Found response property in string format, attempting to extract content');
                
                // This is the problematic format - extract content from it
                return self::extractContentFromNestedResponse($decodedResponse['response'], $parseMarkdown);
            }
            
            // Regular JSON response
            $response = $decodedResponse;
        }

        // Extract content from different possible response formats
        $content = '';

        if (is_array($response)) {
            if (isset($response['message']['content'])) {
                // Standard JSON array format
                $content = $response['message']['content'];
            } elseif (isset($response['choices'][0]['message']['content'])) {
                // OpenAI-like format in array
                $content = $response['choices'][0]['message']['content'];
            } elseif (isset($response['meta_title']) || isset($response['meta_description'])) {
                // Already parsed meta data - return as is
                return $response;
            }
        } elseif (is_object($response)) {
            if (isset($response->message) && isset($response->message->content)) {
                // Object format with nested message
                $content = $response->message->content;
            } elseif (isset($response->choices) && !empty($response->choices)) {
                // OpenAI-like format
                if (isset($response->choices[0]->message->content)) {
                    $content = $response->choices[0]->message->content;
                } elseif (isset($response->choices[0]->text)) {
                    $content = $response->choices[0]->text;
                }
            } elseif (isset($response->content)) {
                // Simple object with content property
                $content = $response->content;
            }
        } elseif (is_string($response)) {
            // Already a string, use as is
            $content = $response;
        }

        // If no content was found, return empty string or error message
        if (empty($content)) {
            return "No content found in response.";
        }

        // Check if content is in meta title/description format
        if (is_string($content) && self::isTitleDescriptionFormat($content)) {
            return self::extractTitleDescription($content);
        }

        // Decode Unicode escape sequences
        $decodedContent = self::decodeUnicodeEscapes($content);

        // Optionally parse markdown
        if ($parseMarkdown && is_string($decodedContent)) {
            $decodedContent = self::parseMarkdown($decodedContent);
        }

        return $decodedContent;
    }

    /**
     * Extract content from the problematic nested response format
     */
    private static function extractContentFromNestedResponse($responseString, $parseMarkdown = true)
    {
        try {
            // Clean up escape sequences to make it more parsable
            $cleaned = preg_replace('/\\\\u0000\\*\\\\u0000_[^,}]+,?/', '', $responseString);
            
            // Try to decode the cleaned string
            $data = json_decode($cleaned, true);
            
            // If failed, try a more aggressive approach
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Extract just the choices array using regex
                if (preg_match('/"choices":\s*\[\s*({[^}]+})\s*\]/', $responseString, $matches)) {
                    $choiceJson = '{' . $matches[1] . '}';
                    $choice = json_decode($choiceJson, true);
                    
                    if (isset($choice['message']['content'])) {
                        $content = $choice['message']['content'];
                        
                        // Check if it's in meta title/description format
                        if (self::isTitleDescriptionFormat($content)) {
                            return self::extractTitleDescription($content);
                        }
                        
                        return self::decodeUnicodeEscapes($content);
                    }
                }
                
                // If we still failed, try direct regex on the content
                if (preg_match('/"content":\s*"([^"]+)"/', $responseString, $matches)) {
                    $content = str_replace('\\"', '"', $matches[1]);
                    $content = str_replace('\\\\', '\\', $content);
                    
                    // Check if it's in meta title/description format
                    if (self::isTitleDescriptionFormat($content)) {
                        return self::extractTitleDescription($content);
                    }
                    
                    return self::decodeUnicodeEscapes($content);
                }
            } else {
                // We successfully parsed the cleaned string
                if (isset($data['choices'][0]['message']['content'])) {
                    $content = $data['choices'][0]['message']['content'];
                    
                    // Check if it's in meta title/description format
                    if (self::isTitleDescriptionFormat($content)) {
                        return self::extractTitleDescription($content);
                    }
                    
                    return self::decodeUnicodeEscapes($content);
                }
            }
            
            // If all extraction attempts failed, return error
            return "Could not extract content from nested response.";
        } catch (\Exception $e) {
            Log::error('Error extracting content from nested response', [
                'error' => $e->getMessage(),
                'response_preview' => substr($responseString, 0, 300)
            ]);
            return "Error processing response: " . $e->getMessage();
        }
    }

    /**
     * Check if content is in the "Meta Title: ... Meta Description: ..." format
     */
    private static function isTitleDescriptionFormat($content)
    {
        return (
            stripos($content, 'meta title') !== false && 
            stripos($content, 'meta description') !== false
        ) || (
            stripos($content, 'tiêu đề meta') !== false && 
            stripos($content, 'mô tả meta') !== false
        );
    }
    
    /**
     * Extract title and description from formatted text
     */
    private static function extractTitleDescription($content)
    {
        $result = [
            'meta_title' => '',
            'meta_description' => ''
        ];
        
        // Match Meta Title using various patterns
        if (preg_match('/(meta title|tiêu đề meta)\s*:\s*(.*?)(?:\n|$)/is', $content, $matches)) {
            $result['meta_title'] = trim($matches[2]);
        }
        
        // Match Meta Description using various patterns
        if (preg_match('/(meta description|mô tả meta)\s*:\s*(.*?)(?:$|(?=\n\n))/is', $content, $matches)) {
            $result['meta_description'] = trim($matches[2]);
        }
        
        return $result;
    }

    /**
     * Decode Unicode escape sequences in a string
     */
    private static function decodeUnicodeEscapes($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // Method 1: Using json_decode (handles \uXXXX sequences)
        $decoded = null;
        
        try {
            // Add quotes if not present
            if (substr($input, 0, 1) !== '"' && substr($input, 0, 1) !== "'") {
                $decoded = json_decode('"' . str_replace('"', '\"', $input) . '"');
            } else {
                $decoded = json_decode($input);
            }
        } catch (\Exception $e) {
            // Ignore errors, will try alternative method
        }

        // If json_decode failed, try alternative method
        if ($decoded === null) {
            // Method 2: Using preg_replace_callback for \uXXXX sequences
            $decoded = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            }, $input);
            
            // Method 3: Replace common Vietnamese characters
            $decoded = preg_replace_callback('/\\\\([\x{00C0}-\x{1EF9}])/u', function ($matches) {
                return $matches[1];
            }, $decoded ?? $input);
        }

        return $decoded ?: $input; // Return original if all methods fail
    }

    /**
     * Basic markdown parsing to HTML
     */
    private static function parseMarkdown($text)
    {
        // Bold: Convert **text** to <strong>text</strong>
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);

        // Italic: Convert *text* or _text_ to <em>text</em>
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/_(.*?)_/s', '<em>$1</em>', $text);

        // Paragraphs: Convert double newlines to paragraphs
        $paragraphs = preg_split('/\n\n+/', $text);
        $paragraphs = array_map(function($p) {
            return '<p>' . str_replace("\n", '<br>', $p) . '</p>';
        }, $paragraphs);

        $text = implode('', $paragraphs);

        return $text;
    }

    /**
     * Check if a string is valid JSON
     */
    private static function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
```

## Explanation

This updated implementation specifically targets the problematic response structure we're seeing in the logs:

1. Adds a dedicated method `extractContentFromNestedResponse()` to handle the special case where the response is nested inside a string
2. Uses multiple fallback strategies to extract the content:
   - Clean up null byte sequences and try to parse the JSON
   - Use regex to extract just the choice object
   - Use regex to directly extract the content property
3. Adds better handling for the meta title/description format
4. Improves Unicode decoding for Vietnamese characters

## Implementation Steps

1. Replace the entire content of `App\Helpers\OpenRouterResponseFormatter` with the new implementation
2. Test with a small batch of content generation

This targeted fix should handle the specific response format that's causing issues in your logs. If there are additional response formats, we may need to add more specific handling for those cases.

## Testing 

After implementing, test by:

1. Running a small batch job (2-3 items)
2. Check the logs for any errors or warnings
3. Verify that content is being extracted correctly
4. Check that Vietnamese characters are displayed properly

If you encounter more unexpected response structures, please share them so we can further refine the solution.