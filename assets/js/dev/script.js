$(function() {
    var ul = $('#upload ul');
    $('#drop a').click(function() {
        // Simulate a click on the file input button
        // to show the file browser dialog
        $(this).parent().find('input').click();
    });
    // Initialize the jQuery File Upload plugin
    $('#upload').fileupload({
        // This element will accept file drag/drop uploading
        dropZone: $('html'),
        // This function is called when a file is added to the queue;
        // either via the browse button, or via drag/drop:
        add: function(e, data) {
            checkBan(data);

            var tpl = $('<li class="working"> \
                        <input type="text" value="0" data-width="48" data-height="48"' + ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /> \
                        <p></p> \
                        <span></span> \
                   </li>');
            // Append the file name and file size
            tpl.find('p').text(data.files[0].name).append('<i>' + formatFileSize(data.files[0].size) + '</i>');
            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);
            // Initialize the knob plugin
            tpl.find('input').knob();
            // Listen for clicks on the cancel icon
            tpl.find('span').click(function() {
                if (tpl.hasClass('working')) {
                    jqXHR.abort();
                }
                tpl.fadeOut(function() {
                    tpl.remove();
                });
            });
            // Automatically upload the file once it is added to the queue
            var jqXHR = data.submit();
            // Check conformity of file for limitations.
            if (!checkSizeLimitations(data.files[0].size)) {
                tpl.find('p').text('File size limitations.');
                tpl.fadeOut(2500, function() {
                    jqXHR.abort();
                });
                return;
            }
        },
        progress: function(e, data) {
            // Calculate the completion percentage of the upload
            var progress = parseInt(data.loaded / data.total * 100, 10);
            // Update the hidden input field and trigger a change
            // so that the jQuery knob plugin knows to update the dial
            data.context.find('input').val(progress).change();
            if (progress == 100) {
                data.context.removeClass('working');
            }
        },
        fail: function(e, data) {
            // Something has gone wrong!
            data.context.addClass('error');
        },
        done: function(e, data) {
            if (data.textStatus === 'success') {
                var http = location.protocol;
                var slashes = http.concat("//");
                var host = slashes.concat(window.location.hostname)
                if (location.port !== '80' && location.port !== '') {
                    host = host.concat(":" + location.port);
                }
                host = host.concat('/');
                var urls = data.result.path.split(' ');
                var urlLong = host.concat(urls[0]);
                var urlShort = host.concat(urls[1]);
                data.context.find('p').append('<i class="short_download_url">' + urlShort + '</i>');
            }
        }
    });
    // Prevent the default action when a file is dropped on the window
    $(document).on('drop dragover', function(e) {
        e.preventDefault();
    });
    // Helper function that formats the file sizes
    function formatFileSize(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }
        if (bytes >= (1024 * 1024 * 1024)) {
            return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GiB';
        }
        if (bytes >= (1024 * 1024)) {
            return (bytes / (1024 * 1024)).toFixed(2) + ' MiB';
        }
        return (bytes / 1024).toFixed(2) + ' KiB';
    }
});
/**
 * Check file for conformity of size limitations.
 *
 * @param int size - Size of file.
 * @param string type - Type of user.
 * @returns {boolean} False - if file doesn't conform limitations. True - if conform.
 */
function checkSizeLimitations(size, type) {
    if (type === undefined) {
        type = 'free';
    }
    var sizeLimit = (type == 'premium' ? sizeLimitPremium() : sizeLimitFree());
    if (size > sizeLimit) {
        return false;
    }
    return true;
}
/**
 * Get size limit for files for free users.
 *
 * @return {int} Size in bytes.
 */
function sizeLimitFree() {
    return 1024 * 1024 * 100; // 100 MiB
}
/**
 * Get size limit for files for premium users.
 *
 * @return {int} Size in bytes.
 */
function sizeLimitPremium() {
    return 1024 * 1024 * 300; // 300 MiB
}

/**
 * Checking if user is banned.
 * @param  {array} data Data of uploaded file.
 */
function checkBan(data) {
    $.ajax({
        url: "/checkban",
        type: "POST",
        data: $('#upload').serialize(),
        cache: true,
        success: function(html) {
            if (html === 'true') {
                data.jqXHR.abort();
                var li = $('li.error');
                li.find('p').text('Error because of user limitations.');
                li.fadeOut(2500, function() {
                    li.remove();
                });
            }
        }
    });
}