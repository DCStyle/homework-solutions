$(document).ready(function() {
    const dropZone = $('#dropZone');
    const fileInput = $('#image');
    const imagePreview = $('#imagePreview');
    const imagePreviewContainer = $('#imagePreviewContainer');
    const removeImageBtn = $('#removeImage');
    const modelSelect = $('#model');
    const deepseekOptions = $('#deepseekOptions');
    const temperatureSlider = $('#temperature');
    const temperatureValue = $('#temperatureValue');

    // Xử lý sự kiện nhấn vào khu vực kéo thả
    dropZone.on('click', function() {
        fileInput.click();
    });

    // Xử lý sự kiện kéo thả
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.on(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    // Thêm hiệu ứng khi kéo file vào
    dropZone.on('dragenter dragover', function() {
        $(this).addClass('border-indigo-500 bg-indigo-100');
        $(this).removeClass('border-indigo-300 bg-indigo-50');
    });

    // Bỏ hiệu ứng khi kéo file ra hoặc thả file
    dropZone.on('dragleave drop', function() {
        $(this).removeClass('border-indigo-500 bg-indigo-100');
        $(this).addClass('border-indigo-300 bg-indigo-50');
    });

    // Xử lý khi thả file
    dropZone.on('drop', function(e) {
        const files = e.originalEvent.dataTransfer.files;

        if (files.length) {
            fileInput[0].files = files;
            updateImagePreview();
        }
    });

    // Xử lý khi chọn file
    fileInput.on('change', updateImagePreview);

    // Cập nhật xem trước hình ảnh
    function updateImagePreview() {
        if (fileInput[0].files && fileInput[0].files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                imagePreview.attr('src', e.target.result);
                imagePreviewContainer.removeClass('hidden');
                dropZone.addClass('hidden');
            };

            reader.readAsDataURL(fileInput[0].files[0]);
        }
    }

    // Xóa hình ảnh
    removeImageBtn.on('click', function() {
        fileInput.val('');
        imagePreviewContainer.addClass('hidden');
        dropZone.removeClass('hidden');
    });

    // Xử lý thay đổi mô hình
    modelSelect.on('change', function() {
        if ($(this).val().startsWith('deepseek')) {
            deepseekOptions.removeClass('hidden');
        } else {
            deepseekOptions.addClass('hidden');
        }
    });

    // Cập nhật hiển thị giá trị nhiệt độ
    temperatureSlider.on('input', function() {
        temperatureValue.text($(this).val());
    });

    // Xử lý chọn ví dụ câu hỏi
    $('.example-prompt').on('click', function() {
        const promptText = $(this).find('p').text();
        $('#prompt').val(promptText).focus();

        // Đánh dấu ví dụ đã chọn
        $('.example-prompt').removeClass('border-indigo-500');
        $(this).addClass('border-indigo-500');
    });
});
