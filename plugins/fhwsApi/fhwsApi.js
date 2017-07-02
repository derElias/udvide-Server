function fhwsApi() {
    let img = document.createElement('img');
    generateMarker(img, pluginData["room"], 1000);
    pluginData["image"] = img.src;
}
