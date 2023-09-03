<?php
#**********************************************************************************#

				
				#****************************************#
				#********** PAGE CONFIGURATION **********#
				#****************************************#
				
				require_once('./include/config.inc.php');
				require_once('./include/form.inc.php');
				require_once('./include/db.inc.php');
				require_once('./include/dateTime.inc.php');


#***********************************************************************************************#


											#*******************************************#
											#********** REGENERATE SESSION ID **********#
											#*******************************************#
										
										
											#********** PREPARE SESSION **********#
			
				session_name('blogprojekt');
				
				
											#********** START/CONTINUE SESSION **********#
				
				session_start();

											#*******************************************#
											#********** CHECK FOR VALID LOGIN **********#
											#*******************************************#
				
				
												#********** NO VALID LOGIN **********#
				if( isset($_SESSION['ID']) === false OR $_SESSION['IPAddress'] !== $_SERVER['REMOTE_ADDR'] ) {
					// Fehlerfall (Seitenaufrufer ist nicht eingeloggt)
if(DEBUG)		echo "<p class='debug auth hint'><b>Line " . __LINE__ . "</b>: Seitenaufrufer ist nicht eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
					
											#********** DELETE EMPTY SESSION **********#
					
					session_destroy();
					
					// Flag zur weiteren Verwendung setzen
					$loggedIn = false;
					

											#*********************** VALID LOGIN *********************#
				} else {
					// Erfolgsfall (Seitenaufrufer ist eingeloggt)
if(DEBUG)		echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Seitenaufrufer ist eingeloggt. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
				
					session_regenerate_id(true);
					
					$userID 				= $_SESSION['ID'];

					// Flag zur weiteren Verwendung setzen
					$loggedIn = true;
					
	
					
				}	//CHECK VALID LOGIN END			


#**********************************************************************************#


				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
				$errorLogin 					= NULL;
				$catFilterID 					= NULL;
				

#**********************************************************************************#




														
														#****************************************#
														#********** PROCESS FORM LOGIN **********#
														#****************************************#
				
				
				
				// Schritt 1 FORM: Prüfen, ob Formular gesendet wurde
				if( isset($_POST['formLogin']) === true ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'Login' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
					
					// Schritt 2 FORM: Formulardaten auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Daten werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$userEmailForm = sanitizeString($_POST['userEmailForm']);
					$passwordForm 	= sanitizeString($_POST['passwordForm']);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$userEmailForm: $userEmailForm <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$passwordForm: $passwordForm <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					// Schritt 3 FORM: Feldvalidierung, Feldvorbelegung, Final Form Validation
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$errorUserEmail 	= validateEmail($userEmailForm);
					$errorPassword 	= validateInputString($passwordForm, minLength:4);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorUserEmail: $errorUserEmail <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$errorPassword: $errorPassword <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					#**********  FORM VALIDATION **********#
					if( $errorUserEmail !== NULL OR $errorPassword !== NULL ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
						$errorLogin = 'Loginname oder Passwort falsch!';
												
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						
						// Schritt 4 FORM: Formulardaten weiterverarbeiten						
						
														
														#*****************************************#
														#********** VALIDATE LOGIN DATA **********#
														#*****************************************#						
														
											#********** FETCH USER DATA FROM DATABASE BY EMAIL **********#
						// Schritt 1 DB: DB-Verbindung herstellen
						$PDO = dbConnect('blogprojekt');
						
						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
						$sql 		= 'SELECT userID, userPassword FROM users
										WHERE userEmail = :userEmail';
						
						$params 	= array( 'userEmail' => $userEmailForm);
						
						
						// Schritt 3 DB: Prepared Statements
						try {
							// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							
							// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);
							
						} catch(PDOException $error) {
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}	
						
						
						// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
						
						$row = $PDOStatement->fetch(PDO::FETCH_ASSOC);
						
if(DEBUG_V)			echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$row <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)			print_r($row);					
if(DEBUG_V)			echo "</pre>";

						// DB-Verbindung schließen
if(DEBUG)			echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						unset($PDO);

						
						#********** 1. VALIDATE EMAIL **********#
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Email-Adresse... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						
						if( $row === false ) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '$userEmailForm' wurde nicht in der DB gefunden! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							// NEUTRALE Fehlermeldung für User
							$errorLogin = 'Loginname oder Passwort falsch!';
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Email-Adresse '$userEmailForm' wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							
													#********** 2. VALIDATE PASSWORD **********#
							
						
							if( password_verify( $passwordForm, $row['userPassword'] ) === false ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt nicht mit dem Passwort aus der DB überein! <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
								// NEUTRALE Fehlermeldung für User
								$errorLogin = 'Loginname oder Passwort falsch!';
								
							} else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Passwort aus dem Formular stimmt mit dem Passwort aus der DB überein. <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
								//$loggedIn 	= true;
								
													#********** 3. PROCESS LOGIN **********#
								
								
										#************************ PREPARE SESSION *********************#
						
								
														#********** START SESSION **********#
								
								if( session_start() === false ) {
									// Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Session! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
								} else {
									// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Session erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
									
									#********** SAVE USER DATA INTO SESSION FILE **********#
									$_SESSION['ID'] 					= $row['userID'];
									$_SESSION['IPAddress'] 			= $_SERVER['REMOTE_ADDR'];
									
if(DEBUG_V)						echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$_SESSION <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)						print_r($_SESSION);					
if(DEBUG_V)						echo "</pre>";		

									
													#********** REDIRECT TO INTERNAL PAGE **********#
									header('LOCATION: index.php');
									
									
								} // 3. PROCESS LOGIN END

							} // 2. VALIDATE PASSWORD END

						} // 1. VALIDATE EMAIL END

					} //  FORM VALIDATION END

				} // PROCESS FORM LOGIN END
				

