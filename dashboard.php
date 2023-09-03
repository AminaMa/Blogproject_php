<?php
#**********************************************************************************#

				
				#****************************************#
				#********** PAGE CONFIGURATION **********#
				#****************************************#
				
				
				require_once('./include/config.inc.php');
				require_once('./include/db.inc.php');
				require_once('./include/form.inc.php');
				require_once('./include/dateTime.inc.php');


#**********************************************************************************#


				#****************************************#
				#********** SECURE PAGE ACCESS **********#
				#****************************************#
				
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
if(DEBUG)		echo "<p class='debug auth err'><b>Line " . __LINE__ . "</b>: Login konnte nicht validiert werden! <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
					
					#********** DENY PAGE ACCESS **********#
					// 1. Session löschen
					
					session_destroy();
					
					// 2. User auf öffentliche Seite umleiten
					
					header('LOCATION: index.php');
					exit();					
					

				#********** VALID LOGIN **********#
				} else {
					// Erfolgsfall (Seitenaufrufer ist eingeloggt)
if(DEBUG)		echo "<p class='debug auth ok'><b>Line " . __LINE__ . "</b>: Login wurde erfolgreich validiert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
					session_regenerate_id(true);
					
					$userID = $_SESSION['ID'];
				}
				

#**********************************************************************************#


				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
								
				$errorNewCategory				= NULL;
				//$errorNewCatMessage	 	= NULL;
				//$selectCategory				= NULL;
				$catID							= NULL;
				$errorImageUpload				= NULL;
				
				$error							= NULL;
				$info								= NULL;
				$success							= NULL;
				$errorBlogContent				= NULL;
				$errorBlogHeadline			= NULL;


#**********************************************************************************#


				#*********************************************#
				#********** FETCH USER DATA FROM DB **********#
				#*********************************************#
				
if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Userdaten aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				// Schritt 1 DB: DB-Verbindung herstellen
				$PDO = dbConnect('blogprojekt');
				
				// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
				$sql 		= 'SELECT userFirstName, userLastName	
								FROM users 
								WHERE userID = :userID';
				
				$params 	= array( 'userID' => $userID );
				
				// Schritt 3 DB: Prepared Statements
				try {
					// Prepare: SQL-Statement vorbereiten
					$PDOStatement = $PDO->prepare($sql);
					
					// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
					$PDOStatement->execute($params);
					
				} catch(PDOException $error) {
if(DEBUG) 		echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}
				
				// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
			
				$row = $PDOStatement->fetch(PDO::FETCH_ASSOC);


					#********** ALL CATEGORIES READING FROM DB **********#

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



				// DB-Verbindung schließen
if(DEBUG)	echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
				unset($PDO);				
				
				// Werte aus $row zur einfacheren Verarbeitung in Variablen umkopieren
				$userFirstName				= $row['userFirstName'];
				$userLastName				= $row['userLastName'];
				
				



#*****************************************************************************************#



				#********************************************#
				#********** PROCESS URL PARAMETERS **********#
				#********************************************#
				
				
				// Schritt 1 URL: Prüfen, ob Parameter übergeben wurde
				if( isset($_GET['action']) === true ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
					
					// Schritt 2 URL: Parameterwert auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Parameterwert wird ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$action = sanitizeString($_GET['action']);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					
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
						
					} // BRANCHING END
					
				} // PROCESS URL PARAMETERS END
				




