<?php
require_once 'udvide.php';

$ud = new udvide();
echo time();
?>
<div id="drop_zone">Drop files here</div>
<output id="list"></output>

<script>
    let image;
    let dropZone = document.getElementById('drop_zone');

  function handleFileDrop(evt) {
      evt.stopPropagation();
      evt.preventDefault();

      let files = evt.dataTransfer.files; // FileList object.

      // files is a FileList of File objects. List some properties.
      let output = [];
      if (files[1]) {
          for (let i = 0, f; f = files[i]; i++) {
              output.push('<li><strong>', encodeURI(f.name), '</strong> (', f.type || 'n/a', ') - ',
                  f.size, ' bytes, last modified: ',
                  f.lastModifiedDate ? f.lastModifiedDate.toLocaleDateString() : 'n/a',
                  '</li>');
          }
      } else {
          file=files[0];


          let reader = new FileReader();

          // Closure to capture the file information.
          reader.onload = (function(theFile) {
              return function(e) {
                  // Render thumbnail.
                  let span = document.createElement('span');
                  image = e.target.result;
                  span.innerHTML = ['<img class="thumb" src="', image,
                      '" title="', encodeURI(theFile.name), '"/>'].join('');
                  document.getElementById('list').insertBefore(span, null);
              };
          });

          // Read in the image file as a data URL.
          reader.readAsDataURL(file);


      }
    document.getElementById('list').innerHTML = '<ul>' + output.join('') + '</ul>';
  }

  function handleDragOver(evt) {
      evt.stopPropagation();
      evt.preventDefault();
      evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
  }

  // Setup the dnd listeners.
  dropZone.addEventListener('dragover', handleDragOver, false);
  dropZone.addEventListener('drop', handleFileDrop, false);
</script>