#*************************************************************************************************#



													#********************************************#
													#********** PROCESS URL PARAMETERS **********#
													#********************************************#
				
				
				// Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
				if( isset($_GET['action']) === true ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
					
					// Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Parameterwert wird ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$action = sanitizeString($_GET['action']);
//if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
					// Schritt 3 URL:  Parameterwert verzweigen
					
					
					#***************************************  LOGOUT  *********************************#
					
					if( $action === 'logout' ) {
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Logout wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// Schritt 4 URL: Parameterwert weiterverarbeiten (in jedem Zweig individuell)
						
						
						// 1. Session löschen
						session_destroy();
						
						// 2. User auf öffentliche Seite umleiten
						header('LOCATION: index.php');
						
						// 3. Fallback, falls die Umleitung per HTTP-Header ausgehebelt werden sollte
						exit();
						
					} // Logput END
					
					
									#********************** action is a Selected CATEGORY*******************
					
					elseif( $action === 'filterByCategory' ) {
						
//if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: filterByCategory... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// Schritt 4 URL: Parameterwert weiterverarbeiten (in jedem Zweig individuell)
						
						
						if( isset($_GET['catID']) === true ){
							
							$catFilterID = sanitizeString($_GET['catID']);
//if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$catFilterID: $catFilterID <i>(" . basename(__FILE__) . ")</i></p>\n";

							
						}
						
						
					
						
					} // action is a Selected CATEGORY END
					
					
					
					
					
					
					
				} // PROCESS URL PARAMETERS END
				




#********************************************************************************************************************#




												#*****************************************************#
												#************ FETCH ALLE CATEGORIES FROM DB **********#
												#*****************************************************#
										
//if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: FETCH ALLE CATEGORIES FROM DB... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
						// Schritt 1 DB: DB-Verbindung herstellen
							$PDO = dbConnect('blogprojekt');
			

						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
							$sql 		= 'SELECT * FROM categories';
								
							$params 	= array();
								
						// Schritt 3 DB: Prepared Statements
							try {
						// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
									
						// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);
									
							} catch(PDOException $error) {
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
							}
				
						// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
								
							$allCategoriesArray = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);

/*
if(DEBUG_V)	echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$allCategoriesArray <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($allCategoriesArray);					
if(DEBUG_V)	echo "</pre>";
*/



