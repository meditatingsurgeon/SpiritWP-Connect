jQuery(document).ready(function($) {
    // Admin JS for SpiritWP Connect
    
    // Auto-dismiss notices
    setTimeout(function() {
        $('.spwp-admin-wrap .updated, .spwp-admin-wrap .error').fadeOut('slow');
    }, 5000);
});
