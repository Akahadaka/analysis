<body style="background-color:#CCC">
	<div style="border:1px solid #AAA; background-color:#FFF;padding:10px 10px 0;">
		<form method="post">
			<div style="padding:5px;">
				<div><label for="source">Enter the folder location of your TXT files:</label></div>
				<div><input type="text" name="source" value="<?php echo $default_source; ?>" style="width:100%;font-size:1.05em;padding:5px"/></div>
			</div>
			<div style="padding:5px;">
				<div><label for="output">Enter the location to save your output:</label></div>
				<div><input type="text" name="output" value="<?php echo $default_output; ?>" style="width:100%;font-size:1.05em;padding:5px" /></div>
			</div>
			<div style="padding:5px;">
				<input type="submit" name="submit" value="Save" style="font-size:1.1em;padding:5px 20px" /></div>
			</div>
		</form>
            
	</div>
        
        <?php echo $dump_data; ?>
</body>