#********************************************************************************************************************#




											#*********************************************#
											#********** PROCESS FORM NEW BLOG  **********#
											#*********************************************#
				
				
						// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
					if( isset($_POST['formNewBlog']) === true ) {
if(DEBUG)			echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'formNewBlog' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
						// Schritt 2 FORM: Formulardaten auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Daten werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

						$catID 							= sanitizeString($_POST['selectCategory']);
						$blogHeadline 					= sanitizeString($_POST['blogHeadline']);
						$blogImageAlignment 			= sanitizeString($_POST['blogImageAlignment']);
						$blogContent 					= sanitizeString($_POST['blogContent']);
					
					
						// Schritt 3 FORM: Feldvalidierung, Feldvorbelegung, Final Form Validation
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";


						$errorBlogHeadline 		= validateInputString($blogHeadline, minLength:3);
						$errorBlogContent			= validateInputString($blogContent, maxLength:4000);


						if( $errorBlogHeadline !== NULL OR $errorBlogContent !== NULL ) {
							 
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei <i>(" . basename(__FILE__) . ")</i></p>\n";				
						


														#**********************************#
														#********** IMAGE UPLOAD **********#
														#**********************************#							
											
											#********** CHECK IF IMAGE UPLOAD IS ACTIVE **********#
							if( $_FILES['imgUpload']['tmp_name'] === '' ) {
								// Image Upload inactive
if(DEBUG)					echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Image Upload inactive. <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
							} else {
								// Image Upload active
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Image Upload active. <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
								$validateImageUploadReturnArray = validateImageUpload($_FILES['imgUpload']['tmp_name']);
								
								
//if(DEBUG_V)					echo "<pre class='debug value'><b>Line " . __LINE__ . "</b>: \$validateImageUploadReturnArray <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)					print_r($validateImageUploadReturnArray);					
if(DEBUG_V)					echo "</pre>";								
								
								
								#********** VALIDATE IMAGE UPLOAD **********#
								if( $validateImageUploadReturnArray['imageError'] !== NULL ) {
									// Fehlerfall
									
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Bildupload: $validateImageUploadReturnArray[imageError] <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
									$errorImageUpload = $validateImageUploadReturnArray['imageError'];
									
								} else {
									// Erfolgsfall
									
//if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bild erfolgreich nach <i>'$validateImageUploadReturnArray[imagePath]'</i> auf den Server geladen. <i>(" . basename(__FILE__) . ")</i></p>\n";				
	

							
									
									
												#********** SAVE IMAGE PATH TO VARIABLE **********#
									
									$blogImagePath = $validateImageUploadReturnArray['imagePath'];
								
								} // VALIDATE IMAGE UPLOAD END

							} // IMAGE UPLOAD END
							#*****************************************************#


							#********** FINAL FORM VALIDATION (AFTER IMAGE UPLOAD) **********#
							if( $errorImageUpload !== NULL ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							} else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist komplett fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							

								// Schritt 4 FORM: Formulardaten weiterverarbeiten
//if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Formulardaten werden weiterverarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";
								
												#********** CHECK IMG ALIGMENT **********#
							
								if( $blogImageAlignment == 'left'){
									
								 $blogImageAlignment = 'left';
								 }else{
									 $blogImageAlignment = 'right';
									 
								}  
				
							
								#********** INSERt Felder -BLOG- TO DB **********#
//if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Insert Felder -Blog- in die DB... <i>(" . basename(__FILE__) . ")</i></p>\n";
																
								// Schritt 1 DB: DB-Verbindung herstellen
								$PDO = dbConnect('blogprojekt');
																
								// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
								$sql 		= 'INSERT INTO blogs
												(blogHeadline, blogImagePath, blogImageAlignment, blogContent, catID, userID )
												VALUES
												(:blogHeadline, :blogImagePath, :blogImageAlignment, :blogContent, :catID, :userID )';
								
								$params 	= array( 'blogHeadline' 			=> $blogHeadline,
														'blogImagePath'			=> $blogImagePath,
														'blogImageAlignment' 	=> $blogImageAlignment,
														'blogContent' 				=> $blogContent, 
														'catID' 						=> $catID,
														'userID' 					=> $userID );
								
								// Schritt 3 DB: Prepared Statements
								try {
									// Prepare: SQL-Statement vorbereiten
									$PDOStatement = $PDO->prepare($sql);
									
									// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
									$PDOStatement->execute($params);
									
								} catch(PDOException $error) {
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
									$error = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
								}
								
								// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
								
								$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)					echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
								
								if( $rowCount !== 1 ) {
									// 'Fehlerfall'
									
if(DEBUG)						echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Es wurden keine Blog angelegt. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
									$info = 'Es wurden keine Blog angelegt.';
									
								} else {
									// Erfolgsfall
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Blog erfolgreich angelegt. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
									$success = 'Ihre Blog wurden erfolgreich angelegt.';
									
									
								} // NEW BLOG TO DB END
								
								// DB-Verbindung schließen
if(DEBUG)					echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
								unset($PDO);
								
							} // FINAL FORM VALIDATION (AFTER IMAGE UPLOAD) END
							
						}//CHECK Formular ist formal fehlerfrei END

					}//FORM NEW BLOG END




