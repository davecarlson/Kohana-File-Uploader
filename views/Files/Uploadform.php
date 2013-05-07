<form action="" method="post" class="urlshortener-form" enctype="multipart/form-data">
	<?php echo Form::label("url", "Drag your images into the box below or select files to upload"); ?>
	<br />
	<div id="file-uploader"></div>

	<br class="clear" />
</form>
<?php if (isset($error)): ?>
	<br />
	<div class="url">
		Could not upload file.
	</div>
<?php endif; ?>

<script>        
	function createUploader(){            
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader'),
				action: 'file/post',
				debug: true
			});           
		}
        
        // in your app create uploader as soon as the DOM is ready
        // don't wait for the window to load  
        window.onload = createUploader;     
</script>