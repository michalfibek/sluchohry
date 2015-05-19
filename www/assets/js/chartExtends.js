Chart.types.Doughnut.extend({
    name: "DoughnutAlt",
    draw: function(){
        Chart.types.Doughnut.prototype.draw.apply(this, arguments);
        //console.log(this);
        for(var i = 0; i < this.segments.length; i++){
            var centreAngle = this.segments[i].startAngle + ((this.segments[i].endAngle - this.segments[i].startAngle) / 2),
                rangeFromCentre = (this.segments[i].outerRadius - this.segments[i].innerRadius) / 2 + this.segments[i].innerRadius;

            x = this.segments[i].x + (Math.cos(centreAngle) * rangeFromCentre);
            y = this.segments[i].y + (Math.sin(centreAngle) * rangeFromCentre);
            var ctxMain = this.chart.ctx;
            ctxMain.textAlign = 'center';
            ctxMain.textBaseline = 'middle';
            ctxMain.fillStyle = '#fff';
            ctxMain.font = 'normal 16px Helvetica';
            if (this.segments[i].value > 0)
                ctxMain.fillText(this.segments[i].value, x, y);
        }
    }
});
