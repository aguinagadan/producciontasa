// $(document).ready(function(){
//     $.ajax({
//         url: 'game-v.html',
//         success: function (respuesta) {
//             $('head script').remove()
//         },
//         error: function () {
//             console.log("No se ha podido obtener la información");
//         }
//     });
// });
// $('head').append('<link href="css/stylesheet.css" rel="stylesheet">');
$('#page').html('<iframe src="game.html" style="height: calc(100vh - 60px)" frameborder="0"></iframe>');