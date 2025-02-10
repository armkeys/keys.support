jQuery(document).ready(function($) {
    $('#betterdocs-ajax-search').on('input', function() {
        let keyword = $(this).val();
        let resultsContainer = $('#betterdocs-search-results');

        // If the input is empty, hide results
        if (keyword.length < 1) {
            resultsContainer.html('').hide();
            return;
        }

        $.ajax({
            url: "https://keys.express/wp-admin/admin-ajax.php",
            type: 'POST',
            data: {
                action: 'betterdocs_ajax_search',
                keyword: keyword
            },
            beforeSend: function() {
                resultsContainer.show().html('<div>Searching...</div>');
            },
            success: function(response) {
                resultsContainer.html(response);
            },
            error: function() {
                resultsContainer.html('<div>Something went wrong. Please try again.</div>');
            }
        });
    });

});
