    var pwHtml = $('');
var pwSet = null;
var pwFields = $('#password, #password-verify');

$('#user-password-generate').click(function() {
    var N = 8;
    var pw = (Math.random().toString(36)+'00000000000000000').slice(2, N+2);
    pwFields.val(pw).addClass('pw-generated').removeClass('pw-match');
    pwHtml.remove();
    pwHtml = $('<strong>' + pw + '</strong>');
    pwHtml.appendTo('#generated-pw');
    pwSet = true;
});

pwFields.on('input',function() {
    if (pwSet) {
        pwHtml.remove();
        pwFields.removeClass('pw-generated');
        pwSet = false;
    } else {
        if ( ($('#password').val() == $('#password-verify').val()) && $('#password').val().length > 5 ) {
            pwFields.addClass('pw-match');
        } else {
            pwFields.removeClass('pw-match');
        }
    }
});
