class ImageUploader {
    constructor(options = {}) {
        this.options = {
            uploadUrl: options.uploadUrl || '/images/upload',
            attachUrl: options.attachUrl || '/images/attach',
            deleteUrl: options.deleteUrl || '/images/',
            csrfToken: document.querySelector('meta[name="csrf-token"]').content,
            ...options
        };
    }

    async upload(file, modelType = null, modelId = null) {
        const formData = new FormData();
        formData.append('image', file);

        if (modelType && modelId) {
            formData.append('model_type', modelType);
            formData.append('model_id', modelId);
            return this.sendRequest(this.options.attachUrl, formData);
        }

        return this.sendRequest(this.options.uploadUrl, formData);
    }

    async delete(imageId) {
        return this.sendRequest(`${this.options.deleteUrl}${imageId}`, null, {
            method: 'DELETE'
        });
    }

    async sendRequest(url, formData = null, options = {}) {
        const defaultOptions = {
            method: formData ? 'POST' : 'GET',
            headers: {
                'X-CSRF-TOKEN': this.options.csrfToken
            }
        };

        if (formData) {
            defaultOptions.body = formData;
        }

        const response = await fetch(url, { ...defaultOptions, ...options });
        return response.json();
    }
}