#********************************************************************************************************************#


													
													#*********************************************#
													#********** PROCESS FORM NEW CATEGORY  **********#
													#*********************************************#
				

			
				
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formNewCategory']) === true ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'formNewCategory' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
					// Schritt 2 FORM: Formulardaten auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Daten werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$newCategory = sanitizeString($_POST['newCategory']);
					
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$newCategory: $newCategory <i>(" . basename(__FILE__) . ")</i></p>\n";

					
					// Schritt 3 FORM: Feldvalidierung, Feldvorbelegung, Final Form Validation
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

					
					
									#********** CHECK IF INPUT NEU CATEGORY LEER**********#
									
									/*
					if( $newCategory === '' ) {
						// NEW CATEGORY LEER
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: muss neue Kategorie schreiben. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							//$errorNewCatMessage = "muss neue Kategorie schreiben";
							
					} else {
						// NEW CATEGORY GESCHRIEBEN
if(DEBUG)			echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>:  neue Kategorie geschrieben... <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						*/
						
	
						#****************** CHECK NEW CATEGORY REQUIREMENTS **********#
if(DEBUG)			echo "<p class='debug'><b>Line " . __LINE__ . "</b>: 1. Prüfe, ob neue Kategorie die Anforderungen erfüllt... <i>(" . basename(__FILE__) . ")</i></p>\n";

						$errorNewCategory = validateInputString($newCategory, minLength:3);

						if( $errorNewCategory !== NULL ) {
							// 1. Fehlerfall: Neues Passwort erfüllt nicht die Anforderungen
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! Das neue NewCategory erfüllt nicht die Anforderungen! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							//$errorNewCatMessage = "muss minLength $minLength";
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei! Das neue NewCategory erfüllt die Anforderungen. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
									
						
							#********** CHECK IF CATEGORY IS ALREADY in DB EXIST **********#
	if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Prüfe, ob Kategorie bereits da ist... <i>(" . basename(__FILE__) . ")</i></p>\n";
							
							// Schritt 1 DB: DB-Verbinsung herstellen
							$PDO = dbConnect('blogprojekt');
							
							
							// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
							$sql 		= 'SELECT COUNT(catLabel) FROM categories
											WHERE catLabel = :newCategory';
							
							$params 	= array( 'newCategory' => $newCategory);
							
							
							// Schritt 3 DB: Prepared Statements
							try {
								// Schritt 2 DB: SQL-Statement vorbereiten
								$PDOStatement = $PDO->prepare($sql);
								
								// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
								$PDOStatement->execute($params);
								
							} catch(PDOException $error) {
if(DEBUG) 				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
							}
							
							
							// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
							
							$count = $PDOStatement->fetchColumn();
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						
							#*********************** DB VALIDATION *************************#
							if( $count !== 0 ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Diese Kategorie '$newCategory' ist bereits in DB! <i>(" . basename(__FILE__) . ")</i></p>\n";				
								$errorNewCategory = 'Diese Kategorie ist bereits in DB !';
							
							} else {
								// Erfolgsfall
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Diese Kategorie '$newCategory' ist noch nicht in DB. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							


							
								#**************** SAVE NEW CATEGORY INTO DATABASE **********#
							
if(DEBUG)					echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere NEW CATEGORY in die DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

								// Schritt 2 DB: SQL-Statement und Placeholder-Array erstellen
								$sql 		= 'INSERT INTO categories
												(catLabel)
												VALUES
												(:newCategory)';
								
								$params 	= array( 'newCategory' 	=> $newCategory);
								
								// Schritt 3 DB: Prepared Statements
								try {
									// Prepare: SQL-Statement vorbereiten
									$PDOStatement = $PDO->prepare($sql);
							
									// Execute: SQL-Statement ausführen und ggf. Platzhalter füllen
									$PDOStatement->execute($params);
							
								} catch(PDOException $error) {
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
									$dbError = 'Fehler beim Zugriff auf die Datenbank!';
								}
								
								// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
								
								$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)					echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
						
								if( $rowCount !== 1 ) {
									// Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern neue Kategorie in die DB! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
																
								} else {
									// Erfolgsfall
									$errorNewCategory = "neue Kategorie erfolgreich mit Namen $newCategory in die DB gespeichert";
									
									$newCatID = $PDO->lastInsertID();							
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: neue Kategorie erfolgreich unter ID$newCatID in die DB gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
								
	
	
									#**************** ALL CATEGORY READING FROM DATABASE **********#
	
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
if(DEBUG) 						echo "<p class='debug db err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
									$dbError = 'Fehler beim Zugriff auf die Datenbank!';
									}
				
									// Schritt 4 DB: Datenbankoperation auswerten und DB-Verbindung schließen
								
									$allCategoriesArray = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
	
		
	
								} // Speichern neue Kategorie in die DB END
							} // CHECK IF CATEGORY ALREADY in DB END

											
						// DB-Verbindung schließen
