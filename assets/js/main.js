$(document).ready(function() {
    $('.btn-delete').on('click', function(e) {
        if (!confirm("Are you sure you want to delete this item?")) {
            e.preventDefault();
        }
    });
});