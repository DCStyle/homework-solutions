/**
 * Image Paste Handler
 * 
 * Handles converting pasted images (base64) to uploaded files
 */
class ImagePasteHandler {
    constructor(options = {}) {
        this.options = {
            uploadUrl: options.uploadUrl || '/images/upload',
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
            ...options
        };
    }

    /**
     * Process HTML content with base64 images and replace them with uploaded images
     * @param {string} content - HTML content with possible base64 images
     * @param {function} progressCallback - Callback to update progress
     * @returns {Promise<string>} - HTML content with all images uploaded
     */
    async processContent(content, progressCallback = null) {
        // Find all base64 encoded images
        const imgRegex = /<img[^>]+src="data:image\/[^;]+;base64,[^"]+"/g;
        const matches = content.match(imgRegex) || [];
        
        if (matches.length === 0) {
            return content;
        }

        let processedContent = content;
        for (let i = 0; i < matches.length; i++) {
            if (progressCallback) {
                progressCallback((i / matches.length) * 100);
            }

            // Extract base64 data
            const match = matches[i];
            const base64Regex = /src="(data:image\/[^;]+;base64,[^"]+)"/;
            const base64Match = match.match(base64Regex);
            
            if (base64Match && base64Match[1]) {
                const base64Data = base64Match[1];
                const uploadedUrl = await this.uploadBase64Image(base64Data);
                
                if (uploadedUrl) {
                    // Replace the base64 image with the uploaded one
                    processedContent = processedContent.replace(
                        base64Data, 
                        uploadedUrl
                    );
                }
            }
        }

        if (progressCallback) {
            progressCallback(100);
        }

        return processedContent;
    }

    /**
     * Upload a base64 image
     * @param {string} base64Data - base64 encoded image data
     * @returns {Promise<string>} - URL of the uploaded image
     */
    async uploadBase64Image(base64Data) {
        try {
            // Convert base64 to blob
            const blob = this.base64ToBlob(base64Data);
            if (!blob) return null;

            // Create a file name
            const ext = this.getImageExtensionFromBase64(base64Data);
            const filename = `pasted_image_${Date.now()}.${ext}`;
            
            // Create file from blob
            const file = new File([blob], filename, { type: blob.type });

            // Upload the file
            const formData = new FormData();
            formData.append('image', file);

            const response = await fetch(this.options.uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                // Store the image ID in the hidden input
                const imageIdsInput = document.getElementById('uploaded_image_ids');
                const currentIds = imageIdsInput.value ? JSON.parse(imageIdsInput.value) : [];
                currentIds.push(result.image_id);
                imageIdsInput.value = JSON.stringify(currentIds);
                
                return result.url;
            }
            
            console.error('Error uploading image:', result.message);
            return null;
        } catch (error) {
            console.error('Error uploading base64 image:', error);
            return null;
        }
    }

    /**
     * Convert base64 to Blob
     * @param {string} base64Data - base64 encoded image data
     * @returns {Blob} - Blob representation of the image
     */
    base64ToBlob(base64Data) {
        try {
            // Extract the base64 data part from the full string
            const parts = base64Data.split(',');
            const matches = parts[0].match(/:(.*?);/);
            if (!matches || !matches[1]) return null;
            
            const contentType = matches[1];
            const raw = window.atob(parts[1]);
            const rawLength = raw.length;
            const uInt8Array = new Uint8Array(rawLength);

            for (let i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }

            return new Blob([uInt8Array], { type: contentType });
        } catch (error) {
            console.error('Error converting base64 to blob:', error);
            return null;
        }
    }

    /**
     * Get image extension from base64 data
     * @param {string} base64Data - base64 encoded image data
     * @returns {string} - Image extension (jpg, png, etc.)
     */
    getImageExtensionFromBase64(base64Data) {
        // Extract the MIME type
        const matches = base64Data.match(/^data:image\/([a-zA-Z]+);/);
        if (matches && matches[1]) {
            return matches[1].toLowerCase();
        }
        return 'png'; // Default to png if can't determine
    }
} 