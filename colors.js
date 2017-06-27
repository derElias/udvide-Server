// @script_author Simon
// for the following group of functions:
// "color" refers to a #-lead 4 or 7 length (including the #) hex-String

function colors_Sample() {
    // get a Value from a 2d polygon (in 3d rgb space the red = 255 plane):
    let c = ["#fff","#f00","#ff0","#f0f"];
    // throw stuff at it until its almost dead (reduced to one color)
    let log = colors_GetRandomColorBetween(c);
    // finally log the result
    console.log(log);
}

// get a Random Color in the polygon mesh defined by colors
// @in  array[] each value is a color
// @out string  a color that lays within the polygon mesh from colors
function colors_GetRandomColorBetween(colorsRef) {
    let colors = Object.create(colorsRef); // copy explicitly necessary since Call-by-Reference
    while (colors.length > 1) {
        colors[1] = colors_GetRandomColorBetweenTwo(colors[0],colors[1]);
        colors.shift()
    }
    return colors[0];
}
// get a Random Color on the line between color1 and color2
// @in  color1, color2  both colors
// @out #-lead hexString - Value random on 3D line between color1 and color2
function colors_GetRandomColorBetweenTwo(color1, color2) {
    let color = '#';

    color1 = colors_HexColorToIntArray(color1);
    color2 = colors_HexColorToIntArray(color2);

    let equalRand = Math.random();
    for (let i = 0; i <= 2; i++ ) {
        // @author this next line is adapted from
        // https://gamedev.stackexchange.com/a/23433
        let newVal = Math.round(color1[i] + (color2[i] - color1[i]) * equalRand);
        // prevent single Digit results
        newVal = newVal.toString(16);
        if (newVal.length === 1) {
            newVal = "0" + newVal;
        }
        // add to result
        color += newVal;
    }
    return color;
}
// @in  string    a color
// @out array[3]  3 element array with each value in the range of 0-255
function colors_HexColorToIntArray(hex) {
    if (hex.length === 4) {
        hex = "#"+hex[1]+hex[1]+hex[2]+hex[2]+hex[3]+hex[3];
    }
    return [
        parseInt(hex[1]+hex[2],16),
        parseInt(hex[3]+hex[4],16),
        parseInt(hex[5]+hex[6],16)
    ];
}
