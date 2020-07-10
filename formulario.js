(function($){
$(document).ready(()=>{
    $("#privacidad").click((e)=>{
        //e.preventDefault();
        alert("Aceptando privacidad");
        $("#btnFormulario").prop( "disabled", false );
    })
})
})(jQuery);