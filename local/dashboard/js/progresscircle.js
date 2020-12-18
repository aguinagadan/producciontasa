function makesvg(percentage){

    var abs_percentage = String(Math.abs(percentage));
    // var percentage_str = String(percentage);
    var classes = "success-stroke";

    var svg = '<svg class="circle-chart" viewbox="0 0 33.83098862 33.83098862" xmlns="http://www.w3.org/2000/svg">'
        + '<circle class="circle-chart__background" cx="16.9" cy="16.9" r="15.9" />'
        + '<circle class="circle-chart__circle '+classes+'"'
        + 'stroke-dasharray="'+ abs_percentage+',100"    cx="16.9" cy="16.9" r="15.9" />'
        + '<g class="circle-chart__info">';
    //  + '   <text class="circle-chart__percent" x="17.9" y="15.5">'+percentage_str+'%</text>';

    svg += ' </g></svg>';

    return svg;
}

(function( $ ) {
    $.fn.circlechart = function() {
        this.each(function() {
            var percentage = $(this).data("percentage");
            var inner_text = $(this).text();
            $(this).html(makesvg(percentage, inner_text));
        });
        return this;
    };

}( jQuery ));