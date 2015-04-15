$( '.game-select' ).click(function( event ) {
    event.preventDefault();
    var link = $(this).attr('href');

    $('.modal-difficulty').modal('show');
    $('.btn-difficulty').each( function() {
        var difficultyLink = link + '?difficulty=' + $(this).data('difficulty');
        $(this).attr('href', difficultyLink);
    })
});