#********************************************************************************************************************#



										#************* FETCH ALLE BLOGS FROM DB **************#
										#*****************************************************#

						// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
							$sql 		= 'SELECT blogHeadline, blogImagePath, blogImageAlignment, blogContent, blogDate, userFirstName, userLastName, userCity, catLabel 
											FROM blogs
											INNER JOIN users USING(userID) 
											INNER JOIN categories USING(catID) ';
								
							$params 	= array();
							
										#************* JUST FILTER CATEGORY SHOW **************#
							if( $catFilterID !== NULL ){
								$sql .= ' WHERE catID =:catID ';
								$params['catID'] 	= $catFilterID;
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Filter Kategorie zeigen... <i>(" . basename(__FILE__) . ")</i></p>\n";

							}else{
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Alle Kategorie zeigen... <i>(" . basename(__FILE__) . ")</i></p>\n";

							}
							
							// SHOW LATEST DATE FOR BLOGS 
							$sql .= ' order by blogDate Desc ';	
							
						// Schritt 3 DB: Prepared Statements
							try {
						// Prepare: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
									
						// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
							$PDOStatement->execute($params);
									
							} catch(PDOException $error) {
if(DEBUG) 				echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
							}
				
						// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
								
							$allBlogsArray = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
							


						// DB-Verbindung schließen
if(DEBUG)			echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						unset($PDO);		




#********************************************************************************************************************#



?>






<!doctype html>

<html>
	
	<head>	
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>PHP-Projekt Blog</title>
		
		<link rel="stylesheet" href="./css/main.css">
		<link rel="stylesheet" href="./css/debug.css">
		
		
	</head>
	
	
	<body>
		
		
		
		
		
		
		<!-- -------- PAGE HEADER START -------- -->
		<br>
		<header class="fright loginheader">
		
			<!-- -------- LOGIN FORM START -------- -->
			<?php if( $loggedIn 	=== false ): ?>
						
				<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST">
					<input type="hidden" name="formLogin">
					<fieldset>
						<legend>Login</legend>					
						<span class='error'><?= $errorLogin ?></span><br>
						<input class="short" type="text" name="userEmailForm" placeholder="Email-Adresse...">
						<input class="short" type="password" name="passwordForm" placeholder="Passwort...">
						<input class="short" type="submit" value="Login">
					</fieldset>
				</form>
				
			
			<!-- -------- LOGIN FORM END -------- -->		
			
			<?php elseif( $loggedIn ): ?>
				<p><a href="?action=logout">Logout</a></p>
				<p><a href="dashboard.php">zum Dashboard >></a></p>
			<?php endif ?>
			
			
		</header>
		
		<hr>
		<!-- -------- PAGE HEADER END -------- -->
		
		
		
		
		
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<hr>
								<!-- -------- ------------------------- IF LOGIN ---------------------------------- -------- -->
		
		
		
		
		
		
		
					<h1>PHP- Projekt Blog</h1>
					<p><a href="index.php">alle Einträge anzeigen</a></p>

										
										<!-- -------- All Blogs Start -------- -->

					<?php foreach( $allBlogsArray as $value ): ?>
						  <div>
						  
								<p><?php echo $value['catLabel'] ?></p>
								<p><?php echo $value['blogHeadline'] ?></p><br>
								<p><?php echo $value['userFirstName'] ?> <?php echo $value['userLastName'] ?> (<?php echo $value['userCity'] ?>) schrieb am <?= isoToEuDateTime($value['blogDate'])['date'] ?> um <?= isoToEuDateTime($value['blogDate'])['time'] ?> Uhr</p><br>
								
								

								<?php if( $value['blogImagePath'] ): ?>
									<?php if( $value['blogImageAlignment'] == 'left'): ?>
										<img src="<?= $value['blogImagePath'] ?>" alt="Bild" class="avatar fleft">
										<?php elseif( $value['blogImageAlignment'] == 'right'): ?>
											<img src="<?= $value['blogImagePath'] ?>" alt="Bild" class="avatar fright">
									<?php endif ?>
								<?php endif ?>
								
								<p><?php echo $value['blogContent'] ?></p>
								<hr>
						  
						  
						  </div>
						
					 <?php endforeach; ?>
					
					
										<!-- -------- All Blogs End -------- -->
										
										
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>
				<br>				
					
					
									<!-- -------- All Categories Start -------- -->
					
					<?php foreach($allCategoriesArray as $value): ?>
						  <p><a href="?action=filterByCategory&catID=<?php echo $value['catID'] ?>"><?php echo $value['catLabel'] ?></a></p>
						
					 <?php endforeach; ?>
					
					
									<!-- -------- All Categories End -------- -->





									


				
					 

	
			
			
		
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			

		<br>
		<br>
		<br>
		<br>
		
	</body>
	
</html>