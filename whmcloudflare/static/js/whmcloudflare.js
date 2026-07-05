(function () {
    'use strict';

    function csrfParam() {
        var el = document.querySelector('input[name="cp_security_token"]');
        return el && el.value ? el.value : '';
    }

    function loadContent(query) {
        var data = query || window.location.search.replace(/^\?/, '');
        jQuery('#whmcfContent').html('<p class="whmcf-loading">Loading&hellip;</p>');
        jQuery.ajax({
            url: './index.php',
            type: 'GET',
            data: data,
            success: function (html) {
                jQuery('#whmcfContent').html(html);
            },
            error: function () {
                jQuery('#whmcfContent').html('<div class="alert alert-danger">Failed to load WHMCloudFlare UI.</div>');
            }
        });
    }

    window.whmcfSubmit = function (form) {
        var data = jQuery(form).serialize();
        var token = csrfParam();
        if (token && data.indexOf('cp_security_token=') === -1) {
            data += (data ? '&' : '') + 'cp_security_token=' + encodeURIComponent(token);
        }
        jQuery('#whmcfContent').html('<p class="whmcf-loading">Loading&hellip;</p>');
        jQuery.ajax({
            url: './index.php',
            type: 'POST',
            data: data,
            success: function (html) {
                jQuery('#whmcfContent').html(html);
            },
            error: function () {
                jQuery('#whmcfContent').html('<div class="alert alert-danger">Request failed.</div>');
            }
        });
        return false;
    };

    jQuery(document).ready(function () {
        loadContent();
    });
})();
