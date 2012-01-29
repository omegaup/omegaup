function niceDateString(date) {
    return date.getFullYear() + "-" + 
           (date.getMonth()+1) + "-" +
           date.getDate() + " " +
           date.getHours() + ":" +
           (date.getMinutes() < 10 ? "0" : "") + date.getMinutes();
}

Raphael.fn.drawGrid = function (x, y, w, h, wv, hv, color) {
    // Draw a grid with Raphael
    // x: Starting x position
    // y: Starting y position
    // w: Width
    // h: Height
    // wv: Number of width divisions
    // hv: Number of height divisions
    // color: Color of the gridlines

    var path = ["M", Math.round(x), Math.round(y), "L", Math.round(x + w), Math.round(y), Math.round(x + w), Math.round(y + h), Math.round(x), Math.round(y + h), Math.round(x), Math.round(y)],
        rowHeight = h / hv,
        columnWidth = w / wv;

    color = color || "#000";

    for (var i = 1; i < hv; i++) {
        path = path.concat(["M", Math.round(x), Math.round(y + i * rowHeight), "H", Math.round(x + w)]);
    }

    for (i = 1; i < wv; i++) {
        path = path.concat(["M", Math.round(x + i * columnWidth), Math.round(y), "V", Math.round(y + h)]);
    }

    return this.path(path.join(",")).attr({ stroke: color });
};

