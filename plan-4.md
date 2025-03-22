# Redesigning AIService's processPrompt Function

## Current Issues

After analyzing your requirements and the example error (`","refusal":null}}`), I've identified several issues:

1. The current prompt format is too focused on short SEO metadata rather than comprehensive content
2. The instructions don't provide enough structure for generating full articles
3. Missing clear boundary markers causes the AI to include response JSON artifacts in the content
4. HTML formatting instructions need enhancement for proper editor compatibility

## Redesigned Solution

Here's a complete redesign of the `processPrompt` function to address these issues:

```php
/**
 * Process and format prompt to guide AI model's response format
 *
 * @param string $prompt The original prompt
 * @param string $contentType Type of content (posts, chapters, books, book_groups)
 * @param bool $useHtmlMeta Whether to use HTML formatting
 * @return string The enhanced prompt with formatting instructions
 */
private function processPrompt($prompt, $contentType, $useHtmlMeta = false)
{
    // Define the role and intention clearly based on content type
    $roleInstruction = match($contentType) {
        'posts' => "Bạn là chuyên gia viết nội dung giáo dục với kinh nghiệm trong việc tạo bài giới thiệu chi tiết cho các bài học.",
        'chapters' => "Bạn là chuyên gia giáo dục với kinh nghiệm viết tổng quan về các chương sách giáo khoa.",
        'books' => "Bạn là chuyên gia biên soạn sách giáo dục, có khả năng tạo nội dung giới thiệu sách học tập.",
        'book_groups' => "Bạn là chuyên gia phân tích chương trình giảng dạy với khả năng tạo nội dung giới thiệu bộ sách giáo dục.",
        default => "Bạn là chuyên gia tạo nội dung giáo dục chất lượng cao với hiểu biết sâu sắc về chủ đề.",
    };

    // Create structure guidelines based on content type
    $contentStructure = match($contentType) {
        'posts' => "Hãy viết một bài giới thiệu chi tiết (khoảng 800-1000 từ) về bài học này bao gồm các phần sau:

1. **Tổng quan về bài học**: Giới thiệu chủ đề và mục tiêu chính
2. **Kiến thức và kỹ năng**: Những gì học sinh sẽ học được 
3. **Phương pháp tiếp cận**: Cách thức bài học được tổ chức
4. **Ứng dụng thực tế**: Cách áp dụng kiến thức vào thực tế
5. **Kết nối với chương trình học**: Mối liên hệ với các bài học khác
6. **Hướng dẫn học tập**: Gợi ý phương pháp học hiệu quả",

        'chapters' => "Hãy viết một bài giới thiệu tổng quan (khoảng 800-1000 từ) về chương sách này bao gồm các phần sau:

1. **Giới thiệu chương**: Nội dung và mục tiêu chính
2. **Các bài học chính**: Tổng quan về các bài học trong chương
3. **Kỹ năng phát triển**: Những kỹ năng học sinh sẽ đạt được
4. **Khó khăn thường gặp**: Những thách thức học sinh có thể gặp phải
5. **Phương pháp tiếp cận**: Gợi ý cách tiếp cận học tập hiệu quả
6. **Liên kết kiến thức**: Mối liên hệ với các chương khác",

        'books' => "Hãy viết một bài giới thiệu chi tiết (khoảng 800-1000 từ) về cuốn sách này bao gồm các phần sau:

1. **Tổng quan sách**: Mục đích và đối tượng sử dụng
2. **Cấu trúc nội dung**: Các phần và chương chính
3. **Phương pháp giảng dạy**: Cách tiếp cận giáo dục của sách
4. **Đặc điểm nổi bật**: Điểm mạnh và tính năng đặc biệt
5. **Hỗ trợ học tập**: Các công cụ và tài nguyên đi kèm
6. **Hướng dẫn sử dụng**: Cách sử dụng sách hiệu quả nhất",

        'book_groups' => "Hãy viết một bài giới thiệu tổng quan (khoảng 800-1000 từ) về bộ sách này bao gồm các phần sau:

1. **Giới thiệu bộ sách**: Mục đích và tầm nhìn giáo dục
2. **Đối tượng học sinh**: Phù hợp với những học sinh nào
3. **Cấu trúc chương trình**: Các sách và mối liên hệ giữa chúng
4. **Phương pháp giáo dục**: Cách tiếp cận dạy và học
5. **Lợi ích chính**: Giá trị mang lại cho học sinh và giáo viên
6. **Cách sử dụng hiệu quả**: Hướng dẫn tận dụng tối đa bộ sách",

        default => "Hãy viết một bài nội dung chi tiết (khoảng 800-1000 từ) với cấu trúc rõ ràng, bố cục mạch lạc và thông tin đầy đủ.",
    };

    // HTML formatting instructions when needed
    $formattingInstructions = $useHtmlMeta ? 
"ĐỊNH DẠNG HTML: Bài viết cần được định dạng bằng các thẻ HTML cơ bản để hiển thị trên website:

1. Sử dụng thẻ `<h2>` cho các tiêu đề chính 
2. Sử dụng thẻ `<h3>` cho các tiêu đề phụ
3. Sử dụng thẻ `<p>` cho mỗi đoạn văn
4. Sử dụng thẻ `<strong>` cho văn bản quan trọng cần nhấn mạnh
5. Sử dụng thẻ `<em>` cho văn bản cần in nghiêng
6. Sử dụng thẻ `<ul>` và `<li>` cho danh sách không có thứ tự
7. Sử dụng thẻ `<ol>` và `<li>` cho danh sách có thứ tự

Đảm bảo mỗi thẻ đều được đóng đúng cách. Không sử dụng các thẻ HTML phức tạp khác. Không thêm các thuộc tính CSS hoặc JavaScript." : 

"ĐỊNH DẠNG VĂN BẢN: Hãy viết với cấu trúc rõ ràng, sử dụng tiêu đề, đoạn văn và danh sách để tạo bố cục mạch lạc.";

    // Strict output format requirements to avoid JSON artifacts
    $preciseOutputInstructions = "
HƯỚNG DẪN QUAN TRỌNG VỀ KẾT QUẢ ĐẦU RA:

1. KHÔNG thêm bất kỳ phần mở đầu thừa nào như 'Dưới đây là bài viết' hoặc 'Tôi sẽ viết'.
2. KHÔNG thêm bất kỳ phần kết thúc thừa nào như 'Tôi hy vọng bài viết này hữu ích'.
3. KHÔNG bao gồm bất kỳ dấu ngoặc JSON, ký hiệu đặc biệt, hay chuỗi như 'refusal:null' trong nội dung.
4. BẮT ĐẦU và KẾT THÚC phản hồi của bạn chính xác với nội dung bài viết, không có văn bản thừa.
5. Viết hoàn toàn bằng tiếng Việt với ngữ pháp và chính tả chuẩn mực.

<START_CONTENT>

[Đặt nội dung bài viết chính xác ở đây, không có văn bản thừa]

<END_CONTENT>

BẠN CHỈ ĐƯỢC TRẢ VỀ NỘI DUNG GIỮA CÁC THẺ START_CONTENT VÀ END_CONTENT, KHÔNG ĐƯỢC BAO GỒM CÁC THẺ NÀY!";

    // Combine everything into the final prompt
    $enhancedPrompt = $roleInstruction . "\n\n" . 
                     $contentStructure . "\n\n" . 
                     $formattingInstructions . "\n\n" . 
                     $preciseOutputInstructions . "\n\n" . 
                     $prompt;

    return $enhancedPrompt;
}
```