if(DEBUG)			echo "<p class='debug DB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						unset($PDO);
						
												
						} //  CHECK NEW CATEGORY REQUIREMENTS END
														//} //  CHECK IF INPUT NEU CATEGORY LEER END
				} // FORM NEW CATEGORY END
					#*****************************************************#
					
					
																					

#************************************************************************************************#
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
		
				<p><a href="?action=logout">Logout</a></p>
				<p><a href="index.php">&lt;&lt;zum Frontend </a></p>
			
		</header>
		
		<hr>
		<!-- -------- PAGE HEADER END -------- -->
		
		<br>
		<br>
		<br>
		<br>
		<hr>
		
		
		<h1>PHP Projekt Blog- Dashboard</h1>
		<p>Aktiver Benutzer:<?= $userFirstName ?> <?= $userLastName ?></p>
		<br>
		<h3>Neuen Blog Eintrag verfassen</h3>
			
			
			
			<!-- -------- USER MESSAGES START -------- -->
			<?php if(isset($error)): ?>
				<h4 class="error"><?php echo $error ?></h4>
			<?php elseif(isset($success)): ?>
				<h4 class="success"><?php echo $success ?></h4>
			<?php elseif(isset($info)): ?>
				<h4 class="info"><?php echo $info ?></h4>
			<?php endif ?>
			<!-- -------- USER MESSAGES END -------- -->
			
			<br>
			
			
					<!-- -------- FORM NEW BLOG START -------- -->

			
		<form action="" method="POST" enctype="multipart/form-data">
				
			<input type="hidden" name="formNewBlog">
				
			<select id="selectCategory" name="selectCategory">
			 <?php foreach($allCategoriesArray as $index => $value): ?>
				  <option value="<?php echo $value['catID'] ?>" <?php if ($catID == $value['catID']) echo 'selected' ?>>
						<?php echo $value['catLabel'] ?>
				  </option>
			 <?php endforeach; ?>
			</select><br>
			
			<span class="error"><?= $errorBlogHeadline ?></span><br>	  
			<input type="text" name="blogHeadline" placeholder="Überschrift" ><br>
			  
			  
			 <fieldset style="width: 80%">
					<legend>Bild hochladen</legend>
					<span class="error"><?= $errorImageUpload ?></span><br>
					
					<input type="file" name="imgUpload">
	
			
					<select name="blogImageAlignment" id="blogImageAlignment">
						<option value="left">align left</option>
						<option value="right">align right</option>
						
					 </select><br>
			
			</fieldset>
			  
			 <span class="error"><?= $errorBlogContent ?></span><br>	
			 <textarea name="blogContent" placeholder="text..." ></textarea><br>
			 <input type="submit" value=" veröffentlichen">
			  
			  
		</form>
						<!-- -------- FORM NEW BLOG END -------- -->




		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
			
			
						<!-- -------- FORM NEUE CATEGORY START -------- -->
			
		<h3>Neue Kategorie anlegen</h3>			
		<form action="" method="POST">
			<input type="hidden" name="formNewCategory">
				
			<fieldset>
				
				<span class="error"><?= $errorNewCategory ?></span><br>
				<input type="text" name="newCategory" placeholder="Name der Kategorie"><br>
			
				<input type="submit" value="Neue Kategorie anlegen">
			</fieldset>
				
		</form>
						<!-- -------- FORM NEUE CATEGORY END -------- -->
			
			

		<br>
		<br>
		<br>
		<br>
		
	</body>
	
</html>