Raphael.fn.popup = function (startx, starty, set, pos, ret) {
    // Draw popup with Raphael
    // startx: Starting x position
    // starty: Starting y position
    // set: Raphael set of data to draw inside popup
    // pos: Alingment of data inside popup
    // ret: If true, data is returned instead of path

    function fill(str, obj) {
        // Fill a string with the values of each property in obj (like printf)
        // str: String template to be filled
        // obj: Object with data to replace

        var tokenRegex = /\{([^\}]+)\}/g,
            objNotationRegex = /(?:(?:^|\.)(.+?)(?=\[|\.|$|\()|\[('|")(.+?)\2\])(\(\))?/g; // Matches .xxxxx or ["xxxxx"] to run over object properties

        function replacer(all, key, obj) {
            var res = obj;
            key.replace(objNotationRegex, function (all, name, quote, quotedName, isFunc) {
                name = name || quotedName;
                if (res) {
                    if (name in res) {
                        res = res[name];
                    }
                    if (typeof res === "function" && isFunc) {
                        // If object is a function evaluate its result
                        res = res();
                    }
                }
            });
            res = ((res === null || res === obj) ? all : res) + "";
            return res;
        }

        return String(str).replace(tokenRegex, function (all, key) {
            return replacer(all, key, obj);
        });
    }

    pos = String(pos || "top-middle").split("-");
    pos[1] = pos[1] || "middle";
    var bb = set.getBBox(),              // Bounding box for drawn elements
        w = Math.round(bb.width + 30),   // Width of popup
        h = Math.round(bb.height + 20),  // Height of popup
        x = Math.round(bb.x) - 15,       // Starting x position to draw popup elements
        y = Math.round(bb.y) - 10,       // Starting y position to draw popup elements
        gap = Math.min(h / 2, w / 2, 10), // Size of the popup triangle's gap
        shapes = {
            top: "M{x},{y}h{w4},{w4},{w4},{w4}a{r},{r},0,0,1,{r},{r}v{h4},{h4},{h4},{h4}a{r},{r},0,0,1,-{r},{r}l-{right},0-{gap},{gap}-{gap}-{gap}-{left},0a{r},{r},0,0,1-{r}-{r}v-{h4}-{h4}-{h4}-{h4}a{r},{r},0,0,1,{r}-{r}z",
            bottom: "M{x},{y}l{left},0,{gap}-{gap},{gap},{gap},{right},0a{r},{r},0,0,1,{r},{r}v{h4},{h4},{h4},{h4}a{r},{r},0,0,1,-{r},{r}h-{w4}-{w4}-{w4}-{w4}a{r},{r},0,0,1-{r}-{r}v-{h4}-{h4}-{h4}-{h4}a{r},{r},0,0,1,{r}-{r}z",
            right: "M{x},{y}h{w4},{w4},{w4},{w4}a{r},{r},0,0,1,{r},{r}v{h4},{h4},{h4},{h4}a{r},{r},0,0,1,-{r},{r}h-{w4}-{w4}-{w4}-{w4}a{r},{r},0,0,1-{r}-{r}l0-{bottom}-{gap}-{gap},{gap}-{gap},0-{top}a{r},{r},0,0,1,{r}-{r}z",
            left: "M{x},{y}h{w4},{w4},{w4},{w4}a{r},{r},0,0,1,{r},{r}l0,{top},{gap},{gap}-{gap},{gap},0,{bottom}a{r},{r},0,0,1,-{r},{r}h-{w4}-{w4}-{w4}-{w4}a{r},{r},0,0,1-{r}-{r}v-{h4}-{h4}-{h4}-{h4}a{r},{r},0,0,1,{r}-{r}z"
        },
        mask = [{
        x: x,
        y: y,
        w: w,
        w4: w / 4,
        h4: h / 4,
        right: 0,
        left: w - gap * 2,
        bottom: 0,
        top: h - gap * 2,
        h: h,
        gap: gap
    }, {
        x: x,
        y: y,
        w: w,
        w4: w / 4,
        h4: h / 4,
        left: w / 2 - gap,
        right: w / 2 - gap,
        top: h / 2 - gap,
        bottom: h / 2 - gap,
        h: h,
        gap: gap
    }, {
        x: x,
        y: y,
        w: w,
        w4: w / 4,
        h4: h / 4,
        left: 0,
        right: w - gap * 2,
        top: 0,
        bottom: h - gap * 2,
        h: h,
        gap: gap
    }][pos[1] === "middle" ? 1 : (pos[1] === "top" || pos[1] === "left") * 2];

    var dx = 0,
        dy = 0,
        out = this.path(fill(shapes[pos[0]], mask)).insertBefore(set);

    switch (pos[0]) {
        case "top":
            dx = startx - (x + mask.left + gap);
            dy = starty - (y + h + gap);
            break;

        case "bottom":
            dx = startx - (x + mask.left + gap);
            dy = starty - (y - gap);
            break;

        case "left":
            dx = startx - (x + w + gap);
            dy = starty - (y + mask.top + gap);
            break;

        case "right":
            dx = startx - (x - gap);
            dy = starty - (y + mask.top + gap);
            break;
    }
    out.translate(dx, dy);
    if (ret) {
        ret = out.attr("path");
        out.remove();
        return {
            path: ret,
            dx: dx,
            dy: dy
        };
    }
    set.translate(dx, dy);
    return out;
};

function drawLineChartInternal(options) {
    // Draw line chart with Raphael

    // Handle passed options
    var element = options.element,
        labels = options.labels,
        data = options.values,
        colorhue = options.colorhue || 0,
        voffset = options.voffset || 0;

    // Draw
    var width = options.width || 500,
        height = options.height || 250,
        leftgutter = 50,
        labelsgutter = 20,
        bottomgutter = 30,
        topgutter = 30,
        color = "hsb(" + [colorhue, .5, 1] + ")",
        r = Raphael(element, width, height),
        txt = { font: "12px 'Segoe UI Light', Arial", fill: "#fff" },
        txt1 = { font: "12px 'Segoe UI Semibold', Arial", fill: "#fff", "font-weight": 800, "font-size":14 },
        txt2 = { font: "12px 'Segoe UI Light', Arial", fill: "#000" },
        xdelta = (width - leftgutter) / labels.length,
        max = Math.max.apply(Math, data) - voffset,
        ydelta = (height - bottomgutter - topgutter) / max,
        i = 0,
        len = 0;

    // Draw chart grid
    r.drawGrid(leftgutter, topgutter, width - leftgutter - xdelta, height - topgutter - bottomgutter, 0, 10, "#333");

    // Draw Y-axis labels
    var textOffset = 0,
        current = max;
    for (i = 0; i <= 10; i += 2) {
        var value = current + voffset;
        r.text(labelsgutter, textOffset + topgutter, value).attr(txt);
        current -= Math.round(2 * (max / 10));
        textOffset += 2 * (height - topgutter - bottomgutter) / 10;
    }

    // Initialize graph
    var path = r.path().attr({ stroke: color, "stroke-width": 3, "stroke-linejoin": "round" }),
        bgp = r.path().attr({ stroke: "none", opacity: .3, fill: color }),
        label = r.set(),
        is_label_visible = false,
        first = true,
        leave_timer,
        blanket = r.set();

    // Initialize popup label
    label.push(r.text(60, 12, "").attr(txt1));
    label.push(r.text(60, 27, "").attr(txt).attr({ font: "12px 'Segoe UI', Arial" }));
    label.hide();

    // Initialize popup frame
    var frame = r.popup(0, 0, label, "left").attr({fill: "#000", stroke: "#666", "stroke-width": 2, "fill-opacity": .7}).hide();

    function processHover(x, y, data, labelText, rect) {
        // Create callbacks for hovering over graph to show and hide popup
        // x: X position of the datapoint
        // y: Y position of the datapoint
        // data: Data element of popup
        // labelText: Label of popup
        // rect: Bounding rectangle for hover area

        var dot = r.circle(x, y, 0).attr({ fill: "#000", stroke: color, "stroke-width": 2 });

        rect.hover(function () {
            clearTimeout(leave_timer);
            var side = "right";
            if (x + frame.getBBox().width > width) {
                side = "left";
            }

            label[0].attr({ text: labelText });
            label[1].attr({ text: data });

            var ppp = r.popup(x, y, label, side, 1);

            if (first) {
                frame.show().attr({ path: ppp.path });
                label[0].show().translate(ppp.dx, ppp.dy);
                label[1].show().translate(ppp.dx, ppp.dy);
            } else {
                frame.show().stop().animate({ path: ppp.path }, 200 * is_label_visible);
                label[0].attr({ text: data }).show().stop().animateWith(frame, { translation: [ppp.dx, ppp.dy] }, 200 * is_label_visible);
                label[1].attr({ text: labelText }).show().stop().animateWith(frame, { translation: [ppp.dx, ppp.dy] }, 200 * is_label_visible);
            }

            dot.attr("r", 4);
            is_label_visible = true;
            first = false;
        }, function () {
            dot.attr("r", 0);
            leave_timer = setTimeout(function () {
                frame.hide();
                label[0].hide();
                label[1].hide();
                is_label_visible = false;
            }, 1);
        });
    }

    var pathDescription,
        bgPathDescription,
        first = true;
    
    var oldy;
    for (i = 0, len = labels.length; i < len; i++) {
        var x = Math.round(leftgutter + xdelta * (i));
            //t = r.text(x, height - 6, parseInt(labels[i], 10)).attr(txt).toBack();
        if (typeof data[i] === "number" && !isNaN(data[i])) {
            var y = Math.round(height - bottomgutter - (ydelta * (data[i]-voffset)));
            
            if (first) {
                pathDescription = ["M", x, height - bottomgutter, "L", x, y];
                bgPathDescription = ["M", x, height - bottomgutter, "L", x, y];
                first = false;
                oldy = y;
            } else if (i > 0 && i < len - 1) {
                pathDescription = pathDescription.concat(["L", x, oldy, "L", x, y]);
                bgPathDescription = bgPathDescription.concat(["L", x, y]);
                oldy = y;
            }
            
            
            // draw stripes for hour change
            /* var divisions = 4;
            if (labels[i].getHours() % (divisions*2) < divisions) {
                (function() {
                    var line = ["M", x, y, "V", height-bottomgutter, "H", x + xdelta, "V", y, "Z"];
                    r.path(line.concat()).attr({fill: "#000"}).toBack();
                })();
            } */ 
            
            // a division every hour
            /* if (labels[i].getHours() == 0 && labels[i].getMinutes() == 0) {
                (function() {
                    var line = ["M", x, topgutter, "V", height-bottomgutter];
                    r.path(line.concat()).attr({stroke: "#333", "stroke-width": 3}).toBack();
                })();
            } else if (labels[i].getMinutes() == 0 && labels[i].getHours() % 2 == 0) {
                (function() {
                    var line = ["M", Math.round(x), topgutter, "V", height-bottomgutter];
                    r.path(line.concat()).attr({stroke: "#333"}).toBack();
                })();
            }*/

            blanket.push(r.rect(leftgutter + xdelta * i, 0, xdelta, height - bottomgutter).attr({ stroke: "none", fill: "#fff", opacity: 0 }));

            processHover(x, y, data[i] + " users", niceDateString(labels[i]), blanket[blanket.length - 1]);
        }
    }

    if (typeof y === "number" && !isNaN(y)) {
        pathDescription = pathDescription.concat(["L", x, oldy, "L", x, y]);
        bgPathDescription = bgPathDescription.concat([x, y, x, y]);
    }
    if (bgPathDescription) {
        bgPathDescription = bgPathDescription.concat(["L", x, height - bottomgutter, "z"]);
        path.attr({ path: pathDescription });
        bgp.attr({ path: bgPathDescription });
    }
    console.log(pathDescription);
    frame.toFront();
    label[0].toFront();
    label[1].toFront();
    blanket.toFront();
}

function drawLineChart(options) {
    // Draws a line chart with Raphael
    // Necessary options are:
    // - element: string ID or HTML element to draw the chart on
    // - labels: chart labels
    // - values: chart values
    // Optional options are:
    // - width: canvas width
    // - height: canvas height
    // - colorhue: hue of the chart's color
    // - id: ID of the added element
    
    var oldElement = options.element;

    var element = options.element;
    if (typeof options.element === "string") {
        element = document.getElementById(options.element);
    }

    var container = document.createElement("div");
    element.appendChild(container);

    options.element = container;

    drawLineChartInternal(options);
}