## Enhancements for Response Processing

To handle the current issues with response artifacts (`","refusal":null}}`), add these helper methods to the AIService class:

```php
/**
 * Clean up response artifacts from AI model output
 *
 * @param string $content The raw content from the AI model
 * @return string Cleaned content
 */
private function cleanResponseArtifacts($content)
{
    if (!is_string($content)) {
        return $content;
    }
    
    // Remove JSON artifacts like ","refusal":null}}
    $content = preg_replace('/",\s*"refusal"\s*:\s*null\s*\}\s*\}.*$/s', '', $content);
    $content = preg_replace('/"\s*,\s*".*?\}\s*\}.*$/s', '', $content);
    
    // Remove any trailing JSON that might be included
    $content = preg_replace('/\}\s*\}.*$/s', '', $content);
    
    // Remove any other JSON-like artifacts
    $content = preg_replace('/"?\s*\}\s*\]?\s*"?\s*$/s', '', $content);
    
    // Remove any opening JSON structure that might be included
    $content = preg_replace('/^\s*\{\s*"[^"]+"\s*:\s*\{/s', '', $content);
    
    // Remove any escaped quotes at the beginning or end
    $content = preg_replace('/^"(.*)"$/s', '$1', $content);
    
    // Replace escaped newlines with actual newlines
    $content = str_replace('\\n', "\n", $content);
    
    // Unescape characters
    $content = stripcslashes($content);
    
    return $content;
}

/**
 * Process and format response based on content type
 *
 * @param mixed $response The response from OpenRouter
 * @param string $contentType Type of content
 * @param array $options Additional options
 * @return string|array The formatted and processed content
 */
private function processResponse($response, $contentType, $options = [])
{
    try {
        // Extract content from different possible response formats
        $content = '';

        // Check if response is in the expected format
        if (isset($response->choices) && !empty($response->choices)) {
            if (isset($response->choices[0]->message) && isset($response->choices[0]->message->content)) {
                $content = $response->choices[0]->message->content;
            } else {
                Log::warning('Unexpected response structure', [
                    'response' => json_encode(Arr::except((array)$response, ['usage']))
                ]);
                $content = json_encode($response->choices[0]);
            }
        } else {
            Log::warning('Choices not found in response', [
                'response_keys' => is_object($response) ? array_keys((array)$response) : 'not_object'
            ]);
            
            // Try to extract content from the first level if choices is missing
            $content = $response->content ?? json_encode($response);
        }

        // Clean up any JSON artifacts or unwanted parts
        $content = $this->cleanResponseArtifacts($content);

        switch ($contentType) {
            case 'posts':
                // For articles, we should already have the full HTML content
                if ($options['use_html_meta'] ?? false) {
                    // Ensure the HTML is properly formatted
                    $content = $this->ensureValidHtml($content);
                }
                
                return $content;

            case 'chapters':
            case 'books':
            case 'book_groups':
                // For these types, clean any remaining artifacts
                $content = $this->cleanupDescription($content);
                return $content;

            default:
                return $content;
        }
    } catch (\Exception $e) {
        Log::error('Error processing response', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return $content ?? 'Error processing response';
    }
}

/**
 * Ensure HTML is valid and properly formatted
 *
 * @param string $content The HTML content to validate
 * @return string Valid HTML content
 */
private function ensureValidHtml($content)
{
    // If content doesn't have HTML tags, add basic paragraph tags
    if (strpos($content, '<') === false) {
        return '<p>' . str_replace("\n\n", '</p><p>', $content) . '</p>';
    }
    
    // Check for common HTML issues
    
    // 1. Missing paragraph tags around text
    if (!preg_match('/<p>/i', $content)) {
        $parts = preg_split('/(<h[1-6].*?>.*?<\/h[1-6]>|<ul>.*?<\/ul>|<ol>.*?<\/ol>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        
        foreach ($parts as $part) {
            if (preg_match('/^<h[1-6]|^<ul|^<ol/i', $part)) {
                // This is already a heading or list, keep as is
                $result .= $part;
            } elseif (trim($part) != '') {
                // This is text that needs paragraph tags
                $paragraphs = preg_split('/\n\n+/', trim($part));
                foreach ($paragraphs as $paragraph) {
                    if (trim($paragraph) != '') {
                        $result .= '<p>' . trim($paragraph) . '</p>';
                    }
                }
            }
        }
        
        $content = $result;
    }
    
    // 2. Ensure all tags are properly closed
    $openingTags = [
        '<p>' => '</p>',
        '<h2>' => '</h2>',
        '<h3>' => '</h3>',
        '<strong>' => '</strong>',
        '<em>' => '</em>',
        '<ul>' => '</ul>',
        '<ol>' => '</ol>',
        '<li>' => '</li>'
    ];
    
    foreach ($openingTags as $openTag => $closeTag) {
        $openCount = substr_count(strtolower($content), strtolower($openTag));
        $closeCount = substr_count(strtolower($content), strtolower($closeTag));
        
        // Add missing closing tags if needed
        if ($openCount > $closeCount) {
            $content .= str_repeat($closeTag, $openCount - $closeCount);
        }
    }
    
    return $content;
}
```

## Implementation Steps

1. Replace the existing `processPrompt` function in AIService with the new version
2. Add the new helper methods for response processing
3. Update the response handling logic to use these new methods

## Expected Results

With these changes, your AI-generated content will:

1. Provide comprehensive 800-1000 word articles instead of short metadata
2. Have a consistent structure based on content type
3. Include proper HTML formatting when needed for TinyMCE or frontend display
4. Avoid JSON artifacts and other unwanted content in the output

The key improvements are:
- Clear role definition for the AI
- Detailed content structure guidance
- Explicit formatting instructions
- Boundary markers to prevent artifacts
- Improved response cleaning to remove any remaining artifacts

These changes will create a much more robust system for generating and handling AI-generated content.