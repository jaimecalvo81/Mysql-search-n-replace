<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MySQL Database Search &amp; Replace Tool</title>
</head>
<body>
    <h1>MySQL Database Search &amp; Replace Tool</h1>
    <form method="post" action="mysql-replace.php" class="labelsRightAligned hintsTooltip">
        <fieldset>
	        <legend>Search &amp; Replace</legend>
	        <div>
	            <label for="searchFor">Search for: <span class="reqMark">*</span></label>
	            <input type="text" id="searchFor" name="searchFor" value="" size="40" class="required" />
	        <div>
	            <label for="replaceWith">Replace with: <span class="reqMark">*</span></label>
	            <input type="text" id="replaceWith" name="replaceWith" value="" size="40" class="required" />
	        </fieldset>
	        <fieldset>
	        <legend>Database Information</legend>
	        <div>
	            <label for="hostname">Hostname: <span class="reqMark">*</span></label>
	            <input type="text" id="hostname" name="hostname" value="localhost" size="40" class="required" />
	        <div>
	            <label for="database">Database: <span class="reqMark">*</span></label>
	            <input type="text" id="database" name="database" value="" size="40" class="required" />
			</div>
	        <div>
	            <label for="username">Username <span class="reqMark">*</span></label>
	            <input type="text" id="username" name="username" value="root" size="40" class="required" />
	        <div>
	            <label class="preField" for="password">Password <span class="reqMark">*</span></label>
	            <input type="password" id="password" name="password" value="" size="40" class="required" />
	        </div>
        </fieldset>
        <fieldset>
	        <legend>Search / Replace</legend>
	        <?php
	        	$Searchchecked ='';
	        	$Replacedchecked ='';
	            
	            $OK = isset($_POST['queryType']) ? true : false;
	            if ($OK && isset ($missing) && $_POST['queryType'] == 'search') { 
	            	$Searchchecked =  'checked="checked"';
	            } 
	            if ($OK && isset ($missing) && $_POST['queryType'] == 'replace') { 
	            	$Replacedchecked = 'checked="checked"';
	            }	

	        ?>
	        <div>
	            <input name="queryType" type="radio" id="search" value="search" <?php echo $Searchchecked; ?> />
	            <label class="radio" for="queryType">Search</label>
	            <input name="queryType" type="radio" id="replace" value="replace" <?php echo $Replacedchecked ?> />
	            <label class="radio" for="queryType">Replace</label>
	        </div>
        </fieldset>
        <div class="actions">
            <input type="submit" class="primaryAction" id="submit" name="submitAction" value="start" />
            <input type="button" class="secondaryAction" onclick="history.go(-1)" value="cancel" />
        </div>
    </form>
</body>
</html>
