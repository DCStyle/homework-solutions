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
        // If response is a string (raw JSON), decode it
        if (is_string($response) && self::isJson($response)) {
            $response = json_decode($response, true);
        }

        // Extract content from different possible response formats
        $content = '';

        if (is_array($response) && isset($response['message']['content'])) {
            // Standard JSON array format
            $content = $response['message']['content'];
        } elseif (is_array($response) && isset($response['choices']) && !empty($response['choices'])) {
            // OpenAI-like format as array
            if (isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
            } elseif (isset($response['choices'][0]['text'])) {
                $content = $response['choices'][0]['text'];
            }
        } elseif (is_object($response)) {
            if (isset($response->message) && isset($response->message->content)) {
                // Object format with nested message
                $content = $response->message->content;
            } elseif (isset($response->choices) && !empty($response->choices)) {
                // OpenAI-like format
                if (isset($response->choices[0]->message->content)) {
                    $content = $response->choices[0]->message->content;
                } elseif (isset($response->choices[0]->message) && isset($response->choices[0]->message->content)) {
                    // Alternative nesting structure
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

        // If no content was found yet, try to convert the whole response to string and extract content
        if (empty($content) && (is_object($response) || is_array($response))) {
            // Convert to JSON string for deeper inspection
            $jsonStr = json_encode($response);
            
            // Try to extract content using regex patterns that might match the response
            if (preg_match('/"content"\s*:\s*"((?:\\\\"|[^"])*)"/', $jsonStr, $matches)) {
                $content = str_replace('\\"', '"', $matches[1]);
                Log::debug("Content extracted using regex pattern", ['content_preview' => substr($content, 0, 50)]);
            } elseif (preg_match('/"text"\s*:\s*"((?:\\\\"|[^"])*)"/', $jsonStr, $matches)) {
                $content = str_replace('\\"', '"', $matches[1]);
            }
        }

        // If still no content was found, log detailed info and return error message
        if (empty($content)) {
            $debugInfo = is_string($response) ? $response : json_encode($response);
            Log::warning('Unable to extract content from response', [
                'response_type' => gettype($response),
                'response_preview' => substr($debugInfo, 0, 200) . '...'
            ]);
            return "No content found in response.";
        }

        // Decode Unicode escape sequences
        $decodedContent = self::decodeUnicodeEscapes($content);

        // Optionally parse markdown
        if ($parseMarkdown) {
            $decodedContent = self::parseMarkdown($decodedContent);
        }

        return $decodedContent;
    }

    /**
     * Decode Unicode escape sequences in a string
     *
     * @param string $input The input string with Unicode escapes
     * @return string The decoded string
     */
    private static function decodeUnicodeEscapes($input)
    {
        // Method 1: Using json_decode (handles \uXXXX sequences)
        $decoded = json_decode('"' . str_replace('"', '\"', $input) . '"');

        // If json_decode failed, try alternative method
        if ($decoded === null) {
            // Method 2: Using preg_replace_callback for \uXXXX sequences
            $decoded = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            }, $input);
        }

        return $decoded ?: $input; // Return original if both methods fail
    }

    /**
     * Basic markdown parsing to HTML
     *
     * @param string $text The markdown text
     * @return string HTML formatted text
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
     *
     * @param string $string The string to check
     * @return bool True if valid JSON, false otherwise
     */
    private static function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}