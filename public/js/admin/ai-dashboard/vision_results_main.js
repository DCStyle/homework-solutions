$(document).ready(function() {
    // Copy to clipboard functionality
    const copyBtn = $('#copyBtn');
    const analysisResult = $('#analysisResult');

    copyBtn.on('click', function() {
        // Create a textarea element to hold the text
        const textarea = $('<textarea>');
        textarea.val(analysisResult.text());
        $('body').append(textarea);

        // Select and copy the text
        textarea.select();
        document.execCommand('copy');

        // Remove the textarea
        textarea.remove();

        // Show feedback with tooltip
        const originalHtml = copyBtn.html();
        copyBtn.html('<span class="iconify" data-icon="mdi-check"></span>');

        const tooltip = new bootstrap.Tooltip(copyBtn[0], {
            title: 'Đã sao chép!',
            placement: 'top',
            trigger: 'manual'
        });

        tooltip.show();

        setTimeout(() => {
            copyBtn.html(originalHtml);
            tooltip.hide();
        }, 2000);
    });

    // Download functionality
    const downloadBtn = $('#downloadBtn');

    downloadBtn.on('click', function() {
        // Create a text file from the analysis result
        const text = analysisResult.text();
        const filename = 'ket-qua-phan-tich-hinh-anh.txt';

        const element = $('<a>');
        element.attr('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.attr('download', filename);
        element.css('display', 'none');

        $('body').append(element);
        element[0].click();
        element.remove();
    });
});
