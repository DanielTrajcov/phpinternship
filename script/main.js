$(document).ready(function() {
    $('#voteForm').on('submit', function(e) {
        e.preventDefault(); 

        var formData = $(this).serialize() + '&ajax=true';

        $.ajax({
            type: 'POST',
            url: '', 
            data: formData,
            success: function(response) {
                const data = JSON.parse(response);
                alert(data.message); 

                if (data.status === 'success') {
                    $('#voteForm').trigger('reset'); 
                    $('select').val('').trigger('change'); 
                    $('textarea').val(''); 
                }
            },
            error: function() {
                alert('An error occurred while submitting your vote.');
            }
        });
    });
});


function showForm(formId) {
    document.getElementById('login-form').classList.remove('active');
    document.getElementById('register-form').classList.remove('active');
    document.getElementById(formId + '-form').classList.add('active');
}

// function showForm(formId) {
//   $('.form-container').removeClass('active');
//   $(`#${formId}-form`).addClass('active');
// }
